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
                $user['primary_language_name'] = $this->languageTable->getField(['id'=>$user['primary_language']], 'language_name');
                $user['secondary_language_name'] = $this->languageTable->getField(['id'=>$user['secondary_language']], 'language_name');
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
                $user['primary_language_name'] = $this->languageTable->getField(['id'=>$user['primary_language']], 'language_name');
                $user['secondary_language_name'] = $this->languageTable->getField(['id'=>$user['secondary_language']], 'language_name');
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
            return new JsonModel(['places' => $stateList]);
        }else{
            return $tvResponse;
        }
    }

    public function getCountriesAction()  {
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['limit']) || !isset($request['offset']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $countryList = $this->countriesTable->getCountries4App(['limit' => $request['limit'], 'offset' => $request['offset']]);
            return new JsonModel(['places' => $countryList]);
        }else{
            return $tvResponse;
        }
    }

    public function getWorldCitiesAction()  {
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['limit']) || !isset($request['offset']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $countryid = "";
            if(!isset($request['id'])){
                $countryList = $this->countriesTable->getCountries4App(['limit' => 1, 'offset' => 0]);
                $countryid = $countryList[0]['id'];
            }else{
                $countryid = $request['id'];
            }
            $cityList = $this->citiesTable->getWorldCities4App(['limit' => $request['limit'], 'offset' => $request['offset']], 0, $countryid);
            return new JsonModel(['places' => $cityList]);
        }else{
            return $tvResponse;
        }
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
            if(!isset($request['id'])){
                $stateList = $this->statesTable->getStates4App(['limit' => 1, 'offset' => 0]);
                $stateid = $stateList[0]['id'];
            }else{
                $stateid = $request['id'];
            }
            $cityList = $this->citiesTable->getIndiaCities4App(['limit' => $request['limit'], 'offset' => $request['offset']], 0, $stateid);
            return new JsonModel(['places' => $cityList]);
        }else{
            return $tvResponse;
        }
    }

    public function getIndiaPlacesAction()  {
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['limit']) || !isset($request['offset']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $stateid = "";
            $stateList = $this->statesTable->getStates4App(['limit' => 1, 'offset' => 0]);
            $stateid = $stateList[0]['id'];
            $cityid = "";
            if(!isset($request['city_id'])){
                $cityList = $this->citiesTable->getIndiaCities4App(['limit' => 1, 'offset' => 0],0, $stateid);
                $cityid = $cityList[0]['id'];
            }else{
                $cityid = $request['city_id'];
            }
            $placeList = $this->placesTable->getPlaces4App(['limit' => $request['limit'], 'offset' => $request['offset']], 0, $cityid, true);
            // $totalCount = $this->placesTable->getIndiaPlaces4App(['limit' => 0, 'offset' => 0], 1, $cityid);
            return new JsonModel(['places' => $placeList]);
        }else{
            return $tvResponse;
        }
    }

    public function getWorldPlacesAction()  {
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['limit']) || !isset($request['offset']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $countryid = "";
            $countryList = $this->countriesTable->getCountries4App(['limit' => 1, 'offset' => 0]);
            $countryid = $countryList[0]['id'];
            $cityid = "";
            if(!isset($request['city_id'])){
                $cityList = $this->citiesTable->getWorldCities4App(['limit' => 1, 'offset' => 0],0, $countryid);
                $cityid = $cityList[0]['id'];
            }else{
                $cityid = $request['city_id'];
            }
            $placeList = $this->placesTable->getPlaces4App(['limit' => $request['limit'], 'offset' => $request['offset']], 0, $cityid, false);
            return new JsonModel(['places' => $placeList]);
        }else{
            return $tvResponse;
        }
    }

    public function getAllPlacesAction(){
        $request = $this->getRequest()->getPost();
        $placeList = $this->placesTable->getAllPlaceDetails($request['it'], ['limit' => $request['limit'], 'offset' => $request['offset']]);
        return new JsonModel(['places' => $placeList]);
    }

    public function sampleAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['limit']) || !isset($request['offset']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            return new JsonModel(['success' => true, 'message' => "Sampls Api called..."]);
        }else{
            return $tvResponse;
        }
    }

    public function getIndiaTalesAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['limit']) || !isset($request['offset']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $itList = $this->tourTalesTable->getPlacesList4App(['tour_type' => \Admin\Model\TourTales::tour_type_India_tour, 'limit' => $request['limit'], 'offset' => $request['offset']],0,1);
            return new JsonModel(['tales' => $itList]);
        }else{
            return $tvResponse;
        }
    }

    public function getWorldTalesAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            $itList = $this->tourTalesTable->getPlacesList4App(['tour_type' => \Admin\Model\TourTales::tour_type_World_tour, 'limit' => $request['limit'], 'offset' => $request['offset']],0,1);
            return new JsonModel(['tales' => $itList]);
        }else{
            return $tvResponse;
        }
    }

    public function getFreeIndiaTalesAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            $itList = $this->tourTalesTable->getPlacesList4App(['tour_type' => \Admin\Model\TourTales::tour_type_Free_India_tour, 'limit' => $request['limit'], 'offset' => $request['offset']],0,1);
            return new JsonModel(['tales' => $itList]);
        }else{
            return $tvResponse;
        }
    }

    public function getFreeWorldTalesAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            $itList = $this->tourTalesTable->getPlacesList4App(['tour_type' => \Admin\Model\TourTales::tour_type_Free_World_tour, 'limit' => $request['limit'], 'offset' => $request['offset']],0,1);
            return new JsonModel(['tales' => $itList]);
        }else{
            return $tvResponse;
        }
    }

    public function downloadTalesAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['placeIds']) || !isset($request['primary_language']) || !isset($request['secondary_language']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $csvPlaceIds = $request['placeIds'];
            $csvPlaceIds = '510,520,521,522,523,524,525,526,527,528,529,530,39'; //'51,52,55,59,60,85,87,88';
            $placeIdsArray = explode(',', $csvPlaceIds); 
            // $talesFiles = $this->tourismFilesTable->getTourismFiles(['file_data_type' => \Admin\Model\TourismFiles::file_data_type_places, 'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_audio], $request['primary_language'],$request['secondary_language']); // 'file_data_id' => $placeIdsArray,
            $talesFiles = $this->tourismFilesTable->getTourismFiles(['file_data_type' => \Admin\Model\TourismFiles::file_data_type_places, 'file_data_id' => $placeIdsArray], $request['primary_language'],$request['secondary_language']); 
            $newArr = [];
            array_walk($talesFiles, function ($item, $key) use (&$newArr) {
                $item['file_path'] = $item['file_extension'] == 'mp3' ? 'data/attachments/ENG-ST-1-1-1A-Subhadra-AP-Tirupati-Tirumala+Sacred+Temple.mp3' : $item['file_path'];
                $newArr[$key] = $item;
            });
            // return new JsonModel(['files' => $talesFiles]);
            return new JsonModel(['files' => $newArr]);
        }else{
            return $tvResponse;
        }
    }

    public function getUserProfileAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['user_login_id']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $userProfile = $this->userTable->getUserDetails(['user_login_id' => $request['user_login_id']]);
            $userProfile['primary_language_name'] = $this->languageTable->getField(['id'=>$userProfile['primary_language']], 'language_name');
            $userProfile['secondary_language_name'] = $this->languageTable->getField(['id'=>$userProfile['secondary_language']], 'language_name');
            $userProfile['isSubscribed'] = $this->questtSubscriptionTable->isValidQuesttUser($userProfile['id']);
            if($userProfile['isSubscribed']){
                $qed = $this->questtSubscriptionTable->getField(['user_id' => $userProfile['id']], 'end_date');
                $userProfile['subscriptionExpiry'] = date('Y-m-d', strtotime($qed));
            }else{
                $userProfile['subscriptionExpiry'] = "NA"; //date('10/03/2025');
            }
            $userProfile['pwdBalance'] = '0';
            return new JsonModel(array('success' => true, "data" => $userProfile));
        }else{
            return $tvResponse;
        }
    }

    public function setUserProfileAction(){
        $logResult = $this->logRequest($this->getRequest()->toString());
        $tvResponse = $this->validateAccessToken(request: $this->getRequest());
        $respdata = $tvResponse->getVariables();
        if($respdata['success']){
            $request = $this->getRequest()->getPost();
            if (!isset($request['user_login_id']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            if (!isset($request['username']) || !isset($request['country']) || !isset($request['email']))
                return new JsonModel(array('success'=>false,'message'=>'required data missing..'));
            $where = ['user_login_id' => $request['user_login_id']];
            $saveData = [];
            if(isset($request['username']))
                $saveData['username'] = $request['username'];
            if(isset($request['country']))
                $saveData['country'] = $request['country'];
            if(isset($request['email']))
                $saveData['email'] = $request['email'];
            if(count($saveData) > 0)
                $updateResp = $this->userTable->setFields($saveData, $where);
            if ($updateResp)
                return new JsonModel(array('success' => true, "message" => 'profile updated successfull..'));
            else
                return new JsonModel(array('success' => false, "message" => 'unable to update.. try after sometime..'));
        }else{
            return $tvResponse;
        }
    }
}