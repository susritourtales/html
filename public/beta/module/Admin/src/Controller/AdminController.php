<?php

namespace Admin\Controller;




use Application\Constants\Constants;
use Application\Controller\BaseController;
use Application\Handler\Aes;
use Application\Handler\CronJob;
use Application\Handler\ImageThumb;
use Application\Handler\SendNotification;
use Application\Handler\SendFcmNotification;
use JsonSchema\Exception\JsonDecodingException;
use Laminas\Crypt\BlockCipher;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class AdminController extends BaseController
{
      public  function testAction()
      {

           $aes=new Aes();
           $password=$aes->encrypt('admin@tourtalessusri');
                   print_r($password);

      }

       public function logoutAction()
       {

          session_unset();
           return $this->redirect()->toUrl($this->getBaseUrl()."/a_dMin/login");
       }
    public function uploadFilesAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            //  $request = json_decode(file_get_contents("php://input"),true);

            $request = $this->getRequest()->getPost();

            $files = $this->getRequest()->getFiles();

            $validFiles = array('mp3', 'mp4', 'wav', 'mpeg', 'avi');
            $audioFiles = array('mp3', 'wav', 'mpeg');
            $videoFiles = array('mp4', 'mpg', 'avi');
            $validImageFiles = array('png', 'jpg', 'jpeg');
            $uploadFileDetails=array();
            $aes=new Aes();

            if (isset($files['image_files']))
            {
                $attachment=$files['image_files'];
                    $imagesPath = '';
                    $pdf_to_image = "";
                    $filename = $attachment['name'];
                    $fileExt = explode(".", $filename);
                    $ext = end($fileExt) ? end($fileExt) : "";
                    $ext=strtolower($ext);
                    $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                    $filename = $filenameWithoutExt . "." . $ext;
                    $filePath = "data/images";
                    //@mkdir(getcwd() . "/public/" . "tmp/".$filePath, 0777, true);

                    $filePath = $filePath . "/" . $filename;

                    if (!in_array(strtolower($ext), $validImageFiles))
                    {
                        return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                    }
                $flagImagePath=$filePath;
                $uploadStatus=$this->pushFiles("tmp/".$filePath,$attachment['tmp_name'],$attachment['type']);
                   if(!$uploadStatus)
                   {
                       return new JsonModel(array('success'=>false,"messsage"=>'something went wrong try agian'));
                   }
                $uploadFileDetails = array('file_path' => $filePath,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,'file_extension' => $ext,'status'=>\Admin\Model\TemporaryFiles::status_file_not_copied, 'duration' => 0,'hash'=>'', 'file_name' => $attachment['name']);

            }

            if (isset($files['attachments'])) {
                $uploadFile = $files['attachments'];
                $imagesPath = '';
                $pdf_to_image = "";
                $filename = $uploadFile['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";
                $ext=strtolower($ext);
                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                $filename = $filenameWithoutExt ;
                $filePath = "data/attachments";
                $filePath = $filePath . "/" . $filename;
                if (!in_array(strtolower($ext), $validFiles))
                {

                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }
                $duration='00:00:00';

                $fileDuration=$this->getDuration($uploadFile['tmp_name']);
                if($fileDuration)
                {
                    $duration=$fileDuration;
                }


                $fp = fopen(getcwd() . "/public/" . $filePath, 'w');
                $encodeContent=$aes->encrypt(file_get_contents($uploadFile['tmp_name']));
                $encodeString=$encodeContent['password'];
                $hash=$encodeContent['hash'];
                fwrite($fp,$encodeString);
                fclose($fp);

                $ext=strtolower($ext);
                $uploadStatus=  $this->pushFiles("tmp/".$filePath,getcwd() . "/public/" . $filePath,$ext);
                if(!$uploadStatus)
                {
                    return new JsonModel(array('success'=>false,"messsage"=>'something went wrong try agian'));

                }
                if(in_array($ext,$audioFiles))
                {
                    $fileType=\Admin\Model\TourismFiles::file_extension_type_audio;
                }else{
                    $fileType=\Admin\Model\TourismFiles::file_extension_type_video;
                }
                $uploadFileDetails = array(
                    'file_path' => $filePath,
                    'file_name'=>$filename,
                    'file_extension' => $ext,
                    'file_extension_type' => $fileType,
                    'hash'=>$hash,
                    'status'=>\Admin\Model\TemporaryFiles::status_file_not_copied,
                    'duration' => $duration
                );

            }

            $response=$this->temporaryFiles()->addTemporaryFile($uploadFileDetails);
            if($response['success'])
            {


                return new JsonModel(array('success'=>true,"messsage"=>'uploaded','id'=>$response['id']));
            }else
            {

                return new JsonModel(array('success'=>false,"messsage"=>'something went wrong try agian'));
            }
        }
        return $this->redirect()->toUrl($this->getBaseUrl()."/a_dMin/login");

    }
    function getDuration($file){
        $dur = shell_exec("ffmpeg -i ".$file." 2>&1");
        if(preg_match("/: Invalid /", $dur)){
            return false;
        }
        preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $dur, $duration);
        if(!isset($duration[1])){
            return false;
        }
        return $duration[1].":".$duration[2].":".$duration[3];
    }
    public function indexAction()
    {


        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //  $request = json_decode(file_get_contents("php://input"),true);

            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $files = $this->getRequest()->getFiles();
              $countryId=$request['country_id'];
              $cityId=$request['city_id'];
              $stateId=$request['state_id'];
              $placeName=$request['place_name'];
              $placeDescription=$request['description'];
              $fileDetails=$request['file_details'];

            $validFiles=array('mp3','mp4','wav','mpeg','avi');
            $audioFiles=array('mp3','wav','mpeg');
            $videoFiles=array('mp4','mpg','avi');
            $validImageFiles=array('png','jpg','jpeg');
              $fileDetails=json_decode($fileDetails,true);
              $uploadFileDetails=array();

            $imageFileAttachment=array();

            if(isset($files['images_files']))
            {
                
                foreach ($files['images_files'] as $attachment)
                {
                    $imagesPath = '';
                    $pdf_to_image = "";
                    $filename = $attachment['name'];
                    $fileExt = explode(".", $filename);
                    $ext = end($fileExt) ? end($fileExt) : "";
                    $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                    $filename = $filenameWithoutExt . "." . $ext;
                    $filePath = "data/images";
                    @mkdir(getcwd() . "/public/" . $filePath,0777,true);

                    $filePath = $filePath . "/" . $filename;

                    if (!in_array(strtolower($ext), $validImageFiles)) {
                        return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                    }
                    if (move_uploaded_file($attachment['tmp_name'], getcwd() . "/public/" . $filePath))
                    {
                        $imagesPath = $filePath;
                        $localImagePath = getcwd() . "/public/" . $filePath;
                        chmod(getcwd() . "/public/" . $filePath, 0777);
                    }
                    $uploadFileDetails[] = array('file_path' => $imagesPath, 'file_type'=>1,'file_upload_type' => $ext,'status'=>1, 'duration'=>0 ,'file_language_type' => 0, 'file_name' => $attachment['name']);
                }
                
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'please upload images'));
            }
                      foreach ($fileDetails as $fileDetail) {

                          $fileName = $fileDetail['file_name'];
                          $fileLanaguage = $fileDetail['lanaguage'];


                          $uploadFile = $files['mediaFiles_'.$fileDetail['file_id']];
                          $imagesPath = '';
                          $pdf_to_image = "";
                          $filename = $uploadFile['name'];
                          $fileExt = explode(".", $filename);
                          $ext = end($fileExt) ? end($fileExt) : "";
                          $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                          $filename = $filenameWithoutExt . "." . $ext;
                          $filePath = "data/attachments";
                          @mkdir(getcwd() . "/public/" . $filePath,0777,true);
                          $filePath = $filePath . "/" . $filename;
                          if (!in_array(strtolower($ext), $validFiles)) {
                              return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                          }

                          if (move_uploaded_file($uploadFile['tmp_name'], getcwd() . "/public/" . $filePath)) {
                              $imagesPath = $filePath;
                              $localImagePath = getcwd() . "/public/" . $filePath;
                              chmod(getcwd() . "/public/" . $filePath, 0777);
                              $duration='00:00:00';
                              $fileDuration=$this->getDuration(getcwd() . "/public/" . $filePath);
                              if($fileDuration)
                              {
                                  $duration=$fileDuration;
                              }
                          }
                          $ext=strtolower($ext);

                          if(in_array($ext,$audioFiles))
                          {
                              $fileType=0;
                          }else{
                              $fileType=2;
                          }
                          $uploadFileDetails[] = array('file_path' => $imagesPath,'file_type'=>$fileType,'status'=>1,'file_upload_type' => $ext,
                              'file_language_type' => $fileLanaguage,
                              'file_name' => $fileName,'duration'=>$duration);
                      }

                   //   $imageFiles=implode(',',$imageFileAttachment);
                    $response=$this->tourismPlacesTable()->addTourism(array('place_name'=>$placeName,
                        'place_description'=>$placeDescription,
                        'country_id'=>$countryId,
                        'city_id'=>$cityId,
                        'state_id'=>$stateId,'status'=>1));


                      if($response)
                      {
                               $insertId=$response['id'];
                          $counter = -1;
                          if(count($uploadFileDetails))
                          {
                              foreach ($uploadFileDetails as $details)
                              {
                                  $counter++;
                                  $uploadFileDetails[$counter]['tourism_place_id'] = $insertId;
                                  $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                                  $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                              }

                              $this->tourismPlaceFilesTable()->addMutipleTourismFiles($uploadFileDetails);
                          }
                          return new JsonModel(array('success'=>true,'message'=>'added successfully'));

                      }else
                      {
                            return new JsonModel(array('success'=>false,'message'=>'unable to add'));
                      }
        }
        $countriesList=$this->countriesTable()->getCountries();
           
        $languagesList=$this->languagesTable()->getLanguages();

         return new ViewModel(array('countries'=>$countriesList,"languages"=>$languagesList));
    }
   
    //** country start */
    public function countryListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countryList=$this->countriesTable()->getCountriesList();
        $totalCount=$this->countriesTable()->getCountriesCount();

        return new ViewModel(array('countryList'=>$countryList,'totalCount'=>count($totalCount)));
    }
    public function loadCountryListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();

            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                if(isset($filterData['country']))
                {
                    if(isset($filterData['country']['text']))
                    {
                        $searchData['country']=$filterData['country']['text'];
                    }
                    if(isset($filterData['country']['order']) && $filterData['country']['order'])
                    {
                        $searchData['country_order']=$filterData['country']['order'];
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
                $countResult=$this->countriesTable()->getCountriesCount($searchData);
                  if(count($countResult))
                  {
                      $totalCount=count($countResult);
                  }
            }
            $countryList = $this->countriesTable()->getCountriesList($searchData);
            $view = new ViewModel(array('countryList' => $countryList, 'offset' => $offset,'totalCount'=>$totalCount,'type'=>$type));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function editCountryAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            //  $request = json_decode(file_get_contents("php://input"),true);
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
           ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $files = $this->getRequest()->getFiles();

            $countryName=$request['country_name'];
            $countryId=$request['country_id'];
            $countryDescription=$request['description'];
            $fileDetails=$request['file_details'];

            $validFiles=array('mp3','mp4','wav','mpeg','avi');
            $audioFiles=array('mp3','wav','mpeg');
            $videoFiles=array('mp4','mpg','avi');
            $validImageFiles=array('png','jpg','jpeg');
            $fileDetails=json_decode($fileDetails,true);
            $uploadFileDetails=array();
            $deletedImages=json_decode($request['deleted_images'],true);
            $deletedAudio=json_decode($request['deleted_audio'],true);

            $deleteFiles=array_merge($deletedAudio,$deletedImages);

            $countryName=trim($countryName);
            if($countryName=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please enter country name"));
            }

            $checkCountry=$this->countriesTable()->getFields(array('country_name'=>$countryName,'status'=>1),array('id'));

            if($checkCountry && $checkCountry['id']!=$countryId)
            {
                return new JsonModel(array("success" => false, "message" => "Country Already exists"));
            }
            $imageFileAttachment=array();
            $aes=new Aes();
            $flagImagePath='';
            if(isset($files['flag_file']))
            {
                $attachment=$files['flag_file'];
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
                $flagImagePath=$filePath;
                $this->pushFiles($filePath,$attachment['tmp_name'],$attachment['type']);

                /* if (move_uploaded_file($attachment['tmp_name'], getcwd() . "/public/" . $filePath))
                 {
                     $imagesPath = $filePath;
                     $localImagePath = getcwd() . "/public/" . $filePath;
                    // $this->pushFiles($filePath,$attachment['tmp_name']);

                     chmod(getcwd() . "/public/" . $filePath, 0777);
                 }*/
                /* $fp = fopen(getcwd() . "/public/" . $filePath, 'w');
                 $encodeContent=$aes->encrypt(file_get_contents($attachment['tmp_name']));
                 $encodeString=$encodeContent['password'];
                 $hash=$encodeContent['hash'];
                 fwrite($fp,$encodeString);
                 fclose($fp);*/

            }
            $fileIds=explode(",",$request['file_Ids']);

            $getFiles=$this->temporaryFiles()->getFiles($fileIds);

            $uploadFiles=array();
            if(count($getFiles)) {
                foreach ($getFiles['images'] as $images) {

                    $uploadFileDetails[] = array(
                        'file_path' => $images['file_path'],
                        'file_data_type' => \Admin\Model\TourismFiles::file_data_type_country,
                        'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                        'file_extension' => $images['file_extension'],
                        'status' => 1,
                        'duration' => 0,
                        'file_language_type' => 0,
                        'hash' => '',
                        'file_name' => $images['file_name']
                    );
                    if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                        $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                    }
                }
                $audioFiles = $getFiles['audioFiles'];

                foreach ($fileDetails as $fileDetail) {

                    $fileName = $fileDetail['file_name'];
                    $fileLanaguage = $fileDetail['lanaguage'];


                    if (isset($fileDetail['edit_id']) && $fileDetail['edit_id'] != 0) {

                        $updateData = array('file_name' => $fileName, 'file_language_type' => $fileLanaguage);
                        if (isset($fileDetail['file_id']) && $fileDetail['file_id']) {
                            $filePath = $audioFiles[$fileDetail['file_id']]['file_path'];
                            $ext = $audioFiles[$fileDetail['file_id']]['file_extension'];
                            $duration = $audioFiles[$fileDetail['file_id']]['duration'];
                            $hash = $audioFiles[$fileDetail['file_id']]['hash'];
                            $fileType = $audioFiles[$fileDetail['file_id']]['file_extension_type'];

                            if ($audioFiles[$fileDetail['file_id']]['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                                $uploadUpdateFiles[] = array('old_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'new_path' => $audioFiles[$fileDetail['file_id']]['file_path'],
                                    'id' => $audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                                $copyFiles = $this->copypushFiles($uploadUpdateFiles);

                                if (count($copyFiles['copied'])) {
                                    $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
                                }

                                if (!$copyFiles['status']) {
                                    return new JsonModel(array('success' => false, 'message' => 'unable to update city'));
                                }

                            }

                            $updateData['file_path'] = $filePath;
                            $updateData['file_extension_type'] = $fileType;
                            $updateData['file_extension'] = $ext;
                            $updateData['duration'] = $duration;
                            $updateData['hash'] = $hash;
                        }
                        $tourismFileId = $fileDetail['edit_id'];
                        $updateResponse = $this->tourismFilesTable()->updatePlaceFiles($updateData, array('tourism_file_id' => $tourismFileId));

                    } else {
                        $filePath = $audioFiles[$fileDetail['file_id']]['file_path'];
                        $ext = $audioFiles[$fileDetail['file_id']]['file_extension'];
                        $duration = $audioFiles[$fileDetail['file_id']]['duration'];
                        $hash = $audioFiles[$fileDetail['file_id']]['hash'];
                        $fileType = $audioFiles[$fileDetail['file_id']]['file_extension_type'];

                        if ($audioFiles[$fileDetail['file_id']]['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                            $uploadFiles[] = array('old_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'new_path' => $audioFiles[$fileDetail['file_id']]['file_path'],
                                'id' => $audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                        }
                        $uploadFileDetails[] = array(
                            'file_path' => $filePath,
                            'file_data_type' => \Admin\Model\TourismFiles::file_data_type_country,
                            'file_extension_type' => $fileType,
                            'file_extension' => $ext,
                            'status' => 1,
                            'duration' => $duration,
                            'file_language_type' => $fileLanaguage,
                            'hash' => $hash,
                            'file_name' => $fileName
                        );
                    }
                }
                $copyFiles= $this->copypushFiles($uploadFiles);

                if(count($copyFiles['copied']))
                {
                    $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
                }
                if(!$copyFiles['status'])
                {
                    return new JsonModel(array('success'=>false,'message'=>'unable to update state'));
                }
            }


            //   $imageFiles=implode(',',$imageFileAttachment);


            $data=array('country_name'=>$countryName,
                'country_description'=>$countryDescription,
                'status'=>1);
            if($flagImagePath)
            {
                $data['flag_image']=$flagImagePath;
            }
                $update=$this->countriesTable()->updateCountry($data,array('id'=>$countryId));
                if(!$update) {
                    return new JsonModel(array('success'=>false,'message'=>'unable to update country'));
                }





            $counter = -1;
            if(count($uploadFileDetails)) {
                foreach ($uploadFileDetails as $details) {
                    $counter++;
                    $uploadFileDetails[$counter]['file_data_id'] = $countryId;
                    $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                    $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                }
                $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
            }
            if(count($deleteFiles))
            {

                $this->tourismFilesTable()->deletePlaceFiles($deleteFiles);
            }
            return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));



        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countriesList=$this->countriesTable()->getCountries();

        $languagesList=$this->languagesTable()->getLanguages();

        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countryIdString = rtrim($paramId, "=");
        $countryIdString = base64_decode($countryIdString);
        $countryIdString = explode("=", $countryIdString);
        $countryId = array_key_exists(1, $countryIdString) ? $countryIdString[1] : 0;
         
        $countryDetails=$this->countriesTable()->getCountriesDetails($countryId);
        $imageFiles=array();
        $audioFiles=array();
        $imageCounter=-1;
        $audioCounter=-1;
        
        $countryInfo=array();
        foreach ($countryDetails as $country)
        {
            $countryInfo['country_id']=$country['country_id'];
            $countryInfo['country_name']=$country['country_name'];
            $countryInfo['country_description']=$country['country_description'];
            $countryInfo['flag_image']=$country['flag_image'];
            if($country['file_extension_type']!=1)
            {
                $audioCounter++;
                $audioFiles[$audioCounter]['file_path'] = $country['file_path'];
                $audioFiles[$audioCounter]['file_language_type'] = $country['file_language_type'];
                $audioFiles[$audioCounter]['tourism_file_id'] = $country['tourism_file_id'];
                $audioFiles[$audioCounter]['file_name'] = $country['file_name'];
            }else
            {
                $imageCounter++;
                $imageFiles[$imageCounter]['file_path'] = $country['file_path'];
                $imageFiles[$imageCounter]['file_language_type'] = $country['file_language_type'];
                $imageFiles[$imageCounter]['tourism_file_id'] = $country['tourism_file_id'];
                $imageFiles[$imageCounter]['file_name'] = $country['file_name'];
            }
        }

        return new ViewModel(array('countries'=>$countriesList,'imageUrl'=>$this->filesUrl(),'country_id'=>$countryId,'languages'=>$languagesList,'countryDetails'=>$countryInfo,'audioFiles'=>$audioFiles,'imageFiles'=>$imageFiles));


     //   return new ViewModel(array('countries'=>$countriesList,"languages"=>$languagesList));
    }
    public function addCountryAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
           ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");

            $request = $this->getRequest()->getPost();


          
            $countryName=$request['country_name'];
            $countryDescription=$request['description'];
            $fileDetails=$request['file_details'];

            $fileDetails=json_decode($fileDetails,true);
            $fileIds=explode(",",$request['file_Ids']);
            $uploadFileDetails=array();

              $countryName=trim($countryName);
              if($countryName=='')
              {
                  return new JsonModel(array("success" => false, "message" => "Please enter country name"));
              }

               $checkCountry=$this->countriesTable()->getField(array('country_name'=>$countryName,'status'=>1),'id');

                 if($checkCountry)
                 {
                     return new JsonModel(array("success" => false, "message" => "Country Already exists"));
                 }

            $flagImagePath='';
                $getFiles=$this->temporaryFiles()->getFiles($fileIds);

                $uploadFiles=array();
               foreach ($getFiles['images'] as $images)
               {

                   $uploadFileDetails[] = array(
                       'file_path' => $images['file_path'],
                       'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_country,
                       'file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image,
                       'file_extension' => $images['file_extension'],
                       'status'=>1,
                       'duration'=>0 ,
                       'file_language_type' => 0,
                       'hash'=>'',
                       'file_name' => $images['file_name']
                   );
                   if($images['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                   {
                       $uploadFiles[]=array('old_path'=>$images['file_path'],'new_path'=>$images['file_path'],'id'=>$images['temporary_files_id']);

                   }

               }
                 $audioFiles=isset($getFiles['audioFiles'])?$getFiles['audioFiles']:array();

            foreach ($fileDetails as $fileDetail)
            {
                $fileName = $fileDetail['file_name'];
                $fileLanaguage = $fileDetail['lanaguage'];
                $filePath=$audioFiles[$fileDetail['file_id']]['file_path'];
                $ext=$audioFiles[$fileDetail['file_id']]['file_extension'];
                $duration=$audioFiles[$fileDetail['file_id']]['duration'];
                $hash=$audioFiles[$fileDetail['file_id']]['hash'];
                $fileType=$audioFiles[$fileDetail['file_id']]['file_extension_type'];

                         if($audioFiles[$fileDetail['file_id']]['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                         {
                             $uploadFiles[]=array('old_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],'new_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],
                                 'id'=>$audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                         }
                $uploadFileDetails[] = array(
                    'file_path' => $filePath,
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_country,
                    'file_extension_type'=>$fileType,
                    'file_extension' => $ext,
                    'status'=>1,
                    'duration'=>$duration,
                    'file_language_type' => $fileLanaguage,
                    'hash'=>$hash,
                    'file_name' => $fileName
                );
            }

            //   $imageFiles=implode(',',$imageFileAttachment);

               $copyFiles= $this->copypushFiles($uploadFiles);

                         if(count($copyFiles['copied']))
                         {
                             $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
                         }
               if(!$copyFiles['status'])
               {

                   return new JsonModel(array('success'=>false,'message'=>'unable to adddddd country'));
               }
            $countryId="";
            $getCountryId=$this->countriesTable()->getField(array('country_name'=>$countryName,'status'=>0),'id');
            $data=array('country_name'=>$countryName,
                'country_description'=>$countryDescription,
                'flag_image'=>$flagImagePath,
                'status'=>1);
            if($getCountryId)
            {
               $countryId=$getCountryId;
                $update=$this->countriesTable()->updateCountry($data,array('id'=>$countryId));
                   if(!$update) {
                       return new JsonModel(array('success'=>false,'message'=>'unable to add country'));
                   }
            }else
            {
                $response=$this->countriesTable()->addCountry($data);
                if($response['success'])
                {
                    $countryId=$response['id'];
                }else
                {
                    return new JsonModel(array('success'=>false,'message'=>'unable to add country'));
                }
            }

                $counter = -1;
                if(count($uploadFileDetails)) {
                    foreach ($uploadFileDetails as $details) {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $countryId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
                }
                return new JsonModel(array('success'=>true , 'message'=>'added successfully'));



        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countriesList=$this->countriesTable()->getCountries();
        
        $languagesList=$this->languagesTable()->getLanguages();
        return new ViewModel(array('countries'=>$countriesList,"languages"=>$languagesList));
    }
    public function deleteCountryAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            $request = $this->getRequest()->getPost();
            $countryId=$request['country_id'];
            $response=$this->countriesTable()->updateCountry(array('status'=>0),array('id'=>$countryId));
            if($response)
            {
                  $countryName=$this->countriesTable()->getField(array('id'=>$countryId),'country_name');
                  if(strtolower($countryName)=='india')
                  {
                      $stateUpdate=$this->statesTable()->updateState(array('status'=>0),array('country_id'=>$countryId,'status'=>1));
                  }
                $cityUpdate=$this->citiesTable()->updateCity(array('status'=>0),array('country_id'=>$countryId,'status'=>1));
                  $placeUpdate=$this->tourismPlacesTable()->updateTourism(array('status'=>0),array('country_id'=>$countryId,'status'=>1));
                  $toursUpdate=$this->placePriceTable()->updatePlacePrice(array('status'=>0),array('country_id'=>$countryId,'status'=>1));
                $response=$this->tourismFilesTable()->updatePlaceFiles(array('status'=>0),array('file_data_id'=>$countryId,'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_country));
                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to update'));
            }
        }
    }
    //** country end */

    //** state start */
    public function stateListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $stateList=$this->statesTable()->getStateList();
        $totalCount=$this->statesTable()->getStatesCount();

        return new ViewModel(array('stateList'=>$stateList,'totalCount'=>count($totalCount)));
    }
    public function loadStateListAction()
    {
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
                if(isset($filterData['state']))
                {
                    if(isset($filterData['state']['text']))
                    {
                        $searchData['state']=$filterData['state']['text'];
                    }
                    if(isset($filterData['state']['order']) && $filterData['state']['order'])
                    {
                        $searchData['state_order']=$filterData['state']['order'];
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
                $countResult=$this->statesTable()->getStatesCount($searchData);
                if(count($countResult))
                {
                    $totalCount=count($countResult);
                }
            }
            $stateList = $this->statesTable()->getStateList($searchData);
            $view = new ViewModel(array('stateList' => $stateList, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function addStateAction()
    {
        if($this->getRequest()->isXmlHttpRequest()) 
        {
            //  $request = json_decode(file_get_contents("php://input"),true);

            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $files = $this->getRequest()->getFiles();

            $stateName=$request['state_name'];
            $stateDescription=$request['description'];
            $fileDetails=$request['file_details'];

            $validFiles=array('mp3','mp4','wav','mpeg','avi');
            $audioFiles=array('mp3','wav','mpeg');
            $videoFiles=array('mp4','mpg','avi');
            $validImageFiles=array('png','jpg','jpeg');
            $fileDetails=json_decode($fileDetails,true);
            $uploadFileDetails=array();

            $countryName=$this->countriesTable()->getField(array('country_name'=>'India','status'=>1),'id');
            if($countryName=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please add india country to add state"));

            }

            $check=$this->statesTable()->getField(array('state_name'=>$stateName,'status'=>1),'id');

            if($check)
            {
                return new JsonModel(array("success" => false, "message" => "State Already exists"));
            }
            $imageFileAttachment=array();
            $aes=new Aes();

            /* echo '<pre>';
            print_r($uploadFileDetails);
            exit;*/
            //   $imageFiles=implode(',',$imageFileAttachment);

            $stateId="";
            $fileIds=explode(",",$request['file_Ids']);

            $getFiles=$this->temporaryFiles()->getFiles($fileIds);

            $uploadFiles=array();
            foreach ($getFiles['images'] as $images)
            {

                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_state,
                    'file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $images['file_extension'],
                    'status'=>1,
                    'duration'=>0 ,
                    'file_language_type' => 0,
                    'hash'=>'',
                    'file_name' => $images['file_name']
                );
                if($images['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                {
                    $uploadFiles[]=array('old_path'=>$images['file_path'],'new_path'=>$images['file_path'],'id'=>$images['temporary_files_id']);

                }

            }
            $audioFiles=$getFiles['audioFiles'];

            foreach ($fileDetails as $fileDetail)
            {
                $fileName = $fileDetail['file_name'];
                $fileLanaguage = $fileDetail['lanaguage'];
                $filePath=$audioFiles[$fileDetail['file_id']]['file_path'];
                $ext=$audioFiles[$fileDetail['file_id']]['file_extension'];
                $duration=$audioFiles[$fileDetail['file_id']]['duration'];
                $hash=$audioFiles[$fileDetail['file_id']]['hash'];
                $fileType=$audioFiles[$fileDetail['file_id']]['file_extension_type'];

                if($audioFiles[$fileDetail['file_id']]['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                {
                    $uploadFiles[]=array('old_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],'new_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],
                        'id'=>$audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                }
                $uploadFileDetails[] = array(
                    'file_path' => $filePath,
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_state,
                    'file_extension_type'=>$fileType,
                    'file_extension' => $ext,
                    'status'=>1,
                    'duration'=>$duration,
                    'file_language_type' => $fileLanaguage,
                    'hash'=>$hash,
                    'file_name' => $fileName
                );
            }

            //   $imageFiles=implode(',',$imageFileAttachment);

            $copyFiles= $this->copypushFiles($uploadFiles);

            if(count($copyFiles['copied']))
            {
                $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
            }
            if(!$copyFiles['status'])
            {

                return new JsonModel(array('success'=>false,'message'=>'unable to add state'));
            }

            $data=array('country_id'=>$countryName,
                'state_name'=>$stateName,
                'state_description'=>$stateDescription,
                'status'=>1);
           
                $response=$this->statesTable()->addState($data);
                if($response['success']){
                    $stateId=$response['id'];
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'unable to add state'));
                }
            $counter = -1;
            if(count($uploadFileDetails)) {
                foreach ($uploadFileDetails as $details) {
                    $counter++;
                    $uploadFileDetails[$counter]['file_data_id'] = $stateId;
                    $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                    $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                }
                $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
            }
            return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countriesList=$this->countriesTable()->getActiveCountries();
        $languagesList=$this->languagesTable()->getLanguages();
        return new ViewModel(array('countries'=>$countriesList,"languages"=>$languagesList));
    }
    public function editStateAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
           ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $files = $this->getRequest()->getFiles();

            $stateName=$request['state_name'];
            $stateId=$request['state_id'];
            $stateDescription=$request['description'];
            $fileDetails=$request['file_details'];

            $validFiles=array('mp3','mp4','wav','mpeg','avi');
            $audioFiles=array('mp3','wav','mpeg');
            $videoFiles=array('mp4','mpg','avi');
            $validImageFiles=array('png','jpg','jpeg');
            $fileDetails=json_decode($fileDetails,true);
            $uploadFileDetails=array();

            $countryName=$this->countriesTable()->getField(array('country_name'=>'India','status'=>1),'id');
            if($countryName=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please add india country to add state"));

            }
            $deletedImages=json_decode($request['deleted_images'],true);
            $deletedAudio=json_decode($request['deleted_audio'],true);

            $deleteFiles=array_merge($deletedAudio,$deletedImages);


            $check=$this->statesTable()->getField(array('state_name'=>$stateName,'status'=>1),'id');

            if($check && $check!=$stateId)
            {
                return new JsonModel(array("success" => false, "message" => "State Already exists"));
            }
            $imageFileAttachment=array();
            $aes=new Aes();
            $fileIds=explode(",",$request['file_Ids']);

            $getFiles=$this->temporaryFiles()->getFiles($fileIds);

            $uploadFiles=array();
            if(count($getFiles)) {
                foreach ($getFiles['images'] as $images) {

                    $uploadFileDetails[] = array(
                        'file_path' => $images['file_path'],
                        'file_data_type' => \Admin\Model\TourismFiles::file_data_type_state,
                        'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                        'file_extension' => $images['file_extension'],
                        'status' => 1,
                        'duration' => 0,
                        'file_language_type' => 0,
                        'hash' => '',
                        'file_name' => $images['file_name']
                    );
                    if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                        $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                    }
                }
                $audioFiles = $getFiles['audioFiles'];

                foreach ($fileDetails as $fileDetail) {

                    $fileName = $fileDetail['file_name'];
                    $fileLanaguage = $fileDetail['lanaguage'];


                    if (isset($fileDetail['edit_id']) && $fileDetail['edit_id'] != 0) {

                        $updateData = array('file_name' => $fileName, 'file_language_type' => $fileLanaguage);
                        if (isset($fileDetail['file_id']) && $fileDetail['file_id']) {
                            $filePath = $audioFiles[$fileDetail['file_id']]['file_path'];
                            $ext = $audioFiles[$fileDetail['file_id']]['file_extension'];
                            $duration = $audioFiles[$fileDetail['file_id']]['duration'];
                            $hash = $audioFiles[$fileDetail['file_id']]['hash'];
                            $fileType = $audioFiles[$fileDetail['file_id']]['file_extension_type'];

                            if ($audioFiles[$fileDetail['file_id']]['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                                $uploadUpdateFiles[] = array('old_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'new_path' => $audioFiles[$fileDetail['file_id']]['file_path'],
                                    'id' => $audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                                $copyFiles = $this->copypushFiles($uploadUpdateFiles);

                                if (count($copyFiles['copied'])) {
                                    $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
                                }

                                if (!$copyFiles['status']) {
                                    return new JsonModel(array('success' => false, 'message' => 'unable to update city'));
                                }

                            }

                            $updateData['file_path'] = $filePath;
                            $updateData['file_extension_type'] = $fileType;
                            $updateData['file_extension'] = $ext;
                            $updateData['duration'] = $duration;
                            $updateData['hash'] = $hash;
                        }
                        $tourismFileId = $fileDetail['edit_id'];
                        $updateResponse = $this->tourismFilesTable()->updatePlaceFiles($updateData, array('tourism_file_id' => $tourismFileId));

                    } else {
                        $filePath = $audioFiles[$fileDetail['file_id']]['file_path'];
                        $ext = $audioFiles[$fileDetail['file_id']]['file_extension'];
                        $duration = $audioFiles[$fileDetail['file_id']]['duration'];
                        $hash = $audioFiles[$fileDetail['file_id']]['hash'];
                        $fileType = $audioFiles[$fileDetail['file_id']]['file_extension_type'];

                        if ($audioFiles[$fileDetail['file_id']]['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                            $uploadFiles[] = array('old_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'new_path' => $audioFiles[$fileDetail['file_id']]['file_path'],
                                'id' => $audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                        }
                        $uploadFileDetails[] = array(
                            'file_path' => $filePath,
                            'file_data_type' => \Admin\Model\TourismFiles::file_data_type_state,
                            'file_extension_type' => $fileType,
                            'file_extension' => $ext,
                            'status' => 1,
                            'duration' => $duration,
                            'file_language_type' => $fileLanaguage,
                            'hash' => $hash,
                            'file_name' => $fileName
                        );
                    }
                }
                $copyFiles= $this->copypushFiles($uploadFiles);

                if(count($copyFiles['copied']))
                {
                    $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
                }
                if(!$copyFiles['status'])
                {

                    return new JsonModel(array('success'=>false,'message'=>'unable to update state'));
                }
            }

            /* echo '<pre>';
            print_r($uploadFileDetails);
            exit;*/
            //   $imageFiles=implode(',',$imageFileAttachment);

            $data=array('country_id'=>$countryName,
                'state_name'=>$stateName,
                'state_description'=>$stateDescription,
                'status'=>1);

            $response=$this->statesTable()->updateState($data,array('id'=>$stateId));

            if($response){
                $counter = -1;
                if(count($uploadFileDetails)) {
                    foreach ($uploadFileDetails as $details) {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $stateId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
                }
                if(count($deleteFiles))
                {

                    $this->tourismFilesTable()->deletePlaceFiles($deleteFiles);
                }
                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));

            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to add state'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $languagesList=$this->languagesTable()->getLanguages();

        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $stateIdString = rtrim($paramId, "=");
        $stateIdString = base64_decode($stateIdString);
        $stateIdString = explode("=", $stateIdString);
        $stateId = array_key_exists(1, $stateIdString) ? $stateIdString[1] : 0;

        $stateDetails=$this->statesTable()->getStateDetails($stateId);
        $imageFiles=array();
        $audioFiles=array();
        $imageCounter=-1;
        $audioCounter=-1;

        $stateInfo=array();
        foreach ($stateDetails as $state)
        {
            $stateInfo['state_id']=$state['state_id'];
            $stateInfo['state_name']=$state['state_name'];
            $stateInfo['state_description']=$state['state_description'];
            if($state['file_extension_type']!=1)
            {
                $audioCounter++;
                $audioFiles[$audioCounter]['file_path'] = $state['file_path'];
                $audioFiles[$audioCounter]['file_language_type'] = $state['file_language_type'];
                $audioFiles[$audioCounter]['tourism_file_id'] = $state['tourism_file_id'];
                $audioFiles[$audioCounter]['file_name'] = $state['file_name'];
            }else
            {
                $imageCounter++;
                $imageFiles[$imageCounter]['file_path'] = $state['file_path'];
                $imageFiles[$imageCounter]['file_language_type'] = $state['file_language_type'];
                $imageFiles[$imageCounter]['tourism_file_id'] = $state['tourism_file_id'];
                $imageFiles[$imageCounter]['file_name'] = $state['file_name'];
            }
        }

        return new ViewModel(array('imageUrl'=>$this->filesUrl(),"languages"=>$languagesList,'stateDetails'=>$stateInfo,'audioFiles'=>$audioFiles,'imageFiles'=>$imageFiles));
    }

    public function deleteStateAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            $request = $this->getRequest()->getPost();
            $stateId=$request['state_id'];
            $response=$this->statesTable()->updateState(array('status'=>0),array('id'=>$stateId));
            if($response)
            {
                $cityUpdate=$this->citiesTable()->updateCity(array('status'=>0),array('state_id'=>$stateId,'status'=>1));
                $placeUpdate=$this->tourismPlacesTable()->updateTourism(array('status'=>0),array('state_id'=>$stateId,'status'=>1));
                $toursUpdate=$this->placePriceTable()->updatePlacePrice(array('status'=>0),array('state_id'=>$stateId,'status'=>1));
                $response=$this->tourismFilesTable()->updatePlaceFiles(array('status'=>0),array('file_data_id'=>$stateId,'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_state));

                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));

            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to update'));
            }
        }
    }
    //** state end */

    //** city start */
    public function cityListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $cityList=$this->citiesTable()->getCityList();
        $totalCount=$this->citiesTable()->getCityCount();

        return new ViewModel(array('cityList'=>$cityList,'totalCount'=>count($totalCount)));
    }
    public function loadCityListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
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
                    if(isset($filterData['city']['text']))
                    {
                        $searchData['city']=$filterData['city']['text'];
                    }
                    if(isset($filterData['city']['order']) && $filterData['city']['order'])
                    {
                        $searchData['city_order']=$filterData['city']['order'];
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
                $countResult=$this->citiesTable()->getCityCount($searchData);
                if(count($countResult))
                {
                    $totalCount=count($countResult);
                }
            }
            $cityList = $this->citiesTable()->getCityList($searchData);
            $view = new ViewModel(array('cityList' => $cityList, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function addCityAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
           ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $files = $this->getRequest()->getFiles();

            $countryName=$request['country_name'];
            $stateName=$request['state_name'];
            $cityName=$request['city_name'];
            $description=$request['description'];
            $fileDetails=$request['file_details'];

            $validFiles=array('mp3','mp4','wav','mpeg','avi');
            $audioFiles=array('mp3','wav','mpeg');
            $videoFiles=array('mp4','mpg','avi');
            $validImageFiles=array('png','jpg','jpeg');
            $fileDetails=json_decode($fileDetails,true);
            $uploadFileDetails=array();

            $cityName=trim($cityName);
            if($cityName=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please enter city name"));
            }
            $check=$this->citiesTable()->getField(array('city_name'=>$cityName,'status'=>1),'id');

            if($check)
            {
                return new JsonModel(array("success" => false, "message" => "City Already exists"));
            }
            $imageFileAttachment=array();
            $aes=new Aes();
            $fileIds=explode(",",$request['file_Ids']);

            $getFiles=$this->temporaryFiles()->getFiles($fileIds);

            $uploadFiles=array();
            foreach ($getFiles['images'] as $images)
            {

                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_city,
                    'file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $images['file_extension'],
                    'status'=>1,
                    'duration'=>0 ,
                    'file_language_type' => 0,
                    'hash'=>'',
                    'file_name' => $images['file_name']
                );
                if($images['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                {
                    $uploadFiles[]=array('old_path'=>$images['file_path'],'new_path'=>$images['file_path'],'id'=>$images['temporary_files_id']);

                }

            }
            $audioFiles=$getFiles['audioFiles'];

            foreach ($fileDetails as $fileDetail)
            {
                $fileName = $fileDetail['file_name'];
                $fileLanaguage = $fileDetail['lanaguage'];
                $filePath=$audioFiles[$fileDetail['file_id']]['file_path'];
                $ext=$audioFiles[$fileDetail['file_id']]['file_extension'];
                $duration=$audioFiles[$fileDetail['file_id']]['duration'];
                $hash=$audioFiles[$fileDetail['file_id']]['hash'];
                $fileType=$audioFiles[$fileDetail['file_id']]['file_extension_type'];

                if($audioFiles[$fileDetail['file_id']]['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                {
                    $uploadFiles[]=array('old_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],'new_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],
                        'id'=>$audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                }
                $uploadFileDetails[] = array(
                    'file_path' => $filePath,
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_city,
                    'file_extension_type'=>$fileType,
                    'file_extension' => $ext,
                    'status'=>1,
                    'duration'=>$duration,
                    'file_language_type' => $fileLanaguage,
                    'hash'=>$hash,
                    'file_name' => $fileName
                );
            }

            //   $imageFiles=implode(',',$imageFileAttachment);

            $copyFiles= $this->copypushFiles($uploadFiles);

            if(count($copyFiles['copied']))
            {
                $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
            }
            if(!$copyFiles['status'])
            {

                return new JsonModel(array('success'=>false,'message'=>'unable to add city'));
            }

            $cityId="";
            if($stateName=='')
            {
                $stateName=0;
            }
            $data=array(
                'country_id'=>$countryName,
                'state_id'=>$stateName,
                'city_name'=>$cityName,
                'city_description'=>$description,
                'status'=>1);

            $response=$this->citiesTable()->addCity($data);
            if($response['success']){
                $cityId=$response['id'];
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to add city'));
            }
            $counter = -1;
            if(count($uploadFileDetails)) {
                foreach ($uploadFileDetails as $details) {
                    $counter++;
                    $uploadFileDetails[$counter]['file_data_id'] = $cityId;
                    $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                    $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                }
                $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
            }
            return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
        }

        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countriesList=$this->countriesTable()->getActiveCountries();
        $languagesList=$this->languagesTable()->getLanguages();
        return new ViewModel(array('countries'=>$countriesList,"languages"=>$languagesList));
    }
    public function editCityAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
           ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $files = $this->getRequest()->getFiles();

            $countryName=$request['country_name'];
            $stateName=$request['state_name'];
            $cityId=$request['city_id'];
            $cityName=$request['city_name'];
            $description=$request['description'];
            $fileDetails=$request['file_details'];

            $validFiles=array('mp3','mp4','wav','mpeg','avi');
            $audioFiles=array('mp3','wav','mpeg');
            $videoFiles=array('mp4','mpg','avi');
            $validImageFiles=array('png','jpg','jpeg');
            $fileDetails=json_decode($fileDetails,true);
            $uploadFileDetails=array();
            $deletedImages=json_decode($request['deleted_images'],true);
            $deletedAudio=json_decode($request['deleted_audio'],true);

            $deleteFiles=array_merge($deletedAudio,$deletedImages);


            $cityName=trim($cityName);
            if($cityName=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please enter city name"));
            }
            $check=$this->citiesTable()->getField(array('city_name'=>$cityName,'status'=>1),'id');

            if($check && $cityId!=$check)
            {
                return new JsonModel(array("success" => false, "message" => "City Already exists"));
            }
            $imageFileAttachment=array();
            $aes=new Aes();
            $fileIds=explode(",",$request['file_Ids']);

            $getFiles=$this->temporaryFiles()->getFiles($fileIds);

            $uploadFiles=array();
             if(count($getFiles)) {
                 foreach ($getFiles['images'] as $images) {

                     $uploadFileDetails[] = array(
                         'file_path' => $images['file_path'],
                         'file_data_type' => \Admin\Model\TourismFiles::file_data_type_city,
                         'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                         'file_extension' => $images['file_extension'],
                         'status' => 1,
                         'duration' => 0,
                         'file_language_type' => 0,
                         'hash' => '',
                         'file_name' => $images['file_name']
                     );
                     if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                         $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                     }

                 }
                 $audioFiles = $getFiles['audioFiles'];

                 foreach ($fileDetails as $fileDetail) {

                     $fileName = $fileDetail['file_name'];
                     $fileLanaguage = $fileDetail['lanaguage'];


                     if (isset($fileDetail['edit_id']) && $fileDetail['edit_id'] != 0) {

                         $updateData = array('file_name' => $fileName, 'file_language_type' => $fileLanaguage);
                         if (isset($fileDetail['file_id']) && $fileDetail['file_id']) {
                             $filePath = $audioFiles[$fileDetail['file_id']]['file_path'];
                             $ext = $audioFiles[$fileDetail['file_id']]['file_extension'];
                             $duration = $audioFiles[$fileDetail['file_id']]['duration'];
                             $hash = $audioFiles[$fileDetail['file_id']]['hash'];
                             $fileType = $audioFiles[$fileDetail['file_id']]['file_extension_type'];

                             if ($audioFiles[$fileDetail['file_id']]['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                                 $uploadUpdateFiles[] = array('old_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'new_path' => $audioFiles[$fileDetail['file_id']]['file_path'],
                                     'id' => $audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                                 $copyFiles = $this->copypushFiles($uploadUpdateFiles);

                                 if (count($copyFiles['copied'])) {
                                     $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
                                 }

                                 if (!$copyFiles['status']) {
                                     return new JsonModel(array('success' => false, 'message' => 'unable to update city'));
                                 }

                             }

                             $updateData['file_path'] = $filePath;
                             $updateData['file_extension_type'] = $fileType;
                             $updateData['file_extension'] = $ext;
                             $updateData['duration'] = $duration;
                             $updateData['hash'] = $hash;
                         }
                         $tourismFileId = $fileDetail['edit_id'];
                         $updateResponse = $this->tourismFilesTable()->updatePlaceFiles($updateData, array('tourism_file_id' => $tourismFileId));

                     } else {
                         $filePath = $audioFiles[$fileDetail['file_id']]['file_path'];
                         $ext = $audioFiles[$fileDetail['file_id']]['file_extension'];
                         $duration = $audioFiles[$fileDetail['file_id']]['duration'];
                         $hash = $audioFiles[$fileDetail['file_id']]['hash'];
                         $fileType = $audioFiles[$fileDetail['file_id']]['file_extension_type'];

                         if ($audioFiles[$fileDetail['file_id']]['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                             $uploadFiles[] = array('old_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'new_path' => $audioFiles[$fileDetail['file_id']]['file_path'],
                                 'id' => $audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                         }
                         $uploadFileDetails[] = array(
                             'file_path' => $filePath,
                             'file_data_type' => \Admin\Model\TourismFiles::file_data_type_city,
                             'file_extension_type' => $fileType,
                             'file_extension' => $ext,
                             'status' => 1,
                             'duration' => $duration,
                             'file_language_type' => $fileLanaguage,
                             'hash' => $hash,
                             'file_name' => $fileName
                         );
                     }
                 }
                 $copyFiles= $this->copypushFiles($uploadFiles);

                 if(count($copyFiles['copied']))
                 {
                     $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
                 }
                 if(!$copyFiles['status'])
                 {

                     return new JsonModel(array('success'=>false,'message'=>'unable to update city'));
                 }
             }
            //   $imageFiles=implode(',',$imageFileAttachment);


            /* echo '<pre>';
            print_r($uploadFileDetails);
            exit;*/
            //   $imageFiles=implode(',',$imageFileAttachment);

            if($stateName=='')
            {
                $stateName=0;
            }
            $data=array(
                'country_id'=>$countryName,
                'state_id'=>$stateName,
                'city_name'=>$cityName,
                'city_description'=>$description,
                'status'=>1);

            $response=$this->citiesTable()->updateCity($data,array('id'=>$cityId));
            if($response){
                $counter = -1;
                if(count($uploadFileDetails)) {
                    foreach ($uploadFileDetails as $details) {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $cityId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
                }
                if(count($deleteFiles))
                {

                    $this->tourismFilesTable()->deletePlaceFiles($deleteFiles);
                }
                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update city'));
            }

        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countriesList=$this->countriesTable()->getActiveCountries();
        $languagesList=$this->languagesTable()->getLanguages();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $cityIdString = rtrim($paramId, "=");
        $cityIdString = base64_decode($cityIdString);
        $cityIdString = explode("=", $cityIdString);
        $cityId = array_key_exists(1, $cityIdString) ? $cityIdString[1] : 0;

        $cityDetails=$this->citiesTable()->getCityDetails($cityId);
        $imageFiles=array();
        $audioFiles=array();
        $imageCounter=-1;
        $audioCounter=-1;

        $cityInfo=array();
        foreach ($cityDetails as $city){
            $cityInfo['country_id']=$city['country_id'];
            $cityInfo['state_id']=$city['state_id'];
            $cityInfo['city_id']=$city['city_id'];
            $cityInfo['city_name']=$city['city_name'];
            $cityInfo['city_description']=$city['city_description'];
            $cityInfo['country_name']=strtolower($city['country_name']);
            if($city['file_extension_type']!=1)
            {
                $audioCounter++;
                $audioFiles[$audioCounter]['file_path'] = $city['file_path'];
                $audioFiles[$audioCounter]['file_language_type'] = $city['file_language_type'];
                $audioFiles[$audioCounter]['tourism_file_id'] = $city['tourism_file_id'];
                $audioFiles[$audioCounter]['file_name'] = $city['file_name'];
            }else
            {
                $imageCounter++;
                $imageFiles[$imageCounter]['file_path'] = $city['file_path'];
                $imageFiles[$imageCounter]['file_language_type'] = $city['file_language_type'];
                $imageFiles[$imageCounter]['tourism_file_id'] = $city['tourism_file_id'];
                $imageFiles[$imageCounter]['file_name'] = $city['file_name'];
            }
        }
        $stateList=array();

          if($cityInfo['country_name']=='india')
          {
              $stateList=$this->statesTable()->getStates(array('status'=>1,'country_id'=>$cityInfo['country_id']));
          }

        return new ViewModel(array('countries'=>$countriesList,"languages"=>$languagesList,'stateList'=>$stateList,'imageUrl'=>$this->filesUrl(),'cityDetails'=>$cityInfo,'audioFiles'=>$audioFiles,'imageFiles'=>$imageFiles));
    }

    public function deleteCityAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            $request = $this->getRequest()->getPost();
            $cityId=$request['city_id'];
            $response=$this->citiesTable()->updateCity(array('status'=>0),array('id'=>$cityId));
            if($response)
            {
                $placeUpdate=$this->tourismPlacesTable()->updateTourism(array('status'=>0),array('city_id'=>$cityId,'status'=>1));
                $toursUpdate=$this->placePriceTable()->updatePlacePrice(array('status'=>0),array('city_id'=>$cityId,'status'=>1));
                $response=$this->tourismFilesTable()->updatePlaceFiles(array('status'=>0),array('file_data_id'=>$cityId,'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_city));

                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to update'));
            }
        }
    }
    //** city end */
    
    //** places start */
    public function addPlacesAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);

            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $files = $this->getRequest()->getFiles();
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
           ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $countryName=$request['country_id'];
            $stateName=$request['state_id'];
            $cityName=$request['city_id'];
            $placeName=$request['place_name'];
            $description=$request['description'];
            $fileDetails=$request['file_details'];

            $validFiles=array('mp3','mp4','wav','mpeg','avi');
            $audioFiles=array('mp3','wav','mpeg');
            $videoFiles=array('mp4','mpg','avi');
            $validImageFiles=array('png','jpg','jpeg');
            $fileDetails=json_decode($fileDetails,true);
            $uploadFileDetails=array();

            $cityName=trim($cityName);
            if($cityName=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please enter city name"));
            }
            $check=$this->tourismPlacesTable()->getField(array('city_name'=>$cityName,'status'=>1),'id');

            if($check)
            {
                return new JsonModel(array("success" => false, "message" => "City Already exists"));
            }
              if($stateName=='')
              {
                  $stateName=0;
              }
            $imageFileAttachment=array();
            $aes=new Aes();
            $fileIds=explode(",",$request['file_Ids']);

            $getFiles=$this->temporaryFiles()->getFiles($fileIds);

            $uploadFiles=array();
            foreach ($getFiles['images'] as $images)
            {

                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places,
                    'file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $images['file_extension'],
                    'status'=>1,
                    'duration'=>0 ,
                    'file_language_type' => 0,
                    'hash'=>'',
                    'file_name' => $images['file_name']
                );
                if($images['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                {
                    $uploadFiles[]=array('old_path'=>$images['file_path'],'new_path'=>$images['file_path'],'id'=>$images['temporary_files_id']);

                }

            }
            $audioFiles=$getFiles['audioFiles'];

            foreach ($fileDetails as $fileDetail)
            {
                $fileName = $fileDetail['file_name'];
                $fileLanaguage = $fileDetail['lanaguage'];
                $filePath=$audioFiles[$fileDetail['file_id']]['file_path'];
                $ext=$audioFiles[$fileDetail['file_id']]['file_extension'];
                $duration=$audioFiles[$fileDetail['file_id']]['duration'];
                $hash=$audioFiles[$fileDetail['file_id']]['hash'];
                $fileType=$audioFiles[$fileDetail['file_id']]['file_extension_type'];

                if($audioFiles[$fileDetail['file_id']]['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                {
                    $uploadFiles[]=array('old_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],'new_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],
                        'id'=>$audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                }
                $uploadFileDetails[] = array(
                    'file_path' => $filePath,
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places,
                    'file_extension_type'=>$fileType,
                    'file_extension' => $ext,
                    'status'=>1,
                    'duration'=>$duration,
                    'file_language_type' => $fileLanaguage,
                    'hash'=>$hash,
                    'file_name' => $fileName
                );
            }

            //   $imageFiles=implode(',',$imageFileAttachment);

            $copyFiles= $this->copypushFiles($uploadFiles);

            if(count($copyFiles['copied']))
            {
                $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
            }
            if(!$copyFiles['status'])
            {

                return new JsonModel(array('success'=>false,'message'=>'unable to add Place'));
            }

            $placeId="";
            $data=array(
                'country_id'=>$countryName,
                'state_id'=>$stateName,
                'city_id'=>$cityName,
                'place_name'=>$placeName,
                'place_description'=>$description,
                'status'=>1
            );

            $response=$this->tourismPlacesTable()->addTourism($data);

            if($response['success'])
            {
                $placeId=$response['id'];
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to add city'));
            }

            $counter = -1;
            if(count($uploadFileDetails)) {
                foreach ($uploadFileDetails as $details) {
                    $counter++;
                    $uploadFileDetails[$counter]['file_data_id'] = $placeId;
                    $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                    $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                }
               
                $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
            }
            return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countriesList=$this->countriesTable()->getActiveCountriesForPlaces();
        $languagesList=$this->languagesTable()->getLanguages();
        return new ViewModel(array('countries'=>$countriesList,"languages"=>$languagesList));
    }
    public function editPlaceAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
           ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");

            $request = $this->getRequest()->getPost();

            $files = $this->getRequest()->getFiles();

            $countryName=$request['country_id'];
            $stateName=$request['state_id'];
            $cityName=$request['city_id'];
            $placeId=$request['place_id'];
            $placeName=$request['place_name'];
            $description=$request['description'];
            $fileDetails=$request['file_details'];

            $validFiles=array('mp3','mp4','wav','mpeg','avi');
            $audioFiles=array('mp3','wav','mpeg');
            $videoFiles=array('mp4','mpg','avi');
            $validImageFiles=array('png','jpg','jpeg');
            $fileDetails=json_decode($fileDetails,true);
            $uploadFileDetails=array();
            $deletedImages=json_decode($request['deleted_images'],true);
            $deletedAudio=json_decode($request['deleted_audio'],true);

            $deleteFiles=array_merge($deletedAudio,$deletedImages);


            $placeName=trim($placeName);
            if($placeName=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please enter Place name"));
            }


            if($stateName=='')
            {
                $stateName=0;
            }

            $imageFileAttachment=array();
            $aes=new Aes();
            $fileIds=explode(",",$request['file_Ids']);

            $getFiles=$this->temporaryFiles()->getFiles($fileIds);

            $uploadFiles=array();
            foreach ($getFiles['images'] as $images)
            {

                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places,
                    'file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $images['file_extension'],
                    'status'=>1,
                    'duration'=>0 ,
                    'file_language_type' => 0,
                    'hash'=>'',
                    'file_name' => $images['file_name']
                );
                if($images['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                {
                    $uploadFiles[]=array('old_path'=>$images['file_path'],'new_path'=>$images['file_path'],'id'=>$images['temporary_files_id']);
                }
            }

            $audioFiles=$getFiles['audioFiles'];

            foreach ($fileDetails as $fileDetail) {

                $fileName = $fileDetail['file_name'];
                $fileLanaguage = $fileDetail['lanaguage'];

                if(isset($fileDetail['edit_id'])&& $fileDetail['edit_id']!=0)
                {

                    $updateData=array('file_name'=>$fileName,'file_language_type'=>$fileLanaguage);
                    if(isset($fileDetail['file_id']) && $fileDetail['file_id'] )
                    {
                        $filePath=$audioFiles[$fileDetail['file_id']]['file_path'];
                        $ext=$audioFiles[$fileDetail['file_id']]['file_extension'];
                        $duration=$audioFiles[$fileDetail['file_id']]['duration'];
                        $hash=$audioFiles[$fileDetail['file_id']]['hash'];
                        $fileType=$audioFiles[$fileDetail['file_id']]['file_extension_type'];

                        if($audioFiles[$fileDetail['file_id']]['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                        {
                            $uploadUpdateFiles[]=array('old_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],'new_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],
                                'id'=>$audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                            $copyFiles= $this->copypushFiles($uploadUpdateFiles);

                            if(count($copyFiles['copied']))
                            {
                                $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
                            }

                            if(!$copyFiles['status'])
                            {
                                return new JsonModel(array('success'=>false,'message'=>'unable to update Place'));
                            }

                        }

                        $updateData['file_path']=$filePath;
                        $updateData['file_extension_type']=$fileType;
                        $updateData['file_extension']=$ext;
                        $updateData['duration']=$duration;
                        $updateData['hash']=$hash;
                    }
                    $tourismFileId= $fileDetail['edit_id'];
                    $updateResponse=$this->tourismFilesTable()->updatePlaceFiles($updateData,array('tourism_file_id'=>$tourismFileId));

                }
                else
                {
                    $filePath=$audioFiles[$fileDetail['file_id']]['file_path'];
                    $ext=$audioFiles[$fileDetail['file_id']]['file_extension'];
                    $duration=$audioFiles[$fileDetail['file_id']]['duration'];
                    $hash=$audioFiles[$fileDetail['file_id']]['hash'];
                    $fileType=$audioFiles[$fileDetail['file_id']]['file_extension_type'];

                    if($audioFiles[$fileDetail['file_id']]['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                    {
                        $uploadFiles[]=array('old_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],'new_path'=>$audioFiles[$fileDetail['file_id']]['file_path'],
                            'id'=>$audioFiles[$fileDetail['file_id']]['temporary_files_id']);

                    }
                    $uploadFileDetails[] = array(
                        'file_path' => $filePath,
                        'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places,
                        'file_extension_type'=>$fileType,
                        'file_extension' => $ext,
                        'status'=>1,
                        'duration'=>$duration,
                        'file_language_type' => $fileLanaguage,
                        'hash'=>$hash,
                        'file_name' => $fileName
                    );
                }

            }

            //   $imageFiles=implode(',',$imageFileAttachment);

            $copyFiles= $this->copypushFiles($uploadFiles);

            if(count($copyFiles['copied']))
            {
                $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
            }

            if(!$copyFiles['status'])
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to update Place'));
            }

            $data=array(
                'country_id'=>$countryName,
                'state_id'=>$stateName,
                'city_id'=>$cityName,
                'place_name'=>$placeName,
                'place_description'=>$description,
                'status'=>1
            );

            $response=$this->tourismPlacesTable()->updateTourism($data,array('tourism_place_id'=>$placeId));

            if($response)
            {
                $counter = -1;

                if(count($uploadFileDetails))
                {
                    foreach ($uploadFileDetails as $details)
                    {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $placeId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
                }


                if(count($deleteFiles))
                {
                    $this->tourismFilesTable()->deletePlaceFiles($deleteFiles);
                }

                return new JsonModel(array('success'=>true,'message'=>'updated successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to update place'));
            }


        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countriesList=$this->countriesTable()->getActiveCountriesForPlaces();
        $languagesList=$this->languagesTable()->getLanguages();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $placeIdString = rtrim($paramId, "=");
        $placeIdString = base64_decode($placeIdString);
        $placeIdString = explode("=", $placeIdString);
        $placeId = array_key_exists(1, $placeIdString) ? $placeIdString[1] : 0;

        $placeDetails=$this->tourismPlacesTable()->getPlaceDetails($placeId);
        $imageFiles=array();
        $audioFiles=array();
        $imageCounter=-1;
        $audioCounter=-1;

        $placeInfo=array();


        foreach ($placeDetails as $place){
            $placeInfo['country_id']=$place['country_id'];
            $placeInfo['state_id']=$place['state_id'];
            $placeInfo['city_id']=$place['city_id'];
            $placeInfo['place_id']=$place['tourism_place_id'];
            $placeInfo['place_name']=$place['place_name'];
            $placeInfo['place_description']=$place['place_description'];
            $placeInfo['country_name']=$place['country_name'];
            if($place['file_extension_type']!=1)
            {
                $audioCounter++;
                $audioFiles[$audioCounter]['file_path'] = $place['file_path'];
                $audioFiles[$audioCounter]['file_language_type'] = $place['file_language_type'];
                $audioFiles[$audioCounter]['tourism_file_id'] = $place['tourism_file_id'];
                $audioFiles[$audioCounter]['file_name'] = $place['file_name'];
            }else
            {
                $imageCounter++;
                $imageFiles[$imageCounter]['file_path'] = $place['file_path'];
                $imageFiles[$imageCounter]['file_language_type'] = $place['file_language_type'];
                $imageFiles[$imageCounter]['tourism_file_id'] = $place['tourism_file_id'];
                $imageFiles[$imageCounter]['file_name'] = $place['file_name'];
            }
        }
        $stateList=array();
        $citiesList=array();
        if(strtolower($placeInfo['country_name']) =='india')
        {
            $stateList=$this->statesTable()->getStates(array('status'=>1,'country_id'=>$placeInfo['country_id']));
            $citiesList=$this->citiesTable()->getCities(array('state_id'=>$placeInfo['state_id']));
        }else{
            $citiesList=$this->citiesTable()->getCities(array('country_id'=>$placeInfo['country_id']));
        }


        return new ViewModel(array('countries'=>$countriesList,"languages"=>$languagesList,'stateList'=>$stateList,'cityList'=>$citiesList,'imageUrl'=>$this->filesUrl(),'placeDetails'=>$placeInfo,'audioFiles'=>$audioFiles,'imageFiles'=>$imageFiles));
    }

    //** places end */
    
    public function editAction()
    {

    }
    public function getStatesAction()
    {
         $request = $this->getRequest()->getPost();

         $countryId=$request['country_id'];
         $where=array('country_id'=>$countryId,'status'=>1);
         $statesList=$this->statesTable()->getStates($where);


         return new JsonModel(array('success'=>true,'states'=>$statesList));
    }
    public function getCitiesAction()
    {
        $request = $this->getRequest()->getPost();
              $countryId=$request['country_id'];
        $stateId=$request['state_id'];
              if($stateId)
              {
                  $where=array('state_id'=>$stateId);
              }else{
                  $where=array("country_id"=>$countryId);
              }
        $citiesList=$this->citiesTable()->getCities($where);
        return new JsonModel(array('success'=>true,'cities'=>$citiesList));
    }
    public function deleteAction()
    {
        $view=new ViewModel();
        $view->setTerminal(true);
        return  $view;
    }


     public function loginAction()
     {
         if ($this->getRequest()->isXmlHttpRequest())
         {
             $request = $this->getRequest()->getPost();
            // var_dump($request);exit;
            $userName=$request['user_name'];
            $password=$request['password'];
             $user = $this->userTable()->authenticateUser($userName, $password);
             if(count($user) && $user['role']==\Admin\Model\User::Admin_role)
            {
                if(ob_get_length())
                {
                    ob_end_clean();
                }
                session_unset();
                $this->getAuthDbTable()
                    ->setTableName('users')
                    ->setIdentityColumn("email")
                    ->setCredentialColumn('user_id')
                    ->setIdentity($user["email"])
                    ->setCredential($user['user_id']);
                $this->getAuthService()
                    ->setAdapter($this->getAuthDbTable())
                    ->setStorage($this->getSessionStorage());
                $this->getSessionManager()->rememberMe(60 * 60 * 24 * 30 * 3);
                $result = $this->getAuthService()->authenticate();


                if ($result->isValid()) {
                    $resultRow = (array)$this->getAuthDbTable()->getResultRowObject(array('user_id', 'email', "role"));
                    $this->getSessionStorage()->write($resultRow);
                    $_SESSION['Zend_Auth']->storage['admin_name']=$user['user_name'];
                    return new JsonModel(array("success" => true, "message" => "Valid Credentials"));
                }
                return new JsonModel(array("success" => false, "message" => "Invalid Credentials", "errorCode" => 4));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'invalid credentials'));
            }
         }


         if($this->getLoggedInUserId())
         {
             return $this->redirect()->toUrl($this->getBaseUrl().'/a_dMin/places-list');
         }

     }

     public function twisttLoginAction()
     {
         if ($this->getRequest()->isXmlHttpRequest())
         {
             $request = $this->getRequest()->getPost();
            // var_dump($request);exit;
            $userName=$request['user_name'];
            $password=$request['password'];
             $user = $this->tbeDetailsTable()->authenticateUser($userName, $password);
             if(count($user)) // && ($user['role']==\Admin\Model\TbeDetails::Twistt_TA_role || $user['role']==\Admin\Model\TbeDetails::Twistt_TBE_role))
            {
                if(ob_get_length())
                {
                    ob_end_clean();
                }
                session_unset();
                $this->getAuthDbTable()
                    ->setTableName('tbe_details')
                    ->setIdentityColumn("tbe_mobile")
                    ->setCredentialColumn('user_id')
                    ->setIdentity($user["tbe_mobile"])
                    ->setCredential($user['user_id']);
                $this->getAuthService()
                    ->setAdapter($this->getAuthDbTable())
                    ->setStorage($this->getSessionStorage());
                $this->getSessionManager()->rememberMe(60 * 60 * 24 * 30 * 3);
                $result = $this->getAuthService()->authenticate();


                if ($result->isValid()) {
                    $resultRow = (array)$this->getAuthDbTable()->getResultRowObject(array('user_id', 'tbe_mobile', "role"));
                    $tbeResult['tbe_id'] = $resultRow['user_id'];
                    $tbeResult['tbe_mobile'] = $resultRow['tbe_mobile'];
                    $tbeResult['tbe_role'] = $resultRow['role'];
                    $this->getSessionStorage()->write($tbeResult);
                    $_SESSION['Laminas_Auth']->storage['tuser_name']=$user['tbe_name'];
                    return new JsonModel(array("success" => true, "message" => "Valid Credentials"));
                }
                return new JsonModel(array("success" => false, "message" => "Invalid Credentials", "errorCode" => 4));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'invalid credentials'));
            }
         }
     }

     public function twisttseLoginAction()
     {
         if ($this->getRequest()->isXmlHttpRequest())
         {
             $request = $this->getRequest()->getPost();
            // var_dump($request);exit;
            $userName=$request['user_name'];
            $password=$request['password'];
             $user = $this->taConsultantDetailsTable()->authenticateUser($userName, $password);
             if(count($user)) // && ($user['role']==\Admin\Model\TbeDetails::Twistt_TA_role || $user['role']==\Admin\Model\TbeDetails::Twistt_TBE_role))
            {
                if(ob_get_length())
                {
                    ob_end_clean();
                }
                session_unset();
                $this->getAuthDbTable()
                    ->setTableName('ta_consultant_details')
                    ->setIdentityColumn("mobile")
                    ->setCredentialColumn('id')
                    ->setIdentity($user["mobile"])
                    ->setCredential($user['id']);
                $this->getAuthService()
                    ->setAdapter($this->getAuthDbTable())
                    ->setStorage($this->getSessionStorage());
                $this->getSessionManager()->rememberMe(60 * 60 * 24 * 30 * 3);
                $result = $this->getAuthService()->authenticate();


                if ($result->isValid()) {
                    $resultRow = (array)$this->getAuthDbTable()->getResultRowObject(array('id', 'mobile'));
                    $seResult['se_id'] = $resultRow['id'];
                    $seResult['se_mobile'] = $resultRow['mobile'];
                    $this->getSessionStorage()->write($seResult);
                    $_SESSION['Laminas_Auth']->storage['suser_name']=$user['name'];
                    return new JsonModel(array("success" => true, "message" => "Valid Credentials"));
                }
                return new JsonModel(array("success" => false, "message" => "Invalid Credentials", "errorCode" => 4));
            }else
            {
                return new JsonModel(array('success'=>false,'message'=>'invalid credentials'));
            }
         }
     }

     public function twisttLogoutAction()
    {
        session_unset();
        return $this->redirect()->toUrl($this->getBaseUrl()."/twistt");
    }

    public function twisttseLogoutAction()
    {
        session_unset();
        return $this->redirect()->toUrl($this->getBaseUrl()."/twisttse");
    }

    public function twisttCpwdAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            if(!$this->getLoggedInTbeId())
            {
                  return new JsonModel(array('success'=>false,'message'=>'Please Login To Change Password'));
            }
            $userId=$this->getLoggedInTbeId();
            $currentPassword=$request['current_password'];
            $newPassword=$request['new_password'];
            $checkPassword=$this->tbeDetailsTable()->checkPasswordWithUserId($userId,$currentPassword);
              if(count($checkPassword))
              {
                  $aes=new Aes();
                  $encodeContent = $aes->encrypt($newPassword);
                  $encodeString = $encodeContent['password'];
                  $hash = $encodeContent['hash'];
                  $date = date("Y-m-d H:i:s");
                  $tbeOldPwdDetails = $this->tbeDetailsTable()->getTbeDetails($userId);
                  $oldPwdData = array('tbe_id'=>$userId,'old_pwd'=>$tbeOldPwdDetails['pwd'],'old_hash'=>$tbeOldPwdDetails['hash'], 'created_at'=>$date, 'updated_at'=>$date);
                  $currentPasswordInsert=$this->tbeOldPasswordsTable()->addTbeOldPassword($oldPwdData);

                  if($currentPasswordInsert)
                   {
                       $currentPasswordUpdate=$this->tbeDetailsTable()->setTbeDetails(array('pwd'=>$encodeString, 'hash'=>$hash),array('user_id'=>$userId));
                       return new JsonModel(array('success'=>false,'message'=>'Updated successfully'));

                   }else{
                       return new JsonModel(array('success'=>false,'message'=>'Something went wrong try again after sometime'));

                   }

              }else{
                  return new JsonModel(array('success'=>false,'message'=>'current password does not match'));

              }
        }
    }

    public function twisttseCpwdAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            if(!$this->getLoggedInSeId())
            {
                  return new JsonModel(array('success'=>false,'message'=>'Please Login To Change Password'));
            }
            $userId=$this->getLoggedInSeId();
            $currentPassword=$request['current_password'];
            $newPassword=$request['new_password'];
            $checkPassword=$this->taConsultantDetailsTable()->checkPasswordWithUserId($userId,$currentPassword);
              if(count($checkPassword))
              {
                  $aes=new Aes();
                  $encodeContent = $aes->encrypt($newPassword);
                  $encodeString = $encodeContent['password'];
                  $hash = $encodeContent['hash'];
                  $date = date("Y-m-d H:i:s");
                  $seOldPwdDetails = $this->taConsultantDetailsTable()->getTaConsultantDetails($userId);
                  $oldPwdData = array('se_id'=>$userId,'old_pwd'=>$seOldPwdDetails['pwd'],'old_hash'=>$seOldPwdDetails['hash'], 'created_at'=>$date, 'updated_at'=>$date);
                  $currentPasswordInsert=$this->seOldPasswordsTable()->addSeOldPassword($oldPwdData);

                  if($currentPasswordInsert)
                   {
                       $currentPasswordUpdate=$this->taConsultantDetailsTable()->setTaConsultantDetails(array('pwd'=>$encodeString, 'hash'=>$hash),array('id'=>$userId));
                       return new JsonModel(array('success'=>false,'message'=>'Updated successfully'));

                   }else{
                       return new JsonModel(array('success'=>false,'message'=>'Something went wrong try again after sometime'));

                   }

              }else{
                  return new JsonModel(array('success'=>false,'message'=>'current password does not match'));

              }
        }
    }

      public function fileUploadRowAction()
      {

          $request = $this->getRequest()->getPost();
          $languagesList=$this->languagesTable()->getLanguages();
          $rowNumber=$request['row_number'];
          $rowCount=$request['rows_count'];
          $view=new ViewModel(array("languages"=>$languagesList,'rowNumber'=>$rowNumber,'numberOfrows'=>$rowCount));
          $view->setTerminal(true);
          return $view;

      }

      public function placesListAction()
      {

          if(!$this->getLoggedInUserId())
          {
              return $this->redirect()->toUrl($this->getBaseUrl());
          }
          $tourismList=$this->tourismPlacesTable()->getTourismList(array('limit'=>10,'offset'=>0));


         $tourismListCount=$this->tourismPlacesTable()->getTourismListCount();
         $totalCount=0;

              if(count($tourismListCount))
              {
                  $totalCount= count($tourismListCount);
              }

           return new ViewModel(array('tourismList'=>$tourismList,'totalCount'=>$totalCount));
      }

      public function loadPlacesListAction()
      {
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
                      if(isset($filterData['download']))
                      {
                          if(isset($filterData['download']['text']) && $filterData['download']['text'])
                          {
                              $searchData['download']=$filterData['download']['text'];
                          }
                          if(isset($filterData['download']['order']) && $filterData['download']['order'])
                          {
                              $searchData['download_order']=$filterData['download']['order'];
                          }
                      }
                      if(isset($filterData['likes']))
                      {
                          if(isset($filterData['likes']['text']) && $filterData['likes']['text'])
                          {
                              $searchData['likes']=$filterData['likes']['text'];
                          }
                          if(isset($filterData['likes']['order']) && $filterData['likes']['order'])
                          {
                              $searchData['likes_order']=$filterData['likes']['order'];
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

                  $tourismListCount=$this->tourismPlacesTable()->getTourismListCount($searchData);
                       if(count($tourismListCount))
                       {
                           $totalCount= count($tourismListCount);
                       }
              }

              $tourismList = $this->tourismPlacesTable()->getTourismList($searchData);
              $view = new ViewModel(array('tourismList' => $tourismList,'offset'=>$offset,'type'=>$type,'totalCount'=>$totalCount));
              $view->setTerminal(true);
              return $view;
          }
      }

      public function sendFcmNoticationAction()
      {
              $sendPush=new SendNotification();
              $result=$sendPush->sendPushNotificationToFCMSever();
           $date=date('d-M-Y H:i');
          $password=$this->random_password(6);
          $this->sendPassword('banu.nagumothu@gmail.com','password generated at '.$date,'mail-password',array('password'=>$password));

          $hash = $this->generateHash();

          $cipher = BlockCipher::factory('mcrypt',
              array('algorithm' => 'aes')
          );
          /*Set your encryption key/salt*/
          $cipher->setKey($hash);

          /*Encrypt the data*/
          $encryptedPassword = $cipher->encrypt($password);
          $this->passwordTable()->updatePassword(array('status'=>0),array());

          $this->passwordTable()->addPassword(array('password'=>$encryptedPassword,'hash'=>$hash,'status'=>1));
      }

      public function addCronAction()
      {
          $cron=new CronJob();
          $cronPath=$this->getBaseUrl().'/admin/admin/send-fcm-notication';
                if(isset($_GET['time']) && isset($_GET['password']))
                {
                    $password=$_GET['password'];
                    $passwordCheck=$this->passwordTable()->checkPassword($password);
                    if(count($passwordCheck))
                    {
                        $startTime = $_GET['time'];
                        $since_date = date("Y-m-d H:i:s", strtotime($startTime));
                        $minutes = date("i", strtotime($since_date));
                        $hours = date("h", strtotime($since_date));
                        $days = date("d", strtotime($since_date));
                        $months = date("m", strtotime($since_date));
                        $cronSchedule = "" . $minutes . " " . $hours . " " . $days . " " . $months . " *";
                        $cron->addJob($cronSchedule . " /usr/bin/curl -o temp.txt " . $cronPath);
                        echo 'success';
                        exit;
                    }else
                    {
                        echo 'Invalid';
                        exit;
                    }
                }else{
                    echo 'Invalid';
                    exit;
                }
      }
      /*public function editPlaceAction()
      {
          if($this->getRequest()->isXmlHttpRequest())
          {
              //  $request = json_decode(file_get_contents("php://input"),true);
              $request = $this->getRequest()->getPost();
              //print_r($request);exit;
              $files = $this->getRequest()->getFiles();
              $countryId=$request['country_id'];
              $cityId=$request['city_id'];
              $stateId=$request['state_id'];
              $placeName=$request['place_name'];
              $placeDescription=$request['description'];
              $fileDetails=$request['file_details'];
               $tourismPlaceId=$request['tourism_place_id'];
              $validFiles=array('mp3','mp4','wav','mpeg','avi');
              $audioFiles=array('mp3','wav','mpeg');
              $videoFiles=array('mp4','mpg','avi');
              $validImageFiles=array('png','jpg','jpeg');
              $fileDetails=json_decode($fileDetails,true);
              $uploadFileDetails=array();
              $deletedImages=json_decode($request['deleted_images'],true);
              $deletedAudio=json_decode($request['deleted_audio'],true);

             $deleteFiles=array_merge($deletedAudio,$deletedImages);
              $imageFileAttachment=array();

              if(isset($files['images_files']))
              {

                  foreach ($files['images_files'] as $attachment)
                  {

                      $imagesPath = '';
                      $pdf_to_image = "";
                      $filename = $attachment['name'];
                      $fileExt = explode(".", $filename);
                      $ext = end($fileExt) ? end($fileExt) : "";
                      $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                      $filename = $filenameWithoutExt . "." . $ext;
                      $filePath = "data/images";
                      @mkdir(getcwd() . "/public/" . $filePath,0777,true);

                      $filePath = $filePath . "/" . $filename;

                      if(!in_array(strtolower($ext), $validImageFiles))
                      {

                          return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));

                      }
                      if (move_uploaded_file($attachment['tmp_name'], getcwd() . "/public/" . $filePath))
                      {

                          $imagesPath = $filePath;
                          $localImagePath = getcwd() . "/public/" . $filePath;
                          chmod(getcwd() . "/public/" . $filePath, 0777);

                      }

                      $uploadFileDetails[] = array('file_path' => $imagesPath, 'file_type'=>1,'file_upload_type' => $ext, 'file_language_type' => 0,'duration'=>0,'file_name' => $attachment['name'],'status'=>1);

                  }

              }

              foreach ($fileDetails as $fileDetail)
              {
                  $fileName = $fileDetail['file_name'];
                  $fileLanaguage = $fileDetail['lanaguage'];
                      if(isset($files['mediaFiles_'.$fileDetail['file_id']]) )
                      {
                          $uploadFile = $files['mediaFiles_'.$fileDetail['file_id']];
                          $imagesPath = '';
                          $pdf_to_image = "";
                          $filename = $uploadFile['name'];
                          $fileExt = explode(".", $filename);
                          $ext = end($fileExt) ? end($fileExt) : "";
                          $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                          $filename = $filenameWithoutExt . "." . $ext;
                          $filePath = "data/attachments";
                          @mkdir(getcwd() . "/public/" . $filePath,0777,true);
                          $filePath = $filePath . "/" . $filename;
                          if (!in_array(strtolower($ext), $validFiles)) {
                              return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                          }

                          if (move_uploaded_file($uploadFile['tmp_name'], getcwd() . "/public/" . $filePath)) {
                              $imagesPath = $filePath;
                              $localImagePath = getcwd() . "/public/" . $filePath;
                              chmod(getcwd() . "/public/" . $filePath, 0777);
                              $duration='00:00:00';
                              $fileDuration=$this->getDuration(getcwd() . "/public/" . $filePath);
                              if($fileDuration)
                              {
                                  $duration=$fileDuration;
                              }
                          }
                          $ext=strtolower($ext);

                          if(in_array($ext,$audioFiles))
                          {
                              $fileType=0;
                          }else{
                              $fileType=2;
                          }

                      }

                      if(isset($fileDetail['edit_id'])&& $fileDetail['edit_id']!=0)
                      {

                          $updateData=array('file_name'=>$fileName,'file_language_type'=>$fileLanaguage);
                          if(isset($files['mediaFiles_'.$fileDetail['file_id']]))
                          {
                              $updateData['file_path']=$filePath;
                              $updateData['file_type']=$fileType;
                              $updateData['file_upload_type']=$ext;
                              $updateData['duration']=$duration;
                          }
                            $tourismFileId= $fileDetail['edit_id'];
                          $updateResponse=$this->tourismFilesTable()->updatePlaceFiles($updateData,array('tourism_file_id'=>$tourismFileId));
                      }else{
                          $uploadFileDetails[] = array('file_path' => $imagesPath,'file_type'=>$fileType, 'file_upload_type' => $ext,
                              'file_language_type' => $fileLanaguage,
                              'file_name' => $fileName,'duration'=>$duration,'status'=>1);
                      }
              }

              //   $imageFiles=implode(',',$imageFileAttachment);
              $response=$this->tourismPlacesTable()->updateTourism(array('place_name'=>$placeName,
                  'place_description'=>$placeDescription,
                  'country_id'=>$countryId,
                  'city_id'=>$cityId,
                  'state_id'=>$stateId),array('tourism_place_id'=>$tourismPlaceId));


              if($response)
              {
                  $counter = -1;
                  if(count($uploadFileDetails)) {
                      foreach ($uploadFileDetails as $details)
                      {
                          $counter++;
                          $uploadFileDetails[$counter]['tourism_place_id'] = $tourismPlaceId;
                          $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                          $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                      }
                      $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
                  }
                  if(count($deleteFiles))
                  {
                      $this->tourismFilesTable()->deletePlaceFiles($deleteFiles);
                  }
                  return new JsonModel(array('success'=>true,'message'=>'Deleted successfully'));
              }else
              {
                  return new JsonModel(array('success'=>false,'message'=>'unable to update'));
              }
          }
          $paramId = $this->params()->fromRoute('id', '');
          if (!$paramId)
          {
              return $this->redirect()->toUrl($this->getBaseUrl());
          }

          $placeIdString = rtrim($paramId, "=");
          $placeIdString = base64_decode($placeIdString);
          $placeIdString = explode("=", $placeIdString);
          $placeId = array_key_exists(1, $placeIdString) ? $placeIdString[1] : 0;


               $placeDetails=$this->tourismPlacesTable()->getTourismPlaceDetailsAction($placeId);
          if (!$placeDetails)
          {
              return $this->redirect()->toUrl($this->getBaseUrl());
          }
          $countriesList=$this->countriesTable()->getCountries();
          $languagesList=$this->languagesTable()->getLanguages();
          $statesList=$this->statesTable()->getStates(array('country_id'=>$placeDetails[0]['country_id']));
          $citiesList=$this->citiesTable()->getCities(array('state_id'=>$placeDetails[0]['state_id']));
          $placeInfo=array();
          $imageFiles=array();
          $audioFiles=array();
          $imageCounter=-1;
          $audioCounter=-1;

            foreach ($placeDetails as $place)
            {
                  $placeInfo['country_id']=$place['country_id'];
                  $placeInfo['state_id']=$place['state_id'];
                  $placeInfo['city_id']=$place['city_id'];
                  $placeInfo['place_name']=$place['place_name'];
                  $placeInfo['place_description']=$place['place_description'];
                  if($place['file_type']!=1){
                      $audioCounter++;
                      $audioFiles[$audioCounter]['file_path'] = $place['file_path'];
                      $audioFiles[$audioCounter]['file_language_type'] = $place['file_language_type'];
                      $audioFiles[$audioCounter]['tourism_file_id'] = $place['tourism_file_id'];
                      $audioFiles[$audioCounter]['file_name'] = $place['file_name'];
                  }else{
                      $imageCounter++;
                      $imageFiles[$imageCounter]['file_path']=$place['file_path'];
                      $imageFiles[$imageCounter]['file_language_type']=$place['file_language_type'];
                      $imageFiles[$imageCounter]['tourism_file_id']=$place['tourism_file_id'];
                      $imageFiles[$imageCounter]['file_name']=$place['file_name'];
                  }
            }
              return new ViewModel(array('countries'=>$countriesList,'stateList'=>$statesList,'place_id'=>$placeId,'cityList'=>$citiesList,'languages'=>$languagesList,'placeDetails'=>$placeInfo,'audioFiles'=>$audioFiles,'imageFiles'=>$imageFiles));
      }*/
      public function deletePlaceAction()
      {
          if ($this->getRequest()->isXmlHttpRequest())
          {
              //  $request = json_decode(file_get_contents("php://input"),true);
              $request = $this->getRequest()->getPost();
          $placeId=$request['place_id'];
          $response=$this->tourismPlacesTable()->updateTourism(array('status'=>0),array('tourism_place_id'=>$placeId));
          if($response)
          {
                $toursUpdate=$this->placePriceTable()->deleteTourPlace($placeId);

              $response=$this->tourismFilesTable()->updatePlaceFiles(array('status'=>0),array('file_data_id'=>$placeId,'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places));

              return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));

          }else
          {
              return new JsonModel(array('success'=>false,"message"=>'unable to update'));
          }
          }
      }public function deleteSeasonalSpecialsAction()
      {
          if ($this->getRequest()->isXmlHttpRequest()){
              //  $request = json_decode(file_get_contents("php://input"),true);
              $request = $this->getRequest()->getPost();
          $seasonalId=$request['data_id'];
          $response=$this->seasonalSpecialsTable()->updateSeasonalSpecials(array('status'=>0),array('seasonal_special_id'=>$seasonalId));
          if($response)
          {
              return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
          }else
          {
              return new JsonModel(array('success'=>false,"message"=>'unable to Delete'));
          }
          }
      }

      public function downloadListAction() //public function bookingListAction()
       {
           if(!$this->getLoggedInUserId())
           {
               return $this->redirect()->toUrl($this->getBaseUrl());
           }
           $bookingList=$this->bookingsTable()->bookingsListAdmin();

           $totalCount=$this->bookingsTable()->bookingsListAdminCount();
             if(count($totalCount))
             {
                 $totalCount=$totalCount['booking_count'];
             }
             
           return new ViewModel(array('bookingList'=>$bookingList,'totalCount'=>$totalCount));
       }
       public function loadDownloadListAction()
       {
           if ($this->getRequest()->isXmlHttpRequest()) {
               $request = $this->getRequest()->getPost();
               $searchData=array('limit'=>10,'offset'=>0);
               $type=$request['type'];
               $offset=0;
               $filterData=$request['filter'];
               if($filterData)
               {
                   $filterData=json_decode($filterData,true);
                   if(isset($filterData['booking_id']))
                   {
                       if(isset($filterData['booking_id']['text']) && $filterData['booking_id']['text'])
                       {
                           $searchData['booking_id']=$filterData['booking_id']['text'];
                       }
                       if(isset($filterData['booking_id']['order']) && $filterData['booking_id']['order'])
                       {
                           $searchData['booking_id_order']=$filterData['booking_id']['order'];
                       }
                   } 
                   if(isset($filterData['booking_date']))
                   {
                       if(isset($filterData['booking_date']['text']) && $filterData['booking_date']['text'])
                       {
                           $searchData['booking_date']=$filterData['booking_date']['text'];
                       }
                       if(isset($filterData['booking_date']['order']) && $filterData['booking_date']['order'])
                       {
                           $searchData['booking_date_order']=$filterData['booking_date']['order'];
                       }
                   } if(isset($filterData['amount']))
                   {
                       if(isset($filterData['amount']['text']) && $filterData['amount']['text'])
                       {
                           $searchData['amount']=$filterData['amount']['text'];
                       }
                       if(isset($filterData['amount']['order']) && $filterData['amount']['order'])
                       {
                           $searchData['amount_order']=$filterData['amount']['order'];
                       }
                   } if(isset($filterData['user']))
                   {
                       if(isset($filterData['user']['text']) && $filterData['user']['text'])
                       {
                           $searchData['user']=$filterData['user']['text'];
                       }
                       if(isset($filterData['user']['order']) && $filterData['user']['order'])
                       {
                           $searchData['user_order']=$filterData['user']['order'];
                       }
                   }

                      if(isset($filterData['tour_type']))
                      {
                          if(isset($filterData['tour_type']['text']) && $filterData['tour_type']['text'])
                          {
                              $searchData['tour_type']=$filterData['tour_type']['text'];
                          }
                      }
                   if(isset($filterData['activation_date']))
                   {
                       if(isset($filterData['activation_date']['text']) && $filterData['activation_date']['text'])
                       {
                           $searchData['activation_date']=$filterData['activation_date']['text'];
                       }
                       if(isset($filterData['activation_date']['order']) && $filterData['activation_date']['order'])
                       {
                           $searchData['activation_date_order']=$filterData['activation_date']['order'];
                       }
                   }
                   if(isset($filterData['expiry_date']))
                   {
                       if(isset($filterData['expiry_date']['text']) && $filterData['expiry_date']['text'])
                       {
                           $searchData['expiry_date']=$filterData['expiry_date']['text'];
                       }
                       if(isset($filterData['expiry_date']['order']) && $filterData['expiry_date']['order'])
                       {
                           $searchData['expiry_date_order']=$filterData['expiry_date']['order'];
                       }
                   }  if(isset($filterData['members_count']))
                   {
                       if(isset($filterData['members_count']['text']) && $filterData['members_count']['text'])
                       {
                           $searchData['members_count']=$filterData['members_count']['text'];
                       }
                       if(isset($filterData['members_count']['order']) && $filterData['members_count']['order'])
                       {
                           $searchData['members_count_order']=$filterData['members_count']['order'];
                       }
                   }
                   if(isset($filterData['mobile']))
                   {
                       if(isset($filterData['mobile']['text']) && $filterData['mobile']['text'])
                       {
                           $searchData['mobile']=$filterData['mobile']['text'];
                       }
                       if(isset($filterData['mobile']['order']) && $filterData['mobile']['order'])
                       {
                           $searchData['mobile_order']=$filterData['mobile']['order'];
                       }
                   }
               }
               if (isset($request['page_number'])) {
                   $pageNumber = $request['page_number'];
                   $offset = ($pageNumber * 10 - 10);
                   $limit = 10;
                   $searchData['offset'] = $offset;
                   $searchData['limit'] = $limit;
               }
               $totalCount = 0;
               
               if ($type && $type == 'search') {
                   $tourismListCount = $this->bookingsTable()->bookingsListAdminCount($searchData);

                   if (count($tourismListCount)) {
                       $totalCount=$tourismListCount['booking_count'];
                   }
               }
               $bookingList=$this->bookingsTable()->bookingsListAdmin($searchData);

               $view = new ViewModel(array('bookingList' => $bookingList,'offset'=>$offset,'type'=>$type,'totalCount'=>$totalCount));
               $view->setTerminal(true);
               return $view;
           }
       }
       public function addSlabPriceAction()
       {
           if ($this->getRequest()->isXmlHttpRequest()) 
           {
               $request = $this->getRequest()->getPost();
               $slabPriceIt=$request['slab_price_it'];
               $slabPriceWt=$request['slab_price_wt'];

              // $slabPriceIt=json_decode($slabPriceIt);
              // $slabPriceWt=json_decode($slabPriceWt);
                /*  foreach ($slabPriceIt as $price)
                  {

                  }*/
                  $savePriceWTData=array('price'=>$slabPriceWt,'tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour,'status'=>1,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                  $savePriceITData=array('price'=>$slabPriceIt,'tour_type'=>\Admin\Model\PlacePrices::tour_type_India_tour,'status'=>1,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));

                 $saveData=array($savePriceWTData,$savePriceITData);

                  $updatePrice=$this->priceSlabTable()->updatePrice(array('status'=>0),array());
                   $saveResponse=$this->priceSlabTable()->addMutiplePriceSlabs($saveData);

                   if($saveResponse){
                       return new JsonModel(array('success'=>true,"message"=>'updated successfully'));

                   }else{
                       return new JsonModel(array('success'=>false,"message"=>'unable to update'));
                   }
           }
           $priceList=$this->priceSlabTable()->priceDetailsAdmin();
           $priceListWt=array();
           $priceListIt=array();

               if(count($priceList))
               {
                   $priceListWt=  $this->search($priceList,'tour_type',\Admin\Model\PlacePrices::tour_type_World_tour);

                   if(count($priceListWt))
                   {
                       $priceListWt=json_decode($priceListWt[0]['price'],true);
                   }
                   $priceListIt=  $this->search($priceList,'tour_type',\Admin\Model\PlacePrices::tour_type_India_tour);
                   if(count($priceListIt))
                   {
                       $priceListIt=json_decode($priceListIt[0]['price'],true);
                   }
               }

           return new ViewModel(array('priceListWt'=>$priceListWt,"priceListIt"=>$priceListIt));
       }
       public function addCitySlabDaysAction()
       {
           if ($this->getRequest()->isXmlHttpRequest())
           {
               $request = $this->getRequest()->getPost();
               $days=$request['days'];
                   if($days=="" || $days==null)
                   {
                       return new JsonModel(array('success'=>false,"message"=>'unable to update'));
                   }
              // $slabPriceIt=json_decode($slabPriceIt);
              // $slabPriceWt=json_decode($slabPriceWt);
                /*  foreach ($slabPriceIt as $price)
                  {

                  }*/

                 $saveData=array('days'=>$days,'status'=>1);

                  $updatePrice=$this->cityTourSlabDaysTable()->updateSlabDays(array('status'=>0),array('status'=>1));
                   $saveResponse=$this->cityTourSlabDaysTable()->addSlabDays($saveData);

                   if($saveResponse['success'])
                   {
                       return new JsonModel(array('success'=>true,"message"=>'updated successfully'));

                   }else{
                       return new JsonModel(array('success'=>false,"message"=>'unable to update'));
                   }
           }
           $days=$this->cityTourSlabDaysTable()->getSlabDays();


           return new ViewModel(array('days'=>$days));
       }
       public function slabPricingListAction()
       {
           if(!$this->getLoggedInUserId())
           {
               return $this->redirect()->toUrl($this->getBaseUrl());
           }

           if ($this->getRequest()->isXmlHttpRequest())
           {
               $request = $this->getRequest()->getPost();
           }
           return new ViewModel();
       }
       public function downloadDetailsAction() //public function bookingDetailsAction()
       {

           $paramId = $this->params()->fromRoute('id', '');
           if (!$paramId)
           {
               return $this->redirect()->toUrl($this->getBaseUrl());
           }
           $bookingIdString = rtrim($paramId, "=");
           $bookingIdString = base64_decode($bookingIdString);
           $bookingIdString = explode("=", $bookingIdString);
           $bookingId = array_key_exists(1, $bookingIdString) ? $bookingIdString[1] : 0;
           $bookingDetails=$this->bookingsTable()->bookingsDetailsAdmin($bookingId);

            return new ViewModel(array('bookingDetails'=>$bookingDetails));
            
       }
       public function activeBookingAction()
       {

               $request = $this->getRequest()->getPost();

                $userListDetails=$this->bookingsTable()->getActivateBookingUserList();


                    if(count($userListDetails))
                    {
                          $this->activateBooking($userListDetails);
                    }
             return new JsonModel(array('date'=>"active ".date("y-m-d")));
           /*$bookingDetails=$this->bookingsTable()->*/
       }

       public function deactiveBookingAction()
       {
           $userListDetails=$this->bookingsTable()->getDeActivateBookingUserList();
           if(count($userListDetails))
           {
               $bookingIds=array();
                  foreach ($userListDetails as $booking)
                  {
                      $bookingIds[]=$booking['booking_id'];
                  }

               $this->bookingsTable()->updateMultipleBookings($bookingIds);
           }
           return new JsonModel(array('date'=>"deactive ".date("y-m-d")));
       }

       public function addCronJobAction()
       {

           $activeCronPath=$this->getBaseUrl().'/admin/admin/activeBooking';
           $deactiveCronPath=$this->getBaseUrl().'/admin/admin/deactiveBooking';
                  $cron=new CronJob();
                   $cronSchedule = '0 16 * * *';
                   $cron->addJob($cronSchedule . " /usr/bin/curl -o temp.txt " . $activeCronPath. ' > /var/log/cron.log 2>&1 ');
              $cronSchedule = '0 0 * * *';
                $cron->addJob($cronSchedule . " /usr/bin/curl -o temp.txt " . $deactiveCronPath. ' > /var/log/cron.log 2>&1 ');

           echo 'success';
                   exit;
       }
       public function userDownloadsAction()
        {
           $paramId = $this->params()->fromRoute('id', '');
           if (!$paramId)
           {
               return $this->redirect()->toUrl($this->getBaseUrl());
           }
           $userIdString = rtrim($paramId, "=");
           $userIdString = base64_decode($userIdString);
           $userIdString = explode("=", $userIdString);
           $userId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
                       
           $userDownloads=$this->bookingsTable()->getUserDownloadsAdmin($data=array('user_id'=>$userId,'limit'=>10,'offset'=>0));
           $userDownloadsCount=$this->bookingsTable()->getUserDownloadsAdminCount($data=array('user_id'=>$userId));
           $username=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name'));
            return new ViewModel(array('userDownloads'=>$userDownloads,'username'=>$username,'totalCount'=>$userDownloadsCount['booking_count']));
        }
        public function loadUserDownloadsAction()
    {
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
                $userIdString = $filterData['uid']['text'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $userId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
                $searchData['user_id'] = $userId;

                if(isset($filterData['booking_id']))
                {
                    if(isset($filterData['booking_id']['text']))
                    {
                        $searchData['booking_id']=$filterData['booking_id']['text'];
                    }
                    if(isset($filterData['booking_id']['order']) && $filterData['booking_id']['order'])
                    {
                        $searchData['booking_id_order']=$filterData['booking_id']['order'];
                    }
                }

                if(isset($filterData['created_at']))
                {
                    if(isset($filterData['created_at']['text']))
                    {
                        $searchData['created_at']=$filterData['created_at']['text'];
                    }
                    if(isset($filterData['created_at']['order']) && $filterData['created_at']['order'])
                    {
                        $searchData['created_at_order']=$filterData['created_at']['order'];
                    }
                }

                if(isset($filterData['bookings_count']))
                {
                    if(isset($filterData['bookings_count']['text']))
                    {
                        $searchData['bookings_count']=$filterData['bookings_count']['text'];
                    }
                    if(isset($filterData['bookings_count']['order']) && $filterData['bookings_count']['order'])
                    {
                        $searchData['bookings_count_order']=$filterData['bookings_count']['order'];
                    }
                }

                if(isset($filterData['tour_type']) && $filterData['tour_type']['text'])
                {
                    if(isset($filterData['tour_type']['text']))
                    {
                        $searchData['tour_type']=$filterData['tour_type']['text'];
                    }
                }
            }

            if(isset($request['page_number']))
            {
                $userIdString = $request['uid'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $userId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
                $searchData['user_id'] = $userId;
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }

            $totalCount=0;

            if($type && $type=='search')
            {
                $countResult=$this->bookingsTable()->getUserDownloadsAdminCount($searchData);

                if(count($countResult))
                {
                    $totalCount=$countResult['booking_count'];
                }
            }

            $userDownloads=$this->bookingsTable()->getUserDownloadsAdmin($searchData);
            $view = new ViewModel(array('userDownloads' => $userDownloads, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function usersListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }

        $usersList=$this->userTable()->getAllUsersAdmin();

        $usersListCount=$this->userTable()->getAllUsersAdminCount();


        return new ViewModel(array('usersList'=>$usersList,'totalCount'=>count($usersListCount)));
    }

    public function loadUsersListAction()
    {
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

                if(isset($filterData['user_name']))
                {
                    if(isset($filterData['user_name']['text']))
                    {
                        $searchData['user_name']=$filterData['user_name']['text'];
                    }
                    if(isset($filterData['user_name']['order']) && $filterData['user_name']['order'])
                    {
                        $searchData['user_name_order']=$filterData['user_name']['order'];
                    }
                }

                if(isset($filterData['mobile']))
                {
                    if(isset($filterData['mobile']['text']))
                    {
                        $searchData['mobile']=$filterData['mobile']['text'];
                    }
                    if(isset($filterData['mobile']['order']) && $filterData['mobile']['order'])
                    {
                        $searchData['mobile_order']=$filterData['mobile']['order'];
                    }
                }

                if(isset($filterData['subscription_type']))
                {
                    if(isset($filterData['subscription_type']['text']) && $filterData['subscription_type']['text'])
                    {
                        $searchData['subscription_type']=$filterData['subscription_type']['text'];
                    }
                }

                if(isset($filterData['bookings_count']))
                {
                    if(isset($filterData['bookings_count']['text']))
                    {
                        $searchData['bookings_count']=$filterData['bookings_count']['text'];
                    }
                    if(isset($filterData['bookings_count']['order']) && $filterData['bookings_count']['order'])
                    {
                        $searchData['bookings_count_order']=$filterData['bookings_count']['order'];
                    }
                }

                if(isset($filterData['booking_total_payment']))
                {
                    if(isset($filterData['booking_total_payment']['text']))
                    {
                        $searchData['booking_total_payment']=$filterData['booking_total_payment']['text'];
                    }
                    if(isset($filterData['booking_total_payment']['order']) && $filterData['booking_total_payment']['order'])
                    {
                        $searchData['booking_total_payment_order']=$filterData['booking_total_payment']['order'];
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
                $countResult=$this->userTable()->getAllUsersAdminCount($searchData);

                if(count($countResult))
                {
                    $totalCount=count($countResult);
                }
            }

            $usersList=$this->userTable()->getAllUsersAdmin($searchData);
            $view = new ViewModel(array('usersList' => $usersList, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function payPromoterAction(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $id = $request['id'];
            $currentDT = date("Y-m-d H:i:s");
            $pdata = $this->promoterPaymentsTable()->getFields(array("id"=>$id),array('due_date', 'due_amount', 'promoter_id', 'sponsor_id', 'status'));
            $pdata['id'] = $request['id'];
            $pdata['paid_amount'] = $request['amt'];
            $pdata['trans_ref'] = $request['tr'];
            $pdata['paid_date'] = $currentDT;
            $pdata['transaction_type'] = \Admin\Model\PromoterTransactions::transaction_paid;
            $response = $this->promoterPaymentsTable()->setSponsorPaymentDetails($pdata);
            //$response = true;
            if($response)
            {
                // create promoter transactions array
                /* $promBank = $this->promoterDetailsTable()->getFields(array('user_id'=>$pdata['promoter_id']), array('ifsc_code', 'bank_ac_no', 'due_amount', 'paid_amount', 'trigger_payment', 'latest_paid_date')); */
                $promBank = $this->promoterDetailsTable()->getFields(array('user_id'=>$pdata['promoter_id']), array('ifsc_code', 'bank_ac_no'));
                $spoPay = $this->referTable()->getFields(array('user_id'=>$pdata['sponsor_id']), array('due_amount', 'paid_amount', 'trigger_payment', 'latest_paid_date'));
                $transArr = array('promoter_id'=>$pdata['promoter_id'], 'transaction_type'=>\Admin\Model\PromoterTransactions::transaction_paid, 'sponsor_id'=>$pdata['sponsor_id'], 'account_no'=>$promBank['bank_ac_no'], 'ifsc_code'=>$promBank['ifsc_code'], 'transaction_date'=>$currentDT,'no_of_pwds'=>'0', 'amount'=>$pdata['paid_amount'], 'transaction_ref'=>$pdata['trans_ref']);
                // update promoter transactions table
                $addPrmTrans = $this->promoterTransactionsTable()->addPromoterTransaction($transArr);
                //$addPrmTrans = true;
                if($addPrmTrans){ 
                    // update promoter details table
                    /* $promBank = $this->promoterDetailsTable()->getFields(array('user_id'=>$pdata['promoter_id']), array( 'due_amount', 'paid_amount')); */
                    $trig = 0;
                    if($spoPay['due_amount'] - $pdata['paid_amount'] > 0) //if($promBank['due_amount'] - $pdata['paid_amount'] > 0)
                        $trig = 1;
                    /* $detres = $this->promoterDetailsTable()->updatePromoterDetails(array('due_amount'=> $promBank['due_amount'] - $pdata['paid_amount'], 'latest_due_date'=>$currentDT, 'trigger_payment'=>$trig, 'paid_amount'=>$promBank['paid_amount'] + $pdata['paid_amount'], 'latest_paid_date'=>$currentDT), array('user_id'=>$pdata['promoter_id'])); */
                    $detres = $this->referTable()->updateRefer(array('due_amount'=> $spoPay['due_amount'] - $pdata['paid_amount'], 'latest_due_date'=>$currentDT, 'trigger_payment'=>$trig, 'paid_amount'=>$spoPay['paid_amount'] + $pdata['paid_amount'], 'latest_paid_date'=>$currentDT), array('user_id'=>$pdata['sponsor_id']));
                    if($detres){
                        return new JsonModel(array('success'=>true,"message"=>'paid successfully'));
                    }else{
                        return new JsonModel(array('success'=>false,"message"=>'unable to process the payment'));
                    }
                }else{
                    return new JsonModel(array('success'=>false,"message"=>'unable to process the payment'));
                }
            }else{
                return new JsonModel(array('success'=>false,"message"=>'unable to process the payment'));
            }
        }
    } //unable to process the payment

    public function sponsorDisc50Action(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }

        if ($this->getRequest()->isXmlHttpRequest()){
            $request = $this->getRequest()->getPost();  //var_dump($request);exit;
            $sponsorid = $request['sid'];
            //echo $sponsorid;exit;
            $rresponse = $this->referTable()->updateRefer(array('disc50'=>\Admin\Model\Refer::discount_50), array('user_id'=>$sponsorid));
            if($rresponse){
                $response = $this->userTable()->updateUser(array('discount_percentage'=>50), array('user_id'=>$sponsorid));
                if($response){
                    return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'unable to update'));
                }
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update'));
            }
        }
    }

    public function sponsorTerminateAction(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }

        if ($this->getRequest()->isXmlHttpRequest()){
            $request = $this->getRequest()->getPost();  //var_dump($request);exit;
            $sponsorid = $request['sid'];
            //echo $sponsorid;exit;
            $response = $this->referTable()->updateRefer(array('sponsor_status'=>\Admin\Model\Refer::sponsor_terminated), array('user_id'=>$sponsorid));
            if($response){
                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update'));
            }
        }
    }

    public function promoterTerminateAction(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }

        if ($this->getRequest()->isXmlHttpRequest()){
            $request = $this->getRequest()->getPost();  //var_dump($request);exit;
            $promoterid = $request['pid'];
            $response = $this->userTable()->updateUser(array('is_promoter'=>\Admin\Model\User::Is_terminated_Promoter), array('user_id'=>$promoterid));
            if($response){
                // notify promoter that he is terminated

                $registrationIds = $this->fcmTable()->getDeviceIds($promoterid);
                $subject = "Promoter Termination";
                $message = "You are terminated as the promoter. However you can continue as sponsor and user of the App";
                $notificationDetails = array('notification_data_id'=>$promoterid,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $promoterid, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                $notification = $this->notificationTable()->saveData($notificationDetails);
                if ($registrationIds)
                {
                    $notification = new SendFcmNotification();
                    $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => $subject, 'id' => $notificationDetails['notification_data_id'],'type' => $notificationDetails['notification_type']));
                }

                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update'));
            }
        }
    }

    public function sponsorsPerformanceAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userIdString = rtrim($paramId, "=");
        $userIdString = base64_decode($userIdString);
        $userIdString = explode("=", $userIdString);
        $promoterId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;

        $sponsorsPerformance=$this->referTable()->getSponsorsPerfAdmin(array('promoterId'=>$promoterId,'limit'=>10,'offset'=>0));
        $sponsorsCount=$this->referTable()->getSponsorsPerfAdminCount(array('promoterId'=>$promoterId));
        $promoterName=$this->userTable()->getFields(array('user_id'=>$promoterId),array('user_name'));
        return new ViewModel(array('sponsorsPerformance'=>$sponsorsPerformance,'totalCount'=>$sponsorsCount[0]['sponsors_count'], 'promoterName'=>$promoterName));
    }

    public function loadSponsorsPerformanceAction(){
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();  //var_dump($request);exit;          
            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                $userIdString = $filterData['uid']['text'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $promoterId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0; 
                //echo $promoterId; exit;
                $searchData['user_id'] = $promoterId;

                if(isset($filterData['user_name']))
                { 
                    if(isset($filterData['user_name']['text']))
                    { 
                        $searchData['user_name']=$filterData['user_name']['text'];
                    }
                    if(isset($filterData['user_name']['order']) && $filterData['user_name']['order'])
                    {
                        $searchData['user_name_order']=$filterData['user_name']['order'];
                    }
                }
                if(isset($filterData['mobile']))
                {
                    if(isset($filterData['mobile']['text']))
                    {
                        $searchData['mobile']=$filterData['mobile']['text'];
                    }
                    if(isset($filterData['mobile']['order']) && $filterData['mobile']['order'])
                    {
                        $searchData['mobile_order']=$filterData['mobile']['order'];
                    }
                }
                if(isset($filterData['subscription_end_date']))
                {
                    if(isset($filterData['subscription_end_date']['text']))
                    {
                        $searchData['subscription_end_date']=$filterData['subscription_end_date']['text'];
                    }
                    if(isset($filterData['subscription_end_date']['order']) && $filterData['subscription_end_date']['order'])
                    {
                        $searchData['subscription_end_date_order']=$filterData['subscription_end_date']['order'];
                    }
                }
                if(isset($filterData['pwds_purchased']))
                {
                    if(isset($filterData['pwds_purchased']['text']))
                    {
                        $searchData['pwds_purchased']=$filterData['pwds_purchased']['text'];
                    }
                    if(isset($filterData['pwds_purchased']['order']) && $filterData['pwds_purchased']['order'])
                    {
                        $searchData['pwds_purchased_order']=$filterData['pwds_purchased']['order'];
                    }
                }                
                if(isset($filterData['amount_paid']))
                {
                    if(isset($filterData['amount_paid']['text']))
                    {
                        $searchData['amount_paid']=$filterData['amount_paid']['text'];
                    }
                    if(isset($filterData['amount_paid']['order']) && $filterData['amount_paid']['order'])
                    {
                        $searchData['amount_paid_order']=$filterData['amount_paid']['order'];
                    }
                }
            }

            if(isset($request['page_number']))
            {
                $userIdString = $request['uid'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $promoterId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0; 
                //echo $promoterId; exit;

                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }
            $searchData['promoterId']=$promoterId;
            $totalCount=0;
            
            if($type && $type=='search')
            {
                $totalCount=$this->referTable()->getSponsorsPerfAdminCount($searchData);
            }

            $sponsorsPerformance=$this->referTable()->getSponsorsPerfAdmin($searchData);
            $promoterName=$this->userTable()->getFields(array('user_id'=>$promoterId),array('user_name'));
            $view = new ViewModel(array('sponsorsPerformance'=>$sponsorsPerformance, 'promoterName'=>$promoterName, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount[0]['sponsors_count']));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function sponsorsTransactionsAction(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userIdString = rtrim($paramId, "=");
        $userIdString = base64_decode($userIdString);
        $userIdString = explode("=", $userIdString);
        $sponsorId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
        //echo $sponsorId;exit;
        $data = array('sponsorId'=>$sponsorId, 'limit'=>10,'offset'=>0);
        $sponsorTransactions = $this->promoterTransactionsTable()->getSponsorwiseTransactions($data);
        //var_dump($sponsorTransactions); exit;
        $sponsorAllTransactions = $this->promoterTransactionsTable()->getSponsorwiseAllTransactions($data);
        $sponsorName=$this->userTable()->getFields(array('user_id'=>$sponsorId),array('user_name'));
        return new ViewModel(array('sponsorTransactions'=>$sponsorTransactions,'totalCount'=>count($sponsorAllTransactions), 'sponsorName'=>$sponsorName));
    }

    public function loadSponsorsTransactionsAction(){
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();  //var_dump($request);exit;          
            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                $userIdString = $filterData['uid']['text'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $sponsorId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0; 
                //echo $sponsorId; exit;
                $searchData['user_id'] = $sponsorId;

                if(isset($filterData['promoter_name']))
                { 
                    if(isset($filterData['promoter_name']['text']))
                    { 
                        $searchData['promoter_name']=$filterData['promoter_name']['text'];
                    }
                    if(isset($filterData['promoter_name']['order']) && $filterData['promoter_name']['order'])
                    {
                        $searchData['promoter_name_order']=$filterData['promoter_name']['order'];
                    }
                }
                if(isset($filterData['promoter_mobile']))
                {
                    if(isset($filterData['promoter_mobile']['text']))
                    {
                        $searchData['promoter_mobile']=$filterData['promoter_mobile']['text'];
                    }
                    if(isset($filterData['promoter_mobile']['order']) && $filterData['promoter_mobile']['order'])
                    {
                        $searchData['promoter_mobile_order']=$filterData['promoter_mobile']['order'];
                    }
                }
            }

            if(isset($request['page_number']))
            {
                $userIdString = $request['uid'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $sponsorId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0; 
                //echo $sponsorId; exit;

                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }
            $searchData['sponsorId']=$sponsorId;
            $totalCount=0;
            
            if($type && $type=='search')
            {
                //$totalCount=$this->promoterTransactionsTable()->getSponsorwiseAllTransactions($searchData);
                $sponsorAllTransactions = $this->promoterTransactionsTable()->getSponsorwiseAllTransactions($searchData);
            }

            $sponsorTransactions = $this->promoterTransactionsTable()->getSponsorwiseTransactions($searchData);
            //$sponsorAllTransactions = $this->promoterTransactionsTable()->getSponsorwiseAllTransactions($searchData);
            $sponsorName=$this->userTable()->getFields(array('user_id'=>$sponsorId),array('user_name'));
            $view =  new ViewModel(array('sponsorTransactions'=>$sponsorTransactions,'offset' => $offset,'type'=>$type,'totalCount'=>count($sponsorAllTransactions), 'sponsorName'=>$sponsorName, 'offset' => $offset));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function promotersPaymentsAction(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $promotersPayments = $this->promoterPaymentsTable()->getPromotersPaymentsDetails();
        $promotersAllPayments = $this->promoterPaymentsTable()->getPromotersPaymentsDetailsCount();
        return new ViewModel(array('promotersPayments'=>$promotersPayments,'totalCount'=>count($promotersAllPayments)));
    }

    public function loadPromotersPaymentsAction(){
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();  //var_dump($request);exit;          
            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                $userIdString = $filterData['uid']['text'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $sponsorId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0; 
                //echo $sponsorId; exit;
                $searchData['user_id'] = $sponsorId;

                if(isset($filterData['sponsor_mobile']))
                { 
                    if(isset($filterData['sponsor_mobile']['text']))
                    { 
                        $searchData['sponsor_mobile']=$filterData['sponsor_mobile']['text'];
                    }
                    if(isset($filterData['sponsor_mobile']['order']) && $filterData['sponsor_mobile']['order'])
                    {
                        $searchData['sponsor_mobile_order']=$filterData['sponsor_mobile']['order'];
                    }
                }
                if(isset($filterData['promoter_mobile']))
                {
                    if(isset($filterData['promoter_mobile']['text']))
                    {
                        $searchData['promoter_mobile']=$filterData['promoter_mobile']['text'];
                    }
                    if(isset($filterData['promoter_mobile']['order']) && $filterData['promoter_mobile']['order'])
                    {
                        $searchData['promoter_mobile_order']=$filterData['promoter_mobile']['order'];
                    }
                }
            }

            if(isset($request['page_number']))
            {
                $userIdString = $request['uid'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $sponsorId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0; 
                //echo $sponsorId; exit;

                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }
            $searchData['sponsorId']=$sponsorId;
            $totalCount=0;
            
            if($type && $type=='search')
            {
                $totalCount=$this->referTable()->getSponsorsPerfAdminCount($searchData);
            }

            $promotersPayments = $this->promoterPaymentsTable()->getPromotersPaymentsDetails($searchData);
            $promotersAllPayments = $this->promoterPaymentsTable()->getPromotersPaymentsDetailsCount($searchData);
            $view = new ViewModel(array('promotersPayments'=>$promotersPayments,'offset' => $offset,'type'=>$type,'totalCount'=>count($promotersAllPayments)));
            $view->setTerminal(true);
            return $view;

            /* $sponsorTransactions = $this->promoterTransactionsTable()->getSponsorwiseTransactions($searchData);
            $sponsorAllTransactions = $this->promoterTransactionsTable()->getSponsorwiseAllTransactions($searchData);
            $sponsorName=$this->userTable()->getFields(array('user_id'=>$sponsorId),array('user_name'));
            $view = new ViewModel(array('sponsorTransactions'=>$sponsorTransactions,'totalCount'=>count($sponsorAllTransactions), 'sponsorName'=>$sponsorName));
            $view->setTerminal(true);
            return $view; */
        }
    }

    public function taPlansAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $taPlans=$this->taPlansTable()->getTaPlansAdmin();
        $taAllPlansCount=$this->taPlansTable()->getTaPlansAdmin(null, 1);
        return new ViewModel(array('taPlans'=>$taPlans,'totalCount'=>$taAllPlansCount));
    }

    public function loadAllTaPlansAction()
    {
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

                if(isset($filterData['plan_name']))
                { 
                    if(isset($filterData['plan_name']['text']))
                    { 
                        $searchData['plan_name']=$filterData['plan_name']['text'];
                    }
                    if(isset($filterData['plan_name']['order']) && $filterData['plan_name']['order'])
                    {
                        $searchData['plan_name_order']=$filterData['plan_name']['order'];
                    }
                }
                if(isset($filterData['mtc']))
                {
                    if(isset($filterData['mtc']['text']))
                    {
                        $searchData['mtc']=$filterData['mtc']['text'];
                    }
                    if(isset($filterData['mtc']['order']) && $filterData['mtc']['order'])
                    {
                        $searchData['mtc_order']=$filterData['mtc']['order'];
                    }
                }
                if(isset($filterData['duration']))
                {
                    if(isset($filterData['duration']['text']))
                    {
                        $searchData['duration']=$filterData['duration']['text'];
                    }
                    if(isset($filterData['duration']['order']) && $filterData['duration']['order'])
                    {
                        $searchData['duration_order']=$filterData['duration']['order'];
                    }
                }
                if(isset($filterData['cost']))
                {
                    if(isset($filterData['cost']['text']))
                    {
                        $searchData['cost']=$filterData['cost']['text'];
                    }
                    if(isset($filterData['cost']['order']) && $filterData['cost']['order'])
                    {
                        $searchData['cost_order']=$filterData['cost']['order'];
                    }
                }
                if(isset($filterData['active']))
                {
                    if(isset($filterData['active']['text']))
                    {
                        $searchData['active']=$filterData['active']['text'];
                    }
                    if(isset($filterData['active']['order']) && $filterData['active']['order'])
                    {
                        $searchData['active_order']=$filterData['active']['order'];
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
                $totalCount=$this->taPlansTable()->getTaPlansAdmin($searchData, 1);
            }
            
            $taPlans=$this->taPlansTable()->getTaPlansAdmin($searchData);
            $view = new ViewModel(array('taPlans' => $taPlans, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function addTaPlanAction(){
        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
           
            $data=array(
                'plan_name'=> $request['plan_name'],
                'mtc'=>$request['mtc'],
                'duration'=>$request['duration'],
                'cost'=>$request['cost'],
                'active'=>$request['active'],
                'status'=> '1');

            $response=$this->taPlansTable()->addTaPlan($data);
            if($response){
                $taPlanId=$response['id'];
                return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to add ta plan'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $taPlans=$this->taPlansTable()->getTaPlansAdmin();
        $taAllPlansCount=$this->taPlansTable()->getTaPlansAdmin(null, 1);
        return new ViewModel(array('taPlans'=>$taPlans,'totalCount'=>$taAllPlansCount));
    }

    public function editTaPlanAction(){
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upIdString = rtrim($paramId, "=");
        $upIdString = base64_decode($upIdString);
        $upIdString = explode("=", $upIdString);
        $planId = array_key_exists(1, $upIdString) ? $upIdString[1] : 0;

        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
           
            $data=array(
                'plan_name'=> $request['plan_name'],
                'mtc'=>$request['mtc'],
                'duration'=>$request['duration'],
                'cost'=>$request['cost'],
                'active'=>$request['active']);

            $response=$this->taPlansTable()->setTaPlan($data, array('id'=>$planId));
            if($response){
                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update ta plan'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $taPlanDetails=$this->taPlansTable()->getTaPlanDetails($planId);
        return new ViewModel(array('taPlanDetails'=>$taPlanDetails));
    }

    public function deleteTaPlanAction()
    {        
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $planId=$request['plan_id'];
            $data=array('status'=>0);
            $response=$this->taPlansTable()->setTaPlan($data,array('id'=>$planId));
            if($response)
            {
                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to delete'));
            }
        }
    }

    public function taPurchasesAction(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $taPurchases=$this->taPurchasesTable()->getTaPurchasesAdmin();
        $totalCount=$this->taPurchasesTable()->getTaPurchasesAdmin(null, 1);
        return new ViewModel(array('taPurchases'=>$taPurchases,'totalCount'=>$totalCount));
    }

    public function loadTaPurchasesAction(){
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

                if(isset($filterData['ta_name']))
                {
                    if(isset($filterData['ta_name']['text']))
                    {
                        $searchData['ta_name']=$filterData['ta_name']['text'];
                    }
                    if(isset($filterData['ta_name']['order']) && $filterData['ta_name']['order'])
                    {
                        $searchData['ta_name_order']=$filterData['ta_name']['order'];
                    }
                }
                if(isset($filterData['dop']))
                { 
                    if(isset($filterData['dop']['text']))
                    { 
                        $searchData['dop']=$filterData['dop']['text'];
                    }
                    if(isset($filterData['dop']['order']) && $filterData['dop']['order'])
                    {
                        $searchData['dop_order']=$filterData['dop']['order'];
                    }
                }
                if(isset($filterData['doe']))
                {
                    if(isset($filterData['doe']['text']))
                    {
                        $searchData['doe']=$filterData['doe']['text'];
                    }
                    if(isset($filterData['doe']['order']) && $filterData['doe']['order'])
                    {
                        $searchData['doe_order']=$filterData['doe']['order'];
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
                if(isset($filterData['name']))
                {
                    if(isset($filterData['name']['text']))
                    {
                        $searchData['name']=$filterData['name']['text'];
                    }
                    if(isset($filterData['name']['order']) && $filterData['name']['order'])
                    {
                        $searchData['name_order']=$filterData['name']['order'];
                    }
                }
                if(isset($filterData['ta_plan_cost']))
                {
                    if(isset($filterData['ta_plan_cost']['text']))
                    {
                        $searchData['ta_plan_cost']=$filterData['ta_plan_cost']['text'];
                    }
                    if(isset($filterData['ta_plan_cost']['order']) && $filterData['ta_plan_cost']['order'])
                    {
                        $searchData['ta_plan_cost_order']=$filterData['ta_plan_cost']['order'];
                    }
                }
                if(isset($filterData['tourists_count']))
                {
                    if(isset($filterData['tourists_count']['text']))
                    {
                        $searchData['tourists_count']=$filterData['tourists_count']['text'];
                    }
                    if(isset($filterData['tourists_count']['order']) && $filterData['tourists_count']['order'])
                    {
                        $searchData['tourists_count_order']=$filterData['tourists_count']['order'];
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
                $totalCount=$this->taPurchasesTable()->getTaPurchasesAdmin($searchData, 1);
            }
            
            $taPurchases=$this->taPurchasesTable()->getTaPurchasesAdmin($searchData);
            $view = new ViewModel(array('taPurchases' => $taPurchases, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function buyPlanAction(){
        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $idsArr = explode(",",$request['ids']);
            $taid = trim($idsArr[0]);
            $taConsId = trim($idsArr[1]);
            $planCost = $request['ta_plan_cost'];
            $planDetails = $this->taPlansTable()->getTaPlanDetails($request['ta_plan_id']);
            $dop = date("Y-m-d H:i:s");
            $noOfDays=$planDetails["validity"];
            $doe = date('Y-m-d', strtotime(date("y-m-d") . " +  $noOfDays days"));
            $upc = $request['tac'] ."_". $request['mtc'] ."_". date("Y-m-d");

            $upcExists = $this->taPurchasesTable()->getField(array('upc'=>$upc), 'id');
            if($upcExists){
                return new JsonModel(array('success'=>false,'message'=>'Plan already purchased today. Try again tommorrow'));
            }

            //$taConsId = $request['ta_cons_id'];
            $pic_counter = 0;

            if(!$taConsId){
                $taConsLatestDueAmtUpdated = 0;
            }else{
                $taConsDetails = $this->taConsultantDetailsTable()->getTaConsultantDetails($taConsId);
                $validConsMobile = $this->taDetailsTable()->getField(array('id'=>$taid), 'cons_mobile');
                if($validConsMobile != $taConsDetails['mobile']){
                    return new JsonModel(array('success'=>false,'message'=>'SE not valid for selected TA'));
                }
                $taConsLatestDueAmt = $this->taDetailsTable()->getField(array('id'=>$taid), 'ta_cons_due_amt');
                $taConsDueAmt =  ((int)$planCost * (int)$taConsDetails['commission']) / 100;
                $pic_count = $this->taConsultantTransactionsTable()->getPicCount(array("ta_id"=>$taid, "ta_cons_id"=>$taConsId));
                if($pic_count >= 0){
                    if($pic_count < $taConsDetails['pic'])
                        $pic_counter = 1;
                }
                if($pic_counter)
                    $taConsLatestDueAmtUpdated = (int)$taConsLatestDueAmt + $taConsDueAmt;
                else
                    $taConsLatestDueAmtUpdated = $taConsDueAmt;
            }
            $taUpdateVals = array(
                'ta_cons_due_amt'=>$taConsLatestDueAmtUpdated,
                'ta_cons_latest_due_dt'=>$dop,
                'trigger_payment'=>1);
            $purchaseData=array(
                'ta_id'=> $taid,
                'ta_plan_id'=>$request['ta_plan_id'],
                'dop'=>$dop,
                'doe'=>$doe,
                'upc'=>$upc,  //$request['tac'] ."_". $request['mtc'] ."_". date("Y-m-d"),
                'ta_cons_id'=>$taConsId,
                'ta_plan_cost'=>$planCost,
                'tourists_count'=>$request['mtc'],
                'status'=> '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"));

            $response = $this->taPurchasesTable()->addTaPurchase($purchaseData);
            if($response['success'] && $taConsId){
                // Add Data to ta_cons_transactions_table
                $purchaseId = $response['id'];
                
                if($pic_counter){
                    $txData = array(
                        'ta_purchase_id'=>$purchaseId,
                        'ta_id'=> $taid,
                        'ta_cons_id'=>$taConsId,
                        'upc'=>$upc,
                        'transaction_type'=>\Admin\Model\TaConsultantTransactions::transaction_due,
                        'transaction_date'=>$dop,
                        'pic_counter'=>$pic_counter,
                        'amount'=>$taConsDueAmt,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"));
                    $txnresp =  $this->taConsultantTransactionsTable()->addTaConsultantTransaction($txData);
                    if($txnresp['success']){
                        $cpData = array(
                            'due_date'=> $dop,
                            'due_amt'=>$taConsDueAmt,
                            'ta_id'=>$taid,
                            'ta_cons_id'=>$taConsId,
                            'status'=> '0',
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s"));
                        $this->taConsPaymentsTable()->setTaPaymentDetails($cpData);
                        $this->taDetailsTable()->setTaDetails($taUpdateVals, array('id'=>$taid));
                    }
                }
                
                return new JsonModel(array('success'=>true , 'message'=>'purchase details added successfully'));
            }else{
                if(!$taConsId){
                    if($response['success'])
                        return new JsonModel(array('success'=>true,'message'=>'purchase details added successfully'));
                    else
                        return new JsonModel(array('success'=>false,'message'=>$response['id'])); //'unable to add purchase details'));
                }else
                    return new JsonModel(array('success'=>false,'message'=>'unable to add purchase details'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }

        $tpList=$this->taPlansTable()->getTaPlansAdmin();
        $seList=$this->taConsultantDetailsTable()->getTaConsAdmin();
        $taList=$this->taDetailsTable()->getTaListAdmin();
        return new ViewModel(array('tpList'=>$tpList,'seList'=>$seList, 'taList'=>$taList));
    }

    public function sePaymentsAction(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $sePayments = $this->taConsPaymentsTable()->getTaConsPaymentsAdmin();
        $totalCount = $this->taConsPaymentsTable()->getTaConsPaymentsAdmin(null, 1);
        return new ViewModel(array('sePayments'=>$sePayments,'totalCount'=>$totalCount));
    }

    public function loadSePaymentsAction(){
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

                if(isset($filterData['tac']))
                { 
                    if(isset($filterData['tac']['text']))
                    { 
                        $searchData['tac']=$filterData['tac']['text'];
                    }
                    if(isset($filterData['tac']['order']) && $filterData['tac']['order'])
                    {
                        $searchData['tac_order']=$filterData['tac']['order'];
                    }
                }

                if(isset($filterData['ta_mobile']))
                { 
                    if(isset($filterData['ta_mobile']['text']))
                    { 
                        $searchData['ta_mobile']=$filterData['ta_mobile']['text'];
                    }
                    if(isset($filterData['ta_mobile']['order']) && $filterData['ta_mobile']['order'])
                    {
                        $searchData['ta_mobile_order']=$filterData['ta_mobile']['order'];
                    }
                }
                if(isset($filterData['name']))
                {
                    if(isset($filterData['name']['text']))
                    {
                        $searchData['name']=$filterData['name']['text'];
                    }
                    if(isset($filterData['name']['order']) && $filterData['name']['order'])
                    {
                        $searchData['name_order']=$filterData['name']['order'];
                    }
                }
                if(isset($filterData['mobile']))
                {
                    if(isset($filterData['mobile']['text']))
                    {
                        $searchData['mobile']=$filterData['mobile']['text'];
                    }
                    if(isset($filterData['mobile']['order']) && $filterData['mobile']['order'])
                    {
                        $searchData['mobile_order']=$filterData['mobile']['order'];
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
                $totalCount=$this->taConsPaymentsTable()->getTaConsPaymentsAdmin($searchData, 1);
            }

            $sePayments=$this->taConsPaymentsTable()->getTaConsPaymentsAdmin($searchData);
            $view = new ViewModel(array('sePayments' => $sePayments, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
            
        }
    }

    public function paySeAction(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $id = $request['id'];
            $currentDT = date("Y-m-d H:i:s");
            $pdata = $this->taConsPaymentsTable()->getFields(array("id"=>$id),array('id','due_date', 'due_amt', 'ta_id', 'ta_cons_id', 'status'));
            //$pdata['id'] = $request['id'];
            $pdata['paid_amt'] = $request['amt'];
            $pdata['trans_ref'] = $request['tr'];
            $pdata['paid_date'] = $currentDT;
            $pdata['transaction_type'] = \Admin\Model\PromoterTransactions::transaction_paid;

            $response = $this->taConsPaymentsTable()->setTaPaymentDetails($pdata); 

            if($response)
            {
                // create sttse transactions array
                $seBank = $this->taConsultantDetailsTable()->getFields(array('id'=>$pdata['ta_cons_id']), array('ifsc_code', 'bank_ac_no'));
                $taPay = $this->taDetailsTable()->getFields(array('id'=>$pdata['ta_id']), array('ta_cons_due_amt', 'ta_cons_paid_amt', 'trigger_payment', 'ta_cons_latest_paid_dt'));
                $transArr = array('ta_cons_id'=>$pdata['ta_cons_id'], 'transaction_type'=>\Admin\Model\PromoterTransactions::transaction_paid, 'ta_id'=>$pdata['ta_id'], 'bank_ac_no'=>$seBank['bank_ac_no'], 'ifsc_code'=>$seBank['ifsc_code'], 'transaction_date'=>$currentDT, 'amount'=>$pdata['paid_amt'], 'transaction_ref'=>$pdata['trans_ref']);
                // update se transactions table
                $addConsTrans = $this->taConsultantTransactionsTable()->addTaConsultantTransaction($transArr);
                if($addConsTrans['success']){ 
                    $trig = 1;
                    $detres = $this->taDetailsTable()->setTaDetails(array('ta_cons_due_amt'=> $taPay['ta_cons_due_amt'] - $pdata['paid_amt'], 'ta_cons_latest_due_dt'=>$currentDT, 'ta_cons_paid_amt'=>$taPay['ta_cons_paid_amt'] + $pdata['paid_amt'], 'ta_cons_latest_paid_dt'=>$currentDT), array('id'=>$pdata['ta_id']));
                    if($detres){
                        return new JsonModel(array('success'=>true,"message"=>'paid successfully'));
                    }else{
                        return new JsonModel(array('success'=>false,"message"=>'unable to process the payment'));
                    }
                }else{
                    return new JsonModel(array('success'=>false,"message"=>'unable to process the payment'));
                }
            }else{
                return new JsonModel(array('success'=>false,"message"=>'unable to process the payment'));
            }
        }
    }

    public function seTasAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userIdString = rtrim($paramId, "=");
        $userIdString = base64_decode($userIdString);
        $userIdString = explode("=", $userIdString);
        $seId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;

        $cons_mobile = $this->taConsultantDetailsTable()->getField(array('id'=>$seId), 'mobile');
        $setas=$this->taDetailsTable()->getSETAsAdmin(array('cons_mobile'=>$cons_mobile,'limit'=>10,'offset'=>0));
        $totalCount=$this->taDetailsTable()->getSETAsAdmin(array('cons_mobile'=>$cons_mobile), 1);
        $seName=$this->taConsultantDetailsTable()->getField(array('id'=>$seId), 'name');
        return new ViewModel(array('setas'=>$setas,'totalCount'=>$totalCount, 'seName'=>$seName));
    }

    public function loadSeTasAction(){
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();  //var_dump($request);exit;          
            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                $userIdString = $filterData['uid']['text'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $seId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0; 
                //echo $promoterId; exit;
                $searchData['cons_mobile'] = $this->taConsultantDetailsTable()->getField(array('id'=>$seId), 'mobile');

                if(isset($filterData['ta_name']))
                { 
                    if(isset($filterData['ta_name']['text']))
                    { 
                        $searchData['ta_name']=$filterData['ta_name']['text'];
                    }
                    if(isset($filterData['ta_name']['order']) && $filterData['ta_name']['order'])
                    {
                        $searchData['ta_name_order']=$filterData['ta_name']['order'];
                    }
                }
                if(isset($filterData['ta_mobile']))
                {
                    if(isset($filterData['ta_mobile']['text']))
                    {
                        $searchData['ta_mobile']=$filterData['ta_mobile']['text'];
                    }
                    if(isset($filterData['ta_mobile']['order']) && $filterData['ta_mobile']['order'])
                    {
                        $searchData['ta_mobile_order']=$filterData['ta_mobile']['order'];
                    }
                }
            }

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
            $searchData['seId']=$seId;
            $totalCount=0;
            
            if($type && $type=='search')
            {
                $totalCount=$this->taDetailsTable()->getSETAsAdmin($searchData, 1);
            }

            $setas=$this->taDetailsTable()->getSETAsAdmin(array('cons_mobile'=>$cons_mobile,'limit'=>10,'offset'=>0));
            $seName=$this->taConsultantDetailsTable()->getField(array('id'=>$seId), 'name');
            $view = new ViewModel(array('setas'=>$setas,'totalCount'=>$totalCount, 'seName'=>$seName, 'offset' => $offset,'type'=>$type));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function setaTransactionsAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userIdString = rtrim($paramId, "=");
        $userIdString = base64_decode($userIdString);
        $userIdString = explode("=", $userIdString);
        $ta_id = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
        if($ta_id){
            $setaTxns=$this->TaConsultantTransactionsTable()->getTaConsTxAdmin(array('ta_id'=>$ta_id,'limit'=>10,'offset'=>0));
            $totalCount=$this->TaConsultantTransactionsTable()->getTaConsTxAdmin(array('ta_id'=>$ta_id), 1);
        }else{
            return new ViewModel(array('setaTxns'=>array(),'totalCount'=>0, 'taName'=>''));
        }
        $taName=$this->taDetailsTable()->getField(array('id'=>$ta_id), 'ta_name');
        return new ViewModel(array('setaTxns'=>$setaTxns,'totalCount'=>$totalCount, 'taName'=>$taName));
    }

    public function loadSetaTransactionsAction(){
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();  //var_dump($request);exit;          
            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                $userIdString = $filterData['uid']['text'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $ta_id = array_key_exists(1, $userIdString) ? $userIdString[1] : 0; 
            }

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
            $searchData['ta_id']=$ta_id;
            $totalCount=0;
            
            if($type && $type=='search')
            {
                $totalCount=$this->TaConsultantTransactionsTable()->getTaConsTxAdmin($searchData, 1);
            }

            $setaTxns=$this->TaConsultantTransactionsTable()->getTaConsTxAdmin(array('ta_id'=>$ta_id,'limit'=>10,'offset'=>0));
            $taName=$this->taDetailsTable()->getField(array('id'=>$ta_id), 'ta_name');
            $view = new ViewModel(array('setas'=>$setas,'totalCount'=>$totalCount, 'ta_name'=>$ta_name, 'offset' => $offset,'type'=>$type));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function taListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $taList=$this->taDetailsTable()->getTaListAdmin();
        $taListCount=($this->taDetailsTable()->getTaListAdmin(null, 1))==array()?0 : ($this->taDetailsTable()->getTaListAdmin(null, 1));
        return new ViewModel(array('taList'=>$taList,'totalCount'=>$taListCount));
    }

    public function loadTaListAction()
    {
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

                if(isset($filterData['ta_name']))
                { 
                    if(isset($filterData['ta_name']['text']))
                    { 
                        $searchData['ta_name']=$filterData['ta_name']['text'];
                    }
                    if(isset($filterData['ta_name']['order']) && $filterData['ta_name']['order'])
                    {
                        $searchData['ta_name_order']=$filterData['ta_name']['order'];
                    }
                }
                if(isset($filterData['ta_mobile']))
                {
                    if(isset($filterData['ta_mobile']['text']))
                    {
                        $searchData['ta_mobile']=$filterData['ta_mobile']['text'];
                    }
                    if(isset($filterData['ta_mobile']['order']) && $filterData['ta_mobile']['order'])
                    {
                        $searchData['ta_mobile_order']=$filterData['ta_mobile']['order'];
                    }
                }
                if(isset($filterData['ta_email']))
                {
                    if(isset($filterData['ta_email']['text']))
                    {
                        $searchData['ta_email']=$filterData['ta_email']['text'];
                    }
                    if(isset($filterData['ta_email']['order']) && $filterData['ta_email']['order'])
                    {
                        $searchData['ta_email_order']=$filterData['ta_email']['order'];
                    }
                }
                if(isset($filterData['ae_name']))
                { 
                    if(isset($filterData['ae_name']['text']))
                    { 
                        $searchData['ae_name']=$filterData['ae_name']['text'];
                    }
                    if(isset($filterData['ae_name']['order']) && $filterData['ae_name']['order'])
                    {
                        $searchData['ae_name_order']=$filterData['ae_name']['order'];
                    }
                }
                if(isset($filterData['ae_mobile']))
                {
                    if(isset($filterData['ae_mobile']['text']))
                    {
                        $searchData['ae_mobile']=$filterData['ae_mobile']['text'];
                    }
                    if(isset($filterData['ae_mobile']['order']) && $filterData['ae_mobile']['order'])
                    {
                        $searchData['ae_mobile_order']=$filterData['ae_mobile']['order'];
                    }
                }
                if(isset($filterData['ae_email']))
                {
                    if(isset($filterData['ae_email']['text']))
                    {
                        $searchData['ae_email']=$filterData['ae_email']['text'];
                    }
                    if(isset($filterData['ae_email']['order']) && $filterData['ae_email']['order'])
                    {
                        $searchData['ae_email_order']=$filterData['ae_email']['order'];
                    }
                }
                if(isset($filterData['cons_mobile']))
                {
                    if(isset($filterData['cons_mobile']['text']))
                    {
                        $searchData['cons_mobile']=$filterData['cons_mobile']['text'];
                    }
                    if(isset($filterData['cons_mobile']['order']) && $filterData['cons_mobile']['order'])
                    {
                        $searchData['cons_mobile_order']=$filterData['cons_mobile']['order'];
                    }
                }
                if(isset($filterData['tac']))
                {
                    if(isset($filterData['tac']['text']))
                    {
                        $searchData['tac']=$filterData['tac']['text'];
                    }
                    if(isset($filterData['tac']['order']) && $filterData['tac']['order'])
                    {
                        $searchData['tac_order']=$filterData['tac']['order'];
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
                $totalCount=$this->taDetailsTable()->getTaListAdmin($searchData, 1);
            }
            
            $taList=$this->taDetailsTable()->getTaListAdmin($searchData);
            $view = new ViewModel(array('taList' => $taList, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
        
    public function addTaAction(){
        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $tac=$request['tac'];
            $fileDetails=$request['file_details'];

            $fileDetails=json_decode($fileDetails,true);
            $fileIds=explode(",",$request['file_Ids']);
            $uploadFileDetails=array();

            $tac=trim($tac);
            if($tac=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please enter TAC"));
            }

            $checkTAC=$this->taDetailsTable()->getField(array('tac'=>$tac,'status'=>1),'id');
            if($checkTAC)  // check for TA Code
            {
                return new JsonModel(array("success" => false, "message" => "TAC Already exists"));
            }

            $chkTaCons = $this->taConsultantDetailsTable()->getField(array("mobile"=>$request['mobile'], "status"=>'1'), "id");
            if($chkTaCons){
                return new JsonModel(array('success'=>false,'message'=>'TA cannot be STTSE'));   
            }

            $chkAE = $this->taConsultantDetailsTable()->getField(array("mobile"=>$request['ae_mobile'], "status"=>'1'), "id");
            if($chkAE){
                return new JsonModel(array('success'=>false,'message'=>'AE cannot be STTSE'));
            }

            //$flagImagePath='';
            $getFiles=$this->temporaryFiles()->getFiles($fileIds);

            $uploadFiles=array();
            foreach ($getFiles['images'] as $images)
            {
                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_ta_logo,
                    'file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $images['file_extension'],
                    'status'=>1,
                    'duration'=>0 ,
                    'file_language_type' => 0,
                    'hash'=>'',
                    'file_name' => $images['file_name']
                );
                if($images['status']==\Admin\Model\TemporaryFiles::status_file_not_copied)
                {
                    $uploadFiles[]=array('old_path'=>$images['file_path'],'new_path'=>$images['file_path'],'id'=>$images['temporary_files_id']);
                }
            }

            $copyFiles= $this->copypushFiles($uploadFiles);

            if(count($copyFiles['copied']))
            {
                $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
            }
            if(!$copyFiles['status'])
            {
                return new JsonModel(array('success'=>false,'message'=>'unable to add ta'));
            }
           
            $data=array(
                'ta_name'=> $request['ta_name'],
                'ta_mobile'=> $request['ta_mobile'],
                'ta_email'=> $request['ta_email'],
                'ae_name'=> $request['ae_name'],
                'ae_mobile'=> $request['ae_mobile'],
                'ae_email'=> $request['ae_email'],
                'cons_mobile'=>$request['cons_mobile'],
                'tac'=>$request['tac'],
                'status'=> '1');

            $response=$this->taDetailsTable()->addTa($data);
            if($response){
                // add logo file details to tourism files table
                $taId = $this->taDetailsTable()->getField(array('tac'=>$tac,'status'=>1),'id');        
                $counter = -1;
                if(count($uploadFileDetails)) {
                    foreach ($uploadFileDetails as $details) {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $taId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
                }
                
                // adding TA as TBE
                $aes = new Aes();
                $password = $request['ae_mobile'];
                $encodeContent = $aes->encrypt($password);
                $encryptPassword = $encodeContent['password'];
                $hash = $encodeContent['hash'];
                
                $data=array(
                    'ta_id'=> $taId,
                    'tbe_name'=> $request['ae_name'],
                    'tbe_mobile'=> $request['ae_mobile'],
                    'tbe_email'=> $request['ae_email'],
                    'role'=> 'A',
                    'login_id'=> $request['ae_mobile'],
                    'pwd'=> $encryptPassword,
                    'hash'=> $hash,
                    'status' => 1, 'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"));

                $tberesp=$this->tbeDetailsTable()->addTbe($data);
                if(!$tberesp){
                    return new JsonModel(array('success'=>false,'message'=>'unable to add TBE details'));
                }
                                               
                return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to add TA details'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $taList=$this->taDetailsTable()->getTaListAdmin();
        $taListCount=$this->taDetailsTable()->getTaListAdmin(null, 1);
        return new ViewModel(array('taList'=>$taList,'totalCount'=>$taListCount));
    }
    
    public function editTaAction(){
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upIdString = rtrim($paramId, "=");
        $upIdString = base64_decode($upIdString);
        $upIdString = explode("=", $upIdString);
        $taId = array_key_exists(1, $upIdString) ? $upIdString[1] : 0;

        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $tac=$request['tac'];
            $files = $this->getRequest()->getFiles();
            $fileDetails=$request['file_details'];
            $validImageFiles=array('png','jpg','jpeg');
            $fileDetails=json_decode($fileDetails,true);
            $uploadFileDetails=array();
            $deletedImages=json_decode($request['deleted_images'],true);
            $deleteFiles=$deletedImages;
            $tac=trim($tac);
            if($tac=='')
            {
                return new JsonModel(array("success" => false, "message" => "Please enter TAC"));
            }

            $checkTAC = $this->taDetailsTable()->getField(array('tac'=>$tac,'status'=>1),'id');
            if($checkTAC != "" && $checkTAC != $taId)  // check for TA Code
            {
                return new JsonModel(array("success" => false, "message" => "TAC Already exists"));
            }

            $chkTaCons = $this->taConsultantDetailsTable()->getField(array("mobile"=>$request['mobile'], "status"=>'1'), "id");
            if($chkTaCons){
                return new JsonModel(array('success'=>false,'message'=>'TA cannot be STTSE'));   
            }
            
            $fileIds=explode(",",$request['file_Ids']);

            $getFiles=$this->temporaryFiles()->getFiles($fileIds);
            $uploadFiles=array();
            if(count($getFiles)) {
                foreach ($getFiles['images'] as $images) {
                    $uploadFileDetails[] = array(
                        'file_path' => $images['file_path'],
                        'file_data_type' => \Admin\Model\TourismFiles::file_data_type_ta_logo,
                        'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                        'file_extension' => $images['file_extension'],
                        'status' => 1,
                        'duration' => 0,
                        'file_language_type' => 0,
                        'hash' => '',
                        'file_name' => $images['file_name']
                    );
                    if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                        $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                    }
                }
                
                $copyFiles= $this->copypushFiles($uploadFiles);

                if(count($copyFiles['copied']))
                {
                    $this->temporaryFiles()->updateCopiedFiles($copyFiles['copied']);
                }
                if(!$copyFiles['status'])
                {
                    return new JsonModel(array('success'=>false,'message'=>'unable to update TA'));
                }
            }

            $data=array(
                'ta_name'=> $request['ta_name'],
                'ta_mobile'=> $request['ta_mobile'],
                'ta_email'=> $request['ta_email'],
                'ae_name'=> $request['ae_name'],
                'ae_mobile'=> $request['ae_mobile'],
                'ae_email'=> $request['ae_email'],
                'cons_mobile'=>$request['cons_mobile'],
                'tac'=>$request['tac']);
           
            $response=$this->taDetailsTable()->setTaDetails($data, array('id'=>$taId));
            if($response){
                $counter = -1;
                if(count($uploadFileDetails)) {
                    foreach ($uploadFileDetails as $details) {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $taId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable()->addMutipleTourismFiles($uploadFileDetails);
                }
                if(count($deleteFiles))
                {
                    $this->tourismFilesTable()->deletePlaceFiles($deleteFiles);
                }
            
                // editing TA as TBE
                $tbeId=$this->tbeDetailsTable()->getField(array('tbe_mobile'=>$request['ae_mobile']), 'user_id');
                /* $aes = new Aes();
                $password = $request['ae_mobile'];
                $encodeContent = $aes->encrypt($password);
                $encryptPassword = $encodeContent['password'];
                $hash = $encodeContent['hash']; */
                
                $data=array(
                    'tbe_name'=> $request['ae_name'],
                    'tbe_email'=> $request['ae_email'], 
                    'updated_at' => date("Y-m-d H:i:s"));

                // $tberesp=$this->tbeDetailsTable()->addTbe($data);
                $tberesp=$this->tbeDetailsTable()->setTbeDetails($data, array('user_id'=>$tbeId));
                if(!$tberesp){
                    return new JsonModel(array('success'=>false,'message'=>'unable to add TBE details'));
                }

                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update ta details'));
            }
        }else{
            if(!$this->getLoggedInUserId())
            {
                return $this->redirect()->toUrl($this->getBaseUrl());
            }
            $taDetails=$this->taDetailsTable()->getTaDetails($taId);
            $imageFiles=array();
            $imageCounter=-1;
            foreach ($taDetails as $tagent)
            {
                if($tagent['file_extension_type']==1)
                {
                    $imageCounter++;
                    $imageFiles[$imageCounter]['file_path'] = $tagent['file_path'];
                    $imageFiles[$imageCounter]['file_language_type'] = $tagent['file_language_type'];
                    $imageFiles[$imageCounter]['tourism_file_id'] = $tagent['tourism_file_id'];
                    $imageFiles[$imageCounter]['file_name'] = $tagent['file_name'];
                }
            }
            return new ViewModel(array('taDetails'=>$taDetails,'imageUrl'=>$this->filesUrl(), 'imageFiles'=>$imageFiles));
        }
    }

    public function deleteTaAction()
    {        
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $taId=$request['ta_id'];
            $data=array('status'=>0);
            $response=$this->taDetailsTable()->setTaDetails($data,array('id'=>$taId));
            if($response)
            {
                $this->tourismFilesTable()->updatePlaceFiles(array('status'=>0),array('file_data_id'=>$taId,'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_ta_logo));
                $this->tbeDetailsTable()->setTbeDetails($data, array('ta_id'=>$taId));
                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to delete'));
            }
        }
    }

    public function taConsAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $taCons=$this->taConsultantDetailsTable()->getTaConsAdmin();
        $taConsCount=($this->taConsultantDetailsTable()->getTaConsAdmin(null, 1))==array()?0 : ($this->taConsultantDetailsTable()->getTaConsAdmin(null, 1));
        return new ViewModel(array('taCons'=>$taCons,'totalCount'=>$taConsCount));
    }

    public function loadTaConsAction()
    {
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

                if(isset($filterData['name']))
                { 
                    if(isset($filterData['name']['text']))
                    { 
                        $searchData['name']=$filterData['name']['text'];
                    }
                    if(isset($filterData['name']['order']) && $filterData['name']['order'])
                    {
                        $searchData['name_order']=$filterData['name']['order'];
                    }
                }
                if(isset($filterData['mobile']))
                {
                    if(isset($filterData['mobile']['text']))
                    {
                        $searchData['mobile']=$filterData['mobile']['text'];
                    }
                    if(isset($filterData['mobile']['order']) && $filterData['mobile']['order'])
                    {
                        $searchData['mobile_order']=$filterData['mobile']['order'];
                    }
                }
                if(isset($filterData['email']))
                {
                    if(isset($filterData['email']['text']))
                    {
                        $searchData['email']=$filterData['email']['text'];
                    }
                    if(isset($filterData['email']['order']) && $filterData['email']['order'])
                    {
                        $searchData['email_order']=$filterData['email']['order'];
                    }
                }
                if(isset($filterData['address']))
                {
                    if(isset($filterData['address']['text']))
                    {
                        $searchData['address']=$filterData['address']['text'];
                    }
                    if(isset($filterData['address']['order']) && $filterData['address']['order'])
                    {
                        $searchData['address_order']=$filterData['address']['order'];
                    }
                }
                if(isset($filterData['bank_name']))
                { 
                    if(isset($filterData['bank_name']['text']))
                    { 
                        $searchData['bank_name']=$filterData['bank_name']['text'];
                    }
                    if(isset($filterData['bank_name']['order']) && $filterData['bank_name']['order'])
                    {
                        $searchData['bank_name_order']=$filterData['bank_name']['order'];
                    }
                }
                if(isset($filterData['ifsc_code']))
                {
                    if(isset($filterData['ifsc_code']['text']))
                    {
                        $searchData['ifsc_code']=$filterData['ifsc_code']['text'];
                    }
                    if(isset($filterData['ifsc_code']['order']) && $filterData['ifsc_code']['order'])
                    {
                        $searchData['ifsc_code_order']=$filterData['ifsc_code']['order'];
                    }
                }
                if(isset($filterData['bank_ac_no']))
                {
                    if(isset($filterData['bank_ac_no']['text']))
                    {
                        $searchData['bank_ac_no']=$filterData['bank_ac_no']['text'];
                    }
                    if(isset($filterData['bank_ac_no']['order']) && $filterData['bank_ac_no']['order'])
                    {
                        $searchData['bank_ac_no_order']=$filterData['bank_ac_no']['order'];
                    }
                }
                if(isset($filterData['pic']))
                {
                    if(isset($filterData['pic']['text']))
                    {
                        $searchData['pic']=$filterData['pic']['text'];
                    }
                    if(isset($filterData['pic']['order']) && $filterData['pic']['order'])
                    {
                        $searchData['pic_order']=$filterData['pic']['order'];
                    }
                }
                if(isset($filterData['commission']))
                {
                    if(isset($filterData['commission']['text']))
                    {
                        $searchData['commission']=$filterData['commission']['text'];
                    }
                    if(isset($filterData['commission']['order']) && $filterData['commission']['order'])
                    {
                        $searchData['commission_order']=$filterData['commission']['order'];
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
                $totalCount=$this->taConsultantDetailsTable()->getTaConsAdmin($searchData, 1);
            }
            
            $taCons=$this->taConsultantDetailsTable()->getTaConsAdmin($searchData);
            $view = new ViewModel(array('taCons' => $taCons, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function getSeNameAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        if($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $cons_mobile=$request['cons_mobile'];
            $data=array('status'=>0);
            $response=$this->taConsultantDetailsTable()->getField(array('mobile'=>$cons_mobile), 'name');
            if($response)
            {
                return new JsonModel(array('success'=>true,"message"=>$response));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to find SE'));
            }
        }
    }

    public function addTaSdsAction(){
        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
           
            $chkTourists = $this->taSdsTable()->getField(array("tourist_mobile"=>$request['mobile'], "travel_date"=>$request['tdate']), "id");
            if(!$chkTourists){
                $sttUserId = $this->userTable()->getField(array("mobile"=>$request['mobile']), "user_id");
                if($sttUserId){
                    $role = $this->userTable()->getField(array("user_id"=>$sttUserId), "role");
                    if($role != \Admin\Model\User::Individual_role){
                        return new JsonModel(array('success'=>false,'message'=>'Tourist is already subscriber of STT'));
                    }
                }
                $doe = $this->taPurchasesTable()->getField(array('upc'=>$request['upc']), 'doe');
                $tc = 0;
                if ($doe >= date("Y-m-d")){
                    $tc = $this->taPurchasesTable()->getField(array('upc'=>$request['upc']), 'tourists_count');
                }
                else{
                    return new JsonModel(array('success'=>false,'message'=>'upc expired'));
                }
                if($tc){

                    $data=array(
                        'tbe_id'=> $request['tbe_id'],
                        'role'=> $request['role'],
                        'tourist_name'=> $request['name'],
                        'tourist_mobile'=> $request['mobile'],
                        'mobile_country_code'=>$request['mobile_country_code'],
                        'travel_date'=> $request['tdate'],
                        'upc'=> $request['upc'],
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"));

                    $response=$this->taSdsTable()->addTaSDS($data);
                    if($response){
                        $this->taPurchasesTable()->setTaPurchases(array('tourists_count'=> $tc - 1),array('upc'=> $request['upc']));
                        //send notification / sms
                        $subject = "TWISTT notification";
                        $taName = $this->tbeDetailsTable()->getTaName($request['tbe_id']);
                        $ntxt = "Congratulations. M/s $taName has sponsored your subscription for 15 days from " . date('d-m-Y', strtotime($request['tdate'])) . ". As a complement, we will be opening the subscription 3 days earlier. That enables you to download the tales of your choice before embarking on the tour. Wish you a happy Tour";

                        if($sttUserId){
                            $registrationIds = $this->fcmTable()->getDeviceIds($sttUserId);
                            $notificationDetails = array('notification_data_id'=>$sttUserId,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $sttUserId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $ntxt,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                            $notification = $this->notificationTable()->saveData($notificationDetails);
                            if ($registrationIds)
                            {
                                $notification = new SendFcmNotification();
                                $notificationSent = $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => $subject, 'id' => $notificationDetails['notification_data_id'],'type' => $notificationDetails['notification_type']));
                            }
                        }
                        $smsSent = $this->sendBookingSms($request['mobile_country_code'].$request['mobile'],array('text'=>$ntxt));

                        return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
                    }else{
                        return new JsonModel(array('success'=>false,'message'=>'unable to add tourist details'));
                    }
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'mtc reached'));
                }
            }else{
                return new JsonModel(array('success'=>false,'message'=>'Tourist already added to SDS'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $taCons=$this->taConsultantDetailsTable()->getTaConsAdmin();
        $taConsCount=($this->taConsultantDetailsTable()->getTaConsAdmin(null, 1))==array()?0 : ($this->taConsultantDetailsTable()->getTaConsAdmin(null, 1));
        return new ViewModel(array('taCons'=>$taCons,'totalCount'=>$taConsCount));
    }

    public function addTaConsAction(){
        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $chkAEs = $this->taDetailsTable()->getField(array("ae_mobile"=>$request['mobile'], "status"=>'1'), "id");
            if($chkAEs){
                return new JsonModel(array('success'=>false,'message'=>'STTSE cannot be AE'));
            }

            $chkTBEs = $this->tbeDetailsTable()->getField(array("tbe_mobile"=>$request['mobile'], "status"=>'1'), "user_id");
            if(!$chkTBEs){
                $aes = new Aes();
                $password = $request['mobile'];
                $encodeContent = $aes->encrypt($password);
                $encryptPassword = $encodeContent['password'];
                $hash = $encodeContent['hash'];

                $data=array(
                    'name'=> $request['name'],
                    'mobile'=> $request['mobile'],
                    'email'=> $request['email'],
                    'address'=> $request['address'],
                    'bank_name'=> $request['bank_name'],
                    'ifsc_code'=> $request['ifsc_code'],
                    'bank_ac_no'=>$request['ban'],
                    'pic'=>$request['pic'],
                    'commission'=>$request['commission'], 
                    'pwd'=> $encryptPassword,
                    'hash'=> $hash,
                    'status'=> '1');
                
                $response=$this->taConsultantDetailsTable()->addTaConsultant($data);
                if($response){
                    return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'unable to add STTSE details'));
                }
            }else{
                return new JsonModel(array('success'=>false,'message'=>'STTSE cannot be TA or TBE'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $taCons=$this->taConsultantDetailsTable()->getTaConsAdmin();
        $taConsCount=($this->taConsultantDetailsTable()->getTaConsAdmin(null, 1))==array()?0 : ($this->taConsultantDetailsTable()->getTaConsAdmin(null, 1));
        return new ViewModel(array('taCons'=>$taCons,'totalCount'=>$taConsCount));
    }

    public function editTaConsAction(){
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upIdString = rtrim($paramId, "=");
        $upIdString = base64_decode($upIdString);
        $upIdString = explode("=", $upIdString);
        $taId = array_key_exists(1, $upIdString) ? $upIdString[1] : 0;

        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;

            $chkTBEs = $this->tbeDetailsTable()->getField(array("tbe_mobile"=>$request['mobile'], "status"=>'1'), "user_id");
            if(!$chkTBEs){
                $data=array(
                    'name'=> $request['name'],
                    'mobile'=> $request['mobile'],
                    'email'=> $request['email'],
                    'address'=> $request['address'],
                    'bank_name'=> $request['bank_name'],
                    'ifsc_code'=> $request['ifsc_code'],
                    'bank_ac_no'=>$request['ban'],
                    'pic'=>$request['pic'],
                    'commission'=>$request['commission']);

                $response=$this->taConsultantDetailsTable()->setTaConsultantDetails($data, array('id'=>$taId));
                if($response){
                    return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'unable to update STTSE details'));
                }
            }else{
                return new JsonModel(array('success'=>false,'message'=>'STTSE cannot be TA or TBE'));
            }
        }
        
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $tacDetails=$this->taConsultantDetailsTable()->getTaConsultantDetails($taId);
        return new ViewModel(array('tacDetails'=>$tacDetails));
    }

    public function deleteTaConsAction()
    {       
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        } 
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $tacId=$request['tac_id'];
            $data=array('status'=>0);
            $response=$this->taConsultantDetailsTable()->setTaConsultantDetails($data,array('id'=>$tacId));
            if($response)
            {
                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to delete'));
            }
        }
    }

    public function tbeListAction()
    {
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upIdString = rtrim($paramId, "=");
        $upIdString = base64_decode($upIdString);
        $upIdString = explode("=", $upIdString);
        $taId = array_key_exists(1, $upIdString) ? $upIdString[1] : 0;

        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $tbeList=$this->tbeDetailsTable()->getTbeAdmin(array('limit'=>10,'offset'=>0), 0, $taId);
        $tbeCount=($this->tbeDetailsTable()->getTbeAdmin(array('limit'=>10,'offset'=>0), 1, $taId))==array()?0 : ($this->tbeDetailsTable()->getTbeAdmin(array('limit'=>10,'offset'=>0), 1, $taId));
        $taName=$this->taDetailsTable()->getField(array('id'=>$taId), 'ta_name');
        return new ViewModel(array('tbeList'=>$tbeList,'totalCount'=>$tbeCount, 'taName'=>$taName, 'taId'=>$taId));
    }

    public function loadTbeListAction()
    {
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upIdString = rtrim($paramId, "=");
        $upIdString = base64_decode($upIdString);
        $upIdString = explode("=", $upIdString);
        $taId = array_key_exists(1, $upIdString) ? $upIdString[1] : 0;

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

                if(isset($filterData['tbe_name']))
                { 
                    if(isset($filterData['tbe_name']['text']))
                    { 
                        $searchData['tbe_name']=$filterData['tbe_name']['text'];
                    }
                    if(isset($filterData['tbe_name']['order']) && $filterData['tbe_name']['order'])
                    {
                        $searchData['tbe_name_order']=$filterData['tbe_name']['order'];
                    }
                }
                if(isset($filterData['tbe_mobile']))
                {
                    if(isset($filterData['tbe_mobile']['text']))
                    {
                        $searchData['tbe_mobile']=$filterData['tbe_mobile']['text'];
                    }
                    if(isset($filterData['tbe_mobile']['order']) && $filterData['tbe_mobile']['order'])
                    {
                        $searchData['tbe_mobile_order']=$filterData['tbe_mobile']['order'];
                    }
                }
                if(isset($filterData['tbe_email']))
                {
                    if(isset($filterData['tbe_email']['text']))
                    {
                        $searchData['tbe_email']=$filterData['tbe_email']['text'];
                    }
                    if(isset($filterData['tbe_email']['order']) && $filterData['tbe_email']['order'])
                    {
                        $searchData['tbe_email_order']=$filterData['tbe_email']['order'];
                    }
                }
                if(isset($filterData['role']))
                {
                    if(isset($filterData['role']['text']))
                    {
                        $searchData['role']=$filterData['role']['text'];
                    }
                    if(isset($filterData['role']['order']) && $filterData['role']['order'])
                    {
                        $searchData['role_order']=$filterData['role']['order'];
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
                $totalCount=$this->tbeDetailsTable()->getTbeAdmin($searchData, 1, $taId);
            }
            
            $tbeList=$this->tbeDetailsTable()->getTbeAdmin($searchData, 0, $taId);
            $taName=$this->taDetailsTable()->getField(array('id'=>$taId), 'ta_name');
            $view = new ViewModel(array('tbeList' => $tbeList, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount, 'taName'=>$taName, 'taId'=>$taId));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function tbeTouristsAction() {
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upIdString = rtrim($paramId, "=");
        $upIdString = base64_decode($upIdString);
        $upIdString = explode("=", $upIdString);
        $tbeId = array_key_exists(1, $upIdString) ? $upIdString[1] : 0;

        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $taId = $this->tbeDetailsTable()->getField(array("user_id"=>$tbeId, "status"=>'1'), "ta_id");
        $tbeList = $this->tbeDetailsTable()->getTasTbeList($taId);
        $tList = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 0, array('tbe_id'=>$tbeId));
        $totalCount = $this->taSdsTable()->getTBETouristsdetails(array('limit'=>10,'offset'=>0), 1, array('tbe_id'=>$tbeId));
        $tbeName=$this->tbeDetailsTable()->getField(array("user_id"=>$tbeId), "tbe_name");
        $pdArr = array('taId'=>$taId,'tbeId'=>$tbeId, 'tbeName'=>$tbeName); 
        return new ViewModel(array('tList'=>$tList,'totalCount'=>$totalCount, 'tbeList'=>$tbeList, 'pdArr'=>$pdArr));
    }

    public function loadTbeTouristsAction() {
        
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upIdString = rtrim($paramId, "=");
        $upIdString = base64_decode($upIdString);
        $upIdString = explode("=", $upIdString);
        $tbeId = array_key_exists(1, $upIdString) ? $upIdString[1] : 0;

        $taId = $this->tbeDetailsTable()->getField(array("user_id"=>$tbeId, "status"=>'1'), "ta_id");

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
            if($type && $type=='search')
            {
                $totalCount=$this->taSdsTable()->getTBETouristsdetails($searchData, 1, array('tbe_id'=>$tbeId));
            }
        }
        $tList = $this->taSdsTable()->getTBETouristsdetails($searchData, 0, array('tbe_id'=>$tbeId));
        $tbeName = $this->tbeDetailsTable()->getField(array("user_id"=>$tbeId), "tbe_name");
        $pdArr = array('taId'=>$taId,'tbeId'=>$tbeId, 'tbeName'=>$tbeName); 
        $view = new ViewModel(array('tList'=>$tList,'totalCount'=>$totalCount, 'offset' => $offset,'type'=>$type, 'pdArr'=>$pdArr));
            $view->setTerminal(true);
            return $view;
    }


    public function addTbeAction(){
        $taId = '';
        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $taId = $request['ta_id'];
            $taTbeCount = $this->tbeDetailsTable()->getTasTbeCount($taId);
            if($taTbeCount >= 10){
                return new JsonModel(array('success'=>false , 'message'=>'Maximum no. of TBEs added already'));
            }
            $chkTaCons = $this->taConsultantDetailsTable()->getField(array("mobile"=>$request['mobile'], "status"=>'1'), "id");
            if(!$chkTaCons){
                $chkTA = $this->tbeDetailsTable()->getField(array("tbe_mobile"=>$request['mobile'], "status"=>'1'), "user_id");
                if(!$chkTA){
                    $aes = new Aes();
                    $password = $request['mobile'];
                    $encodeContent = $aes->encrypt($password);
                    $encryptPassword = $encodeContent['password'];
                    $hash = $encodeContent['hash'];
                    
                    $data=array(
                        'ta_id'=> (int)$request['ta_id'],
                        'tbe_name'=> $request['name'],
                        'tbe_mobile'=> $request['mobile'],
                        'tbe_email'=> $request['email'],
                        'role'=> 'B',
                        'login_id'=> $request['mobile'],
                        'pwd'=> $encryptPassword,
                        'hash'=> $hash,
                        'status' => 1, 'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"));

                    $response=$this->tbeDetailsTable()->addTbe($data);
                    if($response){
                        return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
                    }else{
                        return new JsonModel(array('success'=>false,'message'=>'unable to add TBE details'));
                    }
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'TBE already added under another TA'));
                }
            }else{
                return new JsonModel(array('success'=>false,'message'=>'TBE cannot be STTSE'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $tbeList=$this->tbeDetailsTable()->getTbeAdmin(null, 0, $taId);
        $tbeCount=($this->tbeDetailsTable()->getTbeAdmin(null, 1, $taId))==array()?0 : ($this->tbeDetailsTable()->getTbeAdmin(null, 1, $taId));
        $taName=$this->taDetailsTable()->getField(array('id'=>$taId), 'ta_name');
        return new ViewModel(array('tbeList'=>$tbeList,'totalCount'=>$tbeCount, 'taName'=>$taName, taId=>$taId));
    }

    public function editTbeAction(){
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upIdString = rtrim($paramId, "=");
        $upIdString = base64_decode($upIdString);
        $upIdString = explode("=", $upIdString);
        $tbeId = array_key_exists(1, $upIdString) ? $upIdString[1] : 0;

        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $chkTaCons = $this->taConsultantDetailsTable()->getField(array("mobile"=>$request['mobile'], "status"=>'1'), "id");
            if(!$chkTaCons){
                //$chkTA = $this->tbeDetailsTable()->getField(array("tbe_mobile"=>$request['mobile'], "status"=>'1'), "user_id");
                /* if(!$chkTA){ */                
                    $data=array(
                        'tbe_name'=> $request['name'],
                        'tbe_email'=> $request['email']
                        );

                    $response=$this->tbeDetailsTable()->setTbeDetails($data, array('user_id'=>$tbeId));
                    if($response){
                        return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
                    }else{
                        return new JsonModel(array('success'=>false,'message'=>'unable to update TBE details'));
                    }
                /* }else{
                    return new JsonModel(array('success'=>false,'message'=>'TBE already added under another TA'));
                } */
            }else{
                return new JsonModel(array('success'=>false,'message'=>'STTSE cannot be TA or TBE'));
            }
        }
        
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $tbeDetails=$this->tbeDetailsTable()->getTbeDetails($tbeId);
        return new ViewModel(array('tbeDetails'=>$tbeDetails));
    }

    public function deleteTbeAction()
    {        
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $tbeId=$request['tbe_id'];
            $data=array('status'=>0);
            $dataExists = $this->taSdsTable()->getField(array('tbe_id'=>$tbeId), 'id');
            if($dataExists)
                return new JsonModel(array('success'=>false,"message"=>'unable to delete due to dependent data'));

            $response=$this->tbeDetailsTable()->setTbeDetails($data,array('user_id'=>$tbeId));
            if($response)
            {
                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to delete'));
            }
        }
    }

    public function promotersListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        
        $promotersList=$this->userTable()->getAllPromotersAdmin();
        $promotersListCount=$this->userTable()->getAllPromotersAdminCount();
        return new ViewModel(array('promotersList'=>$promotersList,'totalCount'=>$promotersListCount[0]['promoters_count']));
    }

    public function loadPromotersListAction()
    {
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

                if(isset($filterData['user_name']))
                { 
                    if(isset($filterData['user_name']['text']))
                    { 
                        $searchData['user_name']=$filterData['user_name']['text'];
                    }
                    if(isset($filterData['user_name']['order']) && $filterData['user_name']['order'])
                    {
                        $searchData['user_name_order']=$filterData['user_name']['order'];
                    }
                }
                if(isset($filterData['mobile']))
                {
                    if(isset($filterData['mobile']['text']))
                    {
                        $searchData['mobile']=$filterData['mobile']['text'];
                    }
                    if(isset($filterData['mobile']['order']) && $filterData['mobile']['order'])
                    {
                        $searchData['mobile_order']=$filterData['mobile']['order'];
                    }
                }
                if(isset($filterData['sponsors_successful']))
                {
                    if(isset($filterData['sponsors_successful']['text']))
                    {
                        $searchData['sponsors_successful']=$filterData['sponsors_successful']['text'];
                    }
                    if(isset($filterData['sponsors_successful']['order']) && $filterData['sponsors_successful']['order'])
                    {
                        $searchData['sponsors_successful_order']=$filterData['sponsors_successful']['order'];
                    }
                }
                if(isset($filterData['sponsors_terminated']))
                {
                    if(isset($filterData['sponsors_terminated']['text']))
                    {
                        $searchData['sponsors_terminated']=$filterData['sponsors_terminated']['text'];
                    }
                    if(isset($filterData['sponsors_terminated']['order']) && $filterData['sponsors_terminated']['order'])
                    {
                        $searchData['sponsors_terminated_order']=$filterData['sponsors_terminated']['order'];
                    }
                }
                if(isset($filterData['sponsors_active']))
                {
                    if(isset($filterData['sponsors_active']['text']))
                    {
                        $searchData['sponsors_active']=$filterData['sponsors_active']['text'];
                    }
                    if(isset($filterData['sponsors_active']['order']) && $filterData['sponsors_active']['order'])
                    {
                        $searchData['sponsors_active_order']=$filterData['sponsors_active']['order'];
                    }
                }
                if(isset($filterData['amount_due']))
                {
                    if(isset($filterData['amount_due']['text']))
                    {
                        $searchData['amount_due']=$filterData['amount_due']['text'];
                    }
                    if(isset($filterData['amount_due']['order']) && $filterData['amount_due']['order'])
                    {
                        $searchData['amount_due_order']=$filterData['amount_due']['order'];
                    }
                }
                if(isset($filterData['amount_paid']))
                {
                    if(isset($filterData['amount_paid']['text']))
                    {
                        $searchData['amount_paid']=$filterData['amount_paid']['text'];
                    }
                    if(isset($filterData['amount_paid']['order']) && $filterData['amount_paid']['order'])
                    {
                        $searchData['amount_paid_order']=$filterData['amount_paid']['order'];
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
                $countResult=$this->userTable()->getAllPromotersAdminCount($searchData);

                if(count($countResult))
                {
                    $totalCount=count($countResult);
                }
            }
            
            $promotersList=$this->userTable()->getAllPromotersAdmin($searchData);
            $view = new ViewModel(array('promotersList' => $promotersList, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount[0]['promoters_count']));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function sponsorsListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $npc = $this->pricingTable()->getNPCDetails();
        $data=array('limit'=>10,'offset'=>0,'npcd'=>$npc['npcd'],'npcp'=>$npc['npcp']);
        $sponsorsList=$this->userTable()->getAllSponsorsAdmin($data);
        $sponsorsListCount=$this->userTable()->getAllSponsorsAdminCount();
        return new ViewModel(array('sponsorsList'=>$sponsorsList,'totalCount'=>count($sponsorsListCount)));
    }

    public function loadSponsorsListAction()
    {
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

                if(isset($filterData['user_name']))
                {
                    if(isset($filterData['user_name']['text']))
                    {
                        $searchData['user_name']=$filterData['user_name']['text'];
                    }
                    if(isset($filterData['user_name']['order']) && $filterData['user_name']['order'])
                    {
                        $searchData['user_name_order']=$filterData['user_name']['order'];
                    }
                }

                if(isset($filterData['mobile']))
                {
                    if(isset($filterData['mobile']['text']))
                    {
                        $searchData['mobile']=$filterData['mobile']['text'];
                    }
                    if(isset($filterData['mobile']['order']) && $filterData['mobile']['order'])
                    {
                        $searchData['mobile_order']=$filterData['mobile']['order'];
                    }
                }

                if(isset($filterData['sponsor_type']))
                {
                    if(isset($filterData['sponsor_type']['text']) && $filterData['sponsor_type']['text'])
                    {
                        $searchData['sponsor_type']=$filterData['sponsor_type']['text'];
                    }
                }

                if(isset($filterData['bookings_count']))
                {
                    if(isset($filterData['bookings_count']['text']))
                    {
                        $searchData['bookings_count']=$filterData['bookings_count']['text'];
                    }
                    if(isset($filterData['bookings_count']['order']) && $filterData['bookings_count']['order'])
                    {
                        $searchData['bookings_count_order']=$filterData['bookings_count']['order'];
                    }
                }

                if(isset($filterData['booking_total_payment']))
                {
                    if(isset($filterData['booking_total_payment']['text']))
                    {
                        $searchData['booking_total_payment']=$filterData['booking_total_payment']['text'];
                    }
                    if(isset($filterData['booking_total_payment']['order']) && $filterData['booking_total_payment']['order'])
                    {
                        $searchData['booking_total_payment_order']=$filterData['booking_total_payment']['order'];
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
                $countResult=$this->userTable()->getAllSponsorsAdminCount($searchData);

                if(count($countResult))
                {
                    $totalCount=count($countResult);
                }
            }
            $npc = $this->pricingTable()->getNPCDetails();
            $searchData['npcd']=$npc['npcd'];
            $searchData['npcp']=$npc['npcp'];
            $sponsorsList=$this->userTable()->getAllSponsorsAdmin($searchData);
            $view = new ViewModel(array('sponsorsList' => $sponsorsList, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function parametersListAction(){
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }

        $request = $this->getRequest()->getPost();
        $allvariables = $this->pricingTable()->adminGetParameters(array('plantype'=>'0'));
        
        $i = 0;
        foreach($allvariables as $variables)
        {
            $retVars[$i]['no_of_comp_pwds'] = $variables['no_of_comp_pwds'];
            $retVars[$i]['no_of_days'] = $variables['no_of_days'];
            $retVars[$i]['maxdownloads'] = $variables['maxdownloads'];
            $retVars[$i]['maxquantity'] = $variables['maxquantity'];
            $retVars[$i]['subscription_validity'] = $variables['subscription_validity'];
            $retVars[$i]['sponsor_bonus_min'] = $variables['sponsor_bonus_min'];
            $retVars[$i]['sponsor_comp_pwds'] = $variables['sponsor_comp_pwds'];
            $retVars[$i]['sponsor_pwd_validity'] = $variables['sponsor_pwd_validity'];
            $retVars[$i]['GST'] = $variables['GST'];
            $retVars[$i]['first_rdp'] = $variables['first_rdp'];
            $retVars[$i]['second_rdp'] = $variables['second_rdp'];
            $retVars[$i]['npc_passwords'] = $variables['npc_passwords'];
            $retVars[$i]['npc_days'] = $variables['npc_days'];
            $retVars[$i]['annual_price'] = $variables['price'];
            $i++;
        }

        $allpvariables = $this->pricingTable()->adminGetParameters(array('plantype'=>'1'));
        $i = 0;
        foreach($allpvariables as $pvariables)
        {
            $retVars[$i]['offer_start_date'] = $pvariables['start_date'];
            $retVars[$i]['offer_end_date'] = $pvariables['end_date'];
            $retVars[$i]['offer_price'] = $pvariables['price'];
            $i++;
        }
        //print_r($retVars); exit();
        return new ViewModel(array('parametersList'=>$retVars,'totalCount'=>count($retVars)));
    }
    public function pricingListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $pricingList=$this->pricingTable()->getAllPricingPlans();
        return new ViewModel(array('pricingList'=>$pricingList,'totalCount'=>count($pricingList)));
    }

    public function editPricingAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {  
            $request = $this->getRequest()->getPost();
            $pid = $request['pid'];        
            $data = array();
            $val=$request['pn'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('planname'=>$val), array('id'=>$pid));
                $data['planname'] = $val;

            }
                
            $val=$request['p'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('price'=>$val), array('id'=>$pid));
                $data['price'] = $val;
            }

            $val=$request['sv'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('subscription_validity'=>$val), array('id'=>$pid));
                $data['subscription_validity'] = $val;
            }
            if($pid != "1"){
                $val=$request['sd'];
                if(!is_null($val) && $val != ""){
                    //$response=$this->pricingTable()->updatePricingPlan(array('start_date'=>$val), array('id'=>$pid));
                    $data['start_date'] = date('Y-m-d',strtotime($val));
                }

                $val=$request['ed'];
                if(!is_null($val) && $val != ""){
                    //$response=$this->pricingTable()->updatePricingPlan(array('end_date'=>$val), array('id'=>$pid));
                    $data['end_date'] = date('Y-m-d',strtotime($val));
                }
            }
            $val=$request['spv'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('sponsor_pwd_validity'=>$val), array('id'=>$pid));
                $data['sponsor_pwd_validity'] = $val;
            }

            $val=$request['frd'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('first_rdp'=>$val), array('id'=>$pid));
                $data['first_rdp'] = $val;
            }

            $val=$request['srd'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('second_rdp'=>$val), array('id'=>$pid));
                $data['second_rdp'] = $val;
            }

            $val=$request['cp'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('no_of_comp_pwds'=>$val), array('id'=>$pid));
                $data['no_of_comp_pwds'] = $val;
            }

            $val=$request['scp'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('sponsor_comp_pwds'=>$val), array('id'=>$pid));
                $data['sponsor_comp_pwds'] = $val;
            }

            $val=$request['md'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('maxdownloads'=>$val), array('id'=>$pid));
                $data['maxdownloads'] = $val;
            }

            $val=$request['tf'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('no_of_days'=>$val), array('id'=>$pid));
                $data['no_of_days'] = $val;
            }

            $val=$request['np'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('npc_passwords'=>$val), array('id'=>$pid));
                $data['npc_passwords'] = $val;
            }

            $val=$request['nd'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('npc_days'=>$val), array('id'=>$pid));
                $data['npc_days'] = $val;
            }

            $val=$request['gst'];
            if(!is_null($val) && $val != ""){
                //$response=$this->pricingTable()->updatePricingPlan(array('GST'=>$val), array('id'=>$pid));
                $data['GST'] = $val;
            }

            $val=$request['at'];
            if(!is_null($val) && $val != ""){
                $data['app_text'] = $val;
            }
            $val=$request['wt'];
            if(!is_null($val) && $val != ""){
                $data['web_text'] = $val;
            }
            $response=$this->pricingTable()->updatePricingPlan($data, array('id'=>$pid));
           /*  $stringRepresentation= json_encode($data);
            return new JsonModel(array('success'=>false , 'message'=>$stringRepresentation)); */
            if($response){
                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update'));
            }            
        }
        else
        {
            $paramId = $this->params()->fromRoute('id', '');
            if (!$paramId)
            {
                return $this->redirect()->toUrl($this->getBaseUrl());
            }
            $pidString = rtrim($paramId, "=");
            $pidString = base64_decode($pidString);
            $pidString = explode("=", $pidString);
            $pid = array_key_exists(1, $pidString) ? $pidString[1] : 0;
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $pricing = $this->pricingTable()->getPricingDetails(array("id"=>$pid));
        $view = new ViewModel(array('pricing' => $pricing));
        return $view;
    }

    public function promoterParametersAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $pparameters=$this->promoterParametersTable()->getPromoterParameters();
        return new ViewModel(array('pparameters'=>$pparameters,'totalCount'=>count($pparameters)));
    }

    public function editPromoterParametersAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {  
            $request = $this->getRequest()->getPost();
            $pid = $request['pid'];        
            $data = array();

            $val=$request['pc'];
            if(!is_null($val) && $val != "")
                $data['pwd_ceiling'] = $val;
                
            /* $val=$request['mpp'];
            if(!is_null($val) && $val != "")
                $data['min_pay_pwds'] = $val; */

            $val=$request['app'];
            if(!is_null($val) && $val != "")
                $data['amt_per_pwd'] = $val;
            
            $val=$request['rc'];
            if(!is_null($val) && $val != "")
                $data['redeem_ceiling'] = $val;

            $val=$request['pap'];
            if(!is_null($val) && $val != "")
                $data['pay_after_pwds'] = $val;
            
            $response = $this->promoterParametersTable()->setPromoterParameters($data, array('id'=>$pid));

            if($response){
                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update'));
            }
        }

        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $pparameters = $this->promoterParametersTable()->getPromoterParameters();
        $view = new ViewModel(array('pparameters' => $pparameters[0]));
        return $view;
    }

    public function editSponsorAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {            
            $request = $this->getRequest()->getPost();
            $paramId = $request['suid'];
            if (!$paramId)
            {
                return $this->redirect()->toUrl($this->getBaseUrl());
            }
            $userIdString = rtrim($paramId, "=");
            $userIdString = base64_decode($userIdString);
            $userIdString = explode("=", $userIdString);
            $uid = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;

            $refBy=$request['ref'];
            if(!is_null($refBy) || $refBy != ""){
                $data=array('ref_by'=>$refBy, 'user_id'=>$uid);
                $response=$this->referTable()->adminUpdateRefer($data);
            }
            
            $refM=$request['rfm'];
            if(!is_null($refM) || $refM != ""){
                $data=array('ref_mobile'=>$refM, 'user_id'=>$uid);
                $response=$this->referTable()->adminUpdateRefer($data);
            }
            
            $dp=$request['dp'];
            if(!is_null($dp) || $dp != ""){
                $where=array("user_id"=>$uid);
                $update=array('discount_percentage'=>$dp);
                $response=$this->userTable()->updateUser($update,$where);
            }

            if($response){
                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update'));
            }
        }
    }

    public function editNotifyAction()
    {
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userIdString = rtrim($paramId, "=");
        $userIdString = base64_decode($userIdString);
        $userIdString = explode("=", $userIdString);
        $uid = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;

        if($this->getRequest()->isXmlHttpRequest())
        {       
            $uid = $paramId;  
            $request = $this->getRequest()->getPost();
            $subject = $request['sub'];
            $emailText = $request['email'];
            $smsMessage = $request['sms'];
            
            $user = $this->userTable()->getFields(array("user_id"=>$uid),array('user_name','email','mobile','mobile_country_code'));
            $registrationIds = $this->fcmTable()->getDeviceIds($uid);
            $user['emailText'] = $emailText;
            $user['header'] = $subject;
            $this->mailSTTUserNoAttachment($user['email'], $subject, 'notify', $user);
            
            $notificationDetails = array('notification_data_id'=>$uid,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $uid, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $smsMessage,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
            $notification = $this->notificationTable()->saveData($notificationDetails);
            if ($registrationIds)
            {
                $notification = new SendFcmNotification();
                $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => $subject, 'id' => $notificationDetails['notification_data_id'],'type' => $notificationDetails['notification_type']));
            }
            //$this->sendPasswordSms($user['mobile_country_code'].$user['mobile'],$smsMessage); //array('text'=>$smsMessage)
            return new JsonModel(array('success'=>true , 'message'=>'notified successfully'));
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $user = $this->userTable()->getFields(array("user_id"=>$uid),array('user_id','user_name','email','mobile','mobile_country_code'));
        $view = new ViewModel(array('user' => $user));
        return $view;
    }

    public function sponsorPasswordsAction()
    {
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $userIdString = rtrim($paramId, "=");
        $userIdString = base64_decode($userIdString);
        $userIdString = explode("=", $userIdString);
        $userId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
                    
        $sponsorPasswords=$this->bookingsTable()->getsponsorPasswordsAdmin($data=array('user_id'=>$userId,'limit'=>10,'offset'=>0));
        $sponsorPasswordsCount=$this->bookingsTable()->getsponsorPasswordsAdminCount($data=array('user_id'=>$userId));
        $sponsorName=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name'));
        return new ViewModel(array('sponsorPasswords'=>$sponsorPasswords,'totalCount'=>count($sponsorPasswordsCount), 'sponsorName'=>$sponsorName));
    }

    public function loadSponsorPasswordsAction()
    {
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
                $userIdString = $filterData['uid']['text'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $userId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
                $searchData['user_id'] = $userId;

                if(isset($filterData['booking_id']))
                {
                    if(isset($filterData['booking_id']['text']))
                    {
                        $searchData['booking_id']=$filterData['booking_id']['text'];
                    }
                    if(isset($filterData['booking_id']['order']) && $filterData['booking_id']['order'])
                    {
                        $searchData['booking_id_order']=$filterData['booking_id']['order'];
                    }
                }

                if(isset($filterData['created_at']))
                {
                    if(isset($filterData['created_at']['text']))
                    {
                        $searchData['created_at']=$filterData['created_at']['text'];
                    }
                    if(isset($filterData['created_at']['order']) && $filterData['created_at']['order'])
                    {
                        $searchData['created_at_order']=$filterData['created_at']['order'];
                    }
                }

                if(isset($filterData['bookings_count']))
                {
                    if(isset($filterData['bookings_count']['text']))
                    {
                        $searchData['bookings_count']=$filterData['bookings_count']['text'];
                    }
                    if(isset($filterData['bookings_count']['order']) && $filterData['bookings_count']['order'])
                    {
                        $searchData['bookings_count_order']=$filterData['bookings_count']['order'];
                    }
                }

                if(isset($filterData['tour_type']) && $filterData['tour_type']['text'])
                {
                    if(isset($filterData['tour_type']['text']))
                    {
                        $searchData['tour_type']=$filterData['tour_type']['text'];
                    }
                }
            }

            if(isset($request['page_number']))
            {
                $userIdString = $request['uid'];
                $userIdString = base64_decode($userIdString);
                $userIdString = explode("=", $userIdString);
                $userId = array_key_exists(1, $userIdString) ? $userIdString[1] : 0;
                $searchData['user_id'] = $userId;
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset']=$offset;
                $searchData['limit']=$limit;
            }

            $totalCount=0;

            if($type && $type=='search')
            {
                $countResult=$this->bookingsTable()->getsponsorPasswordsAdminCount($searchData);
                if(count($countResult))
                {
                    $totalCount=count($countResult);
                }
            }

            $sponsorPasswords=$this->bookingsTable()->getsponsorPasswordsAdmin($searchData);
            $view = new ViewModel(array('sponsorPasswords' => $sponsorPasswords, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function tourOperatorListAction()
    {
        $tourOperatorList=$this->tourOperatorDetailsTable()->getTourOperatorList();
        $totalCount=$this->tourOperatorDetailsTable()->getTourOperatorListCount();
        return new ViewModel(array('tourOperatorList'=>$tourOperatorList,'totalCount'=>count($totalCount)));
    }
    public function loadTourOperatorListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            if (isset($request['page_number']))
            {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $searchData['offset'] = $offset;
                $searchData['limit'] = 10;
            }
            $totalCount = 0;


            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);

                if(isset($filterData['tour_operator_name']))
                {
                    if(isset($filterData['tour_operator_name']['text']))
                    {
                        $searchData['tour_operator_name']=$filterData['tour_operator_name']['text'];
                    }
                    if(isset($filterData['tour_operator_name']['order']) && $filterData['tour_operator_name']['order'])
                    {
                        $searchData['tour_operator_name_order']=$filterData['tour_operator_name']['order'];
                    }
                }

                if(isset($filterData['email']))
                {
                    if(isset($filterData['email']['text']))
                    {
                        $searchData['email']=$filterData['email']['text'];
                    }
                    if(isset($filterData['email']['order']) && $filterData['email']['order'])
                    {
                        $searchData['email_order']=$filterData['email']['order'];
                    }
                }

                if(isset($filterData['contact_person']))
                {
                    if(isset($filterData['contact_person']['text']))
                    {
                        $searchData['contact_person']=$filterData['contact_person']['text'];
                    }
                    if(isset($filterData['contact_person']['order']) && $filterData['contact_person']['order'])
                    {
                        $searchData['contact_person_order']=$filterData['contact_person']['order'];
                    }
                }

                if(isset($filterData['mobile_number']))
                {
                    if(isset($filterData['mobile_number']['text']))
                    {
                        $searchData['mobile_number']=$filterData['mobile_number']['text'];
                    }
                    if(isset($filterData['mobile_number']['order']) && $filterData['mobile_number']['order'])
                    {
                        $searchData['mobile_number_order']=$filterData['mobile_number']['order'];
                    }
                }
            }

            if ($type && $type == 'search')
            {
                $totalCount = $this->tourOperatorDetailsTable()->getTourOperatorListCount($searchData);
                if (count($totalCount))
                {
                    $totalCount = count($totalCount);
                }
            }

            $tourOperatorList=$this->tourOperatorDetailsTable()->getTourOperatorList($searchData);
            $view = new ViewModel(array('tourOperatorList' => $tourOperatorList, 'offset' => $offset, 'totalCount' => $totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
     public function seasonalSpecialsListAction()
     {
         $seasonalSpecials=$this->seasonalSpecialsTable()->getSeasonalSpecialsList();
         $totalCount=$this->seasonalSpecialsTable()->getSeasonalSpecialsListCount();
         return new ViewModel(array('seasonalSpecials'=>$seasonalSpecials,'totalCount'=>count($totalCount)));

     }
    public function addSeasonalSpecialsAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $seasonalName=$request['season_name'];
            $tourType=$request['tour_type'];
            $placeIds=$request['place_ids'];
            $seasonalDescription=$request['description'];
            /* $startDate=$request['start_date'];
            $endDate=$request['end_date'];
            $discountPrice=$request['discounted_price'];
            $noOfDays=$request['no_of_days'];
            $price=$request['price']; */

            $files = $this->getRequest()->getFiles();
            $validImageFiles=array('png','jpg','jpeg');

              if($seasonalName=='')
              {
                  return new JsonModel(array('success' => false, 'message' => 'please enter season name'));

              }
            if($seasonalDescription=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter season description'));

            }
            /* if($price=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter price'));

            } 
            if($discountPrice=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter discount price'));

            }
            if($startDate=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter start date'));

            }
            if($endDate=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter end date'));

            } */
              if($placeIds=='')
              {
                  return new JsonModel(array('success' => false, 'message' => 'please select places'));

              }
            if($tourType=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please select tour type'));

            }

            if (isset($files['image_file']))
            {
                $attachment=$files['image_file'];
                    $imagesPath = '';
                    $pdf_to_image = "";
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
                   /* if (move_uploaded_file($attachment['tmp_name'], getcwd() . "/public/" . $filePath)) {
                        $imagesPath = $filePath;
                        $localImagePath = getcwd() . "/public/" . $filePath;
                        chmod(getcwd() . "/public/" . $filePath, 0777);
                    }*/
                $this->pushFiles($filePath,$attachment['tmp_name'],$attachment['type']);

                /* $fp = fopen(getcwd() . "/public/" . $filePath, 'w');
                 $encodeContent=$aes->encrypt(file_get_contents($attachment['tmp_name']));
                 $encodeString=$encodeContent['password'];
                 $hash=$encodeContent['hash'];
                 fwrite($fp,$encodeString);
                 fclose($fp);*/
                    $uploadFileDetails = array(
                        'file_path' => $filePath,
                        'file_data_type' => \Admin\Model\TourismFiles::file_data_type_seasonal_files,
                        'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                        'file_extension' => $ext,
                        'status' => 1,
                        'duration' => 0,
                        'hash' => '',
                        'file_language_type' => 0,
                        'file_name' => $attachment['name']
                    );

            }else
            {
                return new JsonModel(array('success' => false, 'message' => 'please upload images'));
            }
              /* if($startDate=="")
              {
                  $startDate='0000-00-00';
                  $endDate='0000-00-00';
                  $tourType=0;
                  $placeIds='';
                  $price=0;
                  $discountPrice=0;
              } */
            //$data=array('seasonal_name'=>$seasonalName,'no_of_days'=>$noOfDays, 'start_date'=>$startDate, 'end_date'=>$endDate, 'seasonal_description'=>$seasonalDescription, 'seasonal_type'=>$tourType,'place_ids'=>$placeIds,'price'=>$price,'discount_price'=>$discountPrice,'status'=>1);
            $data=array('seasonal_name'=>$seasonalName, 'seasonal_description'=>$seasonalDescription, 'seasonal_type'=>$tourType,'place_ids'=>$placeIds,'status'=>1);
            /* $stringRepresentation= json_encode($data);
            return new JsonModel(array('success'=>false , 'message'=>$stringRepresentation));  */
            $saveResult=$this->seasonalSpecialsTable()->addSeasonalSpecials($data);
              if($saveResult['success'])
              {
                  $id=$saveResult['id'];
                  $uploadFileDetails['file_data_id']=$id;
                  $result= $this->tourismFilesTable()->addTourismFiles($uploadFileDetails);
                 return new JsonModel(array('success'=>true , 'message'=>'added successfully'));

              }
            return new JsonModel(array('success'=>false , 'message'=>'Something went wrong'));
        }
       $placesList=$this->tourismPlacesTable()->getTourismList(array('tour_type'=>\Admin\Model\PlacePrices::tour_type_World_tour,'limit'=>-1));
        return new ViewModel(array('placesList'=>$placesList));
    }
    public function editSeasonalSpecialAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $seasonalName=$request['season_name'];
            $tourType=$request['tour_type'];
            $placeIds=$request['place_ids'];
            $seasonalDescription=$request['description'];
            /* $startDate=$request['start_date'];
            $endDate=$request['end_date'];
            $discountPrice=$request['discounted_price']; */
            $seasonalId=$request['seasonal_id'];
            /* $price=$request['price'];
            $noOfDays=$request['no_of_days']; */

            $files = $this->getRequest()->getFiles();
            $validImageFiles=array('png','jpg','jpeg');

            if($seasonalName=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter season name'));

            }
            if($seasonalDescription=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter season description'));

            }
            /* if($price=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter price'));

            }
            if($discountPrice=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter discount price'));

            }
            if($startDate=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter start date'));

            }
            if($endDate=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please enter end date'));

            } */

            if($placeIds=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please select places'));
            }

            if($tourType=='')
            {
                return new JsonModel(array('success' => false, 'message' => 'please select tour type'));
            }

            $uploadFileDetails=array();
            if (isset($files['image_file']))
            {
                $attachment=$files['image_file'];
                $imagesPath = '';
                $pdf_to_image = "";
                $filename = $attachment['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";
                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                $filename = $filenameWithoutExt;
                $filePath = "data/images";
                @mkdir(getcwd() . "/public/" . $filePath, 0777, true);

                $filePath = $filePath . "/" . $filename . "." . $ext;

                if (!in_array(strtolower($ext), $validImageFiles))
                {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }

                /* if (move_uploaded_file($attachment['tmp_name'], getcwd() . "/public/" . $filePath)) {
                     $imagesPath = $filePath;
                     $localImagePath = getcwd() . "/public/" . $filePath;
                     chmod(getcwd() . "/public/" . $filePath, 0777);
                 }*/
                $this->pushFiles($filePath,$attachment['tmp_name'],$attachment['type']);

                /* $fp = fopen(getcwd() . "/public/" . $filePath, 'w');
                 $encodeContent=$aes->encrypt(file_get_contents($attachment['tmp_name']));
                 $encodeString=$encodeContent['password'];
                 $hash=$encodeContent['hash'];
                 fwrite($fp,$encodeString);
                 fclose($fp);*/
                $uploadFileDetails = array(
                    'file_path' => $filePath,
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_seasonal_files,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $ext,
                    'status' => 1,
                    'duration' => 0,
                    'hash' => '',
                    'file_language_type' => 0,
                    'file_name' => $attachment['name']
                );
            }

            /* if($startDate=="")
            {
                $startDate='0000-00-00';
                $endDate='0000-00-00';
                $tourType=0;
                $placeIds=0;
                $price=0;
                $discountPrice=0;
            } */

            $data = array(
                'seasonal_name'=>$seasonalName,
                /* 'start_date'=>$startDate,
                'end_date'=>$endDate, */
                'seasonal_description'=>$seasonalDescription,
                'seasonal_type'=>$tourType,
                'place_ids'=>$placeIds,
                /* 'price'=>$price,
                'discount_price'=>$discountPrice, */
                'status'=>1 /* ,
                'no_of_days'=>$noOfDays */
            );

            $saveResult=$this->seasonalSpecialsTable()->updateSeasonalSpecials($data,array('seasonal_special_id'=>$seasonalId));
            if($saveResult)
            {
                if(count($uploadFileDetails))
                {

                    $uploadFileDetails['seasonal_special_id']=$seasonalId;
                    $result= $this->tourismFilesTable()->addTourismFiles($uploadFileDetails);

                }

                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }
            return new JsonModel(array('success'=>false , 'message'=>'Something went wrong'));


        }
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $dataIdString = rtrim($paramId, "=");
        $dataIdString = base64_decode($dataIdString);
        $dataIdString = explode("=", $dataIdString);
        $dataId = array_key_exists(1, $dataIdString) ? $dataIdString[1] : 0;
        $details=$this->seasonalSpecialsTable()->seasonalSpecialDetails($dataId);

        $placesList=$this->tourismPlacesTable()->getTourismList(array('tour_type'=>$details['seasonal_type'],'limit'=>-1));

        return new ViewModel(array('placesList'=>$placesList,'seasonalDetails'=>$details,'fileUrl'=>$this->filesUrl()));
    }

    public function loadSeasonalSpecialsListAction()
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
                if(isset($filterData['seasonal_name']))
                {
                    if(isset($filterData['seasonal_name']['text']) && $filterData['seasonal_name']['text'])
                    {
                        $searchData['seasonal_name']=$filterData['seasonal_name']['text'];
                    }
                    if(isset($filterData['seasonal_name']['order']) && $filterData['seasonal_name']['order'])
                    {
                        $searchData['seasonal_name_order']=$filterData['seasonal_name']['order'];
                    }

                } if(isset($filterData['start_date']))
                {

                    if(isset($filterData['start_date']['text']) && $filterData['start_date']['text'])
                    {
                        $searchData['start_date']=$filterData['start_date']['text'];
                    }
                    if(isset($filterData['start_date']['order']) && $filterData['start_date']['order'])
                    {
                        $searchData['start_date_order']=$filterData['start_date']['order'];
                    }

                }
                if(isset($filterData['end_date']))
                {
                    if(isset($filterData['end_date']['text']) && $filterData['end_date']['text'])
                    {
                        $searchData['end_date']=$filterData['end_date']['text'];
                    }
                    if(isset($filterData['end_date']['order']) && $filterData['end_date']['order'])
                    {
                        $searchData['end_date_order']=$filterData['end_date']['order'];
                    }

                }
                if(isset($filterData['price']))
                {
                    if(isset($filterData['price']['text']) && $filterData['price']['text'])
                    {
                        $searchData['price']=$filterData['price']['text'];
                    }
                    if(isset($filterData['price']['order']) && $filterData['price']['order'])
                    {
                        $searchData['price_order']=$filterData['price']['order'];
                    }

                }
                if(isset($filterData['discount_price']))
                {
                    if(isset($filterData['discount_price']['text']) && $filterData['discount_price']['text'])
                    {
                        $searchData['discount_price']=$filterData['discount_price']['text'];
                    }
                    if(isset($filterData['discount_price']['order']) && $filterData['discount_price']['order'])
                    {
                        $searchData['discount_price_order']=$filterData['discount_price_name']['order'];
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
                $totalCount=$this->seasonalSpecialsTable()->getSeasonalSpecialsListCount($searchData);
                if(count($totalCount))
                {
                    $totalCount=count($totalCount);
                }
            }
            $seasonalSpecials=$this->seasonalSpecialsTable()->getSeasonalSpecialsList($searchData);
            $view = new ViewModel(array('seasonalSpecials'=>$seasonalSpecials, 'offset' => $offset,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function samplesAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $sampleAudioList=$this->tourismFilesTable()->getTourismAudioFiles();
        $totalCount=$this->tourismFilesTable()->getTourismAudioFilesCount();

        return new ViewModel(array('sampleAudioList'=>$sampleAudioList,'totalCount'=>count($totalCount),'fileUrl'=>$this->filesUrl()));
    }
     public function addSampleAudioAction()
     {
         if($this->getRequest()->isXmlHttpRequest()) {
             $aes=new Aes();
             //  $request = json_decode(file_get_contents("php://input"),true);
             $request = $this->getRequest()->getPost();
             //print_r($request);exit;
             $files = $this->getRequest()->getFiles();
             $validFiles = array('mp3', 'mp4', 'wav', 'mpeg', 'avi');
             $audioFiles = array('mp3', 'wav', 'mpeg');
             $totalCount = array();
             $fileName = $request['file_name'];
             $fileLanaguage = $request['lanaguage'];
             $uploadFile = $files['mediaFiles'];
             $imagesPath = '';
             $pdf_to_image = "";
             $filename = $uploadFile['name'];
             $fileExt = explode(".", $filename);
             $ext = end($fileExt) ? end($fileExt) : "";
             $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
             $filename = $filenameWithoutExt;
             $filePath = "data/attachments";
             @mkdir(getcwd() . "/public/" . $filePath, 0777, true);
             $filePath = $filePath . "/" . $filename.'.'.$ext;
             if (!in_array(strtolower($ext), $validFiles))
             {
                 return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
             }
             $duration = '00:00:00';

             $fileDuration = $this->getDuration($uploadFile['tmp_name']);
             if ($fileDuration)
             {
                 $duration = $fileDuration;
             }

             /* if(move_uploaded_file($uploadFile['tmp_name'], getcwd() . "/public/" . $filePath))
              {
                  $imagesPath = $filePath;
                  $localImagePath = getcwd() . "/public/" . $filePath;
                  chmod(getcwd() . "/public/" . $filePath, 0777);
              }*/
            // $fp = fopen(getcwd() . "/public/" . $filePath, 'w');
            /* $encodeContent = $aes->encrypt(file_get_contents($uploadFile['tmp_name']));
             $encodeString = $encodeContent['password'];
             $hash = $encodeContent['hash'];
             fwrite($fp, $encodeString);
             fclose($fp);*/

             $ext = strtolower($ext);
             $this->pushFiles($filePath,$uploadFile['tmp_name'],$uploadFile['type']);

             if (in_array($ext, $audioFiles)) {
                 $fileType = \Admin\Model\TourismFiles::file_extension_type_audio;
             } else {
                 $fileType = \Admin\Model\TourismFiles::file_extension_type_video;
             }
             /*  $uploadFileDetails[] = array(
                   'file_path' => $filePath,
                   'file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places,
                   'file_extension_type'=>$fileType,
                   'file_extension' => $ext,
                   'status'=>1,
                   'duration'=>$duration,
                   'hash'=>$hash,
                   'file_language_type' => $fileLanaguage,
                   'file_name' => $fileName
               );*/
             $uploadFileDetails = array(
                 'file_path' => $filePath,
                 'file_data_type' => \Admin\Model\TourismFiles::file_data_type_sample_files,
                 'file_data_id'=>0,
                 'file_extension_type' => $fileType,
                 'file_extension' => $ext,
                 'status' => 1,
                 'duration' => $duration,
                 'hash' => '',
                 'file_language_type' => $fileLanaguage,
                 'file_name' => $fileName,
                 'created_at'=>date("Y-m-d H:i:s"),
                 'updated_at'=>date("Y-m-d H:i:s")
             );
            $result= $this->tourismFilesTable()->addTourismFiles($uploadFileDetails);
            if($result)
            {
                return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
            }

             return new JsonModel(array('success'=>false , 'message'=>'Something went wrong'));

         }
         $languagesList = $this->languagesTable()->getLanguages();

         $totalCount=array();

         return new ViewModel(array("languages"=>$languagesList,'totalCount'=>count($totalCount)));
     }
    public function loadSamplesListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();

            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                if(isset($filterData['language']))
                {
                    if(isset($filterData['language']['text']))
                    {
                        $searchData['language']=$filterData['language']['text'];
                    }
                    if(isset($filterData['language']['order']) && $filterData['language']['order'])
                    {
                        $searchData['language_order']=$filterData['language']['order'];
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
                $countResult=$this->tourismFilesTable()->getTourismAudioFilesCount($searchData);
                if(count($countResult))
                {
                    $totalCount=count($countResult);
                }

            }
            $sampleAudioList=$this->tourismFilesTable()->getTourismAudioFiles($searchData);
            $view = new ViewModel(array('sampleAudioList' => $sampleAudioList, 'offset' => $offset,'totalCount'=>$totalCount,'type'=>$type,'fileUrl'=>$this->filesUrl()));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function placesListForSeasonalSpecialsAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
                $tourType=$request['tour_type'];
                $tourType=intval($tourType);
            $placesList=$this->tourismPlacesTable()->getTourismList(array('tour_type'=>$tourType,'limit'=>-1));
            return new JsonModel(array('success'=>true,'placesList'=>$placesList));
        }
    }

    public function placesListForPrivilageUserAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
                $request = $this->getRequest()->getPost();
                $tourType=$request['tour_type'];
                $tourType=intval($tourType);
                   if($tourType==\Admin\Model\TourismFiles::file_data_type_seasonal_files)
                   {
                       $placesList=$this->seasonalSpecialsTable()->seasonalList(array('tour_type'=>$tourType));
                   }else
                   {
                       $placesList=$this->placePriceTable()->getPlacesForPrivilageUser(array('tour_type'=>$tourType));
                   }

            return new JsonModel(array('success'=>true,'placesList'=>$placesList));
        }
    }

     public function privilageUserListAction()
     {
         $bookingsList=$this->bookingsTable()->bookingsListPrivilageUserList();

           $totalCount=$this->bookingsTable()->bookingsListPrivilageUserListCount();

         return new ViewModel(array('userList'=>$bookingsList,'totalCount'=>count($totalCount)));
     }
    public function deletePrivilageUserAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            $request = $this->getRequest()->getPost();
            $bookingId=$request['data_id'];
            $response=$this->bookingsTable()->updateBooking(array('status'=>0),array('booking_id'=>$bookingId));
            if($response)
            {
                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to Delete'));
            }
        }
    }
     public function loadPrivilageUserListAction()
     {
         if ($this->getRequest()->isXmlHttpRequest())
         {
             $request = $this->getRequest()->getPost();
             $searchData=array('limit'=>10,'offset'=>0);
             $searchData=array('limit'=>10,'offset'=>0);
             $type=$request['type'];
             if (isset($request['page_number']))
             {
                 $pageNumber = $request['page_number'];
                 $offset = ($pageNumber * 10 - 10);
                 $searchData['offset'] = $offset;
                 $searchData['limit'] = 10;
             }
             $filterData=$request['filter'];
             if($filterData)
             {
                 $filterData=json_decode($filterData,true);
                 if(isset($filterData['date_of_registration']))
                 {
                     if(isset($filterData['date_of_registration']['text']) && $filterData['date_of_registration']['text'])
                     {
                         $searchData['date_of_registration']=$filterData['date_of_registration']['text'];
                     }
                     if(isset($filterData['date_of_registration']['order']) && $filterData['date_of_registration']['order'])
                     {
                         $searchData['date_of_registration_order']=$filterData['date_of_registration']['order'];
                     }
                 }
                 if(isset($filterData['mobile']))
                 {
                     if(isset($filterData['mobile']['text']) && $filterData['mobile']['text'])
                     {
                         $searchData['mobile']=$filterData['mobile']['text'];
                     }
                     if(isset($filterData['mobile']['order']) && $filterData['mobile']['order'])
                     {
                         $searchData['mobile_order']=$filterData['mobile']['order'];
                     }
                 }

                 if(isset($filterData['tour_type']))
                 {
                     if(isset($filterData['tour_type']['text']) && $filterData['tour_type']['text'])
                     {
                         $searchData['tour_type']=$filterData['tour_type']['text'];
                     }
                 }
                 if(isset($filterData['activation_date']))
                 {
                     if(isset($filterData['activation_date']['text']) && $filterData['activation_date']['text'])
                     {
                         $searchData['activation_date']=$filterData['activation_date']['text'];
                     }
                     if(isset($filterData['activation_date']['order']) && $filterData['activation_date']['order'])
                     {
                         $searchData['activation_date_order']=$filterData['activation_date']['order'];
                     }
                 }
                if(isset($filterData['duartion']))
                  {
                 if(isset($filterData['duartion']['text']) && $filterData['duartion']['text'])
                 {
                     $searchData['duartion']=$filterData['duartion']['text'];
                 }
                 if(isset($filterData['duartion']['order']) && $filterData['duartion']['order'])
                 {
                     $searchData['duartion_order']=$filterData['duartion']['order'];
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

             $totalCount = 0;
             if ($type && $type == 'search') {
                 $totalCount = $this->bookingsTable()->bookingsListPrivilageUserListCount($searchData);
                 if (count($totalCount)) {
                     $totalCount = count($totalCount);
                 }
             }
             $bookingsList=$this->bookingsTable()->bookingsListPrivilageUserList($searchData);
             $view = new ViewModel(array('userList' => $bookingsList, 'offset' => $offset, 'totalCount' => $totalCount));
             $view->setTerminal(true);
             return $view;

         }
     }
    public function addPrivilageUserAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())

        {
            $request = $this->getRequest()->getPost();
            $tourType = $request['tour_type'];
            $mobileCountryCode = $request['country_code'];
            $placeIds = $request['place_ids'];
            $packageId = $request['package_id'];
            $mobile = $request['mobile'];
            $userType = \Admin\Model\User::Privilage_user;
            $tourDate=$request['activation_date'];
            $noOfDays=$request['duration_days'];
            $noOfUsers=1;
            $price = 0;
            $userId=0;
               $noOfDays=intval($noOfDays);
            $bookingEndDate=date('Y-m-d', strtotime($tourDate. ' + '.$noOfDays.'days'));
            $tourType = intval($tourType);
            $checkUser=$this->userTable()->getFields(array('mobile'=>$mobile,'mobile_country_code'=>$mobileCountryCode,'role'=>\Admin\Model\User::Individual_role),array('user_id','email'));
             $email='';
            $aes=new Aes();
            if ($checkUser)
            {
                $userId = $checkUser['user_id'];
                $userIds[] = $checkUser['user_id'];
               $email=$checkUser['email'];
            }else
            {
                $saveUser = $this->userTable()->newUser(array('mobile' => $mobile,'mobile_country_code'=>$mobileCountryCode, 'role' => \Admin\Model\User::Individual_role, 'status' => 1));
                if ($saveUser['success'])
                {
                    $userId = $saveUser['user_id'];
                    $userIds[] = $saveUser['user_id'];

                }else
                {
                    return new JsonModel(array("success" => false, "message" => "Something Went wrong"));
                }
            }

            $userIds=implode(',',$userIds);

            $saveData=array(
                'tour_type'=>$tourType,'user_type'=>$userType,'user_id'=>$userId,
                'status'=>\Admin\Model\Bookings::Status_Active,
                'booking_type'=>\Admin\Model\Bookings::booking_By_Admin);
                $bookingTourDetails=array(
                    'place_ids'=>$placeIds,
                    'tour_date'=>$tourDate,'expiry_date'=>$bookingEndDate,
                    'price'=>$price,'sponsered_users'=>$userIds,
                    'no_of_days'=>$noOfDays,'no_of_users'=>$noOfUsers
                );

                if($tourType==\Admin\Model\PlacePrices::tour_type_Seasonal_special)
                {
                    $bookingTourDetails['package_id']=$packageId;
                }
            $password = $this->random_password();
            $encodeContent = $aes->encrypt($password);
            $encryptPassword = $encodeContent['password'];
            $hash = $encodeContent['hash'];
            $passwordsData = array ( 'user_id' => $userId, 'hash' => $hash, 'password' => $encryptPassword,'status' => 1);
            $saveBooking = $this->bookingsTable()->addBooking($saveData);

            if($saveBooking['success'])
            {
                $bookingTourDetails['booking_id']=$saveBooking['id'];
                $this->bookingTourDetailsTable()->addBooking($bookingTourDetails);

                if(count($passwordsData))
                {
                    $passwordsData['booking_id'] = $saveBooking['id'];
                    $passwordResponse= $this->passwordTable()->addPassword($passwordsData);
                    $passwordId=$passwordResponse['id'];
                    $notificationDetails = array ('status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => 'Congrats,you have been sponsored for the ' . \Admin\Model\PlacePrices::tour_type[$tourType] . ' by admin. Your password is ' . $password.$passwordId);
                    $notificationDetails['notification_data_id'] = $saveBooking['id'];
                    $this->notificationTable()->saveData($notificationDetails);
                    $smsMessage= sprintf("Congrats! you have been sponsored as privileged users for %s on %s date. Tour will be available until %s date. your password is %s",\Admin\Model\PlacePrices::tour_type[$tourType],date("d-m-Y",strtotime($tourDate)),date("d-m-Y",strtotime($bookingEndDate)),$password.$passwordId);
                    $this->sendPasswordSms($mobileCountryCode.$mobile,array('text'=>$smsMessage));
                    if($email)
                    {
                        $bookingList=$this->bookingsTable()->bookingsDetailsEmail($saveBooking['id']);
                        $subject ="Privilege User ".\Admin\Model\PlacePrices::tour_type[$tourType] ." Booking Details ";
 
                        $this->sendPrivilageUserBookingDetails($email, $subject, 'mail-privileged-booking-details', $bookingList);
                    }
 
                }
              
               
                return  new JsonModel(array('success'=>true,'message'=>'Added Successfully','booking_id'=>$saveBooking['id']));
            }
            return new JsonModel(array("success"=>false,"message"=>"Something Went wrong"));
            // $this->sendBookingSms($mobileCountryCode.$mobile,array('text'=>$notificationDetails[$counter]['notification_text']));
        }
    }

    public function deleteSamplesAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            $request = $this->getRequest()->getPost();
            $fileID=$request['sample_id'];
            $response=$this->tourismFilesTable()->updatePlaceFiles(array('status'=>0),array('tourism_file_id'=>$fileID));
            if($response)
            {
                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));

            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to update'));
            }
        }
    }

    public function tourOperatorDetailsAction()
    {
        $paramId = $this->params()->fromRoute('id', '');

        if(!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $tourOperatorIdString = rtrim($paramId, "=");
        $tourOperatorIdString = base64_decode($tourOperatorIdString);
        $tourOperatorIdString = explode("=", $tourOperatorIdString);
        $tourOperatorId = array_key_exists(1, $tourOperatorIdString) ? $tourOperatorIdString[1] : 0;
        $tourOperatorDetails=$this->tourOperatorDetailsTable()->tourOperatorDetails($tourOperatorId);

        return new ViewModel(array('tourOperatorDetails'=>$tourOperatorDetails,'fileUrl'=>$this->filesUrl()));
    }

    public function editTourOperatorAction()
    {
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $tourOperatorIdString = rtrim($paramId, "=");
        $tourOperatorIdString = base64_decode($tourOperatorIdString);
        $tourOperatorIdString = explode("=", $tourOperatorIdString);
        $tourOperatorId = array_key_exists(1, $tourOperatorIdString) ? $tourOperatorIdString[1] : 0;
        $tourOperatorDetails=$this->tourOperatorDetailsTable()->tourOperatorDetails($tourOperatorId);
        /*echo '<pre>';
        print_r($tourOperatorDetails);
        exit;*/
        return new ViewModel(array('tourOperatorDetails'=>$tourOperatorDetails,'fileUrl'=>$this->filesUrl()));
    }
    public function updateStatusAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $tourOperatorId=$request['tour_operator_id'];
            $status=$request['status'];
               $updateStatus=$this->tourOperatorDetailsTable()->updateTourOperator(array('status'=>$status),array('tour_operator_id'=>$tourOperatorId));
            if($updateStatus)
            {

                return new JsonModel(array('success'=>true,"message"=>'updated successfully'));

            }else
            {

                return new JsonModel(array('success'=>false,"message"=>'something went worng try agian'));

            }
        }
    }
    public function tourOperatorUpdateAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $files = $this->getRequest()->getFiles();

            $tourOperatorId = $request['tour_operator_id'];
            if (!$tourOperatorId) {
                return new JsonModel(array('success' => false, "message" => 'something went worng try agian'));
            }
            $applyDiscount = $request['apply_discount'];
            $discountPercentage = $request['percentage'];
            $discountDays = $request['duration'];
            $companyName = $request['company_name'];
            $companyPanNumber = $request['company_pan_number'];
            $companyContactName = $request['company_contact_name'];
            $companyEmail = $request['company_email'];
            $companyMobileNumber = $request['company_mobile_number'];
            $coordinatorName = $request['coordinator_name'];
            $coordinatorMobile = $request['coordinator_mobile'];
            $coordinatorEmail = $request['coordinator_email'];
            $coordinatorName1 = $request['coordinator_name_1'];
            $coordinatorMobile1 = $request['coordinator_mobile_1'];
            $coordinatorEmail1 = $request['coordinator_email_1'];
            $companyState = $request['company_state'];
            $llpinRefNo = $request['llpin_ref_no'];
            $companyDesignation = $request['company_designation'];
            $companyRegistrationDate = $request['company_registration_date'];
            $companyWebSite = $request['company_url'];
            $coordinatorDesignation = $request['coordinator_designation'];
            $coordinatorDesignation1 = $request['coordinator_designation_1'];

            $updateData = array();
            if ($applyDiscount == 1) {
                if (!$discountPercentage) {
                    return new JsonModel(array('success' => false, "message" => 'something went worng try agian'));
                }
                if (!$discountDays) {
                    return new JsonModel(array('success' => false, "message" => 'something went worng try agian'));
                }
                $userId = $this->tourOperatorDetailsTable()->getField(array('tour_operator_id' => $tourOperatorId), 'user_id');
                $discountEndDate = date('Y-m-d', strtotime(date("y-m-d") . ' +  ' . $discountDays . ' days'));
                $notificationDetails = array('notification_data_id' => $tourOperatorId, 'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_DISCOUNT, 'notification_text' => 'Discount For You ' . $discountPercentage . '% Upto ' . $discountEndDate);
                $this->notificationTable()->saveData($notificationDetails);
                $updateData = array('apply_discount' => $applyDiscount, 'discount_percentage' => $discountPercentage, 'discount_days' => $discountDays, 'discount_end_date' => $discountEndDate);
            } else if ($applyDiscount == 0) {
                $updateData = array('apply_discount' => $applyDiscount);
            }
            $getTourOperatorUserId=$this->tourOperatorDetailsTable()->getField(array('tour_operator_id'=>$tourOperatorId),'user_id');

            $checkMobileNumber=$this->userTable()->checkMobileRegistred(array('mobile'=>$companyMobileNumber,'mobile_country_code'=>'91'));
            $userId='';

            $additionalDetails=array('llpin_ref_no'=>$llpinRefNo,'recognition_description'=>'','company_web_site'=>$companyWebSite,'designation'=>$companyDesignation);
            $additionalDetails=json_encode($additionalDetails);
            if(count($checkMobileNumber) && $checkMobileNumber[0]['user_id']!=$getTourOperatorUserId)
            {
                if($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role)
                {
                    return new JsonModel(array('success'=>false,'message'=>'mobile number for tour Operator already registered as a tour Coordinator'));
                }
                if($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_Operator_role)
                {
                    return new JsonModel(array('success'=>false,'message'=>'mobile number for tour Operator already registered as a tour Operator'));
                }
            }
              if($companyEmail==$coordinatorEmail)
              {
                  return new JsonModel(array('success' => false, 'message' => 'email would not be same for tour Operator and tour Coordinator 1'));
              }
            if($companyMobileNumber==$coordinatorMobile)
            {
                return new JsonModel(array('success' => false, 'message' => 'mobile number would not be same for tour Operator and tour Coordinator 1'));
            }
            $checkEmail=$this->userTable()->verfiyUser(array('email'=>$companyEmail));

            if(count($checkEmail) && $checkEmail[0]['user_id']!=$getTourOperatorUserId)
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

            $coordinatorUpdate=array('user_name'=>$coordinatorName,'email'=>$coordinatorEmail,'designation'=>$coordinatorDesignation);

            $coordinator1Update=array('user_name'=>$coordinatorName1,'email'=>$coordinatorEmail1,'designation'=>$coordinatorDesignation1);

            if(isset($files['company_registration']) && count($files['company_registration']))
            {
                $filename = $files['company_registration']['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";
                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                $filename = $filenameWithoutExt;
                $filePath = "data/attachment";
                @mkdir(getcwd() . "/public/" . $filePath, 0777, true);

                $filePath = $filePath . "/" . $filename . "." . $ext;
                $recognitionDocument=$filePath;
                /*if (!in_array(strtolower($ext), $validImageFiles)) {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }*/
                $this->pushFiles($filePath, $files['company_registration']['tmp_name'], $files['company_registration']['type']);
                $updateData['registration_certificate']=$recognitionDocument;

            }
            if(isset($files['coordinator_registration']) && count($files['coordinator_registration']))
            {
                $filename = $files['coordinator_registration']['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";
                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                $filename = $filenameWithoutExt;
                $filePath = "data/attachment";
                @mkdir(getcwd() . "/public/" . $filePath, 0777, true);

                $filePath = $filePath . "/" . $filename . "." . $ext;
                $recognitionDocument=$filePath;
                /*if (!in_array(strtolower($ext), $validImageFiles)) {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }*/
                $this->pushFiles($filePath, $files['coordinator_registration']['tmp_name'], $files['coordinator_registration']['type']);
                $coordinatorUpdate['coordinator_certificate']=$recognitionDocument;
                $coordinatorUpdate['certificate_date']=date("Y-m-d");
            }

            $recognitionDocument1="";

            if(isset($files['coordinator_registration_1']) && count($files['coordinator_registration_1']))
            {
                $filename = $files['coordinator_registration_1']['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";
                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                $filename = $filenameWithoutExt;
                $filePath = "data/attachment";
                @mkdir(getcwd() . "/public/" . $filePath, 0777, true);

                $filePath = $filePath . "/" . $filename . "." . $ext;
                $recognitionDocument1=$filePath;
                /*if (!in_array(strtolower($ext), $validImageFiles)) {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }*/

                $this->pushFiles($filePath, $files['coordinator_registration_1']['tmp_name'], $files['coordinator_registration_1']['type']);
                $coordinator1Update['coordinator_certificate']=$recognitionDocument1;
                $coordinator1Update['certificate_date']=date("Y-m-d");

            }
            $updateData['company_name']=$companyName;
            $updateData['email_id']=$companyEmail;
            $updateData['pan_number']=$companyPanNumber;
            $updateData['contact_person']=$companyContactName;
            $updateData['state']=$companyState;
            $updateData['registration_date']=$companyRegistrationDate;
            $updateData['additional_details']=$additionalDetails;


              //$updateUser=$this->userTable()->updateUser(array('mobile'=>$companyMobileNumber),array('user_id'=>$getTourOperatorUserId));

            $checkMobileNumber=$this->userTable()->checkMobileRegistred(array('mobile'=>$coordinatorMobile,'mobile_country_code'=>'91'));
            $userId='';
            /*  echo '<pre>';
              print_r($checkMobileNumber);
              exit;*/
            $checkEmail=$this->userTable()->verfiyUser(array('email'=>$coordinatorEmail));
            $getTourCoordinatorUserId=$this->tourCoordinatorDetailsTable()->getField(array('company_id'=>$tourOperatorId,'coordinator_order'=>1,'status'=>1),'user_id');
            $getTourCoordinator1UserId=$this->tourCoordinatorDetailsTable()->getField(array('company_id'=>$tourOperatorId,'coordinator_order'=>2,'status'=>1),'user_id');

            if(count($checkMobileNumber) && $checkMobileNumber[0]['user_id']!=$getTourCoordinatorUserId)
            {
                if($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role)
                {
                    return new JsonModel(array('success'=>false,'message'=>'mobile number for coordinator 1 already registered as a tour Coordinator'));
                }
                if($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_Operator_role)
                {
                    return new JsonModel(array('success'=>false,'message'=>'mobile number for coordinator 1 already registered as a tour Operator'));
                }
            }

            if(count($checkEmail)  && $checkEmail[0]['user_id']!=$getTourCoordinatorUserId)
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

            if($coordinatorMobile1 && $coordinatorEmail1) {
                             if ($getTourCoordinator1UserId) {

                                 if($companyEmail==$coordinatorEmail1)
                                 {
                                     return new JsonModel(array('success' => false, 'message' => 'email would not be same for tour Operator and tour Coordinator 1'));
                                 }
                                 if($coordinatorEmail1==$coordinatorEmail)
                                 {
                                     return new JsonModel(array('success' => false, 'message' => 'email would not be same for tour Coordinator 1 and tour Coordinator 2'));
                                 }


                                 if($companyMobileNumber==$coordinatorMobile)
                                 {
                                     return new JsonModel(array('success' => false, 'message' => 'mobile number would not be same for tour Operator and tour Coordinator 1'));
                                 }
                                 if($coordinatorMobile==$coordinatorMobile1)
                                 {
                                     return new JsonModel(array('success' => false, 'message' => 'mobile number would not be same for tour Coordinator 1 and tour Coordinator 2'));
                                 }
                                 $checkMobileNumber = $this->userTable()->checkMobileRegistred(array('mobile' => $coordinatorMobile1, 'mobile_country_code' => '91'));
                                 $userId = '';
                                 /*  echo '<pre>';
                                   print_r($checkMobileNumber);
                                   exit;*/
                                 $checkEmail = $this->userTable()->verfiyUser(array('email' => $coordinatorEmail1));

                                 if (count($checkMobileNumber) && $checkMobileNumber[0]['user_id'] != $getTourCoordinator1UserId) {
                                     if ($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role) {
                                         return new JsonModel(array('success' => false, 'message' => 'mobile number for coordinator 2 already registered as a tour Coordinator'));
                                     }
                                     if ($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_Operator_role) {
                                         return new JsonModel(array('success' => false, 'message' => 'mobile number for coordinator 2 already registered as a tour Operator'));
                                     }
                                 }
                                 if (count($checkEmail) && $checkEmail[0]['user_id'] != $getTourCoordinatorUserId) {
                                     if ($checkEmail[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role) {
                                         return new JsonModel(array('success' => false, 'message' => 'email already registered as a tour Coordinator'));
                                     }
                                     if ($checkEmail[0]['company_role'] == \Admin\Model\User::Tour_Operator_role) {
                                         return new JsonModel(array('success' => false, 'message' => 'email already registered as a tour Operator'));
                                     }
                                 }
                                 $updateCoordinator1 = $this->tourCoordinatorDetailsTable()->updateCoordinator($coordinator1Update, array('company_id' => $tourOperatorId, 'coordinator_order' => 2));
                             } else {

                                 if($companyEmail==$coordinatorEmail)
                                 {
                                     return new JsonModel(array('success' => false, 'message' => 'email would not be same for tour Operator and tour Coordinator 1'));
                                 }
                                 if($coordinatorEmail1==$coordinatorEmail)
                                 {
                                     return new JsonModel(array('success' => false, 'message' => 'email would not be same for tour Coordinator 1 and tour Coordinator 2'));
                                 }

                                 $checkEmail = $this->userTable()->verfiyUser(array('email' => $coordinatorEmail1));

                                 $checkMobileNumber = $this->userTable()->checkMobileRegistred(array('mobile' => $coordinatorMobile1, 'mobile_country_code' => '91'));
                                 $userId = '';
                                 /*  echo '<pre>';
                                   print_r($checkMobileNumber);
                                   exit;*/

                                 if (count($checkMobileNumber)) {
                                     if ($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role) {
                                         return new JsonModel(array('success' => false, 'message' => 'mobile number for coordinator 2 already registered as a tour Coordinator'));
                                     }
                                     if ($checkMobileNumber[0]['company_role'] == \Admin\Model\User::Tour_Operator_role) {
                                         return new JsonModel(array('success' => false, 'message' => 'mobile number for coordinator 2 already registered as a tour Operator'));
                                     }
                                 }
                                 if (count($checkEmail)) {
                                     if ($checkEmail[0]['company_role'] == \Admin\Model\User::Tour_coordinator_role) {
                                         return new JsonModel(array('success' => false, 'message' => 'email for coordinator 2 already registered as a tour Coordinator'));
                                     }
                                     if ($checkEmail[0]['company_role'] == \Admin\Model\User::Tour_Operator_role) {
                                         return new JsonModel(array('success' => false, 'message' => 'email for coordinator 2 already registered as a tour Operator'));
                                     }
                                 }
                                 $saveCoordinatorUser = array(
                                     'mobile' => $coordinatorMobile1,
                                     'mobile_country_code' => '91',
                                     'role' => \Admin\Model\User::Tour_coordinator_role,
                                     'company_role' => \Admin\Model\User::Tour_coordinator_role
                                 );

                                 $saveCoordinator = array('designation' => '', 'coordinator_order' => 2,
                                     'user_name' => $coordinatorName1, 'email' => $coordinatorEmail1,
                                     'coordinator_certificate' => $recognitionDocument1,
                                     'certificate_date' => date("Y-m-d"),
                                     'status' => 1, 'company_id' => $tourOperatorId);

                                 $saveUser = $this->userTable()->newUser($saveCoordinatorUser);

                                 $aes = new Aes();
                                 $password = $this->random_password();
                                 $encodeContent = $aes->encrypt($password);
                                 $encryptPassword = $encodeContent['password'];
                                 $hash = $encodeContent['hash'];
                                 $passwordsData = array(
                                     'user_id' => $saveUser['user_id'],
                                     'password_type' => \Admin\Model\Password::passowrd_type_login,
                                     'booking_id' => 0,
                                     'hash' => $hash,
                                     'password' => $encryptPassword, 'status' => 1, 'created_at' => date("Y-m-d H:i:s"),
                                     'updated_at' => date("Y-m-d H:i:s")
                                 );
                                 $savePassword = $this->passwordTable()->addPassword($passwordsData);
                                 $saveCoordinator['user_id'] = $saveUser['user_id'];
                                 $saveCoordinatorDetails = $this->tourCoordinatorDetailsTable()->addTourCoordinator($saveCoordinator);
                                 $this->sendPasswordSms('91' . $coordinatorMobile1, array('text' => 'Your Password For Account is ' . $password));
                             }
                         }
            $updateCoordinator=$this->tourCoordinatorDetailsTable()->updateCoordinator($coordinatorUpdate,array('company_id'=>$tourOperatorId,'coordinator_order'=>1));

            $updateStatus = $this->tourOperatorDetailsTable()->updateTourOperator($updateData, array('tour_operator_id' => $tourOperatorId));


            if ($updateStatus) {

                return new JsonModel(array('success' => true, "message" => 'updated successfully'));

            } else {

                return new JsonModel(array('success' => false, "message" => 'something went worng try agian'));

            }
        }
    }
    public function applyDiscountAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            if(!$this->getLoggedInUser())
            {
                return new JsonModel(array('success'=>false,"message"=>'something went worng try agian'));
            }
            $request = $this->getRequest()->getPost();
            $tourOperatorId=$request['tour_operator_id'];
            if(!$tourOperatorId)
            {
                return new JsonModel(array('success'=>false,"message"=>'something went worng try agian'));
            }
            $applyDiscount = $request['apply_discount'];
            $discountPercentage = $request['percentage'];
            $discountDays = $request['duration'];
            $updateData=array();
            if($applyDiscount==1)
            {
                if(!$discountPercentage)
                {
                    return new JsonModel(array('success'=>false,"message"=>'something went worng try agian'));
                }
                if(!$discountDays)
                {
                    return new JsonModel(array('success'=>false,"message"=>'something went worng try agian'));
                }
                $userId=$this->tourOperatorDetailsTable()->getField(array('tour_operator_id'=>$tourOperatorId),'user_id');
                $discountEndDate=date('Y-m-d', strtotime(date("y-m-d") . ' +  '.$discountDays.' days'));
                $notificationDetails = array('notification_data_id'=>$tourOperatorId,'status' =>\Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_DISCOUNT, 'notification_text' => 'Discount For You '.$discountPercentage .'% Upto '.$discountEndDate);
                $this->notificationTable()->saveData($notificationDetails);
                $updateData=array('apply_discount'=>$applyDiscount,'discount_percentage'=>$discountPercentage,'discount_days'=>$discountDays,'discount_end_date'=>$discountEndDate);
            }else if($applyDiscount==0)
            {
                $updateData=array('apply_discount'=>$applyDiscount);
            }
            $updateStatus=$this->tourOperatorDetailsTable()->updateTourOperator($updateData,array('tour_operator_id'=>$tourOperatorId));
            if($updateStatus)
            {

                return new JsonModel(array('success'=>true,"message"=>'updated successfully'));

            }else
            {

                return new JsonModel(array('success'=>false,"message"=>'something went worng try agian'));

            }
        }
    }
    public function inboxAction()
    {
           $inboxList=$this->inboxTable()->getInboxMessages(array('limit'=>10,'offset'=>0));
           $inboxListCount=$this->inboxTable()->getInboxMessagesCount();
            /* echo '<pre>';
            print_r($inboxList);
            exit;*/
        $inboxListCount=isset($inboxListCount['inboxCount'])?$inboxListCount['inboxCount']:0;
        return new ViewModel(array('inboxList'=>$inboxList,'totalCount'=>$inboxListCount));
    }
    public function loadInboxListAction()
    {

        if ($this->getRequest()->isXmlHttpRequest()) {

            $request = $this->getRequest()->getPost();

            $searchData=array('limit'=>10,'offset'=>0);

            $type=$request['type'];

            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                if(isset($filterData['user_name']))
                {
                    if(isset($filterData['user_name']['text']))
                    {
                        $searchData['user_name']=$filterData['user_name']['text'];
                    }
                    if(isset($filterData['user_name']['order']) && $filterData['user_name']['order'])
                    {
                        $searchData['user_name_order']=$filterData['user_name']['order'];
                    }
                }
                if(isset($filterData['received_date']))
                {
                    if(isset($filterData['received_date']['text']))
                    {
                        $searchData['received_date']=$filterData['received_date']['text'];
                    }
                    if(isset($filterData['received_date']['order']) && $filterData['received_date']['order'])
                    {
                        $searchData['received_date_order']=$filterData['received_date']['order'];
                    }
                }
                if(isset($filterData['message']))
                {
                    if(isset($filterData['message']['text']))
                    {
                        $searchData['message']=$filterData['message']['text'];
                    }
                    if(isset($filterData['message']['order']) && $filterData['message']['order'])
                    {
                        $searchData['message_order']=$filterData['message']['order'];
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


            $totalCount = 0;
            if ($type && $type == 'search') {
                $totalCount = $this->inboxTable()->getInboxMessagesCount($searchData);
                if (count($totalCount)) {
                    $totalCount = $totalCount['inboxCount'];
                }
            }
            $inboxList = $this->inboxTable()->getInboxMessages($searchData);
            $view = new ViewModel(array('inboxList' => $inboxList, 'offset' => $offset,'totalCount'=>$totalCount,'type'=>$type));
            $view->setTerminal(true);
            return $view;
        }
    }
     public function sendMessageAction()
     {
         if ($this->getRequest()->isXmlHttpRequest())
         {

             $request = $this->getRequest()->getPost();
             $inboxId=$request['inbox_id'];
             $message=$this->safeString($request['message']);
            $updateResponse=$this->inboxTable()->updateInbox(array('reply'=>$message),array('inbox_id'=>$inboxId));
             if($updateResponse)
             {
                 $inboxDetails=$this->inboxTable()->getInboxMessageDetails(array('inbox_id'=>$inboxId));
                 $this->sendreply($inboxDetails['email'],'Message Reply','reply-message',$inboxDetails);
                 return new JsonModel(array('success'=>true,'message'=>'Email Sent Successfully'));

             }else{
                 return new JsonModel(array('success'=>false,'message'=>'Something went worng again sometime'));

             }
         }
         return $this->redirect()->toUrl($this->getBaseUrl());

     }
    public function inboxSingleAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {

            $request = $this->getRequest()->getPost();
            $inboxId=$request['inbox_id'];
            $inboxDetails=$this->inboxTable()->getInboxMessageDetails(array('inbox_id'=>$inboxId));
            $view= new ViewModel(array('inboxDetails'=>$inboxDetails));
            $view->setTerminal(true);
            return $view;
        }
        return $this->redirect()->toUrl($this->getBaseUrl());

    }
    
    public function financialStatementsAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
    }

    public function loadFinancialStatementsAction(){
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            if(!$this->getLoggedInUserId())
            {
                  return new JsonModel(array('success'=>false,'message'=>'Please Login To Change Password'));
            }
            //$data=array('limit'=>10,'offset'=>0);
            if($request['start_date'])
                $data['start_date']=$request['start_date'];
            if($request['end_date'])
                $data['end_date']=$request['end_date'];
            if($request['optType'])
                $data['optType']=$request['optType'];
            $financialStatement=$this->paymentTable()->getFinancialStatements($data);
            //$this->exportToExcel($financialStatement, $data['start_date'] . "_" . $data['end_date']);
            $view= new ViewModel(array('financialStatement'=>$financialStatement));
            $view->setTerminal(true);
            return $view;
        }
        else
        {
            if(!$this->getLoggedInUserId())
            {
                return $this->redirect()->toUrl($this->getBaseUrl());
            }
        }
    }
    public function exportToExcel($fs, $pd) {
        $filename ="export.xls" ;//'Financial_Statement_' . $pd . '.xls';
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filePath\"");
        
        $isPrintHeader = false;
        foreach ($fs as $row) {
            if (! $isPrintHeader) {
                echo implode("\t", array_keys($row)) . "\n";
                $isPrintHeader = true;
            }
            echo implode("\t", array_values($row)) . "\n";
        }
    }

    public function paymentsSingleAction()
    {
    }
    public function changePasswordAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            if(!$this->getLoggedInUserId())
            {
                  return new JsonModel(array('success'=>false,'message'=>'Please Login To Change Password'));
            }
            $userId=$this->getLoggedInUserId();
            $currentPassword=$request['current_password'];
            $newPassword=$request['new_password'];
            $checkPassword=$this->passwordTable()->checkPasswordWithUserId($userId,$currentPassword);
              if(count($checkPassword))
              {
                  $aes=new Aes();
                  $encodeContent = $aes->encrypt($newPassword);
                  $encodeString = $encodeContent['password'];
                  $hash = $encodeContent['hash'];
                  $currentPasswordInsert=$this->passwordTable()->addPassword(array('user_id'=>$userId,'password_type'=>0,'booking_id'=>0,'password'=>$encodeString,'hash'=>$hash,"status"=>1));

                  if($currentPasswordInsert['success'])
                   {

                       $currentPasswordUpdate=$this->passwordTable()->updatePassword(array('status'=>0),array('id'=>$checkPassword['id']));
                       return new JsonModel(array('success'=>false,'message'=>'Updated successfully'));

                   }else{
                       return new JsonModel(array('success'=>false,'message'=>'Something went worng again sometime'));

                   }

              }else{
                  return new JsonModel(array('success'=>false,'message'=>'current password does not match'));

              }
        }
    }
    public function paymentsAction()
    {
          $payments=$this->paymentTable()->getPayments(array('limit'=>10,'offset'=>0));
          $paymentsCount=$this->paymentTable()->getPaymentCount();
          //print_r($paymentsCount); exit;
          //$paymentsCount=isset($paymentsCount['count'])?$paymentsCount['count']:0;

          return new ViewModel(array('paymentList'=>$payments,'totalCount'=>$paymentsCount));
    }
    public function loadPaymentListAction()
    {

        if ($this->getRequest()->isXmlHttpRequest()) {

            $request = $this->getRequest()->getPost();

            $searchData=array('limit'=>10,'offset'=>0);

            $type=$request['type'];

            $offset=0;

            $filterData=$request['filter'];

            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                if(isset($filterData['booking_id']))
                {
                    if(isset($filterData['booking_id']['text']))
                    {
                        $searchData['booking_id']=$filterData['booking_id']['text'];
                    }
                    if(isset($filterData['booking_id']['order']) && $filterData['booking_id']['order'])
                    {
                        $searchData['booking_id_order']=$filterData['booking_id']['order'];
                    }
                }
                if(isset($filterData['transaction_id']))
                {
                    if(isset($filterData['transaction_id']['text']))
                    {
                        $searchData['transaction_id']=$filterData['transaction_id']['text'];
                    }
                    if(isset($filterData['transaction_id']['order']) && $filterData['transaction_id']['order'])
                    {
                        $searchData['transaction_id_order']=$filterData['transaction_id']['order'];
                    }
                }
                if(isset($filterData['transaction_date']))
                {
                    if(isset($filterData['transaction_date']['text']))
                    {
                        $searchData['transaction_date']=$filterData['transaction_date']['text'];
                    }
                    if(isset($filterData['transaction_date']['order']) && $filterData['transaction_date']['order'])
                    {
                        $searchData['transaction_date_order']=$filterData['transaction_date']['order'];
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
            $totalCount = 0;
            /* if ($type && $type == 'search') {
                $totalCount = $this->paymentTable()->getPaymentCount($searchData);
                if (count($totalCount)) {
                    $totalCount = count($totalCount);
                }
            } */
            
            $paymentList = $this->paymentTable()->getPayments($searchData);
            $totalCount = $this->paymentTable()->getPaymentCount($searchData);
            $view = new ViewModel(array('paymentList' => $paymentList, 'offset' => $offset,'totalCount'=>$totalCount,'type'=>$type));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function bannersAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $files = $this->getRequest()->getFiles();

            $validImageFiles = array('png', 'jpg', 'jpeg');
            $uploadFileDetails=array();
            $aes=new Aes();

            if (isset($files['image']))
            {
                $attachment=$files['image'];
                $imagesPath = '';
                $pdf_to_image = "";
                $filename = $attachment['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";
                $ext=strtolower($ext);
                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                $filename = $filenameWithoutExt . "." . $ext;
                $filePath = "data/images";
                //@mkdir(getcwd() . "/public/" . "tmp/".$filePath, 0777, true);

                $filePath = $filePath . "/" . $filename;

                if (!in_array(strtolower($ext), $validImageFiles))
                {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }
                $flagImagePath=$filePath;
                $uploadStatus=$this->pushFiles($filePath,$attachment['tmp_name'],$attachment['type']);
                if(!$uploadStatus)
                {
                    return new JsonModel(array('success'=>false,"messsage"=>'something went wrong try agian'));
                }
                $uploadFileDetails = array('image_path'=>$filePath,'status'=>1);

            }else{
                return new JsonModel(array('success'=>false,'message'=>'Please Upload Image'));

            }
            if($uploadFileDetails)
            {
                $saveResponse=$this->bannersTable()->addBanners($uploadFileDetails);
                if($saveResponse['success'])
                {
                    return new JsonModel(array('success'=>true,"message"=>'uploaded successfully'));

                }else{
                    return new JsonModel(array('success'=>false,"message"=>'something went wrong try agian'));

                }
            }
        }
        $banners=$this->bannersTable()->getBanners();

        return new ViewModel(array('banners'=>$banners,'imageUrl'=>$this->filesUrl()));
    }
   public function deleteBannerAction()
   {
       if ($this->getRequest()->isXmlHttpRequest())
       {
           $request = $this->getRequest()->getPost();

             $bannerId=$request['id'];
             if($bannerId=="" || $bannerId==null)
             {
                 return new JsonModel(array('success'=>false,"message"=>'something went wrong try agian'));
             }
           $updateResponse=$this->bannersTable()->updateBanner(array('status'=>0),array('banner_id'=>$bannerId));
           if($updateResponse)
           {
               return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));

           }else{
               return new JsonModel(array('success'=>false,"message"=>'something went wrong try agian'));

           }
       }

   }
    function search($array, $key, $value)
    {
        $results = array();

        if (is_array($array))
        {
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, $this->search($subarray, $key, $value));
        }

        return $results;
    }

    //** upcoming start */
    public function upcomingListAction()
    {
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upcomingList=$this->upcomingTable()->getUpcomingList();
        $totalCount=$this->upcomingTable()->getUpcomingCount();
        $languagesList=$this->languagesTable()->getLanguages();
        return new ViewModel(array('upcomingList'=>$upcomingList,'languagesList'=>$languagesList,'totalCount'=>count($totalCount)));
    }
    public function loadUpcomingListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $searchData=array('limit'=>10,'offset'=>0);
            $type=$request['type'];
            $offset=0;
            $filterData=$request['filter'];
            if($filterData)
            {
                $filterData=json_decode($filterData,true);
                if(isset($filterData['tour_type']) && $filterData['tour_type']['text'])
                {
                    if(isset($filterData['tour_type']['text']))
                    {
                        $searchData['tour_type']=$filterData['tour_type']['text'];
                    }
                }
                if(isset($filterData['month_year']))
                {
                    if(isset($filterData['month_year']['text']) && $filterData['month_year']['text'])
                    {
                        $searchData['month_year']=$filterData['month_year']['text'];
                    }
                    if(isset($filterData['month_year']['order']) && $filterData['month_year']['order'])
                    {
                        $searchData['month_year_order']=$filterData['month_year']['order'];
                    }
                }
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
                    if(isset($filterData['city']['text']))
                    {
                        $searchData['city']=$filterData['city']['text'];
                    }
                    if(isset($filterData['city']['order']) && $filterData['city']['order'])
                    {
                        $searchData['city_order']=$filterData['city']['order'];
                    }
                }

                if(isset($filterData['place']))
                {
                    if(isset($filterData['place']['text']))
                    {
                        $searchData['place']=$filterData['place']['text'];
                    }
                    if(isset($filterData['place']['order']) && $filterData['place']['order'])
                    {
                        $searchData['place_order']=$filterData['place']['order'];
                    }
                }

                if(isset($filterData['languages']))
                {
                    if(isset($filterData['languages']['text']))
                    {
                        $searchData['languages']=$filterData['languages']['text'];
                    }
                    if(isset($filterData['languages']['order']) && $filterData['languages']['order'])
                    {
                        $searchData['languages_order']=$filterData['languages']['order'];
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
                $countResult=$this->upcomingTable()->getUpcomingCount($searchData);
                if(count($countResult))
                {
                    $totalCount=count($countResult);
                }
            }
            $upcomingList = $this->upcomingTable()->getUpcomingList($searchData);
            $languagesList=$this->languagesTable()->getLanguages();
            $view = new ViewModel(array('upcomingList' => $upcomingList, 'languagesList'=>$languagesList, 'offset' => $offset,'type'=>$type,'totalCount'=>$totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }
    public function addUpcomingAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            //  $request = json_decode(file_get_contents("php://input"),true);
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $countryName=$request['country_name'];
            $stateName=$request['state_name'];
            $cityName=$request['city_name'];
            $placeName=$request['place_name'];
            $languages=$request['languages'];
            $tourType=$request['tour_type'];
            $month=$request['month'];
            $year=$request['year'];
           
            $data=array(
                'month_year'=> $month ."-". $year,
                'tour_type'=>$tourType,
                'country'=>$countryName,
                'state'=>$stateName,
                'city'=>$cityName,
                'place'=>$placeName,
                'languages'=>$languages,
                'status'=>1);

            $response=$this->upcomingTable()->addUpcoming($data);
            if($response['success']){
                $upcomingId=$response['id'];
                return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to add upcoming details'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $languagesList=$this->languagesTable()->getLanguages();
        $view = new ViewModel(array('languagesList'=>$languagesList));
        return $view;
    }

    public function addPromoterAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {            
            $request = $this->getRequest()->getPost();
            $action = $request['action'];
            if($action == "find"){
                $mobile = $request['mobile'];
                $user = $this->userTable()->getFields(array("mobile"=>$mobile, "role"=>\Admin\Model\User::Sponsor_role), array("user_id", "user_name")); 
                // "is_promoter"=>\Admin\Model\User::Is_Promoter,
                if($user){
                    return new JsonModel(array('success'=>true , 'id'=>$user['user_id'], 'name'=>$user['user_name']));
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'Sponsor not found'));
                }
            }
            elseif($action == "add"){
                $mobile = $request['mobile'];
                $response = $this->userTable()->updateUser(array("is_promoter"=>\Admin\Model\User::Is_Promoter), array("mobile"=>$mobile, "mobile_country_code"=>'91'));
                $userId = $this->userTable()->getField(array("mobile"=>$mobile), "user_id");
                if($response){
                    $nTitle = "Welcome as Promoter";
                    $message="Congratulations! You are Promoter now. Please logout and login into the app to acces your Promoter profile";
                    $notificationDetails = array('notification_data_id'=>$userId ,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                    $registrationIds = $this->fcmTable()->getDeviceIds($userId);
                    $notification = $this->notificationTable()->saveData($notificationDetails);
                    if ($registrationIds)
                    {
                        $notification = new SendFcmNotification();
                        $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => $nTitle, 'id' => $notificationDetails['notification_data_id'], 'type' => $notificationDetails['notification_type']));
                    }
                    
                    return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'unable to add promoter'));
                }
            }
            
            //$response=$this->upcomingTable()->addUpcoming($data);
            $response['success'] = 1;
            if($response['success']){
                return new JsonModel(array('success'=>true , 'message'=>'added successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to add upcoming details'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $languagesList=$this->languagesTable()->getLanguages();
        $view = new ViewModel(array('languagesList'=>$languagesList));
        return $view;
    }

    public function editUpcomingAction()
    {
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId)
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upIdString = rtrim($paramId, "=");
        $upIdString = base64_decode($upIdString);
        $upIdString = explode("=", $upIdString);
        $upcomingId = array_key_exists(1, $upIdString) ? $upIdString[1] : 0;

        if($this->getRequest()->isXmlHttpRequest())
        {
            $upcomingId = $paramId;
            //  $request = json_decode(file_get_contents("php://input"),true);
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
           ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $countryName=$request['country_name'];
            $stateName=$request['state_name'];
            $cityName=$request['city_name'];
            $placeName=$request['place_name'];
            $languages=$request['languages'];
            $tourType=$request['tour_type'];
            $month=$request['month'];
            $year=$request['year'];
           
            $data=array(
                'month_year'=> $month ."-". $year,
                'tour_type'=>$tourType,
                'country'=>$countryName,
                'state'=>$stateName,
                'city'=>$cityName,
                'place'=>$placeName,
                'languages'=>$languages);

            $response=$this->upcomingTable()->updateUpcoming($data,array('id'=>$upcomingId));
            if($response){
                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $upcomingDetails = $this->upcomingTable()->getUpcomingDetails($upcomingId);
        $languagesList=$this->languagesTable()->getLanguages();
        $view = new ViewModel(array('upcomingDetails' => $upcomingDetails, 'languagesList'=>$languagesList));
        return $view;
    }

    public function deleteUpcomingAction()
    {        
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $request = $this->getRequest()->getPost();
            $upcomingId=$request['upcoming_id'];
            $data=array('status'=>0);
            $response=$this->upcomingTable()->updateUpcoming($data,array('id'=>$upcomingId));
            if($response)
            {
                return new JsonModel(array('success'=>true,"message"=>'Deleted successfully'));
            }else
            {
                return new JsonModel(array('success'=>false,"message"=>'unable to delete'));
            }
        }
    }
    //** upcoming end */

    public function editSscAction()
    {        
        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            //print_r($request);exit;
            $ssc=$request['ssc'];
            $data=array('ssc'=>$ssc);
            $response=$this->sscTable()->setSSC($data,array('id'=>1));
            if($response){
                return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
            }else{
                return new JsonModel(array('success'=>false,'message'=>'unable to update'));
            }
        }
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $ssc['ssc'] = $this->sscTable()->getSSC();
        $subscribers = $this->userTable()->getAllSubscribers();
        $ssc['count'] = count($subscribers);
        $view = new ViewModel(array('ssc'=>$ssc['ssc'], 'count'=>$ssc['count']));
        return $view;
    }    

    public function addLanguageAction()
    {   
        if(!$this->getLoggedInUserId())
        {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        if($this->getRequest()->isXmlHttpRequest())
        {
            set_time_limit(0);
            ini_set('mysql.connect_timeout','0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            $lang=$request['lng'];
            if($lang != ""){
                $langID=array('id'=>(int)$lang);
                $data=array('language_type'=>2);
                $response=$this->languagesTable()->activateLanguage($data, $langID);
                if($response){
                    return new JsonModel(array('success'=>true , 'message'=>'updated successfully'));
                }else{
                    return new JsonModel(array('success'=>false,'message'=>'unable to update'));
                }
            }
        }else{
            $acldata = $this->languagesTable()->getLanguages();
            $alldata = $this->languagesTable()->getInactiveLanguages();
            $view = new ViewModel(array('acldata'=>$acldata, 'alldata'=>$alldata));
            //$view->setTerminal(true);
            return $view;
        }
    }

    public function renewalReminderMailAction()
    {
        $renewalUsers = $this->userTable()->getRenewalUsers();
        //print_r($renewalUsers); exit;
        $subject = "STT subscription renewal reminder";
        if(count($renewalUsers) > 100){
            $ruChunks=array_chunk($renewalUsers,100);
            foreach($ruChunks as $ruChunk){
                foreach($ruChunk as $user){
                    //email notification
                    $email = $user['email'];
                    $this->mailSTTUserNoAttachment($email, $subject, 'renew-remainder', $user);
                    // app notification
                    $message = "Your subscription period is going to expire by " . date("d/m/Y", strtotime($user['subscription_end_date'])) . ". Please renew.";
                    $notificationDetails = array('notification_data_id'=>$user['user_id'] ,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $user['user_id'], 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                    $registrationIds = $this->fcmTable()->getDeviceIds($user['user_id']);
                    $notification=   $this->notificationTable()->saveData($notificationDetails);

                    if($registrationIds)
                    {
                        $notification = new SendFcmNotification();
                        $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => "Renewal reminder", 'id' => $notificationDetails['notification_data_id'], 'type' => $notificationDetails['notification_type']));
                    }
                }
                //echo "mailed 1000";
            }
        }else{
            foreach($renewalUsers as $user){
                //email notification
                $email = $user['email'];
                $this->mailSTTUserNoAttachment($email, $subject, 'renew-remainder', $user);
                // app notification
                $message = "Your subscription period is going to expire by " . date("d/m/Y", strtotime($user['subscription_end_date'])) . ". Please renew.";
                $notificationDetails = array('notification_data_id'=>$user['user_id'] ,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $user['user_id'], 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                $registrationIds = $this->fcmTable()->getDeviceIds($user['user_id']);
                $notification=   $this->notificationTable()->saveData($notificationDetails);

                if($registrationIds)
                {
                    $notification = new SendFcmNotification();
                    $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => "Renewal reminder", 'id' => $notificationDetails['notification_data_id'], 'type' => $notificationDetails['notification_type']));
                }
            }
        }
        /* for($x = 0; $x <= count($renewalUsers)/100 - 1; $x++){
            $usersSlice = array_slice($renewalUsers,($x*100), 100);
            $counter = 1;
            foreach($usersSlice as $user){
                $email = $user['email'];
                $this->mailSTTUserNoAttachment($email, $subject, 'renew-remainder', $user);
                echo $counter . '. Mailed succesfully <br>';  
                $counter++;          
            }
            echo "******************* Mailed 100 *******************" . "<br>"; 
        }*/
//echo "mailed all";
        exit;
        //return true;  
    }

    public function mailLogsAction(){
        $subject = "STT http logs as on " . date("d-m-Y");
        $email = "susrilogs@gmail.com";
        $mY = date("m-Y");
        $fullPath = "/var/www/html/public/beta/logs/httplogs/httpRequests-$mY.log";
        $mailed = $this->emailSTTLogFiles($email, $subject, 'logs', array('type'=>'http'), $fullPath);        
        /* if($mailed)
            echo "http logs mailed - $mailed";
        else
            echo "unknown error"; */
        exit;
    }

    public function dbBackupAction(){
        $subject = "STT DB backup as on " . date("d-m-Y");
        $email = "susrilogs@gmail.com";
        $dmY = date("d-m-Y");
        $fullPath = "/var/www/html/logs/dblogs/sttdb_$dmY.sql";
        $mailed = $this->emailSTTLogFiles($email, $subject, 'logs', array('type'=>'db'), $fullPath);
                
        /* if($mailed)
            echo "db logs mailed - $mailed";
        else
            echo "unknown error"; */
        exit;
    }

    public function addRmmCronAction()
       {
            $cron=new CronJob();
            /* $jobs = $cron->getJobs();
            foreach($jobs as $job){
                $cron->removeJob($job);
            } */
            //$activeCronPath=$this->getBaseUrl().'/admin/admin/renewalReminderMail';

            $activeCronPath='/var/www/html/public/test/public/admin/admin/renewalReminderMail';
            $cron=new CronJob();
            $cronSchedule = '0 15 * * *';   // cron job to run everyday at 8am

            //$cron->addJob($cronSchedule . " /usr/bin/curl -o temp.txt " . $activeCronPath. ' > /var/log/cron.log 2>&1 ');
            //$cron->addJob($cronSchedule . " /usr/bin/php " . $activeCronPath);
            // echo 'success';
            print_r($cron->getJobs()); 
            exit;
       }

}