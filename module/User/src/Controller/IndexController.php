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
    $banners = $this->bannerTable->getBanners();
    $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_HOME_PAGE);
    return new ViewModel(['marqText' => $marqText, 'banners' => $banners, 'imageUrl' => $this->filesUrl()]);
  }
  public function aboutUsAction()
  {
    $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_ABOUT_PAGE);
    return new ViewModel();
  }

  public function contactAction()
  {
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
    try {
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

        $userdetails['user_login_id'] = $userProfile->email;
        $userdetails['username'] = $userProfile->displayName;
        $userdetails['email'] = $userProfile->email;
        $userdetails['country'] = $userProfile->country;
        $userdetails['city'] = $userProfile->city;
        $userdetails['state'] = $userProfile->region;
        $userdetails['gender'] = $userProfile->gender;
        $userdetails['user_type_id'] = \Admin\Model\User::TWISTT_Executive;
        if ($userProfile->photoURL !== null && $userProfile->photoURL !== "")
          $userdetails['photo_url'] = strtok($userProfile->photoURL, '?');
        $userdetails['access_token'] = $accessToken['access_token'];
        $userdetails['token_expiry'] = $accessToken['expires_at'];
        $aes = new Aes();
        $encodeContent = $aes->encrypt($accessToken['access_token']);
        $userdetails['password'] = $encodeContent['password'];
        $userdetails['hash'] = $encodeContent['hash'];
        $userId = $this->userTable->getField(['user_login_id' => $userdetails['user_login_id']], 'id');
        if ($userId) {
          $execId = $this->executiveDetailsTable->executiveExists($userId);
          if ($execId) {
            $updateValues['access_token'] = $userdetails['access_token'];
            $updateValues['password'] = $userdetails['password'];
            $updateValues['hash'] = $userdetails['hash'];
            $ures = $this->userTable->updateUser($updateValues, ['id' => $userId]);
            if (!$ures)
              return new ViewModel(['success' => false, "message" => 'unknown error.. please try again later..']);
            $this->authService->getAdapter()
              ->setIdentity($userdetails['user_login_id'])
              ->setCredential($userdetails['password']);
            $this->authService->authenticate();
            if ($this->authService->hasIdentity()) {
              $config = $this->getConfig();
              $loginId = $this->authService->getIdentity();
              $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
              $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/buy-coupons');
            } else {
              $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
            }
          } else {
            return new ViewModel(['success' => false, "message" => 'not a valid TWISTT executive']);
          }
        } else {
          return new ViewModel(['success' => false, "message" => 'user not found']);
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
    $userdetails['user_login_id'] = str_replace("+", "", $postData['loginid']);
    $userdetails['password'] = $postData['password'];
    $checkRole = $this->userTable->getField(['user_login_id' => $userdetails['user_login_id']], 'user_type_id');
    if($checkRole != \Admin\Model\User::TWISTT_Executive)
      return new JsonModel(array('success' => false, "message" => 'invalid credentials'));
    $res = $this->userTable->checkPasswordWithUserId($userdetails['user_login_id'], $userdetails['password']);
    if ($res) {
      $this->authService->getAdapter()
        ->setIdentity($userdetails['user_login_id'])
        ->setCredential($userdetails['password']);
      $ares = $this->authService->authenticate();
      return new JsonModel(array('success' => true, "message" => 'credentials valid'));
    } else {
      return new JsonModel(array('success' => false, "message" => 'invalid credentials'));
    }
  }

  public function executiveAddAction()
  {
    $postData = $this->params()->fromPost();
    $validImageFiles = array('png', 'jpg', 'jpeg');
    $userdetails = [];
    $bankDetails = [];
    $uploadedFiles[0] = $this->params()->fromFiles('photo');
    $uploadedFiles[1] = $this->params()->fromFiles('aadhar');
    $uploadedFiles[2] = $this->params()->fromFiles('pan');
    $i = 0;
    if (isset($uploadedFiles)) {
      foreach ($uploadedFiles as $uploadedFile) {
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
        switch ($i) {
          case 0:
            $userdetails['photo_url'] = $filePath;
            break;
          case 1:
            $userdetails['aadhar_url'] = $filePath;
            break;
          case 2:
            $userdetails['pan_url'] = $filePath;
            break;
        }
        $i++;
      }
    } else {
      return new JsonModel(array('success' => false, "message" => 'image not submitted.'));
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
    $postData['ccmobile'] = str_replace("+", "", $postData['ccmobile']);
    $aes = new Aes();
    $encodeContent = $aes->encrypt($postData['ccmobile']);
    $userdetails['password'] = $encodeContent['password'];
    $userdetails['hash'] = $encodeContent['hash'];
    $userId = $this->userTable->userExists($userdetails['user_login_id']);
    if ($userId) {
      $ures = $this->userTable->updateUser($userdetails, ['id' => $userId]);
    } else {
      return new JsonModel(array('success' => false, "message" => 'unknown error occurred.. please try after sometime..'));
    }
    if ($ures) {
      $execId = $this->executiveDetailsTable->executiveExists($userId);
      $bankDetails['user_id'] = $userId;
      $bankDetails['bank_name'] = $postData['bankName'];
      $bankDetails['bank_account_no'] = $postData['bankAccount'];
      $bankDetails['ifsc_code'] = $postData['ifsc'];
      $bankDetails['commission_percentage'] = "20";
      if (!$execId) {
        $bres = $this->executiveDetailsTable->addExecutive($bankDetails);
      } else {
        //$bres = $this->executiveDetailsTable->setExecutiveDetails($bankDetails, ['id' => $execId]);
        return new JsonModel(array('success' => false, "message" => 'TWISTT Executive already exists..'));
      }
      if ($bres) {
        $this->authService->getAdapter()
          ->setIdentity($userdetails['user_login_id'])
          ->setCredential($userdetails['password']);
        $ares = $this->authService->authenticate();
        return new JsonModel(array('success' => true, "message" => 'you have succesfully registered as TWISTT Executive.'));
      } else {
        return new JsonModel(array('success' => false, "message" => 'error saving user bank details.. please try after sometime..'));
      }
    } else {
      return new JsonModel(array('success' => false, "message" => 'error saving user details.. please try after sometime..'));
    }
  }
  public function executiveEditAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userId = $this->userTable->userExists($loginId['user_login_id']);
      $checkRole = $this->userTable->getField(['id' => $userId], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $postData = $this->params()->fromPost();
      $validImageFiles = array('png', 'jpg', 'jpeg');
      $userdetails = [];
      $bankDetails = [];
      $uploadedFiles[0] = $this->params()->fromFiles('photo');
      $uploadedFiles[1] = $this->params()->fromFiles('aadhar');
      $uploadedFiles[2] = $this->params()->fromFiles('pan');
      $i = 0;
      if (isset($uploadedFiles)) {
        foreach ($uploadedFiles as $uploadedFile) {
          if ($uploadedFile['error'] !== UPLOAD_ERR_NO_FILE) {
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
            } else { // new photo successfully uploaded - delete old photo
              switch ($i) {
                case 0:
                  $oldPPUrl = $this->userTable->getField(['id' => $userId], 'photo_url');
                  break;
                case 1:
                  $oldPPUrl = $this->userTable->getField(['id' => $userId], 'aadhar_url');
                  break;
                case 2:
                  $oldPPUrl = $this->userTable->getField(['id' => $userId], 'pan_url');
                  break;
              }
              if ($this->fileExists($oldPPUrl)) {
                if (!$this->deleteFile($oldPPUrl)) {
                  return new JsonModel(array('success' => false, "message" => 'unable to delete old photo..'));
                }
              } /* else {
                return new JsonModel(array('success' => false, "message" => 'unable to find old photo to be replaced..'));
              } */
            }
            switch ($i) {
              case 0:
                $userdetails['photo_url'] = $filePath;
                break;
              case 1:
                $userdetails['aadhar_url'] = $filePath;
                break;
              case 2:
                $userdetails['pan_url'] = $filePath;
                break;
            }
          }
          $i++;
        }
      } else {
        return new JsonModel(array('success' => false, "message" => 'Photo not submitted.'));
      }

      $userdetails['username'] = $postData['name'];
      $userdetails['email'] = $postData['email'];
      $userdetails['country'] = $postData['country'];
      $userdetails['city'] = $postData['city'];
      $userdetails['state'] = $postData['state'];
      $userdetails['gender'] = $postData['gender'];
      if ($userId) {
        $ures = $this->userTable->updateUser($userdetails, ['id' => $userId]);
      } else {
        return new JsonModel(array('success' => false, "message" => 'unknown error occurred.. please try after sometime..'));
      }
      if ($ures) {
        $execId = $this->executiveDetailsTable->executiveExists($userId);
        $bankDetails['user_id'] = $userId;
        $bankDetails['bank_name'] = $postData['bankName'];
        $bankDetails['bank_account_no'] = $postData['bankAccount'];
        $bankDetails['ifsc_code'] = $postData['ifsc'];
        if (!$execId) {
          $bres = $this->executiveDetailsTable->addExecutive($bankDetails);
        } else {
          $bres = $this->executiveDetailsTable->setExecutiveDetails($bankDetails, ['id' => $execId]);
        }
        if ($bres) {
          return new JsonModel(array('success' => true, "message" => 'updated succesfully..'));
        } else {
          return new JsonModel(array('success' => false, "message" => 'error saving user bank details.. please try after sometime..'));
        }
      } else {
        return new JsonModel(array('success' => false, "message" => 'error saving user details.. please try after sometime..'));
      }
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executiveProfileAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
      $checkRole = $this->userTable->getField(['id' => $userDetails['id']], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'config' => $config['hybridauth'], 'imageUrl' => $this->filesUrl()]);
    } else {
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
      $otpType = $request['otpType'];
      $vm = $request['vm'];
      $rb = $request['rb'];
      if ($otpType == \Admin\Model\Otp::Executive_Registration) {
        if ($cc == "91") {
          $otp = $this->generateOtp();
          $otpRes = $this->otpTable->addOtpDetails(['otp' => $otp, 'otp_type_id' => $otpType, 'verification_mode' => $vm, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => $rb, 'otp_sent_to' => $ccmobile]);
          if ($otpRes) {
            $resp = "1"; //$this->sendOtpSms($ccmobile, $otp);
            if ($resp) {
              return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
            } else {
              return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again after sometime..'));
            }
          } else {
            return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again after sometime..'));
          }
        }
      } else if ($otpType == \Admin\Model\Otp::Forgot_Password) {
        if (!empty($mobile)) {
          $isMobileNoRegistered = $this->userTable->getField(['mobile_number' => $mobile], 'id');
          if ($isMobileNoRegistered) {
            $getcc = $this->userTable->getField(['mobile_number' => $mobile], 'country_phone_code');
            if ($getcc == "91") {
              $otp = $this->generateOtp();
              $otpRes = $this->otpTable->addOtpDetails(['otp' => $otp, 'otp_type_id' => $otpType, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => $rb, 'otp_sent_to' => $mobile]);
              if ($otpRes) {
                $resp = "1"; //$this->sendOtpSms($ccmobile, $otp);
                if ($resp) {
                  return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
                } else {
                  return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again after sometime..'));
                }
              } else {
                return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again after sometime..'));
              }
            } else {
              return new JsonModel(array('success' => false, "message" => 'not a registered mobile no..'));
            }
          } /* else{
            $isEmailRegistered = $this->userTable->getField(['email' => $mobile], 'id');
            if($isEmailRegistered){
              $otp = $this->generateOtp();
              $otpRes = $this->otpTable->addOtpDetails(['otp' => $otp, 'otp_type_id' => $otpType, 'verification_mode' => \Admin\Model\Otp::Email_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => $rb, 'otp_sent_to' => $mobile]);
              if($otpRes){
                $resp = $this->sendmail($mobile, 'otp to reset your password', 'forgot-password', $otp);
                if($resp){
                  return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
                }else{
                  return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again after sometime..'));
                }
              }else{
                return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again after sometime..'));
              }
            }else{
              return new JsonModel(array('success' => false, "message" => 'not a registered mobile no / email id'));
            }
          } */
        } else {
          return new JsonModel(array('success' => false, "message" => 'please provide registered mobile no / email id'));
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
      if ($type == \Admin\Model\Otp::Executive_Registration) {
        if ($cc == "91") {
          if ($otp == '' || $otp == null) {
            return new JsonModel(array("success" => false, "message" => "OTP is required"));
          }
          if ($ccmobile == '' || $ccmobile == null) {
            return new JsonModel(array("success" => false, "message" => "mobile number is missing"));
          }
        }
        $data = ['otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Executive_Registration, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::TWISTT_Request, 'otp_sent_to' => $ccmobile];
        $verify = $this->otpTable->verifyOtp($data);
        if ($verify) {
          $updateResponse = $this->otpTable->setOtpDetails(array('sent_status_id' => \Admin\Model\Otp::Is_verifed), $data);
          if ($updateResponse) {
            return new JsonModel(array("success" => true, "message" => "Otp verified succesfully.. please enter your details.."));
          } else {
            return new JsonModel(array("success" => false, "message" => "unable to verify otp.. please try again"));
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
        } else {
          return new JsonModel(array("success" => false, "message" => "otp not valid.. please try again"));
        }
      } elseif ($type == \Admin\Model\Otp::Forgot_Password) {
        $data = ['otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Forgot_Password, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::TWISTT_Request, 'otp_sent_to' => $mobile];
        $verify = $this->otpTable->verifyOtp($data);
        if ($verify) {
          $updateResponse = $this->otpTable->setOtpDetails(array('sent_status_id' => \Admin\Model\Otp::Is_verifed), $data);
          if ($updateResponse) {
            return new JsonModel(array("success" => true, "message" => "Otp verified succesfully.."));
          } else {
            return new JsonModel(array("success" => false, "message" => "unable to verify otp.. please try again"));
          }
        } else {
          return new JsonModel(array("success" => false, "message" => "wrong otp"));
        }
      } else {
        return new JsonModel(array("success" => false, "message" => "wrong otp type"));
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
      if ($userId) {
        $execId = $this->executiveDetailsTable->executiveExists($userId);
        if ($execId)
          return new JsonModel(array('success' => false, "message" => 'mobile number already registered.'));
      }
    }
  }
  public function executiveBuyCouponsAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
      $checkRole = $this->userTable->getField(['id' => $userDetails['id']], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $questtValid = $this->questtSubscriptionTable->isValidQuesttUser($userDetails['id']);
      $qed = $this->questtSubscriptionTable->getField(['user_id' => $loginId['id']], 'end_date');
      $qed = date('d-m-Y', strtotime($qed));
      $udcp = 0;
      $uccp = 0;
      if ($questtValid && $bankDetails['banned'] == '0') {
        if ($userDetails['country_phone_code'] == '91') {
          $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_inr');
          $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_inr');
        } else {
          $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_usd');
          $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_usd');
        }
      }
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'config' => $config['hybridauth'], 'imageUrl' => $this->filesUrl(), 'isQUESTTValid' => $questtValid, 'udcp' => $udcp, 'uccp' => $uccp, 'qed' => $qed]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executivePayAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
      $checkRole = $this->userTable->getField(['id' => $userDetails['id']], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        return new JsonModel(array('success' => false, "message" => 'invalid operation..'));
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $questtValid = $this->questtSubscriptionTable->isValidQuesttUser($userDetails['id']);
      if ($questtValid && $bankDetails['banned'] == '0') {
        $request = $this->getRequest()->getPost();
        $dc = $request['dc'];
        $cc = $request['cc'];

        if ($dc == 0 && $cc == 0) {
          return new JsonModel(array('success' => false, "message" => 'Please mention the no of coupons to purchase..'));
        }
        if ($userDetails['country_phone_code'] == '91') {
          $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_inr');
          $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_inr');
        } else {
          $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_usd');
          $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_usd');
        }
        $totalAmount = $dc * $udcp + $cc * $uccp;
        if ($totalAmount != 0 && round($totalAmount) != 0) {
          $saveData['payment_status'] = \Admin\Model\ExecutivePurchase::payment_in_process;
          $saveData['user_id'] = $userDetails['id'];
          $saveData['executive_id'] = $bankDetails['id'];
          $saveData['amount'] = number_format((float)$totalAmount, 2, '.', '');
          if ($userDetails['country_phone_code'] == "91") {
            $saveData['currency'] = "INR";
          } else {
            $saveData['currency'] = "USD";
          }
          $purchaseResp = $this->executivePurchaseTable->addExecutivePurchase($saveData);
          if ($purchaseResp['success'])
            $purchaseId = $purchaseResp['id'];
          if ($purchaseId) {
            $api = new Razorpay();
            if ($userDetails['country_phone_code'] == "91") {
              $orderData = [
                'amount'          => intval($totalAmount * 100),
                'currency'        => 'INR',
                'receipt' => 'TC_' . $purchaseId,
                'payment_capture' => 1
              ];
            } else {
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
            if ($response) {
              $setResp = $this->executivePurchaseTable->setExecutivePurchase(['receipt' => $razorpayOrder['receipt'], 'razorpay_order_id' => $razorpayOrder['id']], ['id' => $purchaseId]);
              /* if (session_status() == PHP_SESSION_NONE) {
                session_start();
              }
               $_SESSION['razorpay_order_id'] = $razorpayOrder['id']; */
              if ($setResp) {
                return new JsonModel(array('success' => true, 'message' => 'order created', 'order' => $razorpayOrder));
              } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to process your payment now.. please try after sometime..'));
              }
            } else {
              return new JsonModel(array('success' => false, 'message' => 'unable to process your payment now.. please try after sometime..'));
            }
          }
        }
      } else {
        return new JsonModel(array('success' => false, "message" => 'coupons purchase not allowed..'));
      }
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executiveCheckoutAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
      $checkRole = $this->userTable->getField(['id' => $userDetails['id']], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        return new JsonModel(array('success' => false, "message" => 'invalid operation..'));
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $questtValid = $this->questtSubscriptionTable->isValidQuesttUser($userDetails['id']);

      if ($questtValid && $bankDetails['banned'] == '0') {
        $request = $this->getRequest()->getPost();
        $dc = $request['dc'];
        $cc = $request['cc'];
        $razorpay_payment_id = $request['razorpay_payment_id'];
        $razorpay_order_id = $request['razorpay_order_id'];
        $razorpay_signature = $request['razorpay_signature'];

        /* if (session_status() == PHP_SESSION_NONE) {
          session_start();
        } */
        $attributes = array(
          'razorpay_order_id' => $razorpay_order_id, //$_SESSION['razorpay_order_id'],
          'razorpay_payment_id' => $razorpay_payment_id,
          'razorpay_signature' => $razorpay_signature
        );
        $api = new Razorpay();
        try {
          $api->checkPaymentSignature($attributes);
          $orderResp = $api->checkOrderStatus($razorpay_order_id);
          if ($orderResp['status'] !== "paid")
            return new JsonModel(array('success' => false, 'message' => 'payment not successful'));

          $setResp = $this->executivePurchaseTable->setExecutivePurchase(['razorpay_payment_id' => $razorpay_payment_id, 'razorpay_signature' => $razorpay_signature, 'payment_status' => \Admin\Model\ExecutivePurchase::payment_success], ['razorpay_order_id' => $razorpay_order_id]);
          if ($setResp) {
            $purchase = $this->executivePurchaseTable->getExecutivePurchase(['razorpay_payment_id' => $razorpay_payment_id]);
            $ved = $this->questtSubscriptionTable->getField(['user_id' => $purchase['user_id']], 'end_date');
            if ($userDetails['country_phone_code'] == '91') {
              $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_inr');
              $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_inr');
            } else {
              $udcp = $this->subscriptionPlanTable->getField(['id' => 1], 'dp_usd');
              $uccp = $this->subscriptionPlanTable->getField(['id' => 1], 'ccp_usd');
            }
            for ($i = 0; $i < $dc; $i++) {
              $coupons[$i]['executive_id'] = $purchase['executive_id'];
              $coupons[$i]['purchase_id'] = $purchase['id'];
              $coupons[$i]['coupon_type'] = \Admin\Model\Coupons::Coupon_Type_Discount;
              $coupons[$i]['coupon_code'] = $this->generateCouponCode('D');
              $coupons[$i]['amount'] = $udcp;
              $coupons[$i]['validity_end_date'] = $ved;
              $coupons[$i]['coupon_status'] = \Admin\Model\Coupons::Coupon_Status_Active;
            }
            $count = count($coupons);
            for ($j = $count; $j < $count + $cc; $j++) {
              $coupons[$j]['executive_id'] = $purchase['executive_id'];
              $coupons[$j]['purchase_id'] = $purchase['id'];
              $coupons[$j]['coupon_type'] = \Admin\Model\Coupons::Coupon_Type_Complimentary;
              $coupons[$j]['coupon_code'] = $this->generateCouponCode('C');
              $coupons[$j]['amount'] = $uccp;
              $coupons[$j]['validity_end_date'] = $ved;
              $coupons[$j]['coupon_status'] = \Admin\Model\Coupons::Coupon_Status_Active;
            }
            $miresp = $this->couponsTable->addMutipleCoupons($coupons);
            if ($miresp) {
              return new JsonModel(array('success' => true, 'message' => 'payment successful'));
            } else {
              return new JsonModel(array('success' => false, 'message' => 'unknown error'));
            }
          } else {
            return new JsonModel(array('success' => false, 'message' => 'unable to process.. please after sometime'));
          }
        } catch (\Exception $e) {
          return new JsonModel(array('success' => false, "message" => 'Razorpay Error : ' . $e->getMessage()));
        }
      } else {
        return new JsonModel(array('success' => false, "message" => 'coupons purchase not allowed..'));
      }
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executiveTrackCouponsAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
      $checkRole = $this->userTable->getField(['id' => $userDetails['id']], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $qed = $this->questtSubscriptionTable->getField(['user_id' => $loginId['id']], 'end_date');
      $qed = date('d-m-Y', strtotime($qed));
      $coupons = $this->couponsTable->getCouponsList(['executive_id' => $bankDetails['id'], 'limit' => 10, 'offset' => 0]);
      $totalCount = $this->couponsTable->getCouponsList(['executive_id' => $bankDetails['id']], 1);
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'config' => $config['hybridauth'], 'coupons' => $coupons, 'totalCount' => $totalCount, 'qed' => $qed, 'imageUrl' => $this->filesUrl()]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }

  public function loadCouponsListAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $searchData = array('limit' => 10, 'offset' => 0);
      $type = $request['type'];
      $offset = 0;
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
      $checkRole = $this->userTable->getField(['id' => $userDetails['id']], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);

      if (isset($request['page_number'])) {
        $pageNumber = $request['page_number'];
        $offset = ($pageNumber * 10 - 10);
        $limit = 10;
        $searchData['offset'] = $offset;
        $searchData['limit'] = $limit;
      }
      $searchData['executive_id'] = $bankDetails['id'];
      $totalCount = 0;

      if ($type && $type == 'search') {
        $totalCount = $this->couponsTable->getCouponsList($searchData, 1);
      }
      $coupons = $this->couponsTable->getCouponsList($searchData);
      $view = new ViewModel(array('coupons' => $coupons, 'totalCount' => $totalCount));
      $view->setTerminal(true);
      return $view;
    }
  }
  public function executiveTrackCommissionsAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
      $checkRole = $this->userTable->getField(['id' => $userDetails['id']], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $qed = $this->questtSubscriptionTable->getField(['user_id' => $loginId['id']], 'end_date');
      $qed = date('d-m-Y', strtotime($qed));
      $transactions = $this->executiveTransactionTable->getTransactionsList(['executive_id' => $bankDetails['id'], 'limit' => 10, 'offset' => 0]);
      $totalCount = $this->executiveTransactionTable->getTransactionsList(['executive_id' => $bankDetails['id']], 1);
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'transactions' => $transactions, 'totalCount' => $totalCount, 'config' => $config['hybridauth'], 'qed' => $qed, 'imageUrl' => $this->filesUrl()]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }

  public function loadTransactionsListAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $searchData = array('limit' => 10, 'offset' => 0);
      $type = $request['type'];
      $offset = 0;
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
      $checkRole = $this->userTable->getField(['id' => $userDetails['id']], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $qed = $this->questtSubscriptionTable->getField(['user_id' => $loginId['id']], 'end_date');
      $qed = date('d-m-Y', strtotime($qed));
      if (isset($request['page_number'])) {
        $pageNumber = $request['page_number'];
        $offset = ($pageNumber * 10 - 10);
        $limit = 10;
        $searchData['offset'] = $offset;
        $searchData['limit'] = $limit;
      }
      $searchData['executive_id'] = $bankDetails['id'];
      $totalCount = 0;

      if ($type && $type == 'search') {
        $totalCount = $this->executiveTransactionTable->getTransactionsList($searchData, 1);
      }
      $transactions = $this->executiveTransactionTable->getTransactionsList($searchData);
      $view = new ViewModel(array('transactions' => $transactions, 'totalCount' => $totalCount));
      $view->setTerminal(true);
      return $view;
    }
  }
  public function questtValidityAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $lid = $request['lid'];
      $userId = $this->userTable->getField(['user_login_id' => $lid], 'id');
      if ($userId) {
        $isValidQuesttUser = $this->questtSubscriptionTable->isValidQuesttUser($userId);
        if ($isValidQuesttUser)
          return new JsonModel(array('success' => true, "message" => 'Valid QUESTT user.. may proceed with TWISTT executive registration process..'));
        else
          return new JsonModel(array('success' => false, "message" => 'not a valid QUESTT user'));
      } else {
        return new JsonModel(array('success' => false, "message" => 'user not found'));
      }
    }
  }
  public function twisttExecutiveTermsAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->userTable->getUserDetails(['user_login_id' => $loginId['user_login_id']]);
      $checkRole = $this->userTable->getField(['id' => $userDetails['id']], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['user_id' => $userDetails['id']]);
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'bankDetails' => $bankDetails, 'config' => $config['hybridauth'], 'imageUrl' => $this->filesUrl()]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function executiveTermsAction()
  {
    return new ViewModel();
  }
  public function executiveForgotPasswordAction()
  {
    return new ViewModel();
  }

  public function changePasswordAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userId = $this->userTable->userExists($loginId['user_login_id']);
      $checkRole = $this->userTable->getField(['id' => $userId], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $config = $this->getConfig();
      return new ViewModel(['userId' => $loginId['user_login_id'], 'config' => $config['hybridauth'],]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
    }
  }
  public function resetPasswordAction()
  {
    $postData = $this->params()->fromPost();
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userId = $this->userTable->userExists($loginId['user_login_id']);
      $checkRole = $this->userTable->getField(['id' => $userId], 'user_type_id');
      if($checkRole != \Admin\Model\User::TWISTT_Executive)
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      $check = $this->userTable->checkPasswordWithUserId($loginId['user_login_id'], $postData['current_password']);
      if ($check) {
        $aes = new Aes();
        $encodeContent = $aes->encrypt($postData['new_password']);
        $userdetails['password'] = $encodeContent['password'];
        $userdetails['hash'] = $encodeContent['hash'];
        $res = $this->userTable->updateUser($userdetails, ['id' => $check['id']]);
        if ($res)
          return new JsonModel(array('success' => true, "message" => 'password changed succesfully..'));
        else
          return  new JsonModel(array('success' => false, "message" => 'unable to change password.. try after sometime..'));
      } else {
        return new JsonModel(array('success' => false, "message" => 'Current password wrong'));
      }
    } else {
      if ($this->getRequest()->isXmlHttpRequest()) {
        $request = $this->getRequest()->getPost();
        $type = $request['type'];
        $otp = $request['otp'];
        $mobile = $request['mobile'];
        $new_password = $request['new_password'];
        $data = ['otp' => $otp, 'otp_type_id' => $type, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Is_verifed, 'otp_requested_by' => \Admin\Model\Otp::TWISTT_Request, 'otp_sent_to' => $mobile];
        $verify = $this->otpTable->verifyOtp($data);
        if ($verify) {
          $aes = new Aes();
          $encodeContent = $aes->encrypt($new_password);
          $userdetails['password'] = $encodeContent['password'];
          $userdetails['hash'] = $encodeContent['hash'];
          $res = $this->userTable->updateUser($userdetails, ['mobile_number' => $mobile]);
          if ($res)
            return new JsonModel(array('success' => true, "message" => 'password changed succesfully..'));
          else
            return  new JsonModel(array('success' => false, "message" => 'unable to change password.. try after sometime..'));
        }
        return  new JsonModel(array('success' => false, "message" => 'unable to change password.. try after sometime..'));
      } else {
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/executive/login');
      }
    }
  }

  public function enablerRegisterAction()
  {
    $config = $this->getConfig();
    return new ViewModel(['callbackUrl' => $config['hybridauth']['callback']]);
  }

  public function enablerLoginAction()
  {
    $config = $this->getConfig();
    return new ViewModel(['callbackUrl' => $config['enbhybridauth']['callback']]);
  }
  public function sttEnablerAuthAction()
  {
    $postData = $this->params()->fromPost();
    $userdetails['user_login_id'] = str_replace("+", "", $postData['loginid']);
    $userdetails['password'] = $postData['password'];
    $res = $this->enablerTable->checkPasswordWithUserId($userdetails['user_login_id'], $userdetails['password']);
    if ($res) {
      $this->authService->getAdapter()
        ->setIdentity($userdetails['user_login_id'])
        ->setCredential($userdetails['password']);
      $ares = $this->authService->authenticate();
      var_dump($ares); exit();
      return new JsonModel(array('success' => true, "message" => 'credentials valid'));
    } else {
      return new JsonModel(array('success' => false, "message" => 'invalid credentials'));
    }
  }
  public function enablerAuthAction()
  {
    try {
      $config = $this->getConfig();
      $hybridauth = new Hybridauth($config['enbhybridauth']);
      $storage = new Session();
      $error = false;

      if (isset($_GET['provider'])) {
        if (in_array($_GET['provider'], $hybridauth->getProviders())) {
          $storage->set('provider', $_GET['provider']);
        } else {
          $error = $_GET['provider'];
        }
      }

      if (isset($_GET['logout'])) {
        if (in_array($_GET['logout'], $hybridauth->getProviders())) {
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

        $userdetails['user_login_id'] = $userProfile->email;
        $userdetails['username'] = $userProfile->displayName;
        $userdetails['company_name'] = $userProfile->displayName;
        $userdetails['email'] = $userProfile->email;
        $userdetails['country'] = $userProfile->country;
        $userdetails['city'] = $userProfile->city;
        $userdetails['login_type'] = \Admin\Model\Enabler::login_type_social;
        if ($userProfile->photoURL !== null && $userProfile->photoURL !== "")
          $userdetails['photo_url'] = strtok($userProfile->photoURL, '?');
        $userdetails['access_token'] = $accessToken['access_token'];
        $userdetails['token_expiry'] = $accessToken['expires_at'];
        $aes = new Aes();
        $encodeContent = $aes->encrypt($accessToken['access_token']);
        $userdetails['password'] = $encodeContent['password'];
        $userdetails['hash'] = $encodeContent['hash'];
        $userId = $this->enablerTable->getField(['user_login_id' => $userdetails['user_login_id']], 'id');
        if ($userId) {
          $updateValues['access_token'] = $userdetails['access_token'];
          $updateValues['password'] = $userdetails['password'];
          $updateValues['hash'] = $userdetails['hash'];
          $ures = $this->enablerTable->updateEnabler($updateValues, ['id' => $userId]);
          if (!$ures)
            return new ViewModel(['success' => false, "message" => 'unknown error.. please try again later..']);
        } else {
          $ures = $this->enablerTable->addEnabler($userdetails);
          if (!$ures)
            return new ViewModel(['success' => false, "message" => 'unknown error.. please try again later..']);
        }
        $this->authService->getAdapter()
          ->setIdentity($userdetails['user_login_id'])
          ->setCredential($userdetails['password']);
        $this->authService->authenticate();
        if ($this->authService->hasIdentity()) {
          $config = $this->getConfig();
          $loginId = $this->authService->getIdentity();
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/buy-plans');
        } else {
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
        }
      }
    } catch (Exception $e) {
      error_log($e->getMessage());
      echo $e->getMessage();
    }
  }

  public function enablerSendOtpAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $mobile = $request['mobile'];
      $email = $request['email'];
      $ccmobile = $request['ccmobile'];
      $cc = $request['cc'];
      $otpType = $request['otpType'];
      $vm = $request['vm'];
      $rb = $request['rb'];
      if ($otpType == \Admin\Model\Otp::Enabler_Registration) {
        if ($cc == "91") {
          $otp = $this->generateOtp();
          $otpRes = $this->otpTable->addOtpDetails(['otp' => $otp, 'otp_type_id' => $otpType, 'verification_mode' => $vm, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => $rb, 'otp_sent_to' => $ccmobile]);
          if ($otpRes) {
            $resp = "1"; //$this->sendOtpSms($ccmobile, $otp);
            if ($resp) {
              return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
            } else {
              return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again after sometime..'));
            }
          } else {
            return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again after sometime..'));
          }
        }else{
          $otp = $this->generateOtp();
          $otpRes = $this->otpTable->addOtpDetails(['otp' => $otp, 'otp_type_id' => $otpType, 'verification_mode' => $vm, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => $rb, 'otp_sent_to' => $email]);
          if($otpRes){
            $resp = $this->sendmail($email, 'otp to reset your password', 'ten-verify-email', $otp);
            if($resp){
              return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
            }else{
              return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again after sometime..'));
            }
          }else{
            return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again after sometime..'));
          }
        }
      } else if ($otpType == \Admin\Model\Otp::Forgot_Password) {
        if (!empty($mobile)) {
          $isMobileNoRegistered = $this->userTable->getField(['mobile_number' => $mobile], 'id');
          if ($isMobileNoRegistered) {
            $getcc = $this->userTable->getField(['mobile_number' => $mobile], 'country_phone_code');
            if ($getcc == "91") {
              $otp = $this->generateOtp();
              $otpRes = $this->otpTable->addOtpDetails(['otp' => $otp, 'otp_type_id' => $otpType, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => $rb, 'otp_sent_to' => $mobile]);
              if ($otpRes) {
                $resp = "1"; //$this->sendOtpSms($ccmobile, $otp);
                if ($resp) {
                  return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
                } else {
                  return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again after sometime..'));
                }
              } else {
                return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again after sometime..'));
              }
            } else {
              return new JsonModel(array('success' => false, "message" => 'not a registered mobile no..'));
            }
          } else{
            $isEmailRegistered = $this->userTable->getField(['email' => $email], 'id');
            if($isEmailRegistered){
              $otp = $this->generateOtp();
              $otpRes = $this->otpTable->addOtpDetails(['otp' => $otp, 'otp_type_id' => $otpType, 'verification_mode' => \Admin\Model\Otp::Email_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => $rb, 'otp_sent_to' => $email]);
              if($otpRes){
                $resp = $this->sendmail($email, 'otp to reset your password', 'forgot-password', $otp);
                if($resp){
                  return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
                }else{
                  return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again after sometime..'));
                }
              }else{
                return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again after sometime..'));
              }
            }else{
              return new JsonModel(array('success' => false, "message" => 'not a registered mobile no / email id'));
            }
          }
        } else {
          return new JsonModel(array('success' => false, "message" => 'please provide valid mobile no / email id'));
        }
      }
      return new JsonModel(array('success' => true, "message" => 'otp sent successfully.. please enter the otp..'));
    }else{
      return new JsonModel(array("success" => false, "message" => "unkown request"));
    }
  }

  public function enablerVerifyOtpAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $mobile = $request['mobile'];
      $email = $request['email'];
      $ccmobile = $request['ccmobile'];
      $cc = $request['cc'];
      $otp = $request['otp'];
      $type = $request['otp_type'];
      $data = [];
      if ($type == \Admin\Model\Otp::Enabler_Registration) {
        if ($otp == '' || $otp == null) {
          return new JsonModel(array("success" => false, "message" => "OTP is required"));
        }
        if ($cc == "91") {
          if ($ccmobile == '' || $ccmobile == null) {
            return new JsonModel(array("success" => false, "message" => "mobile number is missing"));
          }
          $data = ['otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Enabler_Registration, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::TWISTT_Request, 'otp_sent_to' => $ccmobile];
        }else{
          if ($email == '' || $email == null) {
            return new JsonModel(array("success" => false, "message" => "email id is missing"));
          }
          $data = ['otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Enabler_Registration, 'verification_mode' => \Admin\Model\Otp::Email_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::TWISTT_Request, 'otp_sent_to' => $email];
        }
        
        $verify = $this->otpTable->verifyOtp($data);
        if ($verify) {
          $updateResponse = $this->otpTable->setOtpDetails(array('sent_status_id' => \Admin\Model\Otp::Is_verifed), $data);
          if ($updateResponse) {
            return new JsonModel(array("success" => true, "message" => "Otp verified succesfully.. accept terms & conditions to proceed with registration.."));
          } else {
            return new JsonModel(array("success" => false, "message" => "unable to verify otp.. please try again"));
          }
        } else {
          return new JsonModel(array("success" => false, "message" => "otp not valid.. please try again"));
        }
      } elseif ($type == \Admin\Model\Otp::Forgot_Password) {
        $data = ['otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Forgot_Password, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::TWISTT_Request, 'otp_sent_to' => $mobile];
        $verify = $this->otpTable->verifyOtp($data);
        if ($verify) {
          $updateResponse = $this->otpTable->setOtpDetails(array('sent_status_id' => \Admin\Model\Otp::Is_verifed), $data);
          if ($updateResponse) {
            return new JsonModel(array("success" => true, "message" => "Otp verified succesfully.."));
          } else {
            return new JsonModel(array("success" => false, "message" => "unable to verify otp.. please try again"));
          }
        } else {
          return new JsonModel(array("success" => false, "message" => "wrong otp"));
        }
      } else {
        return new JsonModel(array("success" => false, "message" => "wrong otp type"));
      }
    }else{
      return new JsonModel(array("success" => false, "message" => "unkown request"));
    }
  }

  public function enablerAddAction()
  {
    $postData = $this->params()->fromPost();
    $validImageFiles = array('png', 'jpg', 'jpeg');
    $enablerdetails = [];
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
        $enablerdetails['photo_url'] = $filePath;
    } else {
      return new JsonModel(array('success' => false, "message" => 'image not submitted.'));
    }

    $enablerdetails['username'] = $postData['name'];
    $enablerdetails['company_name'] = $postData['cname'];
    $enablerdetails['mobile_number'] = $postData['mobile'];
    $enablerdetails['country_phone_code'] = $postData['cc'];
    $enablerdetails['email'] = $postData['email'];
    $postData['ccmobile'] = str_replace("+", "", $postData['ccmobile']);
    if($postData['cc'] == "91"){
      $enablerdetails['login_type'] = \Admin\Model\Enabler::login_type_mobile;
      $enablerdetails['user_login_id'] = $postData['ccmobile'];
    }else{
      $enablerdetails['login_type'] = \Admin\Model\Enabler::login_type_email;
      $enablerdetails['user_login_id'] = $postData['email'];
    }
    $enablerdetails['country'] = $postData['country'];
    $enablerdetails['city'] = $postData['city'];
    $aes = new Aes();
    $encodeContent = $aes->encrypt($postData['ccmobile']);
    $enablerdetails['password'] = $encodeContent['password'];
    $enablerdetails['hash'] = $encodeContent['hash'];
    $enablerId = $this->enablerTable->enablerExists($enablerdetails['user_login_id']);
    if ($enablerId) {
      return new JsonModel(array('success' => false, "message" => 'TWISTT Enabler already exists'));
    }
    $eres = $this->enablerTable->addEnabler($enablerdetails);
    if ($eres['id']) {
      $this->authService->getAdapter()
        ->setIdentity($enablerdetails['user_login_id'])
        ->setCredential($enablerdetails['password']);
      $ares = $this->authService->authenticate();
      return new JsonModel(array('success' => true, "message" => 'you have succesfully registered as TWISTT Enabler..'));
    } else {
      return new JsonModel(array('success' => false, "message" => 'error saving your details.. please try after sometime..'));
    }
  }

  public function enablerEditAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $userId = $this->enablerTable->enablerExists($loginId['user_login_id']);
      $postData = $this->params()->fromPost();
      $validImageFiles = array('png', 'jpg', 'jpeg');
      $userdetails = [];
      $uploadedFile = $this->params()->fromFiles('photo');

      if (isset($uploadedFile)) {
          if ($uploadedFile['error'] !== UPLOAD_ERR_NO_FILE) {
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
            } else { // new photo successfully uploaded - delete old photo
              $oldPPUrl = $this->enablerTable->getField(['id' => $userId], 'photo_url');
              if ($this->fileExists($oldPPUrl)) {
                if (!$this->deleteFile($oldPPUrl)) {
                  return new JsonModel(array('success' => false, "message" => 'unable to delete old photo..'));
                }
              } /* else {
                return new JsonModel(array('success' => false, "message" => 'unable to find old photo to be replaced..'));
              } */
            }
            $userdetails['photo_url'] = $filePath;
          }
      } else {
        return new JsonModel(array('success' => false, "message" => 'Photo not submitted.'));
      }

      $userdetails['username'] = $postData['name'];
      $userdetails['country'] = $postData['country'];
      $userdetails['city'] = $postData['city'];
      $userdetails['company_name'] = $postData['cname'];
      if ($userId) {
        $ures = $this->enablerTable->updateEnabler($userdetails, ['id' => $userId]);
      } else {
        return new JsonModel(array('success' => false, "message" => 'unknown error occurred.. please try after sometime..'));
      }
      if ($ures) {
        return new JsonModel(array('success' => true, "message" => 'updated succesfully..'));
      } else {
        return new JsonModel(array('success' => false, "message" => 'error saving user details.. please try after sometime..'));
      }
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function enablerForgotPasswordAction()
  {
    return new ViewModel();
  }

  public function enablerChangePasswordAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $config = $this->getConfig();
      return new ViewModel(['userId' => $loginId['user_login_id'], 'config' => $config['hybridauth']]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }
  public function enablerResetPasswordAction()
  {
    $postData = $this->params()->fromPost();
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $check = $this->enablerTable->checkPasswordWithUserId($loginId['user_login_id'], $postData['current_password']);
      if ($check) {
        $aes = new Aes();
        $encodeContent = $aes->encrypt($postData['new_password']);
        $userdetails['password'] = $encodeContent['password'];
        $userdetails['hash'] = $encodeContent['hash'];
        $res = $this->enablerTable->updateEnabler($userdetails, ['id' => $check['id']]);
        if ($res)
          return new JsonModel(array('success' => true, "message" => 'password changed succesfully..'));
        else
          return  new JsonModel(array('success' => false, "message" => 'unable to change password.. try after sometime..'));
      } else {
        return new JsonModel(array('success' => false, "message" => 'Current password wrong'));
      }
    } else {
      if ($this->getRequest()->isXmlHttpRequest()) {
        $request = $this->getRequest()->getPost();
        $type = $request['type'];
        $otp = $request['otp'];
        $mobile = $request['mobile'];
        $new_password = $request['new_password'];
        $data = ['otp' => $otp, 'otp_type_id' => $type, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Is_verifed, 'otp_requested_by' => \Admin\Model\Otp::TWISTT_Request, 'otp_sent_to' => $mobile];
        $verify = $this->otpTable->verifyOtp($data);
        if ($verify) {
          $aes = new Aes();
          $encodeContent = $aes->encrypt($new_password);
          $userdetails['password'] = $encodeContent['password'];
          $userdetails['hash'] = $encodeContent['hash'];
          $res = $this->enablerTable->updateEnabler($userdetails, ['mobile_number' => $mobile]);
          if ($res)
            return new JsonModel(array('success' => true, "message" => 'password changed succesfully..'));
          else
            return  new JsonModel(array('success' => false, "message" => 'unable to change password.. try after sometime..'));
        }
        return  new JsonModel(array('success' => false, "message" => 'unable to change password.. try after sometime..'));
      } else {
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      }
    }
  }

  public function enablerProfileAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($userDetails))
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $config = $this->getConfig();
      if($userDetails['login_type'] == \Admin\Model\Enabler::login_type_social) 
        $imageUrl = "";
      else
        $imageUrl = $this->filesUrl();
      return new ViewModel(['userDetails' => $userDetails, 'config' => $config['hybridauth'], 'imageUrl' => $imageUrl]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function enablerBuyPlansAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $epWhere['status'] = \Admin\Model\EnablerPlans::status_active;
      $cpa = $this->enablerPurchaseTable->enablerCPAvailed($enablerDetails['id']);
      if($cpa > 0)
        $epWhere['plan_type'] = \Admin\Model\EnablerPlans::Paid_Plan;
      $enablerPlans = $this->enablerPlansTable->getEnablerPlans($epWhere);
      $lastPurchaseDate = $this->enablerPurchaseTable->getEnablerLastPurchaseDate($enablerDetails['id']);
      $lpd = date('Y-m-d', strtotime($lastPurchaseDate));
      $config = $this->getConfig();
      if($enablerDetails['login_type'] == \Admin\Model\Enabler::login_type_social) 
        $imageUrl = "";
      else
        $imageUrl = $this->filesUrl();
      return new ViewModel(['enablerDetails' => $enablerDetails, 'enablerPlans' => $enablerPlans, 'lpd' => $lpd, 'config' => $config['hybridauth'], 'imageUrl' => $imageUrl]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function getPlanPriceAction(){
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $plan_id = $request['pid'];
      $coupon_code = $request['cc'];
      if ($this->authService->hasIdentity()) {
        $loginId = $this->authService->getIdentity();
        $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
        if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
        $plan = $this->enablerPlansTable->getEnablerPlans(['status' => \Admin\Model\EnablerPlans::status_active, 'id' => $plan_id]);
        $planPrice = 0.00;
        $pad = 0.00;
        $discount = 0.00;
        if($enablerDetails['country_phone_code'] == '91'){
          $planPrice = number_format($plan[0]['price_inr'], 2);
        }else{
          $planPrice = number_format($plan[0]['price_usd'], 2);
        }
        if($coupon_code != ""){
          $checkCoupon = $this->couponsTable->getCoupon(['coupon_code' => $coupon_code]);
          if($checkCoupon){
            if($checkCoupon[0]['coupon_status'] == \Admin\Model\Coupons::Coupon_Status_Redeemed)
              return new JsonModel(array('success' => false, "message" => 'Coupon code already redeemed..'));
            $today = date('Y-m-d');
            if($checkCoupon[0]['validity_end_date'] < $today)
              return new JsonModel(array('success' => false, "message" => 'This coupon code expired..'));

            if($checkCoupon[0]['coupon_type'] == \Admin\Model\Coupons::Coupon_Type_Complimentary){
              if(str_contains($plan[0]['plan_name'], $checkCoupon[0]['coupon_type']))
                return new JsonModel(array('success' => true, "message" => 'success', 'pp' => $planPrice, 'pad' => $pad));
              else
                return new JsonModel(array('success' => false, "message" => 'This coupon code cannot be applied for the selected plan..'));
            }else{
              $discount = number_format($this->subscriptionPlanTable->getField(['active' => '1'], 'cd_percentage'), 2);
              $pad = number_format($planPrice * (1 - $discount / 100), 2);
              if(str_contains($plan[0]['plan_name'], 'P'))
                return new JsonModel(array('success' => true, "message" => 'success', 'pp' => $planPrice, 'pad' => $pad));
              else
                return new JsonModel(array('success' => false, "message" => 'This coupon code cannot be applied for the selected plan..'));
            }
          }else{
            return new JsonModel(array('success' => false, "message" => 'This coupon code is not valid..'));
          }
        }else{
          if($enablerDetails['stt_disc'] != "0.00"){
            $discount = number_format($enablerDetails['stt_disc'], 2);
            $pad = number_format($planPrice * (1 - $discount / 100), 2);
            return new JsonModel(array('success' => true, "message" => 'success', 'pp' => $planPrice, 'pad' => $pad));
          }else{
            return new JsonModel(array('success' => true, "message" => 'success', 'pp' => $planPrice, 'pad' => $planPrice));
          }
        }
      } else {
        $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      }
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function enablerPurchaseRequestAction(){
    if ($this->getRequest()->isXmlHttpRequest()) {
      if ($this->authService->hasIdentity()) {
        $loginId = $this->authService->getIdentity();
        $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
        if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
        $request = $this->getRequest()->getPost();
        $cc = $request['cc'];
        $pp = $request['pp'];
        $totalAmount = $request['pad'];
        $pid = $request['pid'];
        $dname = $request['dname'];
        $saveData['enabler_id'] = $enablerDetails['id'];
        $saveData['display_name'] = $dname;
        $saveData['plan_id'] = $pid;
        $saveData['stt_disc'] = $enablerDetails['stt_disc'];
        $saveData['actual_price'] = number_format((float)$pp, 2, '.', '');
        $plan = $this->enablerPlansTable->getEnablerPlans(['status' => \Admin\Model\EnablerPlans::status_active, 'id' => $pid]);
        if($totalAmount == 0 && round($totalAmount) == 0){
          $checkCoupon = $this->couponsTable->getCoupon(['coupon_code' => $cc]);
          if($checkCoupon){
            $today = date('Y-m-d');
            if($checkCoupon[0]['validity_end_date'] < $today)
              return new JsonModel(array('success' => false, "message" => 'This coupon code expired..'));

            if($checkCoupon[0]['coupon_type'] == \Admin\Model\Coupons::Coupon_Type_Complimentary){
              if(str_contains($plan[0]['plan_name'], $checkCoupon[0]['coupon_type'])){
                $saveData['price_after_disc'] = number_format((float)$totalAmount, 2, '.', '');
              }else{
                return new JsonModel(array('success' => false, "message" => 'This coupon code cannot be applied for the selected plan..'));
              }
            }else{
              return new JsonModel(array('success' => false, "message" => 'not a complimentary coupon..'));
            }
          }else{
            return new JsonModel(array('success' => false, "message" => 'not a valid complimentary coupon..'));
          }
        }else{
          $saveData['price_after_disc'] = number_format((float)$totalAmount, 2, '.', '');
        }

        if ($enablerDetails['country_phone_code'] == "91") {
          $saveData['currency'] = "INR";
        } else {
          $saveData['currency'] = "USD";
        }
        if($cc != ""){
          $saveData['coupon_code'] = $cc;
          $execId = $this->couponsTable->getField(['coupon_code' => $cc], 'executive_id');
          $userId = $this->executiveDetailsTable->getField(['id' => $execId], 'user_id');
          $execDetails = $this->userTable->getUserDetails(['id' => $userId]);
          $saveData['executive_name'] = $execDetails['username'];
          $saveData['executive_mobile'] = "(" . $execDetails['country_phone_code'] . ")" . $execDetails['mobile_number'];
        }
        $saveData['purchase_date'] = date('Y-m-d H:i:s');
        $saveData['invoice'] = 'IN'.$saveData['enabler_id'].$saveData['plan_id'].date('YmdHis');
        $purReqResp = $this->enablerPurchaseRequestTable->addEnablerPurchaseRequest($saveData);
        if (!$purReqResp['success'])
          return new JsonModel(array('success' => false, "message" => 'unable to process your order now.. please try later..'));
        $purchaseRequestId = $purReqResp['id'];
        $prId =base64_encode('prId=' . $purchaseRequestId);
        return new JsonModel(array('success' => true, "message" => 'please check your invoice and proceed with payment...', 'pid' => $prId));
      }
    }
  }

  public function enablerInvoiceAction(){
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $paramId = $this->params()->fromRoute('id', '');
      if (!$paramId) {
          return $this->redirect()->toUrl($this->getBaseUrl());
      }
      $prIdString = rtrim($paramId, "=");
      $prIdString = base64_decode($prIdString);
      $prIdString = explode("=", $prIdString);
      $iId = array_key_exists(1, $prIdString) ? $prIdString[1] : 0;
      if ($iId != 0) {
        $prDetails = $this->enablerPurchaseRequestTable->getEnablerPurchaseRequest(['id' => $iId]);
        $prRes = $this->enablerPurchaseTable->getField(['invoice' => $prDetails['invoice']], 'id');
        $show = 1;
        if($prRes)
          $show = 0;
      }else{
        return new JsonModel(array('success' => false, "message" => 'invoice not found..'));
      } 
      $config = $this->getConfig();
      if($enablerDetails['login_type'] == \Admin\Model\Enabler::login_type_social) 
        $imageUrl = "";
      else
        $imageUrl = $this->filesUrl();
      return new ViewModel(['enablerDetails' => $enablerDetails, 'prDetails' => $prDetails, 'show' => $show, 'prId'=>$paramId, 'config' => $config['hybridauth'], 'imageUrl' => $imageUrl]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function enablerReceiptAction(){
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $paramId = $this->params()->fromRoute('id', '');
      if (!$paramId) {
          return $this->redirect()->toUrl($this->getBaseUrl());
      }
      $prIdString = rtrim($paramId, "=");
      $prIdString = base64_decode($prIdString);
      $prIdString = explode("=", $prIdString);
      $rId = array_key_exists(1, $prIdString) ? $prIdString[1] : 0;
      if ($rId != 0) {
        $prDetails = $this->enablerPurchaseRequestTable->getEnablerPurchaseRequest(['id' => $rId]);
        $purchase = $this->enablerPurchaseTable->getEnablerPurchase(['invoice' => $prDetails['invoice']]);
        $planName = $this->enablerPlansTable->getField(['id' => $purchase['plan_id']], 'plan_name');
      } else {
        return new JsonModel(array('success' => false, "message" => 'receipt not found..'));
      } 
      $config = $this->getConfig();
      return new ViewModel(['enablerDetails' => $enablerDetails, 'purchase' => $purchase, 'prId'=>$paramId, 'planName'=>$planName, 'config' => $config['hybridauth'], 'imageUrl' => $this->filesUrl()]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function enablerPayAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $request = $this->getRequest()->getPost();
      $prid = $request['prid'];
      if (!$prid) {
        return new JsonModel(array('success' => false, "message" => 'invalid purchase request..'));
      }
      $prIdString = rtrim($prid, "=");
      $prIdString = base64_decode($prIdString);
      $prIdString = explode("=", $prIdString);
      $iId = array_key_exists(1, $prIdString) ? $prIdString[1] : 0;
      if ($iId != 0) {
        $prDetails = $this->enablerPurchaseRequestTable->getEnablerPurchaseRequest(['id' => $iId]);
      }else{
        return new JsonModel(array('success' => false, "message" => 'invoice not found..'));
      } 
      
      $totalAmount = $prDetails['price_after_disc'];
      $saveData['payment_status'] = \Admin\Model\EnablerPurchase::payment_in_process;
      $saveData['enabler_id'] = $enablerDetails['id'];
      $saveData['display_name'] = $prDetails['display_name'];
      $saveData['plan_id'] = $prDetails['plan_id'];
      $saveData['invoice'] = $prDetails['invoice'];
      $saveData['actual_price'] = number_format((float)$prDetails['actual_price'], 2, '.', '');
      $plan = $this->enablerPlansTable->getEnablerPlans(['status' => \Admin\Model\EnablerPlans::status_active, 'id' => $prDetails['plan_id']]);
      $lic_bal = '0';
      if($plan[0]['plan_type'] == \Admin\Model\EnablerPlans::Complimentary_Plan)
        $lic_bal = substr($plan[0]['plan_name'], strpos($plan[0]['plan_name'], 'C')+1);
      else
        $lic_bal = substr($plan[0]['plan_name'], strpos($plan[0]['plan_name'], 'P')+1);

      $saveData['lic_bal'] = $lic_bal;
      if($totalAmount == 0 && round($totalAmount) == 0){
        $checkCoupon = $this->couponsTable->getCoupon(['coupon_code' => $prDetails['coupon_code']]);
        if($checkCoupon){
          $today = date('Y-m-d');
          if($checkCoupon[0]['validity_end_date'] < $today)
            return new JsonModel(array('success' => false, "message" => 'This coupon code expired..'));

          if($checkCoupon[0]['coupon_type'] == \Admin\Model\Coupons::Coupon_Type_Complimentary){
            if(str_contains($plan[0]['plan_name'], $checkCoupon[0]['coupon_type'])){
              $saveData['price_after_disc'] = number_format((float)$totalAmount, 2, '.', '');
            }else{
              return new JsonModel(array('success' => false, "message" => 'This coupon code cannot be applied for the selected plan..'));
            }
          }else{
            return new JsonModel(array('success' => false, "message" => 'not a complimentary coupon..'));
          }
        }else{
          return new JsonModel(array('success' => false, "message" => 'not a valid complimentary coupon..'));
        }
      }else{
        $saveData['price_after_disc'] = number_format((float)$totalAmount, 2, '.', '');
      }
      
      if ($enablerDetails['country_phone_code'] == "91") {
        $saveData['currency'] = "INR";
      } else {
        $saveData['currency'] = "USD";
      }
      if($prDetails['coupon_code'] != ""){
        $saveData['coupon_code'] = $prDetails['coupon_code'];
        $execId = $this->couponsTable->getField(['coupon_code' => $prDetails['coupon_code']], 'executive_id');
        $userId = $this->executiveDetailsTable->getField(['id' => $execId], 'user_id');
        $execDetails = $this->userTable->getUserDetails(['id' => $userId]);
        $execBDetails = $this->executiveDetailsTable->getExecutiveDetails(['id' => $execId]);
        $saveData['executive_name'] = $execDetails['username'];
        $saveData['executive_mobile'] = "(" . $execDetails['country_phone_code'] . ")" . $execDetails['mobile_number'];
      }
      $saveData['purchase_date'] = date('Y-m-d H:i:s');
      $purchaseResp = $this->enablerPurchaseTable->addEnablerPurchase($saveData);
      if ($purchaseResp['success'])
        $purchaseId = $purchaseResp['id'];
      if ($purchaseId) {
        if($saveData['price_after_disc'] == "0.00"){
          $setResp = $this->enablerPurchaseTable->setEnablerPurchase(['payment_status' => \Admin\Model\EnablerPurchase::payment_success, 'receipt' => 'TP_' . $purchaseId.$saveData['enabler_id'].$saveData['plan_id'].date('YmdHis')], ['id' => $purchaseId]);
          $updateCoupon = [];
          if($setResp){
            $purDetails = $this->enablerPurchaseTable->getEnablerPurchase(['id' => $purchaseId]);
            if(!is_null($purDetails['coupon_code']) && !empty($purDetails['coupon_code'])){
              $updateCoupon['redeemer_login'] = $enablerDetails['user_login_id'];
              $updateCoupon['redeemer_name'] = $enablerDetails['company_name'] == "" ? $enablerDetails['username'] : $enablerDetails['company_name'];
              $updateCoupon['redeemer_mobile'] = '(' . $enablerDetails['country_phone_code'] . ')' . $enablerDetails['mobile_number'];
              $updateCoupon['redeemed_on'] = $purDetails['purchase_date'];
              $updateCoupon['redeemer_paid'] = $purDetails['price_after_disc'];
              $updateCoupon['redeemer_actual_amount'] = $purDetails['actual_price'];
              $updateCoupon['commission_receivable'] = number_format((float)$purDetails['price_after_disc'], 2, '.', '') * number_format((float)$execBDetails['commission_percentage'], 2, '.', '') / 100;
              $updateCoupon['coupon_status'] = \Admin\Model\Coupons::Coupon_Status_Redeemed;
              $setCoupon = $this->couponsTable->setCoupons($updateCoupon, ['coupon_code' => $purDetails['coupon_code']]);
              if($setCoupon){
                return new JsonModel(array('success' => true, 'message' => 'plan purchased successfully..', 'rid'=>$prid,));
              }else{
                return new JsonModel(array('success' => false, 'message' => 'unable to process your request now.. unknown error..'));
              }
            }else{return new JsonModel(array('success' => true, 'message' => 'plan purchased successfully..', 'rid'=>$prid,));
            }
          }else{
            return new JsonModel(array('success' => false, 'message' => 'unable to process your request now..'));
          }
        }

        $api = new Razorpay();
        if ($enablerDetails['country_phone_code'] == "91") {
          $orderData = [
            'amount'          => intval($totalAmount * 100),
            'currency'        => 'INR',
            'receipt' => 'TP_' . $purchaseId.$saveData['enabler_id'].$saveData['plan_id'].date('YmdHis'),
            'payment_capture' => 1
          ];
        } else {
          $orderData = [
            'amount'          => intval($totalAmount * 100),
            'currency'        => 'USD',
            'receipt' => 'TP_' . $purchaseId.$saveData['enabler_id'].$saveData['plan_id'].date('YmdHis'),
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
        if ($response) {
          $setResp = $this->enablerPurchaseTable->setEnablerPurchase(['receipt' => $razorpayOrder['receipt'], 'razorpay_order_id' => $razorpayOrder['id']], ['id' => $purchaseId]);
          if ($setResp) {
            return new JsonModel(array('success' => true, 'message' => 'order created', 'order' => $razorpayOrder));
          } else {
            return new JsonModel(array('success' => false, 'message' => 'unable to process your payment now.. please try after sometime..'));
          }
        } else {
          return new JsonModel(array('success' => false, 'message' => 'unable to process your payment now.. please try after sometime..'));
        }
      }
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }
  public function enablerCheckoutAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $request = $this->getRequest()->getPost();
      $prid = $request['prid'];
      if (!$prid) {
        return new JsonModel(array('success' => false, "message" => 'invalid purchase request..'));
      }
      $prIdString = rtrim($prid, "=");
      $prIdString = base64_decode($prIdString);
      $prIdString = explode("=", $prIdString);
      $iId = array_key_exists(1, $prIdString) ? $prIdString[1] : 0;
      if ($iId != 0) {
        $prDetails = $this->enablerPurchaseRequestTable->getEnablerPurchaseRequest(['id' => $iId]);
      }else{
        return new JsonModel(array('success' => false, "message" => 'invoice not found..'));
      } 
      $cc = $prDetails['coupon_code'];
      $pp = $prDetails['actual_price'];
      $pad = $prDetails['price_after_disc'];
      $razorpay_payment_id = $request['razorpay_payment_id'];
      $razorpay_order_id = $request['razorpay_order_id'];
      $razorpay_signature = $request['razorpay_signature'];

      $attributes = array(
        'razorpay_order_id' => $razorpay_order_id,
        'razorpay_payment_id' => $razorpay_payment_id,
        'razorpay_signature' => $razorpay_signature
      );
      $api = new Razorpay();
      try {
        $api->checkPaymentSignature($attributes);
        $orderResp = $api->checkOrderStatus($razorpay_order_id);
        if ($orderResp['status'] !== "paid"){
          $setResp = $this->enablerPurchaseTable->setEnablerPurchase(['razorpay_payment_id' => $razorpay_payment_id, 'razorpay_signature' => $razorpay_signature, 'payment_status' => \Admin\Model\EnablerPurchase::payment_failure], ['razorpay_order_id' => $razorpay_order_id]);
          return new JsonModel(array('success' => false, 'message' => 'payment not successful'));
        }
        $setResp = $this->enablerPurchaseTable->setEnablerPurchase(['razorpay_payment_id' => $razorpay_payment_id, 'razorpay_signature' => $razorpay_signature, 'payment_status' => \Admin\Model\EnablerPurchase::payment_success], ['razorpay_order_id' => $razorpay_order_id]);
        $updateCoupon = [];
        if($setResp){
          if(!is_null($cc) && !empty($cc)){
            $execId = $this->couponsTable->getField(['coupon_code' => $prDetails['coupon_code']], 'executive_id');
            $execBDetails = $this->executiveDetailsTable->getExecutiveDetails(['id' => $execId]);
            $updateCoupon['redeemer_login'] = $enablerDetails['user_login_id'];
            $updateCoupon['redeemer_name'] = $enablerDetails['company_name'] == "" ? $enablerDetails['username'] : $enablerDetails['company_name'];
            $updateCoupon['redeemer_mobile'] = '(' . $enablerDetails['country_phone_code'] . ')' . $enablerDetails['mobile_number'];
            $updateCoupon['redeemed_on'] = date('Y-m-d H:i:s');
            $updateCoupon['redeemer_paid'] = $pad;
            $updateCoupon['redeemer_actual_amount'] = $pp;
            $updateCoupon['commission_receivable'] = number_format((float)$pad, 2, '.', '') * number_format((float)$execBDetails['commission_percentage'], 2, '.', '') / 100;
            $updateCoupon['coupon_status'] = \Admin\Model\Coupons::Coupon_Status_Redeemed;
            $setCoupon = $this->couponsTable->setCoupons($updateCoupon, ['coupon_code' => $cc]);
            if($setCoupon){
              // add executive txn
              $exTxnData['coupon_id'] = $this->couponsTable->getField(['coupon_code' => $cc], 'id');
              $exTxnData['executive_id'] = $this->couponsTable->getField(['coupon_code' => $cc], 'executive_id');
              $exTxnData['user_id'] = $this->executiveDetailsTable->getField(['id' => $exTxnData['executive_id']], 'user_id');
              $exTxnData['transaction_type'] = \Admin\Model\ExecutiveTransaction::transaction_due;
              $exTxnData['transaction_date'] = date('Y-m-d H:i:s');
              $execTxn = $this->executiveTransactionTable->getExecutiveTransaction(['executive_id' => $exTxnData['executive_id']]);
              $exTxnData['total_earnings'] = $execTxn['total_earnings'] + $updateCoupon['commission_receivable'];
              $exTxnData['balance_outstanding'] = $execTxn['balance_outstanding'] + $updateCoupon['commission_receivable'];
              $txnRes = $this->executiveTransactionTable->addExecutivePurchase($exTxnData);
              if($txnRes)
                return new JsonModel(array('success' => true, 'message' => 'plan purchased successfully..', 'pid'=>$prid,));
              else
                return new JsonModel(array('success' => false, 'message' => 'unable to process your request now.. unknown error..'));
            }else{
              return new JsonModel(array('success' => false, 'message' => 'unable to process your request now.. unknown error..'));
            }
          }else{
            return new JsonModel(array('success' => true, 'message' => 'plan purchased successfully..', 'pid'=>$prid,));
          }
        }else{
          return new JsonModel(array('success' => false, 'message' => 'unable to process your request now..'));
        }
      } catch (\Exception $e) {
        return new JsonModel(array('success' => false, "message" => 'Razorpay Error : ' . $e->getMessage()));
      }
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function enablerTrackPurchasesAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $purchases = $this->enablerPurchaseTable->getEnablerPurchasesList(['enabler_id' => $enablerDetails['id'], 'limit' => 10, 'offset' => 0]);
      $totalCount = $this->enablerPurchaseTable->getEnablerPurchasesList(['enabler_id' => $enablerDetails['id']], 1);
      $config = $this->getConfig();
      if($enablerDetails['login_type'] == \Admin\Model\Enabler::login_type_social) 
        $imageUrl = "";
      else
        $imageUrl = $this->filesUrl();
      return new ViewModel(['enablerDetails' => $enablerDetails, 'purchases' => $purchases, 'totalCount' => $totalCount, 'config' => $config['hybridauth'], 'imageUrl' => $imageUrl]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function loadPurchasesListAction()
  {
    if ($this->getRequest()->isXmlHttpRequest()) {
      $request = $this->getRequest()->getPost();
      $searchData = array('limit' => 10, 'offset' => 0);
      $type = $request['type'];
      $offset = 0;
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getenablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      if (isset($request['page_number'])) {
        $pageNumber = $request['page_number'];
        $offset = ($pageNumber * 10 - 10);
        $limit = 10;
        $searchData['offset'] = $offset;
        $searchData['limit'] = $limit;
      }
      $searchData['enabler_id'] = $enablerDetails['id'];
      $totalCount = 0;

      if ($type && $type == 'search') {
        $totalCount = $this->enablerPurchaseTable->getEnablerPurchasesList($searchData, 1);
      }
      $purchases = $this->enablerPurchaseTable->getEnablerPurchasesList($searchData);
      $view = new ViewModel(array('purchases' => $purchases, 'totalCount' => $totalCount));
      $view->setTerminal(true);
      return $view;
    }
  }
  public function enablerTrackPlansAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $enblerPL = $this->enablerPurchaseTable->getEnablerPurchasedPlansList($enablerDetails['id']);
      $sales = $this->enablerSalesTable->getEnablerSalesList(['enabler_id' => $enablerDetails['id'], 'limit' => 10, 'offset' => 0]);
      $totalCount = $this->enablerSalesTable->getEnablerSalesList(['enabler_id' => $enablerDetails['id']], 1);
      $config = $this->getConfig();
      if($enablerDetails['login_type'] == \Admin\Model\Enabler::login_type_social) 
        $imageUrl = "";
      else
        $imageUrl = $this->filesUrl();
      return new ViewModel(['enablerDetails' => $enablerDetails, 'sales' => $sales, 'totalCount' => $totalCount, 'epl' => $enblerPL, 'config' => $config['hybridauth'], 'imageUrl' => $imageUrl]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function enablerSellAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $enablerDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($enablerDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $request = $this->getRequest()->getPost();
      $saveData['enabler_id'] = $enablerDetails['id'];
      $saveData['purchase_id'] = $request['selPlan'];
      $epd = $this->enablerPurchaseTable->getEnablerPurchase(['id'=>$request['selPlan']]);
      $saveData['plan_id'] = $epd['plan_id'];
      $saveData['sale_date'] = date('Y-m-d');
      $saveData['tourist_name'] = $request['tname'];
      $saveData['tourist_mobile'] = "(" . $request['cc'] . ")" . $request['mobile'];
      $saveData['tourist_email'] = $request['email'];
      $saveData['tour_start_date'] = date('Y-m-d', strtotime($request['datepicker']));
      $saveData['twistt_start_date'] = date('Y-m-d', strtotime($request['datepicker'] . ' -3 days'));
      $plan_name = $this->enablerPlansTable->getField(['id' => $epd['plan_id']], 'plan_name');
      if(strstr($plan_name, \Admin\Model\EnablerPlans::Paid_Plan))
        $twisttPd = substr($plan_name, 0, strpos($plan_name, \Admin\Model\EnablerPlans::Paid_Plan));
      else if(strstr($plan_name, \Admin\Model\EnablerPlans::Complimentary_Plan))
        $twisttPd = substr($plan_name, 0, strpos($plan_name, \Admin\Model\EnablerPlans::Complimentary_Plan));
      $saveData['twistt_end_date'] = date('Y-m-d', strtotime($saveData['twistt_start_date'] . " $twisttPd days"));
      $lic_bal = (int)$epd['lic_bal'] - 1;
      $saveData['lic_bal'] = $lic_bal;
      $updatePDT_LB = $this->enablerPurchaseTable->setEnablerPurchase(['lic_bal' => $lic_bal], ['id' => $epd['id']]);
      if(!$updatePDT_LB)
        return new JsonModel(array('success' => false, 'message' => 'unable to process your request..'));
      $addESData = $this->enablerSalesTable->addEnablerSale($saveData);
      if(!$addESData)
        return new JsonModel(array('success' => false, 'message' => 'unable to process your request..'));
      return new JsonModel(array('success' => true, 'message' => 'successfull'));
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }
  public function enablerTermsAction()
  {
    if ($this->authService->hasIdentity()) {
      $loginId = $this->authService->getIdentity();
      $userDetails = $this->enablerTable->getEnablerDetails(['user_login_id' => $loginId['user_login_id']]);
      if(is_null($userDetails))
          $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
      $config = $this->getConfig();
      return new ViewModel(['userDetails' => $userDetails, 'config' => $config['hybridauth'], 'imageUrl' => $this->filesUrl()]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function enablerLogoutAction()
  {
    return new ViewModel();
  }
  public function termsPrivacyAction()
  {
    $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TERMS_PAGE);
    return new ViewModel();
  }
}
