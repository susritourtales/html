<?php
/**
 * Created by PhpStorm.
 * User: Akshay
 * Date: 3/12/2019
 * Time: 8:37 PM
 */

namespace Application\Controller;


use Application\Channel\Sms;
use Application\Handler\Aes;
use Application\Handler\CronJob;
use Application\Handler\Crypto;
use Application\Handler\Instamojo;
use Application\Handler\SendFcmNotification;
use Aws\Exception\AwsException;
use Aws\ResultInterface;
use Aws\S3\S3Client;
use Aws\CommandPool;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\JsonModel;
use Aws\Sdk;
class BaseController extends AbstractActionController
{
    protected $countriesTable;
    protected $userTable;
    protected $pricingTable;   // -- Added my Manjary
    protected $sponsorTypesTable;   // -- Added my Manjary
    protected $sponsorPhotoTable;   // -- Added my Manjary
    protected $upcomingTable;   // -- Added my Manjary
    protected $referTable;   // -- Added my Manjary
    protected $sscTable;   // -- Added my Manjary
    protected $downloadedFileInfo;
    protected $temporaryFiles;
    protected $likes;
    protected $notificationTable;
    protected $tourOperatorDetailsTable;
    protected $tourCoordinatorDetailsTable;
    protected $seasonalSpecialsTable;
    protected $statesTable;
    protected $citiesTable;
    protected $languagesTable;
    protected $tourismPlacesTable;
    protected $placePriceTable;
    protected $tourismFilesTable;
    protected $fcmTable;
    protected $otpTable;
    protected $paymentTable;
    protected $passwordTable;
    protected $bookingsTable;
    protected $bookingTourDetailsTable;
    protected $priceSlabTable;
    protected $mailer;
    protected $config;
    protected $s3;
    protected $authService;
    protected $sessionManager;
    protected $sessionSaveManager;
    protected $authDbTable;
    protected $sessionStorage;
    protected $inboxTable;
    protected $bannersTable;
    protected $cityTourSlabDaysTable;
    
    const  token='dG91cmlzbUFwcGxpY2F0aW9u';
    public function onDispatch(MvcEvent $e)
    {

          /*$api = new Instamojo();
        $response = $api->paymentRequestCreate(array(
            "purpose" => "Tour",
            "amount" => 10,
            "send_email" => true,
            'email'=>'banu.nagumopthu@gmail.com',
            'buyer_name'=>'banu',
            'phone'=>'9494342225',
            'allow_repeated_payments'=> true,
            "redirect_url" => $this->getBaseUrl().'/application/booking-status',
        ));

        //$order=$api->paymentOrderCreate(array('id'=>$response['id']));
        echo '<pre>';
        print_r($response);
        exit;*/
       /* $this->copypushFiles();*/
        error_reporting('');
        ini_set('display_error', 0);
        date_default_timezone_set('Asia/Kolkata');
          /* $this->copypushFiles();
           exit;*/

        return parent::onDispatch($e);
        
    }

    public  function tokenValidation($token)
    {
        if(!($token== \Application\Controller\BaseController::token))
        {
             return false;
        }

         return true;
    }

