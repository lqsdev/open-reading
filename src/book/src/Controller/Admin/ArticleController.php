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
    protected function submit($id, $articleId)
    {
        // Move asset
        $modelDraftAsset     = $this->getModel('asset_draft');
        $resultsetDraftAsset = $modelDraftAsset->select(array(
            'draft' => $id,
        ));
        $modelAsset = $this->getModel('asset');
        foreach ($resultsetDraftAsset as $asset) {
            $data = array(
                'media'         => $asset->media,
                'type'          => $asset->type,
                'article'       => $articleId,
            );
            $rowAsset = $modelAsset->createRow($data);
            $rowAsset->save();
        }

        // Clear draft assets info
        $modelDraftAsset->delete(array('draft' => $id));
    }
    
    public function editAction()
    {
        $form = new ArticleForm('article');        
        $form->setAttribute('action', $this->url('', array('action' => 'edit')));
        $this->view()->assign('form', $form);
        
        $configs = Pi::service('module')->config('', $this->getModule());
        $configs['max_media_size'] = Media::transferSize($configs['max_media_size']);
        $configs['max_image_size'] = Media::transferSize($configs['max_image_size']);
        $this->view()->assign('configs', $configs);
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);            
            $form->setInputFilter(new ArticleFilter);
            if (!$form->isValid()) {
                $this->view()->assign('form', $form);
                return;
            }
            $data = $form->getData();
            $inputData = array(
                'title'   => $data['title'],
                'content' => $data['content'],
                );
            $row = $this->getModel('article')->createRow($inputData);
            $row->save();
            
            $this->submit($data['fake_id'], $row->id);
        }
        $this->view()->setTemplate('article-edit');
    }
    
    public function saveImageAction()
    {
        Pi::service('log')->active(false);
        $module  = $this->getModule();

        $return  = array('status' => false);
        $mediaId = Service::getParam($this, 'media_id', 0);
        $id      = Service::getParam($this, 'id', 0);
        $fakeId  = Service::getParam($this, 'fake_id', 0);
        // Checking is id valid
        if (empty($fakeId)) {
            $return['message'] = __('Invalid ID!');
            echo json_encode($return);
            exit;
        }
        $rename  = $fakeId;
        
        $extensions = array_filter(explode(',', $this->config('image_extension')));
        foreach ($extensions as &$ext) {
            $ext = strtolower(trim($ext));
        }
        
        // Get distination path
        $destination = Service::getTargetDir('feature', $module, true);
        
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
            $rename     .= '.' . $ext;
            $fileName    = rtrim($destination, '/') . '/' . $rename;
            if (!copy(Pi::path($rowMedia->url), Pi::path($fileName))) {
                $return['message'] = __('Can not create image file!');
                echo json_encode($return);
                exit;
            }
        } else {
            $rawInfo = $this->request->getFiles('upload');
            
            $ext     = pathinfo($rawInfo['name'], PATHINFO_EXTENSION);
            $rename .= '.' . $ext;
            
            $upload      = new UploadHandler;
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
        $uploadInfo['w']        = $this->config('feature_width');
        $uploadInfo['h']        = $this->config('feature_height');
        $uploadInfo['thumb_w']  = $this->config('feature_thumb_width');
        $uploadInfo['thumb_h']  = $this->config('feature_thumb_height');

        Service::saveImage($uploadInfo);

        // Save image to draft
        $rowArticle = $this->getModel('article')->find($id);
        if ($rowArticle) {
            $rowArticle->image = $fileName;
            $rowArticle->save();
        } else {
            // Or save info to session
            $session = Service::getUploadSession($module);
            $session->$id = $uploadInfo;
        }

        $imageSize    = getimagesize(Pi::path($fileName));
        $originalName = isset($rawInfo['name']) ? $rawInfo['name'] : $rename;

        // Prepare return data
        $return['data'] = array(
            'originalName' => $originalName,
            'size'         => filesize(Pi::path($fileName)),
            'w'            => $imageSize['0'],
            'h'            => $imageSize['1'],
            'preview_url'  => Pi::url(Service::getThumbFromOriginal($fileName)),
        );

        $return['status'] = true;
        echo json_encode($return);
        exit();
    }

    public function removeImageAction()
    {
        Pi::service('log')->active(false);
        $id           = Service::getParam($this, 'id', 0);
        $fakeId       = Service::getParam($this, 'fake_id', 0);
        $affectedRows = 0;
        $module       = $this->getModule();

        if ($id) {
            $rowDraft = $this->getModel('draft')->find($id);

            if ($rowDraft && $rowDraft->image) {

                $thumbUrl = Service::getThumbFromOriginal($rowDraft->image);
                if ($rowDraft->article) {
                    $modelArticle = $this->getModel('article');
                    $rowArticle   = $modelArticle->find($rowDraft->article);
                    if ($rowArticle && $rowArticle->image != $rowDraft->image) {
                        // Delete file
                        @unlink(Pi::path($rowDraft->image));
                        @unlink(Pi::path($thumbUrl));
                    }
                } else {
                    @unlink(Pi::path($rowDraft->image));
                    @unlink(Pi::path($thumbUrl));
                }

                // Update db
                $rowDraft->image = '';
                $affectedRows    = $rowDraft->save();
            }
        } else if ($fakeId) {
            $session = Service::getUploadSession($module, 'feature');

            if (isset($session->$fakeId)) {
                $uploadInfo = $session->$fakeId;

                $url = Service::getThumbFromOriginal($uploadInfo['tmp_name']);
                $affectedRows = unlink(Pi::path($uploadInfo['tmp_name']));
                @unlink(Pi::path($url));

                unset($session->$id);
                unset($session->$fakeId);
            }
        }

        return array(
            'status'    => $affectedRows ? true : false,
            'message'   => 'ok',
        );
    }
    
    /**
     * Save asset
     */
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
        
        // Saving asset data
        $model     = $this->getModel('asset_draft');
        $rowAssets = $model->select(array(
            'draft' => $draftId,
            'media' => $mediaId
        ));
        if ($rowAssets->count() == 0) {
            $data     = array(
                'draft'   => $draftId,
                'media'   => $mediaId,
                'type'    => $type,
            );
            $row = $model->createRow($data);
            $row->save();
            
            if (!$row->id) {
                $return['message'] = __('Can not save data!');
                echo json_encode($return);
                exit();
            }
        }
        
        $return['data']    = array(
            'media'       => $mediaId,
            'id'          => $row->id,
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
    
    /**
     * Delete asset
     */
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
        
        $result = $this->getModel('asset_draft')->delete(array('id' => $id));
        if ($result) {
            $return['message'] = __('Delete item sucessful!');
            $return['status']  = true;
        } else {
            $return['message'] = __('Can not delete item!');
        }
        
        echo json_encode($return);
        exit();
    }
}
