<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\Db\Adapter\Adapter;
use Laminas\Session\Container as SessionContainer;
use Aws\Exception\AwsException;
use Aws\ResultInterface;
use Aws\CommandPool;
use Aws\Sdk;
use Application\Channel\Sms;

use Admin\Model\LanguageTable;
use Admin\Model\CountriesTable;
use Admin\Model\PlacesTable;
use Admin\Model\StatesTable;
use Admin\Model\CitiesTable;
use Admin\Model\TemporaryFilesTable;
use Admin\Model\TourismFilesTable;
use Admin\Model\TourTalesTable;
use Admin\Model\AppParameterTable;
use Admin\Model\SubscriptionPlanTable;
use Admin\Model\QuesttSubscriptionTable;
use Admin\Model\UserTable;
use Admin\Model\BannerTable;
use Admin\Model\ExecutiveDetailsTable;
use Admin\Model\ExecutivePurchaseTable;
use Admin\Model\ExecutiveTransactionTable;
use Admin\Model\OtpTable;
use Admin\Model\CouponsTable;
use Admin\Model\EnablerTable;
use Admin\Model\EnablerPurchaseTable;
use Admin\Model\EnablerSalesTable;
use Admin\Model\EnablerPlansTable;

class BaseController extends AbstractActionController
{
    protected $authService;
    protected $dbAdapter;
    protected $sessionSaveManager;
    protected $authDbTable;
    protected $sessionStorage;
    protected $sessionManager;
    protected $sessionContainer;
    protected $s3;
    protected $config;
    protected $mailer;

    protected $userTable;
    protected $languageTable;
    protected $bannerTable;
    protected $temporaryFiles;
    protected $tourismFilesTable;
    protected $countriesTable;
    protected $statesTable;
    protected $placesTable;
    protected $citiesTable;
    protected $tourTalesTable;
    protected $appParameterTable;
    protected $subscriptionPlanTable;
    protected $questtSubscriptionTable;
    protected $executiveDetailsTable;
    protected $executivePurchaseTable;
    protected $executiveTransactionTable;
    protected $otpTable;
    protected $couponsTable;
    protected $enablerTable;
    protected $enablerPurchaseTable;
    protected $enablerSalesTable;
    protected $enablerPlansTable;

    public function __construct(
        AuthenticationService $authService,
        Adapter $dbAdapter,
        LanguageTable $language_table,
        CountriesTable $countries_table,
        StatesTable $states_table,
        CitiesTable $cities_table,
        PlacesTable $places_table,
        TourTalesTable $tour_tales_table,
        TemporaryFilesTable $temporary_files_table,
        TourismFilesTable $tourism_files_table,
        AppParameterTable $app_parameter_table,
        SubscriptionPlanTable $subscriptio_plan_table,
        QuesttSubscriptionTable $questt_subscription_table,
        UserTable $user_table,
        BannerTable $banner_table,
        ExecutiveDetailsTable $executive_details,
        ExecutivePurchaseTable $executive_purchase,
        ExecutiveTransactionTable $executive_transaction,
        OtpTable $otp_table,
        CouponsTable $coupons_table,
        EnablerTable $enabler_table,
        EnablerPurchaseTable $enabler_purchase_table,
        EnablerSalesTable $enabler_sales_table,
        EnablerPlansTable $enabler_plans_table,
    ) {
        $this->sessionContainer = new SessionContainer('stt_session');
        $this->sessionManager = $this->sessionContainer->getManager();
        $this->sessionManager->rememberMe(604800); // = 60 * 60 * 24 * 7
        $this->authService = $authService;
        $this->dbAdapter = $dbAdapter;

        $this->languageTable = $language_table;
        $this->countriesTable = $countries_table;
        $this->statesTable = $states_table;
        $this->citiesTable = $cities_table;
        $this->placesTable = $places_table;
        $this->tourTalesTable = $tour_tales_table;
        $this->temporaryFiles = $temporary_files_table;
        $this->tourismFilesTable = $tourism_files_table;
        $this->appParameterTable = $app_parameter_table;
        $this->subscriptionPlanTable = $subscriptio_plan_table;
        $this->questtSubscriptionTable = $questt_subscription_table;
        $this->userTable = $user_table;
        $this->bannerTable = $banner_table;
        $this->executiveDetailsTable = $executive_details;
        $this->executivePurchaseTable = $executive_purchase;
        $this->executiveTransactionTable = $executive_transaction;
        $this->otpTable = $otp_table;
        $this->couponsTable = $coupons_table;
        $this->enablerTable = $enabler_table;
        $this->enablerPurchaseTable = $enabler_purchase_table;
        $this->enablerSalesTable = $enabler_sales_table;
        $this->enablerPlansTable = $enabler_plans_table;
    }

