<?php
return array (
  'service_manager' => 
  array (
    'aliases' => 
    array (
      'HttpRouter' => 'Zend\\Router\\Http\\TreeRouteStack',
      'router' => 'Zend\\Router\\RouteStackInterface',
      'Router' => 'Zend\\Router\\RouteStackInterface',
      'RoutePluginManager' => 'Zend\\Router\\RoutePluginManager',
      'Zend\\Db\\Adapter\\Adapter' => 'Zend\\Db\\Adapter\\AdapterInterface',
    ),
    'factories' => 
    array (
      'Zend\\Router\\Http\\TreeRouteStack' => 'Zend\\Router\\Http\\HttpRouterFactory',
      'Zend\\Router\\RoutePluginManager' => 'Zend\\Router\\RoutePluginManagerFactory',
      'Zend\\Router\\RouteStackInterface' => 'Zend\\Router\\RouterFactory',
      'ValidatorManager' => 'Zend\\Validator\\ValidatorPluginManagerFactory',
      'Zend\\Db\\Adapter\\AdapterInterface' => 'Zend\\Db\\Adapter\\AdapterServiceFactory',
    ),
    'abstract_factories' => 
    array (
      0 => 'Zend\\Db\\Adapter\\AdapterAbstractServiceFactory',
    ),
  ),
  'route_manager' => 
  array (
  ),
  'router' => 
  array (
    'routes' => 
    array (
      'home' => 
      array (
        'type' => 'Zend\\Router\\Http\\Literal',
        'options' => 
        array (
          'route' => '/',
          'defaults' => 
          array (
            'controller' => 'Application\\Controller\\IndexController',
            'action' => 'index',
          ),
        ),
      ),
      'application' => 
      array (
        'type' => 'Zend\\Router\\Http\\Segment',
        'options' => 
        array (
          'route' => '/application[/:action]',
          'defaults' => 
          array (
            'controller' => 'Application\\Controller\\IndexController',
            'action' => 'index',
          ),
        ),
      ),
      'album' => 
      array (
        'type' => 'Zend\\Router\\Http\\Segment',
        'options' => 
        array (
          'route' => '/album/album[/:action]',
          'defaults' => 
          array (
            'controller' => 'Album\\Controller\\AdminController',
            'action' => 'index',
          ),
        ),
      ),
      'index' => 
      array (
        'type' => 'Zend\\Router\\Http\\Segment',
        'options' => 
        array (
          'route' => '/album/index[/:action]',
          'defaults' => 
          array (
            'controller' => 'Album\\Controller\\IndexController',
            'action' => 'index',
          ),
        ),
      ),
    ),
  ),
  'controllers' => 
  array (
    'factories' => 
    array (
      'Application\\Controller\\IndexController' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
      'Album\\Controller\\IndexController' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
      'Album\\Controller\\AdminController' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
    ),
  ),
  'view_manager' => 
  array (
    'display_not_found_reason' => true,
    'display_exceptions' => true,
    'doctype' => 'HTML5',
    'not_found_template' => 'error/404',
    'exception_template' => 'error/index',
    'template_map' => 
    array (
      'layout/layout' => 'C:\\wamp64\\www\\zf3_skeleton\\module\\Application\\config/../view/layout/layout.phtml',
      'application/index/index' => 'C:\\wamp64\\www\\zf3_skeleton\\module\\Application\\config/../view/application/index/index.phtml',
      'error/404' => 'C:\\wamp64\\www\\zf3_skeleton\\module\\Application\\config/../view/error/404.phtml',
      'error/index' => 'C:\\wamp64\\www\\zf3_skeleton\\module\\Application\\config/../view/error/index.phtml',
      'album/album/add' => 'C:\\wamp64\\www\\zf3_skeleton\\module\\Album\\config/../view/album/album/add.phtml',
      'album/index/index' => 'C:\\wamp64\\www\\zf3_skeleton\\module\\Album\\config/../view/album/index/index.phtml',
    ),
    'template_path_stack' => 
    array (
      0 => 'C:\\wamp64\\www\\zf3_skeleton\\module\\Application\\config/../view',
      1 => 'C:\\wamp64\\www\\zf3_skeleton\\module\\Album\\config/../view',
    ),
    'strategies' => 
    array (
      0 => 'ViewJsonStrategy',
    ),
  ),
  'db' => 
  array (
    'driver' => 'Pdo',
    'dsn' => 'mysql:dbname=zf3;host=localhost',
    'username' => 'root',
    'password' => '',
    'driver_options' => 
    array (
      1002 => 'SET NAMES \'UTF8\', time_zone = "+00:00"',
    ),
  ),
);