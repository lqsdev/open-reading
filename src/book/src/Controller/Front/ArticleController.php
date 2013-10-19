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
 * Article controller
 */
class ArticleController extends ActionController
{
    public function viewAction()
    {
        $id = $this->params('id');
        $row = $this->getModel('article')->find($id);
        if ($row != null) {
            $article = $row->toArray();
        }
        
        
        $this->view()->assign('article', $article);
        $this->view()->setTemplate('article-view');
    }
}
