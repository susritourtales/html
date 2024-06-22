<?php

namespace Admin;

use Laminas\Router\Http\Segment;
use Laminas\Router\Http\Literal;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'admin' => [
                'type' => Literal::class, //Segment::class,
                'options' => [
                    'route' => '/admin', //'/admin[/:action[/:id]]',
                    /* 'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ), */
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
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
            ],
            'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/a_dMin/logout',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'languages' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/a_dMin/languages[/:action[/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ),
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'languages',
                    ],
                ],
            ],
            'add-language' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-language',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-language',
                    ],
                ],
            ],
            'delete-language' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-language',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-language',
                    ],
                ],
            ],
            'language-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-language-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-language-list',
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
            ],
            'fileUploadRow' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/admin/file-upload-row',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'fileUploadRow',
                    ],
                ],
            ],
            'countries' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/a_dMin/countries',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'countries',
                    ],
                ],
            ],
            'country-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-country-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-country-list',
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
            'edit-country' => [
                'type' => Segment::class,
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
            ],
            'delete-country' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-country[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-country',
                    ],
                ],
            ],
            'states' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/a_dMin/states',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'states',
                    ],
                ],
            ],
            'state-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-state-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-state-list',
                    ],
                ],
            ],
            'add-state' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-state',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-state',
                    ],
                ],
            ],
            'edit-state' => [
                'type' => Segment::class,
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
            ],
            'delete-state' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-state[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-state',
                    ],
                ],
            ],
            'cities' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/a_dMin/cities',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'cities',
                    ],
                ],
            ],
            'city-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-city-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-city-list',
                    ],
                ],
            ],
            'add-city' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-city',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-city',
                    ],
                ],
            ],
            'edit-city' => [
                'type' => Segment::class,
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
            ],
            'delete-city' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-city[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-city',
                    ],
                ],
            ],
            'get-states' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/get-states',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'get-states',
                    ],
                ],
            ],
            'places' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/a_dMin/places',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'places',
                    ],
                ],
            ],
            'places-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-places-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-places-list',
                    ],
                ],
            ],
            'add-place' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-place',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-place',
                    ],
                ],
            ],
            'edit-place' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-place[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-place',
                    ],
                ],
            ],
            'delete-place' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-place[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-place',
                    ],
                ],
            ],
            'get-cities' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/get-cities',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'get-cities',
                    ],
                ],
            ],
            'add-tour-get-places' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/add-tour-get-places',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-tour-get-places',
                    ],
                ],
            ],
            'india-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/india-tour-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'india-tour-list',
                    ],
                ],
            ],
            'load-india-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-india-tour-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-india-tour-list',
                    ],
                ],
            ],
            'add-india-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-india-tour',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-india-tour',
                    ],
                ],
            ],
            'world-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/world-tour-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'world-tour-list',
                    ],
                ],
            ],
            'add-world-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-world-tour',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-world-tour',
                    ],
                ],
            ],
            'load-world-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-world-tour-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-world-tour-list',
                    ],
                ],
            ],
            'bunched-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/bunched-tour-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'bunched-tour-list',
                    ],
                ],
            ],
            'load-bunched-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-bunched-tour-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-bunched-tour-list',
                    ],
                ],
            ],
            'add-bunched-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-bunched-tour',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-bunched-tour',
                    ],
                ],
            ],
            'edit-bunched-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-bunched-tour[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-bunched-tour',
                    ],
                ],
            ],
            'get-places' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/get-places',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'get-places',
                    ],
                ],
            ],
            'get-tales' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/get-tales',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'get-tales',
                    ],
                ],
            ],
            'free-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/free-tour-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'free-tour-list',
                    ],
                ],
            ],
            'load-free-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-free-tour-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-free-tour-list',
                    ],
                ],
            ],
            'add-free-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-free-tour[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-free-tour',
                    ],
                ],
            ],
            'add-free-tour-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/add-free-tour-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'add-free-tour-list',
                    ],
                ],
            ],
            'delete-tour-tale' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/delete-tour-tale',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-tour-tale',
                    ],
                ],
            ],
            'parameters' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/parameters',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'parameters',
                    ],
                ],
            ],
            'twistt-executives' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/executives',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'executives',
                    ],
                ],
            ],
            'executives-list' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/load-executives-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'load-executives-list',
                    ],
                ],
            ],
            'edit-executive' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-executive[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-executive',
                    ],
                ],
            ],
            'modify-executive' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/modify-executive',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'modify-executive',
                    ],
                ],
            ],
            'delete-executive' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/delete-executive[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'delete-executive',
                    ],
                ],
            ],
            'executive-coupons-commissions' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/executive/coupons-commissions[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'coupons-commissions',
                    ],
                ],
            ],
            'load-coupons-commissions' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/a_dMin/load-coupons-commissions-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'load-coupons-commissions-list',
                    ],
                ],
            ],
            'executive-commissions-payments' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/executive/commissions-payments[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'commissions-payments',
                    ],
                ],
            ],
            'load-commissions-payments' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/a_dMin/load-commissions-payments-list',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'load-commissions-payments-list',
                    ],
                ],
            ],
            'change-password' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/change-password',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'change-password',
                    ],
                ],
            ],
            'subscription-plans' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/subscription-plans',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'subscription-plans',
                    ],
                ],
            ],
            'add-subscription-plan' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-subscription-plan',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-subscription-plan',
                    ],
                ],
            ],
            'edit-subscription-plan' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/edit-subscription-plan[/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_~\-!@#\$%\^&*\(\)=]*[/]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-subscription-plan',
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
            Controller\AdminController::class => ReflectionBasedAbstractFactory::class,
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
            'admin/admin/index' => __DIR__ . '/../view/admin/admin/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'module_layouts' => [
        'Admin' => 'layout/layout',
    ],
];
