<?php

namespace TraceAes;

use Zend\Router\Http\Segment;

return [
    'session_config' => [
        'name' => 'traceaes_session',
        'cookie_httponly' => true,
    ],
    'session_storage' => [
        'type' => \Zend\Session\Storage\SessionArrayStorage::class,
    ],
    'router' => [
        'routes' => [
            'trace-aes' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/trace-aes[/:controller[/:action[/:id]]]',
                    'constraints' => [
                        'controller' => 'citerne|chargement|trajet|alerte|verification|position|carte|auth',
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
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\CiterneController::class => Controller\Factory\CiterneControllerFactory::class,
            Controller\ChargementController::class => Controller\Factory\ChargementControllerFactory::class,
            Controller\TrajetController::class => Controller\Factory\TrajetControllerFactory::class,
            Controller\AlerteController::class => Controller\Factory\AlerteControllerFactory::class,
            Controller\VerificationController::class => Controller\Factory\VerificationControllerFactory::class,
            Controller\PositionGpsController::class => Controller\Factory\PositionGpsControllerFactory::class,
            Controller\CarteController::class => Controller\Factory\CarteControllerFactory::class,
            Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
        ],
        'aliases' => [
            'citerne' => Controller\CiterneController::class,
            'chargement' => Controller\ChargementController::class,
            'trajet' => Controller\TrajetController::class,
            'alerte' => Controller\AlerteController::class,
            'verification' => Controller\VerificationController::class,
            'position' => Controller\PositionGpsController::class,
            'carte' => Controller\CarteController::class,
            'auth' => Controller\AuthController::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Model\CiterneTable::class => Model\Factory\CiterneTableFactory::class,
            Model\ChargementTable::class => Model\Factory\ChargementTableFactory::class,
            Model\TrajetTable::class => Model\Factory\TrajetTableFactory::class,
            Model\AlerteTable::class => Model\Factory\AlerteTableFactory::class,
            Model\DepotTable::class => Model\Factory\DepotTableFactory::class,
            Model\PointControleTable::class => Model\Factory\PointControleTableFactory::class,
            Model\AgentTable::class => Model\Factory\AgentTableFactory::class,
            Model\ScelleNumeriqueTable::class => Model\Factory\ScelleNumeriqueTableFactory::class,
            Model\VerificationArriveeTable::class => Model\Factory\VerificationArriveeTableFactory::class,
            Model\PositionGpsTable::class => Model\Factory\PositionGpsTableFactory::class,
            Model\SocieteTransportTable::class => Model\Factory\SocieteTransportTableFactory::class,
            Service\ChargementService::class => Service\Factory\ChargementServiceFactory::class,
            Service\CiterneService::class => Service\Factory\CiterneServiceFactory::class,
            Service\MoteurAlertesService::class => Service\Factory\MoteurAlertesServiceFactory::class,
            Service\VerificationArriveeService::class => Service\Factory\VerificationArriveeServiceFactory::class,
            Service\PositionGpsService::class => Service\Factory\PositionGpsServiceFactory::class,
            Service\CarteDataService::class => Service\Factory\CarteDataServiceFactory::class,
            \Zend\Authentication\AuthenticationService::class => Service\Factory\AuthenticationServiceFactory::class,
            Service\AuthService::class => Service\Factory\AuthServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\IdentiteConnectee::class => View\Helper\Factory\IdentiteConnecteeFactory::class,
        ],
        'aliases' => [
            'identiteConnectee' => View\Helper\IdentiteConnectee::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'trace-aes' => __DIR__ . '/../view',
        ],
    ],
];
