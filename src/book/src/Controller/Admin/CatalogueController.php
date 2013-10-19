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
 * Catalogue controller
 */
class CatalogueController extends ActionController
{
    public function editAction()
    {   
        $bid = $this->params('bid');
        $cid = $this->params('cid');
        $row = $this->getModel('catalogue')->find($cid);
        $pageList = $row == null ?  '' : json_decode($row['data']);
        
        $this->view()->assign('bid', $bid);
        $this->view()->assign('cid', $cid);
        $this->view()->assign('title', 'Book Catalogue Edit');
        $this->view()->assign('pages', $pageList);
        $this->view()->setTemplate('catalogue-edit');
    }
    
    public function saveAction()
    {
        $cid = $this->request->getPost('cid');
        $catalogue = $this->request->getPost('catalogue');
        
        $data = json_encode($catalogue);
        $row = $this->getModel('catalogue')->find($cid);

        if ($row == null) {
            return array(
                'status' => 0,
                'message' => __('Save catalogue data failed.')
            );
        } else {
            $row->assign(array('id' => $cid, 'data' => $data));
            $row->save(false);
            return array(
                'status' => 1,
                'message' => __('Catalogue data saved successfully.')
            );
        }
    }
  
    public function editItemAction()
    {
        $bid = $this->params('bid');
        $cid = $this->params('cid');

        $model = $this->getModel('article');
        $tableArticle = $model->getTable();
        
        $model = $this->getModel('catalogue_rel_article');
        $select = $model->select()
                ->jion(array('article' => $tableArticle),
                        $tableArticle.'.id = article_id',
                        array('title', 'introduction'), Select::JOIN_LEFT)
                ->where(array('book_id' => $bid, 'cata_data_id' => $cid));
        $select->order(array('id'));
        $articles = $model->selectWith($select);
        
        $this->view()->assign('articles', $articles);
        $this->view()->setTemplate('catalogue-edit-item');
    }
}
