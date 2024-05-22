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
    return new ViewModel();
  }

  public function executiveLoginAction()
  {
    return new ViewModel();
  }
  public function executiveLogoutAction()
  {
    return new ViewModel();
  }
  public function executiveAuthAction()
  {
    try{
      return new JsonModel(array('success' => true, "message" => 'Index controller'));
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
        $userdetails['address'] = $userProfile->address;
        $userdetails['city'] = $userProfile->city;
        $userdetails['state'] = $userProfile->region;
        $userdetails['age'] = $userProfile->age;
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
    $userdetails['user_login_id'] = $postData['loginid'];
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

  public function executiveProfileAction(){
    if ($this->authService->hasIdentity()) {
      $config = $this->getConfig();
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      return new ViewModel(['userDetails' => $userDetails, 'callbackUrl' => $config['hybridauth']['callback']]);
    }else{
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }

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
    $userdetails['user_login_id'] = $postData['ccmobile'];
    $userdetails['mobile_number'] = $postData['mobile'];
    $userdetails['country_phone_code'] = $postData['cc']; //str_replace($postData['mobile'], '', $postData['phone']);
    $userdetails['email'] = $postData['email'];
    $userdetails['address'] = $postData['address'];
    $userdetails['city'] = $postData['city'];
    $userdetails['state'] = $postData['state'];
    $userdetails['age'] = $postData['age'];
    $userdetails['gender'] = $postData['gender'];
    $userdetails['education'] = $postData['education'];
    $userdetails['occupation'] = $postData['occupation'];
    $userdetails['user_type_id'] = \Admin\Model\User::TWISTT_Executive;
    $aes = new Aes();
    $encodeContent = $aes->encrypt($postData['ccmobile']);
    $userdetails['password'] = $encodeContent['password'];
    $userdetails['hash'] = $encodeContent['hash'];
    $userId = $this->userTable->userExists($userdetails['user_login_id']);
    if(!$userId){
      $ures = $this->userTable->addUser($userdetails);
    }else{
      $ures = $this->userTable->updateUser($userdetails, ['id' => $userId]);
    }
    if($ures){
      $execId = $this->executiveDetailsTable->executiveExists($userId);
      $bankDetails['user_id'] = $userId;
      $bankDetails['bank_name'] = $postData['bankName'];
      $bankDetails['bank_account_no'] = $postData['bankAccount'];
      $bankDetails['ifsc_code'] = $postData['ifsc'];
      $bankDetails['commission_percentage'] = "10";
      if(!$execId){
        $bres = $this->executiveDetailsTable->addExecutive($bankDetails);
      }else{
        $bres = $this->executiveDetailsTable->setExecutiveDetails($bankDetails, ['id' => $execId]);
      }
      if($bres){
        if (session_status() == PHP_SESSION_NONE) {
          session_start();
        }
        $_SESSION['username'] = $userdetails['username'];
        $_SESSION['user_login_id'] = $userdetails['user_login_id'];
        $this->authService->getAdapter()
                ->setIdentity($userdetails['user_login_id'])
                ->setCredential($userdetails['password']);
        $ares = $this->authService->authenticate();
        return new JsonModel(array('success' => true, "message" => 'you have succesfuly registered as TWISTT Executive.'));
      }else{
        return new JsonModel(array('success' => false, "message" => 'error saving user bank details.. please try after sometime..'));
      }
    }else{
      return new JsonModel(array('success' => false, "message" => 'error saving user details.. please try after sometime..'));
    }
  }

  public function executiveHomeAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id'=>$loginId['user_login_id']]);
      return new ViewModel(['userDetails' => $userDetails]);
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
            $exists = '0';
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
            }
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
  public function executiveForgotPasswordAction()
  {
    return new ViewModel();
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
