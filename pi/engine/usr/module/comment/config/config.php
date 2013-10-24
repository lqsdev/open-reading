<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

$config = array(
    'leading_limit' => array(
        'title'     => _t('Number of comments on leading page.'),
        'value'         => 5,
        'filter'        => 'int',
    ),

    'list_limit' => array(
        'title'     => _t('Number of comments on list page.'),
        'value'         => 20,
        'filter'        => 'int',
    ),

    'display_operation' => array(
        'title'         => _t('Display operation'),
        'description'   => _t('Level of operations on post list pages.'),
        'value'         => 'author',
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    ''          => _t('Disable'),
                    'member'    => _t('For members'),
                    'author'    => _t('For authors'),
                    'admin'     => _t('For administrators'),
                ),
            ),
        ),
    ),

    'auto_approve'  => array(
        'title'         => _t('Auto approve submission.'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'int',
    ),
);

return $config;