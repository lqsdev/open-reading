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
        $this->view()->setTemplate('catalogue-view');
    }
  
    public function editItemAction()
    {   
        $this->view()->setTemplate('catalogue-edit-item');
    }
}
