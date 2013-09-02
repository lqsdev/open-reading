<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Pi;
use Pi\Paginator\Paginator;
use Module\Article\Model\Article;
use Module\Article\Service;
use Module\Article\Entity;

/**
 * Search controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class SearchController extends ActionController
{
    /**
     * Searching articles by title. 
     */
    public function simpleAction()
    {
        $order  = 'time_publish DESC';
        $page   = Service::getParam($this, 'p', 1);
        $module = $this->getModule();

        $config = Pi::service('module')->config('', $module);
        $limit  = intval($config['page_limit_all']) ?: 40;
        $offset = $limit * ($page - 1);

        // Build where
        $where   = array();
        $keyword = Service::getParam($this, 'keyword', '');
        if ($keyword) {
            $where['subject like ?'] = sprintf('%%%s%%', $keyword);
        }
        
        // Retrieve data
        $articleResultset = Entity::getAvailableArticlePage(
            $where, 
            $page, 
            $limit, 
            null, 
            $order, 
            $module
        );

        // Total count
        $where = array_merge($where, array(
            'time_publish <= ?' => time(),
            'status'            => Article::FIELD_STATUS_PUBLISHED,
            'active'            => 1,
        ));
        $modelArticle   = $this->getModel('article');
        $totalCount     = $modelArticle->getSearchRowsCount($where);

        // Paginator
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
            'router'    => $this->getEvent()->getRouter(),
            'route'     => $this->getEvent()
                ->getRouteMatch()->getMatchedRouteName(),
            'params'    => array_filter(array(
                'module'        => $module,
                'controller'    => 'search',
                'action'        => 'simple',
                'keyword'       => $keyword,
            )),
        ));

        $this->view()->assign(array(
            'title'        => __('Search result about '),
            'articles'     => $articleResultset,
            'keyword'      => $keyword,
            'p'            => $page,
            'paginator'    => $paginator,
        ));
    }
}
