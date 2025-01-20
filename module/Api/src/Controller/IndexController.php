<?php
namespace Api\Controller;
use Application\Controller\BaseController;
use Application\Handler\Aes;
use Laminas\View\Model\JsonModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class IndexController extends BaseController
{
    public function indexAction(){
        return  new JsonModel(array('success'=>true,'message'=>'twistt api accessed successfully...'));
    }

    public function testAction(){
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $user = array('userId'=> "12345",
            'isLoggedIn'=> true,
            'isSubscribed'=> false,
            'subscriptionType'=> "U",
            'subscriptionExpiry'=> "2024-12-31",
            'primaryLanguage'=> "TEL",
            'secondaryLanguage'=> "ENG");
            return new JsonModel(array('success'=>true,'data'=>$user));
        }else{
            return $tvResponse;
        }
    }

    public function loginAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $user_login_id = '';
        if(isset($request['loginType'])){
            if ($request['loginType'] == 'm'){
                if (!isset($request['identifier']))
                    return  new JsonModel(array('success'=>false,'message'=>'User login id missing..'));
                $user_login_id = $request['identifier'];
            }elseif($request['loginType'] == 'e'){
                $user_login_id = $request['email'];
            }elseif($request['loginType'] == 's'){
                $user_login_id = $request['identifier'];
                $user = $this->userTable->getUserDetails(['user_login_id' => $user_login_id, 'display' => 1]);
                if(!count($user)){
                    $saveUser = [];
                    $saveUser['user_login_id'] = $user_login_id;
                    $saveUser['login_type'] = $request['loginType'];
                    $saveUser['oauth_provider'] = $request['provider'];
                    $saveUser['username'] = $request['username'];
                    $saveUser['country'] = $request['country'];
                    $saveUser['user_type_id'] = \Admin\Model\User::New_User;
                    $insertResult = $this->userTable->addUser($saveUser);
                    if($insertResult['success']){
                        $user = $this->userTable->getUserDetails(['id' => $insertResult['id'], 'display' => 1]);
                    }else{
                        return new JsonModel(array('success'=>false,'message'=>$insertResult['message']));
                    }
                }
                $user['isLoggedIn'] = true;
                $user['subscriptionType'] = "U";
                $user['access_token'] = $this->generateAccessToken(['userId' => $user['id'], 'loginId' => $user_login_id]);
                $user['isSubscribed'] = $this->questtSubscriptionTable->isValidQuesttUser($user['id']);
                if($user['isSubscribed']){
                    $user['subscriptionType'] = "Q";
                    $qed = $this->questtSubscriptionTable->getField(['user_id' => $user['id']], 'end_date');
                    $user['subscriptionExpiry'] = date('Y-m-d', strtotime($qed));
                }
                $user = array_map(function ($value) {
                    return $value === null ? '' : $value;
                }, $user);
                return new JsonModel(array('success'=>true,'data'=>$user));
            }
        }
        if (!isset($request['password']))
            return  new JsonModel(array('success'=>false,'message'=>'password missing..'));
        $providedPassword = $request['password'];
        $user_login_id = str_replace('+', '', $user_login_id);
        if ($user_login_id) {
            $isProvidedPasswordValid = $this->userTable->checkPasswordWithUserId($user_login_id, $providedPassword);
            if ($isProvidedPasswordValid) {
                $user = $this->userTable->getUserDetails(['user_login_id' => $user_login_id, 'display' => 1]);
                $user['isLoggedIn'] = true;
                $user['subscriptionType'] = "U";
                $user['access_token'] = $this->generateAccessToken(['userId' => $user['id'], 'loginId' => $user_login_id]);
                $user['isSubscribed'] = $this->questtSubscriptionTable->isValidQuesttUser($user['id']);
                if($user['isSubscribed']){
                    $user['subscriptionType'] = "Q";
                    $qed = $this->questtSubscriptionTable->getField(['user_id' => $user['id']], 'end_date');
                    $user['subscriptionExpiry'] = date('Y-m-d', strtotime($qed));
                }
                $user = array_map(function ($value) {
                    return $value === null ? '' : $value;
                }, $user);
                return new JsonModel(array('success'=>true,'data'=>$user));
            } else {
                return  new JsonModel(array('success'=>false,'message'=>'Invalid credentials..'));
            }
        } else {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid credentials..'));
        }
    }

    public function signupAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $user_login_id = '';
        if(isset($request['loginType'])){
            if ($request['loginType'] == 'm'){
                if (!isset($request['identifier']))
                    return  new JsonModel(array('success'=>false,'message'=>'User login id missing..'));
                if(!isset($request['countryCode'])){
                    if($request['countryCode'] != 'IN'){
                        if(!isset($request['foreignEmail'])){
                            return  new JsonModel(array('success'=>false,'message'=>'email id missing..'));
                        }
                    }
                }
                $user_login_id = $request['identifier'];
            }elseif($request['loginType'] == 'e'){
                if(!isset($request['email'])){
                    return  new JsonModel(array('success'=>false,'message'=>'email id missing..'));
                }
                $user_login_id = $request['email'];
            }elseif($request['loginType'] == 's'){
                $user_login_id = $request['identifier'];
            }
        }
        if (!isset($request['password']))
            return  new JsonModel(array('success'=>false,'message'=>'password missing..'));

        $user_login_id = str_replace('+', '', $user_login_id);
        $uId = $this->userTable->getField(['user_login_id' => $user_login_id, 'display' => 1], 'id');
        $saveUser = [];
        $saveUser['user_login_id'] = $user_login_id;
        $saveUser['login_type'] = $request['loginType'];
        $saveUser['oauth_provider'] = $request['provider'];
        $saveUser['username'] = $request['username'];
        $saveUser['country'] = $request['country'];
        $aes = new Aes();
        $encodeContent = $aes->encrypt($request['password']);
        $saveUser['password'] = $encodeContent['password'];
        $saveUser['hash'] = $encodeContent['hash'];
        $saveUser['user_type_id'] = \Admin\Model\User::New_User;

        if($uId){ // user is already registered
            return  new JsonModel(array('success'=>false,'message'=>'existing user.. please login to enjoy the tales..'));
        }else { // create new user
            // $insertResult = $this->userTable->addUser($saveUser);
            $insertResult['success'] = true;
            if($insertResult['success']){ // new user data successfully inserted
                $id = 3; // $insertResult['id'];
                $otp = $this->generateOtp();
                if ($request['loginType'] == 'm'){
                    if($request['countryCode'] == 'IN'){
                        $otpDetails = ['user_id'=> $id, 'otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::User_Registration, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::App_Request, 'otp_sent_to' => $request['identifier']];
                        $otpRes = $this->otpTable->addOtpDetails($otpDetails);
                        if ($otpRes) {
                            $resp = $this->sendOtpSms($request['identifier'], $otp, 'TEn_Password_Reset_Otp');
                            if ($resp) {
                                return new JsonModel(array('success' => true, "message" => 'otp sent successfully to your mobile.. please enter the otp.. ' . $otp, "data" => $otpDetails));
                            } else {
                            return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again later..'));
                            }
                        }else {
                            return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again later..'));
                        }
                    }else{
                        $otpDetails = ['user_id'=> $id, 'otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::User_Registration, 'verification_mode' => \Admin\Model\Otp::Email_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::App_Request, 'otp_sent_to' => $request['foreignEmail']];
                        $otpRes = $this->otpTable->addOtpDetails($otpDetails);
                        if ($otpRes) {
                            $resp = $this->sendmail($request['foreignEmail'], 'otp to signup for TWISTT app', 'App_Registration_Otp', $otp);
                            if ($resp) {
                                return new JsonModel(array('success' => true, "message" => 'otp sent successfully to your email id.. please enter the otp.. ' . $otp, "data" => $otpDetails));
                            } else {
                                return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again later..'));
                            }
                        }else {
                            return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again later..'));
                        }
                    }
                }elseif($request['loginType'] == 'e'){
                    $otpDetails = ['user_id'=> $id, 'otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::User_Registration, 'verification_mode' => \Admin\Model\Otp::Email_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::App_Request, 'otp_sent_to' => $request['email']];
                    $otpRes = $this->otpTable->addOtpDetails($otpDetails);
                    if ($otpRes) {
                        $resp = $this->sendmail($request['email'], 'otp to signup for TWISTT app', 'App_Registration_Otp', $otp);
                        if ($resp) {
                            return new JsonModel(array('success' => true, "message" => 'otp sent successfully to your email id.. please enter the otp.. ' . $otp, "data" => $otpDetails));
                        } else {
                            return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again later..'));
                        }
                    }else {
                        return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again later..'));
                    }
                }  
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unknown error.. please try later..'));
            }
        }
    }

    public function verifyOtpAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if (!isset($request['user_id']) || !isset($request['otp']) || !isset($request['otp_type_id']) || !isset($request['verification_mode']) || !isset($request['sent_status_id']) || !isset($request['otp_requested_by']) || !isset($request['otp_sent_to']))
            return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
        $otpData = $request->toArray();
        $verify = $this->otpTable->verifyOtp($otpData);
        if($verify){
            $updateResponse = $this->otpTable->setOtpDetails(array('sent_status_id' => \Admin\Model\Otp::Is_verifed), $otpData);
            if ($updateResponse) {
                $userData = $this->userTable->getUserDetails(['id' => $request['user_id']]);
                $userData['isLoggedIn'] = true;
                $userData['subscriptionType'] = "U";
                $userData['isSubscribed'] = false;
                $userData['access_token'] = $this->generateAccessToken(['userId' => $request['user_id'], 'loginId' => $userData['user_login_id']]);
                $userData = array_map(function ($value) {
                    return $value === null ? '' : $value;
                }, $userData);
                return new JsonModel(array('success'=>true,'message'=>'otp verified successfully..', 'data'=>$userData));
            } else {
                return new JsonModel(array("success" => false, "message" => "unable to verify otp.. please try again"));
            }
        }else{
            return new JsonModel(array('success'=>false,'message'=>'otp not verified..'));
        }
    }

    public function sendOtpAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if (!isset($request['user_login_id']))
            return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
        $user_login_id = $request['user_login_id'];
        $user_login_id = str_replace('+', '', $user_login_id);
        $userData = $this->userTable->getUserDetails(['user_login_id' => $user_login_id, 'display' => 1]);
        if(!count($userData))
            return new JsonModel(array('success'=>false,'message'=>'no such user..'));
        $otp = $this->generateOtp();
        if($userData['login_type'] == 'm'){
            if($userData['country_phone_code'] == '91'){
                $otpDetails = ['user_id'=> $userData['id'], 'otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Forgot_Password, 'verification_mode' => \Admin\Model\Otp::Mobile_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::App_Request, 'otp_sent_to' => $userData['user_login_id']];
                $otpRes = $this->otpTable->addOtpDetails($otpDetails);
                if ($otpRes) {
                    $resp = $this->sendOtpSms($userData['user_login_id'], $otp, 'TEn_Password_Reset_Otp');
                    if ($resp) {
                        return new JsonModel(array('success' => true, "message" => 'otp sent successfully to your mobile.. please enter the otp.. ' . $otp, "data" => $otpDetails));
                    } else {
                    return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again later..'));
                    }
                }else {
                    return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again later..'));
                }
            }else{
                $otpDetails = ['user_id'=> $userData['id'], 'otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Forgot_Password, 'verification_mode' => \Admin\Model\Otp::Email_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::App_Request, 'otp_sent_to' => $userData['foreignEmail']];
                $otpRes = $this->otpTable->addOtpDetails($otpDetails);
                if ($otpRes) {
                    $resp = $this->sendmail($userData['foreignEmail'], 'otp to signup for TWISTT app', 'App_Forgot_Password_Otp', $otp);
                    if ($resp) {
                        return new JsonModel(array('success' => true, "message" => 'otp sent successfully to your email id.. please enter the otp.. ' . $otp, "data" => $otpDetails));
                    } else {
                        return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again later..'));
                    }
                }else {
                    return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again later..'));
                }
            }
        }elseif($userData['login_type'] == 'e'){
            $otpDetails = ['user_id'=> $userData['id'], 'otp' => $otp, 'otp_type_id' => \Admin\Model\Otp::Forgot_Password, 'verification_mode' => \Admin\Model\Otp::Email_Verification, 'sent_status_id' => \Admin\Model\Otp::Not_verifed, 'otp_requested_by' => \Admin\Model\Otp::App_Request, 'otp_sent_to' => $userData['user_login_id']];
                $otpRes = $this->otpTable->addOtpDetails($otpDetails);
                if ($otpRes) {
                    $resp = $this->sendmail($userData['user_login_id'], 'otp to signup for TWISTT app', 'App_Forgot_Password_Otp', $otp);
                    if ($resp) {
                        return new JsonModel(array('success' => true, "message" => 'otp sent successfully to your email id.. please enter the otp.. ' . $otp, "data" => $otpDetails));
                    } else {
                        return new JsonModel(array('success' => false, "message" => 'unable to send otp.. please try again later..'));
                    }
                }else {
                    return new JsonModel(array('success' => false, "message" => 'unknown error.. please try again later..'));
                }
        }else{
            return new JsonModel(array('success'=>false,'message'=>'unknown user..'));
        }
    }

    public function resetPasswordAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if (!isset($request['user_login_id']) || !isset($request['password']) )
            return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
        $aes = new Aes();
        $encodeContent = $aes->encrypt($request['password']);
        $saveUser['password'] = $encodeContent['password'];
        $saveUser['hash'] = $encodeContent['hash'];
        $updateResp = $this->userTable->updateUser($saveUser, ['user_login_id' => $request['user_login_id']]);
        if ($updateResp)
          return new JsonModel(array('success' => true, "message" => 'password reset successfull..'));
        else
          return new JsonModel(array('success' => false, "message" => 'unable to reset password.. try after sometime..'));
    }

    public function getLanguagesAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            $primaryLanguages = $this->languageTable->getLanguages(\Admin\Model\Language::primary_language);
            $secondaryLanguages = $this->languageTable->getLanguages(\Admin\Model\Language::secondary_language);
            return new JsonModel(array('success' => true, "data" => ['primaryLanguages' => $primaryLanguages, 'secondaryLanguages' => $secondaryLanguages]));
        }else{
            return $tvResponse;
        }
    }

    public function setLanguagesAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){    
            $request = $this->getRequest()->getPost();
            if (!isset($request['user_login_id']) || !isset($request['primary_language_id'])  || !isset($request['secondary_language_id']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $data['primary_language'] = $request['primary_language_id'];
            $data['secondary_language'] = $request['secondary_language_id'];
            $where['user_login_id'] = $request['user_login_id'];
            $updateResp = $this->userTable->updateUser($data, $where);
            if ($updateResp)
            return new JsonModel(array('success' => true, "message" => 'languages updated successfull..'));
            else
            return new JsonModel(array('success' => false, "message" => 'unable to update.. try after sometime..'));
        }else{
            return $tvResponse;
        }
    }

    public function getStatesAction()  {
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['limit']) || !isset($request['offset']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $stateList = $this->statesTable->getStates4App(['limit' => $request['limit'], 'offset' => $request['offset']]);
            $totalCount = $this->statesTable->getStates4App(['limit' => 0, 'offset' => 0], 1);
            return new JsonModel(['places' => $stateList]);
        }else{
            return $tvResponse;
        }
    }

    public function getWorldCitiesAction()  {
        $request = $this->getRequest()->getPost();
        if (!isset($request['limit']) || !isset($request['offset']) || !isset($request['country_id']))
            return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
        $stateList = $this->citiesTable->getWorldCities4App(['limit' => $request['limit'], 'offset' => $request['offset']], 0, $request['country_id']);
        $totalCount = $this->citiesTable->getWorldCities4App(['limit' => 0, 'offset' => 0], 1, $request['country_id']);
        return new JsonModel(['places' => $stateList]);
    }
    public function getIndiaCitiesAction()  {
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['limit']) || !isset($request['offset']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $stateid = "";
            if(!isset($request['state_id'])){
                $stateList = $this->statesTable->getStates4App(['limit' => 1, 'offset' => 0]);
                $stateid = $stateList[0]['id'];
            }else{
                $stateid = $request['state_id'];
            }
                
            $stateList = $this->citiesTable->getIndiaCities4App(['limit' => $request['limit'], 'offset' => $request['offset']], 0, $stateid);
            $totalCount = $this->citiesTable->getIndiaCities4App(['limit' => 0, 'offset' => 0], 1, $request['state_id']);
            return new JsonModel(['places' => $stateList]);
        }else{
            return $tvResponse;
        }
    }

    public function getPlacesAction()  {
        $request = $this->getRequest()->getPost();
        if (!isset($request['limit']) || !isset($request['offset']))
            return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
        $stateList = $this->statesTable->getStates4App(['limit' => $request['limit'], 'offset' => $request['offset']]);
        $totalCount = $this->statesTable->getStates4App(['limit' => 0, 'offset' => 0], 1);
        return new JsonModel(['places' => $stateList]);
    }
}