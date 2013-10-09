<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

/**
 * Module config and meta
 * 
 * @author Zongshu Lin <lin40553024@163.com>
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
                    'book'       => 'table',
                    'catalogue'  => 'table',
                    'article'    => 'table',
                ),
            ),
            // Database meta
            'navigation'    => 'navigation.php',
            'block'         => 'block.php',
            'config'        => 'config.php',
            'route'         => 'route.php',
            'acl'           => 'acl.php',
            'page'          => 'page.php',
        ),
    ),
);
