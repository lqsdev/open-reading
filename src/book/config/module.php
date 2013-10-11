<?php

/**
 * Base module config
 *
 * @copyright       Copyright (c) Long Quan Si ...
 * @license         All rights reserved
 * @author          lqsit
 * @package         Module\Base
 */
/**
 * Application manifest
 */
return array(
    // Module meta
    'meta' => array(
        // Module title, required
        'title' => __('Book'), 
        // Version number, required
        'version' => '0.1.0',
        // Distribution license, required
        'license' => 'New BSD',
        // Logo image, for admin, optional
        'logo' => 'image/logo.png',
        'clonable' => false,
    ),
    // Author information
    'author' => array(
        // Author full name, required
        'name' => 'lqsic',
//        'readme' => 'docs/lqsdic-readme.txt',
       
    ),
    // Maintenance actions
    'maintenance' => array(
        // resource
        'resource' => array(
            // Database meta
            'database' => array(
                // SQL schema/data file
                'sqlfile' => 'sql/book.sql',
                // Tables to be removed during uninstall, 
                // optional - the table list will be generated automatically upon installation
                'schema' => array(
                    'book'      => 'table',
                    'catalogue' => 'table',
                    'catalogue_rel_article'   => 'table',
                    'article'        => 'table',                  
                ),
            ),
            // Module configs
            'config' => 'config.php',
            // Navigation definition
            'navigation' => 'navigation.php',
        )
    )
);
