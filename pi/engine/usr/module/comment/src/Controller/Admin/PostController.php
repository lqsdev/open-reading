<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Comment\Form\PostForm;
use Module\Comment\Form\PostFilter;

/**
 * Comment post controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PostController extends ActionController
{
    /**
     * Comment post
     *
     * @return string
     */
    public function indexAction()
    {
        $id = _get('id', 'int') ?: 1;
        $post = Pi::api('comment')->getPost($id);
        $target = array();
        if ($post) {
            $post['content'] = Pi::api('comment')->renderPost($post);
            $target = Pi::api('comment')->getTarget($post['root']);
            $user = Pi::service('user')->get($post['uid'], array('name'));
            $user['url'] =  Pi::service('user')->getUrl('profile', $post['uid']);
            $user['avatar'] = Pi::service('avatar')->get($post['uid']);
            $post['user'] = $user;
            $active = $post['active'];
            $post['operations'] = array(
                'edit'  => array(
                    'title' => __('Edit'),
                    'url'   => $this->url('', array(
                        'action'        => 'edit',
                        'id'            => $id,
                    )),
                ),
                'delete'  => array(
                    'title' => __('Delete'),
                    'url'   => $this->url('', array(
                        'action'        => 'delete',
                        'id'            => $id,
                    )),
                ),
                'approve'  => array(
                    'title' => $active ? __('Disable') : __('Enable'),
                    'url'   => $this->url('', array(
                        'action'        => 'approve',
                        'id'            => $id,
                        'flag'          => $active ? 0 : 1,
                    )),
                ),
            );
        }
        $title = __('Comment post');
        $this->view()->assign('comment', array(
            'title'     => $title,
            'post'      => $post,
            'target'    => $target,
        ));
        $this->view()->setTemplate('comment-view', '', 'front');
    }

    public function editAction()
    {
        $id = _get('id', 'int') ?: 1;
        $redirect = _get('redirect');

        $post = Pi::api('comment')->getPost($id);
        $target = array();
        if ($post) {
            $target = Pi::api('comment')->getTarget($post['root']);
            $user = Pi::service('user')->get($post['uid'], array('name'));
            $user['url'] =  Pi::service('user')->getUrl('profile', $post['uid']);
            $user['avatar'] = Pi::service('avatar')->get($post['uid']);
            $post['user'] = $user;
        }

        $title = __('Comment post edit');
        $this->view()->assign('comment', array(
            'title'     => $title,
            'post'      => $post,
            'target'    => $target,
        ));

        $data = array_merge($post, array(
            'redirect' => $redirect,
        ));
        $form = Pi::api('comment')->getForm($data);
        $form->setAttribute('action', $this->url('', array(
            'action'    => 'submit',
        )));

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('comment-edit', '', 'front');
    }

    /**
     * Action for comment post submission
     */
    public function submitAction()
    {
        $result = $this->processPost();
        $redirect = '';
        if ($this->request->isPost()) {
            $return = (bool) $this->request->getPost('return');
            if (!$return) {
                $redirect = $this->request->getPost('redirect');
            }
        } else {
            $return = (bool) $this->params('return');
            if (!$return) {
                $redirect = $this->params('redirect');
            }
        }

        if (!$return) {
            if ($redirect) {
                $redirect = urldecode($redirect);
            } elseif (!empty($result['data'])) {
                $redirect = $this->url('', array(
                    'action'    => 'index',
                    'id'        => $result['data']
                ));
            } else {
                $redirect = $this->url('', array('controller' => 'list'));
            }
            $this->jump($redirect, $result['message']);
        } else {
            return $result;
        }
    }

    /**
     * Process comment post submission
     *
     * @return array
     */
    protected function processPost()
    {
        $id = 0;
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $markup = $data['markup'];
            $form = new PostForm('comment-post', $markup);
            $form->setInputFilter(new PostFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                if (empty($values['id'])) {
                    if (Pi::config('auto_approve', 'comment')) {
                        $values['active'] = 1;
                    }
                    $values['uid'] = Pi::service('user')->getId();
                    $values['ip'] = Pi::service('user')->getIp();
                }
                //vd($values);
                $id = Pi::api('comment')->addPost($values);
                if ($id) {
                    $status = 1;
                    $message = __('Comment post saved successfully.');
                } else {
                    $status = 0;
                    $message = __('Comment post not saved.');
                }
            } else {
                $status = -1;
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $status = -2;
            $message = __('Invalid submission.');
        }

        $result = array(
            'data'      => $id,
            'status'    => $status,
            'message'   => $message,
        );

        return $result;
    }

    /**
     * Approve/disapprove a post
     *
     * @return bool
     */
    public function approveAction()
    {
        $id         = _get('id', 'int');
        $flag       = _get('flag');
        $return     = _get('return');
        $redirect   = _get('redirect');

        if (null === $flag) {
            $status     = Pi::api('comment')->approve($id);
        } else {
            $status = Pi::api('comment')->approve($id, $flag);
        }
        $message = $status
            ? __('Operation succeeded.') : __('Operation failed');

        if (!$return) {
            if ($redirect) {
                $redirect = urldecode($redirect);
            } else {
                $redirect = $this->url('', array(
                    'action'    => 'index',
                    'id'        => $id,
                ));
            }
            $this->jump($redirect, $message);
        } else {
            $result = array(
                'status'    => (int) $status,
                'message'   => $message,
                'data'      => $id,
            );

            return $result;
        }
    }
    
    /**
     * Batch enable or disable comment
     * 
     * @return viewModel 
     */
    public function batchApproveAction()
    {
        $id       = $this->params('id', '');
        $ids      = array_filter(explode(',', $id));
        $flag     = $this->params('flag', 0);
        $redirect = $this->params('redirect', '');

        $model  = $this->getModel('post');
        $model->update(array('active' => $flag), array('id' => $ids));
        
        if ($redirect) {
            $redirect = urldecode($redirect);
            return $this->redirect()->toUrl($redirect);
        } else {
            // Go to list page
            return $this->redirect()->toRoute('', array(
                'action'     => 'index',
            ));
        }
    }

    /**
     * Delete a comment post
     *
     * @return array
     */
    public function deleteAction()
    {
        $id = _get('id', 'int');
        $return = _get('return');
        $redirect = _get('redirect');

        $status     = Pi::api('comment')->deletePost($id);
        $message = $status
            ? __('Operation succeeded.') : __('Operation failed');

        if (!$return) {
            if ($redirect) {
                $redirect = urldecode($redirect);
            } else {
                $redirect = $this->url('', array('controller' => 'list'));
            }
            $this->jump($redirect, $message);
        } else {
            $result = array(
                'status'    => (int) $status,
                'message'   => $message,
                'data'      => $id,
            );

            return $result;
        }
    }
    
    /**
     * Batch delete comments
     * 
     * @return viewModel 
     */
    public function batchDeleteAction()
    {
        $id       = $this->params('id', '');
        $ids      = array_filter(explode(',', $id));
        $redirect = $this->params('redirect', '');

        $model  = $this->getModel('post');
        $model->delete(array('id' => $ids));
        
        if ($redirect) {
            $redirect = urldecode($redirect);
            return $this->redirect()->toUrl($redirect);
        } else {
            // Go to list page
            return $this->redirect()->toRoute('', array(
                'action'     => 'index',
            ));
        }
    }
}
