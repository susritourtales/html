<?php

namespace User;

use User\Controller\IndexController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
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
                'type' => Literal::class,
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
            ], 'twistt' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'twistt',
                    ],
                ],
            ], 'twistt-executive-terms' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/terms',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-terms',
                    ],
                ],
            ], 'twistt-executive-register' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/register',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-register',
                    ],
                ],
            ], 'twistt-executive-login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/login',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-login',
                    ],
                ],
            ], 'twistt-executive-logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/logout',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-logout',
                    ],
                ],
            ], 'twistt-executive-auth' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/auth',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-auth',
                    ],
                ],
            ], 'twistt-executive-stt-auth' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/stt-auth',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'stt-auth',
                    ],
                ],
            ], 'twistt-executive-add' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/add',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-add',
                    ],
                ],
            ], 'twistt-executive-edit' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/edit',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-edit',
                    ],
                ],
            ], 'twistt-executive-home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/home',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-home',
                    ],
                ],
            ], 'twistt-executive-profile' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/profile',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-profile',
                    ],
                ],
            ], 'twistt-executive-verify-mobile' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/verify-mobile',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-verify-mobile',
                    ],
                ],
            ],  'twistt-executive-send-otp' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/send-otp',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-send-otp',
                    ],
                ],
            ],  'twistt-executive-verify-otp' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/verify-otp',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-verify-otp',
                    ],
                ],
            ], 'twistt-enabler-register' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/register',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-register',
                    ],
                ],
            ], 'twistt-enabler-login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/login',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-login',
                    ],
                ],
            ], 'twistt-enabler-logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/logout',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-logout',
                    ],
                ],
            ], 'questt-validity' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/questt-validity',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'questt-validity',
                    ],
                ],
            ], 'forgot-password' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/forgot-password',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'forgot-password',
                    ],
                ],
            ], 'change-password' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/change-password',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'change-password',
                    ],
                ],
            ], 'reset-password' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/reset-password',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'reset-password',
                    ],
                ],
            ], 
        ],
    ],
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
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout'   => __DIR__ . '/../view/layout/user-layout.phtml',
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
