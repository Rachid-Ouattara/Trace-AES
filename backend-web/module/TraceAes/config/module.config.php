<?php

namespace TraceAes;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'trace-aes' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/trace-aes[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => \Zend\ServiceManager\Factory\InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'trace-aes' => __DIR__ . '/../view',
        ],
    ],
];