    public function getLoggedInUserId()
    {
        try {
            $user = $this->getLoggedInUser();
            if ($user) {
                return $user['user_login_id'];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getLoggedInUser()
    {
        try {
            $user = $this->getAuthService()->getIdentity();
            return $user;
        } catch (\Exception $e) {
            return array();
        }
    }

    public function getAuthService()
    {
        try {
            if (!$this->authService) {
                $sm = $this->getEvent()->getApplication()->getServiceManager();
                $this->authService = $sm->get('AuthService');
            }
            return $this->authService;
        } catch (\Exception $e) {
            return array();
        }
    }

    public function getAuthDbTable()
    {

        if (!$this->authDbTable)
        {
            $sm = $this->getEvent()->getApplication()->getServiceManager();
            $this->authDbTable = $sm->get('AuthDbTable');
        }
        return $this->authDbTable;
    }

    public function getSessionStorage()
    {
        if (!$this->sessionStorage)
        {
            $sm = $this->getEvent()->getApplication()->getServiceManager();
            $this->sessionStorage = $sm->get('SessionStorage');
        }
        return $this->sessionStorage;
    }

    public function getSessionManager()
    {
        if (!$this->sessionManager)
        {
            $sm = $this->getEvent()->getApplication()->getServiceManager();
            $this->sessionManager = $sm->get('SessionManager');
        }
        return $this->sessionManager;
    }

    public function getSessionSaveManager()
    {
        if (!$this->sessionSaveManager)
        {
            $sm = $this->getEvent()->getApplication()->getServiceManager();
            $this->sessionSaveManager = $sm->get('SessionSaveManager');
        }
        return $this->sessionSaveManager;
    }

    public function getBaseUrl()
    {
        $event = $this->getEvent();
        $request = $event->getRequest();
        $router = $event->getRouter();
        $uri = $router->getRequestUri();
        return $baseUrl = sprintf('%s://%s%s', $uri->getScheme(), $uri->getHost(), $request->getBaseUrl());
    }

    public function checkAdmin()
    {
        if (!$this->authService->hasIdentity()) {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function destroySessionAction()
    {
        // Destroy the session
        $this->sessionManager->destroy();
    }

    public function filesUrl()
    {
        return 'https://s3.amazonaws.com/files.susri/';
    }

    public function getConfig(){
        $serviceLocator = $this->getEvent()->getApplication()->getServiceManager();
        $this->config = $serviceLocator->get('Config');
        return $this->config;
    }
    public function copypushFiles($files)
    {
        $serviceLocator = $this->getEvent()->getApplication()->getServiceManager();
        if ($this->s3 == null || $this->config == null) {
            $this->config = $serviceLocator->get('Config');
            $this->config = isset($this->config['aws']) ? $this->config['aws'] : array();
            $sdk = new Sdk($this->config);
            $this->s3 = $sdk->createS3();
        }

        // header("Content-Type: image/jpg");
        $i = 0;
        $copiedFiles = array();
        $fileIds = array();
        $i = -1;
        $value = array();
        foreach ($files as $file) {
            $i++;
            $fileIds[$i] = $file['id'];
            $value[] = $this->s3->getCommand('CopyObject', [
                'Bucket'     =>  $this->config["bucket"],
                'Key'        => "{$file['new_path']}",
                'CopySource' => "{$this->config["bucket"]}/tmp/{$file['old_path']}",
            ]);
        }

        try {
            if (count($value)) {
                $results = CommandPool::batch($this->s3, $value);
                foreach ($results as $result) {

                    if ($result instanceof ResultInterface) {
                        // Result handling here
                        array_push($copiedFiles, $fileIds[$i]);
                    }
                    if ($result instanceof AwsException) {
                        // AwsException handling here
                    }
                }
                if (count($copiedFiles) == count($fileIds)) {
                    return array('status' => true, 'copied' => $copiedFiles);
                } else {
                    return array('status' => false, 'copied' => $copiedFiles);
                }
            } else {
                return array('status' => true, 'copied' => $copiedFiles);
            }
        } catch (\Exception $e) {
            /* print_r($e->getMessage());
            exit; */
            return false;
        }
        //return true;
    }

    public function pushFiles($fileName, $filepath, $attachmentType)
    {
        try {
            $serviceLocator = $this->getEvent()->getApplication()->getServiceManager();
            if ($this->s3 == null || $this->config == null) {
                $this->config = $serviceLocator->get('Config');
                $this->config = isset($this->config['aws']) ? $this->config['aws'] : array();
                $sdk = new Sdk($this->config);
                $this->s3 = $sdk->createS3();
            }
            // header("Content-Type: image/jpg");
            $value = $this->s3->putObject(array(
                'Bucket' => $this->config["bucket"],
                'Key' =>  $fileName,
                'Body' => fopen($filepath, 'r'),
                'ACL' => 'public-read',
                "Cache-Control" => "max-age=94608000",
                "ContentType" => $attachmentType,
                "signatureVersion" => "v4",
                "Expires" => gmdate(
                    "D, d M Y H:i:s T",
                    strtotime("+36 years")
                ),
                "Metadata" => array(
                    'Expires' => gmdate(
                        "D, d M Y H:i:s T",
                        strtotime("+36 years")
                    )
                )
            ));
            return $value;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function fileExists($filepath){
        try {
            $serviceLocator = $this->getEvent()->getApplication()->getServiceManager();
            if ($this->s3 == null || $this->config == null) {
                $this->config = $serviceLocator->get('Config');
                $this->config = isset($this->config['aws']) ? $this->config['aws'] : array();
                $sdk = new Sdk($this->config);
                $this->s3 = $sdk->createS3();
            }
            $result = $this->s3->headObject([
                'Bucket' => $this->config["bucket"],
                'Key'    => $filepath,
            ]);
            return true;
        } catch (AwsException $e) {
            if ($e->getStatusCode() == 404) {
                //echo "The file does not exist.";
                return false;
            } else {
                //echo "An error occurred: " . $e->getMessage();
                return false;
            }
        }
    }
    public function deleteFile($filepath){
        try {
            $serviceLocator = $this->getEvent()->getApplication()->getServiceManager();
            if ($this->s3 == null || $this->config == null) {
                $this->config = $serviceLocator->get('Config');
                $this->config = isset($this->config['aws']) ? $this->config['aws'] : array();
                $sdk = new Sdk($this->config);
                $this->s3 = $sdk->createS3();
            }
            $result = $this->s3->deleteObject([
                'Bucket' => $this->config["bucket"],
                'Key'    => $filepath,
            ]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    function getDuration($file)
    {
        $dur = shell_exec("ffmpeg -i " . $file . " 2>&1");
        if (preg_match("/: Invalid /", $dur)) {
            return false;
        }
        preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $dur, $duration);
        if (!isset($duration[1])) {
            return false;
        }
        return $duration[1] . ":" . $duration[2] . ":" . $duration[3];
    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString . "_" . strtotime(date("Y-m-d H:i:s"));
    }

    function random_password($length = 8)
    {
        $chars = "0123456789";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

    function randomNumericpassword($length = 8)
    {
        $chars = "0123456789";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }
    function generateCouponCode($couponType, $length = 10) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length - 1; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $couponType . "-" . $randomString;
    }

    public function generateOtp($length = 4){

        try
        {
            if ($_SERVER['APPLICATION_ENV'] === 'development') {
                return "1111";
            }
            $length = intval($length);
            $characters = '0123456789';
            $otp = "";
            for ($i = 0; $i < $length; $i++)
            {
                $otp .= $characters[rand(0, strlen($characters) - 1)];
            }
            if(strlen($otp) < 4)
            {
                for($j = 0; $j < 4 - strlen($otp); $j++)
                {
                    $otp .= 0;
                }
            }
            //return $otp;
            return '1111';
        }catch(\Exception $e)
        {
            return "";
        }
    }

    public function sendOtpSms($mobile, $otp,$smsAction='otp')
    {
        if ($_SERVER['APPLICATION_ENV'] === 'development') {
            $mobile = '917330781638'; 
        }
        $smsObject = new Sms();
        $response = $smsObject->send(
            $mobile,
            'sms-template',
            array(
                'action' => $smsAction,
                "otp" => $otp
            )
        );
        return $response;
    }

    private function getMailer()
    {
        try
        {
            if (!$this->mailer)
            {
                $this->mailer = $this->getEvent()->getApplication()->getServiceManager()->get('Application\Channel\Mail');
            }
            return $this->mailer;
        }catch (\Exception $e){
            return null;
        }

    }

    function sendmail($receiverEmail, $subject, $action,$data)
    {
        try
        {
            $this->getMailer()->send(
                'susritourtales@gmail.com',
                $receiverEmail,
                $subject,
                'email-template',
                array(
                    'action' => $action,
                    "baseUrl" => 'http://susritourtales.com',
                    "data" => $data
                )
            );
            return true;
        }catch(\Exception $e)
        {
            return false;
        }
    }
}
