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
        'description'   => __('General module for content management.'),
        'version'       => '1.0.1-beta.1',
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
    'maintenance'   => array(
        'resource'      => array(
            'database'      => array(
                'sqlfile'      => 'sql/mysql.sql',
                'schema'       => array(
                    'book'                  => 'table',
                    'catalogue'             => 'table',
                    'catalogue_rel_article' => 'table',
                    'article'               => 'table',
                ),
            ),
            // Database meta
            'navigation'    => 'navigation.php',
            'route'         => 'route.php',
        ),
    ),
);
