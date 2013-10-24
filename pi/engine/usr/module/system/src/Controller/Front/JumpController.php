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
use Pi\Mvc\Controller\ActionController;

/**
 * Page jump
 */
class JumpController extends ActionController
{
    /**
     * Transition page jump
     */
    public function indexAction()
    {
        $this->view()->setTemplate('jump')->setLayout('layout-simple');
        $params = Pi::service('cookie')->get('PI_JUMP', true, true);
        //vd($params); exit();
        if (empty($params['time'])) {
            $params['time'] = 3;
        }
        if (empty($params['url'])) {
            $params['url'] = Pi::url('www');
        }
        //vd($params);
        $this->view()->assign($params);
    }
}
