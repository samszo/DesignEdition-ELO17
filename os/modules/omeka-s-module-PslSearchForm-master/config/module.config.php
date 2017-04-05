<?php
return [
    'form_elements' => [
        'factories' => [
            'PslSearchForm\Form\PslForm' => 'PslSearchForm\Service\Form\PslFormFactory',
            'PslSearchForm\Form\FilterFieldset' => 'PslSearchForm\Service\Form\FilterFieldsetFactory',
            'PslSearchForm\Form\PslFormConfigFieldset' => 'PslSearchForm\Service\Form\PslFormConfigFieldsetFactory',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'search' => [
        'form_adapters' => [
            'psl' => 'PslSearchForm\FormAdapter\PslFormAdapter',
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'        => 'gettext',
                'base_dir'    => __DIR__ . '/../language',
                'pattern'     => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
];
