<?php

declare(strict_types=1);

namespace Application;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
class Module
{
    public function getConfig() //: array
    {
        /* @var array $config */
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
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
            if (isset($configs['module_layouts'][$moduleNamespace])) {
                $controller->layout($configs['module_layouts'][$moduleNamespace]);
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
