<?php

return array(
    'service_manager' => array(
        'invokables' => array(
            'Omeka2Importer\Omeka2Client' => 'Omeka2Importer\Service\Omeka2Client',
        ),
    ),
    'api_adapters' => array(
        'invokables' => array(
            'omekaimport_records' => 'Omeka2Importer\Api\Adapter\OmekaimportRecordAdapter',
            'omekaimport_imports' => 'Omeka2Importer\Api\Adapter\OmekaimportImportAdapter',
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'Omeka2Importer\Controller\Index' => 'Omeka2Importer\Service\Controller\IndexControllerFactory',
        ),
    ),
    'form_elements' => [
        'invokables' => [
            'Omeka2Importer\Form\ImportForm' => 'Omeka2Importer\Form\ImportForm',
        ],
        'factories' => [
            'Omeka2Importer\Form\MappingForm' => 'Omeka2Importer\Service\Form\MappingFormFactory',
        ],
    ],
    'view_manager' => array(
        'template_path_stack' => array(
            OMEKA_PATH.'/modules/Omeka2Importer/view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
        'resourceClassSelector' => 'Omeka2Importer\View\Helper\ResourceClassSelector',
        ),
    ),
    'entity_manager' => array(
        'mapping_classes_paths' => array(
            OMEKA_PATH.'/modules/Omeka2Importer/src/Entity',
        ),
    ),

    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'omeka2importer' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/omeka2importer',
                            'defaults' => array(
                                '__NAMESPACE__' => 'Omeka2Importer\Controller',
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'past-imports' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/past-imports',
                                    'defaults' => array(
                                        '__NAMESPACE__' => 'Omeka2Importer\Controller',
                                        'controller' => 'Index',
                                        'action' => 'past-imports',
                                    ),
                                ),
                            ),
                            'map-elements' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/map-elements',
                                    'defaults' => array(
                                        '__NAMESPACE__' => 'Omeka2Importer\Controller',
                                        'controller' => 'Index',
                                        'action' => 'map-elements',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'navigation' => array(
        'AdminModule' => array(
            array(
                'label' => 'Omeka 2 Importer',
                'route' => 'admin/omeka2importer',
                'resource' => 'Omeka2Importer\Controller\Index',
                'pages' => array(
                    array(
                        'label' => 'Import',
                        'route' => 'admin/omeka2importer',
                        'resource' => 'Omeka2Importer\Controller\Index',
                    ),
                    array(
                        'label' => 'Import',
                        'route' => 'admin/omeka2importer/map-elements',
                        'resource' => 'Omeka2Importer\Controller\Index',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Past Imports',
                        'route' => 'admin/omeka2importer/past-imports',
                        'controller' => 'Index',
                        'action' => 'past-imports',
                        'resource' => 'Omeka2Importer\Controller\Index',
                    ),
                ),
            ),
        ),
    ),
);
