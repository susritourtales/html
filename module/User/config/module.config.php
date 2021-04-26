<?php

namespace User;

use User\Controller\IndexController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'user' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/user/index[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'about-us' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/about-us',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'about-us',
                    ],
                ],
            ], 'country-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/country-list[/:tour]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'tour' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'country-list',
                    ],
                ],
            ],
            'acknowledgements' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/acknowledgements',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'acknowledgements',
                    ],
                ],
            ],
            'tours' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/tours',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'tours',
                    ],
                ],
            ],
            'faqs' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/faqs',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'faqs',
                    ],
                ],
            ],
            'contact' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/contact',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'contact',
                    ],
                ],
            ],

            'contributor' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/contributor',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'contributor',
                    ],
                ],
            ],
            'terms-privacy' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/terms-privacy',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'terms-privacy',
                    ],
                ],
            ],
            'user-city-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/city-list/[:tour]/[:countryName]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'tour' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                        'countryName' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'city-list',
                    ],
                ],
            ],
            'user-state-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/state-list/[:tour]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'tour' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*'
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'state-list',
                    ],
                ],
            ],
            'user-places-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/places-list/[:tour]/[:cityName]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'tour' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                        'cityName' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'places-list',
                    ],
                ],
            ],
            'booking-details' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/booking-details',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'tour' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                        'cityName' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'booking-details',
                    ],
                ],
            ],
            'help' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/help',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'help',
                    ],
                ],
            ],   'seasonal-specials' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/seasonal-specials',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'seasonal-specials',
                    ],
                ],
            ],  'seasonal-places-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/seasonal-places-list/[:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*'
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'seasonal-places-list',
                    ],
                ],
            ],
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
            'api/index/index' => __DIR__ . '/../view/user/index/index.phtml',
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
