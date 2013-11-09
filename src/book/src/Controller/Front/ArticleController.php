<?php

/**
 * Class controller Class
 * @copyright       Copyright (c) lqsit
 * @license         All rights reserved
 * @author          lqsic
 * @package         Module\book
*/

namespace Module\Book\Controller\Front;

use \Zend\Db\Sql\Select;
use Pi\Mvc\Controller\ActionController;

/**
 * Article controller
 */
class ArticleController extends ActionController
{
    public function viewAction()
    {
        $bid = $this->params('bid');
        $cdid = $this->params('cdid');
        
        $id = $this->params('id');
        $row = $this->getModel('article')->find($id);
        if ($row != null) {
            $article = $row->toArray();
        }
        
        $this->view()->assign('bid', $bid);
        $this->view()->assign('cdid', $cdid);        
        $this->view()->assign('article', $article);
        $this->view()->setTemplate('article-view');
    }
    
    public function listAction()
    {
        $bid = $this->params('bid');
        $cdid = $this->params('cdid');

        $articleTable = $this->getModel('article')->getTable();
        $relTable = $this->getModel('catalogue_rel_article')->getTable();
        $model = $this->getModel('catalogue_rel_article');

        $select = $model->select()
                ->join(array('article' => $articleTable),
                         $relTable. '.article_id = article.id',
                        array('title'),
                        Select::JOIN_LEFT)
                ->where(array('book_id' => $bid, 'cata_data_id' => $cdid));
        $select->order(array('id'));
        $articles = $model->selectWith($select);

        $this->view()->assign('bid', $bid);
        $this->view()->assign('cdid', $cdid);
        $this->view()->assign('articles', $articles);
        $this->view()->setTemplate('article-list');
    }
}
