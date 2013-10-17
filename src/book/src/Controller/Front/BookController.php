<?php

/**
 * Class controller Class
 * @copyright       Copyright (c) lqsit
 * @license         All rights reserved
 * @author          lqsic
 * @package         Module\book
*/

namespace Module\Book\Controller\Front;

use Pi\Mvc\Controller\ActionController;

/**
 * Book controller
 */
class BookController extends ActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute('', array('action' => 'list'));
    }
    
    public function listAction()
    {
       
        $columns = array('id', 'title', 'cover_url');
        $model  = $this->getModel('book');
        $select = $model->select()
                        ->columns($columns)
                        ->order('id ASC');

        $rowset = $model->selectWith($select);

        $books  = array();
        foreach ($rowset as $row) {
            $books[$row->id] = $row->toArray();        
        }

        /*$books = array(
            array(    'id'  => 1,
                    'title' => '森林心',
                'cover_url' => 'image/book1.png'),
            array(    'id'  => 2,
                    'title' => '好消息',
                'cover_url' => 'image/book2.png'),
            array(    'id'  => 3,
                    'title' => '龙队',
                'cover_url' => 'image/book3.png'),
            array(    'id'  => 4,
                    'title' => '森林心',
                'cover_url' => 'image/book1.png'),
            array(    'id'  => 5,
                    'title' => '好消息',
                'cover_url' => 'image/book2.png'),
            array(    'id'  => 6,
                    'title' => '龙队',
                'cover_url' => 'image/book3.png'));*/
        
        $this->view()->assign('books', $books);
        $this->view()->setTemplate('book-list');
    }

    public function viewAction()
    {
        $id = $this->params('id');

        $row = $this->getModel('book')->find($id);
        if ($row != null) {
            $book = $row->toArray();
        }

        /*$book = array(
            'id'    => $id,
            'title' => '森林心',
            'cover_url' => 'image/book1.png',
            'introduction' => "咒语是佛菩萨的秘密藏中自然流露出的语言，
                当持诵者念诵真言时，就会得到佛菩萨的加持和相应，
                而感召不可思议的力量。\n
                念此咒可得聪明才智，得大智慧。可破除烦恼与障碍。能开发潜能，
                增长智慧，破除疑难，增强记忆、伶俐聪辩，了解诸法真实义。
                亦能消除语业、破愚痴，得诸佛菩萨之般若智慧。");*/

        $this->view()->assign('book', $book);
        $this->view()->setTemplate('book-view');
    }
}
