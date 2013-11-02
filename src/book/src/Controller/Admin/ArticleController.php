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
use Module\Book\Form\ArticleFilter;
use Module\Book\Form\ArticleForm;
use Pi;
use Module\Book\Service;
use Module\Book\Media;

/**
 * Article controller
 */
class ArticleController extends ActionController
{
    public function listAction()
    {
        $model = $this->getModel('article');
        $select = $model->select();
        $select->order(array('id'));
        $articles = $model->selectWith($select);
    }
    
    public function editAction()
    {
        $id = $this->params('id');

        // 读取config信息
        $configs = $this->getModuleConfig();
        $this->view()->assign('configs', $configs);
        
        // 读取文件信息
        $form = new ArticleForm('article');        
        $form->setAttribute('action', $this->url('', array('action' => 'save')));
        $row = $this->getModel('article')->find($id);
        $form->setData(
                array(
                         'id' => $id,
                        'bid' => $this->params('bid'),
                       'cdid' => $this->params('cdid'),
                      'title' => $row['title'],
                    'content' => $row['content'],
        ));
        $this->view()->assign('form', $form);

        // 读取图片信息
        $images = $this->getArticleImages($id);
        $this->view()->assign('images', $images);

        $this->view()->setTemplate('article-edit');
    }

    public function saveAction()
    {
        if (!$this->request->isPost())
            exit;

        // 校验、获取post数据
        $post = $this->request->getPost();
        $form = new ArticleForm('article');        
        $form->setData($post);
        $form->setInputFilter(new ArticleFilter);
        if (!$form->isValid()) {
            $form->setAttribute('action', $this->url('', array('action' => 'save')));
            $this->view()->assign('form', $form);
            $configs = $this->getModuleConfig();
            $this->view()->assign('configs', $configs);
            $this->view()->setTemplate('article-edit');
            return;
        }
        
        $data = $form->getData();
        $rowId = $this->saveArticles($data);

        $this->submit($rowId, $data['fake_id']);
        return $this->redirect()->toRoute('', array(
                    'action' => 'edit',
                        'id' => $rowId,
                       'bid' => $data['cid'],
                      'cdid' => $data['cdid'],
                       'save' => 'success'
        ));
    } 

    public function saveAssetAction()
    {
        Pi::service('log')->active(false);
        
        $type    = Service::getParam($this, 'type', 'attachment');
        $mediaId = Service::getParam($this, 'media', 0);
        $draftId = Service::getParam($this, 'id', 0);
        if (empty($draftId)) {
            $draftId = Service::getParam($this, 'fake_id', 0);
        }
        
        $return = array('status' => false);
        // Checking if is draft exists
        if (empty($draftId)) {
            $return['message'] = __('Invalid draft ID!');
            echo json_encode($return);
            exit();
        }
        
        // Checking if is media exists
        $rowMedia = $this->getModel('media')->find($mediaId);
        if (empty($rowMedia)) {
            $return['message'] = __('Invalid media!');
            echo json_encode($return);
            exit();
        }
        
        $fakeId  = Service::getParam($this, 'fake_id', 0);
        $module  = $this->getModule();
        $session = Service::getUploadSession($module, 'asset');
        $media = isset($session->$fakeId) ? $session->$fakeId : array();
        $media[$mediaId] = 'save';
        $session->$fakeId = $media;
        
        $return['data']    = array(
            'media'       => $mediaId,
            'id'          => $mediaId,
            'title'       => $rowMedia->title,
            'size'        => $rowMedia->size,
            'downloadUrl' => $this->url('admin', array(
                'controller' => 'media',
                'action'     => 'download',
                'id'         => $mediaId,
            )),
        );
        // Generating a preview url for image file
        if ('image' == $type) {
            $return['data']['preview_url'] = Pi::url($rowMedia->url);
        }
        $return['status']  = true;
        $return['message'] = __('Save asset successful!');
        echo json_encode($return);
        exit();
    }
    
    public function removeAssetAction()
    {
        Pi::service('log')->active(false);
        
        $return = array('status' => false);
        $id = Service::getParam($this, 'id', 0);
        if (empty($id)) {
            $return['message'] = __('Invalid ID!');
            echo json_encode($return);
            exit();
        }

        $fakeId  = Service::getParam($this, 'fake_id', 0);
        $module  = $this->getModule();
        $session = Service::getUploadSession($module, 'asset');
        $media = isset($session->$fakeId) ? $session->$fakeId : array();
        $media[$id] = 'remove';
        $session->$fakeId = $media;

        $return['message'] = __('Delete item sucessful!');
        $return['status']  = true;

        echo json_encode($return);
        exit();
    }
    
    protected function submit($articleId, $fakeId)
    {
        $module  = $this->getModule();
        $session = Service::getUploadSession($module, 'asset');
        if (!isset($session->$fakeId)) {
            return;
        }
        
        $modelAsset = $this->getModel('asset');
        foreach ($session->$fakeId as $media => $status) {
            var_dump($media);
            $modelAsset->delete(array('article' => $articleId, 'media' => $media));
            if ($status == 'save') {
                $data = array(
                    'article' => $articleId,
                    'media'   => $media,
                    'type'    => 'media',
                );
                $rowAsset = $modelAsset->createRow($data);
                $rowAsset->save();
            }
        }
    }
    
    protected function getArticleImages($id)
    {
        // Getting media IDs
        $mediaIds = array(0);
        $resultsetDraftAsset = $this->getModel('asset')
                ->select(array('article' => $id))->toArray();
        foreach ($resultsetDraftAsset as $asset) {
            $mediaIds[] = $asset['media'];
        }

        // Getting media details
        $medias = array();
        $resultsetMedia = $this->getModel('media')->select(array('id' => $mediaIds));
        foreach ($resultsetMedia as $media) {
            $medias[$media->id] = $media->toArray();
        }
        
        // Getting assets
        $images = array();
        foreach ($resultsetDraftAsset as $asset) {
            $media = $medias[$asset['media']];
            $imageSize = getimagesize(Pi::path($media['url']));
            $images[] = array(
                'id'    => $media['id'],
                'title' => $media['title'],
                'size'  => $media['size'],
                'w'     => $imageSize['0'],
                'h'     => $imageSize['1'],
                'downloadUrl' => $this->url('',
                        array(
                            'controller' => 'media',
                            'action'     => 'download',
                            'id'         => $media['id'],
                        )
                    ),
                'preview_url' => Pi::url($media['url']),
                'thumb_url' => Pi::url(Service::getThumbFromOriginal($media['url'])),
            );
        }

        return $images;
    }
    
    protected function saveArticles($data)
    {
        // id 为空，为新建 
        if (empty($data['id'])) {
            $row = $this->getModel('article')->createRow(
                    array(
                        'title' => $data['title'],
                        'content' => $data['content'],
                    )
            );
            $row->save();
            $catalogueRow = $this->getModel('catalogue_rel_article')
                    ->createRow( array(
                        'book_id' => $data['bid'],
                        'cata_data_id' => $data['cdid'],
                        'article_id' => $row->id,
                    )
            );
            $catalogueRow->save();
        } else {
            $row = $this->getModel('article')->find($data['id']);
            $row->assign(
                    array(
                        'title' => $data['title'],
                        'content' => $data['content'],
                    )
            );
            $row->save();
        }
        return $row->id;
    }

    protected function getModuleConfig()
    {
        $configs = Pi::service('module')->config('', $this->getModule());
        $configs['max_media_size'] = Media::transferSize($configs['max_media_size']);
        $configs['max_image_size'] = Media::transferSize($configs['max_image_size']);
        return $configs;
    }
}
