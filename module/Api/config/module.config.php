<?php
namespace Api;

use Laminas\Router\Http\Segment;
use Laminas\Router\Http\Literal;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

return [
    // This lines opens the configuration for the RouteManager
    'router' => [
        // Open configuration for all possible routes
        'routes' => [
            // Define a new route called "api"
            'api' => [
                // Define a "Segment" route type:
                'type' => Segment::class,
                // Configure the route itself
                'options' => [
                    // Listen to "/api" as uri:
                    'route' => '/api[/:action][/:id]',
                    // Define default controller and action to be called when this route is matched
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    // 'controllers' => [
    //     'factories' => [
    //         Controller\IndexController::class => InvokableFactory::class,
    //     ],
    // ],
    'service_manager' => [
        'abstract_factories' => [
            ReflectionBasedAbstractFactory::class,
        ],
        'factories' => [
            Controller\IndexController::class => ReflectionBasedAbstractFactory::class,
            'Laminas\Authentication\AuthenticationService' => function ($serviceManager) {
                $dbAdapter = $serviceManager->get('Laminas\Db\Adapter\Adapter');
                $authAdapter = new \Admin\Auth\Adapter\DbTable($dbAdapter);
                return new \Laminas\Authentication\AuthenticationService(null, $authAdapter);
            },
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];