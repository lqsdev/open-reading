<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Module\Book\Form;

use Pi\Form\Form as BaseForm;

class BookForm extends BaseForm {

    public function init() {
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                  'type' => 'hidden',              
            ),
        ));
        
        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => __('Book Title'),
            ),
            'attributes' => array(
                       'type' => 'text',
                'placeholder' => __('Title'),
                      'class' => 'span8',
            ),
        ));

        $this->add(array(
            'name' => 'introduction',
            'options' => array(
                'label' => __('Introduction'),
            ),
            'attributes' => array(
                       'type' => 'textarea',
                'placeholder' => __('Introduction'),
                      'class' => 'span8',
                       'rows' => '8',
            ),
        ));
        
        $this->add(array(
            'name' => 'fake_id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => uniqid(),
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'value' => __('Submit'),
            ),
            'type' => 'submit',
        ));
    }
}
