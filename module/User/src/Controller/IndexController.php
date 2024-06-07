<?php

namespace User\Controller;

use Application\Controller\BaseController;
use Application\Handler\Aes;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\Storage\Session;
use Laminas\Mvc\Controller\AbstractActionController;
use Application\Handler\Razorpay;

class IndexController extends BaseController 
{ //AbstractActionController

public function indexAction()
{
  $marqText = "Awareness enhances enjoyment... Know about the place you visit by listening to Susri Tour Tales...";
  $banners=$this->bannerTable->getBanners();
  $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_HOME_PAGE);
  return new ViewModel(['marqText'=>$marqText,'banners'=>$banners, 'imageUrl'=>$this->filesUrl()]);
}
public function aboutUsAction() {
  $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_ABOUT_PAGE);
  return new ViewModel();
}

public function contactAction() {
  $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_CONTACT_PAGE);
  return new ViewModel();
}

  public function twisttAction()
  {
    return new ViewModel();
  }

  public function executiveRegisterAction()
  {
    $config = $this->getConfig();
    return new ViewModel(['callbackUrl' => $config['hybridauth']['callback']]);
  }

  public function executiveLoginAction()
  {
    $config = $this->getConfig();
    return new ViewModel(['callbackUrl' => $config['hybridauth']['callback']]);
  }
  public function executiveLogoutAction()
  {
    return new ViewModel();
  }
  public function executiveAuthAction()
  {
    try{
      $config = $this->getConfig();
      $hybridauth = new Hybridauth($config['hybridauth']);
      $storage = new Session();
      $error = false;
      //
      // Event 1: User clicked SIGN-IN link
      //
      if (isset($_GET['provider'])) {
        // Validate provider exists in the $config
        if (in_array($_GET['provider'], $hybridauth->getProviders())) {
            // Store the provider for the callback event
            $storage->set('provider', $_GET['provider']);
        } else {
            $error = $_GET['provider'];
        }
      }

      //
      // Event 2: User clicked LOGOUT link
      //
      if (isset($_GET['logout'])) {
        //echo $_GET['logout']; exit;
        if (in_array($_GET['logout'], $hybridauth->getProviders())) {
            // Disconnect the adapter
            $adapter = $hybridauth->getAdapter($_GET['logout']);
            $adapter->disconnect();
            session_unset();
            $storage->clear();
            header("Location: /twistt/executive/login");
            exit;
        } else {
            $error = $_GET['logout'];
        }
      }

      //
      // Handle invalid provider errors
      //
      if ($error) {
        error_log('Hybridauth Error: Provider ' . json_encode($error) . ' not found or not enabled in $config');
        echo 'Hybridauth Error: Provider ' . json_encode($error) . ' not found or not enabled in $config';
        exit;
      }
    
      if ($provider = $storage->get('provider')) {
        $hybridauth->authenticate($provider);
        $storage->set('provider', null);
        // Retrieve the provider record
        $adapter = $hybridauth->getAdapter($provider);
        $userProfile = $adapter->getUserProfile();
        $accessToken = $adapter->getAccessToken();

        $userdetails['username'] = $userProfile->displayName;
        $userdetails['user_login_id'] = $userProfile->email;
        $userdetails['email'] = $userProfile->email;
        $userdetails['country'] = $userProfile->country;
        $userdetails['city'] = $userProfile->city;
        $userdetails['state'] = $userProfile->region;
        $userdetails['gender'] = $userProfile->gender;
        $userdetails['user_type_id'] = \Admin\Model\User::TWISTT_Executive;
        $userdetails['photo_url'] = strtok($userProfile->photoURL, '?');
        $userdetails['access_token'] = $accessToken['access_token'];
        $userdetails['token_expiry'] = $accessToken['expires_at'];
        $aes = new Aes();
        $encodeContent = $aes->encrypt($accessToken['access_token']);
        $userdetails['password'] = $encodeContent['password'];
        $userdetails['hash'] = $encodeContent['hash'];
        $userId = $this->userTable->userExists($userdetails['user_login_id']);
        if(!$userId){
          $ures = $this->userTable->addUser($userdetails);
        }else{
          $updateValues['access_token'] = $userdetails['access_token'];
          $updateValues['password'] = $userdetails['password'];
          $updateValues['hash'] = $userdetails['hash'];
          $ures = $this->userTable->updateUser($updateValues, ['id' => $userId]);
        }
        if(!$ures)
          return new JsonModel(array('success' => false, "message" => 'unknown error'));

        $this->authService->getAdapter()
            ->setIdentity($userdetails['user_login_id'])
            ->setCredential($userdetails['password']);
        $ares = $this->authService->authenticate();
        
        if ($this->authService->hasIdentity()) {
          $config = $this->getConfig();
          $loginId = $this->authService->getIdentity();
          $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
          return new ViewModel(['userDetails' => $userDetails, 'callbackUrl' => $config['hybridauth']['callback']]);
        }else{
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
        }
      }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo $e->getMessage();
    }
  }
  public function sttAuthAction()
  {
    $postData = $this->params()->fromPost();
    $userdetails['user_login_id'] = str_replace("+","",$postData['loginid']);
    $userdetails['password'] = $postData['password'];
    $res = $this->userTable->checkPasswordWithUserId($userdetails['user_login_id'], $userdetails['password']);
    if($res){
      /* if (session_status() == PHP_SESSION_NONE) {
        session_start();
      }
     $username = $this->userTable->getField(["user_login_id" => $userdetails['user_login_id']], "username");
       $_SESSION['username'] = $username;
      $_SESSION['user_login_id'] = $postData['user_login_id']; */
      $this->authService->getAdapter()
              ->setIdentity($userdetails['user_login_id'])
              ->setCredential($userdetails['password']);
      $ares = $this->authService->authenticate();
      return new JsonModel(array('success' => true, "message" => 'credentials valid'));
    }else{
      return new JsonModel(array('success' => false, "message" => 'invalid credentials'));
    } 
  }

  /* public function executiveProfileAction(){
    if ($this->authService->hasIdentity()) {
      $config = $this->getConfig();
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      return new ViewModel(['userDetails' => $userDetails, 'callbackUrl' => $config['hybridauth']['callback']]);
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  } */

  public function executiveAddAction(){
    //return new JsonModel(array('success' => true, "message" => 'you have succesfuly registered as TWISTT Executive.'));
    $postData = $this->params()->fromPost();
    $validImageFiles = array('png', 'jpg', 'jpeg');
    $userdetails = [];
    $bankDetails = [];
    $uploadedFile = $this->params()->fromFiles('photo');
    if (isset($uploadedFile)) {
        $attachment = $uploadedFile;
        $filename = $attachment['name'];
        $fileExt = explode(".", $filename);
        $ext = end($fileExt) ? end($fileExt) : "";
        $ext = strtolower($ext);
        $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
        $filename = $filenameWithoutExt . "." . $ext;
        $filePath = "data/profiles";
        $filePath = $filePath . "/" . $filename;
        if (!in_array(strtolower($ext), $validImageFiles)) {
            return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
        }
        $uploadStatus = $this->pushFiles($filePath, $attachment['tmp_name'], $attachment['type']);
        if (!$uploadStatus) {
            return new JsonModel(array('success' => false, "message" => 'unable to upload photo.. try agian after sometime..'));
        }
        $userdetails['photo_url'] = $filePath;
    }else{
      return new JsonModel(array('success' => false, "message" => 'Photo not submitted.'));
    }
    
    $userdetails['username'] = $postData['name'];
    $userdetails['user_login_id'] = $postData['lid'];
    $userdetails['mobile_number'] = $postData['mobile'];
    $userdetails['country_phone_code'] = $postData['cc'];
    $userdetails['email'] = $postData['email'];
    $userdetails['country'] = $postData['country'];
    $userdetails['city'] = $postData['city'];
    $userdetails['state'] = $postData['state'];
    $userdetails['gender'] = $postData['gender'];
    $userdetails['user_type_id'] = \Admin\Model\User::TWISTT_Executive;
    $postData['ccmobile'] = str_replace("+","",$postData['ccmobile']);
    $aes = new Aes();
    $encodeContent = $aes->encrypt($postData['ccmobile']);
    $userdetails['password'] = $encodeContent['password'];
    $userdetails['hash'] = $encodeContent['hash'];
    $userId = $this->userTable->userExists($userdetails['user_login_id']);
    if($userId){
      $ures = $this->userTable->updateUser($userdetails, ['id' => $userId]);
    }else{
      return new JsonModel(array('success' => false, "message" => 'unknown error occurred.. please try after sometime..'));
    }
    if($ures){
      $execId = $this->executiveDetailsTable->executiveExists($userId);
      $bankDetails['user_id'] = $userId;
      $bankDetails['bank_name'] = $postData['bankName'];
      $bankDetails['bank_account_no'] = $postData['bankAccount'];
      $bankDetails['ifsc_code'] = $postData['ifsc'];
      $bankDetails['commission_percentage'] = "20";
      if(!$execId){
        $bres = $this->executiveDetailsTable->addExecutive($bankDetails);
      }else{
        $bres = $this->executiveDetailsTable->setExecutiveDetails($bankDetails, ['id' => $execId]);
      }
      if($bres){
        /* if (session_status() == PHP_SESSION_NONE) {
          session_start();
        }
        $_SESSION['username'] = $userdetails['username'];
        $_SESSION['user_login_id'] = $userdetails['user_login_id']; */
        $this->authService->getAdapter()
                ->setIdentity($userdetails['user_login_id'])
                ->setCredential($userdetails['password']);
        $ares = $this->authService->authenticate();
        return new JsonModel(array('success' => true, "message" => 'you have succesfully registered as TWISTT Executive.'));
      }else{
        return new JsonModel(array('success' => false, "message" => 'error saving user bank details.. please try after sometime..'));
      }
    }else{
      return new JsonModel(array('success' => false, "message" => 'error saving user details.. please try after sometime..'));
    }
  }
  public function executiveEditAction(){
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userId = $this->userTable->userExists($loginId['user_login_id']);
      $postData = $this->params()->fromPost();
      $validImageFiles = array('png', 'jpg', 'jpeg');
      $userdetails = [];
      $bankDetails = [];
      $uploadedFile = $this->params()->fromFiles('photo');
      if (isset($uploadedFile)) {
        if($uploadedFile['error'] !== UPLOAD_ERR_NO_FILE){
            $attachment = $uploadedFile;
            $filename = $attachment['name'];
            $fileExt = explode(".", $filename);
            $ext = end($fileExt) ? end($fileExt) : "";
            $ext = strtolower($ext);
            $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
            $filename = $filenameWithoutExt . "." . $ext;
            $filePath = "data/profiles";
            $filePath = $filePath . "/" . $filename;
            if (!in_array(strtolower($ext), $validImageFiles)) {
              return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
            }
            $uploadStatus = $this->pushFiles($filePath, $attachment['tmp_name'], $attachment['type']);
            if (!$uploadStatus) {
              return new JsonModel(array('success' => false, "message" => 'unable to upload photo.. try agian after sometime..'));
            }else{ // new photo successfully uploaded - delete old photo
              $oldPPUrl = $this->userTable->getField(['id'=>$userId], 'photo_url');
              if ($this->fileExists($oldPPUrl)) {
                if (!$this->deleteFile($oldPPUrl)) {
                  return new JsonModel(array('success' => false, "message" => 'unable to delete old photo..'));
                }
              } else {
                return new JsonModel(array('success' => false, "message" => 'unable to find old photo to be replaced..'));
              }
            }
            $userdetails['photo_url'] = $filePath;
          }
      }else{
        return new JsonModel(array('success' => false, "message" => 'Photo not submitted.'));
      }
      
      $userdetails['username'] = $postData['name'];
      $userdetails['email'] = $postData['email'];
      $userdetails['country'] = $postData['country'];
      $userdetails['city'] = $postData['city'];
      $userdetails['state'] = $postData['state'];
      $userdetails['gender'] = $postData['gender'];
      if($userId){
        $ures = $this->userTable->updateUser($userdetails, ['id' => $userId]);
      }else{
        return new JsonModel(array('success' => false, "message" => 'unknown error occurred.. please try after sometime..'));
      }
      if($ures){
        $execId = $this->executiveDetailsTable->executiveExists($userId);
        $bankDetails['user_id'] = $userId;
        $bankDetails['bank_name'] = $postData['bankName'];
        $bankDetails['bank_account_no'] = $postData['bankAccount'];
        $bankDetails['ifsc_code'] = $postData['ifsc'];
        if(!$execId){
          $bres = $this->executiveDetailsTable->addExecutive($bankDetails);
        }else{
          $bres = $this->executiveDetailsTable->setExecutiveDetails($bankDetails, ['id' => $execId]);
        }
        if($bres){
          return new JsonModel(array('success' => true, "message" => 'updated succesfully..'));
        }else{
          return new JsonModel(array('success' => false, "message" => 'error saving user bank details.. please try after sometime..'));
        }
      }else{
        return new JsonModel(array('success' => false, "message" => 'error saving user details.. please try after sometime..'));
      }
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executiveProfileAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'config' => $config['hybridauth'], 'imageUrl'=>$this->filesUrl()]);
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executiveSendOtpAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $mobile = $request['mobile'];
      $ccmobile = $request['ccmobile'];
      $cc = $request['cc'];
      if($cc == "91"){
        $otp = $this->generateOtp();
        $otpRes = $this->otpTable->addOtpDetails(['otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Executive_Registration, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::TWISTT_Request, 'otp_sent_to' => $ccmobile]);
        if($otpRes){
          $resp = "1"; //$this->sendOtpSms($ccmobile, $otp);
        }else{
          return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again after sometime..'));
        }
        if($resp){
          return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
        }else{
          return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again after sometime..'));
        }
      }
      return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
    }
  }
  public function executiveVerifyOtpAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $mobile = $request['mobile'];
      $ccmobile = $request['ccmobile'];
      $cc = $request['cc'];
      $otp = $request['otp'];
      $type = $request['otp_type'];
      if($cc == "91"){
        if($otp=='' || $otp==null)
        {
          return new JsonModel(array("success"=>false,"message"=>"OTP is required"));
        }
        if($ccmobile == '' || $ccmobile == null)
        {
          return new JsonModel(array("success"=>false,"message"=>"mobile number is missing"));
        }
        if($type==\Admin\Model\Otp::Executive_Registration){
          $data = ['otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Executive_Registration, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::TWISTT_Request, 'otp_sent_to' => $ccmobile];
          $verify=$this->otpTable->verifyOtp($data);
          if($verify){
            $updateResponse=$this->otpTable->setOtpDetails(array('sent_status_id'=>\Admin\Model\Otp::Is_verifed), $data);
            if($updateResponse){
              return new JsonModel(array("success"=>true,"message"=>"Otp verified succesfully.. please enter your details.."));
            }else{
              return new JsonModel(array("success"=>false,"message"=>"unable to verify otp.. please try again"));
            }
            /* $exists = '0';
            $ud = [];
            if($updateResponse){
              $ud = $this->userTable->getUserDetails(['user_login_id' => $ccmobile]);
              if($ud){
                $exists = '1'; // user exists
                $bd = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $ud['id']]);
                if($bd){
                  $exists = '2'; // executive exists
                  $ud = [];
                }
              }
              return new JsonModel(array("success"=>true,"message"=>"Otp verified succesfully.. please enter your details..", "exists"=>$exists, "details"=>$ud));
            }else{
              return new JsonModel(array("success"=>false,"message"=>"unable to verify otp.. please try again"));
            } */
          }else{
            return new JsonModel(array("success"=>false,"message"=>"otp not valid.. please try again"));
          }
        }else{
          return new JsonModel(array("success"=>false,"message"=>"wrong otp type"));
        }
      }
    }
  }
  public function executiveVerifyMobileAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $mobile = $request['mobile'];
      $ccmobile = $request['ccmobile'];
      $cc = str_replace($mobile, '', $ccmobile);
      $userId = $this->userTable->userExists($ccmobile);
      $execId = 0;
      if($userId){
        $execId = $this->executiveDetailsTable->executiveExists($userId);
        if($execId)
          return new JsonModel(array('success' => false, "message" => 'mobile number already registered.'));
      }
    }
  }
  public function executiveBuyCouponsAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $questtValid = $this->questtSubscriptionTable->isValidQuesttUser($userDetails['id']);
      $qed = $this->questtSubscriptionTable->getField(['user_id'=>$loginId['id']], 'end_date');
      $qed = date('d-m-Y', strtotime($qed));
      $udcp = 0;
      $uccp = 0;
      if($questtValid && $bankDetails['banned'] == '0') {
        if($userDetails['country_phone_code'] == '91'){
          $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_inr');
          $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_inr');
        }else{
          $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_usd');
          $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_usd');
        }
      }
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'config' => $config['hybridauth'], 'imageUrl'=>$this->filesUrl(), 'isQUESTTValid' => $questtValid, 'udcp' => $udcp, 'uccp' => $uccp, 'qed'=>$qed]);
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executivePayAction(){
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $questtValid = $this->questtSubscriptionTable->isValidQuesttUser($userDetails['id']);
      if($questtValid && $bankDetails['banned'] == '0') {
        $request = $this->getRequest()->getPost();
        $dc = $request['dc'];
        $cc = $request['cc'];
        
        if($dc == 0 && $cc ==0){
          return new JsonModel(array('success' => false, "message" => 'Please mention the no of coupons to purchase..'));
        }
        if($userDetails['country_phone_code'] == '91'){
          $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_inr');
          $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_inr');
        }else{
          $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_usd');
          $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_usd');
        }
        $totalAmount = $dc * $udcp + $cc * $uccp;
        if($totalAmount!=0 && round($totalAmount)!=0)
        {                
          $saveData['payment_status'] = \Admin\Model\ExecutivePurchase::payment_in_process;
          $saveData['user_id'] = $userDetails['id'];
          $saveData['executive_id'] = $bankDetails['id'];
          $saveData['amount'] = number_format((float)$totalAmount, 2, '.', '');
          if($userDetails['country_phone_code']=="91"){
            $saveData['currency'] = "INR";
          }else{
            $saveData['currency'] = "USD";
          }
          $purchaseId = $this->executivePurchaseTable->addExecutivePurchase($saveData);
          if($purchaseId){
            $api = new Razorpay();
            if($userDetails['country_phone_code']=="91"){
              $orderData = [
                  'amount'          => intval($totalAmount * 100),
                  'currency'        => 'INR',
                  'receipt' => 'TC_' . $purchaseId,
                  'payment_capture' => 1 
              ];
            }
            else{
              $orderData = [
                  'amount'          => intval($totalAmount * 100),
                  'currency'        => 'USD',
                  'receipt' => 'TC_' . $purchaseId,
                  'payment_capture' => 1 
              ];
            }
            $razorpayOrder = [];
            try {
              $response = $api->paymentRequestCreate($orderData); 
              $razorpayOrder['id'] = $response['id'];
              $razorpayOrder['amount'] = $response['amount'];
              $razorpayOrder['currency'] = $response['currency'];
              $razorpayOrder['receipt'] = $response['receipt'];
            } catch (\Exception $e) {
                return new JsonModel(array('success' => false, 'message' => $e->getMessage()));
            }
            if($response){
              $setResp = $this->executivePurchaseTable->setExecutivePurchase(['receipt' => $razorpayOrder['receipt'], 'razorpay_order_id' => $razorpayOrder['id']], ['id' => $purchaseId]);
              if (session_status() == PHP_SESSION_NONE) {
                session_start();
              }
               $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];
              if($setResp){
                return new JsonModel(array('success' => true, 'message' => 'order created', 'order' => $razorpayOrder));
              }else{
                return new JsonModel(array('success' => false, 'message' => 'unable to process your payment now.. please try after sometime..'));
              }
            }else{
              return new JsonModel(array('success' => false, 'message' => 'unable to process your payment now.. please try after sometime..'));
            }
          }
        }
      }else{
        return new JsonModel(array('success' => false, "message" => 'coupons purchase not allowed..'));
      }
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executiveCheckoutAction(){
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $questtValid = $this->questtSubscriptionTable->isValidQuesttUser($userDetails['id']);

      if($questtValid && $bankDetails['banned'] == '0') {
        $request = $this->getRequest()->getPost();
        $dc = $request['dc'];
        $cc = $request['cc'];
        $razorpay_payment_id = $request['razorpay_payment_id'];
        $razorpay_order_id = $request['razorpay_order_id'];
        $razorpay_signature = $request['razorpay_signature'];
        
        if (session_status() == PHP_SESSION_NONE) {
          session_start();
        }
        $attributes = array(
          'razorpay_order_id' => $_SESSION['razorpay_order_id'],
          'razorpay_payment_id' => $razorpay_payment_id,
          'razorpay_signature' => $razorpay_signature
      );
      $api = new Razorpay();
      try {
          $api->checkPaymentSignature($attributes);
          $setResp = $this->executivePurchaseTable->setExecutivePurchase(['razorpay_payment_id' => $razorpay_payment_id, 'razorpay_signature' => $razorpay_signature, 'payment_status' => \Admin\Model\ExecutivePurchase::payment_success], ['razorpay_order_id' => $_SESSION['razorpay_order_id']]);
          if($setResp){
            $purchase = $this->executivePurchaseTable->getExecutivePurchase(['razorpay_payment_id' => $razorpay_payment_id]);
            $ved = $this->questtSubscriptionTable->getField(['user_id'=>$purchase['user_id']], 'end_date');
            if($userDetails['country_phone_code'] == '91'){
              $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_inr');
              $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_inr');
            }else{
              $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_usd');
              $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_usd');
            }
            for ($i = 0; $i < $dc ; $i++) {
              $coupons[$i]['executive_id'] = $purchase['executive_id'];
              $coupons[$i]['purchase_id'] = $purchase['id'];
              $coupons[$i]['coupon_type'] = \Admin\Model\Coupons::Coupon_Type_Discount;
              $coupons[$i]['coupon_code'] = $this->generateCouponCode('D');
              $coupons[$i]['amount'] = $udcp;
              $coupons[$i]['commission_receivable'] = ($udcp * $bankDetails['commission_percentage']) / 100;
              $coupons[$i]['validity_end_date'] = $ved;
              $coupons[$i]['coupon_status'] = \Admin\Model\Coupons::Coupon_Status_Active;
            }
            $count = count($coupons);
            for ($j = $count; $j < $count + $cc ; $j++) {
              $coupons[$j]['executive_id'] = $purchase['executive_id'];
              $coupons[$j]['purchase_id'] = $purchase['id'];
              $coupons[$j]['coupon_type'] = \Admin\Model\Coupons::Coupon_Type_Complimentary;
              $coupons[$j]['coupon_code'] = $this->generateCouponCode('C');
              $coupons[$j]['amount'] = $uccp;
              $coupons[$j]['commission_receivable'] = ($uccp * $bankDetails['commission_percentage']) / 100;
              $coupons[$j]['validity_end_date'] = $ved;
              $coupons[$j]['coupon_status'] = \Admin\Model\Coupons::Coupon_Status_Active;
            }
            $miresp = $this->couponsTable->addMutipleCoupons($coupons);
            if($miresp){
              return new JsonModel(array('success' => true, 'message' => 'payment successful'));
            }else{
              return new JsonModel(array('success' => false, 'message' => 'unknown error'));
            }
          }else{
            return new JsonModel(array('success' => false, 'message' => 'unable to process.. please after sometime'));
          }
      } catch (\Exception $e) {
        return new JsonModel(array('success' => false, "message" => 'Razorpay Error : ' . $e->getMessage()));
      }
      }else{
        return new JsonModel(array('success' => false, "message" => 'coupons purchase not allowed..'));
      }
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executiveTrackCouponsAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $qed = $this->questtSubscriptionTable->getField(['user_id'=>$loginId['id']], 'end_date');
      $qed = date('d-m-Y', strtotime($qed));
      $coupons = $this->couponsTable->getCouponsList(['executive_id' => $bankDetails['id'], 'limit'=>10,'offset'=>0]);
      $totalCount=$this->couponsTable->getCouponsList(['executive_id' => $bankDetails['id']], 1);
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'config' => $config['hybridauth'], 'coupons'=>$coupons, 'totalCount'=>$totalCount, 'qed' => $qed, 'imageUrl'=>$this->filesUrl()]);
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  
  public function loadCouponsListAction(){
    if ($this->getRequest()->isXmlHttpRequest())
    {
        $request = $this->getRequest()->getPost();
        $searchData=array('limit'=>10,'offset'=>0);
        $type=$request['type'];
        $offset=0;
        $loginId = $this->authService->getIdentity();
        $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
        $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);

        if(isset($request['page_number']))
        {
            $pageNumber = $request['page_number'];
            $offset = ($pageNumber * 10 - 10);
            $limit = 10;
            $searchData['offset']=$offset;
            $searchData['limit']=$limit;
        }
        $searchData['executive_id']=$bankDetails['id'];
        $totalCount=0;
        
        if($type && $type=='search')
        {
            $totalCount=$this->couponsTable->getCouponsList($searchData, 1);
        }
        $coupons = $this->couponsTable->getCouponsList(['executive_id' => $bankDetails['id'], 'limit'=>10,'offset'=>0]);
        $view = new ViewModel(array('coupons'=>$coupons, 'totalCount'=>$totalCount));
        $view->setTerminal(true);
        return $view;
    }
  }
  public function executiveTrackCommissionsAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $qed = $this->questtSubscriptionTable->getField(['user_id'=>$loginId['id']], 'end_date');
      $qed = date('d-m-Y', strtotime($qed));
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'config' => $config['hybridauth'], 'qed' => $qed, 'imageUrl'=>$this->filesUrl()]);
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function questtValidityAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $lid = $request['lid'];
      $userId = $this->userTable->getField(['user_login_id' => $lid], 'id');
      if($userId){
        $isValidQuesttUser = $this->questtSubscriptionTable->isValidQuesttUser($userId);
        if($isValidQuesttUser)
          return new JsonModel(array('success' => true, "message" => 'Valid QUESTT user.. may proceed with TWISTT exxecutive registration process..'));
        else
          return new JsonModel(array('success' => false, "message" => 'not a valid QUESTT user'));
      }else{
        return new JsonModel(array('success' => false, "message" => 'user not found'));
      }
    }
  }
  public function twisttExecutiveTermsAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'config' => $config['hybridauth'], 'imageUrl'=>$this->filesUrl()]);
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executiveTermsAction()
  {
    return new ViewModel();
  }
  public function forgotPasswordAction()
  {
    return new ViewModel();
  }

  public function changePasswordAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      return new ViewModel(['userId' => $loginId['user_login_id']]);
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function resetPasswordAction()
  {
    $postData = $this->params()->fromPost();
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $check = $this->userTable->checkPasswordWithUserId($loginId['user_login_id'], $postData['current_password']);
      if($check){
        $aes = new Aes();
        $encodeContent = $aes->encrypt($postData['new_password']);
        $userdetails['password'] = $encodeContent['password'];
        $userdetails['hash'] = $encodeContent['hash'];
        $res = $this->userTable->updateUser($userdetails,['id'=>$check['id']]);
        if($res)
          return new JsonModel(array('success' => true, "message" => 'password changed succesfully..'));
        else
          return  new JsonModel(array('success' => false, "message" => 'unable to change password.. try after sometime..'));
      }else{
        return new JsonModel(array('success' => false, "message" => 'Current password wrong'));
      }
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }

  public function enablerRegisterAction()
  {
    return new ViewModel();
  }

  public function enablerLoginAction()
  {
    return new ViewModel();
  }

  public function termsPrivacyAction() {
    $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TERMS_PAGE);
      return new ViewModel();
  }
}
