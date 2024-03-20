<?php

namespace Admin;

use Laminas\Router\Http\Segment;
use Laminas\Router\Http\Literal;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

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
                    'route' => '/a_dMin/edit-bunched-tour',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'edit-bunched-tour',
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
            'add-free-tour' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/a_dMin/add-free-tour',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'add-free-tour',
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
                    'route' => '/a_dMin/edit-subscription-plan',
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
        'template_path_stack' => [
            'admin' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
