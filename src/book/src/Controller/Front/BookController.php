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

        $cid = $book['catalogue_id'];
        $rowCatalogue = $this->getModel('catalogue')->find($cid);
        
        $catalogue = $rowCatalogue == null ?  '' : json_decode($rowCatalogue['data']);
        
        $this->view()->assign('book', $book);
        $this->view()->assign('catalogue', $catalogue);
        $this->view()->setTemplate('book-view');
    }
}
