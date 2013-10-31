<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    // Admin section
    'admin' => array(
        array(
            'controller'    => 'index',
            'permission'    => 'user',
        ),
        array(
            'controller'    => 'role',
            'permission'    => 'role',
        ),
        array(
            'controller'    => 'maintenance',
            'permission'    => 'maintenance',
        ),
    ),
);
