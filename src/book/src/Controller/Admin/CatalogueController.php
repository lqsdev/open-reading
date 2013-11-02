<?php

/**
 * Class controller Class
 * @copyright       Copyright (c) lqsit
 * @license         All rights reserved
 * @author          lqsic
 * @package         Module\book
*/

namespace Module\Book\Controller\Admin;

use \Zend\Db\Sql\Select;
use Pi\Mvc\Controller\ActionController;

/**
 * Catalogue controller
 */
class CatalogueController extends ActionController
{
    public function editAction()
    {   
        $bid = $this->params('bid');

        $rowBook = $this->getModel('book')->find($bid);
        $cid = $rowBook['catalogue_id'];

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
            $row = $this->getModel('catalogue')->createRow(array('id' => $cid, 'data' => $data));
            $row->save(false);
            return array(
                'status' => 1,
                'message' => __('Insert catalogue succeed.')
            );
        } else {
            $row->assign(array('id' => $cid, 'data' => $data));
            $row->save(false);
            return array(
                'status' => 1,
                'message' => __('Update catalogue succeed.')
            );
        }
    }
  
    public function editItemAction()
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
        $this->view()->setTemplate('catalogue-edit-item');
    }
}
