<?php

namespace Application;

use Application\Controller\IndexController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'application' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'payment-response-gateway' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/application/payment-response',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'paymentResponse',
                    ],
                ],
            ],'check-mail' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/application/check-mail',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'checkMail',
                    ],
                ],
            ],'application-booking-status' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/application/booking-status',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'bookingStatus',
                    ],
                ],
            ],'andriodRedirect' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/application/andriod-redirect',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'andriodRedirect',
                    ],
                ],
            ],'application-payment-process' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/payment-process[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'payment-process',
                    ],
                ],
            ],'payment-process-details' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/payment-process-details[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'payment-process-details',
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
    'moduleLayouts' => array(
        'User' => 'layout/user-layout',
    ),
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
