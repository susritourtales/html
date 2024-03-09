<?php

namespace Admin;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\LanguageTable::class => function ($container) {
                    $tableGateway = $container->get(Model\LanguageTableGateway::class);
                    return new Model\LanguageTable($tableGateway);
                },
                Model\LanguageTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Language());
                    return new TableGateway('language', $dbAdapter, null, $resultSetPrototype);
                },
                Model\CountriesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\CountriesTableGateway::class);
                    return new Model\CountriesTable($tableGateway);
                },
                Model\CountriesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Countries());
                    return new TableGateway('country', $dbAdapter, null, $resultSetPrototype);
                },
                Model\StatesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\StatesTableGateway::class);
                    return new Model\StatesTable($tableGateway);
                },
                Model\StatesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\States());
                    return new TableGateway('state', $dbAdapter, null, $resultSetPrototype);
                },
                Model\CitiesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\CitiesTableGateway::class);
                    return new Model\CitiesTable($tableGateway);
                },
                Model\CitiesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Cities());
                    return new TableGateway('city', $dbAdapter, null, $resultSetPrototype);
                },
                Model\PlacesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\PlacesTableGateway::class);
                    return new Model\PlacesTable($tableGateway);
                },
                Model\PlacesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Places());
                    return new TableGateway('place', $dbAdapter, null, $resultSetPrototype);
                },
                Model\TourTalesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\TourTalesTableGateway::class);
                    return new Model\TourTalesTable($tableGateway);
                },
                Model\TourTalesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\TourTales());
                    return new TableGateway('tour_tale', $dbAdapter, null, $resultSetPrototype);
                },
                Model\UserTable::class => function ($container) {
                    $tableGateway = $container->get(Model\UserTableGateway::class);
                    return new Model\UserTable($tableGateway);
                },
                Model\UserTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\User());
                    return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
                },
                Model\TemporaryFilesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\TemporaryFilesTableGateway::class);
                    return new Model\TemporaryFilesTable($tableGateway);
                },
                Model\TemporaryFilesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\TemporaryFiles());
                    return new TableGateway('temporary_files', $dbAdapter, null, $resultSetPrototype);
                },
                Model\TourismFilesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\TourismFilesTableGateway::class);
                    return new Model\TourismFilesTable($tableGateway);
                },
                Model\TourismFilesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\TourismFiles());
                    return new TableGateway('tourism_files', $dbAdapter, null, $resultSetPrototype);
                },
                Model\AppParameterTable::class => function ($container) {
                    $tableGateway = $container->get(Model\AppParameterTableGateway::class);
                    return new Model\AppParameterTable($tableGateway);
                },
                Model\AppParameterTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\AppParameter());
                    return new TableGateway('app_parameter', $dbAdapter, null, $resultSetPrototype);
                },
                Model\SubscriptionPlanTable::class => function ($container) {
                    $tableGateway = $container->get(Model\SubscriptionPlanTableGateway::class);
                    return new Model\SubscriptionPlanTable($tableGateway);
                },
                Model\SubscriptionPlanTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\SubscriptionPlan());
                    return new TableGateway('app_parameter', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\AdminController::class => function ($container) {
                    return new Controller\AdminController(
                        $container->get(\Laminas\Authentication\AuthenticationService::class),
                        $container->get(\Laminas\Db\Adapter\Adapter::class),
                        $container->get(Model\LanguageTable::class),
                        $container->get(Model\CountriesTable::class),
                        $container->get(Model\StatesTable::class),
                        $container->get(Model\CitiesTable::class),
                        $container->get(Model\PlacesTable::class),
                        $container->get(Model\TourTalesTable::class),
                        $container->get(Model\TemporaryFilesTable::class),
                        $container->get(Model\TourismFilesTable::class),
                        $container->get(Model\AppParameterTable::class),
                        $container->get(Model\SubscriptionPlanTable::class),
                        $container->get(Model\UserTable::class)
                    );
                },
            ],
        ];
    }
}
