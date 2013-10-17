<?php

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
            'article-homepage'  => array(
                'label'         => _t('Book Homepage'),
                'route'         => 'default',
                'controller'    => 'book',
                'action'        => 'index',
            ),
        ),
        
        // Default admin navigation
        'admin'   => array(
            'book'           => array(
                'label'         => _t('Book List'),
                'route'         => 'admin',
                'controller'    => 'book',
                'action'        => 'list',
            ),
            'article'             => array(
                'label'         => _t('Artilce'),
                'route'         => 'admin',
                'controller'    => 'article',
                'action'        => 'edit',
            ),
             'media'             => array(
                'label'         => _t('Media'),
                'route'         => 'admin',
                'controller'    => 'media',
                'action'        => 'list',
            ),
        ),
    ),
);
