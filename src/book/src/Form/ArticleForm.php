<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Module\Book\Form;

use Pi\Form\Form as BaseForm;

class ArticleForm extends BaseForm {

    public function init() {
        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => __('Title'),
            ),
            'attributes' => array(
                'type' => 'text',
                'value' => __('Title'),
            ),
        ));

        $this->add(array(
            'name' => 'content',
            'options' => array(
                'label' => __('Content'),
            ),
            'attributes' => array(
                'type' => 'textarea',
                'value' => __('Content'),
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
