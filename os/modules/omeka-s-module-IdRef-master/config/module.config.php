<?php

return [
    'data_types' => [
        'invokables' => [
            'idref' => 'IdRef\DataType\IdRef',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'IdRef\Controller\Admin\IdRef' => 'IdRef\Controller\Admin\IdRefController',
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'idref-search' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/idref/search',
                            'defaults' => [
                                '__NAMESPACE__' => 'IdRef\Controller\Admin',
                                'controller' => 'IdRef',
                                'action' => 'search',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