     public function filesUrl()
     {
         return 'https://s3.amazonaws.com/files.susri/';
     }
    public function getLoggedInUserId()
    {
        try {
            $user = $this->getLoggedInUser();
            if ($user) {
                return $user['user_id'];
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
            //$user = $this->getLoggedInUser();
            return $user;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function generateHash()
    {
        $hashLength = 32;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $hash = "";
        for ($i = 0; $i < $hashLength; $i++)
        {
            $hash .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $hash;
    }
    public function generateOtp($length = 4){

        try
        {

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
        }catch(\Exception $e)
        {
            return "";
        }
    }

    public function getBaseUrl()
    {
        $event = $this->getEvent();
        $request = $event->getRequest();
        $router = $event->getRouter();
        $uri = $router->getRequestUri();
        //return $baseUrl = sprintf('http://%s%s', $uri->getHost(), $request->getBaseUrl()); // remove this line and uncomment below line while live - Manjary
        return $baseUrl = sprintf('%s://%s%s', $uri->getScheme(), $uri->getHost(), $request->getBaseUrl());
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
    public function sendOtpSms($mobile, $otp,$smsAction='otp')
    {
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
    public function sendPasswordSms($mobile, $data,$smsAction='notify')
    {
        $smsObject = new Sms();
        $response = $smsObject->send(
            $mobile,
            'sms-template',
            array(
                'action' => $smsAction,
                "text" => $data
            )
        );
        return $response;
    }
    public function sendBookingSms($mobile, $data,$smsAction='booking')
    {
             
        $smsObject = new Sms();
        $response = $smsObject->send(
            $mobile,
            'sms-template',
            array(
                'action' => $smsAction,
                "data" => $data
            )
        );
        return $response;
    }
    protected function safeString($string)
    {

        $string = html_entity_decode($string);
        return trim(strip_tags(preg_replace('!\s+!', ' ', $string)));

    }
    public function activateBooking($userListDetails)
    {
        $counter=0;
        foreach($userListDetails as $user)
        {
            $tourType=$user['tour_type'];
            $notficationMessage='Your Activation Date for '. \Admin\Model\PlacePrices::tour_type[$tourType] .'is enabled today Please Click here to download';
            $mobileCountryCode=$user['mobile_country_code'];
            $mobile = $user['mobile'];
            $userId = $user['user_id'];
            $bookingId=$user['booking_id'];
            $notificationDetails[] = array(
                'status' => \Admin\Model\Notification::STATUS_UNREAD,
                'notification_recevier_id' => $userId,
                'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_ACTIVATE,
                'notification_text' => $notficationMessage ,
                'notification_data_id'=>$bookingId,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s"));

            $message=$notificationDetails[$counter]['notification_text'];

            $registrationIds = $this->fcmTable()->getDeviceIds($notificationDetails[$counter]['notification_recevier_id']);
            if($registrationIds)
            {
                $notification=new SendFcmNotification();
                $notification->sendPushNotificationToFCMSever($registrationIds,array('message'=>$notificationDetails[$counter]['notification_text'],'title'=>'Your Activation Date '.\Admin\Model\PlacePrices::tour_type[$tourType],'id'=>$notificationDetails[$counter]['notification_data_id'],'type'=>$notificationDetails[$counter]['notification_type']));
            }
            $this->sendBookingSms($mobileCountryCode.$mobile,array('text'=>$message));
            $counter++;
        }
        $this->notificationTable()->addMutipleData($notificationDetails);
    }

    public function deactivateBooking($userListDetails)
    {

    }
    function sendPassword($receiverEmail, $subject, $action,$data)
    {
        try
        {
            $this->getMailer()->send(
                'tourmates@tvishasystems.com',
                $receiverEmail,
                $subject,
                'email-template',
                array(
                    'action' => $action,
                    "baseUrl" => 'http://toursim.com',
                    "password" => $data['password'],
                )
            );
            return true;
        }catch(\Exception $e)
        {
            return false;
        }
    }
    function emailSTTUserWithAttachment($receiverEmail, $subject, $action,$data,$attach)
    {
        try
        {
            $this->getMailer()->send(
                'tourmates@tvishasystems.com',
                $receiverEmail,
                $subject,
                'email-template',
                array(
                    'action' => $action,
                    "baseUrl" => 'http://susritourtales.com',
                    "data" => $data
                ),
                array($attach)
            );
            return true;
        }catch(\Exception $e)
        {
            return false;
        }

    }
    function sendbookingDetails($receiverEmail, $subject, $action,$data,$attach)
    {
        try
        {
            $this->getMailer()->send(
                'tourmates@tvishasystems.com',
                $receiverEmail,
                $subject,
                'email-template',
                array(
                    'action' => $action,
                    "baseUrl" => 'http://susritourtales.com',
                    "data" => $data
                ),
                array($attach) 
            );
            return true;
        }catch(\Exception $e)
        {
            return false;
        }

    }
    function mailSTTUserNoAttachment($receiverEmail, $subject, $action,$data)
    {
        try
        {
            $this->getMailer()->send(
                'tourmates@tvishasystems.com',
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
    function sendreply($receiverEmail, $subject, $action,$data)
    {
        try
        {
            $this->getMailer()->send(
                'tourmates@tvishasystems.com',
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
    function sendmail($receiverEmail, $subject, $action,$data)
    {
        try
        {
            $this->getMailer()->send(
                'tourmates@tvishasystems.com',
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

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString . "_" . strtotime(date("Y-m-d H:i:s"));
    }

    function random_password( $length = 8 )
    {
        $chars = "0123456789";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }

    function randomNumericpassword( $length = 8 )
    {

        $chars = "0123456789";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }

    public function notifyUser($userDetails){
        if($userDetails['booking_type']==\Admin\Model\Bookings::booking_Sponsorship){
            $message="Congratulations on converting yourself as a \"Sponsor\".\nWelcome to the Promoting Group of STT.\n\nYou can buy passwords at discounted price and  sell them to interested persons/tourists.\nIt gives you an opportunity to earn simultaneously while serving the tourist.\nPlease go through the e-mail for more details.";
            $nTitle = "Welcome as Sponsor";
        }
        elseif($userDetails['booking_type']==\Admin\Model\Bookings::booking_Subscription|| $userDetails['booking_type']==\Admin\Model\Bookings::booking_Sponsored_Subscription){
            //$message="Congratulations on your choice of subscribing to STT. Welcome to Susri Tour Tales.\nSelect, Download and Listen to the tales about the tourist places  of your choice.\nEnjoy your time with STT.\n\nYou can also become a sponsor. For details, see the message  sent to your registered mail Id.";
            if($userDetails['mobile_country_code'] == "91"){
                $message="Congratulations on your choice of subscribing to STT.\nWelcome to Susri Tour Tales.\nSelect, Download and Listen to the tales about the tourist places  of your choice.\nEnjoy your time with STT.\n\nYou can also become a sponsor. For details, see the message  sent to your registered mail Id.";
            } else {
                $message="Congratulations on your choice of subscribing to STT.\nWelcome to Susri Tour Tales.\nSelect, Download and Listen to the tales about the tourist places  of your choice.\nEnjoy your time with STT.";
            }
            $nTitle = "Welcome as Subscriber";
        }        
        $notificationDetails = array('notification_data_id'=>$userDetails['user_id'] ,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userDetails['user_id'], 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
        $registrationIds = $this->fcmTable()->getDeviceIds($userDetails['user_id']);
        $notification = $this->notificationTable()->saveData($notificationDetails);
        if ($registrationIds)
        {
            $notification = new SendFcmNotification();
            $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => $nTitle, 'id' => $notificationDetails['notification_data_id'], 'type' => $notificationDetails['notification_type']));
        }
     }
     public function smsUser($userDetails){
        if($userDetails['booking_type']==\Admin\Model\Bookings::booking_Sponsorship){
            $message="Welcome%20to%20Sponsors%27%20Group%20of%20Susri%20Tour%20Tales.%0ABuy%20subscription%20passwords%20at%20discounted%20prices%20and%20sell%20at%20profits.%20%0ASee%20e-mail%20message%20for%20details.";
        }
        elseif($userDetails['booking_type']==\Admin\Model\Bookings::booking_Subscription || $userDetails['booking_type']==\Admin\Model\Bookings::booking_Sponsored_Subscription){
            $message="Welcome%20to%20Susri%20Tour%20Tales.%0ADownload%20and%20Listen%20to%20the%20tales%20of%20your%20choice.%0A%0ABecome%20a%20sponsor%20and%20earn%20through%20App.%0ASee%20e-mail%20message%20for%20details.";
        }        
        $sendSms=array('mobile'=>$userDetails['mobile_country_code'].$userDetails['mobile'],'message'=>$message);
        $this->sendPasswordSms($sendSms['mobile'],array('text'=>$sendSms['message']));
     }
     public function mailToUser($userDetails)
     {  
        $userId=$userDetails['user_id'];
        $email=$userDetails['email'];

        if($email)
        {
            /* $stream_opts = [
                "ssl" => [
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ]
            ]; */ 
            
            if($userDetails['booking_type']==\Admin\Model\Bookings::booking_Sponsorship){
                $subject = "Welcome as STT Sponsor";
                $userDetails['heading'] = $subject;
                $this->mailSTTUserNoAttachment($email, $subject, 'mail-stt-user', $userDetails);
            }
            elseif($userDetails['booking_type']==\Admin\Model\Bookings::booking_Sponsored_Subscription){
                $subject = "Welcome as STT Subscriber";
                $userDetails['heading'] = $subject;
                $this->mailSTTUserNoAttachment($email, $subject, 'mail-stt-user', $userDetails);
                /* $fname="susri_subscription_".$userId. "_". $userDetails['booking_id'] .".pdf";
                $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?suid=0&bid=' . $userDetails['booking_id'], true, stream_context_create($stream_opts)); // added by Manjary - end -- remove on live
                // $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?suid=' . $userId, true);
                $mpdf = new mPDF(['tempDir' => getcwd()."/public/data/temp"]);
                $mpdf->SetDisplayMode("fullpage");
                $mpdf->WriteHTML($html);
                $mpdf->Output(getcwd()."/public/data/".$fname, "F");
                $this->emailSTTUser($email, $subject, 'mail-stt-user', $userDetails, getcwd()."/public/data/".$fname);  */
            }
            return true;
        }
     }

     public function getPasswordPricingDetails($userID){
        $cc = $this->userTable()->getField(array('user_id'=>$userID), 'mobile_country_code');
        //$pricingDetails = $this->pricingTable()->getPricingDetails(array('plantype'=>'1'));
        if($cc == "91")
            $pricingDetails = $this->pricingTable()->getPricingDetails(array('id'=>'2'));
        else
            $pricingDetails = $this->pricingTable()->getPricingDetails(array('id'=>'4'));

        $pricingDetails['oriprice'] = number_format((double)$pricingDetails['price'], 2, '.', '');
        $discPerc = 0;
        $user = $this->userTable()->getFields(array('user_id'=>$userID),array('role','discount_percentage', 'discount_end_date','subscription_end_date','subscription_count'));
        $today = date('Y-m-d');
        if($user['role']==\Admin\Model\User::Sponsor_role)
        {
                $discPerc = intval($user['discount_percentage']);
        }
        
        if(!empty($pricingDetails)){
            if($pricingDetails['end_date'] != null){
                $planEndDate = date('Y-m-d',strtotime($pricingDetails['end_date']));
                $planStartDate = date('Y-m-d',strtotime($pricingDetails['start_date']));
                if(!($planStartDate <= $today && $planEndDate >= $today)){
                    //$pricingDetails = $this->pricingTable()->getPricingDetails(array('plantype'=>'0'));
                    if($cc == "91")
                        $pricingDetails = $this->pricingTable()->getPricingDetails(array('id'=>'1'));
                    else
                        $pricingDetails = $this->pricingTable()->getPricingDetails(array('id'=>'3'));

                    $pricingDetails['oriprice'] = number_format((double)$pricingDetails['price'], 2, '.', '');
                }
            }
        }

        //$variables = $this->pricingTable()->getPricingDetails(array('plantype'=>'0'));
        if($cc == "91")
            $variables = $this->pricingTable()->getPricingDetails(array('id'=>'1'));
        else
            $variables = $this->pricingTable()->getPricingDetails(array('id'=>'2'));

        if(!empty($variables)){
            $pricingDetails['no_of_comp_pwds'] = $variables['no_of_comp_pwds'];
            $pricingDetails['no_of_days'] = $variables['no_of_days'];
            $pricingDetails['maxdownloads'] = $variables['maxdownloads'];
            $pricingDetails['maxquantity'] = $variables['maxquantity'];
            $pricingDetails['subscription_validity'] = $variables['subscription_validity'];
            $pricingDetails['sponsor_bonus_min'] = $variables['sponsor_bonus_min'];
            $pricingDetails['sponsor_comp_pwds'] = $variables['sponsor_comp_pwds'];
            $pricingDetails['sponsor_pwd_validity'] = $variables['sponsor_pwd_validity'];
            $pricingDetails['GST'] = $variables['GST'];
            $pricingDetails['first_rdp'] = $variables['first_rdp'];
            $pricingDetails['second_rdp'] = $variables['second_rdp'];
            $pricingDetails['npc_passwords'] = $variables['npc_passwords'];
            $pricingDetails['npc_days'] = $variables['npc_days'];
            $pricingDetails['web_text'] = $variables['web_text'];
            $pricingDetails['applied_rdp'] = 0;
        }

        if(!empty($pricingDetails)){
            $price = number_format((double)$pricingDetails['price'], 2, '.', '');
            if($discPerc > 0){
                $finalPrice = $price-($price*$discPerc)/100;
                $pricingDetails['price'] = $finalPrice; // * (1+($subscriptionDetails['GST']/100));
            }
            else
            {
                $pricingDetails['price'] = $price; // * (1+($subscriptionDetails['GST']/100));
            }
        }
        //$pricingDetails['annual_price'] = $this->pricingTable()->getField(array('plantype'=>'0'),"price");
        if($cc == "91")
            $pricingDetails['annual_price'] = $this->pricingTable()->getField(array('id'=>'1'),"price");
        else
            $pricingDetails['annual_price'] = $this->pricingTable()->getField(array('id'=>'3'),"price");

        return $pricingDetails;
    }

    public function getEffectivePricingDetails($userID){
        $cc = $this->userTable()->getField(array('user_id'=>$userID), 'mobile_country_code');
        //$pricingDetails = $this->pricingTable()->getPricingDetails(array('plantype'=>'1'));
        if($cc == "91")
            $pricingDetails = $this->pricingTable()->getPricingDetails(array('id'=>'2'));
        else
            $pricingDetails = $this->pricingTable()->getPricingDetails(array('id'=>'4'));

        $pricingDetails['oriprice'] = number_format((double)$pricingDetails['price'], 2, '.', '');
        // $pricingDetails['oriprice'] = $pricingDetails['oriprice'] * (1+($subscriptionDetails['GST']/100));
        $discPerc = 0;
        $user = $this->userTable()->getFields(array('user_id'=>$userID),array('role','discount_percentage', 'discount_end_date','subscription_end_date','subscription_count'));
        $today = date('Y-m-d');

        /* if($user['role']==\Admin\Model\User::Sponsor_role)// && $user['apply_discount']=="1")
        {
                $discPerc = intval($user['discount_percentage']);
        } */
        
        if(!empty($pricingDetails)){
            if($pricingDetails['end_date'] != null){
                $planEndDate = date('Y-m-d',strtotime($pricingDetails['end_date']));
                $planStartDate = date('Y-m-d',strtotime($pricingDetails['start_date']));
                //if((!($planStartDate < $today && $planEndDate > $today)) || ($user['role']==\Admin\Model\User::Subscriber_role || $user['role']==\Admin\Model\User::Sponsor_role)){
                if((!($planStartDate <= $today && $planEndDate >= $today)) || ($user['subscription_count'] != 0 && $user['role']!=\Admin\Model\User::Individual_role)){
                    //$pricingDetails = $this->pricingTable()->getPricingDetails(array('plantype'=>'0'));
                    if($cc == "91")
                        $pricingDetails = $this->pricingTable()->getPricingDetails(array('id'=>'1'));
                    else
                        $pricingDetails = $this->pricingTable()->getPricingDetails(array('id'=>'3'));

                    $pricingDetails['oriprice'] = number_format((double)$pricingDetails['price'], 2, '.', '');
                    //$pricingDetails['oriprice'] = $pricingDetails['oriprice'] * (1+($subscriptionDetails['GST']/100));
                }
            }
        }

        //$variables = $this->pricingTable()->getPricingDetails(array('plantype'=>'0'));
        if($cc == "91")
            $variables = $this->pricingTable()->getPricingDetails(array('id'=>'1'));
        else
            $variables = $this->pricingTable()->getPricingDetails(array('id'=>'3'));

        if(!empty($variables)){
            $pricingDetails['no_of_comp_pwds'] = $variables['no_of_comp_pwds'];
            $pricingDetails['no_of_days'] = $variables['no_of_days'];
            $pricingDetails['maxdownloads'] = $variables['maxdownloads'];
            $pricingDetails['maxquantity'] = $variables['maxquantity'];
            $pricingDetails['subscription_validity'] = $variables['subscription_validity'];
            $pricingDetails['sponsor_bonus_min'] = $variables['sponsor_bonus_min'];
            $pricingDetails['sponsor_comp_pwds'] = $variables['sponsor_comp_pwds'];
            $pricingDetails['sponsor_pwd_validity'] = $variables['sponsor_pwd_validity'];
            $pricingDetails['GST'] = $variables['GST'];
            $pricingDetails['first_rdp'] = $variables['first_rdp'];
            $pricingDetails['second_rdp'] = $variables['second_rdp'];
            $pricingDetails['npc_passwords'] = $variables['npc_passwords'];
            $pricingDetails['npc_days'] = $variables['npc_days'];
            $pricingDetails['web_text'] = $variables['web_text'];
            $pricingDetails['applied_rdp'] = 0;
        }
        
        if($user['role']==\Admin\Model\User::Subscriber_role || $user['role']==\Admin\Model\User::Sponsor_role){
            if($user['subscription_count'] == 1)
            {
                $discPerc = $pricingDetails['first_rdp']; // 25;
            }
            elseif($user['subscription_count'] > 1)
            {
                $discPerc = $pricingDetails['second_rdp']; // 50;
            }
            $pricingDetails['applied_rdp'] = $discPerc;
        }

        if(!empty($pricingDetails)){
            $price = number_format((double)$pricingDetails['price'], 2, '.', '');
            if($discPerc > 0){
                $finalPrice = $price-($price*$discPerc)/100;
                $pricingDetails['price'] = $finalPrice; // * (1+($subscriptionDetails['GST']/100));
            }
            else
            {
                $pricingDetails['price'] = $price; // * (1+($subscriptionDetails['GST']/100));
            }
        }
        //$pricingDetails['annual_price'] = $this->pricingTable()->getField(array('plantype'=>'0'),"price");
        if($cc == "91")
            $pricingDetails['annual_price'] = $this->pricingTable()->getField(array('id'=>'1'),"price");
        else
            $pricingDetails['annual_price'] = $this->pricingTable()->getField(array('id'=>'3'),"price");

        return $pricingDetails;
    }

    public function copypushFiles($files)
    {
        $serviceLocator=$this->getEvent()->getApplication()->getServiceManager();
        if($this->s3 == null || $this->config == null)
        {
            $this->config = $serviceLocator->get('Config');
            $this->config = isset($this->config['aws']) ? $this->config['aws'] : array();

            /* $this->s3 = new S3Client([
                 'version' => 'latest',
                 'region'  => $this->config['region']
             ]);*/


            $sdk = new Sdk($this->config);
            $this->s3 = $sdk->createS3();
        }

        // header("Content-Type: image/jpg");
        $i=0;
        $copiedFiles=array();
         /* try{

              foreach ($files as $file)
              {

                  $value[] = $this->s3->copyObject([
                      'Bucket'     =>  $this->config["bucket"],
                      'Key'        => "{$file['new_path']}",
                      'CopySource' => "{$this->config["bucket"]}/tmp/{$file['old_path']}",
                  ]);
                  array_push($copiedFiles,$file['id']);
              }

              return array('status'=>true,'copied'=>$copiedFiles);
          }catch (\Exception $e)
          {
                    print_r($e->getMessage());
              return array('status'=>false,'copied'=>$copiedFiles);


          }*/

               $fileIds=array();
               $i=-1;
        $value=array();
        foreach ($files as $file)
        {
            $i++;
            $fileIds[$i] = $file['id'];
            $value[] = $this->s3->getCommand('CopyObject',[
                'Bucket'     =>  $this->config["bucket"],
                'Key'        => "{$file['new_path']}",
                'CopySource' => "{$this->config["bucket"]}/tmp/{$file['old_path']}",
            ]);
        }

        try
        {
                if(count($value)) {
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
                }else{
                    return array('status' => true, 'copied' => $copiedFiles);

                }

        }catch (\Exception $e)
        {
               print_r($e->getMessage());
               exit;
            return false;
            // General error handling here
        }
        return true;

    }
    public function pushFiles($fileName,$filepath,$attachmentType)
    {
        try
        {
            $serviceLocator=$this->getEvent()->getApplication()->getServiceManager();
            if($this->s3 == null || $this->config == null)
            {
                $this->config = $serviceLocator->get('Config');
                $this->config = isset($this->config['aws']) ? $this->config['aws'] : array();

               /* $this->s3 = new S3Client([
                    'version' => 'latest',
                    'region'  => $this->config['region']
                ]);*/

                $sdk = new Sdk($this->config);
                $this->s3 = $sdk->createS3();
            }

           // header("Content-Type: image/jpg");

            $value= $this->s3->putObject(array(
                'Bucket' => $this->config["bucket"],
                'Key' =>  $fileName,
                'Body' => fopen($filepath,'r'),
                'ACL' => 'public-read',
                "Cache-Control" => "max-age=94608000",
                "ContentType" => $attachmentType,
                "signatureVersion" => "v4",
                "Expires" => gmdate("D, d M Y H:i:s T",
                    strtotime("+36 years")),
                "Metadata" => array(
                    'Expires' => gmdate("D, d M Y H:i:s T",
                        strtotime("+36 years"))
                )
            ));
            return $value;
             //    return true;
            //   @unlink(getcwd(). "/public/" .$filepath);
        }catch (\Exception $e)
        {
                      return false;
        }
    }
    public function userTable()
    {
        try {

            if ($this->userTable == null) {
                $this->userTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/UserTable");
            }

            return $this->userTable;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function upcomingTable()     // -- Added my Manjary
    {
        try {
            if ($this->upcomingTable == null) {
                $this->upcomingTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/UpcomingTable");
            }
            return $this->upcomingTable;
        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function referTable()     // -- Added my Manjary
    {
        try {
            if ($this->referTable == null) {
                $this->referTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/ReferTable");
            }
            return $this->referTable;
        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function sscTable()     // -- Added my Manjary
    {
        try {
            if ($this->sscTable == null) {
                $this->sscTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/SscTable");
            }
            return $this->sscTable;
        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function pricingTable()     // -- Added my Manjary
    {
        try {
            if ($this->pricingTable == null) {
                $this->pricingTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/PricingTable");
            }
            return $this->pricingTable;
        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function sponsorTypesTable()     // -- Added my Manjary
    {
        try {

            if ($this->sponsorTypesTable == null) {
                $this->sponsorTypesTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/SponsorTypesTable");
            }

            return $this->sponsorTypesTable;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function sponsorPhotoTable()     // -- Added my Manjary
    {
        try {

            if ($this->sponsorPhotoTable == null) {
                $this->sponsorPhotoTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/SponsorPhotoTable");
            }

            return $this->sponsorPhotoTable;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function downloadedFileInfo()
    {
        try {

            if ($this->downloadedFileInfo == null) {
                $this->downloadedFileInfo = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/DownloadedFileInfo");
            }

            return $this->downloadedFileInfo;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function inboxTable()
    {
        try
        {

            if ($this->inboxTable == null) {
                $this->inboxTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/InboxTable");
            }

            return $this->inboxTable;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {

            return null;
        } catch (ContainerExceptionInterface $e) {

            return null;
        }
    }
    public function bannersTable()
    {
        try
        {

            if ($this->bannersTable == null) {
                $this->bannersTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/BannersTable");
            }

            return $this->bannersTable;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {

            return null;
        } catch (ContainerExceptionInterface $e) {

            return null;
        }
    }
    public function cityTourSlabDaysTable()
    {
        try
        {

            if ($this->cityTourSlabDaysTable == null) {
                $this->cityTourSlabDaysTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/CityTourSlabDaysTable");
            }

            return $this->cityTourSlabDaysTable;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {

            return null;
        } catch (ContainerExceptionInterface $e) {

            return null;
        }
    }
    public function temporaryFiles()
    {
        try
        {
            if ($this->temporaryFiles == null)
            {

                $this->temporaryFiles = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/TemporaryFilesTable");

            }
            return $this->temporaryFiles;
        }catch(\Exception $e){
            /*print_r($e->getMessage());
                  exit;*/
            return null;
        } catch (NotFoundExceptionInterface $e)
        {
            return null;
        } catch (ContainerExceptionInterface $e)
        {
            return null;
        }
    }
    public function likes()
    {
        try {

            if ($this->likes == null) {
                $this->likes = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/likes");
            }

            return $this->likes;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function notificationTable()
    {
        try
        {

            if ($this->notificationTable == null)
            {
                $this->notificationTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/Notification");
            }

            return $this->notificationTable;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
/*   Removed by Manjary for STT subscription version - start  
    public function tourOperatorDetailsTable()
    {
        try{
            if ($this->tourOperatorDetailsTable == null)
            {

                $this->tourOperatorDetailsTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/TourOperatorDetails");

            }
            return $this->tourOperatorDetailsTable;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }

    public function tourCoordinatorDetailsTable()
    {
        try
        {
            if ($this->tourCoordinatorDetailsTable == null)
            {
                $this->tourCoordinatorDetailsTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/TourCoordinatorDetails");
            }
            return $this->tourCoordinatorDetailsTable;
        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    Removed by Manjary for STT subscription version - end
 */
    public function seasonalSpecialsTable()
    {
        try
        {
            if ($this->seasonalSpecialsTable == null)
            {
                $this->seasonalSpecialsTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/SeasonalSpecials");
            }

            return $this->seasonalSpecialsTable;

        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function countriesTable()
    {
        try {
            if ($this->countriesTable == null) {
                $this->countriesTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/Countries");
            }
            return $this->countriesTable;
        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {

            return null;
        } catch (ContainerExceptionInterface $e) {

            return null;
        }
    }
    public function bookingsTable()
    {
        try {
            if ($this->bookingsTable == null) {
                $this->bookingsTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/Bookings");
            }
            return $this->bookingsTable;
        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {

            return null;
        } catch (ContainerExceptionInterface $e) {

            return null;
        }
    }
    public function bookingTourDetailsTable()
    {
        try {
            if ($this->bookingTourDetailsTable == null) {
                $this->bookingTourDetailsTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/BookingTourDetails");
            }
            return $this->bookingTourDetailsTable;
        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {

            return null;
        } catch (ContainerExceptionInterface $e) {

            return null;
        }
    }
    public function priceSlabTable()
    {
        try
        {
            if ($this->priceSlabTable == null)
            {
                $this->priceSlabTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/PriceSlab");
            }
            return $this->priceSlabTable;

        } catch (\Exception $e)
        {
            return null;
        }catch(NotFoundExceptionInterface $e)
        {
            return null;
        }catch(ContainerExceptionInterface $e) {

            return null;
        }
    }
    public function passwordTable()
    {
        try {
            if ($this->passwordTable == null) {
                $this->passwordTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/Password");
            }
            return $this->passwordTable;
        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {

            return null;
        } catch (ContainerExceptionInterface $e) {

            return null;
        }
    }
    public function statesTable()
    {
        try {

            if ($this->statesTable == null) {
                $this->statesTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/States");
            }
            return $this->statesTable;
        } catch (\Exception $e) {

            return null;
        } catch (NotFoundExceptionInterface $e) {

            return null;
        } catch (ContainerExceptionInterface $e) {

            return null;
        }
    }
    public function citiesTable()
    {
        try {
            if ($this->citiesTable == null) {
                $this->citiesTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/Cities");
            }
            return $this->citiesTable;

        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function languagesTable()
    {
        try {
            if ($this->languagesTable == null) {
                $this->languagesTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/Languages");
            }
            return $this->languagesTable;

        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function tourismPlacesTable()
    {
        try {
            if ($this->tourismPlacesTable == null) {
                $this->tourismPlacesTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/TourismPlaces");
            }
            return $this->tourismPlacesTable;

        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function placePriceTable()
    {
        try
        {
            if ($this->placePriceTable == null)
            {
                $this->placePriceTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/PlacePrices");
            }
            return $this->placePriceTable;
        }catch(\Exception $e)
        {
               /*print_r($e->getMessage());
            exit;*/
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function tourismFilesTable()
    {
        try {
            if ($this->tourismFilesTable == null) {
                $this->tourismFilesTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/TourismFiles");
            }
            return $this->tourismFilesTable;

        } catch (\Exception $e)
        {
            return null;
        } catch (NotFoundExceptionInterface $e)
        {
            return null;
        } catch (ContainerExceptionInterface $e)
        {
            return null;
        }
    }public function fcmTable()
    {
        try {
            if ($this->fcmTable == null) {
                $this->fcmTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/Fcm");
            }
            return $this->fcmTable;

        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function otpTable()
    {
        try {

            if ($this->otpTable == null) {
                $this->otpTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/OtpTable");
            }

            return $this->otpTable;

        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function paymentTable()
    {
        try {

            if ($this->paymentTable == null) {
                $this->paymentTable = $this->getEvent()->getApplication()->getServiceManager()->get("Admin/Model/PaymentTable");
            }

            return $this->paymentTable;

        } catch (\Exception $e) {
            return null;
        } catch (NotFoundExceptionInterface $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    public function getAuthService()
    {
        try {
            if (!$this->authService)
            {
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
        
    public function logRequest($logString){
        $fullPath = "/var/www/html/public/beta/logs/httplogs.txt";
        echo exec('whoami');
        $parts = explode( '/', $fullPath );
        array_pop( $parts );
        $dir = implode( '/', $parts );
    
        if( !is_dir( $dir ) )
            @mkdir( $dir, 0777, true );

        //$timestamp = date("dd/mm/yyyy")." &raquo;";
        file_put_contents( $fullPath, "test string", FILE_APPEND | LOCK_EX );
        //$myfile = file_put_contents($fullPath, $timestamp. $logString.PHP_EOL , FILE_APPEND | LOCK_EX);
        print_r(error_get_last());
        return $myfile;
    }
}
