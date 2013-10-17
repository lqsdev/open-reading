<?php

/**
 * Class Indexcontroller Class
 * @copyright       Copyright (c) lqsit
 * @license         All rights reserved
 * @author          lqsic
 * @package         Module\book
 */

namespace Module\Book\Controller\Front;

use Pi\Mvc\Controller\ActionController;

/**
 * Index controller
 */
class IndexController extends ActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute(
            '',
            array(
                'controller' => 'Book', 
                'action'     => 'list'
            )
        ); 
    }
}
