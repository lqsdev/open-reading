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
 * Article controller
 */
class ArticleController extends ActionController
{

    public function editAction()
    {
        $this->view()->setTemplate('article-edit');
    }

    public function viewAction()
    {   
        $this->view()->setTemplate('article-view');
    }
}