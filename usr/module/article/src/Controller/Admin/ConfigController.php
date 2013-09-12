<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Pi;
use Module\Article\Form\DraftCustomForm;
use Module\Article\Form\DraftCustomFilter;
use Module\Article\File;
use Module\Article\Form\DraftEditForm;
use Module\Article\Service;

/**
 * Config controller
 * 
 * Feature list:
 * 
 * 1. Custom config the form to display in draft edit page
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ConfigController extends ActionController
{
    const ELEMENT_EDIT_PATH = 'var/article/config/elements.edit.php';
    
    const FORM_MODE_NORMAL   = 'normal';
    const FORM_MODE_EXTENDED = 'extension';
    const FORM_MODE_CUSTOM   = 'custom';
    
    /**
     * Saving config result into file
     * 
     * @param array|int  $elements     Elements want to display, if mode is 
     *                                 not custom, its value is mode name
     * @param array      $allElements  All elements in article edit page
     * @param array      $options      Elements of normal and extended mode
     * @return bool 
     */
    protected function saveFormConfig(
        $elements, 
        $allElements, 
        $options = array()
    ) {
        $content =<<<EOD
<?php
/**

EOD;
        
        $normalContent =<<<EOD
 * The elements of normal mode for displaying are showed as follows:
 * 

EOD;
        if (isset($options[self::FORM_MODE_NORMAL])) {
            foreach ($options[self::FORM_MODE_NORMAL] as $value) {
                $normalContent .= ' * ' . $value . "\r\n";
            }
            $content .= $normalContent;
            $content .= " *\r\n";
        }
        
        $extendedContent =<<<EOD
 * The elements of extension mode for displaying are showed as follows:
 * 

EOD;
        if (isset($options[self::FORM_MODE_EXTENDED])) {
            foreach ($options[self::FORM_MODE_EXTENDED] as $value) {
                $extendedContent .= ' * ' . $value . "\r\n";
            }
            $content .= $extendedContent;
            $content .= " *\r\n";
        }
        
        $all =<<<EOD
 * The all elements for displaying are showed as follows, if you choose custom 
 * mode, you need to return the element wants to display in `elements` field.
 * For example:
 * return array(
 *     'mode'     => 'custom',
 *     'elements' => array(
 *         'title',
 *         'subtitle',
 *         ...
 *     ),
 * );      

EOD;
        foreach (array_keys($allElements) as $value) {
            $all .= ' * ' . $value . "\r\n";
        }
        $content .= $all;
        $content .= "**/\r\n";
        
        $codeContent =<<<EOD
return array(

EOD;
        if (is_string($elements)) {
            $codeContent .= '    \'mode\'     => \'' . $elements . '\',' . "\r\n";
        } else {
            $codeContent .= '    \'mode\'     => \'' 
                         . self::FORM_MODE_CUSTOM 
                         . '\',' . "\r\n";
            $codeContent .= '    \'elements\' => array(' . "\r\n";
            foreach ($elements as $element) {
                $codeContent .= '        \'' . $element . '\',' . "\r\n";
            }
            $codeContent .= '    ),' . "\r\n";
        }
        $content .= $codeContent;
        $content .= ');' . "\r\n";
        
        $filename = Pi::path(self::ELEMENT_EDIT_PATH);
        $result   = File::addContent($filename, $content);
        
        return $result;
    }
    
    /**
     * Default action, jump to form configuration page
     * 
     * @return ViewModel 
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', array('action' => 'form'));
    }

    /**
     * Config whether to display form in draft edit page
     * 
     * @return ViewModel 
     */
    public function formAction()
    {
        $items = DraftEditForm::getExistsFormElements();
        $this->view()->assign('items', $items);
        
        $form = new DraftCustomForm('custom', array('elements' => $items));
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => 'form')),
            'method'  => 'post',
            'class'   => 'form-horizontal',
        ));
        $options = Service::getFormConfig();
        if (!empty($options)) {
            $data['mode'] = $options['mode'];
            if (self::FORM_MODE_CUSTOM == $options['mode']) {
                foreach ($options['elements'] as $value) {
                    $data[$value] = 1;
                }
            }
            $form->setData($data);
        }
        
        $this->view()->assign('title', __('Configuration Form'));
        $this->view()->assign('form', $form);
        $this->view()->assign('custom', self::FORM_MODE_CUSTOM);
        $this->view()->assign('action', 'form');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (self::FORM_MODE_CUSTOM != $post['mode']) {
                foreach (array_keys($items) as $name) {
                    $post[$name] = 0;
                }
            }
            $neededElements = DraftEditForm::getNeededElements();
            $form->setData($post);
            $form->setInputFilter(
                new DraftCustomFilter($post['mode'], 
                array('needed' => $neededElements))
            );

            if (!$form->isValid()) {
                return Service::renderForm(
                    $this, 
                    $form, 
                    __('Items marked red is required!')
                );
            }
            
            $data     = $form->getData();
            $elements = array();
            if (self::FORM_MODE_CUSTOM == $data['mode']) {
                foreach (array_keys($items) as $name) {
                    if (!empty($data[$name])) {
                        $elements[] = $name;
                    }
                }
            } else {
                $elements = $data['mode'];
            }
            $options = array(
                self::FORM_MODE_NORMAL   => DraftEditForm::getDefaultElements(
                    self::FORM_MODE_NORMAL
                ),
                self::FORM_MODE_EXTENDED => DraftEditForm::getDefaultElements(
                    self::FORM_MODE_EXTENDED
                ),
            );
            $result  = $this->saveFormConfig($elements, $items, $options);
            if (!$result) {
                return Service::renderForm($this, $form, __('Can not save data!'));
            }
            
            Service::renderForm($this, $form, __('Data saved successful!'), false);
        }
    }
}
