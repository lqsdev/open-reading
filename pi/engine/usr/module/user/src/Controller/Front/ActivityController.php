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

/**
 * Activity controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class ActivityController extends ActionController
{
    /**
     * Display activity
     *
     * @return array|void
     */
    public function indexAction()
    {
        $name     = _get('name');
        $uid      = _get('uid');
        $ownerUid = Pi::user()->getId();
        $limit  = Pi::service('module')->config('list_limit', 'user');
        $isOwner  = 0;

        if (!$uid && !$ownerUid) {
            return $this->jumpTo404('An error occur');
        }

        // Check is owner
        if (!$uid) {
            $isOwner = 1;
            $uid     = $ownerUid;
        }
        if (!$name) {
            $this->jumpTo404('An error occur');
        }

        // Get user base info
        $user = Pi::api('user', 'user')->get(
            $uid,
            array('name', 'gender', 'birthdate'),
            true
        );
        // Get viewer role: public member follower following owner
        if ($isOwner) {
            $role = 'owner';
        } else {
            $role = Pi::user()->hasIdentity() ? 'member' : 'public';
        }
        $user = Pi::api('user', 'privacy')->filterProfile(
            $uid,
            $role,
            $user,
            'user'
        );

        // Get activity list for nav display
        $activityList = Pi::api('user', 'activity')->getList();

        // Get current activity data
        $data = Pi::api('user', 'activity')->get($uid, $name, $limit);

        // Get nav
        if ($isOwner) {
            $nav = Pi::api('user', 'nav')->getList($name);
        } else {
            $nav = Pi::api('user', 'nav')->getList($name, $uid);
        }

        // Get quick link
        $quicklink = Pi::api('user','quicklink')->getList();

        $this->view()->assign(array(
            'list'      => $activityList,
            'current'   => $name,
            'data'      => $data,
            'user'      => $user,
            'nav'       => $nav,
            'uid'       => $uid,
            'quicklink' => $quicklink,
            'is_owner'  => $isOwner,
        ));

    }

    /**
     * Test for activity more link contents
     */
    public function moreAction()
    {
        $this->view()->setTemplate('activity-more');
    }
}
