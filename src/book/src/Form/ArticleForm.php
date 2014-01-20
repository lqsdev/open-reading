<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Module\Book\Form;

use Pi\Form\Form as BaseForm;
use Pi;

class ArticleForm extends BaseForm {

    public function init() {
        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => __('Title'),
            ),
            'attributes' => array(
                       'type' => 'text',
                'placeholder' => __('Title'),
                      'class' => 'span12',
            ),
        ));

        $this->add(array(
            'name' => 'content',
            'options' => array(
                'label' => __('Content'),
            ),
            'attributes' => array(
                       'type' => 'textarea',
                'placeholder' => __('Conent'),
                      'class' => 'span12',
                       'rows' => 20,
            ),
        ));
        
//        $editorConfig = Pi::config()->load("module.article.ckeditor.php");
//        $editorConfig['editor'] = 'html';
//        $editorConfig['set'] = '';
//        
//        $this->add(array(
//            'name' => 'content',
//            'attributes' => array(
//                'type' => 'editor',
//                
//            ),
//            'options' => $editorConfig,
//        ));
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',              
            ),
        ));
        
        $this->add(array(
            'name' => 'bid',
            'attributes' => array(
                'type' => 'hidden',              
            ),
        ));
        
        $this->add(array(
            'name' => 'cdid',
            'attributes' => array(
                'type' => 'hidden',              
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
