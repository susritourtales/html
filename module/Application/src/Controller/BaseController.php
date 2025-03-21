<?php

namespace Application\Controller;
require 'vendor/autoload.php';
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\Db\Adapter\Adapter;
use Laminas\Session\Container as SessionContainer;
use Laminas\Http\PhpEnvironment\Request;
use Aws\Exception\AwsException;
use Aws\ResultInterface;
use Aws\CommandPool;
use Aws\Sdk;
use Application\Channel\Sms;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Laminas\View\Model\JsonModel;

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
use Admin\Model\EnablerPurchaseRequestTable;
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
    protected $jwtSecret;
    protected $tokenIssuer;
    protected $tokenExpiry;

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
    protected $executivePurchaseRequestTable;
    protected $executiveTransactionTable;
    protected $otpTable;
    protected $couponsTable;
    protected $enablerTable;
    protected $enablerPurchaseTable;
    protected $enablerPurchaseRequestTable;
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
        EnablerPurchaseRequestTable $enabler_purchase_request_table,
        EnablerSalesTable $enabler_sales_table,
        EnablerPlansTable $enabler_plans_table,
    ) {
        $this->sessionContainer = new SessionContainer('stt_session');
        $this->sessionManager = $this->sessionContainer->getManager();
        $this->sessionManager->rememberMe(604800); // = 60 * 60 * 24 * 7
        $this->authService = $authService;
        $this->dbAdapter = $dbAdapter;
        $this->tokenExpiry = 100 * 24 * 60 * 60; //100 days

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
        $this->enablerPurchaseRequestTable = $enabler_purchase_request_table;
        $this->enablerSalesTable = $enabler_sales_table;
        $this->enablerPlansTable = $enabler_plans_table;

        //$this->logRequest();
    }


    /**
     * Generate a client secret using your .p8 key
     */
    function generateClientSecret() {
        $config = $this->getConfig();
        $appauth = $config['apphybridauth']['providers']['Apple']['keys'];
        $key_id = $appauth['key_id'];
        $team_id = $appauth['team_id'];
        $client_id = $appauth['id'];
        $key_file_path = $appauth['key_file']; 
        
        $header = ['alg' => 'ES256', 'kid' => $key_id];
        $claims = [
            'iss' => $team_id,
            'iat' => time(),
            'exp' => time() + (86400 * 180), // Valid for 180 days
            'aud' => 'https://appleid.apple.com',
            'sub' => $client_id,
        ];
        $privateKey = file_get_contents($key_file_path);
        $jwt = JWT::encode($claims, $privateKey, 'ES256', $key_id);
        return $jwt;
    }

    function validateAppleIdToken($idToken) {
        // Step 1: Fetch Apple's public keys
        $keysUrl = "https://appleid.apple.com/auth/keys";
        $keysResponse = file_get_contents($keysUrl);
        if ($keysResponse === false) {
            throw new Exception("Failed to fetch Apple's public keys.");
        }
    
        $keys = json_decode($keysResponse, true);
        if (!isset($keys['keys'])) {
            throw new Exception("Invalid key format from Apple.");
        }
    
        // Step 2: Decode the JWT header to get the `kid` (Key ID)
        $jwtParts = explode('.', $idToken);
        if (count($jwtParts) !== 3) {
            throw new Exception("Invalid id_token format.");
        }
        $header = json_decode(base64_decode($jwtParts[0]), true);
        if (!isset($header['kid'])) {
            throw new Exception("Invalid id_token header.");
        }
        $kid = $header['kid'];
    
        // Step 3: Find the matching public key
        $publicKey = null;
        foreach ($keys['keys'] as $key) {
            if ($key['kid'] === $kid) {
                $publicKey = $key;
                break;
            }
        }
        if (!$publicKey) {
            throw new Exception("No matching public key found for kid: $kid");
        }
    
        // Step 4: Convert the public key to a format usable by firebase/php-jwt
        $jwks = ['keys' => [$publicKey]];
        $keySet = JWK::parseKeySet($jwks);
    
        // Step 5: Decode and validate the id_token
        try {
            $decoded = JWT::decode($idToken, $keySet);
        } catch (\Exception $e) {
            throw new Exception("Failed to decode id_token: " . $e->getMessage());
        }
    
        // Step 6: Verify token claims
        $iss = "https://appleid.apple.com"; 
        $aud = "com.susritourtales.twistt"; 
    
        if ($decoded->iss !== $iss) {
            throw new Exception("Invalid issuer: " . $decoded->iss);
        }
    
        if ($decoded->aud !== $aud) {
            throw new Exception("Invalid audience: " . $decoded->aud);
        }
    
        if ($decoded->exp < time()) {
            throw new Exception("Token has expired.");
        }
    
        // Token is valid
        return $decoded;// return (array) $decoded;
    }

    /**
     * Base64 URL Encode
     */
    function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function getJWTSecret(){
        return $this->jwtSecret;
    }

    public function generateAccessToken($data){
        $config = $this->getConfig();
        $this->jwtSecret = isset($config['JWT_SECRET']) ? $config['JWT_SECRET'] : "";
        $this->tokenIssuer = isset($config['TOKEN_ISSUER']) ? $config['TOKEN_ISSUER'] : "";

        // Generate JWT token
        $secretKey = $this->jwtSecret;
        $issuer = $this->tokenIssuer;
        $issuedAt = time();
        $expiration = $issuedAt + (int)$this->tokenExpiry;
        $payload = [
            'iss' => $issuer,
            'iat' => $issuedAt,
            'exp' => $expiration,
            'data' => $data,
        ];
        $accessToken = JWT::encode($payload, $secretKey, 'HS256');
        return $accessToken;
    }

    public function validateAccessToken($request){
        // Retrieve the Authorization header
        $authorizationHeader = $request->getHeaders('Authorization');
        if (!$authorizationHeader) {
            return new JsonModel(['success' => false, 'message' => 'Authorization header is missing'], [401]);
        }
        $authorizationHeaderValue = $authorizationHeader->getFieldValue();
        // Extract the token (assuming "Bearer <token>" format)
        $token = str_replace('Bearer ', '', $authorizationHeaderValue);
        $config = $this->getConfig();
        $this->jwtSecret = isset($config['JWT_SECRET']) ? $config['JWT_SECRET'] : "";
        $this->tokenIssuer = isset($config['TOKEN_ISSUER']) ? $config['TOKEN_ISSUER'] : "";

        try {
            // Decode and verify the token
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            // Optional: Validate claims like `exp` and `iss`
            if ($decoded->exp < time()) {
                return new JsonModel(['success' => false, 'message' => 'Token has expired. Please login again.'], [401]);
            }
            if ($decoded->iss !== $this->tokenIssuer) {
                return new JsonModel(['success' => false, 'message' => 'Invalid token issuer'], [401]);
            }
            // Attach user data to the request
            //$request = $request->withAttribute('user', $decoded->data);
            return new JsonModel(['success' => true, 'message' => 'Token valid', 'user' => $decoded->data]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'message' => 'Invalid token'], [401]);
        }
    }

    public function logRequest($logString){
        //$logString = $this->getRequest()->toString();
        $logDirectory =  __DIR__ . '/../../../../public/logs/httplogs';
        $fullPath = "$logDirectory/httpRequests.log"; //"$logDirectory/httpRequests-$mY.log";
        $timestamp = "\n\n" . date("d-m-Y H:i:s") . " >> \n";
        $myfile = file_put_contents($fullPath, $timestamp . $logString . PHP_EOL, FILE_APPEND | LOCK_EX);
        if ($myfile === false) {
            error_log("Failed to write to log file: $fullPath");
        }
        // print_r(error_get_last());
        return $myfile; 
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
            header("Location: " . $this->getBaseUrl() . '/a_dMin/login');
            exit();
            // return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
            /* return new JsonModel(array("success" => false, "message" => "invalid user.. please login..")); */
        }/*else{
            return new JsonModel(array("success" => true, "message" => "valid user"));
        }*/
    }

    public function destroySessionAction()
    {
        // Destroy the session
        $this->sessionManager->destroy();
    }
    public function isDateBetween($startDate, $endDate) {
        $currentDate = date('Y-m-d'); 
        return $currentDate >= $startDate && $currentDate <= $endDate;
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
                $i = -1;
                foreach ($results as $result) {
                    $i++;
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
            /* if(isset($_SERVER['APPLICATION_ENV'])){
                if ($_SERVER['APPLICATION_ENV'] === 'development') {
                    return "1111";
                }
            } */
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
            return $otp;
            // return '1111';
        }catch(\Exception $e)
        {
            return "";
        }
    }

    public function sendOtpSms($mobile, $otp,$smsAction='otp')
    {
        //$test = false;
        if(isset($_SERVER['APPLICATION_ENV'])){
            if ($_SERVER['APPLICATION_ENV'] === 'development') {
                $mobile = '7330781638';
            }
        }
        $smsObject = new Sms();
        $response = $smsObject->send(
            $mobile,
            'sms-template',
            array(
                'action' => $smsAction,
                "otp" => $otp,
                "receipt_url" => $this->getBaseUrl() . "/sms-status",
                "text" => ""
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
