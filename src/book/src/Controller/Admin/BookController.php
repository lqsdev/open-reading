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
        $books = array(
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
                'cover_url' => 'image/book3.png'));
        
        $this->view()->assign('books', $books);
        $this->view()->setTemplate('book-list');
    }
}
