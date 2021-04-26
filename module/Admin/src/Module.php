<?php

namespace Admin;


use Admin\Model\Banners;
use Admin\Model\BannersTable;
use Admin\Model\Bookings;
use Admin\Model\BookingsTable;
use Admin\Model\BookingTourDetails;
use Admin\Model\BookingTourDetailsTable;
use Admin\Model\Cities;
use Admin\Model\CitiesTable;
use Admin\Model\CityTourSlabDays;
use Admin\Model\CityTourSlabDaysTable;
use Admin\Model\Countries;
use Admin\Model\CountriesTable;
use Admin\Model\DownloadedFileInfo;
use Admin\Model\DownloadedFileInfoTable;
use Admin\Model\Fcm;
use Admin\Model\FcmTable;
use Admin\Model\Inbox;
use Admin\Model\InboxTable;
use Admin\Model\Languages;
use Admin\Model\LanguagesTable;
use Admin\Model\Likes;
use Admin\Model\LikesTable;
use Admin\Model\Notification;
use Admin\Model\NotificationTable;
use Admin\Model\Otp;
use Admin\Model\OtpTable;
use Admin\Model\Password;
use Admin\Model\PasswordTable;
use Admin\Model\Payments;
use Admin\Model\PaymentsTable;
use Admin\Model\PlacePrices;
use Admin\Model\PlacePricesTable;
use Admin\Model\PriceSlab;
use Admin\Model\PriceSlabTable;
use Admin\Model\SeasonalSpecials;
use Admin\Model\SeasonalSpecialsTable;
use Admin\Model\States;
use Admin\Model\StatesTable;
use Admin\Model\TemporaryFiles;
use Admin\Model\TemporaryFilesTable;
use Admin\Model\TourCoordinatorDetails;
use Admin\Model\TourCoordinatorDetailsTable;
use Admin\Model\TourismFiles;
use Admin\Model\TourismFilesTable;
use Admin\Model\TourismPlaces;
use Admin\Model\TourismPlacesTable;
use Admin\Model\TourOperatorDetails;
use Admin\Model\TourOperatorDetailsTable;
use Admin\Model\User;
use Admin\Model\UserTable;
use Admin\Model\Pricing;   // -- Added my Manjary
use Admin\Model\PricingTable;    // -- Added my Manjary
use Admin\Model\SponsorTypes;   // -- Added my Manjary
use Admin\Model\SponsorTypesTable;    // -- Added my Manjary
use Admin\Model\SponsorPhoto;    // -- Added my Manjary
use Admin\Model\SponsorPhotoTable;    // -- Added my Manjary
use Admin\Model\Upcoming;  // -- Added my Manjary
use Admin\Model\UpcomingTable;  // -- Added my Manjary
use Admin\Model\Refer;  // -- Added my Manjary
use Admin\Model\ReferTable;  // -- Added my Manjary
use Admin\Model\Ssc;  // -- Added my Manjary
use Admin\Model\SscTable;  // -- Added my Manjary
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Session\Config\SessionConfig;
use Laminas\Session\Container;
use Laminas\Authentication\Storage\Session;

use Laminas\Session\SaveHandler\DbTableGateway;
use Laminas\Authentication\AuthenticationService;

