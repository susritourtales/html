<?php

namespace Api\Controller;


use Application\Controller\BaseController;
use Application\Handler\Aes;
use Application\Handler\Razorpay;
use Application\Handler\SendFcmNotification;
use Mpdf\Mpdf;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Application\Handler\Instamojo;
use Application\Channel\Sms;


class IndexController extends BaseController{
    public function indexAction()
    {
         // echo 'hi';
         // exit;
        $request = $this->getRequest()->getPost();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$this->tokenValidation($request['token']))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
       /* $states=$this->statesTable()->getStates();
           $reusls=array();
           $i=0;
           foreach ($states as $state)
           {
             $status=  $this->citiesTable()->updateCity(array('country_id'=>$state['country_id']),array('state_id'=>$state['id']));

             if($status)
             {
                 $reusls[]=$i;
                 $i++;
             }
           }*/
       //exit;

        return new ViewModel();
    }

    public function countryListAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());        
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $tourType=$request['tour_type'];
        $limit=$request['limit'];
        $offset=$request['offset'];
        $search=$request['search'];

        if(is_null($search))
        {
            $search='';
        }
        if(is_null($tourType))
        {
            $tourType=\Admin\Model\PlacePrices::tour_type_World_tour;
        }
        if(is_null($limit))
        {
            $limit=10;
        }else
         {
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else
        {
            $offset=intval($offset);
        }

        $countriesList=$this->countriesTable()->getActiveCountriesList(array('limit'=>$limit,'offset'=>$offset,'tour_type'=>$tourType,'search'=>$search));
        
        return new JsonModel(array('success'=>true,'countries'=>$countriesList));
    }

    public function getUpcomingToursAction(){
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $tourType=$request['tour_type'];
        $limit=$request['limit'];
        $offset=$request['offset'];

        if(is_null($tourType))
        {
            $tourType=\Admin\Model\PlacePrices::tour_type_World_tour;
        }

        if(is_null($limit))
        {
            $limit=10;
        }else
         {
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else{
            $offset=intval($offset);
        }

        $placesList=$this->upcomingTable()->getUpcomingList(array('tour_type'=>$tourType,'limit'=>$limit, 'offset'=>$offset));
        return new JsonModel(array('success'=>true,'tours'=>$placesList));
    }

    public function getAvailableToursAction(){
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $tourType=$request['tour_type'];
        $upcoming=$request['upcoming'];
        $limit=$request['limit'];
        $offset=$request['offset'];

        if(is_null($tourType))
        {
            $tourType=\Admin\Model\PlacePrices::tour_type_World_tour;
        }

        if(is_null($limit))
        {
            $limit=10;
        }else
         {
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else{
            $offset=intval($offset);
        }

        if($upcoming == "1")
        {
            $placesList=$this->upcomingTable()->getUpcomingList(array('tour_type'=>$tourType,'api'=>'1','limit'=>$limit, 'offset'=>$offset));
        }
        else 
        {
            if($tourType==\Admin\Model\PlacePrices::tour_type_India_tour || $tourType == \Admin\Model\PlacePrices::tour_type_Spiritual_tour)
            {
                $placesList=$this->tourismPlacesTable()->getAvailableList(array('tour_type'=>$tourType,'limit'=>$limit, 'offset'=>$offset, 'city_order'=>1, 'place_name_order'=>1, 'country_id'=>'101'));
            }
            else if($tourType==\Admin\Model\PlacePrices::tour_type_Seasonal_special){
                $placesList=$this->seasonalSpecialsTable()->getAvailableFT(array('limit'=>$limit, 'offset'=>$offset));
            }
            else{
                $placesList=$this->tourismPlacesTable()->getAvailableList(array('tour_type'=>$tourType,'limit'=>$limit, 'offset'=>$offset, 'country_order'=>1, 'city_order'=>1));
            }
            /* if($tourType == \Admin\Model\PlacePrices::tour_type_India_tour)
                $placesList=$this->placePriceTable()->getPlacesList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'limit'=>$limit,'offset'=>$offset)); */
            //$placesList=$this->tourismPlacesTable()->getActiveTourismPlacesList(array('tour_type'=>$tourType,'limit'=>$limit, 'offset'=>$offset));  
        }
        return new JsonModel(array('success'=>true,'tours'=>$placesList));
    }

    public function stateListAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $tourType=$request['tour_type'];
        $countryId=$request['country_id'];
        $limit=$request['limit'];
        $offset=$request['offset'];
        $search=$request['search'];

        if(is_null($search))
        {
            $search='';;
        }
        if(is_null($tourType))
        {
            $tourType=\Admin\Model\PlacePrices::tour_type_World_tour;
        }

        if(is_null($limit))
        {
            $limit=10;
        }else
         {
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else{
            $offset=intval($offset);
        }

        if($tourType==\Admin\Model\PlacePrices::tour_type_India_tour || $tourType == \Admin\Model\PlacePrices::tour_type_Spiritual_tour)
        {
            $countryId=$this->countriesTable()->getField(array('country_name'=>'india'),'id');
        }

        $statesList=$this->statesTable()->getActiveStatesList(array('limit'=>$limit,'offset'=>$offset,'tour_type'=>$tourType,'country_id'=>$countryId,'search'=>$search));

        return new JsonModel(array('success'=>true,'states'=>$statesList));
    }

    public function cityListAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $tourType=$request['tour_type'];
        $countryId=$request['country_id'];
        $stateId=$request['state_id'];
        $limit=$request['limit'];
        $offset=$request['offset'];
        $search=$request['search'];

        if(is_null($search))
        {
            $search='';;
        }
        if(is_null($tourType))
        {
            $tourType=\Admin\Model\PlacePrices::tour_type_World_tour;
        }

        if(is_null($limit))
        {
            $limit=10;
        }else{
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else{
            $offset=intval($offset);
        }

        if(is_null($stateId))
        {
            $stateId='';
        }
        
        $citiesList=$this->citiesTable()->getActiveCitiesList(array('limit'=>$limit,'offset'=>$offset,'tour_type'=>$tourType,'country_id'=>$countryId,'state_id'=>$stateId,'search'=>$search));
        
        return new JsonModel(array('success'=>true,'cities'=>$citiesList));
    }
    public function placesListAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $tourType=$request['tour_type'];
        $limit=$request['limit'];
        $offset=$request['offset'];
        $search=$request['search'];

        if(is_null($search))
        {
            $search='';;
        }
        if(is_null($tourType))
        {
            $tourType=\Admin\Model\PlacePrices::tour_type_World_tour;
        }
        if(is_null($limit))
        {
            $limit=10;
        }else{
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else{
            $offset=intval($offset);
        }


        // $where=array();
        $cityId=$request['city_id'];
        if($tourType==\Admin\Model\PlacePrices::tour_type_City_tour)
        {
            $limit=-1;
        }
        $days=$this->cityTourSlabDaysTable()->getSlabDays();
        if($days)
        {
            $noOfDays=$days;
        }else{
            $noOfDays=4;
        }
        $citiesList=$this->tourismPlacesTable()->getActiveTourismPlacesList(array('limit'=>$limit,'days'=>$days,'offset'=>$offset,'tour_type'=>$tourType,'city_id'=>$cityId,'search'=>$search));


        return new JsonModel(array('success'=>true,'places'=>$citiesList,'days'=>$noOfDays));
    }
    public function getLanguagesListAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$this->tokenValidation($request['token']))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $maxId=$request['max_id'];
        if(is_null($maxId)){
            $maxId=0;
        }
        $languagesList=$this->languagesTable()->getLanguagesByMaxId($maxId);
        return new JsonModel(array('success'=>true,'languages'=>$languagesList));
    }
    public function checkUserAction()
    {
        $request = $this->getRequest()->getPost();
        if(!$this->tokenValidation($request['token']))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $countrycode = $request['mobile_country_code'];
        $phone = $request['mobile'];
        if(is_null($phone)){
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $userExists=$this->userTable()->getFields(array("mobile"=>$phone),array('user_id'));
        return new JsonModel(array('success'=>true,'message'=>count($userExists)));
    }
    public function getTourismPlacesAction()
    {
        $request = $this->getRequest()->getPost();
        if(!$this->tokenValidation($request['token']))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $cityId=$request['city_id'];
        $where=array('city_id'=>$cityId);
        $where=array();
        $maxId=$request['max_id'];
        if(is_null($maxId))
        {
            $maxId=0;
        }
        $date=$request['updated_date'];
        $placesList=$this->tourismPlacesTable()->getTourismPlacesList($date);
        return new JsonModel(array('success'=>true,'tourism_places'=>$placesList));
    }

    public function getTourismPlaceFilesAction()
    {
        $request = $this->getRequest()->getPost();
        if(!$this->tokenValidation($request['token']))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $tourismPlaceId=$request['tourism_place_id'];
        $where=array('tourism_place_id'=>$tourismPlaceId);
        $where=array();
        $maxId=$request['max_id'];
        $date=$request['updated_date'];


        $placesList=$this->tourismFilesTable()->getTourismFilesList($date);
        return new JsonModel(array('success'=>true,'tourism_places'=>$placesList));
    }

   /* public function checkPasswordAction()
    {
        $request=$this->getRequest()->getPost();
        if(!$this->tokenValidation($request['token']))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $password=$request['password'];
        $adminpassword='root@123456';
          $passwordCheck=$this->passwordTable()->checkPassword($password);
        if(count($passwordCheck))
        {
            return  new JsonModel(array('success'=>true,'message'=>'Successfully login',"app_version"=>$passwordCheck[0]['id']));
        }
        return  new JsonModel(array('success'=>false,'message'=>'Invalid creditional'));

    }*/
    public function otpVerifyAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());

        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }        
        $otp=$request["otp"];
        $user_id=$request["user_id"];
        $countryCode = $request["country_code"];
        $mobile = $request["mobile"];

        if($countryCode == "91"){
            if($otp=='' || $otp==null)
            {
                return new JsonModel(array("success"=>false,"message"=>"OTP is required"));
            }
        }

        if($user_id=='' || $user_id==null)
        {
            return new JsonModel(array("success"=>false,"message"=>"UserId is missing"));
        }
        if($countryCode == '' || $countryCode == null)
        {
            return new JsonModel(array("success"=>false,"message"=>"Country code is missing"));
        }
        $type=intval($request['otp_type']);
        if($type==\Admin\Model\Otp::FORGOT_OTP)
        {
            $data=array("otp"=>$otp,"user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::FORGOT_OTP,"status"=>\Admin\Model\Otp::Not_verifed);
        }else  if($type==\Admin\Model\Otp::REGISTER_EMAIL_OTP)
        {
            $data=array("otp"=>$otp,"user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::REGISTER_EMAIL_OTP,"status"=>\Admin\Model\Otp::Not_verifed);
        }else
        {
            $data=array("otp"=>$otp,"user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::LOGIN_OTP,"status"=>\Admin\Model\Otp::Not_verifed);
        }

        if($countryCode != "91"){
            $checkUser=$this->userTable()->getUserByUserId($user_id);
            $prefs=$this->pricingTable()->getPrefs();
            $photoFilePath = $this->sponsorPhotoTable()->getField(array('file_data_id'=>$user_id),'file_path');
            $refDetails = $this->referTable()->getRefers(array('user_id'=>$user_id));
            $user=array(
                'name'=>$checkUser['user_name'],
                'email'=>$checkUser['email'],
                'id'=>$checkUser['user_id'],
                'type'=>intval($checkUser['role']),
                'mobile'=>$checkUser['mobile'],
                'mobile_country_code'=>$checkUser['mobile_country_code'],
                'user_type'=>$checkUser['role'],   // added by Manjary for STT subscription version
                'role'=>$checkUser['role'],    // added by Manjary for STT subscription version
                'ref_name'=>$refDetails[0]['ref_by'],
                'ref_mobile'=>$refDetails[0]['ref_mobile'],
                'resState'=>$checkUser['res_state'],  // Added by Manjary for STT subscription version
                'address'=>$checkUser['address'],  // Added by Manjary for STT subscription version
                'sponsor_type'=>$checkUser['sponsor_type'],    // Added by Manjary for STT subscription version
                'subscription_end_date'=>$checkUser['subscription_end_date'],  // Added by Manjary 
                'non_renewal_handled'=>false,   // Added by Manjary
                'image_path'=>$photoFilePath,  // Added by Manjary for STT 
                'is_promoter'=>$checkUser['is_promoter']
            );
            $prefs=array(
                'max_tales'=>$prefs['max_tales'],
                'time_frame'=>$prefs['time_frame'],
                'max_qty'=>$prefs['max_qty']
            );
            return new JsonModel(array("success"=>true,"message"=>"Account verified","user"=>$user, "prefs"=>$prefs));
        }

        $verfiy=$this->otpTable()->verfiy($data);
        if($verfiy)
        {
            $updateResponse=$this->otpTable()->updateData(array('status'=>\Admin\Model\Otp::Is_verifed),$data);
           /* $where=array("user_id"=>$user_id);
            if($type==\Admin\Model\Otp::REGISTER_EMAIL_OTP)
            {
                $update=array("is_email_verified"=>\Admin\Model\User::Is_user_verified);
                $this->userTable()->updateUser($update,$where);
            }else if($type==\Admin\Model\Otp::LOGIN_OTP){
                $update=array("is_mobile_verified"=>\Admin\Model\User::Is_user_verified);
                $this->userTable()->updateUser($update,$where);
            }*/

            $checkUser=$this->userTable()->getUserByUserId($user_id);
            $prefs=$this->pricingTable()->getPrefs();
            $photoFilePath = $this->sponsorPhotoTable()->getField(array('file_data_id'=>$user_id),'file_path');
            $refDetails = $this->referTable()->getRefers(array('user_id'=>$user_id));
            $user=array(
                'name'=>$checkUser['user_name'],
                'email'=>$checkUser['email'],
                'id'=>$checkUser['user_id'],
                'type'=>intval($checkUser['role']),
                'mobile'=>$checkUser['mobile'],
                'mobile_country_code'=>$checkUser['mobile_country_code'],
                'user_type'=>$checkUser['role'],   // added by Manjary for STT subscription version
                'role'=>$checkUser['role'],    // added by Manjary for STT subscription version
                'ref_name'=>$refDetails[0]['ref_by'],
                'ref_mobile'=>$refDetails[0]['ref_mobile'],
                'resState'=>$checkUser['res_state'],  // Added by Manjary for STT subscription version
                'address'=>$checkUser['address'],  // Added by Manjary for STT subscription version
                'sponsor_type'=>$checkUser['sponsor_type'],    // Added by Manjary for STT subscription version
                'subscription_end_date'=>$checkUser['subscription_end_date'],  // Added by Manjary 
                'non_renewal_handled'=>false,   // Added by Manjary
                'image_path'=>$photoFilePath,  // Added by Manjary for STT 
                'is_promoter'=>$checkUser['is_promoter']
            );
            $prefs=array(
                'max_tales'=>$prefs['max_tales'],
                'time_frame'=>$prefs['time_frame'],
                'max_qty'=>$prefs['max_qty']
            );
            return new JsonModel(array("success"=>true,"message"=>"Account verified","user"=>$user, "prefs"=>$prefs));
        }else
        {
            return new JsonModel(array("success"=>false,"message"=>"Invalid Otp"));
        }
    }

    public function getLimitsAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        
         $prefs=$this->pricingTable()->getPrefs();
         return new JsonModel(array('success'=>true,'message'=>'success', 'max_tales'=>$prefs['max_tales'],'time_frame'=>$prefs['time_frame'], 'max_qty'=>$prefs['max_qty']));
    }
    public function subscribeTextsAction()
    {
       $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
       $request = $this->getRequest()->getPost();
       $mobile = $request['mobile'];
       $mobileCountryCode = $request['mobile_country_code'];

       if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
       {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
       }
       if($mobile != ""){
           $checkUser=$this->userTable()->getField(array('mobile_country_code'=>$mobileCountryCode,'mobile'=>$mobile),'user_id');
       }
       $userExists = false;
       if($checkUser){
           $userExists = true;
       }
       $appTexts = $this->pricingTable()->getAppTexts($mobileCountryCode);
       $ssc = $this->sscTable()->getSSC();
       $count = "";
       if($ssc == "1"){
           $subscribers = $this->userTable()->getAllSubscribers();
           $count = count($subscribers);
       }
       $appTexts['count'] = $count;
       $appTexts['user_exists'] = $userExists;
       if(!empty($appTexts)){
           return  new JsonModel(array('success'=>true,'message'=>'Success','subscribe_texts'=>$appTexts));
       }
       return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
    }

    public function getVariablesAction(){
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $mobile = $request['mobile'];
        $mobileCountryCode = $request['mobile_country_code'];
        //print_r($request);
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($mobile != ""){
            $checkUser=$this->userTable()->getField(array('mobile_country_code'=>$mobileCountryCode,'mobile'=>$mobile),'user_id');
        }
        $userExists = false;
        if($checkUser){
            $userExists = true;
        }
        $retVars = $this->pricingTable()->getAppTexts($mobileCountryCode);
        $ssc = $this->sscTable()->getSSC();
        $count = "";
        if($ssc == "1"){
            $subscribers = $this->userTable()->getAllSubscribers();
            $count = count($subscribers);
        }
        $retVars['count'] = $count;
        $retVars['user_exists'] = $userExists;

         $variables = $this->pricingTable()->getPricingDetails(array('plantype'=>'0'));
         $retVars['success'] = $userExists;
         $retVars['message'] = $userExists ? 'success' : 'no such user';
         $retVars['no_of_comp_pwds'] = $variables['no_of_comp_pwds'];
         $retVars['no_of_days'] = $variables['no_of_days'];
         $retVars['maxdownloads'] = $variables['maxdownloads'];
         $retVars['maxquantity'] = $variables['maxquantity'];
         $retVars['subscription_validity'] = $variables['subscription_validity'];
         $retVars['sponsor_bonus_min'] = $variables['sponsor_bonus_min'];
         $retVars['sponsor_comp_pwds'] = $variables['sponsor_comp_pwds'];
         $retVars['sponsor_pwd_validity'] = $variables['sponsor_pwd_validity'];
         $retVars['GST'] = $variables['GST'];
         $retVars['first_rdp'] = $variables['first_rdp'];
         $retVars['second_rdp'] = $variables['second_rdp'];
         $retVars['npc_passwords'] = $variables['npc_passwords'];
         $retVars['npc_days'] = $variables['npc_days'];
         $retVars['annual_price'] = $variables['price'];

         $pvariables = $this->pricingTable()->getPricingDetails(array('plantype'=>'1'));
         $retVars['offer_start_date'] = date('d-m-y', strtotime($pvariables['start_date']));
         $retVars['offer_end_date'] = date('d-m-y', strtotime($pvariables['end_date']));
         $retVars['offer_price'] = $pvariables['price'];

         return new JsonModel($retVars);
    }

    public function resendOtpAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $user_id=$request["user_id"];
        
            if($user_id=='' || $user_id==null)
            {
                return new JsonModel(array("success"=>false,"message"=>"UserId is missing"));
            }

        $userDetails=$this->userTable()->getUserByUserId($user_id);
        if(count($userDetails))
        {
            $type=intval($request['otp_type']);
                  if(is_null($type))
                  {
                      $type=\Admin\Model\Otp::LOGIN_OTP;
                  }
            if($type==\Admin\Model\Otp::FORGOT_OTP)
            {
                $data=array("user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::FORGOT_OTP,"status"=>\Admin\Model\Otp::Not_verifed);
            }else
            {
                $data=array("user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::LOGIN_OTP,"status"=>\Admin\Model\Otp::Not_verifed);
            }

            $update = $this->otpTable()->updateData(array('status' => \Admin\Model\Otp::Is_verifed), $data);
            $otp="1111"; //$this->generateOtp();
            if($type==\Admin\Model\Otp::LOGIN_OTP)
            {
                $mobileNumber = $userDetails['mobile_country_code'] . $userDetails['mobile'];
                //  $otp = $this->generateOtp();
                $otpData = array("user_id" => $user_id, "otp" => $otp, "otp_type" => $type , "status" => \Admin\Model\Otp::Not_verifed);
                $this->otpTable()->insertData($otpData);
                $response = $this->sendOtpSms($mobileNumber, $otp);
            }else if($type==\Admin\Model\Otp::FORGOT_OTP)
            {
                $mobileNumber = $userDetails['mobile_country_code'] . $userDetails['mobile'];
                //$this->sendOtpSms($mobileNumber, $otp);
                $otpData = array("user_id" => $user_id, "otp" => $otp, "otp_type" => $type , "status" => \Admin\Model\Otp::Not_verifed);
                $this->otpTable()->insertData($otpData);
                $response = $this->sendOtpSms($mobileNumber, $otp,'forgot-password');
            }

            $errMsg = "";
             $jsonResp = json_decode($response, true);
             if(count($jsonResp['errors'])){
                $errArr = $jsonResp['errors'];
                $errMsg = $errMsg . $errArr[0]['message'];
             }
             if(count($jsonResp['warnings'])){
                $warnArr = $jsonResp['warnings'];
                $errMsg = $errMsg . $warnArr[0]['message'];
             }  
             if($errMsg != ""){
                 return new JsonModel(array('success'=>false,'message'=>$errMsg));
             }
            return new JsonModel(array("success" => true, "message" => "Otp Has been sent to mobile", "user_id" => $user_id, "otp" => $otp));

        }else
        {
            return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
        }
    }
    public function userLoginAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $mobileNumber=$request['mobile'];
        $countryCode=$request['mobile_country_code'];
        $vc = $request['vc'];

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        /* if(!$this->isApkVersionlatest($vc)){
            return  new JsonModel(array('success'=>false,'message'=>'Please update your app to latest version available on playstore'));
        } */
        if($mobileNumber=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Mobile number missing'));
        }
        if($countryCode=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Country code missinsg'));
        }
        //$checkMobile=$this->userTable()->verfiyUser(array('mobile'=>$mobileNumber,'mobile_country_code'=>$countryCode,'role'=>\Admin\Model\User::Individual_role));  -- removed by Manjary
        $checkMobile=$this->userTable()->verfiyUser(array('mobile'=>$mobileNumber,'mobile_country_code'=>$countryCode));
         if(count($checkMobile))
         {             
             $user_id=$checkMobile[0]['user_id'];
             $cleared = $this->fcmTable()->clearFCMOnLogin($user_id);             
             $data=array("user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::LOGIN_OTP,"status"=>\Admin\Model\Otp::Not_verifed);

             $update = $this->otpTable()->updateData(array('status' => \Admin\Model\Otp::Is_verifed), $data);
             $otp="1111"; //$this->generateOtp();
             $insertData=array("user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::LOGIN_OTP,"status"=>\Admin\Model\Otp::Not_verifed,'otp'=>$otp);
             $this->otpTable()->insertData($insertData);
             $response = $this->sendOtpSms($countryCode.$mobileNumber, $otp);
             /* $errMsg = "";
             $jsonResp = json_decode($response, true);
             if(count($jsonResp['errors'])){
                $errArr = $jsonResp['errors'];
                $errMsg = $errMsg . $errArr[0]['message'];
             }
             if(count($jsonResp['warnings'])){
                $warnArr = $jsonResp['warnings'];
                $errMsg = $errMsg . $warnArr[0]['message'];
             }  
             if($errMsg != ""){
                 return new JsonModel(array('success'=>false,'message'=>$errMsg));
             } */
             if(!$this->isApkVersionlatest($vc)){
                return  new JsonModel(array('success'=>true,'message'=>'Please update your app to latest version available on playstore','user_id'=>$user_id,'status'=>2));
             }
             else
                return  new JsonModel(array('success'=>true,'message'=>'','user_id'=>$user_id,'status'=>2));

         }else{
             $saveData=array('mobile_country_code'=>$countryCode,'mobile'=>$mobileNumber,'role'=>\Admin\Model\User::Individual_role,'status'=>1);
             $result=$this->userTable()->newUser($saveData);
            if(!$result['success'])
            {
                return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
            }
             $user_id=$result['user_id'];

             $data=array("user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::LOGIN_OTP,"status"=>\Admin\Model\Otp::Not_verifed);

             $update = $this->otpTable()->updateData(array('status' => \Admin\Model\Otp::Is_verifed), $data);

             $otp="1111"; //$this->generateOtp();

             $insertData=array("user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::LOGIN_OTP,"status"=>\Admin\Model\Otp::Not_verifed,'otp'=>$otp);

             $this->otpTable()->insertData($insertData);

             $response = $this->sendOtpSms($countryCode.$mobileNumber,$otp);
             /* $errMsg = "";
             $jsonResp = json_decode($response, true);
             if(count($jsonResp['errors'])){
                $errArr = $jsonResp['errors'];
                $errMsg = $errMsg . $errArr[0]['message'];
             }
             if(count($jsonResp['warnings'])){
                $warnArr = $jsonResp['warnings'];
                $errMsg = $errMsg . $warnArr[0]['message'];
             }  
            
             if($errMsg != ""){
                 return new JsonModel(array('success'=>false,'message'=>$errMsg));
             } */
             return  new JsonModel(array('success'=>true,'message'=>'','user_id'=>$user_id,'status'=>1));
         }
    }
    public function testSmsAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $mobileNumber=$request['mobile'];
        $countryCode=$request['mobile_country_code'];
        $msgTxt = "Welcome%20to%20Sponsors%27%20Group%20of%20Susri%20Tour%20Tales.%0ABuy%20subscription%20passwords%20at%20discounted%20prices%20and%20sell%20at%20profits.%20%0ASee%20e-mail%20message%20for%20details.";
        $response = $this->sendPasswordSmsTest($countryCode.$mobileNumber, $msgTxt);
        //$response = $this->sendOtpSmsTest($countryCode.$mobileNumber,"3434");
        $errMsg = "";
        $jsonResp = json_decode($response, true);
        if(count($jsonResp['errors'])){
        $errArr = $jsonResp['errors'];
        $errMsg = $errMsg . $errArr[0]['message'] . "\n";
        }
        if(count($jsonResp['warnings'])){
        $warnArr = $jsonResp['warnings'];
        $errMsg = $errMsg . $warnArr[0]['message'] . "\n";
        }  
        print_r($response);
        if($errMsg != ""){
            return new JsonModel(array('success'=>false,'message'=>$errMsg));
        }
        return  new JsonModel(array('success'=>true,'message'=>'Message delivered'));
        //return $response;
    }
    public function sendOtpSmsTest($mobile, $otp,$smsAction='otp')
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
    public function sendPasswordSmsTest($mobile, $data,$smsAction='notp')
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
     public function profileUpdateAction()
     {
         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
        
         $userName = $request['name'];
         $email = $request['email'];
         $userId = $request['user_id'];
         $rState = $request['resState'];
         $address = $request['address'];
         $sponsorType = $request['sponsor_type'];
         $refName = $request['ref_name'];
         $refMobile = $request['ref_mobile'];
         $role = $request['role'];
         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($userName)
         {
             $data['user_name']=$userName;
            // return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($email)
         {
             $data['email']=$email;
         }
         if($rState)
         {
             $data['res_state']=$rState;
         }
         if($address)
         {
             $data['address']=$address;
         }
         if($sponsorType)
         {
             $data['sponsor_type']=$sponsorType;
             $data['sponsor_reg_date'] = date("Y-m-d H:i:s");
         }
         if($refName != "" || $refMobile != ""){
            $refData['ref_by'] = $refName;
            $refData['ref_mobile'] = $refMobile;
            $refData['user_id'] = $userId;
            $refId = $this->userTable()->getField(array('mobile'=>$refMobile), 'user_id');
            if($refId != null){
                $refData['ref_id'] = $refId;
            }
         }
         
         if($role)
         {
            $data['role']=$role;
         }

         if(!count($data))
         {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         $data['discount_percentage'] = 30;
         $roleb4 = $this->userTable()->getField(array('user_id'=>$userId), 'role');
         $profileUpdate=$this->userTable()->updateUser($data,array('user_id'=>$userId));
         //var_dump($data); exit;
         //var_dump($refData); exit;
         $refUpdate['success'] = true;
         if($refData)
            if($refData['ref_id'] != null)
                $refUpdate = $this->referTable()->addRefer($refData);

           if($profileUpdate && $refUpdate['success'])
           {
               //$checkUser=$this->userTable()->getUserByUserId($userId);
               $checkUser=$this->userTable()->getFields(array('user_id'=>$userId),array('user_id','user_name','email','mobile','mobile_country_code','role','subscription_start_date','subscription_end_date','res_state','address','status','company_role','sponsor_type','sponsor_reg_date', 'is_promoter'));
               $photoFilePath = $this->sponsorPhotoTable()->getField(array('file_data_id'=>$userId),'file_path');
               $refDetails = $this->referTable()->getRefers(array('user_id'=>$userId));
               $user=array(
                   'name'=>$checkUser['user_name'],
                   'email'=>$checkUser['email'],
                   'id'=>$checkUser['user_id'],
                   'mobile'=>$checkUser['mobile'],
                   'mobile_country_code'=>$checkUser['mobile_country_code'],
                   'type'=>intval($checkUser['role']),
                   'ref_name'=>$refDetails[0]['ref_by'],
                   'ref_mobile'=>$refDetails[0]['ref_mobile'],
                   'role'=>$checkUser['role'],
                   //'user_type'=>$checkUser['user_type'],  // Added by Manjary for STT subscription version
                   'resState'=>$checkUser['res_state'],  // Added by Manjary for STT subscription version
                   'address'=>$checkUser['address'],  // Added by Manjary for STT subscription version
                   'image_path'=>$photoFilePath,
                   'subscription_end_date'=>$checkUser['subscription_end_date'],
                   'non_renewal_handled'=>false,
                   'sponsor_type'=>$checkUser['sponsor_type'],    // Added by Manjary for STT subscription version
                   'is_promoter'=>$checkUser['is_promoter']
               );
               
               if(intval($role) == \Admin\Model\User::Sponsor_role){
                    if(intval($checkUser['role']) == \Admin\Model\User::Sponsor_role){
                        if($checkUser['role'] != $roleb4){
                            $userDetails=$checkUser;
                            $userDetails['booking_type']=\Admin\Model\Bookings::booking_Sponsorship;
                            $userDetails['subs_start_date'] = $checkUser['subscription_start_date'];
                            $userDetails['subs_end_date'] = $checkUser['subscription_end_date'];
                            $this->mailToUser($userDetails);
                            $this->notifyUser($userDetails);
                            $this->smsUser($userDetails);
                        }
                    }
                }
               return new JsonModel(array("success"=>true,"message"=>"Profile updated","user"=>$user,'status'=>1));
           }
         return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }
    
     public function cityTourBookingAction()
     {
         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $files = $this->getRequest()->getFiles();
         $userId = $request['user_id'];

         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($userId=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         $cities=$request['cities'];
         $placeIds=$request['places'];
         $tourDate=date('y-m-d');
         $noOfUsers=$request['no_of_users'];


         $userType=$request['user_type'];
         $price=$request['price'];


         if($cities=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid cities'));
         }
         if($userType=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid User'));
         }

         if($price=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid place'));
         }

         if($placeIds=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Places'));
         }
         $days=$this->cityTourSlabDaysTable()->getSlabDays();
            if($days)
            {
                $noOfDays=$days;
            }else{
                $noOfDays=4;
            }

         $bookingEndDate=date('Y-m-d', strtotime(date("y-m-d") . ' + 30 days'));

         $uploadFileDetails=array();
         $placeIds=json_decode($placeIds,true);
         $cityIds=json_decode($cities,true);

         $placeIds=implode(",",$placeIds);

         $cityIds=implode(",",$cityIds);
         $validImageFiles=array('png','jpg','jpeg');

         $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name','mobile','email'));


         if(isset($files['itinerary_file']))
         {
           $attachment=$files['itinerary_file'];
                 $imagesPath = '';
                 $pdf_to_image = "";
                 $filename = $attachment['name'];
                 $fileExt = explode(".", $filename);
                 $ext = end($fileExt) ? end($fileExt) : "";
                 $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                 $filename = $filenameWithoutExt ;
                 $filePath = "data/images";
                 @mkdir(getcwd() . "/public/" . $filePath,0777,true);

                 $filePath = $filePath . "/" . $filename.".".$ext;

                 if (!in_array(strtolower($ext), $validImageFiles))
                 {
                     return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                 }
                 /* if (move_uploaded_file($attachment['tmp_name'], getcwd() . "/public/" . $filePath))
                  {
                      $imagesPath = $filePath;
                      $localImagePath = getcwd() . "/public/" . $filePath;
                      chmod(getcwd() . "/public/" . $filePath, 0777);
                  }*/
                 if($attachment['tmp_name'])
                 {
                     $this->pushFiles($filePath, $attachment['tmp_name'], $attachment['type']);
                 }

                 $uploadFileDetails = array ('file_path' => $filePath,
                     'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_itinerary_files,
                     'file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image,
                     'file_extension' => $ext,
                     'status'=>1,
                     'duration'=>0 ,
                     'hash'=>'',
                     'file_language_type' => 0,
                     'file_name' => $attachment['name']);
         }

         $saveData=array('tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour,
             'user_type'=>$userType,
             'user_id'=>$userId,
             'status'=>1,
             'payment_status'=>0,
             'booking_type'=>\Admin\Model\Bookings::booking_by_User
         );

         $price=$this->placePriceTable()->getCityTourPrice(explode(",",$cityIds));

         $passwordsData=array();

         $aes=new Aes();

         $price = $price*$noOfUsers;

         $bookingTourDetails= array(
             'place_ids'=>$placeIds,
             'tour_date'=>$tourDate,
             'expiry_date'=>$bookingEndDate,
             'no_of_days'=>$noOfDays,
             'no_of_users'=>$noOfUsers,
             'price'=>$price,
             'status'=>1
         );

         $applyDiscount=0;

         if($userType==\Admin\Model\User::Tour_Operator_role)
         {

             $tourOperatorDetails=$this->userTable()->getUserDetails($userId);

             if($tourOperatorDetails)
             {

                 if($tourOperatorDetails['apply_discount'])
                 {

                     if($tourOperatorDetails['discount_end_date']>date("Y-m-d"))
                     {
                         $applyDiscount=intval($tourOperatorDetails['discount_percentage']);
                     }
                 }
             }

             $userName = $tourOperatorDetails['contact_person'];

             $email = $tourOperatorDetails['email_id'];

         }else if($userType==\Admin\Model\User::Tour_coordinator_role)
         {
             $tourCoordinatorDetails=$this->userTable()->getUserDetails($userId);
             if($tourCoordinatorDetails)
             {
                 if($tourCoordinatorDetails['apply_discount'])
                 {
                     if($tourCoordinatorDetails['discount_end_date']>date("Y-m-d"))
                     {
                         $applyDiscount=intval($tourCoordinatorDetails['discount_percentage']);
                     }
                 }
             }
             $userName=$tourCoordinatorDetails['user_name'];
             $email=$tourCoordinatorDetails['email'];
         }else
         {
             $userName=$userDetails['user_name'];
             $email=$userDetails['email'];
         }

         $discountPrice=$price;

          if($applyDiscount!=0)
          {
              $bookingTourDetails['discount_percentage']=$applyDiscount;
              $discountPrice=$price-($price*$applyDiscount)/100;
          }else
          {
              $bookingTourDetails['discount_percentage']=0;
          }
         if($discountPrice==0)
         {
             $saveData['payment_status']=1;
         }

         $saveBooking=$this->bookingsTable()->addBooking($saveData);


         if($saveBooking['success'])
         {
             $bookingId=$saveBooking['id'];
             $counter=-1;
             $passwordCounter=-1;
             if(count($uploadFileDetails))
             {
                 $uploadFileDetails['file_data_id']=$saveBooking['id'];
                 $this->tourismFilesTable()->addTourismFiles($uploadFileDetails);
             }

                 $bookingTourDetails['booking_id'] = $saveBooking['id'];
                 $bookingTourDetails["created_at"] = date("Y-m-d H:i:s");
                 $bookingTourDetails["updated_at"] = date("Y-m-d H:i:s");
                 $saveBookingTourDetails=$this->bookingTourDetailsTable()->addBooking($bookingTourDetails);

             for($i=0;$i<$noOfUsers;$i++)
             {
                 $password = $this->random_password();
                 $encodeContent = $aes->encrypt($password);
                 $encryptPassword = $encodeContent['password'];
                 $hash = $encodeContent['hash'];
                 $passwordsData[] = array('user_id' => 0, 'hash' => $hash, 'password' => $encryptPassword, 'status' => 1,"booking_id"=>$bookingId,
                     'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"),'password_type'=>\Admin\Model\Password::passowrd_type_booking);
                 $bookingPassword = array($password.$bookingId);
             }


             $this->passwordTable()->addMutiplePasswords($passwordsData);

             $transactionId='';
             $url='';
             $details=array();
             if($discountPrice!=0) {
                 $api = new Razorpay();
                 $orderData = [
                     'receipt'         => $bookingId,
                     'amount'          => $discountPrice * 100, // 2000 rupees in paise
                     'currency'        => 'INR',
                     'payment_capture' => 1 // auto capture
                 ];

                 $response = $api->paymentRequestCreate($orderData);

                 // $bookingList=$this->bookingsTable()->bookingsDetails($bookingId);
                 $transactionId=$response['id'];
                 $url = $this->getBaseUrl().'/payment-process/'.$transactionId;

                 $userDetails = $this->userTable()->getFields(array('user_id' => $userId), array('email', 'mobile', 'mobile_country_code','role'));
                 $mobile = $userDetails['mobile'];
                 $mobileCountry = $userDetails['mobile_country_code'];
                 $userType = $userDetails['role'];
                 if($userType==\Admin\Model\User::Tour_Operator_role)
                 {
                     $tourOperatorDetails=$this->userTable()->getUserDetails($userId);
                     $userName = $tourOperatorDetails['contact_person'];
                     $email = $tourOperatorDetails['email_id'];
                 }else if($userType==\Admin\Model\User::Tour_coordinator_role)
                 {
                     $tourCoordinatorDetails=$this->userTable()->getUserDetails($userId);
                     $userName=$tourCoordinatorDetails['user_name'];
                     $email=$tourCoordinatorDetails['email'];
                 }else
                 {
                     $userName = $userDetails['user_name'];
                     $email = $userDetails['email'];
                 }
                 $paymentRequest = $this->paymentTable()->addPayment(array('payment_request_id' => $response['id'], 'currency'=>$response['currency'],   'payment_response_id' => '', 'booking_id' => $bookingId, 'status' => \Admin\Model\Payments::payment_in_process));

                 $details=array('keyId'=>\Application\Handler\Razorpay::keyId,'razorpayOrderId' => $transactionId, 'userDetails' => array('email' => $email, 'mobile' => $mobile), 'url' => $this->getBaseUrl());

             }else{
                 $this->sendEmail($bookingId,$userId);
             }
             return  new JsonModel(array('success'=>true,'message'=>'Booked Successfully','booking_id'=>$saveBooking['id'],'payment_details'=>$details,'transaction_id'=>$transactionId,'url'=>$url));

         }
         return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }

     public function subscriptionDetailsAction()
     {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];
        $userType = $request['user_type'];
        $tourType = $request['tour_type'];
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        if($userId=="")
        {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $pricingDetails = $this->getEffectivePricingDetails($userId);
        $pricingDetails["price"] = $pricingDetails["price"] * (1+($pricingDetails['GST']/100));
        $pricingDetails["oriprice"] = $pricingDetails["oriprice"] * (1+($pricingDetails['GST']/100));
        if(!empty($pricingDetails)){
            $sdobject = json_decode(json_encode($pricingDetails), FALSE);
            return  new JsonModel(array('success'=>true,'message'=>'Success','SubscriptionDetails'=>$sdobject));
        }
        return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }

     public function passwordPricingAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];
        $userType = $request['user_type'];
        $tourType = $request['tour_type'];
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        if($userId=="")
        {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $pricingDetails = $this->getPasswordPricingDetails($userId);
        $pricingDetails["price"] = $pricingDetails["price"] * (1+($pricingDetails['GST']/100));
        $pricingDetails["oriprice"] = $pricingDetails["oriprice"] * (1+($pricingDetails['GST']/100));
        if(!empty($pricingDetails)){
            $sdobject = json_decode(json_encode($pricingDetails), FALSE);
            return  new JsonModel(array('success'=>true,'message'=>'Success','SubscriptionDetails'=>$sdobject));
        }
        return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }

     public function getSponsorTypesAction()
     {
         $headers = $this->getRequest()->getHeaders();
         $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         $sponsorTypes = $this->sponsorTypesTable()->getAllSponsorTypes();
         if(count($sponsorTypes)){
            return  new JsonModel(array('success'=>true,'message'=>'Success','sponsor_types'=>$sponsorTypes));
         }
        return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }

     public function getPromoterNameAction(){
         $headers = $this->getRequest()->getHeaders();
         $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         $mobile = $request['mobile'];
         $promoterName = "";
         $promoterName = $this->userTable()->getField(array("mobile"=>$mobile, "is_promoter"=>\Admin\Model\User::Is_Promoter, "role"=>\Admin\Model\User::Sponsor_role), "user_name");
         if(strlen($promoterName)){
            return new JsonModel(array('success'=>true,'message'=>'Success','name'=>$promoterName));
         }
        return new JsonModel(array("success"=>false,"message"=>"Promoter not found. Please check the promoter mobile number entered."));
     }

     public function setPromoterTermsFlagAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $where = array("user_id" => $request['user_id']);
        $data = array("terms_accepted" => $request['terms_accepted']);
        $res = $this->promoterDetailsTable()->addUpdatePromoterDetails($data, $where);
        if($res){
            return new JsonModel(array('success'=>true,'message'=>'success'));
        }
        return new JsonModel(array("success"=>false,"message"=>"Unknown error"));
     }

     public function getPromoterTermsFlagAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $where = array("user_id" => $request['user_id']);
        $col = "terms_accepted";
        $res = $this->promoterDetailsTable()->getField($where, $col);
        if($res){
            return new JsonModel(array('success'=>true,'message'=>$res));
        }
        return new JsonModel(array("success"=>false,"message"=>"not found"));
     }

     public function getPromoterDetailsAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $where = array("user_id" => $request['user_id']);
        $res = $this->promoterDetailsTable()->getPromoterDetails($where);
        $ures = $this->userTable()->getFields($where, array("user_name", "email", "status"=>"is_promoter", "resigned_date"));
        $rArr = array_merge($ures, $res);
        //var_dump($ures);exit;
        if($rArr){
            return new JsonModel(array('success'=>true,'promoter'=>$rArr));
        }
        return new JsonModel(array("success"=>false,"message"=>"not found"));
     }

     public function updatePromoterDetailsAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $where = array("user_id" => $request['user_id']);
        $udata = array("user_name" => $request['user_name'], "email"=> $request['email']);
        $pdata = array("state_city" => $request['state'],"permanent_addr"=>$request['permanent_addr'], "bank_name"=> $request['bank_name'], "ifsc_code"=>$request['ifsc_code'], "bank_ac_no"=>$request['bank_ac_no']);
        $upres = $this->userTable()->updateUser($udata, $where);
        if($upres){
            $uppres = $this->promoterDetailsTable()->addUpdatePromoterDetails($pdata, $where);
            if($uppres){
                $res = $this->promoterDetailsTable()->getPromoterDetails($where);
                $ures = $this->userTable()->getFields($where, array("user_name", "email", "status"=>"is_promoter", "resigned_date"));
                $rArr = array_merge($ures, $res);
                return new JsonModel(array('success'=>true,'promoter'=>$rArr));
            }
            return new JsonModel(array("success"=>false,"message"=>"Details not updated"));
        }
        return new JsonModel(array("success"=>false,"message"=>"Unknown error"));
     }

     public function getPromoterProfessionalSummaryAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $res = $this->userTable()->getPromoterProfessionalSummary($request['user_id']);
        
        if($res){
            return new JsonModel(array('success'=>true,'message'=>'success', 'summary'=>$res));
        }
        return new JsonModel(array("success"=>false,"message"=>"not found"));
     }

     public function setPromoterDetailsAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $where = array("user_id" => $request['user_id']);
        $data = $request;
        unset($data['user_id']);
        
        $res = $this->promoterDetailsTable()->addUpdatePromoterDetails($data, $where);
        if($res){
            return new JsonModel(array('success'=>true,'message'=>'success'));
        }
        return new JsonModel(array("success"=>false,"message"=>"Unknown error"));
     }

     public function getSponsorsPerformanceListAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $limit=$request['limit'];
        $offset=$request['offset'];
        
        if(is_null($limit))
        {
            $limit=10;
        }else{
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else{
            $offset=intval($offset);
        }
        $data = array('promoterId'=>$request['user_id'], 'limit'=>$limit,'offset'=>$offset);
        $res = $this->referTable()->getSponsorsPerfAdmin($data);
        return new JsonModel(array('success'=>true,'message'=>'success', 'sponsors'=>$res));
     }

     /* public function getPromoterTransactionsAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $limit=$request['limit'];
        $offset=$request['offset'];
        
        if(is_null($limit))
        {
            $limit=10;
        }else{
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else{
            $offset=intval($offset);
        }
        $data = array('promoterId'=>$request['user_id'], 'limit'=>$limit,'offset'=>$offset);
        $res = $this->promoterTransactionsTable()->getPromoterPaymentDetails($data);
        return new JsonModel(array('success'=>true,'message'=>'success', 'transactions'=>$res));
     } */

     public function getPromoterSponsorwiseTransactionsAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $limit=$request['limit'];
        $offset=$request['offset'];
        
        if(is_null($limit))
        {
            $limit=10;
        }else{
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else{
            $offset=intval($offset);
        }
        $data = array('sponsorId'=>$request['user_id'], 'limit'=>$limit,'offset'=>$offset);
        $res = $this->promoterTransactionsTable()->getSponsorwiseTransactions($data);
        return new JsonModel(array('success'=>true,'message'=>'success', 'transactions'=>$res));
     }

     public function setSponsorDisc50Action(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $where = array("user_id" => $request['user_id']);
        
        $rresponse = $this->referTable()->updateRefer(array('disc50'=>\Admin\Model\Refer::discount_50), $where);
            if($rresponse){
                $response = $this->userTable()->updateUser(array('discount_percentage'=>50), $where);
                if($response){
                    return new JsonModel(array('success'=>true , 'message'=>'discount applied successfully'));
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'unknown error'));
                }
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unknown error'));
            }
     }

     public function promoterRedeemAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $where = array("user_id" => $request['user_id']);
        $res = $this->promoterDetailsTable()->updatePromoterDetails(array('redeem'=>1), $where);
        if($res){
            return new JsonModel(array('success'=>true,'message'=>'success'));
        }else{
            return new JsonModel(array('success'=>false,'message'=>'unknown error'));
        }
     }

     public function promoterResignAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $where = array("user_id" => $request['user_id']);
        $today = date('Y-m-d H:i:s');
        $response = $this->userTable()->updateUser(array('is_promoter'=>\Admin\Model\User::Is_resigned_Promoter, 'resigned_date'=>$today), $where);
        if($response){
            $rdt = $this->userTable()->getField($where, 'resigned_date');
            return new JsonModel(array('success'=>true , 'message'=>$rdt));
        }else{
            return new JsonModel(array('success'=>false,'message'=>'unknown error'));
        }
     }

     public function sponsorTerminateAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
           return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $where = array("user_id" => $request['user_id']);
        $response = $this->referTable()->updateRefer(array('sponsor_status'=>\Admin\Model\Refer::sponsor_terminated), $where);
        if($response){
            return new JsonModel(array('success'=>true , 'message'=>'sponsor terminated successfully'));
        }else{
            return new JsonModel(array('success'=>false,'message'=>'unknown error'));
        }
    }

     public function buyPasswordsCheckOutAction()
     {
         $headers = $this->getRequest()->getHeaders();
         $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $userId = $request['user_id'];
         $no_of_pwds = $request['no_of_pwds'];
         $vc = $request['vc'];
         
         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         /* if(!$this->isApkVersionlatest($vc)){
            return  new JsonModel(array('success'=>false,'message'=>'Please update your app to latest version available on playstore'));
         } */
         if($userId=="")
         {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         if($no_of_pwds=="" || $no_of_pwds=="0")
         {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         if(!is_null($no_of_pwds) || $no_of_pwds!=""){
            $noOfPwds = intval($no_of_pwds);
         }

         $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name','role','mobile','mobile_country_code','email','discount_percentage', 'discount_end_date','passwords_count','subscription_end_date'));
         if(!count($userDetails))
           {
               return  new JsonModel(array('success'=>false,'message'=>'Invalid Access','error'=>6));
           }
           if($userDetails['role'] != \Admin\Model\User::Sponsor_role){
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
           }
         $subsEndDate = date('Y-m-d',strtotime($userDetails['subscription_end_date']));
         $today = date('Y-m-d');
        if($today > $subsEndDate){
            return  new JsonModel(array('success'=>false,'message'=>'Subscription Expired'));
        }
         $userType = $userDetails['role'];
         $pwdCount = $userDetails['passwords_count'];
         $pricingDetails = $this->getPasswordPricingDetails($userId);
         $unitPrice = $pricingDetails["price"] * (1+($pricingDetails['GST']/100));
         $noOfCompPwds=$pricingDetails["sponsor_comp_pwds"];
         $noOfUsers = $no_of_pwds;
         $data['bonus_flag'] = false;
         if($pwdCount < 10){
             if($pwdCount + $noOfUsers >= 10){
                $noOfUsers = $no_of_pwds + $noOfCompPwds;
                $data['bonus_flag'] = true;
             }
         }         
         $noOfDays=$pricingDetails["sponsor_pwd_validity"];
         $tourType = \Admin\Model\PlacePrices::tour_type_All_tour;

         $saveData=array('tour_type'=>$tourType,'user_type'=>$userType,'user_id'=>$userId,'status'=>1,'booking_type'=>\Admin\Model\Bookings::booking_Buy_Passwords,'payment_status'=>0);
         $tourDate=date("Y-m-d");
         $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . " +  $noOfDays days"));
         $price = $unitPrice * intval($no_of_pwds);
         $bookingTourDetails=array(
             'place_ids'=>"",
             'tour_date'=>$tourDate,
             'expiry_date'=>$bookingEndDate,
             'no_of_days'=>$noOfDays,
             'no_of_users'=>$noOfUsers,
             'price'=>$price,
             'status'=>1
         );

         $passwordsData=array();
         $aes=new Aes();
         $bookingTourDetails['price']=$price;
         //$userName=$userDetails['user_name'];
         $email=$userDetails['email'];
         
         if($userDetails['role'] == \Admin\Model\User::Sponsor_role)// && $userDetails['apply_discount'] == "1")
            $bookingTourDetails['discount_percentage']=$userDetails['discount_percentage'];
         else
            $bookingTourDetails['discount_percentage']=0;

         if($price==0 || round($price)==0)
         {
             $saveData['payment_status']=\Admin\Model\Payments::payment_success;
         }

         if(!$email)
         {
             return  new JsonModel(array('success'=>false,'message'=>'Please Add Email to proceed Booking'));
         }
         
         $saveBooking=$this->bookingsTable()->addBooking($saveData);

         if($saveBooking['success'])
         {
             $bookingTourDetails['booking_id']=$saveBooking['id'];
             $bookingId=$saveBooking['id'];
             $saveBookingTourDetails=$this->bookingTourDetailsTable()->addBooking($bookingTourDetails);
             
             for($i=0;$i<$noOfUsers;$i++)
             {
                 $password = $this->random_password();
                 $encodeContent = $aes->encrypt($password);
                 $encryptPassword = $encodeContent['password'];
                 $hash = $encodeContent['hash'];
                 $passwordType = \Admin\Model\Password::passowrd_type_booking;
                 if($data['bonus_flag']){
                    if($i >= $noOfUsers - $noOfCompPwds){
                        $passwordType = \Admin\Model\Password::passowrd_type_bonus;
                    }
                 }
                 $passwordsData[] = array('user_id' => 0, 'hash' => $hash, 'password' => $encryptPassword, 'status' => 1,"booking_id"=>$bookingId,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"),'password_expiry_date'=>$bookingEndDate,'password_type'=>$passwordType);
                 //$bookingPassword = array($password.$bookingId);
             }

             $this->passwordTable()->addMutiplePasswords($passwordsData);
             //$data['passwords_count'] = $pwdCount + $noOfUsers;
             //$pcUpdate=$this->userTable()->updateUser($data,array('user_id'=>$userId));

             $transactionId='';
             $url='';
             $details=array();
            
             if($price!=0 && round($price)!=0)
             {                 
                 $api = new Razorpay();
                 if($userDetails['mobile_country_code']=="91"){
                    $orderData = [
                        'receipt'         => $bookingId,
                        'amount'          => intval($price * 100),
                        'currency'        => 'INR',
                        'payment_capture' => 1 
                    ];
                 }
                 else{
                    $orderData = [
                        'receipt'         => $bookingId,
                        'amount'          => intval($price * 100),
                        'currency'        => 'USD',
                        'payment_capture' => 1 
                    ];
                 }
                
                 $response = $api->paymentRequestCreate($orderData); 
                 if($response['id'])
                 {
                    $transactionId=$response['id'];
                    $url = $this->getBaseUrl().'/payment-process/'.$transactionId;

                    $userDetails = $this->userTable()->getFields(array('user_id' => $userId), array('email', 'mobile', 'mobile_country_code','role','user_name'));
                    $mobile = $userDetails['mobile'];
                    //$mobileCountry = $userDetails['mobile_country_code'];
                    $userType = $userDetails['role'];
                    $email = $userDetails['email'];

                    $paymentRequest = $this->paymentTable()->addPayment(array('payment_request_id' => $response['id'], 'currency'=>$response['currency'],   'payment_response_id' => '', 'booking_id' => $bookingId, 'status' => \Admin\Model\Payments::payment_in_process));

                    $details=array('keyId'=>\Application\Handler\Razorpay::keyId,'razorpayOrderId' => $transactionId, 'userDetails' => array('email' => $email, 'mobile' => $mobile), 'url' => $this->getBaseUrl());
                }
                else
                {
                    return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
                }
            }
            else{
                $this->sendEmail($bookingId,$userId);
            }
             return  new JsonModel(array('success'=>true,'message'=>'Booked Successfully','booking_id'=>$saveBooking['id'],'payment_details'=>$details,'transaction_id'=>$transactionId,'url'=>$url));
         }
         return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }

     public function subscriptionCheckOutAction()
     {
         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $userId = $request['user_id'];
         $userType = $request['user_type'];
         $vc = $request['vc'];
         //$tourType = $request['tour_type'];

         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         /* if(!$this->isApkVersionlatest($vc)){
            return  new JsonModel(array('success'=>false,'message'=>'Please update your app to latest version available on playstore'));
         } */
         if($userId=="")
         {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name','role','mobile','mobile_country_code','email','discount_percentage', 'discount_end_date', 'subscription_end_date'));
         $userType = $userDetails['role'];
           if(!count($userDetails))
           {
               return  new JsonModel(array('success'=>false,'message'=>'Invalid Access','error'=>6));
           }
           /* if($userType != \Admin\Model\User::Individual_role){
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
           } */
         
         //$subscriptionDetails = $this->pricingTable()->getFields(array('usertype'=>\Admin\Model\User::Individual_role, 'active'=>'1'),array('price','no_of_comp_pwds','subscription_validity'));
         $subscriptionDetails = $this->getEffectivePricingDetails($userId);
         $price = round($subscriptionDetails["price"] * (1+($subscriptionDetails['GST']/100)), 2);
         $noOfCPs=$subscriptionDetails["no_of_comp_pwds"];
         $noOfUsers = 1;
         $noOfDays=$subscriptionDetails["subscription_validity"];
         $tourType = \Admin\Model\PlacePrices::tour_type_All_tour;
         $saveData=array('tour_type'=>$tourType,'user_type'=>$userType,'user_id'=>$userId,'status'=>1,'booking_type'=>\Admin\Model\Bookings::booking_Subscription,'payment_status'=>\Admin\Model\Payments::payment_in_process);
         $tourDate=date("Y-m-d");
         $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . " +  $noOfDays days"));
         
        /*  if($userDetails['subscription_end_date'] == "0000-00-00 00:00:00"){
            $tourDate=date("Y-m-d");
            $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . " +  $noOfDays days"));
         }else{
         $tourDate=$userDetails['subscription_end_date'];
         $bookingEndDate = date('Y-m-d', strtotime($tourDate . " +  $noOfDays days"));
        } */
        //echo "\n" . $tourDate . " ; " . $bookingEndDate; exit;
         $bookingTourDetails=array(
             'place_ids'=>"",
             'tour_date'=>$tourDate,
             'expiry_date'=>$bookingEndDate,
             'no_of_days'=>$noOfDays,
             'no_of_users'=>$noOfUsers,
             'price'=>$price,
             'status'=>1
         );

         $passwordsData=array();
         $aes=new Aes();
         $bookingTourDetails['price']=$price;
         $applyDiscount=$subscriptionDetails['applied_rdp'];

         /* if($user['role']==\Admin\Model\User::Sponsor_role && $userDetails['apply_discount']=="1")
         {
            $discEndDate = date('Y-m-d',strtotime($userDetails['discount_end_date']));
            if($discEndDate >= date("Y-m-d"))
            {
                $applyDiscount=intval($userDetails['discount_percentage']);
            }
         } */
        
         $userName=$userDetails['user_name'];
         $email=$userDetails['email'];

         $discountPrice=$price;
         if($applyDiscount > 0)
         {
             //$discountPrice=$price-($price*$applyDiscount)/100;
             $bookingTourDetails['discount_percentage']=$applyDiscount; //$discountPrice;
         }else
         {
             $bookingTourDetails['discount_percentage']=0;
         }

         if($discountPrice==0)
         {
             $saveData['payment_status']=\Admin\Model\Payments::payment_success;
         }

         if(!$email)
         {
             return  new JsonModel(array('success'=>false,'message'=>'Please Add Email to proceed Booking'));
         }

         if($discountPrice==0 || round($discountPrice)==0)
         {
             $saveData['payment_status']=1;
         }

         $saveBooking=$this->bookingsTable()->addBooking($saveData);

         if($saveBooking['success'])
         {
             $bookingTourDetails['booking_id']=$saveBooking['id'];
             $bookingId=$saveBooking['id'];
               
             $saveBookingTourDetails=$this->bookingTourDetailsTable()->addBooking($bookingTourDetails);

             for($i=0;$i<$noOfCPs;$i++)
             {
                 $password = $this->random_password();
                 $encodeContent = $aes->encrypt($password);
                 $encryptPassword = $encodeContent['password'];
                 $hash = $encodeContent['hash'];
                 $passwordsData[] = array('user_id' => 0, 'hash' => $hash, 'password' => $encryptPassword, 'status' => 1,"booking_id"=>$bookingId,
                     'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"), 'password_expiry_date'=>$bookingEndDate, 'password_type'=>\Admin\Model\Password::passowrd_type_booking);
                 $bookingPassword = array($password.$bookingId);
             }

             $this->passwordTable()->addMutiplePasswords($passwordsData);
             $transactionId='';
             $url='';
             $details=array();
            
             if($discountPrice!=0 && round($discountPrice)!=0)
             {
                 $api = new Razorpay();
                 if($userDetails['mobile_country_code'] == "91"){
                    $orderData = [
                        'receipt'         => $bookingId,
                        'amount'          => $price * 100,
                        'currency'        => 'INR',
                        'payment_capture' => 1 
                    ];
                 }
                 else{
                    $orderData = [
                        'receipt'         => $bookingId,
                        'amount'          => $price * 100,
                        'currency'        => 'USD',
                        'payment_capture' => 1 
                    ];
                 }
                 
                 $response = $api->paymentRequestCreate($orderData); 
                if($response['id']){
                    $transactionId=$response['id'];
                    $url = $this->getBaseUrl().'/payment-process/'.$transactionId;

                    $userDetails = $this->userTable()->getFields(array('user_id' => $userId), array('email', 'mobile', 'mobile_country_code','role','user_name'));
                    $mobile = $userDetails['mobile'];
                    $mobileCountry = $userDetails['mobile_country_code'];
                    $userType = $userDetails['role'];
                    $email = $userDetails['email'];

                    $paymentRequest = $this->paymentTable()->addPayment(array('payment_request_id' => $response['id'], 'currency'=>$response['currency'],   'payment_response_id' => '', 'booking_id' => $bookingId, 'status' => \Admin\Model\Payments::payment_in_process));

                    $details=array('keyId'=>\Application\Handler\Razorpay::keyId,'razorpayOrderId' => $transactionId, 'userDetails' => array('email' => $email, 'mobile' => $mobile, 'role'=>$userType), 'url' => $this->getBaseUrl());
                }
                else
                {
                    return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
                }
            }
            else{
                $this->sendEmail($bookingId,$userId);
            }
             return  new JsonModel(array('success'=>true,'message'=>'Booked Successfully','booking_id'=>$saveBooking['id'],'payment_details'=>$details,'transaction_id'=>$transactionId,'url'=>$url));
         }
         return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }

     public function bookingCheckOutAction()
     {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $files = $this->getRequest()->getFiles();
        $userId = $request['user_id'];
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($userId=="")
        {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $placeIds=$request['places'];
        $tourType=$request['tour_type'];
        /*   - removed by Manjary for STT-SV - start
        $noOfDays=$request['no_of_days'];
        $noOfUsers=$request['no_of_users']; 
          - removed by Manjary for STT-SV  -end */
         $userType=$request['user_type'];
         $price=$request['price'];
         $uploadFileDetails=array();
         if($placeIds=="" || is_null($placeIds) || empty($placeIds))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access','error'=>1));
         }

         if($tourType=="" || is_null($tourType) || empty($tourType))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access','error'=>2));
         }
         /*   - removed by Manjary for STT-SV - start
         if($noOfDays=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access','error'=>3));
         }
         if($noOfUsers=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access','error'=>4));
         }   
         if($price=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access','error'=>5));
         }  - removed by Manjary for STT-SV  - end */

         $validImageFiles=array('png','jpg','jpeg');
        // $pricingDetails = $this->pricingTable()->getPricingDetails(array('active'=>'1'));
         $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name','role','mobile','email','discount_percentage', 'discount_end_date', 'subscription_end_date'));
           if(!count($userDetails))
           {
               return  new JsonModel(array('success'=>false,'message'=>'Invalid Access','error'=>6));
           }

         $placeIds=json_decode($placeIds,true);
         $noOfPlaces=count($placeIds);
         $placeIds=implode(",",$placeIds);

         //$subscriptionDetails = $this->pricingTable()->getFields(array('usertype'=>\Admin\Model\User::Individual_role, 'active'=>'1'),array('price','no_of_comp_pwds','no_of_days','subscription_validity'));
         $subscriptionDetails = $this->getEffectivePricingDetails($userId);
         $price = $subscriptionDetails["price"] * (1+($subscriptionDetails['GST']/100));
         $noOfCPs=$subscriptionDetails["no_of_comp_pwds"];
         $noOfUsers=1;
         $noOfDays=$subscriptionDetails["subscription_validity"];

         $saveData=array('tour_type'=>$tourType,'user_type'=>$userType,'user_id'=>$userId,'status'=>1,'booking_type'=>\Admin\Model\Bookings::booking_Subscription,'payment_status'=>\Admin\Model\Payments::payment_in_process);
         $tourDate=date("Y-m-d");
         $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . " +  $noOfDays days"));
         /* if($userDetails['subscription_end_date'] == "0000-00-00 00:00:00"){
            $tourDate=date("Y-m-d");
            $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . " +  $noOfDays days"));
         }else{
            $tourDate=$userDetails['subscription_end_date'];
            $bookingEndDate = date('Y-m-d', strtotime($tourDate . " +  $noOfDays days"));
         } */
        
         $bookingTourDetails=array(
             'place_ids'=>$placeIds,
             'tour_date'=>$tourDate,
             'expiry_date'=>$bookingEndDate,
             'no_of_days'=>$noOfDays,
             'no_of_users'=>$noOfUsers,
             'price'=>$price,
             'status'=>1
         );

         $passwordsData=array();
         $aes=new Aes();

          /*   - removed by Manjary for STT-SV - start
            $priceDetails=$this->priceSlabTable()->priceDetails($tourType);

              if(count($priceDetails))
              {
                  $price=$priceDetails[$noOfDays];
              }

           $price=$price*$noOfPlaces*$noOfUsers;
             - removed by Manjary for STT-SV - end  */
         $bookingTourDetails['price']=$price;
         $applyDiscount=0;

         /* if($userType==\Admin\Model\User::Subscriber_role)
         {  // change the code accordingly to apply subscriber discounts and other info - start
             $tourOperatorDetails=$this->userTable()->getUserDetails($userId);
             if($tourOperatorDetails)
             {
                 if($tourOperatorDetails['apply_discount'])
                 {
                     if($tourOperatorDetails['discount_end_date']>date("Y-m-d"))
                     {
                         $applyDiscount=intval($tourOperatorDetails['discount_percentage']);
                     }
                 }
             }
             $userName = $tourOperatorDetails['contact_person'];
             $email = $tourOperatorDetails['email_id'];
             // change the code accordingly to apply subscriber discounts and other info - end
         }
         else
         {
             $userName=$userDetails['user_name'];
             $email=$userDetails['email'];
         } */
         if($userDetails['role']==\Admin\Model\User::Sponsor_role) // && $userDetails['apply_discount']=="1")
         {
            $discEndDate = date('Y-m-d',strtotime($userDetails['discount_end_date']));
            if($discEndDate >= date("Y-m-d"))
            {
                $applyDiscount=intval($userDetails['discount_percentage']);
            }
         }
        
         $userName=$userDetails['user_name'];
         $email=$userDetails['email'];

         $discountPrice=$price;
         if($applyDiscount!=0)
         {
             $discountPrice=$price-($price*$applyDiscount)/100;
             $bookingTourDetails['discount_percentage']=$discountPrice;
         }else
         {
             $bookingTourDetails['discount_percentage']=0;
         }

         if($discountPrice==0)
         {
             $saveData['payment_status']=\Admin\Model\Payments::payment_success;
         }

         if(!$email)
         {
             return  new JsonModel(array('success'=>false,'message'=>'Please Add Email to proceed Booking'));
         }

         if($discountPrice==0 || round($discountPrice)==0)
         {
             $saveData['payment_status']=\Admin\Model\Payments::payment_success;
         }

         $saveBooking=$this->bookingsTable()->addBooking($saveData);

         if($saveBooking['success'])
         {
             $bookingTourDetails['booking_id']=$saveBooking['id'];
             $bookingId=$saveBooking['id'];
                /* if(count($uploadFileDetails))   -- can be removed at later stage if not needed
                {
                    $uploadFileDetails['file_data_id']=$saveBooking['id'];
                    $this->tourismFilesTable()->addTourismFiles($uploadFileDetails);
                } */
             $saveBookingTourDetails=$this->bookingTourDetailsTable()->addBooking($bookingTourDetails);

             for($i=0;$i<$noOfCPs;$i++)
             {
                 $password = $this->random_password();
                 $encodeContent = $aes->encrypt($password);
                 $encryptPassword = $encodeContent['password'];
                 $hash = $encodeContent['hash'];
                 $passwordsData[] = array('user_id' => 0, 'hash' => $hash, 'password' => $encryptPassword, 'status' => 1,"booking_id"=>$bookingId,
                     'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"),'password_type'=>\Admin\Model\Password::passowrd_type_booking);
                 $bookingPassword = array($password.$bookingId);
             }

             $this->passwordTable()->addMutiplePasswords($passwordsData);
             $transactionId='';
             $url='';
             $details=array();
            
             if($discountPrice!=0 && round($discountPrice)!=0)
             {
                 $api = new Razorpay();
                 $orderData = [
                     'receipt'         => $bookingId,
                     'amount'          => $price * 100, //$discountPrice * 100, // 2000 rupees in paise
                     'currency'        => 'INR',
                     'payment_capture' => 1 // auto capture
                 ];
                 $response = $api->paymentRequestCreate($orderData); 
                /*
                $subscriptionData = [
                    'plan_id' => 'plan_EkXdT5TI6eRV7W',
                    'customer_notify' => 1,
                    'total_count' => 1
                ];
                 $response  = $api->subscriptionRequestCreate($subscriptionData);*/
                 $transactionId=$response['id'];
                 $url = $this->getBaseUrl().'/payment-process/'.$transactionId;

                 $userDetails = $this->userTable()->getFields(array('user_id' => $userId), array('email', 'mobile', 'mobile_country_code','role','user_name'));
                 $mobile = $userDetails['mobile'];
                 $mobileCountry = $userDetails['mobile_country_code'];
                 $userType = $userDetails['role'];
                 $email = $userDetails['email'];

                 $paymentRequest = $this->paymentTable()->addPayment(array('payment_request_id' => $response['id'], 'currency'=>$response['currency'],   'payment_response_id' => '', 'booking_id' => $bookingId, 'status' => \Admin\Model\Payments::payment_in_process));

                 $details=array('keyId'=>\Application\Handler\Razorpay::keyId,'razorpayOrderId' => $transactionId, 'userDetails' => array('email' => $email, 'mobile' => $mobile), 'url' => $this->getBaseUrl());
            }
            else{
                $this->sendEmail($bookingId,$userId);
            }
             return  new JsonModel(array('success'=>true,'message'=>'Booked Successfully','booking_id'=>$saveBooking['id'],'payment_details'=>$details,'transaction_id'=>$transactionId,'url'=>$url));
         }
         return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }
     public function seasonalSpecialBookingAction()
     {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $files = $this->getRequest()->getFiles();
        $userId = $request['user_id'];

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($userId=="")
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $tourDate=date("y-m-d");
        $noOfUsers=1; 
        $packageId=$request['package_id'];

        if($packageId=="")
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name','mobile','email','role'));
         if(!count($userDetails))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));

         }
         $packageDetails=$this->seasonalSpecialsTable()->getFields(array('seasonal_special_id'=>$packageId),array('place_ids','no_of_days','start_date','end_date'));

        if(!count($packageDetails))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $userType = $userDetails['role'];
        $tourType=\Admin\Model\PlacePrices::tour_type_Seasonal_special;
        $placeIds=$packageDetails['place_ids'];
        $pricingDetails = $this->getEffectivePricingDetails($userId);        
        $noOfDays = $pricingDetails['no_of_days'];
        $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . " +  $noOfDays days"));
        $saveData=array(
            'tour_type'=>$tourType,
            'user_type'=>$userType,
            'user_id'=>$userId,
            'payment_status'=>\Admin\Model\Payments::payment_in_process,
            'status'=>1,
            'booking_type' => \Admin\Model\Bookings::booking_by_User
        );
        $bookingTourDetails=array('place_ids'=>$placeIds,'tour_date'=>$tourDate,'expiry_date'=>$bookingEndDate,
            'no_of_days'=>$noOfDays,'no_of_users'=>$noOfUsers,'status'=>1,'package_id'=>$packageId);
        $userName=$userDetails['user_name'];
        $email=$userDetails['email'];
        $saveData['payment_status']=\Admin\Model\Payments::payment_success;

         $saveBooking=$this->bookingsTable()->addBooking($saveData);
         if($saveBooking['success'])
         {
            $bookingId=$saveBooking['id'];
            $bookingTourDetails['booking_id']=$saveBooking['id'];
            $saveBookingTourDetails=$this->bookingTourDetailsTable()->addBooking($bookingTourDetails);
            $bookingDetailsLaguages =$this->bookingTourDetailsTable()->getLanguages($bookingId);
            return new JsonModel(array('success'=>true,'message'=>'booked successfully','booking_id'=>$bookingId,'languages'=>$bookingDetailsLaguages));    
            //return  new JsonModel(array('success'=>true,'message'=>'Booked Successfully','booking_id'=>$saveBooking['id'],'payment_details'=>array(),'transaction_id'=>"",'url'=>""));
         }

        return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }

     public function seasonalSpecialBookingAction_old()
     {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $files = $this->getRequest()->getFiles();
        $userId = $request['user_id'];

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($userId=="")
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $tourDate=date("y-m-d");
        $bookingEndDate=$request['booking_end_date'];
        $noOfUsers=$request['no_of_users'];
        $userType=$request['user_type'];

        $packageId=$request['package_id'];



        if($noOfUsers=="")
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }




        if($packageId=="")
           {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }

         $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name','mobile','email'));
         if(!count($userDetails))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));

         }

        $validImageFiles=array('png','jpg','jpeg');
        $uploadFileDetails=array();
        if(isset($files['itinerary_file']))
        {
            $attachment=$files['itinerary_file'];
                $imagesPath = '';
                $pdf_to_image = "";
                $filename = $attachment['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";

                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));

                $filename = $filenameWithoutExt ;

                $filePath = "data/images";

                @mkdir(getcwd() . "/public/" . $filePath,0777,true);

                $filePath = $filePath . "/" . $filename.".".$ext;

                if (!in_array(strtolower($ext), $validImageFiles))
                {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }

                /* if (move_uploaded_file($attachment['tmp_name'], getcwd() . "/public/" . $filePath))
                 {
                     $imagesPath = $filePath;
                     $localImagePath = getcwd() . "/public/" . $filePath;
                     chmod(getcwd() . "/public/" . $filePath, 0777);
                 }*/

                $this->pushFiles($filePath,$attachment['tmp_name'],$attachment['type']);

                /*$fp = fopen(getcwd() . "/public/" . $filePath, 'w');
                $encodeContent=$aes->encrypt(file_get_contents($attachment['tmp_name']));
                $encodeString=$encodeContent['password'];
                $hash=$encodeContent['hash'];
                fwrite($fp,$encodeString);
                fclose($fp);*/

                $uploadFileDetails = array(
                    'file_path' => $filePath,
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_itinerary_files,
                    'file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $ext,
                    'status'=>1,
                    'duration'=>0 ,
                    'hash'=>'',
                    'file_language_type' => 0,
                    'file_name' => $attachment['name']
                );
            }

         $packageDetails=$this->seasonalSpecialsTable()->getFields(array('seasonal_special_id'=>$packageId),array('place_ids','no_of_days','start_date','end_date','price','discount_price'));

           if(!count($packageDetails))
           {
               return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
           }
           $tourType=\Admin\Model\PlacePrices::tour_type_Seasonal_special;
           $price=$packageDetails['discount_price'];
        $placeIds=$packageDetails['place_ids'];
        $bookingEndDate=$packageDetails['end_date'];
        $datediff = strtotime($bookingEndDate) - strtotime($tourDate);

       // $noOfDays =round($datediff / (60 * 60 * 24));
         $noOfDays=$packageDetails['no_of_days'];

         $saveData=array(
            'tour_type'=>$tourType,
            'user_type'=>$userType,
            'user_id'=>$userId,
            'payment_status'=>0,
            'status'=>1,
            'booking_type' => \Admin\Model\Bookings::booking_by_User
        );
         $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . ' +  30 days'));

        $bookingTourDetails=array('place_ids'=>$placeIds,'tour_date'=>$tourDate,'expiry_date'=>$bookingEndDate,
            'no_of_days'=>$noOfDays,'no_of_users'=>$noOfUsers,'price'=>$price,'status'=>1,'package_id'=>$packageId);
        $userIds=array();



        $aes=new Aes();

        $price=$price*$noOfUsers;
        $bookingTourDetails['price']=$price;
        $userIds=implode(',',$userIds);
        $bookingTourDetails['sponsered_users']="";
         $applyDiscount=0;
         if($userType==\Admin\Model\User::Tour_Operator_role)
         {
             $tourOperatorDetails=$this->userTable()->getUserDetails($userId);
             if($tourOperatorDetails)
             {
                 if($tourOperatorDetails['apply_discount'])
                 {
                     if($tourOperatorDetails['discount_end_date']>date("Y-m-d"))
                     {
                         $applyDiscount=intval($tourOperatorDetails['discount_percentage']);
                     }
                 }
             }
             $userName = $tourOperatorDetails['contact_person'];
             $email = $tourOperatorDetails['email_id'];

         }else if($userType==\Admin\Model\User::Tour_coordinator_role)
         {
             $tourCoordinatorDetails=$this->userTable()->getUserDetails($userId);
             if($tourCoordinatorDetails)
             {
                 if($tourCoordinatorDetails['apply_discount'])
                 {
                     if($tourCoordinatorDetails['discount_end_date']>date("Y-m-d"))
                     {
                         $applyDiscount=intval($tourCoordinatorDetails['discount_percentage']);
                     }
                 }
             }
             $userName=$tourCoordinatorDetails['user_name'];
             $email=$tourCoordinatorDetails['email'];
         }else
         {
             $userName=$userDetails['user_name'];
             $email=$userDetails['email'];
         }
         $discountPrice=$price;
         if($applyDiscount!=0)
         {
             $bookingTourDetails['discount_percentage']=$applyDiscount;
             $discountPrice=$price-($price*$applyDiscount)/100;
         }else{
             $bookingTourDetails['discount_percentage']=0;
         }
         if($discountPrice==0)
         {
             $saveData['payment_status']=1;
         }
        $saveBooking=$this->bookingsTable()->addBooking($saveData);


        if($saveBooking['success'])
        {
            $bookingId=$saveBooking['id'];
            $bookingTourDetails['booking_id']=$saveBooking['id'];
            if(count($uploadFileDetails))
            {
                $uploadFileDetails['file_data_id']=$saveBooking['id'];
                $this->tourismFilesTable()->addTourismFiles($uploadFileDetails);
            }
            $saveBookingTourDetails=$this->bookingTourDetailsTable()->addBooking($bookingTourDetails);
            for($i=0;$i<$noOfUsers;$i++)
            {
                $password = $this->random_password();
                $encodeContent = $aes->encrypt($password);
                $encryptPassword = $encodeContent['password'];
                $hash = $encodeContent['hash'];
                $passwordsData[] = array('user_id' => 0, 'hash' => $hash, 'password' => $encryptPassword, 'status' => 1,"booking_id"=>$bookingId,
                    'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"),'password_type'=>\Admin\Model\Password::passowrd_type_booking);
                $bookingPassword = array($password.$bookingId);
            }


            $this->passwordTable()->addMutiplePasswords($passwordsData);


            $transactionId='';
            $url='';
            $details=array();
            if($discountPrice!=0) {
                $api = new Razorpay();
                $orderData = [
                    'receipt'         => $bookingId,
                    'amount'          => $discountPrice * 100, // 2000 rupees in paise
                    'currency'        => 'INR',
                    'payment_capture' => 1 // auto capture
                ];

                $response = $api->paymentRequestCreate($orderData);

                // $bookingList=$this->bookingsTable()->bookingsDetails($bookingId);
                $transactionId=$response['id'];
                $url = $this->getBaseUrl().'/payment-process/'.$transactionId;

                $userDetails = $this->userTable()->getFields(array('user_id' => $userId), array('email', 'mobile', 'mobile_country_code','role'));
                $mobile = $userDetails['mobile'];
                $mobileCountry = $userDetails['mobile_country_code'];
                $userType = $userDetails['role'];
                if($userType==\Admin\Model\User::Tour_Operator_role)
                {
                    $tourOperatorDetails=$this->userTable()->getUserDetails($userId);
                    $userName = $tourOperatorDetails['contact_person'];
                    $email = $tourOperatorDetails['email_id'];
                }else if($userType==\Admin\Model\User::Tour_coordinator_role)
                {
                    $tourCoordinatorDetails=$this->userTable()->getUserDetails($userId);
                    $userName=$tourCoordinatorDetails['user_name'];
                    $email=$tourCoordinatorDetails['email'];
                }else
                {
                    $userName = $userDetails['user_name'];
                    $email = $userDetails['email'];
                }
                $paymentRequest = $this->paymentTable()->addPayment(array('payment_request_id' => $response['id'], 'currency'=>$response['currency'],   'payment_response_id' => '', 'booking_id' => $bookingId, 'status' => \Admin\Model\Payments::payment_in_process));

                $details=array('keyId'=>\Application\Handler\Razorpay::keyId,'razorpayOrderId' => $transactionId, 'userDetails' => array('email' => $email, 'mobile' => $mobile), 'url' => $this->getBaseUrl());

            }else{
                $this->sendEmail($bookingId,$userId);
            }
            return  new JsonModel(array('success'=>true,'message'=>'Booked Successfully','booking_id'=>$saveBooking['id'],'payment_details'=>$details,'transaction_id'=>$transactionId,'url'=>$url));

        }

        return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
     }

    public function updateBookingUsersAction()
     {
         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $userId = $request['user_id'];
         $bookingId =$request['booking_id'];
         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($userId=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($bookingId=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         $usersList=json_decode($request['users_list'],true);

         $aes=new Aes();
         $counter=0;

         $bookingDetails=$this->bookingTourDetailsTable()->bookingDetails(array('booking_id'=>$bookingId,'user_id'=>$userId));

            if(!count($bookingDetails))
            {
                return  new JsonModel(array('success'=>false,'message'=>'Invalid Booking'));
            }

         $sponseredUsers=array_values(array_filter(explode(",",$bookingDetails['sponsered_users'])));
         $userName  =$bookingDetails['user_name'];
         $tourType=$bookingDetails['tour_type'];
         $tourDate=$bookingDetails['tour_date'];
         $sendSms=array();
         $userIds=array();
         $passwordsData=array();
         $notificationDetails=array();
         $bookingUserId=$userId;

         foreach ($usersList as $user)
         {
             $mobile=$user['mobile'];
             $mobileCountryCode=$user['mobile_country_code'];
             $userId='';
             if(trim($mobile) && trim($mobileCountryCode))
             {
                 $checkUser=$this->userTable()->getField(array('mobile_country_code'=>$mobileCountryCode,'mobile'=>$mobile),'user_id');
                 if(!in_array($checkUser,$sponseredUsers) || !$checkUser)
                 {
                     if ($checkUser)
                     {

                         $userId = $checkUser;
                         $userIds[] = $checkUser;

                     }else
                     {
                         $saveUser = $this->userTable()->newUser(array('mobile' => $mobile, 'mobile_country_code' => $mobileCountryCode, 'role' => \Admin\Model\User::Sponsor_role, 'status' => 1));
                         if ($saveUser['success'])
                         {
                             $userId = $saveUser['user_id'];
                             $userIds[] = $saveUser['user_id'];
                         }else
                         {
                             return new JsonModel(array("success" => false, "message" => "Something Went wrong"));
                         }
                     }

                     $password = $this->random_password();
                     $encodeContent = $aes->encrypt($password);
                     $encryptPassword = $encodeContent['password'];
                     $hash = $encodeContent['hash'];
                     $passwordsData[] = array('user_id' => $userId,'booking_id'=>$bookingId ,'hash' => $hash, 'password' => $encryptPassword, 'status' => 1,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));

                     if($userId==$bookingUserId)
                     {
                         $message=sprintf('Congrats, You %s is booked with the activation date of %s . You will receive password on that date.', \Admin\Model\PlacePrices::tour_type[$tourType],$tourDate);
                     }else
                     {
                         $message=sprintf('Congrats, You have been sponsored for the %s by %s with the activation date of %s . You will receive password on that date.', \Admin\Model\PlacePrices::tour_type[$tourType] ,$userName,$tourDate);
                     }

                     $notificationDetails[$counter] = array('notification_data_id'=>$bookingId ,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                     $sendSms[]=array('mobile'=>$mobileCountryCode.$mobile,'message'=>$message);
                     $counter++;
                 }
             }
         }

            $users=array_values(array_unique(array_merge($sponseredUsers,$userIds)));
             $user=implode(",",$users);

             $updateBooking=$this->bookingTourDetailsTable()->updateBooking(array('sponsered_users'=>",".$user.","),array('booking_id'=>$bookingId));

               if($updateBooking)
               {

                   if(count($passwordsData)){
                       $this->passwordTable()->addMutiplePasswords($passwordsData);
                   }
                   if(count($notificationDetails))
                   {
                       $notificationCounter=-1;
                       foreach ($notificationDetails as $details) {
                           $notificationCounter++;
                           $registrationIds = $this->fcmTable()->getDeviceIds($notificationDetails[$notificationCounter]['notification_recevier_id']);
                           if ($registrationIds)
                           {
                               $notification = new SendFcmNotification();
                               $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails[$notificationCounter]['notification_text'], 'title' => 'sponsored for the ' . \Admin\Model\PlacePrices::tour_type[$tourType], 'id' => $notificationDetails[$notificationCounter]['notification_data_id'], 'type' => $notificationDetails[$notificationCounter]['notification_type']));
                           }
                       }
                    $notification=   $this->notificationTable()->addMutipleData($notificationDetails);

                   }
                   if(count($sendSms))
                   {
                       foreach ($sendSms as $sms)
                       {
                           $this->sendBookingSms($sms['mobile'],array('text'=>$sms['message']));
                       }
                   }
                   return  new JsonModel(array('success'=>true,'message'=>'updated Successfully'));

               }else{
                   return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));

               }
     }
       public function bookingsListAction()
       {
           $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
           $request = $this->getRequest()->getPost();
           $userId = $request['user_id'];
           if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
           {
               return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
           }
           if($userId=="")
           {
               return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
           }
           $type=$request['type'];
              if(is_null($type))
              {
                  $type=0;
              }
           $limit=$request['limit'];
           $offset=$request['offset'];
           
           if(is_null($limit))
           {
               $limit=10;
           }else{
                  $limit=intval($limit);
           }

           if(is_null($offset))
           {
               $offset=0;
           }else{
               $offset=intval($offset);
           }
           $bookingList=$this->bookingsTable()->bookingsList(array('limit'=>$limit,'offset'=>$offset,'user_id'=>$userId,'type'=>$type));
           return  new JsonModel(array('success'=>true,'bookings'=>$bookingList));
       }

      public function priceSlabDaysDetailsAction()
      {
          $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
          $request = $this->getRequest()->getPost();
          $userId = $request['user_id'];
          $tourType=$request['tour_type'];

          if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
          {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }

          if($tourType=="")
          {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }

          if($userId=="")
          {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }

          $priceDetails=$this->priceSlabTable()->priceDetails($tourType);

              $price=array();

             foreach ($priceDetails as $key=>$value)
             {
                 $price[]=array('day'=>$key,'price'=>floatval($value));
             }

           return new JsonModel(array('success'=>true,'price_slab_days'=>$price));
      }
      public function notRenewedAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        if($userId=="")
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        /* $userRole=$this->userTable()->getField(array('user_id'=>$userId),'role');
        if($userRole == \Admin\Model\User::Sponsor_role){
            $inavlidatePwdResult = $this->passwordTable()->invalidatePasswords($userId);
        }
        if($inavlidatePwdResult){ */
        $userRole=$this->userTable()->getField(array('user_id'=>$userId),'role');
        if($userRole == \Admin\Model\User::Sponsor_role){
            $updated = $this->userTable()->updateUser(array("role"=>\Admin\Model\User::Individual_role, "discount_percentage"=>30),array("user_id"=>$userId));
        }else{
            $updated = $this->userTable()->updateUser(array("role"=>\Admin\Model\User::Individual_role),array("user_id"=>$userId));
        }
        
        if($updated){
            $checkUser=$this->userTable()->getUserByUserId($userId);
            $photoFilePath = $this->sponsorPhotoTable()->getField(array('file_data_id'=>$userId),'file_path');
            $refDetails = $this->referTable()->getRefers(array('user_id'=>$userId));
            $user=array(
                'name'=>$checkUser['user_name'],
                'email'=>$checkUser['email'],
                'id'=>$checkUser['user_id'],
                'type'=>intval($checkUser['role']),
                'mobile'=>$checkUser['mobile'],
                'mobile_country_code'=>$checkUser['mobile_country_code'],
                'user_type'=>$checkUser['role'],
                'role'=>$checkUser['role'],
                'resState'=>$checkUser['res_state'],
                'address'=>$checkUser['address'],
                'ref_name'=>$refDetails[0]['ref_by'],
                'ref_mobile'=>$refDetails[0]['ref_mobile'],
                'sponsor_type'=>$checkUser['sponsor_type'],
                'subscription_end_date'=>$checkUser['subscription_end_date'],
                'image_path'=>$photoFilePath,
                'non_renewal_handled'=>true,
                'is_promoter'=>$checkUser['is_promoter']
            ); 
            return new JsonModel(array('success'=>true,'message'=>'user downgraded','user'=>$user));
        }else
        return new JsonModel(array('success'=>false,'message'=>'Something went wrong'));
      }

      public function passwordsListAction()
      {
          $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
          $request = $this->getRequest()->getPost();
          $userId = $request['user_id'];
          if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
          {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }
          if($userId=="")
          {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }
          $userRole=$this->userTable()->getField(array('user_id'=>$userId),'role');
          if($userRole != \Admin\Model\User::Sponsor_role){
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
           }
           $limit=$request['limit'];
           $offset=$request['offset'];
           
           if(is_null($limit))
           {
               $limit=10;
           }else{
               $limit=intval($limit);
           }

           if(is_null($offset))
           {
               $offset=0;
           }else{
               $offset=intval($offset);
           }

          $redeemed=$request['redeemed'];
          if($redeemed!="0" && $redeemed!="1")
          {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }
          $data['user_id'] = $userId;
          $data['today'] = date('Y-m-d');
          $data['offset'] = $offset;
          $data['limit'] = $limit;
          $passwordList=$this->passwordTable()->getPasswordDetails($data, $redeemed);
          return  new JsonModel(array('success'=>true,'passwords'=>$passwordList));
       }
    
    public function passwordSoldAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
          $request = $this->getRequest()->getPost();
          $userId = $request['user_id'];
          if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
          {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }
          if($userId=="")
          {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }
          $userRole=$this->userTable()->getField(array('user_id'=>$userId),'role');
          if($userRole != \Admin\Model\User::Sponsor_role){
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
           }

          $id=$request['password_id'];
          if($id == "0")
          {
              return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }
          if($request['sold'] == "1")
            $sold = 1;
          else
            $sold = 0;
          $update=$this->passwordTable()->updatePassword(array('sold'=>$sold), array('id'=>$id));
          if($update)
            return  new JsonModel(array('success'=>true,'message'=>"Successfully updated"));
          else
          return  new JsonModel(array('success'=>false,'message'=>'Not Updated'));
    }

    public function bookingDetailsAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        if($userId=="")
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $bookingId=$request['booking_id'];
        if($bookingId=="")
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $bookingList=$this->bookingsTable()->bookingsDetails($bookingId);
        $bookingList['languages'] =$this->bookingTourDetailsTable()->getLanguages($bookingId);

        return  new JsonModel(array('success'=>true,'booking_details'=>$bookingList));

     }

    public function spiritualTourBookingAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];
        $placeIds = $request['places'];
        $userType = $request['user_type'];
        $tourType = $request['tour_type'];
        if (!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success' => false, 'message' => 'Invalid Access'));
        }

        if ($userId == "")
        {
            return new JsonModel(array('success' => false, 'message' => 'Invalid Access'));
        }

        if ($placeIds == ""  || !isset($request['places']))
        {
            return new JsonModel(array('success' => false,'message' => 'Invalid Access'));
        }

        /* if ($userType == "")
        {
            $userType = "1";
        } */

        if ($tourType == "")
        {
            $tourType = \Admin\Model\PlacePrices::tour_type_Spiritual_tour;
        }
        
        $subscriptionDetails = $this->getEffectivePricingDetails($userId);
        $noOfDays=$subscriptionDetails["no_of_days"];
        $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . " +  $noOfDays days"));
        //$newDldCount = count($placeIds);
        $placeIds = json_decode($placeIds, true);
        $placeIds=implode(",",$placeIds);
        $userDetails = $this->userTable()->getFields(array('user_id' => $userId), array('role','downloads_count'));
        /*$oldDldCount = $userDetails['downloads_count'];
        $totalDlds = $newDldCount + $oldDldCount;
        if($subscriptionDetails['maxdownloads'] < $totalDlds){
            return new JsonModel(array('success' => false,'message' => 'Downloads exceeded'));
        } */
        $userType = $userDetails['role'];
        $saveData = array(
            'tour_type' => $tourType, // \Admin\Model\PlacePrices::tour_type_Spiritual_tour,  - modified by Manjary
            'user_type' => $userType,  //1, - modified by Manjary
            'user_id' => $userId,
            'status' => \Admin\Model\Bookings::Status_Ongoing,
            'booking_type' => \Admin\Model\Bookings::booking_by_User,
            'payment_status'=>\Admin\Model\Payments::payment_success
        );
        
        $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . ' + ' . $noOfDays . 'days'));
        $bookingTourDetails = array('place_ids' => $placeIds,
            'tour_date' => date("y-m-d"),
            'expiry_date' => $bookingEndDate,
            'no_of_days' => $noOfDays, 'no_of_users' => 1, 'price' => 0, 'status' => 1,'discount_percentage'=>0);

        $saveBooking = $this->bookingsTable()->addBooking($saveData);
        
        if ($saveBooking['success']) {
            $bookingId=$saveBooking['id'];
            $bookingTourDetails['booking_id'] = $saveBooking['id'];
            $saveBookingTourDetails = $this->bookingTourDetailsTable()->addBooking($bookingTourDetails);
            //$updateCount = $this->userTable()->updateUser(array('downloads_count'=>$totalDlds),array('user_id'=>$userId));
            $bookingDetailsLaguages =$this->bookingTourDetailsTable()->getLanguages($bookingId);
           // $bookingId=$saveBookingTourDetails['id'];
            return new JsonModel(array('success'=>true,'message'=>'booked successfully','booking_id'=>$bookingId,'languages'=>$bookingDetailsLaguages));
        }
    }

    public function notificationListAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($userId=="")
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $limit=$request['limit'];
        $offset=$request['offset'];

        if(is_null($limit))
        {
            $limit=10;
        }else
        {
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else
        {
            $offset=intval($offset);
        }

        $bookingList=$this->notificationTable()->getNotifications(array('limit'=>$limit,'offset'=>$offset,'user_id'=>$userId));

        return  new JsonModel(array('success'=>true,'notification_list'=>$bookingList));
    }
     public function checkPasswordAction()
     {

         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $userId = $request['user_id'];

         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($userId=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

        $password = $request['password'];
        $tourType = $request['tour_type'];
        $bookingPassword = substr($password, 0, 8);
        $passwordId= substr($password,8,strlen($password)-7);

        if($passwordId=="" || $bookingPassword== "")
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Password'));
        }

        $response=$this->passwordTable()->checkPasswordForSubscription(array('password_id'=>$passwordId,'password'=>$bookingPassword));

          if($response)
          {

              /* if($response[0]['tour_type']!=$tourType)
              {
                  return  new JsonModel(array('success'=>false,'message'=>'Invalid Password'));
              } */

              if($response[0]['expiry_date']<date("Y-m-d") && $response[0]['expiry_date']!=date("Y-m-d"))
              {
                  return new JsonModel(array('success'=>false,'message'=>'Password Expired'));
              }

              /* if($response[0]['user_id']!=0 && strtotime($response[0]['password_expiry_date'])>0 && ($response[0]['password_expiry_date']<date("Y-m-d") && $response[0]['password_expiry_date']!=date("Y-m-d"))) */
              $seDate = date('Y-m-d',strtotime($response[0]['subscription_end_date']));
              $peDate = date('Y-m-d',strtotime($response[0]['password_expiry_date']));
              if($response[0]['user_id']!=0 && $peDate < $seDate && ($peDate < date("Y-m-d") && $peDate!=date("Y-m-d")))
              {
                  return new JsonModel(array('success'=>false,'message'=>'Password Expired'));
              }
              /* if($response[0]['subscription_count'] != 0){
                return new JsonModel(array('success'=>false,'message'=>'Invalid Action'));
              } */
              
              if($response[0]['user_id']!=0)
              {
                  return new JsonModel(array('success'=>false,'message'=>'Password Already Used By Another User'));
              }
              
              $bookingId = $response[0]['booking_id'];
              $currentDate = date("y-m-d");
              $noOfDays = $response[0]['no_of_days'];
              $expiryDate = date('Y-m-d', strtotime(date("y-m-d") . ' + '.$noOfDays.'  days'));
              $tourType = \Admin\Model\PlacePrices::tour_type_All_tour;
              $userType = \Admin\Model\User::Individual_role;

            //$subscriptionDetails = $this->pricingTable()->getFields(array('usertype'=>$userType, 'active'=>'1'),array('price','no_of_comp_pwds','subscription_validity'));
            $subscriptionDetails = $this->getEffectivePricingDetails($userId);
            $price = $subscriptionDetails["price"] * (1+($subscriptionDetails['GST']/100));
            $noOfCPs = $subscriptionDetails["no_of_comp_pwds"];
            $noOfUsers = 1;
            $noOfDays=$subscriptionDetails["subscription_validity"];
            $bookingEndDate = date('Y-m-d', strtotime(date("y-m-d") . " +  $noOfDays days"));
            
              /* $sponseredUsers = explode(",",$response[0]['sponsered_users']);
              $sponseredUsers = array_keys(array_filter($sponseredUsers));
             if(in_array($userId,$sponseredUsers))
              {
                  return new JsonModel(array('success'=>false,'message'=>'Password Already Used'));
              }
              if(!in_array($userId,$sponseredUsers))
              {
                  $updatePassword=$this->passwordTable()->updatePassword(array('user_id'=>$userId,'password_first_used_date'=>$currentDate,'password_expiry_date'=>$expiryDate),array('id'=>$response[0]['id']));
                   array_push($sponseredUsers,$userId);
                    $sponseredUsers=implode(",",$sponseredUsers);
                    $this->bookingTourDetailsTable()->updateBooking(array('sponsered_users'=>$sponseredUsers),array('booking_id'=>$bookingId));
              } */
              $bookingTourDetails=array(
                'place_ids'=>"",
                'discount_percentage'=>"0",
                'tour_date'=>$currentDate,
                'no_of_days'=>$noOfDays,
                'no_of_users'=>1,
                'expiry_date'=>$bookingEndDate,
                'price'=>"",
                'created_at'=>$currentDate,
                'status'=>1
              );
            $saveData=array('tour_type'=>$tourType,'user_type'=>$userType,'user_id'=>$userId,'status'=>1,'booking_type'=>\Admin\Model\Bookings::booking_Sponsored_Subscription,'payment_status'=>\Admin\Model\Payments::payment_success);
            $saveBooking=$this->bookingsTable()->addBooking($saveData);
            if($saveBooking['success'])
            {
                $bookingId=$saveBooking['id'];
                $bookingTourDetails['booking_id']=$bookingId;
                $saveBookingTourDetails=$this->bookingTourDetailsTable()->addBooking($bookingTourDetails);
            }
            else
            {
                return new JsonModel(array('success'=>false,'message'=>'Something Wrong'));
            }
            $subscriptionCount = $this->userTable()->getField(array('user_id'=>$userId),"subscription_count");
            $subscriptionCount = $subscriptionCount+1;
            $updatePassword=$this->passwordTable()->updatePassword(array('user_id'=>$userId,'password_first_used_date'=>$currentDate),array('id'=>$response[0]['id']));
            if($updatePassword){
                $where=array("user_id"=>$userId);
                $update=array("role"=>\Admin\Model\User::Subscriber_role, "subscription_type"=>\Admin\Model\Bookings::booking_Sponsored_Subscription, "subscription_start_date"=>date("Y-m-d"), "subscription_end_date"=>$bookingEndDate, "subscription_count"=>$subscriptionCount, "renewed_on"=>date("Y-m-d"));
                $this->userTable()->updateUser($update,$where);
            }
            
            $passwordsData=array();
            $aes=new Aes();
            // obtain no.of passwords 
            for($i=0;$i<$noOfCPs;$i++)
            {
                $password = $this->random_password();
                $encodeContent = $aes->encrypt($password);
                $encryptPassword = $encodeContent['password'];
                $hash = $encodeContent['hash'];
                $passwordsData[] = array('user_id' => 0, 'hash' => $hash, 'password' => $encryptPassword, 'status' => 1,"booking_id"=>$bookingId,
                    'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"), 'password_expiry_date'=>$bookingEndDate, 'password_type'=>\Admin\Model\Password::passowrd_type_booking);
                $bookingPassword = array($password.$bookingId);
            }

            $this->passwordTable()->addMutiplePasswords($passwordsData);

            /* $data['role']=\Admin\Model\User::Subscriber_role;
            $profileUpdate=$this->userTable()->updateUser($data,array('user_id'=>$userId));
            if($profileUpdate)
            { */
            $checkUser=$this->userTable()->getUserByUserId($userId);
            $photoFilePath = $this->sponsorPhotoTable()->getField(array('file_data_id'=>$userId),'file_path');
            $refDetails = $this->referTable()->getRefers(array('user_id'=>$userId));
            $user=array(
                'name'=>$checkUser['user_name'],
                'email'=>$checkUser['email'],
                'id'=>$checkUser['user_id'],
                'mobile'=>$checkUser['mobile'],
                'mobile_country_code'=>$checkUser['mobile_country_code'],
                'type'=>intval($checkUser['role']),
                'role'=>$checkUser['role'],
                'ref_name'=>$refDetails[0]['ref_by'],
                'ref_mobile'=>$refDetails[0]['ref_mobile'],
                'resState'=>$checkUser['res_state'],  // Added by Manjary for STT SV
                'address'=>$checkUser['address'],  // Added by Manjary for STT SV
                'sponsor_type'=>$checkUser['sponsor_type'],// Added by Manjary for STT SV
                'image_path'=>$photoFilePath,
                'subscription_end_date'=>$checkUser['subscription_end_date'],
                'company_name'=>"",
                'is_promoter'=>$checkUser['is_promoter']
            );
            
            if(intval($checkUser['role']) == \Admin\Model\User::Subscriber_role){
                $userDetails=$this->userTable()->getFields(array('user_id'=>$checkUser['user_id']),array('user_id','user_name','email','mobile','mobile_country_code','role','subscription_start_date','subscription_end_date','res_state','address','status','company_role','renewed_on'));
                $bookingDetails=$this->bookingsTable()->bookingDetails(array('user_id'=>$userId, 'booking_id'=>$bookingId));
                $bookingTourDetails=$this->bookingtourdetailsTable()->getFields(array('booking_id'=>$bookingId),  array('discount_percentage','tour_date','no_of_days','no_of_users','expiry_date','sponsered_users','price','booking_tour_id', 'created_at'));
                
                $bookingList = $bookingTourDetails;
                $bookingList['user_type'] = $userDetails['role'];
                $bookingList['tax'] = ($bookingTourDetails['price']?($bookingTourDetails['price']-($bookingTourDetails['price']/1.18)):0);
                $bookingList['discount_price'] = ($bookingTourDetails['discount_percentage']==""?0:$bookingTourDetails['discount_percentage']);
                $bookingList['booking_id'] = $bookingId;
                $bookingList['user_name']=$userDetails['user_name'];
                $bookingList['mobile']=$userDetails['mobile'];
                $bookingList['mobile_country_code']=$userDetails['mobile_country_code'];
                $bookingList['role'] = $userDetails['role'];
                $bookingList['email']=$userDetails['email'];
                $bookingList['subs_start_date'] = $userDetails['subscription_start_date'];
                $bookingList['renewed_on'] = $userDetails['renewed_on'];
                $bookingList['subs_end_date'] = $userDetails['subscription_end_date'];
                $bookingList['booking_type'] = \Admin\Model\Bookings::booking_Sponsored_Subscription;
                $bookingList['user_id'] = $userId;
                $bookingList['passwords']=$this->bookingsTable()->bookingPasswords($bookingId);
                $userDetails['booking_type'] = \Admin\Model\Bookings::booking_Sponsored_Subscription;
                $this->mailToUser($bookingList);
                $this->notifyUser($userDetails);
                $this->smsUser($userDetails);
            }
            //}

                // return user details instead of language
                return new JsonModel(array('success'=>true,'message'=>'Valid Credentials','user'=>$user, 'booking_id'=>$bookingId));

                /* $checkDownload=$this->downloadedFileInfo()->getFields(array('user_id'=>$userId,'booking_id'=>$bookingId),array('langauges'));
            $isAlreadyDownloaded=false;
            $downloadLanguages='';
                            if(count($checkDownload))
                            {
                                $downloadLanguages=$checkDownload['langauges'];
                                $isAlreadyDownloaded=true;
                            }
            $bookingDetailsLaguages =$this->bookingTourDetailsTable()->getLanguages($bookingId); 

            return new JsonModel(array('success'=>true,'message'=>'Valid Credentials','download_languages'=>$downloadLanguages,'languages'=>$bookingDetailsLaguages,'already_downloaded'=>$isAlreadyDownloaded,'booking_id'=>$bookingId));*/
          }else
          {
                return new JsonModel(array('success'=>false,'message'=>'Invalid Credentials'));
          }
     }
    public function downloadFilesAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];
        $tourType =$request['tour_type'];
        $userType =$request['user_type'];  // -- added by Manjary
        $password =$request['password'];
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        if($userId=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $tourType = intval($tourType);

        if(intval($userType) == \Admin\Model\User::Individual_role){ // -- condition added by Manjary
            /* if(($password=="" || is_null($password)) && $tourType != \Admin\Model\PlacePrices::tour_type_Spiritual_tour) // - removed by Manjary*/
            if($tourType != \Admin\Model\PlacePrices::tour_type_Spiritual_tour)
            {
                return new JsonModel(array('success'=>false,'message'=>'Invalid Booking'));
            }
        }
        $bookingId =$request['booking_id'];
        $languages =$request['languages'];
        $deviceId =$request['device_id'];
        $deviceName =$request['device_name'];
        $deviceType =$request['device_type'];


        if($bookingId=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        if(is_null($tourType))
        {
            $tourType ='';
        }
        if(is_null($languages))
        {
            $languages ='';
        }
        //$bookingDetails=$this->filesDetails($bookingId,$languages,$password,$tourType,array('device_id'=>$deviceId,'device_name'=>$deviceName,'device_type'=>$deviceType),$userId);
        /* echo '<pre>';
         print_r($fileDetails);
         exit;*/
         
         $bookingPassword = substr($password, 0, 8);
         $passwordId= substr($password,8,strlen($password)-7);
         $deviceDetails=  array('device_id'=>$deviceId,'device_name'=>$deviceName,'device_type'=>$deviceType);

         $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_id','user_name','email','mobile','mobile_country_code','role','subscription_start_date','subscription_end_date','res_state','address','status','company_role'));
         $userType = $userDetails['role'];

        if(intval($userType) == \Admin\Model\User::Subscriber_role || intval($userType) == \Admin\Model\User::Sponsor_role){  // -- condition added by Manjary  -- 
            if($tourType!=\Admin\Model\PlacePrices::tour_type_Spiritual_tour)
            {
                // check for subscription validity  // check for no of downloads -  to be developed later
                $currentDate = date('Y-m-d');
                $subscriptionEndDate = date('Y-m-d',strtotime($userDetails['subscription_end_date']));
                if($currentDate > $subscriptionEndDate){
                    return new JsonModel(array('success'=>false,'message'=>'Subscription expired'));
                }
                
                /* $checkPassword=$this->passwordTable()->checkPasswordForBooking(array('booking_id'=>$bookingId,'user_id'=>$userId,'password'=>$bookingPassword,'password_id'=>$passwordId));
                if(count($checkPassword)==0)
                {
                    return new JsonModel(array('success'=>false,'message'=>'Invalid User'));
                }
                if($checkPassword[0]['password_expiry_date']<date("Y-m-d") && $checkPassword[0]['password_expiry_date']!=date("Y-m-d"))
                {
                    return new JsonModel(array('success'=>false,'message'=>'Booking Expired'));
                } */
            }
        }

        $downloadPlaces=$this->bookingsTable()->bookingFiles($bookingId);


        if(!count($downloadPlaces))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Booking'));
        }

        $countryIds=array_unique(array_filter(explode(",",$downloadPlaces['country_id'])));

        $stateIds=array_unique(array_filter(explode(",",$downloadPlaces['state_id'])));

        $cityIds=array_unique(array_filter(explode(",",$downloadPlaces['city_id'])));

        $placeIds=array_unique(array_filter(explode(",",$downloadPlaces['place_id'])));

        $packageId='';

        if($downloadPlaces['tour_type']==\Admin\Model\PlacePrices::tour_type_Seasonal_special)
        {

            $packageId=$downloadPlaces['package_id'];

            $packageDetails=$this->seasonalSpecialsTable()->getFields(array('seasonal_special_id'=>$packageId),array('seasonal_name','seasonal_type','seasonal_description','seasonal_special_id'));

            if(count($packageDetails))
            {
                $bookingDetails['package_details']=array('seasonal_name'=>$packageDetails['seasonal_name'],'seasonal_type'=>$packageDetails['seasonal_type'],'seasonal_description'=>$packageDetails['seasonal_description'],'seasonal_special_id'=>$packageDetails['seasonal_special_id']);
            }

        }

        $getDefaultLanguage=$this->languagesTable()->getDefaultLanguages();

        $totalLanguages=array();
        $defaultLanguage=array();
        $otherLanguage=array();
        if($languages)
        {
            $languages=array_values(array_filter(explode(",",$languages)));
              foreach ($languages as $language)
              {
                  if(in_array($language,$getDefaultLanguage))
                  {
                      $defaultLanguage[]=$language;
                  }else{
                      $otherLanguage[]=$language;
                  }
              }

        }else{
            $otherLanguage=$getDefaultLanguage;
        }
            /*  print_r($otherLanguage);
        exit;*/
        $fileType=array(\Admin\Model\TourismFiles::file_extension_type_audio,\Admin\Model\TourismFiles::file_extension_type_image);

        $countryDetails=$this->countriesTable()->countriesList($countryIds);
        $stateDetails=$this->statesTable()->statesList($stateIds);
        $cityDetails=$this->citiesTable()->citiesList($cityIds);
        $placeDetails=$this->tourismPlacesTable()->tourismPlacesList($placeIds);
        if(count($otherLanguage)) {
            $fileDetails = $this->tourismFilesTable()->tourisFilesList($countryIds, $stateIds, $cityIds, $placeIds, $packageId, $otherLanguage, $fileType);
        }else{
            $fileDetails = $this->tourismFilesTable()->tourisFilesList($countryIds, $stateIds, $cityIds, $placeIds, $packageId, $defaultLanguage, $fileType);

        }
        $countryList=array();
        $stateList=array();
        $cityList=array();
        $placeList=array();
        $packageList=array();
        foreach ($fileDetails as $files)
        {
            if($files['file_extension_type']==\Admin\Model\TourismFiles::file_extension_type_audio)
            {

                if($files['file_data_type']==\Admin\Model\TourismFiles::file_data_type_country)
                {
                        if(!in_array($files['file_data_id'],$countryList))
                        {
                            $countryList[]=$files['file_data_id'];
                        }
                }
                if($files['file_data_type']==\Admin\Model\TourismFiles::file_data_type_state)
                {
                    if(!in_array($files['file_data_id'],$stateList))
                    {
                        $stateList[]=$files['file_data_id'];
                    }
                }
                if($files['file_data_type']==\Admin\Model\TourismFiles::file_data_type_city)
                {
                    if(!in_array($files['file_data_id'],$cityList)) {
                        $cityList[] = $files['file_data_id'];
                    }
                }
                if($files['file_data_type']==\Admin\Model\TourismFiles::file_data_type_places)
                {
                    if(!in_array($files['file_data_id'],$placeList)) {
                        $placeList[] = $files['file_data_id'];
                    }
                }
                if($files['file_data_type']==\Admin\Model\TourismFiles::file_data_type_seasonal_files)
                {
                    if(!in_array($files['file_data_id'],$packageList)) {
                        $packageList = $files['file_data_id'];
                    }
                }
            }
        }
        if(count($otherLanguage))
        {
            $getDefaultCountrieIds=array();
            $getDefaultStateIds=array();
            $getDefaultCityIds=array();
            $getDefaultPlaceIds=array();
            $getDefaultPackageIds='';
            if(count($countryIds) != count($countryList))
            {
                            foreach ($countryIds as $country)
                            {
                                if(!in_array($country,$countryList))
                                {
                                    $getDefaultCountrieIds[] = $country;
                                }
                            }

            }
            if(count($stateIds) != count($stateList))
            {
                foreach ($stateIds as $state)
                {
                    if(!in_array($state,$stateList))
                    {
                        $getDefaultStateIds[] = $state;
                    }
                }
            }
            if(count($cityIds) != count($cityList))
            {
                foreach ($cityIds as $city)
                {
                    if(!in_array($city,$cityList))
                    {
                        $getDefaultCityIds[] = $city;
                    }
                }
            }
            if(count($placeIds) != count($placeList))
            {
                foreach ($placeIds as $place)
                {
                    if(!in_array($place,$placeList))
                    {

                        $getDefaultPlaceIds[] = $place;

                    }
                }
            }
            if($packageId)
            {
                $getDefaultPackageIds=$packageId;
            }

            if($getDefaultCountrieIds || $getDefaultStateIds || $getDefaultCityIds ||$getDefaultPlaceIds || $getDefaultPackageIds)
            {
                $fileType=array(\Admin\Model\TourismFiles::file_extension_type_audio);

                $getFiles=$this->tourismFilesTable()->tourisFilesList($getDefaultCountrieIds,$getDefaultStateIds,$getDefaultCityIds,$getDefaultPlaceIds,$getDefaultPackageIds,$defaultLanguage,$fileType);

                $fileDetails=array_merge($fileDetails,$getFiles);
            }
        }

        $bookingDetails['tour_type']=$downloadPlaces['tour_type'];

        /*  -- removed by Manjary - start
        if($downloadPlaces['itinerary_image'])
        {
            $bookingDetails['itinerary_image']=$downloadPlaces['itinerary_image'];
        } 
        -- removed by Manjary - end  */

        $bookingDetails['booking_id']=$downloadPlaces['booking_tour_id'];
        $bookingDetails['user_type']=$downloadPlaces['user_type'];

        //if($userType == "2" || $userType == "3"){
        if(intval($userType) == \Admin\Model\User::Subscriber_role || intval($userType) == \Admin\Model\User::Sponsor_role){
            $bookingDetails['tour_date'] = $downloadPlaces['tour_date'];
            $bookingDetails['expiry_date'] = $downloadPlaces['expiry_date'];
        }
        else
        {
            if($tourType!=\Admin\Model\PlacePrices::tour_type_Spiritual_tour)
            {
                $bookingDetails['tour_date'] = $checkPassword[0]['password_first_used_date'];
                $bookingDetails['expiry_date'] = $checkPassword[0]['password_expiry_date'];
            }else
            {
                $bookingDetails['tour_date'] = $downloadPlaces['tour_date'];
                $bookingDetails['expiry_date'] = $downloadPlaces['expiry_date'];
            } 
        }

        $bookingDetails['no_of_days'] = $downloadPlaces['no_of_days'];
        $bookingDetails['country'] = $countryDetails;
        $bookingDetails['state'] = $stateDetails;
        $bookingDetails['city'] = $cityDetails;
        $bookingDetails['place'] = $placeDetails;
        $bookingDetails['files'] = $fileDetails;

        /* $saveDownloadData=array('user_id'=>$userId,'booking_id'=>$bookingId,
            'place_ids'=>",".implode($placeIds,",").",",
            'country_ids'=>",".implode($countryIds,",").",",
            'city_ids'=>",".implode($cityIds,",").",",
            'langauges'=>implode(",",$getDefaultLanguage)
        ); */
        $saveDownloadData=array('user_id'=>$userId,'booking_id'=>$bookingId,
            'place_ids'=>",".implode(",", $placeIds).",",
            'country_ids'=>",".implode(",",$countryIds).",",
            'city_ids'=>",".implode(",",$cityIds).",",
            'langauges'=>implode(",",$getDefaultLanguage)
        );

        if(count($stateIds))
        {
            $saveDownloadData['state_ids']=",".implode($stateIds,",").",";
        }

        if($downloadPlaces['tour_type']==\Admin\Model\PlacePrices::tour_type_Seasonal_special)
        {
            //$packageId=$downloadPlaces['package_id'];
            $saveDownloadData['package_ids']=$packageId;
        }

        if(count($deviceDetails))
        {
            $saveDownloadData['device_id']=is_null($deviceDetails['device_id'])?'':$deviceDetails['device_id'];
            $saveDownloadData['device_type']=is_null($deviceDetails['device_type'])?0:$deviceDetails['device_type'];
            $saveDownloadData['device_name']=is_null($deviceDetails['device_name'])?'':$deviceDetails['device_name'];
        }

        $saveDownloadResponse=  $this->downloadedFileInfo()->addDownload($saveDownloadData);
        return  new JsonModel(array('success'=>true,'booking_details'=>$bookingDetails));
    }

    private function filesDetails($bookingId,$languages,$password,$tourType,$deviceDetails,$userId)
    {

                     if($tourType!=\Admin\Model\PlacePrices::tour_type_Spiritual_tour)
                     {
                         $checkPassword=$this->passwordTable()->checkPasswordForBooking(array('booking_id'=>$bookingId,'user_id'=>$userId,'password'=>$password));

                         if(!count($password))
                         {
                             return new JsonModel(array('success'=>false,'message'=>'Invalid User'));
                         }

                         if($checkPassword[0]['password_expiry_date']<date("Y-m-d"))
                         {
                             return new JsonModel(array('success'=>false,'message'=>'Booking Expired'));
                         }
                     }

                 $downloadPlaces=$this->bookingsTable()->bookingFiles($bookingId);

          if(!count($downloadPlaces))
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Booking'));
          }

          $countryIds=array_unique(array_filter(explode(",",$downloadPlaces['country_id'])));
          $stateIds=array_unique(array_filter(explode(",",$downloadPlaces['state_id'])));
          $cityIds=array_unique(array_filter(explode(",",$downloadPlaces['city_id'])));
          $placeIds=array_unique(array_filter(explode(",",$downloadPlaces['place_id'])));
          $packageId='';
          if($downloadPlaces['tour_type']==\Admin\Model\PlacePrices::tour_type_Seasonal_special)
             {
                 $packageId=$downloadPlaces['package_id'];
                 $packageDetails=$this->seasonalSpecialsTable()->getFields(array('seasonal_special_id'=>$packageId),array('seasonal_name','seasonal_type','seasonal_description','seasonal_special_id'));
                 if(count($packageDetails))
                 {
                     $bookingDetails['package_details']=array('seasonal_name'=>ucfirst($packageDetails['seasonal_name']),'seasonal_type'=>$packageDetails['seasonal_type'],'seasonal_description'=>$packageDetails['seasonal_description'],'seasonal_special_id'=>$packageDetails['seasonal_special_id']);
                 }
             }
             $getDefaultLanguage=$this->languagesTable()->getDefaultLanguages();
          $totalLanguages=array();
            if($languages)
            {
                $getDefaultLanguage[]=$languages;

            }


          $countryDetails=$this->countriesTable()->countriesList($countryIds);
          $stateDetails=$this->statesTable()->statesList($stateIds);
          $cityDetails=$this->citiesTable()->citiesList($cityIds);
          $placeDetails=$this->tourismPlacesTable()->tourismPlacesList($placeIds);
          $fileDetails=$this->tourismFilesTable()->tourisFilesList($countryIds,$stateIds,$cityIds,$placeIds,$packageId,$getDefaultLanguage);
          $bookingDetails['tour_type']=$downloadPlaces['tour_type'];
          if($downloadPlaces['itinerary_image'])
          {
              $bookingDetails['itinerary_image']=$downloadPlaces['itinerary_image'];

          }
          $bookingDetails['booking_id']=$downloadPlaces['booking_tour_id'];
          $bookingDetails['user_type']=$downloadPlaces['user_type'];
          $bookingDetails['tour_date']=$downloadPlaces['tour_date'];
          $bookingDetails['expiry_date']=$downloadPlaces['expiry_date'];
          $bookingDetails['no_of_days']=$downloadPlaces['no_of_days'];
          $bookingDetails['country']=$countryDetails;
          $bookingDetails['state']=$stateDetails;
          $bookingDetails['city']=$cityDetails;
          $bookingDetails['place']=$placeDetails;
          $bookingDetails['files']=$fileDetails;
          $saveDownloadData=array('user_id'=>$userId,'booking_id'=>$bookingId,
              'place_ids'=>",".implode($placeIds,",").",",
              'country_ids'=>",".implode($countryIds,",").",",
              'city_ids'=>",".implode($cityIds,",").",",

          );

         if(count($stateIds))
         {
             $saveDownloadData['state_ids']=",".implode($stateIds,",").",";
         }

          if($downloadPlaces['tour_type']==\Admin\Model\PlacePrices::tour_type_Seasonal_special)
          {
              //$packageId=$downloadPlaces['package_id'];
              $saveDownloadData['package_ids']=$packageId;
          }

          if(count($deviceDetails))
          {
              $saveDownloadData['device_id']=is_null($deviceDetails['device_id'])?'':$deviceDetails['device_id'];
              $saveDownloadData['device_type']=is_null($deviceDetails['device_type'])?0:$deviceDetails['device_type'];
              $saveDownloadData['device_name']=is_null($deviceDetails['device_name'])?'':$deviceDetails['device_name'];
          }

          $this->downloadedFileInfo()->addDownload($saveDownloadData);
          return $bookingDetails;
      }

      public function fileLinkAction()
      {

           return new JsonModel(array('success'=>true,'s3'=>base64_encode($this->filesUrl())));

      }
      /* Removed by Manjary for STT subscription version - start
      public function  tourOperatorSignupAction()
      {
          $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
          $request = $this->getRequest()->getPost();
          $files = $this->getRequest()->getFiles();

          if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }

          $companyName=$request['company_name'];
          $companyPanNumber=$request['company_pan_number'];
          $companyRegistrationCertificate=$files['company_registration_certificate'];
          $contcatName=$request['contact_name'];
          $designation=$request['designation'];
          $contactMobile=$request['contact_mobile'];
          $contactMobileCountry=$request['contact_mobile_country'];
          $emailId=$request['email_id'];
          $companyCoordinatorCertificate1=$files['company_coordinator_certificate_1'];
          $companyCoordinatorCertificate2=$files['company_coordinator_certificate_2'];
           $coordinatorDetails = $request['coordinator_details'];

          $llpinRefNo=$request['llpin_ref_no'];
          $companyStatus=$request['company_status'];
          $state = $request['state'];
          $registrationDate = $request['registration_date'];
          $recognitionDescription = $request['recognition_description'];
          $companyWebSite = $request['company_web_site'];

          if($companyName=="")
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>1));
          }

          if($companyPanNumber=="")
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>2));
          }

          if($companyRegistrationCertificate=="")
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>3));
          }

          if($designation=="")
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>4));
          }

          if($contcatName=="")
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>5));
          }

          if($emailId=="")
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>6));
          }

          if($companyCoordinatorCertificate1=="")
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>7));
          }

          if($coordinatorDetails=="")
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>8));
          }

        //  if($companyStatus=="")
        //   {
        //       return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>9));
        //   }


          if($state=="")
          {
              return new JsonModel(array('success'=>false,'message'=>'Invalid Access','error_code'=>10));
          }

          $registrationDate=date("Y-m-d",strtotime($registrationDate));
          $checkMobileNumber=$this->userTable()->checkMobileRegistred(array('mobile'=>$contactMobile,'mobile_country_code'=>$contactMobileCountry));
          $userId='';
            //    echo '<pre>';
            //     print_r($checkMobileNumber);
            //     exit;
          if(count($checkMobileNumber))
          {
              if($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role)
              {
                  return new JsonModel(array('success'=>false,'message'=>'mobile number already registered as a tour Coordinator'));
              }
                if($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_Operator_role)
                {
                    return new JsonModel(array('success'=>false,'message'=>'mobile number already registered as a tour Operator'));
                }
          }
          $checkEmail=$this->userTable()->verfiyUser(array('email'=>$emailId));

          if(count($checkEmail))
          {
              if($checkEmail[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role)
              {
                  return new JsonModel(array('success'=>false,'message'=>'email already registered as a tour Coordinator'));
              }
              if($checkEmail[0]['company_role'] == \Admin\Model\User::Tour_Operator_role)
              {
                  return new JsonModel(array('success'=>false,'message'=>'email already registered as a tour Operator'));
              }
          }
          $validImageFiles=array('png','jpg','jpeg');
          $saveCoordinator=array();
          $counter=0;

        $coordinatorDetails=json_decode($coordinatorDetails,true);
          $coordinatorMobilesList=array();
          $coordinatorEmailList=array();
        foreach ($coordinatorDetails as $coordinator)
        {
              $coordinatorMobile=$coordinator['mobile'];
              $coordinatorMobileCountryCode=$coordinator['mobile_country_code'];
              $designation=$coordinator['designation'];
              $name=$coordinator['name'];
              $email=$coordinator['email'];
              if($coordinatorMobile && $coordinatorMobileCountryCode && $designation) {


                  $checkMobileNumber = $this->userTable()->checkMobileRegistred(array('mobile'=>$coordinatorMobile,'mobile_country_code'=>$coordinatorMobileCountryCode));

                       if($contactMobile == $coordinatorMobile)
                       {
                           return new JsonModel(array('success' => false, 'message' => 'Mobile number would not be same for tour Operator and tour Coordinator'));
                       }

                  if($email == $emailId)
                  {
                      return new JsonModel(array('success' => false, 'message' => 'email would not be same for tour Operator and tour Coordinator'));
                  }

                       if(in_array($coordinatorMobile,$coordinatorMobilesList))
                       {

                        return new JsonModel(array('success' => false, 'message' => 'Mobile number would not be same tour Coordinators'));

                       }
                  if(in_array($email,$coordinatorEmailList))
                  {
                      return new JsonModel(array('success' => false, 'message' => 'email would not be same tour Coordinators'));
                  }
                      if(count($checkMobileNumber))
                      {
                          if ($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role)
                          {
                              return new JsonModel(array('success' => false, 'message' => 'Mobile number already registered as a tour Coordinator'));
                          }
                          if ($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_Operator_role)
                          {
                              return new JsonModel(array('success' => false, 'message' => 'Mobile number already registered as a tour Operator'));
                          }
                      }
                  $checkEmail=$this->userTable()->verfiyUser(array('email'=>$email));

                  if(count($checkEmail))
                  {
                      if($checkEmail[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role)
                      {
                          return new JsonModel(array('success'=>false,'message'=>'email already registered as a tour Coordinator'));
                      }
                      if($checkEmail[0]['company_role'] == \Admin\Model\User::Tour_Operator_role)
                      {
                          return new JsonModel(array('success'=>false,'message'=>'email already registered as a tour Operator'));
                      }
                  }

                  array_push($coordinatorMobilesList,$coordinatorMobile);
                  array_push($coordinatorEmailList,$email);

                  if ($counter == 0)
                  {
                      $attachment = $companyCoordinatorCertificate1;
                  }else
                  {
                      $attachment = $companyCoordinatorCertificate2;
                  }

                  $filename = $attachment['name'];
                  $fileExt = explode(".", $filename);
                  $ext = end($fileExt) ? end($fileExt) : "";
                  $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                  $filename = $filenameWithoutExt;
                  $filePath = "data/attachment";
                  @mkdir(getcwd() . "/public/" . $filePath, 0777, true);
                  $filePath = $filePath . "/" . $filename . "." . $ext;

                //   if (!in_array(strtolower($ext), $validImageFiles)) {
                //       return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                //   }
                  $this->pushFiles($filePath, $attachment['tmp_name'], $attachment['type']);

                  $saveCoordinatorUser[$counter] = array('mobile' => $coordinator['mobile'], 'mobile_country_code' => $coordinator['mobile_country_code'], 'role' => \Admin\Model\User::Tour_coordinator_role,'company_role' => \Admin\Model\User::Tour_coordinator_role);
                 $saveCoordinator[$counter]=array('designation'=>$designation,'coordinator_order'=>$counter+1,'user_name'=>$name,'email'=>$email,'coordinator_certificate'=>$filePath,'certificate_date'=>date("Y-m-d"),'status'=>1);
              }
              $counter++;
          }
          $recognitionDocument='';
           if(isset($files['recognition_document']) && count($files['recognition_document']))
           {
               $filename = $files['recognition_document']['name'];
               $fileExt = explode(".", $filename);
               $ext = end($fileExt) ? end($fileExt) : "";
               $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
               $filename = $filenameWithoutExt;
               $filePath = "data/attachment";
               @mkdir(getcwd() . "/public/" . $filePath, 0777, true);

               $filePath = $filePath . "/" . $filename . "." . $ext;
               $recognitionDocument=$filePath;
            //    if (!in_array(strtolower($ext), $validImageFiles)) {
            //        return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
            //    }
               $this->pushFiles($filePath, $files['recognition_document']['tmp_name'], $files['recognition_document']['type']);
           }

             $saveUser=$this->userTable()->newUser(array('mobile'=>$contactMobile,'company_role'=>\Admin\Model\User::Tour_Operator_role,'email'=>$emailId,'mobile_country_code'=>$contactMobileCountry,'role'=>\Admin\Model\User::Tour_Operator_role,'status'=>1));
               if(!$saveUser['success'])
               {
                   return new JsonModel(array('success'=>false,'message'=>'Something Went Wrong Try Again'));
               }

               $userId=$saveUser['user_id'];

          $attachment=$companyRegistrationCertificate;
          $filename = $attachment['name'];
          $fileExt = explode(".", $filename);
          $ext = end($fileExt) ? end($fileExt) : "";
          $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
          $filename = $filenameWithoutExt;
          $filePath = "data/attachment";
          @mkdir(getcwd() . "/public/" . $filePath, 0777, true);

          $filePath = $filePath . "/" . $filename . "." . $ext;

          if (!in_array(strtolower($ext), $validImageFiles))
          {
              return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
          }

          $this->pushFiles($filePath, $attachment['tmp_name'], $attachment['type']);

          if(is_null($llpinRefNo))
          {
              $llpinRefNo='';
          }

          if(is_null($recognitionDescription))
          {
              $recognitionDescription='';
          }

          if(is_null($companyWebSite))
          {
              $companyWebSite='';
          }

          $additionalDetails=array('llpin_ref_no'=>$llpinRefNo,'recognition_description'=>$recognitionDescription,'company_web_site'=>$companyWebSite);
          $additionalDetails=json_encode($additionalDetails);
          $saveOperator=$this->tourOperatorDetailsTable()->addTourOperator(array('registration_date'=>$registrationDate,'company_status'=>$companyStatus,'state'=>$state,'additional_details'=>$additionalDetails,'user_id'=>$userId,'company_name'=>$companyName,'contact_person'=>$contcatName,'email_id'=>$emailId,'pan_number'=>$companyPanNumber,'registration_certificate'=>$filePath,'status'=>1));

          if(!$saveOperator['success'])
            {
                return new JsonModel(array('success'=>false,'message'=>'Something Went Wrong Try Again'));
            }

            $tourCompanyId=$saveOperator['id'];
            $aes=new Aes();
          $password = $this->random_password();
          $encodeContent = $aes->encrypt($password);
          $encryptPassword = $encodeContent['password'];
          $hash = $encodeContent['hash'];
          $passwordsData[] = array('user_id' => $userId,
              'password_type'=>\Admin\Model\Password::passowrd_type_login,
              'booking_id'=>0 ,
              'hash' => $hash,
              'password' => $encryptPassword,
              'status' => 1,
              'created_at'=>date("Y-m-d H:i:s"),
              'updated_at'=>date("Y-m-d H:i:s"));
          $sendSms[]=array('mobile'=>$contactMobileCountry.$contactMobile,'message'=>'Your Password For Account is '.$password);

            $counter = -1;
          if(count($saveCoordinator)) {
              foreach ($saveCoordinator as $details)
              {
                  $counter++;
                  $saveUser=$this->userTable()->newUser($saveCoordinatorUser[$counter]);
                  $userId=$saveUser['user_id'];
                  $saveCoordinator[$counter]['user_id'] = $saveUser['user_id'];
                  $saveCoordinator[$counter]['company_id'] = $tourCompanyId;
                  $saveCoordinator[$counter]["created_at"] = date("Y-m-d H:i:s");
                  $saveCoordinator[$counter]["updated_at"] = date("Y-m-d H:i:s");
                  $password = $this->random_password();
                  $encodeContent = $aes->encrypt($password);
                  $encryptPassword = $encodeContent['password'];
                  $hash = $encodeContent['hash'];
                  $passwordsData[] = array('user_id' => $userId,'password_type'=>\Admin\Model\Password::passowrd_type_login,'booking_id'=>0 ,'hash' => $hash, 'password' => $encryptPassword, 'status' => 1,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                  $sendSms[]=array('mobile'=>$saveCoordinatorUser[$counter]['mobile_country_code'].$saveCoordinatorUser[$counter]['mobile'],'message'=>'Your Password For Account is '.$password);
              }
              $save=$this->tourCoordinatorDetailsTable()->addMutipleTourCoordinator($saveCoordinator);

              $savePassword=$this->passwordTable()->addMutiplePasswords($passwordsData);

                 foreach ($sendSms as $messageData)
                 {
                     $this->sendPasswordSms($messageData['mobile'],array('text'=>$messageData['message']));
                 }


          }
          return new JsonModel(array('success'=>true,'message'=>'Success'));
      }
      Removed by Manjary for STT subscription version - end */
     public  function seasonalSpecialsListAction()
     {
         $headers = $this->getRequest()->getHeaders();
         $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $userId = $request['user_id'];
         $search = $request['search'];
         $tourType=$request['tour_type'];
         $countryId=$request['country_id'];

         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
           if(is_null($tourType))
           {
               $tourType='';
           }
         if(is_null($countryId))
         {
             $countryId='';
         }
         if($userId=="")
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         $list=$this->seasonalSpecialsTable()->seasonalSpecials(array('tour_type'=>$tourType,'country_id'=>$countryId,'search'=>$search));
       return new JsonModel(array('success'=>true,'seasonal_special_list'=>$list));
     }

    public  function seasonalSpecialsCountryAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];
        $search = $request['search'];

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($userId=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $list=$this->seasonalSpecialsTable()->seasonalSpecialsCountryList();
        return new JsonModel(array('success'=>true,'countries'=>$list));
    }

     public function packageDetailsAction()
     {
         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $userId = $request['user_id'];
         $seasonalSpecialsId = $request['seasonal_special_id'];

         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($userId=="")
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($seasonalSpecialsId=="")
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         $list=$this->seasonalSpecialsTable()->seasonalSpecialDetailsWithPlaces($seasonalSpecialsId);
         return new JsonModel(array('success'=>true,'package_details'=>$list));
     }

     public function samplesAudioListAction()
     {
         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $userId = $request['user_id'];

         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($userId=="")
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         $sampleAudioList=$this->tourismFilesTable()->getTourismAudioFilesList();
         return new JsonModel(array('success'=>true,'audio_list'=>$sampleAudioList));
     }

     public function subscribersCountAction(){
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $ssc = $this->sscTable()->getSSC();
        $count = "";
        if($ssc == "1"){
            $subscribers = $this->userTable()->getAllSubscribers();
            $count = count($subscribers);
        }

        return new JsonModel(array('success'=>true,'message'=>"success", 'count'=>$count));
     }
     public function updateFcmAction()
     {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $request=$this->getRequest()->getPost();
//print_r($request);
        $fcmToken=$request['fcm_token'];
        if($fcmToken == ""){
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $userId=$request['user_id'];
        if($userId == ""){
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $deviceId=$request['device_id'];
        if($deviceId == ""){
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $deviceType=$request['device_type'];
        $logout=$this->fcmTable()->saveFcm($fcmToken,$deviceId,$userId);
        //$logout = $this->fcmTable()->getField(array("device_id"=>$deviceId),"logout");
        if($logout)
            return new JsonModel(array('success'=>true,'message'=>"multilogin"));
        else{
            //$save=$this->fcmTable()->saveFcm($fcmToken,$deviceId,$userId);
            return new JsonModel(array('success'=>false,'message'=>"updated"));
        }
    }

    public function favouriteFileAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue())) {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access','errr-cpde'=>0));
        }
        $request=$this->getRequest()->getPost();
         $fileIds=$request['file_ids'];
         $userId=$request['user_id'];
        if($fileIds=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access', 'error-code'=>1));
        }
        $updateStatus=true;

        $fileIds=json_decode($fileIds,true);

             foreach ($fileIds as $fileStatus)
             {
                 $fileId=$fileStatus['file_id'];
                 $status=$fileStatus['status'];
                 $fileDetails = $this->tourismFilesTable()->getFields(array('tourism_file_id' => $fileId), array('file_data_type', 'file_data_id'));
                 if (!count($fileDetails))
                 {
                     return new JsonModel(array('success' => false, 'message' => 'Invalid File'));
                 }
                 $fileDataId = $fileDetails['file_data_id'];
                 $fileDataType = $fileDetails['file_data_type'];
                 $checkLike = $this->likes()->getFields(array('tourism_file_id' => $fileId), array('like_id', 'status'));
                 if (!$checkLike)
                 {
                     $saveData = $this->likes()->addLike(array('tourism_file_id' => $fileId, 'file_data_type' => $fileDataType, 'file_data_id' => $fileDataId, 'user_id' => $userId, 'status' => \Admin\Model\Likes::Status_Like));
                     if(!$saveData['success'])
                     {
                         $updateStatus=false;
                         break ;
                     }
                 }else
                 {
                     $likeId = $checkLike['like_id'];
                     $updateData = $this->likes()->updateLike(array('status' => $status), array('like_id' => $likeId));
                      if(!$updateData)
                      {
                          $updateStatus=false;
                          break ;
                      }
                 }
             }
        if ($updateStatus)
        {
            return new JsonModel(array('success' => true, 'message' => 'success'));

        }
        return new JsonModel(array('success'=>false,'message'=>'Something Went Wrong Try Again'));
    }

    public function downloadBookingListAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];
        $tourType=$request['tour_type'];

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($userId=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $limit=$request['limit'];
        $offset=$request['offset'];

        if(is_null($limit))
        {
            $limit=10;
        }else
        {
            $limit=intval($limit);
        }

        if(is_null($offset))
        {
            $offset=0;
        }else
        {
            $offset=intval($offset);
        }
        $bookingList=$this->bookingsTable()->sponsoredBookingsList(array('limit'=>$limit,'offset'=>$offset,'user_id'=>$userId,'tour_type'=>$tourType));
        return  new JsonModel(array('success'=>true,'bookings'=>$bookingList));
    }

    public function addSponsorPhotoAction()
     {
         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $files = $this->getRequest()->getFiles();
         $userId = $request['user_id'];

         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($userId=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         
         $validImageFiles=array('png','jpg','jpeg');

         if(isset($files['photo_file']))
         {
             $attachment=$files['photo_file'];
                 $imagesPath = '';
                 if (isset($attachment['name'])) {
                     $filename = $attachment['name'];
                     $fileExt = explode(".", $filename);
                     $ext = end($fileExt) ? end($fileExt) : "";
                     $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                     $filename = $filenameWithoutExt;
                     $filePath = "data/images/sponsors";
                     if (!file_exists(getcwd() . "/public/" . $filePath)) {
                        @mkdir(getcwd() . "/public/" . $filePath, 0777, true);
                     }

                     $filePath = $filePath . "/" . $filename . "." . $ext;

                     if (!in_array(strtolower($ext), $validImageFiles)) {
                         return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                     }
                     $result = $this->pushFiles($filePath, $attachment['tmp_name'], $attachment['type']);
                     if($result)
                        $uploadFileDetails = array('file_path' => $filePath,
                         'file_extension' => $ext,
                         'status' => 1,
                         'file_data_id' => $userId,
                         'file_name' => $attachment['name']);
                 }else{
                     return new JsonModel(array('success'=>false,'message'=>'please upload image '));

                 }
         }else{
             return new JsonModel(array('success'=>false,'message'=>'please upload image '));

         }

        $checkFile=$this->sponsorPhotoTable()->getFields(array('file_data_id'=>$userId),array('id'));
        if(count($checkFile))
        {
            $updateResponse=$this->sponsorPhotoTable()->updateSponsorPhoto(array('status'=>0),array('id'=>$checkFile['id']));
        }
        $saveResponse =  $this->sponsorPhotoTable()->addSponsorPhoto($uploadFileDetails);
        if($saveResponse)
        {
            $photoFilePath = $this->sponsorPhotoTable()->getField(array('id'=>$saveResponse['id']),'file_path');
            return new JsonModel(array("success"=>true,"message"=>"Image uploaded Successfully", "s3"=>$photoFilePath));

        }else{
            return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));

        }
     }

     public function addItineraryImageAction()
     {
         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $files = $this->getRequest()->getFiles();
         $userId = $request['user_id'];
         $bookingId = $request['booking_id'];

         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($userId=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         if($bookingId=="")
         {
             return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }
         $validImageFiles=array('png','jpg','jpeg');

         if(isset($files['itinerary_file']))
         {
             $attachment=$files['itinerary_file'];

                 $imagesPath = '';
                 $pdf_to_image = "";


                 if (isset($attachment['name'])) {
                     $filename = $attachment['name'];
                     $fileExt = explode(".", $filename);
                     $ext = end($fileExt) ? end($fileExt) : "";
                     $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                     $filename = $filenameWithoutExt;
                     $filePath = "data/images";
                     @mkdir(getcwd() . "/public/" . $filePath, 0777, true);

                     $filePath = $filePath . "/" . $filename . "." . $ext;

                     if (!in_array(strtolower($ext), $validImageFiles)) {
                         return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                     }
                     /* if (move_uploaded_file($attachment['tmp_name'], getcwd() . "/public/" . $filePath))
                      {
                          $imagesPath = $filePath;
                          $localImagePath = getcwd() . "/public/" . $filePath;
                          chmod(getcwd() . "/public/" . $filePath, 0777);
                      }*/
                     $this->pushFiles($filePath, $attachment['tmp_name'], $attachment['type']);

                     /*$fp = fopen(getcwd() . "/public/" . $filePath, 'w');
                     $encodeContent=$aes->encrypt(file_get_contents($attachment['tmp_name']));
                     $encodeString=$encodeContent['password'];
                     $hash=$encodeContent['hash'];
                     fwrite($fp,$encodeString);
                     fclose($fp);*/
                     $uploadFileDetails = array('file_path' => $filePath,
                         'file_data_type' => \Admin\Model\TourismFiles::file_data_type_itinerary_files,
                         'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                         'file_extension' => $ext,
                         'status' => 1,
                         'duration' => 0,
                         'hash' => '',
                         'file_language_type' => 0,
                         'file_data_id' => $bookingId,
                         'file_name' => $attachment['name']);
                 }else{
                     return new JsonModel(array('success'=>false,'message'=>'please upload images '));

                 }

         }else{
             return new JsonModel(array('success'=>false,'message'=>'please upload images '));

         }

                 $checkiTineraryFile=$this->tourismFilesTable()->getFields(array('file_data_id'=>$bookingId,'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_itinerary_files),array('tourism_file_id'));
        if(count($checkiTineraryFile))
        {
            $updateResponse=$this->tourismFilesTable()->updatePlaceFiles(array('status'=>0),array('tourism_file_id'=>$checkiTineraryFile['tourism_file_id']));
        }
           $saveResponse=  $this->tourismFilesTable()->addTourismFiles($uploadFileDetails);
            if($saveResponse)
            {
                return new JsonModel(array("success"=>true,"message"=>"Itinerary Image Successfully"));

            }else{
                return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));

            }
     }
     /*  Removed by Manjary for STT subscription version - start
     public function tourOperatorSigninAction()
     {

         $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         $request = $this->getRequest()->getPost();
         $mobile = $request['mobile'];
         $mobileCountryCode = $request['mobile_country_code'];
         $password = $request['password'];
         if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

          if($mobile=="")
          {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
          }

          if($mobileCountryCode=="")
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         if($password=="")
         {
             return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
         }

         $checkUser=$this->userTable()->checkTourOperatorWithMobile($mobile,$mobileCountryCode,$password);
         if(count($checkUser))
         {
                   $status=$checkUser['status'];
             if($status == \Admin\Model\TourOperatorDetails::Pending_status)
             {
                 return new JsonModel(array('success'=>false,'message'=>'Your account has not approved'));
             }
             if($status == \Admin\Model\TourOperatorDetails::Rejected_status)
             {
                 return new JsonModel(array('success'=>false,'message'=>'Your account has been rejected'));
             }
             if($checkUser['company_role']==\Admin\Model\User::Tour_coordinator_role)
             {
                 $userDetails=array('user_name'=>$checkUser['user_name'],'role'=>$checkUser['company_role'],'email'=>$checkUser['email']);
             }else
             {
                 $userDetails=array('contact_person'=>$checkUser['contact_person'],'company_name'=>$checkUser['company_name'],'role'=>$checkUser['company_role'],'email'=>$checkUser['email_id']);
             }
             return new JsonModel(array('success'=>true,"message"=>'Login Successfully','user_id'=>$checkUser['user_id'],'user_details'=>$userDetails));
         }else
         {
             return new JsonModel(array('sucess'=>false,'message'=>'Invalid Credentials'));
         }
     }
    public function forgotPasswordAction()
    {

        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $mobile = $request['mobile'];
        $mobileCountryCode = $request['mobile_country'];
        $password = $request['password'];
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($mobile=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($mobileCountryCode=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }



        $checkUser=$this->userTable()->checkTourOperatorWithMobile($mobile,$mobileCountryCode);
        if(count($checkUser))
        {
            $user_id=$checkUser['user_id'];

            $data=array("user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::FORGOT_OTP,"status"=>\Admin\Model\Otp::Not_verifed);

            $update = $this->otpTable()->updateData(array('status' => \Admin\Model\Otp::Is_verifed), $data);

            $otp=$this->generateOtp();

            $insertData=array("user_id"=>$user_id,"otp_type"=>\Admin\Model\Otp::FORGOT_OTP,"status"=>\Admin\Model\Otp::Not_verifed,'otp'=>$otp);

            $this->otpTable()->insertData($insertData);

            $this->sendOtpSms($mobileCountryCode.$mobile,$otp);

            return new JsonModel(array('success'=>true,"message"=>'OTP Sent To Your Mobile Please Verify To Change Password','user_id'=>$checkUser['user_id']));
        }else
        {
            return new JsonModel(array('sucess'=>false,'message'=>'Invalid User'));
        }
    }
    public function changePasswordAction()
    {
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        $request = $this->getRequest()->getPost();
        $userId = $request['user_id'];
        $password = $request['password'];
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($userId=="")
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

           $aes=new Aes();
        $encodeContent = $aes->encrypt($password);
        $encryptPassword = $encodeContent['password'];
        $hash = $encodeContent['hash'];
        $passwordsData = array('user_id' => $userId,'password_type'=>\Admin\Model\Password::passowrd_type_login,'booking_id'=>0 ,'hash' => $hash, 'password' => $encryptPassword, 'status' => 1,'updated_at'=>date("Y-m-d H:i:s"));

          $updatePassword=$this->passwordTable()->updatePassword($passwordsData,array('user_id'=>$userId));

        if($updatePassword)
        {

            return new JsonModel(array('success'=>true,"message"=>'Password Changed'));
        }else
        {
            return new JsonModel(array('sucess'=>false,'message'=>'Invalid User'));
        }
    }
    Removed by Manjary for STT subscription version - end */
    
    public function sendMessageAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $userId=$request["user_id"];
        $message=$request["message"];

        if($userId=='' || $userId==null)
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        if($message=='' || $message==null)
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        //$data=array('user_id'=>$userId,'message'=>$message);
        //$saveResponse=$this->inboxTable()->addInbox($data);
        $userDetails = $this->userTable()->getFields(array("user_id"=>$userId),array('user_name', 'email','mobile', 'mobile_country_code'));
        $userDetails['message'] = $message;
        $userDetails['method'] = 'App Contact';
        $saveResponse = $this->sendmail('susritourtales@gmail.com','Contact From '.$name,'send-email', $userDetails);
        //array('user_name'=>$name,'email'=>$email,'mobile'=>$mobile,'message'=>$message,'method'=>'App Contact'));
        if($saveResponse)
        {
            return new JsonModel(array("success"=>true,"message"=>"Successfully Sent"));

        }else{
            return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));

        }
    }

    public function getDiscountAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());

        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
        $userId=$request["user_id"];

        if($userId=='' || $userId==null)
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }
         $userDetails=$this->userTable()->getUserDetails($userId);

        if($userDetails)
        {
            $applyDiscount=intval($userDetails['discount_percentage']);
            /* $applyDiscount=0;
            if($userDetails['apply_discount'])
            {
                if($userDetails['discount_end_date']>date("Y-m-d"))
                {
                    $applyDiscount=intval($userDetails['discount_percentage']);
                }
            } */
            return new JsonModel(array("success"=>true,"message"=>"Discount Percentage",'discount_percentage'=>$applyDiscount));

        }else{
            return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));

        }
    }

    public function resendEmailAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $userId=$request["user_id"];

        $bookingId=$request["booking_id"];

        if($userId=='' || $userId==null)
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        if($bookingId=='' || $bookingId==null)
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

          $status=$this->bookingsTable()->getField(array('booking_id'=>$bookingId),'status');

        if($status=='' || $status!=\Admin\Model\Payments::payment_success)
        {
            return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $bookingList=$this->bookingsTable()->bookingsDetailsEmail($bookingId);
        $userDetails=$this->userTable()->getFields(array('user_id'=>$bookingList['user_id']),array('email','role'));
        $userType=$userDetails['role'];
        if($userType==\Admin\Model\User::Tour_Operator_role)
        {
            $tourOperatorDetails=$this->userTable()->getUserDetails($userId);
            $email = $tourOperatorDetails['email_id'];

        }else if($userType==\Admin\Model\User::Tour_coordinator_role)
        {
            $tourCoordinatorDetails=$this->userTable()->getUserDetails($userId);
            $email=$tourCoordinatorDetails['email'];
        }else
        {
            $email=$userDetails['email'];
        }
        if($email)
        {
            $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?bid=' . $bookingId, true);
            $mpdf = new mPDF(['tempDir' => getcwd()."/public/data/temp"]);
            $mpdf->SetDisplayMode("fullpage");
            $mpdf->WriteHTML($html);
            $mpdf->Output(getcwd()."/public/data/susri_booking_".$bookingId.".pdf", "F");
            $subject =\Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']] ." Booking Details ";
            $this->sendbookingDetails($email, $subject, 'mail-booking-details', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf");
            return new JsonModel(array("success"=>true,"message"=>"successfully sent"));
        }else
        {
            return new JsonModel(array("success"=>false,"message"=>"Please Add Email To Proceed"));
        }

    }
    private function sendEmail($bookingId,$userId)
    {

        $bookingList=$this->bookingsTable()->bookingsDetailsEmail($bookingId);
        $userDetails=$this->userTable()->getFields(array('user_id'=>$bookingList['user_id']),array('email','role'));
        $userType=$userDetails['role'];
        if($userType==\Admin\Model\User::Tour_Operator_role)
        {
            $tourOperatorDetails=$this->userTable()->getUserDetails($userId);
            $email = $tourOperatorDetails['email_id'];
        }else if($userType==\Admin\Model\User::Tour_coordinator_role)
        {
            $tourCoordinatorDetails=$this->userTable()->getUserDetails($userId);
            $email=$tourCoordinatorDetails['email'];
        }else
        {
            $email=$userDetails['email'];
        }
        if($email)
        {
            $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?bid=' . $bookingId, true);
            $mpdf = new mPDF(['tempDir' => getcwd()."/public/data/temp"]);
            $mpdf->SetDisplayMode("fullpage");
            $mpdf->WriteHTML($html);
            $mpdf->Output(getcwd()."/public/data/susri_booking_".$bookingId.".pdf", "F");
            $subject =\Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']] ." Booking Details ";
            $this->sendbookingDetails($email, $subject, 'mail-booking-details', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf");
            return true;
        }else
        {
            return false;
        }

    }

    public function bannersAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        if(!$headers->get('Access-Token') || !$this->tokenValidation($headers->get('Access-Token')->getFieldValue()))
        {
            return  new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
        }

        $banners=$this->bannersTable()->getBanners();

        return new JsonModel(array('banners'=>$banners,'success'=>true));
    }

    public function financialStatementsAction(){
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
         
        if($request['start_date'])
            $data['start_date']=$request['start_date'];
        if($request['end_date'])
            $data['end_date']=$request['end_date'];
            
        $financialStatement=$this->paymentTable()->getFinancialStatements($data);
        return new JsonModel(array('success'=>true,'financialStatement'=>$financialStatement));
    }

    public function smsTestAction()
    {
        $request = $this->getRequest()->getPost();
        $headers = $this->getRequest()->getHeaders();
        $logResult = $this->logRequest($this->getRequest()->toString());
        //$this->sendPasswordSms($request['mobile'],array('text'=>"test message from stt api"));
        $smsmsg = "Please use OTP 3434 to verify your mobile phone number for Susri Tour Tales App ";
        $this->sendPasswordSms("917330781638",array('text'=>$smsmsg));
        return new JsonModel(array('success'=>true, 'message'=>'message sent'));
    }
}