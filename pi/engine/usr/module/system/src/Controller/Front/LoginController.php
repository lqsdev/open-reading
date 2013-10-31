<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Authentication\Result;
use Pi\Mvc\Controller\ActionController;
use Module\System\Form\LoginForm;
use Module\System\Form\LoginFilter;

/**
 * User login/logout controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class LoginController extends ActionController
{
    /**
     * Login form
     *
     * @return void
     */
    public function indexAction()
    {
        if (Pi::config('login_disable', 'user')) {
            $this->jump(
                array('route' => 'home'),
                __('Login is disabled. Please come back later.'),
                5
            );
            return;
        }

        // If already logged in
        if (Pi::service('user')->hasIdentity()) {
            $this->view()->assign('title', __('User login'));
            $this->view()->setTemplate('login-message');
            $this->view()->assign(array(
                'identity'  => Pi::service('user')->getIdentity()
            ));
            return;
        }

        // Display login form
        $form = $this->getForm();
        $redirect = $this->params('redirect');
        if (null === $redirect) {
            $redirect = $this->request->getServer('HTTP_REFERER');
        }
        if (null !== $redirect) {
            $redirect = $redirect ? urlencode($redirect) : '';
            $form->setData(array('redirect' => $redirect));
        }
        $this->renderForm($form);
    }

    /**
     * Logout
     */
    public function logoutAction()
    {
        Pi::service('session')->manager()->destroy();
        Pi::service('user')->destroy();
        $redirect = _get('redirect');
        $redirect = $redirect
            ? urldecode($redirect) : array('route' => 'home');

        $this->jump(
            $redirect,
            __('You logged out successfully. Now go back to homepage.')
        );
    }

    /**
     * Render login form
     *
     * @param LoginForm $form
     * @param string $message
     */
    protected function renderForm($form, $message = '')
    {
        $this->view()->setTemplate('login');
        $configs = Pi::registry('config')->read('', 'user');

        if (!empty($configs['attempts'])) {
            $attempts = isset($_SESSION['PI_LOGIN']['attempts'])
                ? $_SESSION['PI_LOGIN']['attempts'] : 0;
            if (!empty($attempts)) {
                if ($attempts >= $configs['attempts']) {
                    $wait = Pi::service('session')->manager()
                        ->getSaveHandler()->getLifeTime() / 60;
                    $message = sprintf(
                        __('Login with the account is suspended, please wait for %d minutes to try again.'),
                        $wait
                    );
                    $this->view()->setTemplate('login-suspended');
                } else {
                    $remaining = $configs['attempts'] - $attempts;
                    $message = sprintf(
                        __('You have %d times to try.'),
                        $remaining
                    );
                }
            }
        }
        $this->view()->assign('title', __('User login'));
        $this->view()->assign('message', $message);
        $this->view()->assign('form', $form);
    }

    /**
     * Process login submission
     *
     * @return void
     */
    public function processAction()
    {
        $configs = $this->preProcess();

        $post = $this->request->getPost();
        $form = $this->getForm();
        $form->setData($post);
        $form->setInputFilter(new LoginFilter);

        if (!$form->isValid()) {
            $this->renderForm($form, __('Invalid input, please try again.'));

            return;
        }

        //$configs    = Pi::registry('config')->read('', 'user');
        $values     = $form->getData();
        $identity   = $values['identity'];
        $credential = $values['credential'];

        if (!empty($configs['attempts'])) {
            $sessionLogin = isset($_SESSION['PI_LOGIN'])
                ? $_SESSION['PI_LOGIN'] : array();
            if (!empty($sessionLogin['attempts'])
                && $sessionLogin['attempts'] >= $configs['attempts']
            ) {
                $this->jump(
                    array('route' => 'home'),
                    __('You have tried too many times. Please try later.'),
                    5
                );

                return;
            }
        }

        $result = Pi::service('user')->authenticate($identity, $credential);
        $result = $this->postProcess($result);

        if (!$result->isValid()) {
            if (!empty($configs['attempts'])) {
                if (!isset($_SESSION['PI_LOGIN'])) {
                    $_SESSION['PI_LOGIN'] = array();
                }
                $_SESSION['PI_LOGIN']['attempts'] =
                    isset($_SESSION['PI_LOGIN']['attempts'])
                    ? ($_SESSION['PI_LOGIN']['attempts'] + 1) : 1;
            }
            $message = __('Invalid credentials provided, please try again.');
            $this->renderForm($form, $message);

            return;
        }

        if ($configs['rememberme'] && $values['rememberme']) {
            Pi::service('session')->manager()
                ->rememberme($configs['rememberme'] * 86400);
        }
        $uid = $result->getData('id');
        Pi::service('session')->setUser($uid);
        Pi::service('user')->bind($uid);
        Pi::service('event')->trigger('login', $uid);

        if (isset($_SESSION['PI_LOGIN'])) {
            unset($_SESSION['PI_LOGIN']);
        }

        if (empty($values['redirect'])) {
            $redirect = array('route' => 'home');
        } else {
            $redirect = urldecode($values['redirect']);
        }
        $this->jump($redirect, __('You have logged in successfully.'));
    }

    /**
     * Load login form
     *
     * @return LoginForm
     */
    public function getForm()
    {
        $form = new LoginForm('login');
        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'process'))
        );

        return $form;
    }

    /**
     * Pre-process handling
     *
     * @return array
     */
    protected function preProcess()
    {
        if (Pi::config('login_disable', 'user')) {
            $this->jump(array('route' => 'home'),
                __('Login is closed. Please try later.'), 5);

            return;
        }

        if (!$this->request->isPost()) {
            $this->jump(array('action' => 'index'), __('Invalid request.'));

            return;
        }

        $configs = Pi::registry('config')->read('', 'user');

        return $configs;
    }

    /**
     * Filtering Result after authentication
     *
     * @param Result $result
     *
     * @return Result
     */
    protected function postProcess(Result $result)
    {
        return $result;
    }
}
