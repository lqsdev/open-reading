<?php

/**
 * Class controller Class
 * @copyright       Copyright (c) lqsit
 * @license         All rights reserved
 * @author          lqsic
 * @package         Module\book
*/

namespace Module\Book\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Module\Book\Form\ArticleFilter;
use Module\Book\Form\ArticleForm;

/**
 * Article controller
 */
class ArticleController extends ActionController
{
    public function editAction()
    {
        $form = new ArticleForm('article');        
        $form->setAttribute('action', $this->url('', array('action' => 'edit')));
        $this->view()->assign('form', $form);                
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);            
            $form->setInputFilter(new ArticleFilter);
            if (!$form->isValid()) {
                $this->view()->assign('form', $form);
                return;
            }
            $data = $form->getData();                     
            $inputData = array(
                'title'   => $data['title'],
                'content' => $data['content'],
                );
            $row = $this->getModel('article')->createRow($inputData);
            $row->save(); 
        }
        $this->view()->assign('form', $form);
        $this->view()->setTemplate('article-edit');
    }
}
