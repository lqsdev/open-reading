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
use Zend\InputFilter\InputFilter;

/**
 * Filter and validator of topic edit form
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class TopicEditFilter extends InputFilter
{
    /**
     * Initializing validator and filter 
     */
    public function __construct($options = array())
    {
        $params = array(
            'table'  => 'topic',
        );
        if (isset($options['id']) and $options['id']) {
            $params['id'] = $options['id'];
        }
        $this->add(array(
            'name'     => 'name',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
            'validators' => array(
                array(
                    'name'    => 'Module\Article\Validator\RepeatName',
                    'options' => $params,
                ),
            ),
        ));
        
        $this->add(array(
            'name'     => 'slug',
            'required' => false,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
            'validators' => array(
                array(
                    'name'    => 'Module\Article\Validator\RepeatSlug',
                    'options' => $params,
                ),
            ),
        ));

        $this->add(array(
            'name'     => 'title',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        $this->add(array(
            'name'     => 'template',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        $this->add(array(
            'name'     => 'content',
            'required' => false,
        ));

        $this->add(array(
            'name'     => 'description',
            'required' => false,
        ));

        $this->add(array(
            'name'     => 'image',
            'required' => false,
        ));

        $this->add(array(
            'name'     => 'id',
            'required' => false,
        ));
    }
}
