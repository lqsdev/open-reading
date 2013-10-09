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
        'cms'    => array(
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
                'action'        => 'list',
            ),
            'dashboard'         => array(
                'label'         => _t('Dashboard'),
                'route'         => 'default',
                'controller'    => 'dashboard',
                'action'        => 'index',
            ),
        ),
    ),
);
