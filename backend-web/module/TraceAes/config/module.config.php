<?php

namespace TraceAes;

use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'trace-aes' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/trace-aes[/:controller[/:action[/:id]]]',
                    'constraints' => [
                        'controller' => 'citerne|chargement|trajet|alerte',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
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
            Controller\IndexController::class => InvokableFactory::class,
            Controller\CiterneController::class => Controller\Factory\CiterneControllerFactory::class,
            Controller\ChargementController::class => Controller\Factory\ChargementControllerFactory::class,
            Controller\TrajetController::class => Controller\Factory\TrajetControllerFactory::class,
            Controller\AlerteController::class => Controller\Factory\AlerteControllerFactory::class,
        ],
        'aliases' => [
            'citerne' => Controller\CiterneController::class,
            'chargement' => Controller\ChargementController::class,
            'trajet' => Controller\TrajetController::class,
            'alerte' => Controller\AlerteController::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Model\CiterneTable::class => Model\Factory\CiterneTableFactory::class,
            Model\ChargementTable::class => Model\Factory\ChargementTableFactory::class,
            Model\TrajetTable::class => Model\Factory\TrajetTableFactory::class,
            Model\AlerteTable::class => Model\Factory\AlerteTableFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'trace-aes' => __DIR__ . '/../view',
        ],
    ],
];
