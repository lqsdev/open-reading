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
            ),
        ));

        $editorConfig = Pi::config()->load("module.article.ckeditor.php");
       
        $editorConfig['editor'] = 'html';
        $editorConfig['set'] = '';
        
        $this->add(array(
            'name' => 'content',
            'attributes' => array(
                'type' => 'editor',              
                
            ),
            'options' => $editorConfig,
           
        ));
        
        $this->add(array(
            'name' => 'id',
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
