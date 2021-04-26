<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Admin\Model\Cities;
use Admin\Model\CitiesTable;
use Admin\Model\Countries;
use Admin\Model\CountriesTable;
use Admin\Model\Fcm;
use Admin\Model\FcmTable;
use Admin\Model\Languages;
use Admin\Model\LanguagesTable;
use Admin\Model\States;
use Admin\Model\StatesTable;
use Admin\Model\TourismPlaceFiles;
use Admin\Model\TourismPlaceFilesTable;
use Admin\Model\TourismPlaces;
use Admin\Model\TourismPlacesTable;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;


class Module
{
    const VERSION = '3.0.3-dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager  = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $eventManager->getSharedManager()->attach('Laminas\Mvc\Controller\AbstractActionController', 'dispatch', function ($event) {
            $controller = $event->getTarget();
            $controllerName = get_class($controller);
            $moduleNamespace = substr($controllerName, 0, strpos($controllerName, '\\'));
            $configs = $event->getApplication()->getServiceManager()->get('config');
            if (isset($configs['moduleLayouts'][$moduleNamespace])) {
                $controller->layout($configs['moduleLayouts'][$moduleNamespace]);
            }
        }, 100);
    }

    public function getServiceConfig()
    {

        return [
            'factories' => [
                'Application\Channel\Mail' => function ($serviceManager)
                {   
                    $transport = $serviceManager->get('mail.transport');
                    /*$mailer = new \Application\Channel\Mail($transport, $serviceManager->get('Application\Logger'));*/
                    $mailer = new \Application\Channel\Mail($transport);
                    return $mailer;
                },
                'mail.transport' => function ($serviceManager)
                {  
                    $config = $serviceManager->get('Config');
                    $transport = new \Laminas\Mail\Transport\Smtp();
                    $transport->setOptions(new \Laminas\Mail\Transport\SmtpOptions($config['mail']['transport']['options']));
                    return $transport;
                },
            ],
        ];
    }
}
