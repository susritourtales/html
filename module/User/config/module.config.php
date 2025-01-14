<?php

namespace User;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

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
            ], 'sms-status' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/sms-status',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'sms-status',
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
            ], 'twistt-executive-buy-coupons' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/buy-coupons',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-buy-coupons',
                    ],
                ],
            ], 'twistt-executive-track-coupons' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/track-coupons',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-track-coupons',
                    ],
                ],
            ],  'twistt-executive-load-coupons' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/load-coupons-list',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'load-coupons-list',
                    ],
                ],
            ], 'twistt-executive-track-commissions' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/track-commissions',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-track-commissions',
                    ],
                ],
            ],  'twistt-executive-load-commissions' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/load-transactions-list',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'load-transactions-list',
                    ],
                ],
            ],  'twistt-executive-track-commissions-earned' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/track-commissions-earned',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-track-commissions-earned',
                    ],
                ],
            ],  'twistt-executive-load-commissions-earned' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/load-transactions-earned-list',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'load-transactions-earned-list',
                    ],
                ],
            ],  'twistt-executive-track-commissions-received' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/track-commissions-received',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-track-commissions-received',
                    ],
                ],
            ],  'twistt-executive-load-commissions-received' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/load-transactions-received-list',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'load-transactions-received-list',
                    ],
                ],
            ], 'twistt-executiveterms' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive-terms',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'twistt-executive-terms',
                    ],
                ],
            ],  'twistt-plans-discounts' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/plans-discounts',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'twistt-plans-discounts',
                    ],
                ],
            ],'twistt-app-auth' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/app/auth',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'app-auth',
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
            ], 'twistt-executive-pay' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/pay',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-pay',
                    ],
                ],
            ], 'twistt-executive-checkout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/executive/checkout',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-checkout',
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
            ],   'twistt-enabler-send-otp' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/send-otp',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-send-otp',
                    ],
                ],
            ],  'twistt-enabler-verify-otp' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/verify-otp',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-verify-otp',
                    ],
                ],
            ],  'twistt-enabler-auth' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/auth',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-auth',
                    ],
                ],
            ], 'twistt-enabler-stt-auth' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/stt-auth',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'stt-enabler-auth',
                    ],
                ],
            ], 'twistt-enabler-add' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/add',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-add',
                    ],
                ],
            ], 'twistt-enabler-edit' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/edit',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-edit',
                    ],
                ],
            ],  'twistt-enabler-buy-plans' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/buy-plans',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-buy-plans',
                    ],
                ],
            ], 'twistt-enabler-track-plans' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/track-plans',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-track-plans',
                    ],
                ],
            ],  'twistt-enabler-load-plans' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/load-plans-list',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'load-plans-list',
                    ],
                ],
            ], 'twistt-enabler-track-purchases' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/track-purchases',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-track-purchases',
                    ],
                ],
            ],  'twistt-enabler-load-purchases' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/load-purchases-list',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'load-purchases-list',
                    ],
                ],
            ], 'twistt-enabler-profile' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/profile',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-profile',
                    ],
                ],
            ],  'enabler-get-plan-price' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/get-plan-price',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'get-plan-price',
                    ],
                ],
            ],  'twistt-enabler-terms' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/terms',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-terms',
                    ],
                ],
            ],  'enabler-purchase-request' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/purchase-request',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-purchase-request',
                    ],
                ],
            ], 'twistt-enabler-pay' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/pay',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-pay',
                    ],
                ],
            ],  'twistt-enabler-invoice' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/enabler/invoice[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'tour' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*'
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-invoice',
                    ],
                ],
            ], 'twistt-enabler-receipt' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/enabler/receipt[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'tour' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*'
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-receipt',
                    ],
                ],
            ], 'twistt-enabler-checkout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/checkout',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-checkout',
                    ],
                ],
            ], 'twistt-enabler-sell' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/twistt/enabler/sell',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-sell',
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
            ], 'executive-forgot-password' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/executive/forgot-password',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'executive-forgot-password',
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
            ], 'enabler-forgot-password' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/enabler/forgot-password',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-forgot-password',
                    ],
                ],
            ], 'enabler-change-password' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/enabler/change-password',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-change-password',
                    ],
                ],
            ], 'enabler-reset-password' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/twistt/enabler/reset-password',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'enabler-reset-password',
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
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/user-layout'   => __DIR__ . '/../../Application/view/layout/user-layout.phtml',
            'user/index/index'     => __DIR__ . '/../view/user/index/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
