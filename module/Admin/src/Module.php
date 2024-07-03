<?php

namespace Admin;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Authentication\AuthenticationService;

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
                    return new TableGateway('subscription_plan', $dbAdapter, null, $resultSetPrototype);
                },
                Model\QuesttSubscriptionTable::class => function ($container) {
                    $tableGateway = $container->get(Model\QuesttSubscriptionTableGateway::class);
                    return new Model\QuesttSubscriptionTable($tableGateway);
                },
                Model\QuesttSubscriptionTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\QuesttSubscription());
                    return new TableGateway('questt_subscription', $dbAdapter, null, $resultSetPrototype);
                },
                Model\BannerTable::class => function ($container) {
                    $tableGateway = $container->get(Model\BannerTableGateway::class);
                    return new Model\BannerTable($tableGateway);
                },
                Model\BannerTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Banner());
                    return new TableGateway('banner', $dbAdapter, null, $resultSetPrototype);
                }, 
                Model\ExecutiveDetailsTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ExecutiveDetailsTableGateway::class);
                    return new Model\ExecutiveDetailsTable($tableGateway);
                },
                Model\ExecutiveDetailsTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\ExecutiveDetails());
                    return new TableGateway('executive_details', $dbAdapter, null, $resultSetPrototype);
                }, 
                Model\ExecutivePurchaseTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ExecutivePurchaseTableGateway::class);
                    return new Model\ExecutivePurchaseTable($tableGateway);
                },
                Model\ExecutivePurchaseTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\ExecutivePurchase());
                    return new TableGateway('executive_purchase', $dbAdapter, null, $resultSetPrototype);
                },
                Model\ExecutiveTransactionTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ExecutiveTransactionTableGateway::class);
                    return new Model\ExecutiveTransactionTable($tableGateway);
                },
                Model\ExecutiveTransactionTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\ExecutiveTransaction());
                    return new TableGateway('executive_transaction', $dbAdapter, null, $resultSetPrototype);
                },
                Model\OtpTable::class => function ($container) {
                    $tableGateway = $container->get(Model\OtpTableGateway::class);
                    return new Model\OtpTable($tableGateway);
                },
                Model\OtpTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Otp());
                    return new TableGateway('otp', $dbAdapter, null, $resultSetPrototype);
                },
                Model\CouponsTable::class => function ($container) {
                    $tableGateway = $container->get(Model\CouponsTableGateway::class);
                    return new Model\CouponsTable($tableGateway);
                },
                Model\CouponsTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Coupons());
                    return new TableGateway('coupons', $dbAdapter, null, $resultSetPrototype);
                },
                Model\EnablerTable::class => function ($container) {
                    $tableGateway = $container->get(Model\EnablerTableGateway::class);
                    return new Model\EnablerTable($tableGateway);
                },
                Model\EnablerTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Enabler());
                    return new TableGateway('enabler', $dbAdapter, null, $resultSetPrototype);
                },
                Model\EnablerPurchaseTable::class => function ($container) {
                    $tableGateway = $container->get(Model\EnablerPurchaseTableGateway::class);
                    return new Model\EnablerPurchaseTable($tableGateway);
                },
                Model\EnablerPurchaseTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\EnablerPurchase());
                    return new TableGateway('enabler_purchase', $dbAdapter, null, $resultSetPrototype);
                },
                Model\EnablerSalesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\EnablerSalesTableGateway::class);
                    return new Model\EnablerSalesTable($tableGateway);
                },
                Model\EnablerSalesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\EnablerSales());
                    return new TableGateway('enabler_sales', $dbAdapter, null, $resultSetPrototype);
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
                        $container->get(AuthenticationService::class),
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
                        $container->get(Model\QuesttSubscriptionTable::class),
                        $container->get(Model\UserTable::class),
                        $container->get(Model\BannerTable::class),
                        $container->get(Model\ExecutiveDetailsTable::class),
                        $container->get(Model\ExecutivePurchaseTable::class),
                        $container->get(Model\ExecutiveTransactionTable::class),
                        $container->get(Model\OtpTable::class),
                        $container->get(Model\CouponsTable::class),
                        $container->get(Model\EnablerTable::class),
                        $container->get(Model\EnablerPurchaseTable::class),
                        $container->get(Model\EnablerSalesTable::class)
                    );
                },
            ],
        ];
    }
}
