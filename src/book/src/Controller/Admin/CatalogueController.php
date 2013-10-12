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
        $bookid = $this->params('bookid');
        $data = '[{"label":"\u7b2c\u4e00\u7ae0","id":1,"pid":0,"depth":0},{"label":"\u7b2c\u4e00\u8282","id":2,"pid":1,"depth":1},{"label":"\u7b2c\u4e00\u5c0f\u8282","id":3,"pid":2,"depth":2},{"label":"\u7b2c\u4e8c\u7ae0","id":4,"pid":0,"depth":0},{"label":"\u7b2c\u4e09\u7ae0","id":5,"pid":0,"depth":0}]';
        $pageList = json_decode($data);        
        $this->view()->assign('bookid', $bookid);
        $this->view()->assign('title', 'Book Catalogue Edit');
        $this->view()->assign('pages', $pageList);
        $this->view()->setTemplate('catalogue-edit');
    }
    
    public function saveAction()
    {
        $status = 1;
        $message = __('Catalogue data saved successfully.');

        $bookid    = $this->request->getPost('bookid');
        $catalogue = $this->request->getPost('catalogue');

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }
  
    public function editItemAction()
    {   
        $this->view()->setTemplate('catalogue-edit-item');
    }
}
