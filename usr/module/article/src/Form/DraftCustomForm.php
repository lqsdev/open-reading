<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Module\Article\Controller\Admin\ConfigController;

/**
 * Custom draft form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftCustomForm extends BaseForm
{
    /**
     * Form elements in draft edit page
     * @var array 
     */
    protected $items = array();
    
    public function __construct($name, $options = array())
    {
        $this->items = isset($options['elements']) 
            ? $options['elements'] : array();
        parent::__construct($name);
    }
    
    /**
     * Initialize form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'mode',
            'options'    => array(
                'label'    => __('Form Mode'),
            ),
            'attributes' => array(
                'value'    => ConfigController::FORM_MODE_EXTENDED,
                'options'  => array(
                    ConfigController::FORM_MODE_NORMAL   => __('Normal'),
                    ConfigController::FORM_MODE_EXTENDED => __('Extended'),
                    ConfigController::FORM_MODE_CUSTOM   => __('Custom'),
                ),
            ),
            'type'       => 'radio',
        ));
        
        foreach ($this->items as $name => $title) {
            $this->add(array(
                'name'          => $name,
                'options'       => array(
                    'label'     => $title,
                ),
                'attributes'    => array(
                    'value'     => 0,
                ),
                'type'          => 'checkbox',
            ));
        }

        $this->add(array(
            'name'          => 'submit',
            'attributes'    => array(                
                'value' => __('Submit'),
            ),
            'type'  => 'submit',
        ));
    }
}
