<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 28/8/19
 * Time: 4:06 PM
 */

namespace Admin\Controller;


use Application\Controller\BaseController;
use Application\Handler\CronJob;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class ToursController  extends BaseController
{
     public function spiritualTourListAction()
     {
        /* $cronPath = $this->getBaseUrl() . "/activate-job";

         $cronSchedule = "0 4 * * *" ;
          $cron=new CronJob();
        $cron->addJob($cronSchedule . " /usr/bin/curl -o temp.txt " . $cronPath . ' > /var/log/cron.log 2>&1');*/
         if(!$this->getLoggedInUserId())
         {
             return $this->redirect()->toUrl($this->getBaseUrl());
         }

         $placesList=$this->placePriceTable()->getPlacesList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_Spiritual_tour,'limit'=>10,'offset'=>0));
         $totalCount=$this->placePriceTable()->getPlacesListCount(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_Spiritual_tour));

         return new ViewModel(array('placesList'=>$placesList,'totalCount'=>count($totalCount)));
     }
    public function addSpiritualTourAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $place = $request['place_name'];
            $stateId = $request['state_id'];
            $cityId = $request['city_id'];
            $price=$request['price'];

            if($stateId=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please select state"));
            }

            $where=array('state_id'=>$stateId,'tour_type'=>\Admin\Model\PlacePrices::tour_type_Spiritual_tour);

            if($cityId)
            {
                $where['city_id']=$cityId;
            }

            if(is_null($place))
            {
                $place='';
            }

            $checkCountryAdded=$this->placePriceTable()->checkPriceAdded($where);
            $data=array('tour_type'=>\Admin\Model\PlacePrices::tour_type_Spiritual_tour,'status'=>1,'state_id'=>$stateId);


            if($checkCountryAdded && $place)
            {
                $placesList=array_unique(array_merge(explode(',',$place),explode(",",$checkCountryAdded['place_id'])));
                $data['place_id']=",".implode(",",$placesList).",";
            }else if($place) {
                $data['place_id']=",".$place.",";
            }

            if($cityId)
            {
                $data['city_id']=$cityId;
            }else
            {
                $data['city_id']=0;
            }
            if(!$place)
            {


                $data['place_id']='';
            }
            $countryId=$this->countriesTable()->getField(array('country_name'=>'india','status'=>1),'id');
            $data['country_id']=$countryId;
            $success=true;
            if(!count($checkCountryAdded))
            {
                $saveData = $this->placePriceTable()->addPlacePrice($data);
                if(!$saveData['success'])
                {
                    $success=false;
                }
            }else{
                $updateData = $this->placePriceTable()->updatePlacePrice($data,array('place_price_id'=>$checkCountryAdded['place_price_id']));
                if(!$updateData)
                {
                    $success=false;
                }
            }
            if($success)
            {
                return new JsonModel(array('success'=>true , 'message'=>'Added to Spiritual Tour Successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to add Spiritual tour'));
            }
            /* $saveData = $this->placePriceTable()->addPlacePrice(array('place_id'=>$place,'tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'status'=>1));
             if($saveData['success'])
             {
                 return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
             }else
             {
                 return new JsonModel(array('success'=>false,'message'=>'unable to add spiritual tour'));
             }*/
        }

        if(!$this->getLoggedInUserId())
        {

            return $this->redirect()->toUrl($this->getBaseUrl());

        }

       // $statesList=$this->statesTable()->getIndiaStates(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_Spiritual_tour));
         $statesList=$this->statesTable()->getActiveIndianStates();

        return new ViewModel(array('statesList'=>$statesList));

    }
    public function loadSpiritualTourListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
             $offset=0;
            $searchData=array('limit'=>10,'offset'=>0,'tour_type'=>\Admin\Model\PlacePrices::tour_type_Spiritual_tour);
            $type=$request['type'];
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                if(isset($filterData['country']))
                {
                    if(isset($filterData['country']['text']) && $filterData['country']['text'])
                    {
                        $searchData['country']=$filterData['country']['text'];
                    }
                    if(isset($filterData['country']['order']) && $filterData['country']['order'])
                    {
                        $searchData['country_order']=$filterData['country']['order'];
                    }
                }
                if(isset($filterData['state']))
                {
                    if(isset($filterData['state']['text']) && $filterData['state']['text'])
                    {
                        $searchData['state']=$filterData['state']['text'];
                    }
                    if(isset($filterData['state']['order']) && $filterData['state']['order'])
                    {
                        $searchData['state_order']=$filterData['state']['order'];
                    }
                }
                if(isset($filterData['city']))
                {

                    if(isset($filterData['city']['text']) && $filterData['city']['text'])
                    {
                        $searchData['city']=$filterData['city']['text'];
                    }
                    if(isset($filterData['city']['order']) && $filterData['city']['order'])
                    {
                        $searchData['city_order']=$filterData['city']['order'];
                    }

                }
                if(isset($filterData['place_name']))
                {

                    if(isset($filterData['place_name']['text']) && $filterData['place_name']['text'])
                    {
                        $searchData['place_name']=$filterData['place_name']['text'];
                    }
                    if(isset($filterData['place_name']['order']) && $filterData['place_name']['order'])
                    {
                        $searchData['place_name_order']=$filterData['place_name']['order'];
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

            if($type && $type=='search')
            {
                $totalCountList = $this->placePriceTable()->getPlacesListCount($searchData);

                if(count($totalCountList))
                {
                    $totalCount=count($totalCountList);
                }
            }
            $placesList = $this->placePriceTable()->getPlacesList($searchData);
            $view = new ViewModel(array('tourismList' => $placesList, 'offset' => $offset,"type"=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function cityTourListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $placesList=$this->placePriceTable()->getPlacesList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour,'limit'=>10,'offset'=>0));
        $totalCount=$this->placePriceTable()->getPlacesListCount(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour));

        return new ViewModel(array('placesList'=>$placesList,'totalCount'=>count($totalCount)));
    }
    public function addCityTourAction()
    {

        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $countryId=$request['country_id'];
            $cityId=$request['city_id'];
            $place=$request['place_name'];
             $price=0; //$request['price'];
            if($countryId=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please select country"));
            }

            $where=array('country_id'=>$countryId,'tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour);
            if(is_null($place))
            {
                $place='';
            }
            if($cityId)
            {
                $where['city_id']=$cityId;
            }
            $checkCountryAdded=$this->placePriceTable()->checkPriceAdded($where);

            if($cityId && count($checkCountryAdded))
            {
               return new JsonModel(array('success'=>false,'message'=>'city already added'));
            }
            $data=array('tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour,'status'=>1,'country_id'=>$countryId,'price'=>$price);


            if($checkCountryAdded)
            {
                $data['place_id']=implode(",",array_unique(explode(',',$place)));
            }else if($place){
                $data['place_id']=",".$place.",";
            }

            if($cityId)
            {
                $data['city_id']=$cityId;
            }else
            {
                $data['city_id']=0;
            }
            if($place)
            {
                $data['place_id']=",".$place.",";
            }else
            {
                $data['place_id']='';
            }
            $success=true;
            if(!count($checkCountryAdded))
            {
                $saveData = $this->placePriceTable()->addPlacePrice($data);
                if(!$saveData['success'])
                {
                    $success=false;
                }
            }else{
                $updateData = $this->placePriceTable()->updatePlacePrice($data,array('place_price_id'=>$checkCountryAdded['place_price_id']));
                if(!$updateData)
                {
                    $success=false;
                }
            }
            if($success)
            {
                return new JsonModel(array('success'=>true , 'message'=>'Added to City Tour Successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to add city tour'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
       // $countryList=$this->countriesTable()->getActiveCountriesListAdmin(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour));
        $countryList=$this->countriesTable()->getActiveCountries();

        return new ViewModel(array('countryList'=>$countryList));
    }
    public function loadCityTourListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $limit = 10;

            $offset=0;
            $searchData=array('limit'=>$limit,'offset'=>$offset,'tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour);
            $type=$request['type'];
            $filterData=$request['filter'];

            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                if(isset($filterData['country']))
                {
                    if(isset($filterData['country']['text']) && $filterData['country']['text'])
                    {
                        $searchData['country']=$filterData['country']['text'];
                    }
                    if(isset($filterData['country']['order']) && $filterData['country']['order'])
                    {
                        $searchData['country_order']=$filterData['country']['order'];
                    }
                }
                if(isset($filterData['state']))
                {
                    if(isset($filterData['state']['text']) && $filterData['state']['text'])
                    {
                        $searchData['state']=$filterData['state']['text'];
                    }
                    if(isset($filterData['state']['order']) && $filterData['state']['order'])
                    {
                        $searchData['state_order']=$filterData['state']['order'];
                    }

                }
                if(isset($filterData['city']))
                {

                    if(isset($filterData['city']['text']) && $filterData['city']['text'])
                    {
                        $searchData['city']=$filterData['city']['text'];
                    }
                    if(isset($filterData['city']['order']) && $filterData['city']['order'])
                    {
                        $searchData['city_order']=$filterData['city']['order'];
                    }

                }
                if(isset($filterData['place_name']))
                {

                    if(isset($filterData['place_name']['text']) && $filterData['place_name']['text'])
                    {
                        $searchData['place_name']=$filterData['place_name']['text'];
                    }
                    if(isset($filterData['place_name']['order']) && $filterData['place_name']['order'])
                    {
                        $searchData['place_name_order']=$filterData['place_name']['order'];
                    }

                }

            }


            if(isset($request['page_number']))
            {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }

            $totalCount=0;

            if($type && $type=='search')
            {

                $totalCountList = $this->placePriceTable()->getPlacesListCount($searchData);

                if(count($totalCountList))
                {
                    $totalCount=count($totalCountList);
                }

            }

            $placesList = $this->placePriceTable()->getPlacesList($searchData);
            $view = new ViewModel(array('tourismList' => $placesList, 'offset' => $offset,"type"=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;

        }
    }
    public function indiaTourListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
      $placesList=$this->placePriceTable()->getPlacesList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'limit'=>10,'offset'=>0));
        /*  echo '<pre>';
           print_r($placesList);
           exit;*/
      $totalCount=$this->placePriceTable()->getPlacesListCount(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour));
      return new ViewModel(array('placesList'=>$placesList,'totalCount'=>count($totalCount)));
     
    }
    public function addIndiaTourAction()
    {

        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $place = $request['place_name'];
            $stateId = $request['state_id'];
            $cityId = $request['city_id'];
            $price=$request['price'];
            if($stateId=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please select state"));
            }

            $where=array('state_id'=>$stateId,'tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour);

            if($cityId)
            {
                $where['city_id']=$cityId;
            }
            if(is_null($place))
            {
                $place='';
            }

            $checkCountryAdded=$this->placePriceTable()->checkPriceAdded($where);
            $data=array('tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'status'=>1,'state_id'=>$stateId);


            if($checkCountryAdded && $place)
            {
                $placesList=array_unique(array_merge(explode(',',$place),explode(",",$checkCountryAdded['place_id'])));
                $data['place_id']=",".implode(",",$placesList).",";
            }else if($place){
                $data['place_id']=",".$place.",";
            }

            if($cityId)
            {
                $data['city_id']=$cityId;
            }else
            {
                $data['city_id']=0;
            }
            if(!$place)
            {
                $data['place_id']='';
            }
            $countryId=$this->countriesTable()->getField(array('country_name'=>'india','status'=>1),'id');
            $data['country_id']=$countryId;
            $success=true;
            if(!count($checkCountryAdded))
            {
                $saveData = $this->placePriceTable()->addPlacePrice($data);
                if(!$saveData['success'])
                {
                    $success=false;
                }
            }else{
                $updateData = $this->placePriceTable()->updatePlacePrice($data,array('place_price_id'=>$checkCountryAdded['place_price_id']));
                if(!$updateData)
                {
                    $success=false;
                }
            }
            if($success)
            {
                return new JsonModel(array('success'=>true , 'message'=>'Added to India Tour Successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to add India tour'));
            }
           /* $saveData = $this->placePriceTable()->addPlacePrice(array('place_id'=>$place,'tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'status'=>1));
            if($saveData['success'])
            {
                return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to add spiritual tour'));
            }*/
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
       // $statesList=$this->statesTable()->getIndiaStates(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour));

        $statesList=$this->statesTable()->getActiveIndianStates();

        return new ViewModel(array('statesList'=>$statesList));
    }
    public function loadIndiaTourListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $limit = 10;

            $offset=0;
            $searchData=array('limit'=>$limit,'offset'=>$offset,'tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour);
            $type=$request['type'];
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                if(isset($filterData['country']))
                {
                    if(isset($filterData['country']['text']) && $filterData['country']['text'])
                    {
                        $searchData['country']=$filterData['country']['text'];
                    }
                    if(isset($filterData['country']['order']) && $filterData['country']['order'])
                    {
                        $searchData['country_order']=$filterData['country']['order'];
                    }
                }
                if(isset($filterData['state']))
                {
                    if(isset($filterData['state']['text']) && $filterData['state']['text'])
                    {
                        $searchData['state']=$filterData['state']['text'];
                    }
                    if(isset($filterData['state']['order']) && $filterData['state']['order'])
                    {
                        $searchData['state_order']=$filterData['state']['order'];
                    }
                }
                if(isset($filterData['city']))
                {

                    if(isset($filterData['city']['text']) && $filterData['city']['text'])
                    {
                        $searchData['city']=$filterData['city']['text'];
                    }
                    if(isset($filterData['city']['order']) && $filterData['city']['order'])
                    {
                        $searchData['city_order']=$filterData['city']['order'];
                    }

                }
                if(isset($filterData['place_name']))
                {

                    if(isset($filterData['place_name']['text']) && $filterData['place_name']['text'])
                    {
                        $searchData['place_name']=$filterData['place_name']['text'];
                    }
                    if(isset($filterData['place_name']['order']) && $filterData['place_name']['order'])
                    {
                        $searchData['place_name_order']=$filterData['place_name']['order'];
                    }

                }
            }

            if(isset($request['page_number']))
            {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }
            $totalCount=0;
            if($type && $type=='search')
            {
                $totalCountList = $this->placePriceTable()->getPlacesListCount($searchData);

                if(count($totalCountList))
                {
                    $totalCount=count($totalCountList);
                }
            }
            $placesList = $this->placePriceTable()->getPlacesList($searchData);
            $view = new ViewModel(array('tourismList' => $placesList, 'type'=>$type,'offset' => $offset,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function worldTourListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $placesList=$this->placePriceTable()->getPlacesList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour,'limit'=>10,'offset'=>0));
        $totalCount=$this->placePriceTable()->getPlacesListCount(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour));

        return new ViewModel(array('placesList'=>$placesList,'totalCount'=>count($totalCount)));
    }
    public function getStatesAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();

            $countryId = $request['country_id'];
            $tourType=$request['tour_type'];
            $where = array('country_id' => $countryId,'tour_type'=>$tourType);
            $statesList = $this->statesTable()->getActiveStatesListAdmin($where);


            return new JsonModel(array('success' => true, 'states' => $statesList));
        }
    }
    public function getCitiesAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $countryId = $request['country_id'];
            $stateId = $request['state_id'];
            $tourType=$request['tour_type'];
            if ($stateId) {
                $where = array('state_id' => $stateId);
            } else {
                $where = array("country_id" => $countryId);
            }
            $where['tour_type']=$tourType;
            $citiesList = $this->citiesTable()->getActiveCitiesListAdmin($where);
            return new JsonModel(array('success' => true, 'cities' => $citiesList));
        }
    }
    public function addWorldTourAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $countryId=$request['country_id'];
            $cityId=$request['city_id'];
            $place=$request['place_id'];
            $price=$request['price'];
            if($countryId=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please select country"));
            }

            $where=array('country_id'=>$countryId,'tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour);

              if($cityId)
              {
                  $where['city_id']=$cityId;
              }
             $checkCountryAdded=$this->placePriceTable()->checkPriceAdded($where);
            $data=array('tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour,'status'=>1,'country_id'=>$countryId);

                if(is_null($place))
                {
                    $place='';
                }
            if($checkCountryAdded && $place)
            {
                $placesList=array_unique(array_merge(explode(',',$place),explode(",",$checkCountryAdded['place_id'])));
                $data['place_id']=",".implode(",",$placesList).",";
            }else if($place){
                $data['place_id']=",".$place.",";
            }

            if($cityId)
            {
                $data['city_id']=$cityId;
            }else
            {
                $data['city_id']=0;
            }
            if(!$place)
            {
                $data['place_id']='';
            }

                  $success=true;
             if(!count($checkCountryAdded))
             {
                 $saveData = $this->placePriceTable()->addPlacePrice($data);
                   if(!$saveData['success'])
                   {
                       $success=false;
                   }
             }else{
                 $updateData = $this->placePriceTable()->updatePlacePrice($data,array('place_price_id'=>$checkCountryAdded['place_price_id']));
                 if(!$updateData)
                 {
                     $success=false;
                 }
             }
            if($success)
            {
                return new JsonModel(array('success'=>true , 'message'=>'Added to World Tour Successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to add city tour'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
       // $countryList=$this->countriesTable()->getActiveCountriesListAdmin(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour));
        $countryList=$this->countriesTable()->getActiveCountries();
        return new ViewModel(array('countryList'=>$countryList));
    }
    public function deleteTourPriceAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            $request = $this->getRequest()->getPost();
            $priceId=$request['price_id'];
            $placeId=$request['place_id'];

               if(!$placeId)
               {
                   $response=$this->placePriceTable()->updatePlacePrice(array('status'=>0),array('place_price_id'=>$priceId));

               }else
               {


                   $response=$this->placePriceTable()->deletePlace($placeId,$priceId);
                   $checkCountry=$this->placePriceTable()->getField(array('place_price_id'=>$priceId),'place_id');
                   $placeList=array_filter(explode(",",$checkCountry));


              if(!count($placeList))
               {
                   $this->placePriceTable()->updatePlacePrice(array('status'=>0),array('place_price_id'=>$priceId));
               }
               }

            if($response)
            {
                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to update'));
            }
        }
    }
    public function loadWorldTourListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $limit = 10;

            $offset=0;
            $searchData=array('limit'=>$limit,'offset'=>$offset,'tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour);
            $type=$request['type'];
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                if(isset($filterData['country']))
                {
                    if(isset($filterData['country']['text']) && $filterData['country']['text'])
                    {
                        $searchData['country']=$filterData['country']['text'];
                    }
                    if(isset($filterData['country']['order']) && $filterData['country']['order'])
                    {
                        $searchData['country_order']=$filterData['country']['order'];
                    }
                }
                if(isset($filterData['state']))
                {
                    if(isset($filterData['state']['text']) && $filterData['state']['text'])
                    {
                        $searchData['state']=$filterData['state']['text'];
                    }
                    if(isset($filterData['state']['order']) && $filterData['state']['order'])
                    {
                        $searchData['state_order']=$filterData['state']['order'];
                    }
                }
                if(isset($filterData['city']))
                {
                    if(isset($filterData['city']['text']) && $filterData['city']['text'])
                    {
                        $searchData['city']=$filterData['city']['text'];
                    }
                    if(isset($filterData['city']['order']) && $filterData['city']['order'])
                    {
                        $searchData['city_order']=$filterData['city']['order'];
                    }
                }
                if(isset($filterData['place_name']))
                {
                    if(isset($filterData['place_name']['text']) && $filterData['place_name']['text'])
                    {
                        $searchData['place_name']=$filterData['place_name']['text'];
                    }
                    if(isset($filterData['place_name']['order']) && $filterData['place_name']['order'])
                    {
                        $searchData['place_name_order']=$filterData['place_name']['order'];
                    }
                }
            }
            if(isset($request['page_number']))
            {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }
            $totalCount=0;
            if($type && $type=='search')
            {
                $totalCountList = $this->placePriceTable()->getPlacesListCount($searchData);

                if(count($totalCountList))
                {
                    $totalCount=count($totalCountList);
                }
            }
            
            $placesList = $this->placePriceTable()->getPlacesList($searchData);
            
            $view = new ViewModel(array('tourismList' => $placesList, 'offset' => $offset,"type"=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function tourismPlacesAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();

            $cityId = $request['city_id'];
            $tourType = $request['tour_type'];
            $where = array('city_id' => $cityId);
            $placesList = $this->tourismPlacesTable()->getTourismPlaces($where);

            return new JsonModel(array('success' => true, 'places' => $placesList));
        }
    }
    public function editCityTourAction()
    {

        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $countryId=$request['country_id'];
            $cityId=$request['city_id'];
            $place=$request['place_name'];
            /* $price=$request['price'];
            $priceId=$request['price_id']; */
            if($countryId=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please select country"));
            }

            $where=array('country_id'=>$countryId,'tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour);

            if($cityId)
            {
                $where['city_id']=$cityId;
            }
            $checkCountryAdded=$this->placePriceTable()->checkPriceAdded($where);
                   /* echo '<pre>';
                    print_r($checkCountryAdded);
                    exit;*/
            if($cityId && count($checkCountryAdded) /* &&  $checkCountryAdded['place_price_id']!=$priceId */)
            {
                return new JsonModel(array('success'=>false,'message'=>'city already added'));
            }
            $data=array('tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour,'status'=>1,'country_id'=>$countryId/* ,'price'=>$price */);


            if($checkCountryAdded)
            {
                $data['place_id']=implode(",",array_unique(explode(',',$place)));
            }

            if($cityId)
            {
                $data['city_id']=$cityId;
            }else
            {
                $data['city_id']=0;
            }
            if($place)
            {
                $data['place_id']=",".$place.",";
            }else
            {
                $data['place_id']='';
            }
            $success=true;

                /* $updateData = $this->placePriceTable()->updatePlacePrice($data,array('place_price_id'=>$priceId));
                if(!$updateData)
                {
                    $success=false;
                } */

            if($success)
            {
                return new JsonModel(array('success'=>true , 'message'=>'Updated City Tour Successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to add city tour'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $cityIdString = rtrim($paramId, "=");
        $cityIdString = base64_decode($cityIdString);
        $cityIdString = explode("=", $cityIdString);
        $cityId = array_key_exists(1, $cityIdString) ? $cityIdString[1] : 0;

        $cityDetails=$this->placePriceTable()->getPlacePrices(array('place_price_id'=>$cityId));
        if(!count($cityDetails))
        {
            return $this->redirect()->toUrl($this->getBaseUrl().'/a_dMin/city-tour-list');
        }
        $countryList=$this->countriesTable()->getActiveCountriesListAdmin(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour));
        $citiesList = $this->citiesTable()->getActiveCitiesListAdmin(array('country_id'=>$cityDetails[0]['country_id'],'tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour));
        $placesList = $this->tourismPlacesTable()->getTourismPlacesAdmin(array('city_id'=>$cityDetails[0]['city_id'],'tour_type'=>\Admin\Model\PlacePrices::tour_type_City_tour));
                     $cityDetails=$cityDetails[0];
                     $cityDetails['place_id']=array_filter(explode(',',$cityDetails['place_id']));

        return new ViewModel(array('countryList'=>$countryList,'cityList'=>$citiesList,'placesList'=>$placesList,'tourDetails'=>$cityDetails));
    }

    public function tourismPlacesAddPriceAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $cityId = $request['city_id'];
            $tourType = $request['tour_type'];
            $where = array('city_id' => $cityId,'tour_type' => $tourType);
            $placesList = $this->tourismPlacesTable()->getTourismPlacesAdmin($where);
            return new JsonModel(array('success' => true, 'places' => $placesList));
        }
    }
}