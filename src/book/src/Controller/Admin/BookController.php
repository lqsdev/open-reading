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
use Module\Book\Form\BookFilter;
use Module\Book\Form\BookForm;
use Pi;
use Module\Book\Service;
/**
 * Book controller
 */
class BookController extends ActionController
{
    public function listAction()
    {
        $model = $this->getModel('book');
        $select = $model->select();
        $select->order(array('id'));
        $books = $model->selectWith($select);        

        $this->view()->assign('books', $books);
        $this->view()->setTemplate('book-list');
    }
    
    public function editAction()
    {
        $form = new BookForm('book');
        $form->setAttribute('action', $this->url('', array('action' => 'edit')));

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);            
            $form->setInputFilter(new BookFilter);
            if (!$form->isValid()) {
                $this->view()->assign('form', $form);
                return;
            }
            $data = $form->getData();                     
            $inputData = array(
                'title' => $data['title'],
                'introduction' => $data['introduction'],
            );
            if (empty($data['id'])) {
                $row = $this->getModel('book')->createRow($inputData);
                $row->save();
                $row->catalogue_id = $row->id;
                $id = $row->id;
                $row->save();
            } else {
                $row = $this->getModel('book')->find($data['id']);
                $row->title = $data['title'];
                $row->introduction = $data['introduction'];
                $row->save();
            }
            $featureImage = $row->cover_url ? Pi::url($row->cover_url) : '';
            $featureThumb = $row->cover_url ? Pi::url(Service::getThumbFromOriginal($row->cover_url)) : '';
        } else {
            $id = $this->params('id');
            if (!empty($id)) {
                $row = $this->getModel('book')->find($id);
                $featureImage = $row->cover_url ? Pi::url($row->cover_url) : '';
                $featureThumb = $row->cover_url ? Pi::url(Service::getThumbFromOriginal($row->cover_url)) : '';
                $form->setData(array(
                    'id' => $row->id,
                    'title' => $row->title,
                    'introduction' => $row->introduction,
                ));
            }
        }
        $this->view()->assign('form', $form);
        $this->view()->assign('featureImage', $featureImage);
        $this->view()->assign('featureThumb', $featureThumb);
        $this->view()->setTemplate('book-edit');
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
        $rowArticle = $this->getModel('book')->find($id);

        if ($rowArticle) {
            $rowArticle->cover_url = $fileName;
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
            $rowDraft = $this->getModel('book')->find($id);

            if ($rowDraft && $rowDraft->cover_url) {
                $thumbUrl = Service::getThumbFromOriginal($rowDraft->cover_url);
                @unlink(Pi::path($rowDraft->cover_url));
                @unlink(Pi::path($thumbUrl));
                // Update db
                $rowDraft->cover_url = '';
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
}
