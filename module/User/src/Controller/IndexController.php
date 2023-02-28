<?php

namespace User\Controller;

use Application\Controller\BaseController;
use Application\Handler\Aes;
use Instamojo\Instamojo;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class IndexController extends BaseController {

    public function indexAction() {
       $marqText = $this->pricingTable()->getWebText();
/*           echo '<pre>';
          print_r($marqText);
          exit; */
       $worldTourplacesList=$this->tourismPlacesTable()->getActiveTourismPlacesUser(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour,'offset'=>0,'limit'=>3));
       $spiritualTourplacesList=$this->tourismPlacesTable()->getActiveTourismPlacesUser(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_Spiritual_tour,'offset'=>0,'limit'=>3));
      
        $indiaTourList=$this->tourismPlacesTable()->getActiveTourismPlacesUser(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'offset'=>0,'limit'=>3));
        $banners=$this->bannersTable()->getBanners();
        $cityTourList=$this->citiesTable()->getActiveCitiesList(array('limit'=>3,'tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour,'country_id'=>'','state_id'=>'','offset'=>0));
            /* echo '<pre>';
            print_r($spiritualTourplacesList);
            echo '</pre>';
            echo '<pre>';
            print_r($indiaTourList);
            echo '</pre>'; 
            exit;*/
        $resarr = array_diff($spiritualTourplacesList, $indiaTourList);
        if(!count($resarr))
            $indiaTourList=$this->tourismPlacesTable()->getActiveTourismPlacesUser(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'offset'=>3,'limit'=>3));
            
        $seasonalSpecialslist=$this->seasonalSpecialsTable()->seasonalSpecials(array());

        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_HOME_PAGE);

            $countryId=$this->countriesTable()->getField(array('country_name'=>'india'),'id');

        return new ViewModel(array('marqText'=>$marqText,'banners'=>$banners,'seasonalSpecialslist'=>array_slice($seasonalSpecialslist, 0, 3),'india'=>$countryId,'cityTour'=>$cityTourList,'worldTourPlacesList'=>$worldTourplacesList,'imageUrl'=>$this->filesUrl(),'spiritualTourplacesList'=>$spiritualTourplacesList,'indiaTourList'=>$indiaTourList));
    }
   public function seasonalSpecialsAction()
   {
       $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TOUR_TALES_PAGE);
       $seasonalSpecialslist=$this->seasonalSpecialsTable()->seasonalSpecials(array());

       return new ViewModel(array('seasonalSpecialslist'=>$seasonalSpecialslist,'imageUrl'=>$this->filesUrl()));
   }
    public function aboutUsAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_ABOUT_PAGE);

        return new ViewModel();
    }

    public function acknowledgementsAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_CREDIT_PAGE);

        return new ViewModel();
    }

    public function toursAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TOUR_TALES_PAGE);

        $AvlCTlist = $this->tourismPlacesTable()->getAvailableList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour,'limit'=>-1, 'offset'=>0, 'country_order'=>1, 'city_order'=>1));
        $AvlITlist = $this->tourismPlacesTable()->getAvailableList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'limit'=>-1, 'offset'=>0, 'city_order'=>1, 'place_name_order'=>1, 'country_id'=>'101'));
        $AvlSTlist = $this->tourismPlacesTable()->getAvailableList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_Spiritual_tour,'limit'=>-1, 'offset'=>0, 'city_order'=>1, 'place_name_order'=>1, 'country_id'=>'101'));
        $AvlFTlist = $this->seasonalSpecialsTable()->getAvailableFT(array('limit'=>-1, 'offset'=>0));
        $AvlWTlist = $this->tourismPlacesTable()->getAvailableList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour,'limit'=>-1, 'offset'=>0, 'country_order'=>1, 'city_order'=>1));

        $UpcITlist = $this->upcomingTable()->getUpcomingList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'api'=>'1','limit'=>-1, 'offset'=>0));
        $UpcSTlist = $this->upcomingTable()->getUpcomingList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_Spiritual_tour,'api'=>'1','limit'=>-1, 'offset'=>0));
        $UpcFTlist = $this->upcomingTable()->getUpcomingList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_Seasonal_special,'api'=>'1','limit'=>-1, 'offset'=>0));
        $UpcCTlist = $this->upcomingTable()->getUpcomingList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour,'api'=>'1','limit'=>-1, 'offset'=>0));
        $UpcWTlist = $this->upcomingTable()->getUpcomingList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour,'api'=>'1','limit'=>-1, 'offset'=>0));

        return new ViewModel(array('AvlCTlist'=>$AvlCTlist, 'AvlITlist'=>$AvlITlist, 'AvlSTlist'=>$AvlSTlist, 'AvlFTlist'=>$AvlFTlist, 'AvlWTlist'=>$AvlWTlist, 'UpcITlist'=>$UpcITlist, 'UpcSTlist'=>$UpcSTlist, 'UpcFTlist'=>$UpcFTlist, 'UpcWTlist'=>$UpcWTlist, 'UpcCTlist'=>$UpcCTlist));
    }

    public function faqsAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_FAQ_PAGE);

        return new ViewModel();
    }

    public function contactAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_CONTACT_PAGE);

        return new ViewModel();
    }

    public function termsPrivacyAction() {
      //  $this->layout()->setVariable('activeTab', \Application\Constants\Constants::);

        return new ViewModel();
    }
    
    public function countryListAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TOUR_TALES_PAGE);

        $paramId = $this->params()->fromRoute('tour', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $tourName=str_replace("-","",$paramId);
         $tourName=strtolower($tourName);
         $tourType=(array_key_exists($tourName,\Admin\Model\PlacePrices::tour_type_text))?\Admin\Model\PlacePrices::tour_type_text[$tourName]:'';
         if(!$tourType)
         {
             return $this->redirect()->toUrl($this->getBaseUrl());
         }
        $countriesList=$this->countriesTable()->getActiveCountriesList(array('limit'=>-1,'tour_type'=>$tourType));
        return new ViewModel(array('countriesList'=>$countriesList,'imageUrl'=>$this->filesUrl(),'tourType'=>$tourType));
    }
    
    public function cityListAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TOUR_TALES_PAGE);

        $tour = $this->params()->fromRoute('tour', '');
        $country = $this->params()->fromRoute('countryName', '');
        if (!$tour || !$country)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $tourName=str_replace("-","",$tour);
        $tourName=strtolower($tourName);
        $tourType=(array_key_exists($tourName,\Admin\Model\PlacePrices::tour_type_text))?\Admin\Model\PlacePrices::tour_type_text[$tourName]:'';
        $countryName=str_replace("-"," ",$country);
        $countryName=strtolower($countryName);
        $countryId='';
        $stateId='';
          if($tourType==\Admin\Model\PlacePrices::tour_type_India_tour || $tourType==\Admin\Model\PlacePrices::tour_type_Spiritual_tour)
          {
              $stateId=$this->statesTable()->getStateId($countryName);
          }else{
              $countryId=$this->countriesTable()->getCountryId($countryName);
          }


        if(!$tourType || (!$countryId && !$stateId) )
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $citiesList=$this->citiesTable()->getActiveCitiesList(array('limit'=>-1,'tour_type'=>$tourType,'country_id'=>$countryId,'state_id'=>$stateId));

        return new ViewModel(array('citiesList'=>$citiesList,'countryName'=>$countryName,'tourType'=>$tourType,'imageUrl'=>$this->filesUrl()));
    }
    
    public function placesListAction()
    {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TOUR_TALES_PAGE);

        $tour = $this->params()->fromRoute('tour', '');
        $city = $this->params()->fromRoute('cityName', '');
        if (!$tour || !$city)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }

        $tourName=str_replace("-","",$tour);
        $tourName=strtolower($tourName);
        $tourType=(array_key_exists($tourName,\Admin\Model\PlacePrices::tour_type_text))?\Admin\Model\PlacePrices::tour_type_text[$tourName]:'';
        $cityName=str_replace("-"," ",$city);
        $cityName=strtolower($cityName);
        $cityId=$this->citiesTable()->getCityId($cityName);

        $stateId='';

        if(!$tourType || !$cityId )
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $placesList=$this->tourismPlacesTable()->getActiveTourismPlacesList(array('limit'=>-1,'tour_type'=>$tourType,'city_id'=>$cityId));

         $cityInfo=$this->citiesTable()->getCityInfo($cityId);
                           /* echo '<pre>';
                            print_r($placesList);exit;*/
        return new ViewModel(array('placesLists'=>$placesList,'cityInfo'=>$cityInfo,'cityName'=>$cityName,'tourType'=>$tourType,'imageUrl'=>$this->filesUrl()));
    }

    public function stateListAction()
    {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TOUR_TALES_PAGE);

        $tour = $this->params()->fromRoute('tour', '');

        if (!$tour )
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }

        $tourName=str_replace("-","",$tour);
        $tourName=strtolower($tourName);
        $tourType=(array_key_exists($tourName,\Admin\Model\PlacePrices::tour_type_text))?\Admin\Model\PlacePrices::tour_type_text[$tourName]:'';

        $stateId='';
        if(!$tourType)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $statesList=$this->statesTable()->getActiveStatesListUser(array('limit'=>-1,'tour_type'=>$tourType));

        return new ViewModel(array('stateList'=>$statesList,'tourType'=>$tourType,'imageUrl'=>$this->filesUrl()));
    }

    public function bookingDetailsAction()
    {

        $view=new ViewModel();
        $view->setTerminal(true);
        return $view;
    }
    
    public function helpAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_HELP_PAGE);

        return new ViewModel();
    }

    public function twisttAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel();
    }
    public function twisttaeAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel();
    }
    public function twisttseAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel();
    }

    public function tbeHomeAction() {
        if(!$this->getLoggedInTbeId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userId = $this->getLoggedInTbeId();
        /* $taId = $this->tbeDetailsTable()->getField(array("user_id"=>$userId, "status"=>'1'), "ta_id");
        $taDetails = $this->taDetailsTable()->getTaDetails($taId); */
        $tbeMobile = $this->tbeDetailsTable()->getField(array("user_id"=>$userId, 'status'=>\Admin\Model\TbeDetails::TBE_Active, 'active'=>\Admin\Model\TbeDetails::TBE_Enabled), "tbe_mobile");
        $tbeResp = $this->tbeDetailsTable()->getMobileTas($tbeMobile);
        $upcList = array();
        foreach($tbeResp as $tbe){
            $list = $this->tbeDetailsTable()->getUPCList($tbeMobile, $tbe["ta_id"]);
            $taDetails = $this->taDetailsTable()->getTaDetails($tbe["ta_id"]);
            $ulist = array();
            foreach($list as $l){
                $tal['taLogo'] = $this->filesUrl() . $taDetails[0]['file_path'];
                $l = array_merge($l, $tal);
                $ulist[] = $l;
            }
            $upcList = array_merge($upcList, $ulist);
        }
        //$upcList = $this->tbeDetailsTable()->getUPCList($tbeMobile); //($taId);
        $mccList = $this->countriesTable()->countryCodesList();
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        //return new ViewModel(array('taDetails'=>$taDetails, 'upcList'=>$upcList, 'imageUrl'=>$this->filesUrl(), 'userId'=>$userId));
        return new ViewModel(array('upcList'=>$upcList, 'userId'=>$userId, 'mccList'=>$mccList));
    }

    public function seHomeAction() {
        if(!$this->getLoggedInSeId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userId = $this->getLoggedInSeId();
        $seDetails = $this->taConsultantDetailsTable()->getTaConsultantDetails($userId);
        // pull data from se transactions and return
        $seData=$this->TaConsultantTransactionsTable()->getTaConsTxAdmin(array('ta_cons_id'=>$userId,'limit'=>10,'offset'=>0));
        $totalCount=$this->TaConsultantTransactionsTable()->getTaConsTxAdmin(array('ta_cons_id'=>$userId), 1);
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel(array('seDetails'=>$seDetails, 'userId'=>$userId, 'seData'=>$seData, 'totalCount'=>$totalCount));
    }
    public function loadSeDataAction(){
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();  //var_dump($request);exit;          
            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $userId = $this->getLoggedInSeId();
            $seDetails = $this->taConsultantDetailsTable()->getTaConsultantDetails($userId);

            if(isset($request['page_number']))
            {
                $userIdString = $request['uid'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $seId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0; 
                //echo $seId; exit;

                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }
            $searchData['ta_cons_id']=$userId;
            $totalCount=0;
            
            if($type && $type=='search')
            {
                $totalCount=$this->TaConsultantTransactionsTable()->getTaConsTxAdmin($searchData, 1);
            }

            $seData=$this->TaConsultantTransactionsTable()->getTaConsTxAdmin(array('ta_cons_id'=>$userId, 'limit'=>10,'offset'=>0));
            $view = new ViewModel(array('seDetails'=>$seDetails, 'userId'=>$userId, 'seData'=>$seData, 'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function twisttPasswordAction() {
        if(!$this->getLoggedInTbeId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel(array('userId'=>$userId));
    }

    public function twisttsePasswordAction() {
        if(!$this->getLoggedInSeId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel(array('userId'=>$userId));
    }

    public function twisttForgotPasswordAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel();
    }

    public function twisttseForgotPasswordAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel();
    }

    public function tfpSendOtpAction(){
        $request = $this->getRequest()->getPost();
        $mobile=$request["mobile"];
        if($mobile=='' || $mobile==null)
        {
            return new JsonModel(array("success"=>false,"message"=>"mobile number is missing"));
        }
        // to check if the given input is mobile number
        $tbe=$this->tbeDetailsTable()->getFields(array('tbe_mobile'=>$mobile), array('user_id', 'mobile_country_code'));
        if($tbe){ // valid mobile numner => sms otp to registered mobile number
            if($tbe['mobile_country_code'] != "91"){ // if foreign user => ask for email
                return new JsonModel(array("success"=>false,"message"=>"Please enter the registered email id if you are a foreign user."));
            }else{ // if Indian user => sms otp
                $data=array("tbe_id"=>$tbe['user_id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\TwisttOtp::Not_verifed);
                $update = $this->twisttOtpTable()->updateData(array('status' => \Admin\Model\TwisttOtp::Is_verifed), $data);
                if(strpos($_SERVER[REQUEST_URI], "/beta/public/"))
                    $otp="1111";
                else
                    $otp = $this->generateOtp();
                $insertData=array("tbe_id"=>$tbe['user_id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\Otp::Not_verifed,'otp'=>$otp);
                $this->twisttOtpTable()->insertData($insertData);
                $response = $this->sendOtpSms($tbe['mobile_country_code'].$mobile, $otp);
                return  new JsonModel(array('success'=>true,'message'=>'Please enter the otp received on your registered mobile number'));
            }
        }else{
            // to check if the given input is email id
            $tbe=$this->tbeDetailsTable()->getFields(array('tbe_email'=>$mobile), array('user_id', 'tbe_email'));
            if($tbe){ // valid email id => email otp to registered email id
                $data=array("tbe_id"=>$tbe['user_id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\TwisttOtp::Not_verifed);
                $update = $this->twisttOtpTable()->updateData(array('status' => \Admin\Model\TwisttOtp::Is_verifed), $data);
                if(strpos($_SERVER[REQUEST_URI], "/beta/public/"))
                    $otp="1111";
                else
                    $otp = $this->generateOtp();
                $insertData=array("tbe_id"=>$tbe['user_id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\Otp::Not_verifed,'otp'=>$otp);
                $this->twisttOtpTable()->insertData($insertData);
                $response = $this->mailSTTUserNoAttachment($tbe['tbe_email'], "OTP to reset password" , 'twistt-user', $otp);
                return  new JsonModel(array('success'=>true,'message'=>'Please enter the otp received on your registered email id'));
            }else{ // the given input is neither valid mobile number nor valid email id
                return new JsonModel(array("success"=>false,"message"=>"Please enter the registered mobile number or email id"));
            }
        }
    }

    public function tfpVerifyOtpAction(){
        $request = $this->getRequest()->getPost();
        $otp=$request["otp"];
        $mobile=$request["mobile"];
        if($otp=='' || $otp==null)
        {
            return new JsonModel(array("success"=>false,"message"=>"Otp not entered"));
        }
        $tbe=$this->tbeDetailsTable()->getFields(array('tbe_mobile'=>$mobile), array('user_id', 'mobile_country_code'));
        if(!$tbe){
            $tbe=$this->tbeDetailsTable()->getFields(array('tbe_email'=>$mobile), array('user_id', 'tbe_email'));
        }
        if($tbe){
            $data=array("otp"=>$otp,"tbe_id"=>$tbe['user_id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\TwisttOtp::Not_verifed);
            $verfiy=$this->twisttOtpTable()->verfiy($data);
            if($verfiy){
                $updateResponse=$this->twisttOtpTable()->updateData(array('status'=>\Admin\Model\Otp::Is_verifed),$data);
                return new JsonModel(array("success"=>true,"message"=>"Verification successful"));
            }else{
                return new JsonModel(array("success"=>false,"message"=>"Invalid Otp"));
            }
        }else{
            return new JsonModel(array("success"=>false,"message"=>"Something went wrong. Please try again."));
        }
    }

    public function tsfpSendOtpAction(){
        $request = $this->getRequest()->getPost();
        $mobile=$request["mobile"];
        if($mobile=='' || $mobile==null)
        {
            return new JsonModel(array("success"=>false,"message"=>"mobile number is missing"));
        }
        $se=$this->taConsultantDetailsTable()->getFields(array('mobile'=>$mobile), array('id', 'mobile_country_code'));
        if($se){
            if($se['mobile_country_code'] != "91"){ // if foreign user => ask for email
                return new JsonModel(array("success"=>false,"message"=>"Please enter the registered email id if you are a foreign user."));
            }else{
                $data=array("se_id"=>$se['id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\TwisttOtp::Not_verifed);
                $update = $this->twisttOtpTable()->updateData(array('status' => \Admin\Model\TwisttOtp::Is_verifed), $data);
                if(strpos($_SERVER[REQUEST_URI], "/beta/public/"))
                    $otp="1111";
                else
                    $otp = $this->generateOtp();
                $insertData=array("se_id"=>$se['id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\Otp::Not_verifed,'otp'=>$otp);
                $this->twisttOtpTable()->insertData($insertData);
                $response = $this->sendOtpSms($se['mobile_country_code'].$mobile, $otp);
                return  new JsonModel(array('success'=>true,'message'=>'Please enter the otp received'));
            }
        }else{
            // to check if the given input is email id
            $se=$this->taConsultantDetailsTable()->getFields(array('email'=>$mobile), array('id', 'email'));
            if($se){ // valid email id => email otp to registered email id
                $data=array("se_id"=>$se['id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\TwisttOtp::Not_verifed);
                $update = $this->twisttOtpTable()->updateData(array('status' => \Admin\Model\TwisttOtp::Is_verifed), $data);
                if(strpos($_SERVER[REQUEST_URI], "/beta/public/"))
                    $otp="1111";
                else
                    $otp = $this->generateOtp();
                $insertData=array("se_id"=>$se['id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\Otp::Not_verifed,'otp'=>$otp);
                $this->twisttOtpTable()->insertData($insertData);
                $response = $this->mailSTTUserNoAttachment($se['email'], "OTP to reset password" , 'twistt-user', $otp);
                return  new JsonModel(array('success'=>true,'message'=>'Please enter the otp received on your registered email id'));
            }else{ // the given input is neither valid mobile number nor valid email id
                return new JsonModel(array("success"=>false,"message"=>"Please enter the registered mobile number or email id"));
            }
        }
    }

    public function tsfpVerifyOtpAction(){
        $request = $this->getRequest()->getPost();
        $otp=$request["otp"];
        $mobile=$request["mobile"];
        if($otp=='' || $otp==null)
        {
            return new JsonModel(array("success"=>false,"message"=>"Otp not entered"));
        }
        $se=$this->taConsultantDetailsTable()->getFields(array('mobile'=>$mobile), array('id', 'mobile_country_code'));
        if(!$se){
            $se=$this->taConsultantDetailsTable()->getFields(array('email'=>$mobile), array('id', 'email'));
        }
        if($se){
            $data=array("otp"=>$otp,"se_id"=>$se['id'],"otp_type"=>\Admin\Model\TwisttOtp::FORGOT_OTP,"status"=>\Admin\Model\TwisttOtp::Not_verifed);
            $verfiy=$this->twisttOtpTable()->verfiy($data);
            if($verfiy){
                $updateResponse=$this->twisttOtpTable()->updateData(array('status'=>\Admin\Model\Otp::Is_verifed),$data);
                return new JsonModel(array("success"=>true,"message"=>"Verification successful"));
            }else{
                return new JsonModel(array("success"=>false,"message"=>"Invalid Otp"));
            }
        }else{
            return new JsonModel(array("success"=>false,"message"=>"Something went wrong. Please try again."));
        }
    }

    public function twisttResetPasswordAction(){
        $request = $this->getRequest()->getPost();
        $newPwd = $request['new_password'];
        $mobile=$request["mobile"];
        $tbeId=$this->tbeDetailsTable()->getField(array('tbe_mobile'=>$mobile), 'user_id');
        if($tbeId){
            $aes=new Aes();
            $encodeContent = $aes->encrypt($newPwd);
            $encodeString = $encodeContent['password'];
            $hash = $encodeContent['hash'];
            $date = date("Y-m-d H:i:s");
            //$tbeOldPwdDetails = $this->tbeDetailsTable()->getTbeDetails($tbeId);
            $tbeOldPwdDetails = $this->tbeLoginTable()->getLoginDetails($mobile);
            $oldPwdData = array('tbe_id'=>$tbeId,'old_pwd'=>$tbeOldPwdDetails['pwd'],'old_hash'=>$tbeOldPwdDetails['hash'], 'created_at'=>$date, 'updated_at'=>$date);
            $currentPasswordInsert=$this->tbeOldPasswordsTable()->addTbeOldPassword($oldPwdData);           
            
            if($currentPasswordInsert)
            {
                $currentPasswordUpdate=$this->tbeLoginTable()->setTbeLogin(array('pwd'=>$encodeString, 'hash'=>$hash),array('login_id'=>$mobile));
                /*  $currentPasswordUpdate=$this->tbeLoginTable()->setTbeLogin(array('pwd'=>$encodeString, 'hash'=>$hash),array('user_id'=>$tbeId));
                $currentPasswordUpdate=$this->tbeDetailsTable()->setTbeDetails(array('pwd'=>$encodeString, 'hash'=>$hash),array('user_id'=>$tbeId));*/
                if($currentPasswordUpdate)
                    return new JsonModel(array('success'=>true,'message'=>'Password reset successful'));
                else
                    return new JsonModel(array('success'=>false,'message'=>'Password reset attempt not successful'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'Something went wrong. Try again after sometime'));
            }
        }else{
            return new JsonModel(array("success"=>false,"message"=>"User not found"));
        }
    }

    public function twisttseResetPasswordAction(){
        $request = $this->getRequest()->getPost();
        $newPwd = $request['new_password'];
        $mobile=$request["mobile"];
        $seId=$this->taConsultantDetailsTable()->getField(array('mobile'=>$mobile), 'id');
        if($seId){
            $aes=new Aes();
            $encodeContent = $aes->encrypt($newPwd);
            $encodeString = $encodeContent['password'];
            $hash = $encodeContent['hash'];
            $date = date("Y-m-d H:i:s");
            $seOldPwdDetails = $this->taConsultantDetailsTable()->getTaConsultantDetails($seId);
            $oldPwdData = array('se_id'=>$seId,'old_pwd'=>$seOldPwdDetails['pwd'],'old_hash'=>$seOldPwdDetails['hash'], 'created_at'=>$date, 'updated_at'=>$date);
            $currentPasswordInsert=$this->seOldPasswordsTable()->addSeOldPassword($oldPwdData);

            if($currentPasswordInsert)
            {
                $currentPasswordUpdate=$this->taConsultantDetailsTable()->setTaConsultantDetails(array('pwd'=>$encodeString, 'hash'=>$hash),array('id'=>$seId));
                return new JsonModel(array('success'=>true,'message'=>'Password reset successful'));

            }else{
                return new JsonModel(array('success'=>false,'message'=>'Something went wrong try again after sometime'));

            }
        }else{
            return new JsonModel(array("success"=>false,"message"=>"User not found"));
        }
    }

    public function twisttTbeAction() {
        if(!$this->getLoggedInTbeId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userId = $this->getLoggedInTbeId();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            $tbeId = $userId;
        }else{
            $userIdString = rtrim($paramId, "=");
            $userIdString = base64_decode($userIdString);
            $userIdString = explode("=", $userIdString);
            $tbeId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
            if(!$tbeId)
                $tbeId = $userId;
        }

        $tbeMobile=$this->tbeDetailsTable()->getField(array("user_id"=>$userId, 'status'=>\Admin\Model\TbeDetails::TBE_Active, 'active'=>\Admin\Model\TbeDetails::TBE_Enabled), "tbe_mobile");
        //$taId = $this->tbeDetailsTable()->getField(array("user_id"=>$userId, "status"=>\Admin\Model\TbeDetails::TBE_Active, 'active'=>\Admin\Model\TbeDetails::TBE_Enabled), "ta_id");
        $tbeResp = $this->tbeDetailsTable()->getMobileTas($tbeMobile);
        //$tbeList = $this->tbeDetailsTable()->getTasTbeList($taId);
        $tbeList = array();
        foreach($tbeResp as $tbe){
            if($tbe['role'] == \Admin\Model\TbeDetails::Twistt_TA_role){
                $list = $this->tbeDetailsTable()->getTasTbeList($tbe["ta_id"]);
            }else{
                $list = $this->tbeDetailsTable()->getTasMTbeList($tbeMobile, $tbe["ta_id"]);
            }
            $tbeList = array_merge($tbeList, $list);
        }

        foreach($tbeList as $tbe){
            if($tbe['user_id'] == $tbeId){
                $tmob = $tbe['tbe_mobile'];
                $taId = $tbe['ta_id'];
                $ta_name = $tbe['ta_name'];
            }
        }
        
        $tList = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 0, array('tbe_mobile'=>$tmob, 'ta_id'=>$taId));
        $totalCount = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 1, array('tbe_mobile'=>$tmob, 'ta_id'=>$taId));
        $tbeName=$this->tbeDetailsTable()->getField(array("user_id"=>$tbeId), "tbe_name");
        $taDetails = $this->taDetailsTable()->getTaDetails($taId);
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        $pdArr = array('userId'=>$userId, 'tbeId'=>$tbeId, 'tbeName'=>$tbeName . ' under ' .$ta_name); 
        return new ViewModel(array('tList'=>$tList,'totalCount'=>$totalCount, 'tbeList'=>$tbeList, 'pdArr'=>$pdArr,'taDetails'=>$taDetails, 'imageUrl'=>$this->filesUrl()));
    }

    public function loadTouristsListAction() {
        if(!$this->getLoggedInTbeId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $userId = $this->getLoggedInTbeId();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userIdString = rtrim($paramId, "=");
        $userIdString = base64_decode($userIdString);
        $userIdString = explode("=", $userIdString);
        $tbeId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;

        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();

            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);

                if(isset($filterData['tourist_name']))
                { 
                    if(isset($filterData['tourist_name']['text']))
                    { 
                        $searchData['tourist_name']=$filterData['tourist_name']['text'];
                    }
                    if(isset($filterData['tourist_name']['order']) && $filterData['tourist_name']['order'])
                    {
                        $searchData['tourist_name_order']=$filterData['tourist_name']['order'];
                    }
                }
                if(isset($filterData['tourist_mobile']))
                {
                    if(isset($filterData['tourist_mobile']['text']))
                    {
                        $searchData['tourist_mobile']=$filterData['tourist_mobile']['text'];
                    }
                    if(isset($filterData['tourist_mobile']['order']) && $filterData['tourist_mobile']['order'])
                    {
                        $searchData['tourist_mobile_order']=$filterData['tourist_mobile']['order'];
                    }
                }
                if(isset($filterData['upc']))
                {
                    if(isset($filterData['upc']['text']))
                    {
                        $searchData['upc']=$filterData['upc']['text'];
                    }
                    if(isset($filterData['upc']['order']) && $filterData['upc']['order'])
                    {
                        $searchData['upc_order']=$filterData['upc']['order'];
                    }
                }
                if(isset($filterData['travel_date']))
                {
                    if(isset($filterData['travel_date']['text']))
                    {
                        $searchData['travel_date']=$filterData['travel_date']['text'];
                    }
                    if(isset($filterData['travel_date']['order']) && $filterData['travel_date']['order'])
                    {
                        $searchData['travel_date_order']=$filterData['travel_date']['order'];
                    }
                }
            }

            if(isset($request['page_number']))
            {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }

            $totalCount=0;
            //var_dump($searchData);exit;
            $tbeResp = $this->tbeDetailsTable()->getFields(array("user_id"=>$tbeId), array("tbe_mobile", "ta_id"));
            if($type && $type=='search')
            {
                $totalCount=$this->taSdsTable()->getTBETouristsdetails($searchData, 1, array('tbe_mobile'=>$tbeResp['tbe_mobile'], 'ta_id'=>$tbeResp['ta_id']));
            }
            $tList = $this->taSdsTable()->getTBETouristsdetails($searchData, 0, array('tbe_mobile'=>$tbeResp['tbe_mobile'], 'ta_id'=>$tbeResp['ta_id']));
        }else{
            $tbeResp = $this->tbeDetailsTable()->getFields(array("user_id"=>$userId), array("tbe_mobile", "ta_id"));
            $tList = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 0, array('tbe_mobile'=>$tbeResp['tbe_mobile'], 'ta_id'=>$tbeResp['ta_id']));
            $totalCount = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 1, array('tbe_mobile'=>$tbeResp['tbe_mobile'], 'ta_id'=>$tbeResp['ta_id']));
        }
        $tbeName = $this->tbeDetailsTable()->getField(array("user_id"=>$tbeId), "tbe_name");
        $ta_name = $this->taDetailsTable()->getField(array('id'=>$tbeResp['ta_id']), 'ta_name');
        $pdArr = array('userId'=>$userId, 'tbeId'=>$tbeId, 'tbeName'=>$tbeName . ' under ' .$ta_name); 

        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel(array('tList'=>$tList,'totalCount'=>$totalCount, 'offset' => $offset,'type'=>$type, 'pdArr'=>$pdArr));
    }

    public function twisttUpcAction() {
        if(!$this->getLoggedInTbeId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userId = $this->getLoggedInTbeId();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            $purchaseId = $paramId;
        }else{
            $userIdString = rtrim($paramId, "=");
            $userIdString = base64_decode($userIdString);
            $userIdString = explode("=", $userIdString);
            $purchaseId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
        }

        //$taId = $this->tbeDetailsTable()->getField(array("user_id"=>$userId, "status"=>'1'), "ta_id");
        $tbeMobile = $this->tbeDetailsTable()->getField(array("user_id"=>$userId, 'status'=>\Admin\Model\TbeDetails::TBE_Active, 'active'=>\Admin\Model\TbeDetails::TBE_Enabled), "tbe_mobile");
        $tbeResp = $this->tbeDetailsTable()->getMobileTas($tbeMobile);
        $upcList = array();
        foreach($tbeResp as $tbe){
            if($tbe['role'] == \Admin\Model\TbeDetails::Twistt_TA_role){
                $list = $this->tbeDetailsTable()->getUPCList($tbeMobile, $tbe["ta_id"]);
                $upcList = array_merge($upcList, $list);
            }
        }

        /* $upcList = $this->tbeDetailsTable()->getUPCList($tbeMobile); */
        if($purchaseId){
            foreach($upcList as $ul){
                if($ul['id'] == $purchaseId)
                {
                    $upc = $ul['upc'];
                }
            }
        }else{
            $upc = $upcList[0]['upc'];
            $purchaseId = $upcList[0]['id'];
        } 

        $tList = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 0, array('upc'=>$upc));
        $totalCount = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 1, array('upc'=>$upc));
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        $pdArr = array('userId'=>$userId, 'id'=>$purchaseId, 'upc'=>$upc); 
        return new ViewModel(array('tList'=>$tList,'totalCount'=>$totalCount, 'upcList'=>$upcList, 'pdArr'=>$pdArr));
    }

    public function loadUpcTouristsListAction() {
        if(!$this->getLoggedInTbeId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $userId = $this->getLoggedInTbeId();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userIdString = rtrim($paramId, "=");
        $userIdString = base64_decode($userIdString);
        $userIdString = explode("=", $userIdString);
        $purchaseId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;

        $upc = $this->taPurchasesTable()->getField(array("id"=>$purchaseId, "status"=>'1'), "upc");

        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();

            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);

                if(isset($filterData['tourist_name']))
                { 
                    if(isset($filterData['tourist_name']['text']))
                    { 
                        $searchData['tourist_name']=$filterData['tourist_name']['text'];
                    }
                    if(isset($filterData['tourist_name']['order']) && $filterData['tourist_name']['order'])
                    {
                        $searchData['tourist_name_order']=$filterData['tourist_name']['order'];
                    }
                }
                if(isset($filterData['tourist_mobile']))
                {
                    if(isset($filterData['tourist_mobile']['text']))
                    {
                        $searchData['tourist_mobile']=$filterData['tourist_mobile']['text'];
                    }
                    if(isset($filterData['tourist_mobile']['order']) && $filterData['tourist_mobile']['order'])
                    {
                        $searchData['tourist_mobile_order']=$filterData['tourist_mobile']['order'];
                    }
                }
                if(isset($filterData['tbe_id']))
                {
                    if(isset($filterData['tbe_id']['text']))
                    {
                        $searchData['tbe_id']=$filterData['tbe_id']['text'];
                    }
                    if(isset($filterData['tbe_id']['order']) && $filterData['tbe_id']['order'])
                    {
                        $searchData['tbe_id_order']=$filterData['tbe_id']['order'];
                    }
                }
                if(isset($filterData['travel_date']))
                {
                    if(isset($filterData['travel_date']['text']))
                    {
                        $searchData['travel_date']=$filterData['travel_date']['text'];
                    }
                    if(isset($filterData['travel_date']['order']) && $filterData['travel_date']['order'])
                    {
                        $searchData['travel_date_order']=$filterData['travel_date']['order'];
                    }
                }
            }

            if(isset($request['page_number']))
            {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }

            $totalCount=0;
            //var_dump($searchData);exit;
            if($type && $type=='search')
            {
                $totalCount=$this->taSdsTable()->getTBETouristsdetails($searchData, 1, array('upc'=>$upc));
            }
            $tList = $this->taSdsTable()->getTBETouristsdetails($searchData, 0, array('upc'=>$upc));
        }
        else{
            $tList = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 0, array('upc'=>$upc));
            $totalCount = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 1, array('upc'=>$upc));
        }
        $pdArr = array('userId'=>$userId, 'upc'=>$upc); 

        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TWISTT);
        return new ViewModel(array('tList'=>$tList,'totalCount'=>$totalCount, 'offset' => $offset,'type'=>$type, 'pdArr'=>$pdArr));
    }

    public function contributorAction() {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_CONTRIBUTOR_PAGE);

        return new ViewModel();
    }

    public function sendContactAction()
    {
       if ($this->getRequest()->isXmlHttpRequest()) {
           $request = $this->getRequest()->getPost();
           $name=$request['name'];
           $mobile=$request['mobile'];
           $email=$request['email'];
           $message=$request['message'];
       $this->sendmail('susritourtales@gmail.com','Contact From '.$name,'send-email',array('user_name'=>$name,'email'=>$email,'mobile'=>$mobile,'message'=>$message,'method'=>'Contact'));
       //$this->sendmail('banu.nagumothu@gmail.com','Concat From '.$name,'send-email',array('user_name'=>$name,'email'=>$email,'mobile'=>$mobile,'created_at'=>date("Y-m-d H:i"),'message'=>$message,'method'=>'Concat'));
             return new JsonModel(array('success'=>true,'message'=>'Your Enquiry has been shared successfully'));
       }
    }

    public function sendContributorAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $name=$request['name'];
            $mobile=$request['mobile'];
            $email=$request['email'];
            $message=$request['message'];
            $this->sendmail('susritourtales@gmail.com','Enquiry to be a contributor From '.$name,'send-email',array('user_name'=>$name,'email'=>$email,'mobile'=>$mobile,'message'=>$message,'method'=>'Contributor Request'));
          //  $this->sendmail('banu.nagumothu@gmail.com','Contributor Request From '.$name,'send-email',array('user_name'=>$name,'email'=>$email,'mobile'=>$mobile,'created_at'=>date("Y-m-d H:i"),'message'=>$message,'method'=>'Contributor Request'));
            return new JsonModel(array('success'=>true,'message'=>'Thanks for your interest, we will contact you soon'));
        }
    }
    public function seasonalPlacesListAction()
    {
        $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TOUR_TALES_PAGE);
        $paramId = $this->params()->fromRoute('id', '');
        $dataIdString = rtrim($paramId, "=");
        $dataIdString = base64_decode($dataIdString);
        $dataIdString = explode("=", $dataIdString);
        $dataId = array_key_exists(1, $dataIdString) ? $dataIdString[1] : 0;
        $details=$this->seasonalSpecialsTable()->seasonalDetails($dataId);
                   /*echo '<pre>';
                   print_r($details);
                   exit;*/
        return new ViewModel(array('places'=>$details,'imageUrl'=>$this->filesUrl()));
    }
}
