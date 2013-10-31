<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\User\Form\PasswordForm;
use Module\User\Form\PasswordFilter;
use Module\User\Form\ResetPasswordForm;
use Module\User\Form\ResetPasswordFilter;
use Module\User\Form\FindPasswordForm;
use Module\User\Form\FindPasswordFilter;

/**
 * Password controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class PasswordController extends ActionController
{
    /**
     * Change password for current user
     *
     * @return array|void
     */
    public function indexAction()
    {
        $uid = Pi::user()->getId();
        $result = array(
            'status' => 0,
            'message' => __('Reset password failed'),
        );

        // Redirect login page if not logged in
        if (!$uid) {
            $this->jump(
                array(
                    '',
                    'controller' => 'login',
                    'action'     => 'index',
                ),
                __('Change password need login'),
                3
            );
            return;
        }

        $form = new PasswordForm('password-change');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new PasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Verify password
                $row = Pi::model('user_account')->find($uid, 'id');
                $credential = md5(sprintf(
                    '%s%s%s',
                    $row->salt,
                    $values['credential'],
                    Pi::config('salt')
                ));

                if ($credential == $row->credential) {
                    // Update password
                    Pi::api('user', 'user')->updateAccount(
                        $uid,
                        array(
                            'credential' => $values['credential-new']
                        )
                    );
                    $result['status'] = 1;
                    $result['message'] = __('Reset password successfully');
                } else {
                    $result['message'] = __('Input password error');
                }
            }

            $this->view()->assign('result', $result);
        }

        // Get side nav items
        $groups = Pi::api('user', 'group')->getList();
        $user   = Pi::api('user', 'user')->get($uid, array('uid', 'name'));

        $this->view()->assign(array(
            'form'      => $form,
            'groups'    => $groups,
            'cur_group' => 'password',
            'user'      => $user,
        ));
    }

    /**
     * 1. Display find password form
     * 2. Verify email
     * 3. Send verify email
     *
     */
    public function findAction()
    {
        $result = array(
            'status'  => 0,
            'message' => __('Find password failed'),
        );
        $form = new FindPasswordForm('find-password');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new FindPasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $value = $form->getData();

                // Check email is  exist
                $userRow = $this->getModel('account')->find($value['email'], 'email');
                if (!$userRow) {
                    $this->view()->assign(array(
                        'form'   => $form,
                        'result' => $result,
                    ));

                    return;
                }

                // Set user data
                $uid = (int) $userRow->id;
                $token = md5(mt_rand() . $uid);
                Pi::user()->data()->set(
                    $uid,
                    'find-password',
                    $token
                );

                // Send verify email
                $to = $userRow->email;
                $url = $this->url('', array(
                        'action' => 'process',
                        'uid'     => md5($uid),
                        'token'  => $token
                    )
                );
                $link = Pi::url($url, true);
                list($subject, $body, $type) = $this->setMailParams(
                    $userRow->identity,
                    $link
                );
                $message = Pi::service('mail')->message($subject, $body, $type);
                $message->addTo($to);
                $transport = Pi::service('mail')->transport();
                $transport->send($message);

                $result['status'] = 1;
                $result['message'] = __('Send email successfully. check email and reset password');
            }

            $this->view()->assign('result', $result);
        }

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('password-find');
    }

    /**
     * 1. Verify find password link
     * 2. Update user information
     */
    public function processAction()
    {
        $result = array(
            'status'  => 0,
            'message' => '',
        );
        $hashUid  = _get('uid');
        $token    = _get('token');

        // Verify link invalid
        if (!$hashUid || !$token) {
            $result['message'] = __('Verify link invalid');
            $this->view()->assign('result', $result);
            return;
        }

        $userData = Pi::user()->data()->find(array(
            'value' => $token
        ));
        if (!$userData) {
            $result['message'] = __('Verify link invalid');
            $this->view()->assign('result', $result);
            return;
        }

        $userRow = $this->getModel('account')->find($userData['uid'], 'id');
        if (!$userRow || md5($userRow['id']) != $hashUid) {
            $result['message'] = __('Verify link invalid');
            $this->view()->assign('result', $result);
            return;
        }

        // Verify link expire time
        $expire  =  $userData['time'] + 24 * 3600;
        $current = time();
        if ($current > $expire) {
            $result['message'] = __('Verify link invalid');
            $this->view()->assign('result', $result);
            return;
        }

        $uid  = $userRow->id;
        $form = new ResetPasswordForm('find-password', 'find');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new ResetPasswordFilter('find'));
            $form->setData($data);

            if ($form->isValid()) {
                $values = $form->getData();

                // Update user account data
                Pi::api('user', 'user')->updateAccount(
                    $uid,
                    array('credential' => $values['credential-new'])
                );

                // Delete find password verify token
                Pi::user()->data()->delete($uid, 'find-password');
                $result['message'] = __('Reset password successfully');
                $result['status']  = 1;
            }
            $this->view()->assign('result', $result);
        }

        $this->view()->assign(array(
            'form' => $form
        ));
    }

    /**
     * Set mail params
     *
     * @param $username
     * @param $link
     * @return array
     */
    protected function setMailParams($username, $link)
    {
        $params = array(
            'username'           => $username,
            'find_password_link' => $link,
            'sn'                 => _date(),
        );

        // Load from HTML template
        $data = Pi::service('mail')->template(
            'find-password-mail-html',
            $params
        );

        // Set subject and body
        $subject = $data['subject'];
        $body = $data['body'];
        $type = $data['format'];

        return array($subject, $body, $type);
    }
}
