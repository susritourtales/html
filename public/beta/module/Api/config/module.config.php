<?php

namespace Api;

use Admin\Controller\AdminController;
use Api\Controller\IndexController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [

            'index' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'api/index/index' => __DIR__ . '/../view/api/index/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        /*
         * Con este array de parámetros permitimos enviar datos y no mostrar vista
         */
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];