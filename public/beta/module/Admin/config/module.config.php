<?php

namespace Admin;

use Admin\Controller\AdminController;
use Admin\Controller\IndexController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'index' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/index[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'Adminlogin' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/login',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'login',
                    ],
                ],
            ], 'twistt-login' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/twistt-login',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'twistt-login',
                    ],
                ],
            ],'twistt-logout' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/twistt-logout',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'twistt-logout',
                    ],
                ],
            ], 'twisttse-login' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/twisttse-login',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'twisttse-login',
                    ],
                ],
            ],'twisttse-logout' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/twisttse-logout',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'twisttse-logout',
                    ],
                ],
            ], 'twistt-cpwd' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/twistt-cpwd',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'twistt-cpwd',
                    ],
                ],
            ], 'twisttse-cpwd' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/twisttse-cpwd',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'twisttse-cpwd',
                    ],
                ],
            ], 'sendMessage' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/send-message',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'sendMessage',
                    ],
                ],
            ],
            'uploadFiles' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/upload-files',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'uploadFiles',
                    ],
                ],
            ] ,'edit-place' => array(
                'type' =>Segment::class,
                'options' => array(
                    'route' => '/a_dMin/edit-place[/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ),
                    'defaults' => array(
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-place',
                    ),
                ),
            ),'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/admin[/:action]',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'toursAdmin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/tours[/:action]',
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'add-places' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-places',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-places',
                    ],
                ],
            ],
            'admin-banners' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/banners',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'banners',
                    ],
                ],
            ],
            'admin-delete-banners' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-banner',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'deleteBanner',
                    ],
                ],
            ],
            'load-payment-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/load-payment-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'loadPaymentList',
                    ],
                ],
            ],
            'apply-discount' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/apply-discount',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'apply-discount',
                    ],
                ],
            ],
            'tour-operator-update' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/tour-operator-update',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'tour-operator-update',
                    ],
                ],
            ],
            'add-country' => [
                    'type' => Segment::class,
                    'options' => [
                    'route' => '/a_dMin/add-country',
                    'defaults' => [
                      'controller' => Controller\AdminController::class,
                      'action' => 'add-country',
                    ],
                 ],
           ],
            'add-city-salb-days' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-city-slab-days',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-city-slab-days',
                    ],
                ],
            ],  'edit-ssc' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-ssc',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-ssc',
                    ],
                ],
            ], 'add-language' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-language',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-language',
                    ],
                ],
            ], 'edit-country' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-country[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-country',
                    ],
                ],
            ] , 'edit-state' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-state[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-state',
                    ],
                ],
            ], 'edit-city' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-city[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-city',
                    ],
                ],
            ], 'change-password' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/change-password',

                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'change-password',
                    ],
                ],
            ] ,'edit-seasonal-special' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-seasonal-special[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-seasonal-special',
                    ],
                ],
            ],'admin-country-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/country-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'country-list',
                    ],
                ],
            ],  'add-state' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-state',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-state',
                    ],
                ],
            ],  'state-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/state-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'state-list',
                    ],
                ],
            ],  'add-city' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-city',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-city',
                    ],
                ],
            ], 'edit-city-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-city-tour[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'edit-city-tour',
                    ],
                ],
            ],  'upcoming-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/upcoming-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'upcoming-list',
                    ],
                ],
            ], 'add-upcoming' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-upcoming',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-upcoming',
                    ],
                ],
            ],'edit-notify' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-notify[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-notify',
                    ],
                ],
            ],'edit-upcoming' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-upcoming[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-upcoming',
                    ],
                ],
            ],'delete-upcoming' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-upcoming[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-upcoming',
                    ],
                ],
            ], 'city-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/city-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'city-list',
                    ],
                ],
            ], 'places-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/places-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'places-list',
                    ],
                ],
            ], 'spiritual-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/spiritual-tour-list',
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'spiritual-tour-list',
                    ],
                ],
            ],'add-spiritual-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-spiritual-tour',
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'add-spiritual-tour',
                    ],
                ],
            ], 'city-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/city-tour-list',
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'city-tour-list',
                    ],
                ],
            ],'add-city-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-city-tour',
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'add-city-tour',
                    ],
                ],
            ],'india-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/india-tour-list',
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'india-tour-list',
                    ],
                ],
            ],'add-india-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-india-tour',
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'add-india-tour',
                    ],
                ],
            ],'world-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/world-tour-list',
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'world-tour-list',
                    ],
                ],
            ],'add-world-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-world-tour',
                    'defaults' => [
                        'controller' => Controller\ToursController::class,
                        'action' => 'add-world-tour',
                    ],
                ],
            ],'download-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/download-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'download-list',
                    ],
                ],
            ],/* 'booking-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/booking-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'booking-list',
                    ],
                ],
            ], */'slab-pricing-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/slab-pricing-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'slab-pricing-list',
                    ],
                ],
            ],'parameters-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/parameters-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'parameters-list',
                    ],
                ],
            ],'pricing-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/pricing-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'pricing-list',
                    ],
                ],
            ],'edit-pricing' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-pricing[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-pricing',
                    ],
                ],
            ],'users-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/users-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'users-list',
                    ],
                ],
            ],'admin-user-downloads' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/user-downloads[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'user-downloads',
                    ],
                ],
            ],'sponsors-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/sponsors-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'sponsors-list',
                    ],
                ],
            ],'edit-sponsor' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-sponsor[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'editsponsor',
                    ],
                ],
            ], 'ta-plans' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/ta-plans',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'ta-plans',
                    ],
                ],
            ],'load-all-ta-plans' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/load-all-ta-plans',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'load-all-ta-plans',
                    ],
                ],
            ],'add-ta-plan' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-ta-plan',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-ta-plan',
                    ],
                ],
            ], 'edit-ta-plan' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-ta-plan[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-ta-plan',
                    ],
                ],
            ],'delete-ta-plan' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-ta-plan[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-ta-plan',
                    ],
                ],
            ], 'ta-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/ta-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'ta-list',
                    ],
                ],
            ],'load-ta-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/load-ta-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'load-ta-list',
                    ],
                ],
            ],'add-ta' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-ta',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-ta',
                    ],
                ],
            ], 'edit-ta' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-ta[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-ta',
                    ],
                ],
            ], 'delete-ta' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-ta[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-ta',
                    ],
                ],
            ], 'get-se-name' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/get-se-name',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'get-se-name',
                    ],
                ],
            ],  'buy-plan' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/buy-plan',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'buy-plan',
                    ],
                ],
            ], 'se-payments' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/se-payments',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'se-payments',
                    ],
                ],
            ], 'pay-se' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/pay-se',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'pay-se',
                    ],
                ],
            ], 'tbe-tourists' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/tbe-tourists[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'tbe-tourists',
                    ],
                ],
            ],'load-tbe-tourists' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/load-tbe-tourists[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'load-tbe-tourists',
                    ],
                ],
            ], 'tbe-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/tbe-list[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'tbe-list',
                    ],
                ],
            ],'load-tbe-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/load-tbe-list[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'load-tbe-list',
                    ],
                ],
            ],'add-tbe' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-tbe',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-tbe',
                    ],
                ],
            ], 'edit-tbe' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-tbe[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-tbe',
                    ],
                ],
            ], 'delete-tbe' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-tbe[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-tbe',
                    ],
                ],
            ],'ta-cons' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/ta-cons',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'ta-cons',
                    ],
                ],
            ],'load-ta-cons' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/load-ta-cons',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'load-ta-cons',
                    ],
                ],
            ],'add-ta-sds' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-ta-sds',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-ta-sds',
                    ],
                ],
            ],'add-ta-cons' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-ta-cons',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-ta-cons',
                    ],
                ],
            ], 'edit-ta-cons' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-ta-cons[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-ta-cons',
                    ],
                ],
            ], 'delete-ta-cons' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-ta-cons[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-ta-cons',
                    ],
                ],
            ],'admin-se-tas' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/se-tas[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'se-tas',
                    ],
                ],
            ],'admin-seta-transactions' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/seta-transactions[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'seta-transactions',
                    ],
                ],
            ],'ta-purchases' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/ta-purchases',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'ta-purchases',
                    ],
                ],
            ],'load-ta-purchases' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/load-ta-purchases',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'load-ta-purchases',
                    ],
                ],
            ],'add-promoter' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-promoter',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-promoter',
                    ],
                ],
            ], 'promoters-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/promoters-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'promoters-list',
                    ],
                ],
            ],'admin-sponsors-performance' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/sponsors-performance[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'sponsors-performance',
                    ],
                ],
            ],'admin-sponsors-transactions' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/sponsors-transactions[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'sponsors-transactions',
                    ],
                ],
            ],'admin-promoters-payments' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/promoters-payments[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'promoters-payments',
                    ],
                ],
            ],'admin-pay-promoter' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/pay-promoter[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'pay-promoter',
                    ],
                ],
            ], 'admin-promoter-parameters' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/promoter-parameters[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'promoter-parameters',
                    ],
                ],
            ], 'edit-promoter-parameters' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-promoter-parameters[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-promoter-parameters',
                    ],
                ],
            ], 'admin-sponsor-disc50' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/sponsor-disc50[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'sponsor-disc50',
                    ],
                ],
            ],'admin-sponsor-terminate' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/sponsor-terminate[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'sponsor-terminate',
                    ],
                ],
            ],'admin-promoter-terminate' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/promoter-terminate[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'promoter-terminate',
                    ],
                ],
            ],'admin-sponsor-passwords' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/sponsor-passwords[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'sponsor-passwords',
                    ],
                ],
            ],'add-slab-price' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-slab-price',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-slab-price',
                    ],
                ],
            ],
            'admin-download-details' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/download-details[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'download-details',
                    ],
                ],
            ],/* 'admin-booking-details' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/booking-details[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'booking-details',
                    ],
                ],
            ] */'tour-operator-details' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/tour-operator-details[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'tourOperatorDetails',
                    ],
                ],
            ],'edit-tour-operator' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-tour-operator[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'editTourOperator',
                    ],
                ],
            ],'tour-operator-list' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/tour-operator-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'tour-operator-list',
                    ],
                ],
            ],'seasonal-specials-list' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/seasonal-specials-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'seasonal-specials-list',
                    ],
                ],
            ],'samples' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/samples',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'samples',
                    ],
                ],
            ],'add-sample-audio' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-sample-audio',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-sample-audio',
                    ],
                ],
            ],'add-seasonal-specials' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-seasonal-specials',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-seasonal-specials',
                    ],
                ],
            ],'add-privilage-user' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-privilage-user',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-privilage-user',
                    ],
                ],
            ],'privilage-user-list' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/privilage-user-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'privilage-user-list',
                    ],
                ],
            ],'inbox' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/inbox',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'inbox',
                    ],
                ],
            ],'inbox-single' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/inbox-single',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'inbox-single',
                    ],
                ],
            ],'financial-statements' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/financial-statements',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'financial-statements',
                    ],
                ],
            ],'load-financial-statements' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/load-financial-statements',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'load-financial-statements',
                    ],
                ],
            ],'payments' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/payments',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'payments',
                    ],
                ],
            ],'payments-single' => [
                'type' =>Segment::class,
                'options' => [
                    'route' => '/a_dMin/payments-single',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'payments-single',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\AdminController::class => InvokableFactory::class,
            Controller\ToursController::class => InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'admin/admin/add' => __DIR__ . '/../view/admin/admin/add.phtml',
            'admin/index/index' => __DIR__ . '/../view/admin/index/index.phtml',
            'admin/tours/index' => __DIR__ . '/../view/admin/tours/index.phtml',
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