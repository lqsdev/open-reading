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

/**
 * Article controller
 */
class ArticleController extends ActionController
{
    public function editAction()
    {
        $form = new ArticleForm('article');        
        $form->setAttribute('action', $this->url('', array('action' => 'edit')));
        $this->view()->assign('form', $form);                
        
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
}
