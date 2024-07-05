<?php

declare(strict_types=1);

namespace User;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Authentication\AuthenticationService;

class Module implements ConfigProviderInterface
{
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\IndexController::class => function ($container) {
                    return new Controller\IndexController(
                        $container->get(AuthenticationService::class),
                        $container->get(\Laminas\Db\Adapter\Adapter::class),
                        $container->get(\Admin\Model\LanguageTable::class),
                        $container->get(\Admin\Model\CountriesTable::class),
                        $container->get(\Admin\Model\StatesTable::class),
                        $container->get(\Admin\Model\CitiesTable::class),
                        $container->get(\Admin\Model\PlacesTable::class),
                        $container->get(\Admin\Model\TourTalesTable::class),
                        $container->get(\Admin\Model\TemporaryFilesTable::class),
                        $container->get(\Admin\Model\TourismFilesTable::class),
                        $container->get(\Admin\Model\AppParameterTable::class),
                        $container->get(\Admin\Model\SubscriptionPlanTable::class),
                        $container->get(\Admin\Model\QuesttSubscriptionTable::class),
                        $container->get(\Admin\Model\UserTable::class),
                        $container->get(\Admin\Model\BannerTable::class),
                        $container->get(\Admin\Model\ExecutiveDetailsTable::class),
                        $container->get(\Admin\Model\ExecutivePurchaseTable::class),
                        $container->get(\Admin\Model\ExecutiveTransactionTable::class),
                        $container->get(\Admin\Model\OtpTable::class),
                        $container->get(\Admin\Model\CouponsTable::class),
                        $container->get(\Admin\Model\EnablerTable::class),
                        $container->get(\Admin\Model\EnablerPurchaseTable::class),
                        $container->get(\Admin\Model\EnablerSalesTable::class),
                        $container->get(\Admin\Model\EnablerPlansTable::class)
                    );
                },
            ],
        ];
    }
}
