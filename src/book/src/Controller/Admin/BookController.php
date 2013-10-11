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
        $books = array('楞严经', '法华经', '华严经', '心经');
        
        $this->view()->assign('books', $books);
        $this->view()->setTemplate('book-list');
    }
}
