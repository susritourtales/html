<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
/**
 * List of enabled modules for this application.
 *
 * This should be an array of module namespaces used in the application.
 */
return [
    'Laminas\ZendFrameworkBridge',
    'Laminas\Mail',
    'Laminas\ServiceManager\Di',
    'Laminas\Mvc\Plugin\FilePrg',
    'Laminas\Form',
    'Laminas\Hydrator',
    'Laminas\InputFilter',
    'Laminas\Filter',
    'Laminas\Mvc\Plugin\FlashMessenger',
    'Laminas\Mvc\Plugin\Identity',
    'Laminas\Mvc\Plugin\Prg',
    'Laminas\Session',
    'Laminas\Mvc\I18n',
    'Laminas\I18n',
    'Laminas\Mvc\Console',
    'Laminas\Log',
    'Laminas\Router',
    'Laminas\Validator',
    'AwsModule',
    'Application',
    'Admin',
    'Api',
    'Laminas\Db',
    'User'
];
