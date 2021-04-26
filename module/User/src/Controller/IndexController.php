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
            print_r($cityTourList);
            exit; */
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
