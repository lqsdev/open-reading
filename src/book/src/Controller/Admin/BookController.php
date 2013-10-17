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

/**
 * Book controller
 */
class BookController extends ActionController
{
    public function listAction()
    {
        $model = $this->getModel('book');
        $select = $model->select();
        $select->order(array('id'));
        $books = $model->selectWith($select);        

        $this->view()->assign('books', $books);
        $this->view()->setTemplate('book-list');
    }
}
