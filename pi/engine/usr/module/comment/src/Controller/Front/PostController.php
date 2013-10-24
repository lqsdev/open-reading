<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Controller\Front;

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
     * Comment post view
     *
     * @return string
     */
    public function indexAction()
    {
        $currentUser    = Pi::service('user')->getUser();
        $currentUid     = $currentUser->get('id');

        $id             = _get('id', 'int') ?: 1;
        $post           = Pi::api('comment')->getPost($id);
        $target         = array();

        if ($post) {
            $post['content'] = Pi::api('comment')->renderPost($post);
            $target = Pi::api('comment')->getTarget($post['root']);
            $user = Pi::service('user')->get($post['uid'], array('name'));
            $user['url'] =  Pi::service('user')->getUrl('profile', $post['uid']);
            $user['avatar'] = Pi::service('avatar')->get($post['uid']);
            $post['user'] = $user;
            $active = $post['active'];

            // User operations
            $operations = array(
                'permanent' => array(
                    'title' => __('Permanent link'),
                    'url'   => Pi::api('comment')->getUrl('post', array(
                        'post'  => $id,
                    )),
                ),
            );
            if ($currentUid) {
                $operations['reply'] = array(
                    'title' => __('Reply'),
                    'url'   => Pi::api('comment')->getUrl('reply', array(
                        'post'  => $id,
                    )),
                );
            }
            // Author
            if ($post['uid'] == $currentUid) {
                $operations = array_merge($operations, array(
                    'edit'  => array(
                        'title' => __('Edit'),
                        'url'   => Pi::api('comment')->getUrl('edit', array(
                            'post'  => $id,
                        )),
                    ),
                    'delete'  => array(
                        'title' => __('Delete'),
                        'url'   => Pi::api('comment')->getUrl('delete', array(
                            'post'  => $id,
                        )),
                    ),
                ));
            }
            // Admin
            if ($currentUser->isAdmin()) {
                $operations = array_merge($operations, array(
                    'edit'  => array(
                        'title' => __('Edit'),
                        'url'   => Pi::api('comment')->getUrl('edit', array(
                            'post'  => $id,
                        )),
                    ),
                    'delete'  => array(
                        'title' => __('Delete'),
                        'url'   => Pi::api('comment')->getUrl('delete', array(
                            'post'  => $id,
                        )),
                    ),
                    'approve'  => array(
                        'title' => $active ? __('Disable') : __('Enable'),
                        'url'   => Pi::api('comment')->getUrl('approve', array(
                            'post'  => $id,
                            'flag'  => $active ? 0 : 1,
                        )),
                    ),
                ));
            }
            $post['operations'] = $operations;
        }
        $title = __('Comment post');
        $this->view()->assign('comment', array(
            'title'     => $title,
            'post'      => $post,
            'target'    => $target,
        ));
        $this->view()->setTemplate('comment-view');
    }

    /**
     * Edit a comment post
     */
    public function editAction()
    {
        $currentUser    = Pi::service('user')->getUser();
        $currentUid     = $currentUser->get('id');

        $id             = _get('id', 'int') ?: 1;
        $redirect       = _get('redirect');

        //$status     = 1;
        $message    = '';
        $target     = array();
        $post = Pi::api('comment')->getPost($id);
        // Verify post
        if (!$post) {
            //$status = -1;
            $message = __('Invalid post parameter.');
        // Verify author
        } elseif (!$currentUid
            || ($post['uid'] != $currentUid && !$currentUser->isAdmin('comment'))
        ) {
            //$status = 0;
            $message = __('Operation denied.');
            $post = array();
        } else {
            $target = Pi::api('comment')->getTarget($post['root']);
            $user = array(
                'uid'       => $currentUid,
                'name'      => $currentUser->get('name'),
                'avatar'    => Pi::service('avatar')->get($currentUid),
            );
            $post['user'] = $user;
        }

        $title = __('Comment post edit');
        $this->view()->assign('comment', array(
            'title'     => $title,
            'post'      => $post,
            'target'    => $target,
            'message'   => $message,
        ));

        $data = array_merge($post, array(
            'redirect' => $redirect,
        ));
        $form = Pi::api('comment')->getForm($data);

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('comment-edit');
    }

    /**
     * Reply a comment post
     */
    public function replyAction()
    {
        $currentUser    = Pi::service('user')->getUser();
        $currentUid     = $currentUser->get('id');

        $id             = _get('id', 'int') ?: 1;
        $redirect       = _get('redirect');

        //$status     = 1;
        $message    = '';
        $target     = array();
        $post = Pi::api('comment')->getPost($id);
        // Verify post
        if (!$post) {
            //$status = -1;
            $message = __('Invalid post parameter.');
        // Verify authentication
        } elseif (!$currentUid) {
            //$status = 0;
            $message = __('Operation denied.');
            $post = array();
        } else {
            $target = Pi::api('comment')->getTarget($post['root']);
            $post['content'] = Pi::api('comment')->renderPost($post);
            $user = array(
                'uid'       => $currentUid,
                'name'      => $currentUser->get('name'),
                'avatar'    => Pi::service('avatar')->get($currentUid),
            );
            $post['user'] = $user;
        }

        $title = __('Comment post reply');
        $this->view()->assign('comment', array(
            'title'     => $title,
            'post'      => $post,
            'target'    => $target,
            'message'   => $message,
        ));

        $data = array_merge($post, array(
            'redirect'  => $redirect,
            'root'      => $post['root'],
            'reply'     => $id,
            'id'        => '',
            'content'   => '',
        ));
        $form = Pi::api('comment')->getForm($data);

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('comment-reply');
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
                $redirect = Pi::api('comment')->getUrl('post', array(
                    'post' => $result['data']
                ));
            } else {
                $redirect = Pi::service('url')->assemble('comment');
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
        $currentUser    = Pi::service('user')->getUser();
        $currentUid     = $currentUser->get('id');

        $id             = 0;
        $status         = 1;

        if (!$currentUid) {
            $status = -1;
            $message = __('Operation denied.');
        } elseif (!$this->request->isPost()) {
            $status = -2;
            $message = __('Invalid submission.');
        } else {
            $data = $this->request->getPost();
            $markup = $data['markup'];
            $form = new PostForm('comment-post', $markup);
            $form->setInputFilter(new PostFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                if (!empty($values['root'])) {
                    $root = Pi::model('root', 'comment')->find($values['root']);
                    if (!$root) {
                        $status = -1;
                        $message = __('Root not found.');
                    } elseif (!$root['active']) {
                        $status = -1;
                        $message = __('Comment is disabled.');
                    }
                }
                if (0 < $status) {
                    // For new post
                    if (empty($values['id'])) {
                        if ($this->config('auto_approve')) {
                            $values['active'] = 1;
                        } else {
                            $values['active'] = 0;
                        }
                        $values['uid'] = $currentUid;
                        $values['ip'] = Pi::service('user')->getIp();
                    } else {
                        $post = Pi::api('comment')->getPost($values['id']);
                        if (!$post) {
                            $status = -2;
                            $message = __('Invalid post parameter.');
                        } elseif ($currentUid != $post['uid']
                            && !$currentUser->isAdmin('comment')
                        ) {
                            $status = -1;
                            $message = __('Operation denied.');
                        }
                    }
                }
                if (0 < $status) {
                    //vd($values);
                    $id = Pi::api('comment')->addPost($values);
                    if ($id) {
                        $status = 1;
                        $message = __('Comment post saved successfully.');
                    } else {
                        $status = 0;
                        $message = __('Comment post not saved.');
                    }
                }
            } else {
                $status = -1;
                $message = __('Invalid data, please check and re-submit.');
            }
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
        $currentUser    = Pi::service('user')->getUser();
        //$currentUid     = $currentUser->get('id');

        $id = _get('id', 'int');
        $flag = _get('flag');
        $return = _get('return');
        $redirect = _get('redirect');

        if (!$currentUser->isAdmin('comment')) {
            $status     = -1;
            $message    = __('Operation denied.');
        } else {
            if (null === $flag) {
                $status     = Pi::api('comment')->approve($id);
            } else {
                $status = Pi::api('comment')->approve($id, $flag);
            }
            $message = $status
                ? __('Operation succeeded.') : __('Operation failed');
        }

        if (!$return) {
            if ($redirect) {
                $redirect = urldecode($redirect);
            } else {
                $redirect = Pi::api('comment')->getUrl('post', array(
                    'post' => $id
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
     * Delete a comment post
     *
     * @return array
     */
    public function deleteAction()
    {
        $currentUser    = Pi::service('user')->getUser();
        $currentUid     = $currentUser->get('id');

        $id             = _get('id', 'int');
        $return         = _get('return');
        $redirect       = _get('redirect');

        $post           = Pi::api('comment')->getPost($id);
        if (!$post) {
            $status = -2;
            $message = __('Invalid post parameter.');
        } elseif ($currentUid != $post['uid']
            && !$currentUser->isAdmin('comment')
        ) {
            $status = -1;
            $message = __('Operation denied.');
        } else {
            $status         = Pi::api('comment')->deletePost($id);
            $message        = $status
                ? __('Operation succeeded.') : __('Operation failed');
        }

        if (!$return) {
            if ($redirect) {
                $redirect = urldecode($redirect);
            } else {
                $redirect = Pi::api('comment')->getUrl('list');
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
     * Get privileged operation list on a post
     *
     * @return array
     */
    public function operationAction()
    {
        $id     = _get('id', 'int');
        $uid    = _get('uid', 'int');

        $status = 1;
        $message = '';
        $operations = array();

        $postRow = null;
        if (!$uid && $id) {
            $postRow = Pi::model('post', 'comment')->find($id);
            if ($postRow) {
                $uid = (int) $postRow['uid'];
            }
        }
        if (!$id || !$uid) {
            $status = -1;
            $message = __('Invalid parameters.');
        } else {
            $currentUser    = Pi::service('user')->getUser();
            $currentUid     = $currentUser->get('id');
            $ops = array(
                'login' => array(
                    'title' => __('Login'),
                    'url'   => Pi::service('user')->getUrl('login'),
                ),
                'edit' => array(
                    'title' => __('Edit'),
                    'url'   => Pi::api('comment')->getUrl(
                        'edit',
                        array('post' => $id)
                    ),
                ),
                'delete' => array(
                    'title' => __('Delete'),
                    'url'   => Pi::api('comment')->getUrl(
                        'delete',
                        array('post' => $id)
                    ),
                ),
                'reply' => array(
                    'title' => __('Reply'),
                    'url'   => Pi::api('comment')->getUrl(
                        'reply',
                        array('post' => $id)
                    ),
                ),
                'approve' => array(
                    'title' => __('Enable/Disable'),
                    'url'   => Pi::api('comment')->getUrl(
                        'approve',
                        array(
                            'post'  => $id,
                            'flag'  => !(int) $postRow['active'],
                        )
                    ),
                ),
            );

            if (!$currentUid) {
                $operations = $ops['login'];
            } elseif ($currentUser->isAdmin('comment')) {
                $operations = $ops;
                unset($operations['login']);
            } elseif ($uid == $currentUid) {
                $operations = $ops;
                unset($operations['login'], $operations['approve']);
            } elseif ($uid != $currentUid) {
                $operations = $ops['reply'];
            }
        }
        $result = array(
            'status'    => $status,
            'message'   => $message,
            'data'      => $operations,
        );

        return $result;
    }
}