use Laminas\Session\SaveHandler\DbTableGatewayOptions;
use Laminas\Session\SessionManager;
use Laminas\Authentication\Adapter\DbTable as AuthDbTable;

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
                "Admin/Model/Countries" => function ($sm) {
                    $tableGateway = $sm->get('CountriesTableGateway');
                    $table = new CountriesTable($tableGateway);
                    return $table;
                },
                'CountriesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Countries());
                    return new TableGateway('countries', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/Notification" => function ($sm) {
                    $tableGateway = $sm->get('NotificationTableGateway');
                    $table = new NotificationTable($tableGateway);
                    return $table;
                },
                'NotificationTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Notification());
                    return new TableGateway('notification', $dbAdapter, null, $resultSetPrototype);
                }, "Admin/Model/likes" => function ($sm) {
                    $tableGateway = $sm->get('likesTableGateway');
                    $table = new LikesTable($tableGateway);
                    return $table;
                },
                'likesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Likes());
                    return new TableGateway('likes', $dbAdapter, null, $resultSetPrototype);
                }, "Admin/Model/DownloadedFileInfo" => function ($sm) {
                    $tableGateway = $sm->get('DownloadedFileInfoTableGateway');
                    $table = new DownloadedFileInfoTable($tableGateway);
                    return $table;
                },
                'DownloadedFileInfoTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new DownloadedFileInfo());
                    return new TableGateway('downloaded_file_info', $dbAdapter, null, $resultSetPrototype);
                }, /* "Admin/Model/TourOperatorDetails" => function ($sm) {
                    $tableGateway = $sm->get('TourOperatorDetailsTableGateway');
                    $table = new TourOperatorDetailsTable($tableGateway);
                    return $table;
                },
                'TourOperatorDetailsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new TourOperatorDetails());
                    return new TableGateway('tour_operator_details', $dbAdapter, null, $resultSetPrototype);
                }, "Admin/Model/TourCoordinatorDetails" => function ($sm) {
                    $tableGateway = $sm->get('TourCoordinatorDetailsTableGateway');
                    $table = new TourCoordinatorDetailsTable($tableGateway);
                    return $table;
                },
                'TourCoordinatorDetailsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new TourCoordinatorDetails());
                    return new TableGateway('tour_coordinator_details', $dbAdapter, null, $resultSetPrototype);
                }, */ "Admin/Model/SeasonalSpecials" => function ($sm) {
                    $tableGateway = $sm->get('SeasonalSpecialsTableGateway');
                    $table = new SeasonalSpecialsTable($tableGateway);
                    return $table;
                },
                'SeasonalSpecialsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new SeasonalSpecials());
                    return new TableGateway('seasonal_specials', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/States" => function ($sm) {
                    $tableGateway = $sm->get('StatesTableGateway');
                    $table = new StatesTable($tableGateway);
                    return $table;
                },
                'StatesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new States());
                    return new TableGateway('states', $dbAdapter, null, $resultSetPrototype);
                },  "Admin/Model/PriceSlab" => function ($sm) {
                    $tableGateway = $sm->get('PriceSlabTableGateway');
                    $table = new PriceSlabTable($tableGateway);
                    return $table;
                },
                'PriceSlabTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new PriceSlab());
                    return new TableGateway('price_slab', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/Bookings" => function ($sm) {
                    $tableGateway = $sm->get('BookingsTableGateway');
                    $table = new BookingsTable($tableGateway);
                    return $table;
                },
                'BookingsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Bookings());
                    return new TableGateway('bookings', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/InboxTable" => function ($sm) {
                    $tableGateway = $sm->get('InboxTableGateway');
                    $table = new InboxTable($tableGateway);
                    return $table;
                },
                'InboxTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Inbox());
                    return new TableGateway('inbox', $dbAdapter, null, $resultSetPrototype);
                }, 
                "Admin/Model/BookingTourDetails" => function ($sm) {
                    $tableGateway = $sm->get('BookingTourDetailsTableGateway');
                    $table = new BookingTourDetailsTable($tableGateway);
                    return $table;
                },
                'BookingTourDetailsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new BookingTourDetails());
                    return new TableGateway('booking_tour_details', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/Cities" => function ($sm) {
                    $tableGateway = $sm->get('CitiesTableGateway');
                    $table = new CitiesTable($tableGateway);
                    return $table;
                },
                'CitiesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Cities());
                    return new TableGateway('cities', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/UserTable" => function ($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm)
                {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/UpcomingTable" => function ($sm) {    // -- added by Manjary
                    $tableGateway = $sm->get('UpcomingTableGateway');
                    $table = new UpcomingTable($tableGateway);
                    return $table;
                },
                'UpcomingTableGateway' => function ($sm)      // -- added by Manjary
                {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Upcoming());
                    return new TableGateway('upcoming', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/ReferTable" => function ($sm) {    // -- added by Manjary
                    $tableGateway = $sm->get('ReferTableGateway');
                    $table = new ReferTable($tableGateway);
                    return $table;
                },
                'ReferTableGateway' => function ($sm)      // -- added by Manjary
                {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Refer());
                    return new TableGateway('refer', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/PricingTable" => function ($sm) {    // -- added by Manjary
                    $tableGateway = $sm->get('PricingTableGateway');
                    $table = new PricingTable($tableGateway);
                    return $table;
                },
                'PricingTableGateway' => function ($sm)      // -- added by Manjary
                {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Pricing());
                    return new TableGateway('pricing', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/SponsorTypesTable" => function ($sm) {    // -- added by Manjary
                    $tableGateway = $sm->get('SponsorTypesTableGateway');
                    $table = new SponsorTypesTable($tableGateway);
                    return $table;
                },
                'SponsorTypesTableGateway' => function ($sm)      // -- added by Manjary
                {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new SponsorTypes());
                    return new TableGateway('sponsor_types', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/SscTable" => function ($sm) {    // -- added by Manjary
                    $tableGateway = $sm->get('SscTableGateway');
                    $table = new SscTable($tableGateway);
                    return $table;
                },
                'SscTableGateway' => function ($sm)      // -- added by Manjary
                {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Ssc());
                    return new TableGateway('ssc', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/SponsorPhotoTable" => function ($sm) {    // -- added by Manjary
                    $tableGateway = $sm->get('SponsorPhotoTableGateway');
                    $table = new SponsorPhotoTable($tableGateway);
                    return $table;
                },
                'SponsorPhotoTableGateway' => function ($sm)      // -- added by Manjary
                {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new SponsorPhoto());
                    return new TableGateway('sponsor_photo', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/Languages" => function ($sm) {
                    $tableGateway = $sm->get('LanguagesTableGateway');
                    $table = new LanguagesTable($tableGateway);
                    return $table;
                },
                'LanguagesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Languages());
                    return new TableGateway('languages', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/PlacePrices" => function ($sm) {
                    $tableGateway = $sm->get('PlacePricesTableGateway');
                    $table = new PlacePricesTable($tableGateway);
                    return $table;
                },
                'PlacePricesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new PlacePrices());
                    return new TableGateway('place_prices', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/TourismPlaces" => function ($sm) {
                    $tableGateway = $sm->get('TourismPlacesTableGateway');
                    $table = new TourismPlacesTable($tableGateway);
                    return $table;
                },
                'TourismPlacesTableGateway' => function($sm){
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new TourismPlaces());
                    return new TableGateway('tourism_places', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/TourismFiles" => function ($sm){
                    $tableGateway = $sm->get('TourismFilesTableGateway');
                    $table = new TourismFilesTable($tableGateway);
                    return $table;
                },
                'TourismFilesTableGateway' => function ($sm){
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new TourismFiles());
                    return new TableGateway('tourism_files', $dbAdapter, null, $resultSetPrototype);
                },"Admin/Model/Fcm" => function ($sm) {
                    $tableGateway = $sm->get('FcmTableGateway');
                    $table = new FcmTable($tableGateway);
                    return $table;
                },
                'FcmTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Fcm());
                    return new TableGateway('fcm', $dbAdapter, null, $resultSetPrototype);
                }, "Admin/Model/Password" => function ($sm) {
                    $tableGateway = $sm->get('PasswordTableGateway');
                    $table = new PasswordTable($tableGateway);
                    return $table;
                },
                'PasswordTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Password());
                    return new TableGateway('password', $dbAdapter, null, $resultSetPrototype);
                },
                "Admin/Model/OtpTable" => function ($sm) {
                    $tableGateway = $sm->get('OtpTableGateway');
                    $table = new OtpTable($tableGateway);
                    return $table;
                },
                'OtpTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Otp());
                    return new TableGateway('otp', $dbAdapter, null, $resultSetPrototype);
                },"Admin/Model/TemporaryFilesTable" => function ($sm) {
                    $tableGateway = $sm->get('TemporaryFilesTableGateway');
                    $table = new TemporaryFilesTable($tableGateway);
                    return $table;
                },
                'TemporaryFilesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new TemporaryFiles());
                    return new TableGateway('temporary_files', $dbAdapter, null, $resultSetPrototype);
                },"Admin/Model/PaymentTable" => function ($sm) {
                    $tableGateway = $sm->get('PaymentTableGateway');
                    $table = new PaymentsTable($tableGateway);
                    return $table;
                },
                'PaymentTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Payments());
                    return new TableGateway('payments', $dbAdapter, null, $resultSetPrototype);
                },"Admin/Model/CityTourSlabDaysTable" => function ($sm) {
                    $tableGateway = $sm->get('CityTourSlabDaysTableGateway');
                    $table = new CityTourSlabDaysTable($tableGateway);
                    return $table;
                },
                'CityTourSlabDaysTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new CityTourSlabDays());
                    return new TableGateway('city_tour_slab_days', $dbAdapter, null, $resultSetPrototype);
                },"Admin/Model/BannersTable" => function ($sm) {
                    $tableGateway = $sm->get('BannersTableGateway');
                    $table = new BannersTable($tableGateway);
                    return $table;
                },'BannersTableGateway' => function ($sm){
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Banners());
                    return new TableGateway('banners', $dbAdapter, null, $resultSetPrototype);
                },'AuthDbTable' => function($sm)
                {
                    $dbAdapter      = $sm->get('Laminas\Db\Adapter\Adapter');
                    $authDbTable    = new AuthDbTable($dbAdapter);
                    return $authDbTable;
                },
                'SessionStorage' => function($sm) {
                    $sessionStorage = new Session();
                    return $sessionStorage;
                },
                'AuthService' => function($sm) {
                    $authService    = new AuthenticationService();
                    return $authService;
                },
                'SessionManager' => function($sm) {
                    $sessionManager   = new SessionManager();
                    return $sessionManager;
                },
                'SessionSaveManager' => function($sm) {
                    $dbAdapter           = $sm->get('Laminas\Db\Adapter\Adapter');
                    $sessionTableGateway = new TableGateway('session', $dbAdapter);
                    $optionsTableGateway = new DbTableGatewayOptions();
                    $optionsTableGateway -> setIdColumn('id')
                        ->setNameColumn('name')
                        ->setModifiedColumn('modified')
                        ->setLifetimeColumn('lifetime')
                        ->setDataColumn('data');
                    $config = new SessionConfig();
                    $config->setOptions([
                        'remember_me_seconds' => 99999999999,
                        'gc_maxlifetime' => 99999999999,
                        'use_cookies' => true,
                        'cookie_httponly' => true,
                        'name'=> 'susri',
                    ]);
                    $sessionSaveHandler  = new DbTableGateway($sessionTableGateway, $optionsTableGateway);
                    $sessionManager = new SessionManager();
                    $sessionManager->setSaveHandler($sessionSaveHandler);
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
                'SessionDestroyManager' => function($sm) {
                    $manager      = new SessionManager();
                    $manager->getSaveHandler();
                    return $manager;
                }
            ],
        ];
    }
    public function onBootstrap($e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $sm = $e->getApplication()->getServiceManager();
        $sessionManager = $sm->get('SessionSaveManager');
        $sessionManager->start();
    }


}