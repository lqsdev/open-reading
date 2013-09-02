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
use Module\Article\Form\TopicEditForm;
use Module\Article\Form\TopicEditFilter;
use Module\Article\Form\SimpleSearchForm;
use Module\Article\Model\Topic;
use Module\Article\Upload;
use Zend\Db\Sql\Expression;
use Module\Article\Service;
use Module\Article\Model\Article;
use Module\Article\Entity;
use Module\Article\Topic as TopicService;
use Pi\File\Transfer\Upload as UploadHandler;

/**
 * Topic controller
 * 
 * Feature list:
 * 
 * 1. List\add\edit\delete a topic
 * 2. Pull\remove article to\from topic
 * 3. Homepage of a certain topic
 * 4. Article list of a certain topic
 * 5. All topic list
 * 6. AJAX action used to save or remove a topic image
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class TopicController extends ActionController
{
    /**
     * Get topic form object
     * 
     * @param string $action  Form name
     * @return \Module\Article\Form\TopicEditForm 
     */
    protected function getTopicForm($action = 'add')
    {
        $form = new TopicEditForm();
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => $action)),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'class'   => 'form-horizontal',
        ));

        return $form;
    }

    /**
     * Save topic information
     * 
     * @param  array    $data  Topic information
     * @return boolean
     * @throws \Exception 
     */
    protected function saveTopic($data)
    {
        $module     = $this->getModule();
        $modelTopic = $this->getModel('topic');
        $fakeId     = $image = null;

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }
        $data['active'] = 1;

        $fakeId = Service::getParam($this, 'fake_id', 0);

        unset($data['image']);
        
        if (isset($data['slug']) && empty($data['slug'])) {
            unset($data['slug']);
        }

        if (empty($id)) {
            $rowTopic = $modelTopic->createRow($data);
            $rowTopic->save();
            $id       = $rowTopic->id;
        } else {
            $rowTopic = $modelTopic->find($id);

            if (empty($rowTopic->id)) {
                return false;
            }

            $rowTopic->assign($data);
            $rowTopic->save();
        }

        // Save image
        $session    = Upload::getUploadSession($module, 'topic');
        if (isset($session->$id) 
            || ($fakeId && isset($session->$fakeId))
        ) {
            $uploadInfo = isset($session->$id)
                ? $session->$id : $session->$fakeId;

            if ($uploadInfo) {
                $fileName = $rowTopic->id;

                $pathInfo = pathinfo($uploadInfo['tmp_name']);
                if ($pathInfo['extension']) {
                    $fileName .= '.' . $pathInfo['extension'];
                }
                $fileName = $pathInfo['dirname'] . '/' . $fileName;

                $rowTopic->image = rename(
                    Pi::path($uploadInfo['tmp_name']),
                    Pi::path($fileName)
                ) ? $fileName : $uploadInfo['tmp_name'];
                $rowTopic->save();
            }

            unset($session->$id);
            unset($session->$fakeId);
        }

        return $id;
    }
    
    /**
     * Homepage of a topic
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        $topic   = Service::getParam($this, 'topic', '');
        if (empty($topic)) {
            return $this->jumpTo404(__('Invalid topic ID!'));
        }
        if (is_numeric($topic)) {
            $row = $this->getModel('topic')->find($topic);
        } else {
            $row = $this->getModel('topic')->find($topic, 'slug');
        }
        if (!$row->id) {
            return $this->jumpTo404(__('Topic is not exists!'));
        }
        // Return 503 code if topic is not active
        if (!$row->active) {
            return $this->jumpToException(
                __('The topic requested is not active'),
                503
            );
        }
        
        // Get topic articles
        $rowRelations = $this->getModel('article_topic')
                             ->select(array('topic' => $row->id));
        $articleIds   = array();
        foreach ($rowRelations as $relation) {
            $articleIds[] = $relation->article;
        }
        $articleIds   = array_filter($articleIds);
        if (!empty($articleIds)) {
            $where    = array(
                'id'  => $articleIds,
            );
            $articles = Entity::getAvailableArticlePage($where, null, null);
        }
        
        $this->view()->assign(array(
            'content'   => $row->content,
            'title'     => $row->title,
            'image'     => Pi::url($row->image),
            'articles'  => $articles,
        ));
        
        $template = ('default' == $row->template)
            ? 'topic-index' : $row->template;
        $this->view()->setTemplate($template);
    }
    
    /**
     * Topic list page for viewing 
     */
    public function allTopicAction()
    {
        $page       = Service::getParam($this, 'p', 1);
        $page       = $page > 0 ? $page : 1;

        $config = Pi::service('module')->config('', $module);
        $limit  = (int) $config['page_topic_front'] ?: 20;
        
        $where = array(
            'active' => 1,
        );
        
        // Get topics
        $resultsetTopic = TopicService::getTopics($where, $page, $limit);
        
        // Get topic article counts
        $model  = $this->getModel('article_topic');
        $select = $model->select()
                        ->columns(array(
                            'count' => new Expression('count(id)'), 'topic'))
                        ->group(array('topic'));
        $rowRelation   = $model->selectWith($select);
        $articleCount  = array();
        foreach ($rowRelation as $row) {
            $articleCount[$row->topic] = $row->count;
        }

        // Total count
        $modelTopic = $this->getModel('topic');
        $totalCount = $modelTopic->getSearchRowsCount($where);

        // Pagination
        $route     = '.' . Service::getRouteName();
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
                  ->setCurrentPageNumber($page)
                  ->setUrlOptions(array(
                'router'    => $this->getEvent()->getRouter(),
                'route'     => $route,
                'params'    => array(
                    'topic'      => 'all',
                ),
            ));

        $this->view()->assign(array(
            'title'         => __('All Topics'),
            'topics'        => $resultsetTopic,
            'paginator'     => $paginator,
            'count'         => $articleCount,
        ));
    }
    
    /**
     * list articles of a topic for users to view
     */
    public function listAction()
    {
        $topic   = Service::getParam($this, 'topic', '');
        if (empty($topic)) {
            return $this->jumpTo404(__('Invalid topic ID!'));
        }
        if (is_numeric($topic)) {
            $row = $this->getModel('topic')->find($topic);
        } else {
            $row = $this->getModel('topic')->find($topic, 'slug');
        }
        $title = $row->title;
        // Return 503 code if topic is not active
        if (!$row->active) {
            return $this->jumpToException(
                __('The topic requested is not active'),
                503
            );
        }
        
        $topicId    = $row->id;
        $page       = Service::getParam($this, 'p', 1);
        $page       = $page > 0 ? $page : 1;

        $module = $this->getModule();
        $config = Pi::service('module')->config('', $module);
        $limit  = (int) $config['page_topic_front'] ?: 20;
        
        // Getting relations
        $modelRelation = $this->getModel('article_topic');
        $rowRelation   = $modelRelation->select(array('topic' => $topicId));
        $articleIds    = array();
        foreach ($rowRelation as $row) {
            $articleIds[] = $row['article'];
        }
        
        $where = array(
            'id' => $articleIds,
        );
        
        // Get articles
        $resultsetArticle = Entity::getAvailableArticlePage(
            $where,
            $page,
            $limit
        );

        // Total count
        $where = array_merge($where, array(
            'time_publish <= ?' => time(),
            'status'            => Article::FIELD_STATUS_PUBLISHED,
            'active'            => 1,
        ));
        $modelArticle   = $this->getModel('article');
        $totalCount     = $modelArticle->getSearchRowsCount($where);

        // Pagination
        $route     = '.' . Service::getRouteName();
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
                'router'    => $this->getEvent()->getRouter(),
                'route'     => $route,
                'params'    => array(
                    'topic'      => $topic,
                    'list'       => 'all',
                ),
            ));

        $this->view()->assign(array(
            'title'         => empty($topic) ? __('All') : $title,
            'articles'      => $resultsetArticle,
            'paginator'     => $paginator,
        ));
    }
    
    /**
     * List articles of a topic for management
     */
    public function listArticleAction()
    {
        // Checking permission
        $allowed = Service::getModuleResourcePermission('topic');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $modelTopic = $this->getModel('topic');

        $topic      = Service::getParam($this, 'topic', '');
        $page       = Service::getParam($this, 'p', 1);
        $page       = $page > 0 ? $page : 1;

        $module = $this->getModule();
        $config = Pi::service('module')->config('', $module);
        $limit  = (int) $config['page_limit_management'] ?: 20;
        $offset = ($page - 1) * $limit;
        $where  = array();
        
        if (!empty($topic)) {
            $topicId = is_numeric($topic) 
                ? (int) $topic : $modelTopic->slugToId($topic);
            $where['topic'] = $topicId;
        }
        
        // Selecting articles
        $modelRelation = $this->getModel('article_topic');
        $select        = $modelRelation->select()
                                       ->where($where)
                                       ->offset($offset)
                                       ->limit($limit)
                                       ->order('article DESC');
        $rowArticleSet = $modelRelation->selectWith($select)->toArray();
        
        // Getting article details
        $articleIds    = array();
        foreach ($rowArticleSet as $row) {
            $articleIds[] = $row['article'];
        }
        $articleIds    = empty($articleIds) ? 0 : $articleIds;
        $articles      = Entity::getArticlePage(
            array('id' => $articleIds),
            1,
            $limit
        );
        
        // Get topic details
        $rowTopicSet   = $modelTopic->select(array());
        $topics        = array();
        foreach ($rowTopicSet as $row) {
            $topics[$row['id']] = $row['title'];
        }
        
        // Get topic info
        $select     = $modelRelation->select()
            ->where($where)
            ->columns(array('count' => new Expression('count(id)')));
        $totalCount = (int) $modelRelation->selectWith($select)
            ->current()->count;

        // Pagination
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
                'router'    => $this->getEvent()->getRouter(),
                'route'     => $this->getEvent()
                    ->getRouteMatch()
                    ->getMatchedRouteName(),
                'params'    => array_filter(array(
                    'module'        => $module,
                    'controller'    => 'topic',
                    'action'        => 'list-article',
                    'topic'         => $topic,
                )),
            ));

        $this->view()->assign(array(
            'title'         => __('Article List in Topic'),
            'articles'      => $rowArticleSet,
            'details'       => $articles,
            'topics'        => $topics,
            'topic'         => $topic,
            'paginator'     => $paginator,
            'config'        => $config,
            'action'        => 'list-article',
        ));
    }
    
    /**
     * List all articles for pulling
     */
    public function pullAction()
    {
        // Checking permission
        $allowed = Service::getModuleResourcePermission('topic');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $where  = array();
        $page   = Service::getParam($this, 'page', 1);
        $limit  = Service::getParam($this, 'limit', 20);
        $order  = 'time_publish DESC';

        $data   = $ids = array();

        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');
        $categoryModel  = $this->getModel('category');

        // Get category
        $category = Service::getParam($this, 'category', 0);
        if ($category > 1) {
            $categoryIds = $categoryModel->getDescendantIds($category);
            if ($categoryIds) {
                $where['category'] = $categoryIds;
            }
        }
        
        // Get topic
        $modelTopic = $this->getModel('topic');
        $topics     = $modelTopic->getList();

        // Build where
        $where['status'] = Article::FIELD_STATUS_PUBLISHED;
        
        $keyword = Service::getParam($this, 'keyword', '');
        if (!empty($keyword)) {
            $where['subject like ?'] = sprintf('%%%s%%', $keyword);
        }

        // Retrieve data
        $data = Entity::getArticlePage($where, $page, $limit);
        
        // Getting article topic
        $articleIds  = array_keys($data);
        if (empty($articleIds)) {
            $articleIds = array(0);
        }
        $rowRelation = $this->getModel('article_topic')
            ->select(array('article' => $articleIds));
        $relation    = array();
        foreach ($rowRelation as $row) {
            if (isset($relation[$row['article']])) {
                $relation[$row['article']] .= ',' . $topics[$row['topic']];
            } else {
                $relation[$row['article']] = $topics[$row['topic']];
            }
        }

        // Total count
        $select = $modelArticle->select()
            ->columns(array('total' => new Expression('count(id)')))
            ->where($where);
        $resulsetCount = $modelArticle->selectWith($select);
        $totalCount    = (int) $resulsetCount->current()->total;

        // Paginator
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
            'router'    => $this->getEvent()->getRouter(),
            'route'     => $this->getEvent()
                ->getRouteMatch()
                ->getMatchedRouteName(),
            'params'    => array_filter(array(
                'module'        => $module,
                'controller'    => 'topic',
                'action'        => 'pull',
                'category'      => $category,
                'keyword'       => $keyword,
            )),
        ));

        // Prepare search form
        $form = new SimpleSearchForm;
        $form->setData($this->params()->fromQuery());
        
        $this->view()->assign(array(
            'title'      => __('All Articles'),
            'data'       => $data,
            'form'       => $form,
            'paginator'  => $paginator,
            'category'   => $category,
            'categories' => Service::getCategoryList(),
            'action'     => 'pull',
            'topics'     => $topics,
            'relation'   => $relation,
        ));
    }
    
    /**
     * Pull articles into topic
     * 
     * @return ViewModel 
     */
    public function pullArticleAction()
    {
        // Checking permission
        $allowed = Service::getModuleResourcePermission('topic');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $topic = Service::getParam($this, 'topic', '');
        $id    = Service::getParam($this, 'id', 0);
        $ids   = array_filter(explode(',', $id));
        $from  = Service::getParam($this, 'from', '');
        if (empty($topic)) {
            return Service::jumpToErrorOperation(
                $this, 
                __('Target topic is needed!')
            );
        }
        if (empty($ids)) {
            return Service::jumpToErrorOperation(
                $this, 
                __('No articles are selected, please try again!')
            );
        }
        
        $data  = array();
        foreach ($ids as $value) {
            $data[$value] = array(
                'article' => $value,
                'topic'   => $topic,
            );
        }
        
        $model = $this->getModel('article_topic');
        $rows  = $model->select(array('article' => $ids));
        foreach ($rows as $row) {
            if ($topic == $row->topic) {
                unset($data[$row->article]);
            }
        }
        
        foreach ($data as $item) {
            $row = $model->createRow($item);
            $row->save();
        }
        
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-article')
            );
        }
    }
    
    /**
     * Remove pulled articles from a topic
     * 
     * @return ViewModel 
     */
    public function removePullAction()
    {
        $allowed = Service::getModuleResourcePermission('topic');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $id    = Service::getParam($this, 'id', 0);
        $ids   = array_filter(explode(',', $id));
        $from  = Service::getParam($this, 'from', '');
        if (empty($ids)) {
            return $this->jumpTo404('Invalid ID!');
        }
        
        $this->getModel('article_topic')->delete(array('id' => $ids));
                
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-article')
            );
        }
    }
    
    /**
     * Add topic information
     * 
     * @return ViewModel 
     */
    public function addAction()
    {
        $allowed = Service::getModuleResourcePermission('topic');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $form   = $this->getTopicForm('add');
        $form->setData(array(
            'fake_id'  => Upload::randomKey(),
        ));

        Service::setModuleConfig($this);
        $this->view()->assign(array(
            'title'                 => __('Add Topic Info'),
            'form'                  => $form,
        ));
        $this->view()->setTemplate('topic-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new TopicEditFilter);
            $form->setValidationGroup(Topic::getAvailableFields());
            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    __('There are some error occured!')
                );
            }
            
            $data = $form->getData();
            $id   = $this->saveTopic($data);
            if (!$id) {
                return Service::renderForm(
                    $this,
                    $form,
                    __('Can not save data!')
                );
            }
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-topic')
            );
        }
    }

    /**
     * Edit topic information
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        $allowed = Service::getModuleResourcePermission('topic');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        Service::setModuleConfig($this);
        $this->view()->assign('title', __('Edit Topic Info'));
        
        $form = $this->getTopicForm('edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $options = array(
                'id'   => $post['id'],
            );
            $form->setInputFilter(new TopicEditFilter($options));
            $form->setValidationGroup(Topic::getAvailableFields());
            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    __('Can not update data!')
                );
            }
            $data = $form->getData();
            $id   = $this->saveTopic($data);

            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-topic')
            );
        }
        
        $id     = $this->params('id', 0);
        if (empty($id)) {
            $this->jumpto404(__('Invalid topic ID!'));
        }

        $model = $this->getModel('topic');
        $row   = $model->find($id);
        if (!$row->id) {
            return $this->jumpTo404(__('Can not find topic!'));
        }
        
        $form->setData($row->toArray());

        $this->view()->assign('form', $form);
    }
    
    /**
     * Delete topic
     * 
     * @return ViewModel 
     */
    public function deleteAction()
    {
        $allowed = Service::getModuleResourcePermission('topic');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $id     = $this->params('id');
        if (empty($id)) {
            return $this->jumpTo404(__('Invalid topic ID!'));
        }

        $topicModel = $this->getModel('topic');

        // Remove relationship between topic and articles
        $this->getModel('article_topic')->delete(array('topic' => $id));

        // Delete image
        $row = $topicModel->find($id);
        if ($row && $row->image) {
            @unlink(Pi::path($row->image));
        }

        // Remove topic
        $topicModel->delete(array('id' => $id));

        // Go to list page
        return $this->redirect()->toRoute(
            '',
            array('action' => 'list-topic')
        );
    }

    /**
     * List all added topic for management
     */
    public function listTopicAction()
    {
        $allowed = Service::getModuleResourcePermission('topic');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $module = $this->getModule();
        $config = Pi::service('module')->config('', $module);
        $limit  = (int) $config['page_limit_management'] ?: 20;
        $page   = Service::getParam($this, 'p', 1);
        $page   = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $limit;
        
        $model  = $this->getModel('topic');
        $select = $model->select()
                        ->offset($offset)
                        ->limit($limit);
        $rowset = $model->selectWith($select);
        
        $select = $model->select()
            ->columns(array('count' => new Expression('count(*)')));
        $count  = (int) $model->selectWith($select)->current()->count;
        
        $paginator = Paginator::factory($count);
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()
                ->getRouteMatch()
                ->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => 'topic',
                'action'        => 'list-topic',
            ),
        ));

        $this->view()->assign(array(
            'title'   => __('Topic List'),
            'topics'  => $rowset,
            'action'  => 'list-topic',
            'route'   => '.' . Service::getRouteName(),
            'defaultLogo' => Pi::service('asset')
                ->getModuleAsset('image/default-topic-thumb.png', $module),
        ));
    }

    /**
     * Active or deactivate a topic.
     * 
     * @return ViewModel 
     */
    public function activeAction()
    {
        $allowed = Service::getModuleResourcePermission('topic');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $status = Service::getParam($this, 'status', 0);
        $id     = Service::getParam($this, 'id', 0);
        $from   = Service::getParam($this, 'from', 0);
        if (empty($id)) {
            return $this->jumpTo404(__('Invalid topic ID!'));
        }
        
        $this->getModel('topic')->setActiveStatus($id, $status);
        
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-topic')
            );
        }
    }
    
    /**
     * Saving image by AJAX, but do not save data into database.
     * If the image is fetched by upload, try to receive image by Upload class,
     * if it comes from media, try to copy the image from media to topic path.
     * Finally the image data will be saved into session.
     * 
     */
    public function saveImageAction()
    {
        Pi::service('log')->active(false);
        $module  = $this->getModule();

        $return  = array('status' => false);
        $mediaId = Service::getParam($this, 'media_id', 0);
        $id      = Service::getParam($this, 'id', 0);
        if (empty($id)) {
            $id = Service::getParam($this, 'fake_id', 0);
        }
        // Checking is ID exists
        if (empty($id)) {
            $return['message'] = __('Invalid ID!');
            echo json_encode($return);
            exit;
        }
        
        $extensions = array_filter(
            explode(',', $this->config('image_extension')));
        foreach ($extensions as &$ext) {
            $ext = strtolower(trim($ext));
        }
        
        // Get distination path
        $destination = Upload::getTargetDir('topic', $module, true, false);

        if ($mediaId) {
            $rowMedia = $this->getModel('media')->find($mediaId);
            // Checking is media exists
            if (!$rowMedia->id or !$rowMedia->url) {
                $return['message'] = __('Media is not exists!');
                echo json_encode($return);
                exit;
            }
            // Checking is media an image
            if (!in_array(strtolower($rowMedia->type), $extensions)) {
                $return['message'] = __('Invalid file extension!');
                echo json_encode($return);
                exit;
            }
            
            $ext = strtolower(pathinfo($rowMedia->url, PATHINFO_EXTENSION));
            $rename      = $id . '.' . $ext;
            $fileName    = rtrim($destination, '/') . '/' . $rename;
            if (!copy(Pi::path($rowMedia->url), Pi::path($fileName))) {
                $return['message'] = __('Can not create image file!');
                echo json_encode($return);
                exit;
            }
        } else {
            $rawInfo = $this->request->getFiles('upload');

            $ext     = pathinfo($rawInfo['name'], PATHINFO_EXTENSION);
            $rename  = $id . '.' . $ext;

            $upload = new UploadHandler;
            $upload->setDestination(Pi::path($destination))
                   ->setRename($rename)
                   ->setExtension($this->config('image_extension'))
                   ->setSize($this->config('max_image_size'));

            // Checking is uploaded file valid
            if (!$upload->isValid()) {
                $return['message'] = $upload->getMessages();
                echo json_encode($return);
                exit;
            }
            
            $upload->receive();
            $fileName = $destination . '/' . $rename;
        }

        // Scale image
        $uploadInfo['tmp_name'] = $fileName;
        $uploadInfo['w']        = $this->config('topic_width');
        $uploadInfo['h']        = $this->config('topic_height');

        Upload::saveImage($uploadInfo);

        // Save image to topic
        $rowTopic = $this->getModel('topic')->find($id);
        if ($rowTopic) {
            if ($rowTopic->image && $rowTopic->image != $fileName) {
                @unlink(Pi::path($rowTopic->image));
            }

            $rowTopic->image = $fileName;
            $rowTopic->save();
        } else {
            // Or save info to session
            $session = Upload::getUploadSession($module, 'topic');
            $session->$id = $uploadInfo;
        }

        $imageSize = getimagesize(Pi::path($fileName));
        $originalName = isset($rawInfo['name']) ? $rawInfo['name'] : $rename;

        // Prepare return data
        $return['data'] = array(
            'originalName' => $originalName,
            'size'         => filesize(Pi::path($fileName)),
            'w'            => $imageSize['0'],
            'h'            => $imageSize['1'],
            'preview_url'  => Pi::url($fileName),
            'filename'     => $fileName,
        );

        $return['status'] = true;
        echo json_encode($return);
        exit();
    }
    
    /**
     * Removing image by AJAX.
     * This operation will also remove image data in database.
     * 
     * @return ViewModel 
     */
    public function removeImageAction()
    {
        Pi::service('log')->active(false);
        $id           = Service::getParam($this, 'id', 0);
        $fakeId       = Service::getParam($this, 'fake_id', 0);
        $affectedRows = 0;
        $module       = $this->getModule();

        if ($id) {
            $rowTopic = $this->getModel('topic')->find($id);

            if ($rowTopic && $rowTopic->image) {
                // Delete image
                @unlink(Pi::path($rowTopic->image));

                // Update db
                $rowTopic->image = '';
                $affectedRows    = $rowTopic->save();
            }
        } else if ($fakeId) {
            $session = Upload::getUploadSession($module, 'topic');

            if (isset($session->$fakeId)) {
                $uploadInfo = isset($session->$id) 
                    ? $session->$id : $session->$fakeId;

                @unlink(Pi::path($uploadInfo['tmp_name']));

                unset($session->$id);
                unset($session->$fakeId);
            }
        }

        return array(
            'status'    => $affectedRows ? true : false,
            'message'   => 'ok',
        );
    }
}
