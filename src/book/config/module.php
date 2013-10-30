<?php

/**
 * Class controller Class
 * @copyright       Copyright (c) lqsit
 * @license         All rights reserved
 * @author          lqsic
 * @package         Module\book
*/

return array(
    // Module meta
    'meta'         => array(
        'title'         => __('Book'),
        'description'   => __('General module for book management.'),
        'version'       => '1.0.0',
        'license'       => 'New BSD',
        'logo'          => 'image/logo.png',
        'readme'        => 'README.md',
        'clonable'      => false,//是否允许重复安装
    ),
    // Author information
    'author'        => array(
        'name'          => 'lqsdev',
        'email'         => '',
        'website'       => 'http://www.github.com/lqsdev',
        'credits'       => 'lqsdev Team.'
    ),
    // Module dependency: list of module directory names, optional
    'dependency'    => array(
    ),
    // Maintenance actions
    'resource'      => array(
        'database'      => array(
            'sqlfile'      => 'sql/mysql.sql',
        ),
        // Database meta
        'navigation'    => 'navigation.php',
        'route'         => 'route.php',
        'config'        => 'config.php',
    ),
);
