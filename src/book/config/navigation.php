<?php

/**
 * Class controller Class
 * @copyright       Copyright (c) lqsit
 * @license         All rights reserved
 * @author          lqsic
 * @package         Module\book
*/

return array(
    'meta'  => array(
        'front'    => array(
            'title'     => _t('Book site navigation'),
            'section'   => 'front',
        ),
    ),
    'item'  => array(
        // Default front navigation
        'front'   => array(
            'book-homepage'  => array(
                'label'         => _t('Book Homepage'),
                'route'         => 'default',
                'controller'    => 'book',
                'action'        => 'index',
            ),
            'media'             => array(
                'label'         => _t('Media'),
                'route'         => 'default',
                'controller'    => 'media',
                'action'        => 'list',
                'resource'      => array(
                    'resource'  => 'media',
                ),
            ),
        ),
        'admin'   => array(
            'book-homepage'  => array(
                'label'         => _t('Book Homepage'),
                'route'         => 'default',
                'controller'    => 'book',
                'action'        => 'index',
            ),
            'media'             => array(
                'label'         => _t('Media'),
                'route'         => 'default',
                'controller'    => 'media',
                'action'        => 'list',
                'resource'      => array(
                    'resource'  => 'media',
                ),
            ),
        ),
    ),
);
