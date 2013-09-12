<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

/**
 * Permission config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'roles'      => array(
        // Front role for article module
        'article-manager' => array(
            'title'     => _t('Article Manager'),
        ),
        'contributor'     => array(
            'title'     => _t('Contributor'),
            'section'   => 'admin',
            'parents'   => array('staff'),
        )
    ),
    
    'resources'  => array(
        'front'          => array(
            // Article author resource
            'author'     => array(
                'module'      => 'article',
                'title'       => _t('Author management'),
                'access'      => array(
                    'article-manager' => 0,
                    'member'          => 0,
                    'guest'           => 0,
                    'inactive'        => 0,
                    'banned'          => 0,
                ),
            ),
            // Article category resource
            'category'   => array(
                'module'      => 'article',
                'title'       => _t('Category management'),
                'access'      => array(
                    'article-manager' => 0,
                    'member'          => 0,
                    'guest'           => 0,
                    'inactive'        => 0,
                    'banned'          => 0,
                ),
            ),
            // Topic resource
            'topic'      => array(
                'module'      => 'article',
                'title'       => _t('Topic management'),
                'access'      => array(
                    'article-manager' => 0,
                    'member'          => 0,
                    'guest'           => 0,
                    'inactive'        => 0,
                    'banned'          => 0,
                ),
            ),
            // Media resource
            'media'      => array(
                'module'      => 'article',
                'title'       => _t('Media management'),
                'access'      => array(
                    'article-manager' => 0,
                    'member'          => 0,
                    'guest'           => 0,
                    'inactive'        => 0,
                    'banned'          => 0,
                ),
            ),
        ),
        
        'admin'          => array(
            // Article statistics resource
            'statistics' => array(
                'module'      => 'article',
                'title'       => _t('Statistics page view'),
                'access'      => array(
                    'contributor'  => 1,
                ),
            ),
            // Module permission controller
            'permission' => array(
                'module'      => 'article',
                'title'       => _t('Permission management'),
            ),
            // Article configuration
            'config'     => array(
                'module'      => 'article',
                'title'       => _t('Configuration management'),
            ),
        ),
    ),
);
