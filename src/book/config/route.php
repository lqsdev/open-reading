<?php

return array(
    'book' => array(
        'section'  => 'front',
        'priority' => 100,

        'type'     => 'Standard',
        'options'  => array(
            'structure_delimiter'   => '/',
            'param_delimiter'       => '/',
            'key_value_delimiter'   => '-',
            'defaults'        => array(
                'module'     => 'book',
                'controller' => 'index',
                'action'     => 'index',
            ),
        ),
    ),
);
