<?php

namespace Admin\Controller;

use Application\Controller\BaseController;
use Application\Handler\Aes;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;


class AdminController extends BaseController
{
    public function indexAction()
    {
        echo "admin index action";
        exit();
        //$baseUrl = $this->url()->fromRoute();
        // Check if a variable is already set in the session
        if (isset($this->sessionContainer->user)) {
            $username = $this->sessionContainer->user;
            $message = "Welcome back, $username!";
        } else {
            // Set a variable in the session
            $this->sessionContainer->user = 'JohnDoe';
            $message = 'Welcome, new user!';
        }

        return new ViewModel([
            'message' => $message,
        ]);
    }

    // Languages Admin - START
    public function languagesAction()
    {
        if ($this->authService->hasIdentity()) {
            $identity = $this->authService->getIdentity();
            $searchData = ['limit' => 10, 'offset' => 0];
            $languageList = $this->languageTable->getActiveLanguagesList($searchData);
            $totalCount = $this->languageTable->getActiveLanguagesCount();
            return new ViewModel(['languageList' => $languageList, 'totalCount' => $totalCount]);
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function loadLanguageListAction()
    {
        if ($this->authService->hasIdentity()) {
            if ($this->getRequest()->isXmlHttpRequest()) {
                $request = $this->getRequest()->getPost();
                $searchData = array('limit' => 10, 'offset' => 0);
                $type = $request['type'];
                $offset = 0;
                $filterData = $request['filter'];
                if ($filterData) {
                    $filterData = json_decode($filterData, true);
                    if (isset($filterData['language'])) {
                        if (isset($filterData['language']['text'])) {
                            $searchData['language'] = $filterData['language']['text'];
                        }
                        if (isset($filterData['language']['order']) && $filterData['language']['order']) {
                            $searchData['language_order'] = $filterData['language']['order'];
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
                    $countResult = $this->languageTable->getActiveLanguagesCount($searchData);
                    if (count($countResult)) {
                        $totalCount = count($countResult);
                    }
                }
                $languageList = $this->languageTable->getActiveLanguagesList($searchData);
                $totalCount = $this->languageTable->getActiveLanguagesCount();
                $view = new ViewModel(['languageList' => $languageList, 'totalCount' => $totalCount, 'offset' => $offset, 'type' => $type]);
                $view->setTerminal(true);
                return $view;
            }
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function addLanguageAction()
    {
        if ($this->authService->hasIdentity()) {
            $identity = $this->authService->getIdentity();
            if ($this->getRequest()->isXmlHttpRequest()) {
                $request = $this->getRequest()->getPost();
                $idStr = $request['id'];
                $idString = base64_decode($idStr);
                $idString = explode("=", $idString);
                $ID = array_key_exists(1, $idString) ? $idString[1] : 0;

                if ($ID != 0) {
                    $langsactivated = $this->languageTable->updateLanguage(['display' => 1], ['id' => $ID]);
                    if ($langsactivated == 1)
                        return new JsonModel(array("success" => true, "message" => "Updated succesfully"));
                    else
                        return new JsonModel(array("failure" => true, "message" => "Something went wrong"));
                }
            }
            $languageList = $this->languageTable->getInactiveLanguagesList();
            return new ViewModel(['languageList' => $languageList]);
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function deleteLanguageAction()
    {
        if ($this->authService->hasIdentity()) {
            $identity = $this->authService->getIdentity();
            if ($this->getRequest()->isXmlHttpRequest()) {
                $request = $this->getRequest()->getPost();
                $idStr = $request['id'];
                $idString = base64_decode($idStr);
                $idString = explode("=", $idString);
                $ID = array_key_exists(1, $idString) ? $idString[1] : 0;

                if ($ID != 0) {
                    $langsactivated = $this->languageTable->updateLanguage(['display' => 0], ['id' => $ID]);
                    if ($langsactivated == 1)
                        return new JsonModel(array("success" => true, "message" => "Updated succesfully"));
                    else
                        return new JsonModel(array("failure" => true, "message" => "Something went wrong"));
                }
            }
            $languageList = $this->languageTable->getInactiveLanguagesList();
            return new ViewModel(['languageList' => $languageList]);
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    // Languages Admin - END

    // Countries Admin - START
    public function countriesAction()
    {
        if ($this->authService->hasIdentity()) {
            $countryList = $this->countriesTable->getCountries4Admin(['limit' => 10, 'offset' => 0]);
            $totalCount = $this->countriesTable->getCountries4Admin(['limit' => 0, 'offset' => 0], 1);
            return new ViewModel(['countryList' => $countryList, 'totalCount' => $totalCount]);
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function loadCountryListAction()
    {
        if ($this->authService->hasIdentity()) {
            if ($this->getRequest()->isXmlHttpRequest()) {
                $request = $this->getRequest()->getPost();
                $searchData = array('limit' => 10, 'offset' => 0);
                $type = $request['type'];
                $offset = 0;
                $filterData = $request['filter'];
                if ($filterData) {
                    $filterData = json_decode($filterData, true);
                    if (isset($filterData['country'])) {
                        if (isset($filterData['country']['text']) && !empty($filterData['country']['text'])) {
                            $searchData['country'] = $filterData['country']['text'];
                        }
                        if (isset($filterData['country']['order']) && $filterData['country']['order']) {
                            $searchData['country_order'] = $filterData['country']['order'];
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
                    $totalCount = $this->countriesTable->getCountries4Admin(['limit' => 0, 'offset' => 0], 1);
                }
                $countryList = $this->countriesTable->getCountries4Admin($searchData);
                $view = new ViewModel(['countryList' => $countryList, 'totalCount' => $totalCount, 'offset' => $offset, 'type' => $type]);
                $view->setTerminal(true);
                return $view;
            }
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function addCountryAction()
    {
        if ($this->authService->hasIdentity()) {
            $identity = $this->authService->getIdentity();
            if ($this->getRequest()->isXmlHttpRequest()) {
                set_time_limit(0);
                ini_set('mysql.connect_timeout', '0');
                ini_set('max_execution_time', '0');
                ini_set("memory_limit", "-1");

                $request = $this->getRequest()->getPost();
                $countryName = $request['country_name'];
                $fileDetails = $request['file_details'];
                $fileDetails = json_decode($fileDetails, true);
                $fileIds = explode(",", $request['file_Ids']);
                $uploadFileDetails = array();
                $countryName = trim($countryName);
                if ($countryName == '')
                    return new JsonModel(array("success" => false, "message" => "Please enter country name"));

                $checkCountry = $this->countriesTable->getFields(['country_name' => $countryName, 'display' => 1], ['id']);
                if ($checkCountry)
                    return new JsonModel(array("success" => false, "message" => "Country Already exists"));

                $getFiles = $this->temporaryFiles->getFiles($fileIds);
                $uploadFiles = array();
                foreach ($getFiles['images'] as $images) {
                    $uploadFileDetails[] = array(
                        'file_path' => $images['file_path'],
                        'file_data_type' => \Admin\Model\TourismFiles::file_data_type_country,
                        'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                        'file_extension' => $images['file_extension'],
                        'display' => 1,
                        'duration' => 0,
                        'file_language_id' => 0,
                        'hash' => '',
                        'file_name' => $images['file_name']
                    );
                    if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                        $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                    }
                }
                $copyFiles = $this->copypushFiles($uploadFiles);
                if (count($copyFiles['copied'])) {
                    $this->temporaryFiles->updateCopiedFiles($copyFiles['copied']);
                }
                if (!$copyFiles['status'])
                    return new JsonModel(array('success' => false, 'message' => 'unable to add country'));

                $countryId = "";
                $getCountryId = $this->countriesTable->getField(array('country_name' => $countryName, 'display' => 0), 'id');
                $data = ['country_name' => $countryName, 'display' => 1];
                if ($getCountryId) {
                    $countryId = $getCountryId;
                    $update = $this->countriesTable->updateCountry($data, array('id' => $countryId));
                    if (!$update) {
                        return new JsonModel(array('success' => false, 'message' => 'unable to add country'));
                    }
                } else {
                    $response = $this->countriesTable->addCountry($data);
                    if ($response['success']) {
                        $countryId = $response['id'];
                    } else {
                        return new JsonModel(array('success' => false, 'message' => 'unable to add country'));
                    }
                }
                $counter = -1;
                if (count($uploadFileDetails)) {
                    foreach ($uploadFileDetails as $details) {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $countryId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable->addMutipleTourismFiles($uploadFileDetails);
                }
                return new JsonModel(array('success' => true, 'message' => 'added successfully'));
            }
            $countryList = $this->countriesTable->getCountries(['display' => 0]);
            $languagesList = $this->languageTable->getActiveLanguagesList(['limit' => 0, 'offset' => 0]);
            return new ViewModel(['countries' => $countryList, 'languages' => $languagesList]);
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function fileUploadRowAction()
    {
        if ($this->authService->hasIdentity()) {
            $request = $this->getRequest()->getPost();
            $languagesList = $this->languageTable->getActiveLanguagesList(['limit' => 0, 'offset' => 0]);
            $rowNumber = $request['row_number'];
            $rowCount = $request['rows_count'];
            $view = new ViewModel(array("languages" => $languagesList, 'rowNumber' => $rowNumber, 'numberOfrows' => $rowCount));
            $view->setTerminal(true);
            return $view;
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function editCountryAction()
    {
        $this->checkAdmin();

        if ($this->getRequest()->isXmlHttpRequest()) {
            set_time_limit(0);
            ini_set('mysql.connect_timeout', '0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            $countryName = $request['country_name'];
            $countryId = $request['country_id'];
            $fileDetails = $request['file_details'];
            $fileDetails = json_decode($fileDetails, true);
            $uploadFileDetails = array();
            $deleteFiles = json_decode($request['deleted_images'], true);
            $countryName = trim($countryName);
            if ($countryName == '') {
                return new JsonModel(array("success" => false, "message" => "Please enter country name"));
            }
            $checkCountry = $this->countriesTable->getFields(array('country_name' => $countryName, 'display' => 1), array('id'));
            if ($checkCountry && $checkCountry['id'] != $countryId) {
                return new JsonModel(array("success" => false, "message" => "Country not found"));
            }
            $flagImagePath = '';
            $fileIds = explode(",", $request['file_Ids']);
            $getFiles = $this->temporaryFiles->getFiles($fileIds);
            $uploadFiles = array();
            if (count($getFiles)) {
                foreach ($getFiles['images'] as $images) {
                    $uploadFileDetails[] = array(
                        'file_path' => $images['file_path'],
                        'file_data_type' => \Admin\Model\TourismFiles::file_data_type_country,
                        'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                        'file_extension' => $images['file_extension'],
                        'display' => 1,
                        'duration' => 0,
                        'file_language_id' => 0,
                        'hash' => '',
                        'file_name' => $images['file_name']
                    );
                    if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                        $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                    }
                }
                $copyFiles = $this->copypushFiles($uploadFiles);

                if (count($copyFiles['copied'])) {
                    $this->temporaryFiles->updateCopiedFiles($copyFiles['copied']);
                }
                if (!$copyFiles['status']) {
                    return new JsonModel(array('success' => false, 'message' => 'unable to update state'));
                }
            }

            $data = array(
                'country_name' => $countryName,
                'display' => 1
            );
            if ($flagImagePath) {
                $data['flag_image'] = $flagImagePath;
            }
            $update = $this->countriesTable->updateCountry($data, array('id' => $countryId));
            if (!$update) {
                return new JsonModel(array('success' => false, 'message' => 'unable to update country'));
            }

            $counter = -1;
            if (count($uploadFileDetails)) {
                foreach ($uploadFileDetails as $details) {
                    $counter++;
                    $uploadFileDetails[$counter]['file_data_id'] = $countryId;
                    $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                    $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                }
                $this->tourismFilesTable->addMutipleTourismFiles($uploadFileDetails);
            }

            if (count($deleteFiles)) {
                $this->tourismFilesTable->deletePlaceFiles($deleteFiles);
            }
            return new JsonModel(array('success' => true, 'message' => 'updated successfully'));
        }

        $countriesList = $this->countriesTable->getCountries(['display' => 1]);
        $languagesList = $this->languageTable->getActiveLanguagesList(['limit' => 0, 'offset' => 0]);
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
        $countryIdString = rtrim($paramId, "=");
        $countryIdString = base64_decode($countryIdString);
        $countryIdString = explode("=", $countryIdString);
        $countryId = array_key_exists(1, $countryIdString) ? $countryIdString[1] : 0;
        $countryDetails = $this->countriesTable->getCountriesDetails($countryId);
        $imageFiles = array();
        $imageCounter = -1;
        $countryInfo = array();
        foreach ($countryDetails as $country) {
            $countryInfo['country_id'] = $country['country_id'];
            $countryInfo['country_name'] = $country['country_name'];
            if ($country['file_extension_type'] == 1) {
                $imageCounter++;
                $imageFiles[$imageCounter]['file_path'] = $country['file_path'];
                $imageFiles[$imageCounter]['tourism_file_id'] = $country['tourism_file_id'];
                $imageFiles[$imageCounter]['file_name'] = $country['file_name'];
            }
        }
        return new ViewModel(array('countries' => $countriesList, 'imageUrl' => $this->filesUrl(), 'country_id' => $countryId, 'languages' => $languagesList, 'countryDetails' => $countryInfo, 'imageFiles' => $imageFiles));
    }

    public function deleteCountryAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $countryId = $request['id'];
            $response = $this->countriesTable->updateCountry(array('display' => 0), array('id' => $countryId));
            if ($response) {
                $countryName = $this->countriesTable->getField(array('id' => $countryId), 'country_name');
                if (strtolower($countryName) == 'india') {
                    $stateUpdate = $this->statesTable->updateState(array('display' => 0), array('country_id' => $countryId, 'display' => 1));
                }
                $cityUpdate = $this->citiesTable->updateCity(array('display' => 0), array('country_id' => $countryId, 'display' => 1));
                $placeUpdate = $this->placesTable->updatePlace(array('display' => 0), array('country_id' => $countryId, 'display' => 1));
                $toursUpdate = $this->tourTalesTable->updateTourTale(array('display' => 0), array('country_id' => $countryId, 'display' => 1));
                $response = $this->tourismFilesTable->updatePlaceFiles(array('display' => 0), array('file_data_id' => $countryId, 'file_data_type' => \Admin\Model\TourismFiles::file_data_type_country));
                return new JsonModel(array('success' => true, "message" => 'Deleted successfully'));
            } else {
                return new JsonModel(array('success' => false, "message" => 'unable to delete'));
            }
        }
    }

    // Countries Admin - END

    // Cities - START
    public function citiesAction()
    {
        if ($this->authService->hasIdentity()) {
            $cityList = $this->citiesTable->getCityList(['limit' => 10, 'offset' => 0]);
            $totalCount = $this->citiesTable->getCityList(['limit' => 0, 'offset' => 0], 1);
            return new ViewModel(['cityList' => $cityList, 'totalCount' => $totalCount]);
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function loadCityListAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $searchData = array('limit' => 10, 'offset' => 0);
            $type = $request['type'];
            $offset = 0;
            $filterData = $request['filter'];
            if ($filterData) {
                $filterData = json_decode($filterData, true);

                if (isset($filterData['country'])) {
                    if (isset($filterData['country']['text']) && $filterData['country']['text']) {
                        $searchData['country'] = $filterData['country']['text'];
                    }
                    if (isset($filterData['country']['order']) && $filterData['country']['order']) {
                        $searchData['country_order'] = $filterData['country']['order'];
                    }
                }

                if (isset($filterData['state'])) {
                    if (isset($filterData['state']['text']) && $filterData['state']['text']) {
                        $searchData['state'] = $filterData['state']['text'];
                    }
                    if (isset($filterData['state']['order']) && $filterData['state']['order']) {
                        $searchData['state_order'] = $filterData['state']['order'];
                    }
                }

                if (isset($filterData['city'])) {
                    if (isset($filterData['city']['text'])) {
                        $searchData['city'] = $filterData['city']['text'];
                    }
                    if (isset($filterData['city']['order']) && $filterData['city']['order']) {
                        $searchData['city_order'] = $filterData['city']['order'];
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
                $totalCount = $this->citiesTable->getCityList($searchData, 1);
            }
            $cityList = $this->citiesTable->getCityList($searchData);
            $view = new ViewModel(array('cityList' => $cityList, 'offset' => $offset, 'type' => $type, 'totalCount' => $totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function addCityAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            set_time_limit(0);
            ini_set('mysql.connect_timeout', '0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            $countryName = $request['country_name'];
            $stateName = $request['state_name'];
            $cityName = $request['city_name'];
            $fileDetails = $request['file_details'];
            $fileDetails = json_decode($fileDetails, true);
            $uploadFileDetails = array();

            $cityName = trim($cityName);
            if ($cityName == '') {
                return new JsonModel(array("success" => false, "message" => "Please enter city name"));
            }
            $check = $this->citiesTable->getField(array('city_name' => $cityName, 'display' => 1), 'id');
            if ($check) {
                return new JsonModel(array("success" => false, "message" => "City Already exists"));
            }
            $fileIds = explode(",", $request['file_Ids']);
            $getFiles = $this->temporaryFiles->getFiles($fileIds);
            $uploadFiles = array();
            foreach ($getFiles['images'] as $images) {
                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_city,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $images['file_extension'],
                    'display' => 1,
                    'duration' => 0,
                    'file_language_id' => 0,
                    'hash' => '',
                    'file_name' => $images['file_name']
                );
                if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                    $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                }
            }
            $copyFiles = $this->copypushFiles($uploadFiles);
            if (count($copyFiles['copied'])) {
                $this->temporaryFiles->updateCopiedFiles($copyFiles['copied']);
            }
            if (!$copyFiles['status']) {
                return new JsonModel(array('success' => false, 'message' => 'unable to add city'));
            }
            $cityId = "";
            if ($stateName == '') {
                $stateName = 0;
            }
            $data = array(
                'country_id' => $countryName,
                'state_id' => $stateName,
                'city_name' => $cityName,
                'display' => 1
            );
            $response = $this->citiesTable->addCity($data);
            if ($response['success']) {
                $cityId = $response['id'];
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to add city'));
            }
            $counter = -1;
            if (count($uploadFileDetails)) {
                foreach ($uploadFileDetails as $details) {
                    $counter++;
                    $uploadFileDetails[$counter]['file_data_id'] = $cityId;
                    $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                    $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                }
                $this->tourismFilesTable->addMutipleTourismFiles($uploadFileDetails);
            }
            return new JsonModel(array('success' => true, 'message' => 'added successfully'));
        }

        $countriesList = $this->countriesTable->getCountries();
        $languagesList = $this->languageTable->getActiveLanguagesList(['limit' => 0, 'offset' => 0]);
        return new ViewModel(array('countries' => $countriesList, "languages" => $languagesList));
    }

    public function editCityAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            set_time_limit(0);
            ini_set('mysql.connect_timeout', '0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            $countryName = $request['country_name'];
            $stateName = $request['state_name'];
            $cityId = $request['city_id'];
            $cityName = $request['city_name'];
            $fileDetails = $request['file_details'];
            $fileDetails = json_decode($fileDetails, true);
            $uploadFileDetails = array();
            $deleteFiles = json_decode($request['deleted_images'], true);
            $cityName = trim($cityName);
            if ($cityName == '') {
                return new JsonModel(array("success" => false, "message" => "Please enter city name"));
            }
            $check = $this->citiesTable->getField(array('city_name' => $cityName, 'display' => 1), 'id');
            if ($check && $cityId != $check) {
                return new JsonModel(array("success" => false, "message" => "City Already exists"));
            }
            $fileIds = explode(",", $request['file_Ids']);
            $getFiles = $this->temporaryFiles->getFiles($fileIds);
            $uploadFiles = array();
            if (count($getFiles)) {
                foreach ($getFiles['images'] as $images) {
                    $uploadFileDetails[] = array(
                        'file_path' => $images['file_path'],
                        'file_data_type' => \Admin\Model\TourismFiles::file_data_type_city,
                        'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                        'file_extension' => $images['file_extension'],
                        'display' => 1,
                        'duration' => 0,
                        'file_language_id' => 0,
                        'hash' => '',
                        'file_name' => $images['file_name']
                    );
                    if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                        $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                    }
                }
                $copyFiles = $this->copypushFiles($uploadFiles);

                if (count($copyFiles['copied'])) {
                    $this->temporaryFiles->updateCopiedFiles($copyFiles['copied']);
                }
                if (!$copyFiles['status']) {
                    return new JsonModel(array('success' => false, 'message' => 'unable to update city'));
                }
            }
            if ($stateName == '') {
                $stateName = 0;
            }
            $data = array(
                'country_id' => $countryName,
                'state_id' => $stateName,
                'city_name' => $cityName,
                'display' => 1
            );
            $response = $this->citiesTable->updateCity($data, array('id' => $cityId));
            if ($response) {
                $counter = -1;
                if (count($uploadFileDetails)) {
                    foreach ($uploadFileDetails as $details) {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $cityId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable->addMutipleTourismFiles($uploadFileDetails);
                }
                if (count($deleteFiles)) {
                    $this->tourismFilesTable->deletePlaceFiles($deleteFiles);
                }
                return new JsonModel(array('success' => true, 'message' => 'updated successfully'));
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to update city'));
            }
        }
        $countriesList = $this->countriesTable->getCountries();
        $languagesList = $this->languageTable->getActiveLanguagesList(['limit' => 0, 'offset' => 0]);
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $cityIdString = rtrim($paramId, "=");
        $cityIdString = base64_decode($cityIdString);
        $cityIdString = explode("=", $cityIdString);
        $cityId = array_key_exists(1, $cityIdString) ? $cityIdString[1] : 0;
        $cityDetails = $this->citiesTable->getCityDetails($cityId);
        $imageFiles = array();
        $imageCounter = -1;

        $cityInfo = array();
        foreach ($cityDetails as $city) {
            $cityInfo['country_id'] = $city['country_id'];
            $cityInfo['state_id'] = $city['state_id'];
            $cityInfo['city_id'] = $city['city_id'];
            $cityInfo['city_name'] = $city['city_name'];
            $cityInfo['country_name'] = strtolower($city['country_name']);
            if ($city['file_extension_type'] == 1) {
                $imageCounter++;
                $imageFiles[$imageCounter]['file_path'] = $city['file_path'];
                $imageFiles[$imageCounter]['file_language_id'] = $city['file_language_id'];
                $imageFiles[$imageCounter]['tourism_file_id'] = $city['tourism_file_id'];
                $imageFiles[$imageCounter]['file_name'] = $city['file_name'];
            }
        }
        $stateList = array();
        if ($cityInfo['country_name'] == 'india') {
            $stateList = $this->statesTable->getStates(array('display' => 1, 'country_id' => $cityInfo['country_id']));
        }
        return new ViewModel(array('countries' => $countriesList, "languages" => $languagesList, 'stateList' => $stateList, 'imageUrl' => $this->filesUrl(), 'cityDetails' => $cityInfo, 'imageFiles' => $imageFiles));
    }

    public function deleteCityAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $cityId = $request['id'];
            $response = $this->citiesTable->updateCity(array('display' => 0), array('id' => $cityId));
            if ($response) {
                $placeUpdate = $this->placesTable->updatePlace(array('display' => 0), array('city_id' => $cityId, 'display' => 1));
                $toursUpdate = $this->tourTalesTable->updateTourTale(array('display' => 0), array('city_id' => $cityId, 'display' => 1));
                $response = $this->tourismFilesTable->updatePlaceFiles(array('display' => 0), array('file_data_id' => $cityId, 'file_data_type' => \Admin\Model\TourismFiles::file_data_type_city));
                return new JsonModel(array('success' => true, "message" => 'Deleted successfully'));
            } else {
                return new JsonModel(array('success' => false, "message" => 'unable to update'));
            }
        }
    }

    public function getStatesAction()
    {
        $request = $this->getRequest()->getPost();
        $countryId = $request['country_id'];
        $where = array('country_id' => $countryId, 'display' => 1);
        $statesList = $this->statesTable->getStates($where);
        return new JsonModel(array('success' => true, 'states' => $statesList));
    }
    // Cities - END

    // States - END
    public function statesAction()
    {
        if ($this->authService->hasIdentity()) {
            $stateList = $this->statesTable->getStateList(['limit' => 10, 'offset' => 0]);
            $totalCount = $this->statesTable->getStateList(['limit' => 0, 'offset' => 0], 1);
            return new ViewModel(['stateList' => $stateList, 'totalCount' => $totalCount]);
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function loadStateListAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $searchData = array('limit' => 10, 'offset' => 0);
            $type = $request['type'];
            $offset = 0;
            $filterData = $request['filter'];
            if ($filterData) {
                $filterData = json_decode($filterData, true);
                if (isset($filterData['state'])) {
                    if (isset($filterData['state']['text'])) {
                        $searchData['state'] = $filterData['state']['text'];
                    }
                    if (isset($filterData['state']['order']) && $filterData['state']['order']) {
                        $searchData['state_order'] = $filterData['state']['order'];
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
                $totalCount = $this->statesTable->getStateList($searchData, 1);
            }
            $stateList = $this->statesTable->getStateList($searchData);
            $view = new ViewModel(array('stateList' => $stateList, 'offset' => $offset, 'type' => $type, 'totalCount' => $totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function addStateAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $stateName = $request['state_name'];
            $fileDetails = $request['file_details'];
            $fileDetails = json_decode($fileDetails, true);
            $uploadFileDetails = array();
            $countryName = $this->countriesTable->getField(array('country_name' => 'India', 'display' => 1), 'id');
            if ($countryName == '') {
                return new JsonModel(array("success" => false, "message" => "Please add India country to add state"));
            }

            $check = $this->statesTable->getField(array('state_name' => $stateName, 'display' => 1), 'id');
            if ($check) {
                return new JsonModel(array("success" => false, "message" => "State Already exists"));
            }
            $stateId = "";
            $fileIds = explode(",", $request['file_Ids']);
            $getFiles = $this->temporaryFiles->getFiles($fileIds);
            $uploadFiles = array();
            foreach ($getFiles['images'] as $images) {
                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_state,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $images['file_extension'],
                    'display' => 1,
                    'duration' => 0,
                    'file_language_id' => 0,
                    'hash' => '',
                    'file_name' => $images['file_name']
                );
                if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                    $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                }
            }
            $copyFiles = $this->copypushFiles($uploadFiles);
            if (count($copyFiles['copied'])) {
                $this->temporaryFiles->updateCopiedFiles($copyFiles['copied']);
            }
            if (!$copyFiles['status']) {
                return new JsonModel(array('success' => false, 'message' => 'unable to add state'));
            }
            $data = array(
                'country_id' => $countryName,
                'state_name' => $stateName,
                'display' => 1
            );
            $response = $this->statesTable->addState($data);
            if ($response['success']) {
                $stateId = $response['id'];
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to add state'));
            }
            $counter = -1;
            if (count($uploadFileDetails)) {
                foreach ($uploadFileDetails as $details) {
                    $counter++;
                    $uploadFileDetails[$counter]['file_data_id'] = $stateId;
                    $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                    $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                }
                $this->tourismFilesTable->addMutipleTourismFiles($uploadFileDetails);
            }
            return new JsonModel(array('success' => true, 'message' => 'added successfully'));
        }
        if (!$this->getLoggedInUserId()) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $countriesList = $this->countriesTable->getCountries();
        $languagesList = $this->languageTable->getActiveLanguagesList(['limit' => 0, 'offset' => 0]);
        return new ViewModel(array('countries' => $countriesList, "languages" => $languagesList));
    }

    public function editStateAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            set_time_limit(0);
            ini_set('mysql.connect_timeout', '0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            $stateName = $request['state_name'];
            $stateId = $request['state_id'];
            $fileDetails = $request['file_details'];
            $fileDetails = json_decode($fileDetails, true);
            $uploadFileDetails = array();
            $countryName = $this->countriesTable->getField(array('country_name' => 'India', 'display' => 1), 'id');
            if ($countryName == '') {
                return new JsonModel(array("success" => false, "message" => "Please add india country to add state"));
            }
            $deleteFiles = json_decode($request['deleted_images'], true);
            $check = $this->statesTable->getField(array('state_name' => $stateName, 'status' => 1), 'id');
            if ($check && $check != $stateId) {
                return new JsonModel(array("success" => false, "message" => "State Already exists"));
            }
            $fileIds = explode(",", $request['file_Ids']);
            $getFiles = $this->temporaryFiles->getFiles($fileIds);
            $uploadFiles = array();
            if (count($getFiles)) {
                foreach ($getFiles['images'] as $images) {
                    $uploadFileDetails[] = array(
                        'file_path' => $images['file_path'],
                        'file_data_type' => \Admin\Model\TourismFiles::file_data_type_state,
                        'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                        'file_extension' => $images['file_extension'],
                        'display' => 1,
                        'duration' => 0,
                        'file_language_id' => 0,
                        'hash' => '',
                        'file_name' => $images['file_name']
                    );
                    if ($images['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                        $uploadFiles[] = array('old_path' => $images['file_path'], 'new_path' => $images['file_path'], 'id' => $images['temporary_files_id']);
                    }
                }
                $copyFiles = $this->copypushFiles($uploadFiles);
                if (count($copyFiles['copied'])) {
                    $this->temporaryFiles->updateCopiedFiles($copyFiles['copied']);
                }
                if (!$copyFiles['status']) {

                    return new JsonModel(array('success' => false, 'message' => 'unable to update state'));
                }
            }
            $data = array(
                'country_id' => $countryName,
                'state_name' => $stateName,
                'display' => 1
            );
            $response = $this->statesTable->updateState($data, array('id' => $stateId));
            if ($response) {
                $counter = -1;
                if (count($uploadFileDetails)) {
                    foreach ($uploadFileDetails as $details) {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $stateId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable->addMutipleTourismFiles($uploadFileDetails);
                }
                if (count($deleteFiles)) {
                    $this->tourismFilesTable->deletePlaceFiles($deleteFiles);
                }
                return new JsonModel(array('success' => true, 'message' => 'updated successfully'));
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to add state'));
            }
        }
        $languagesList = $this->languageTable->getActiveLanguagesList(['limit' => 0, 'offset' => 0]);
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $stateIdString = rtrim($paramId, "=");
        $stateIdString = base64_decode($stateIdString);
        $stateIdString = explode("=", $stateIdString);
        $stateId = array_key_exists(1, $stateIdString) ? $stateIdString[1] : 0;
        $stateDetails = $this->statesTable->getStateDetails($stateId);
        $imageFiles = array();
        $imageCounter = -1;
        $stateInfo = array();
        foreach ($stateDetails as $state) {
            $stateInfo['state_id'] = $state['state_id'];
            $stateInfo['state_name'] = $state['state_name'];
            if ($state['file_extension_type'] == 1) {
                $imageCounter++;
                $imageFiles[$imageCounter]['file_path'] = $state['file_path'];
                $imageFiles[$imageCounter]['file_language_id'] = $state['file_language_id'];
                $imageFiles[$imageCounter]['tourism_file_id'] = $state['tourism_file_id'];
                $imageFiles[$imageCounter]['file_name'] = $state['file_name'];
            }
        }
        return new ViewModel(array('imageUrl' => $this->filesUrl(), "languages" => $languagesList, 'stateDetails' => $stateInfo, 'imageFiles' => $imageFiles));
    }

    public function deleteStateAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $stateId = $request['id'];
            $response = $this->statesTable->updateState(array('display' => 0), array('id' => $stateId));
            if ($response) {
                $cityUpdate = $this->citiesTable->updateCity(array('display' => 0), array('state_id' => $stateId, 'display' => 1));
                $placeUpdate = $this->placesTable->updatePlace(array('display' => 0), array('state_id' => $stateId, 'display' => 1));
                $toursUpdate = $this->tourTalesTable->updateTourTale(array('display' => 0), array('state_id' => $stateId, 'display' => 1));
                $response = $this->tourismFilesTable->updatePlaceFiles(array('display' => 0), array('file_data_id' => $stateId, 'file_data_type' => \Admin\Model\TourismFiles::file_data_type_state));
                return new JsonModel(array('success' => true, "message" => 'Deleted successfully'));
            } else {
                return new JsonModel(array('success' => false, "message" => 'unable to update'));
            }
        }
    }

    // States - END

    // Places - Start

    public function placesAction()
    {
        if ($this->authService->hasIdentity()) {
            $tourismList = $this->placesTable->getPlacesList4Admin(array('limit' => 10, 'offset' => 0));
            $totalCount = $this->placesTable->getPlacesList4Admin(array('limit' => 0, 'offset' => 0), 1);
            return new ViewModel(['tourismList' => $tourismList, 'totalCount' => $totalCount]);
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function loadPlacesListAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $searchData = array('limit' => 10, 'offset' => 0);
            $type = $request['type'];
            $offset = 0;
            $filterData = $request['filter'];
            if ($filterData) {
                $filterData = json_decode($filterData, true);
                if (isset($filterData['country'])) {
                    if (isset($filterData['country']['text']) && $filterData['country']['text']) {
                        $searchData['country'] = $filterData['country']['text'];
                    }
                    if (isset($filterData['country']['order']) && $filterData['country']['order']) {
                        $searchData['country_order'] = $filterData['country']['order'];
                    }
                }
                if (isset($filterData['state'])) {
                    if (isset($filterData['state']['text']) && $filterData['state']['text']) {
                        $searchData['state'] = $filterData['state']['text'];
                    }
                    if (isset($filterData['state']['order']) && $filterData['state']['order']) {
                        $searchData['state_order'] = $filterData['state']['order'];
                    }
                }
                if (isset($filterData['city'])) {

                    if (isset($filterData['city']['text']) && $filterData['city']['text']) {
                        $searchData['city'] = $filterData['city']['text'];
                    }
                    if (isset($filterData['city']['order']) && $filterData['city']['order']) {
                        $searchData['city_order'] = $filterData['city']['order'];
                    }
                }
                if (isset($filterData['place_name'])) {
                    if (isset($filterData['place_name']['text']) && $filterData['place_name']['text']) {
                        $searchData['place_name'] = $filterData['place_name']['text'];
                    }
                    if (isset($filterData['place_name']['order']) && $filterData['place_name']['order']) {
                        $searchData['place_name_order'] = $filterData['place_name']['order'];
                    }
                }
                if (isset($filterData['download'])) {
                    if (isset($filterData['download']['text']) && $filterData['download']['text']) {
                        $searchData['download'] = $filterData['download']['text'];
                    }
                    if (isset($filterData['download']['order']) && $filterData['download']['order']) {
                        $searchData['download_order'] = $filterData['download']['order'];
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
                $totalCount = $this->placesTable->getPlacesList4Admin($searchData, 1);
            }
            $tourismList = $this->placesTable->getPlacesList4Admin($searchData);
            $view = new ViewModel(array('tourismList' => $tourismList, 'offset' => $offset, 'type' => $type, 'totalCount' => $totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function addPlaceAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            set_time_limit(0);
            ini_set('mysql.connect_timeout', '0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $countryName = $request['country_id'];
            $stateName = $request['state_id'];
            $cityName = $request['city_id'];
            $placeName = $request['place_name'];
            $description = $request['description'];
            $fileDetails = $request['file_details'];
            $audioFiles = array('mp3', 'wav', 'mpeg');
            $fileDetails = json_decode($fileDetails, true);
            $uploadFileDetails = array();
            $placeName = trim($placeName);
            if ($placeName == '') {
                return new JsonModel(array("success" => false, "message" => "Please enter city name"));
            }
            $check = $this->placesTable->getField(array('place_name' => $placeName, 'display' => 1), 'id');
            if ($check) {
                return new JsonModel(array("success" => false, "message" => "Place Already exists"));
            }
            if ($stateName == '') {
                $stateName = 0;
            }
            $fileIds = explode(",", $request['file_Ids']);
            $getFiles = $this->temporaryFiles->getFiles($fileIds);
            $uploadFiles = array();
            foreach ($getFiles['images'] as $images) {
                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_places,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $images['file_extension'],
                    'display' => 1,
                    'duration' => 0,
                    'file_language_id' => 0,
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
                $filePath = $audioFiles[$fileDetail['file_id']]['file_path'];
                $ext = $audioFiles[$fileDetail['file_id']]['file_extension'];
                $duration = $audioFiles[$fileDetail['file_id']]['duration'];
                $hash = $audioFiles[$fileDetail['file_id']]['hash'];
                $fileType = $audioFiles[$fileDetail['file_id']]['file_extension_type'];
                if ($audioFiles[$fileDetail['file_id']]['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                    $uploadFiles[] = array(
                        'old_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'new_path' => $audioFiles[$fileDetail['file_id']]['file_path'],
                        'id' => $audioFiles[$fileDetail['file_id']]['temporary_files_id']
                    );
                }
                $uploadFileDetails[] = array(
                    'file_path' => $filePath,
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_places,
                    'file_extension_type' => $fileType,
                    'file_extension' => $ext,
                    'display' => 1,
                    'duration' => $duration,
                    'file_language_id' => $fileLanaguage,
                    'hash' => $hash,
                    'file_name' => $fileName
                );
            }
            $copyFiles = $this->copypushFiles($uploadFiles);
            if (count($copyFiles['copied'])) {
                $this->temporaryFiles->updateCopiedFiles($copyFiles['copied']);
            }
            if (!$copyFiles['status']) {
                return new JsonModel(array('success' => false, 'message' => 'unable to add Place'));
            }
            $placeId = "";
            $data = array(
                'country_id' => $countryName,
                'state_id' => $stateName,
                'city_id' => $cityName,
                'place_name' => $placeName,
                'place_description' => $description,
                'display' => 1
            );
            $response = $this->placesTable->addPlace($data);
            if ($response['success']) {
                $placeId = $response['id'];
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to add Place'));
            }
            $counter = -1;
            if (count($uploadFileDetails)) {
                foreach ($uploadFileDetails as $details) {
                    $counter++;
                    $uploadFileDetails[$counter]['file_data_id'] = $placeId;
                    $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                    $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                }
                $this->tourismFilesTable->addMutipleTourismFiles($uploadFileDetails);
            }
            return new JsonModel(array('success' => true, 'message' => 'added successfully'));
        }
        $countriesList = $this->countriesTable->getCountries();
        $languagesList = $this->languageTable->getActiveLanguagesList(['limit' => 0, 'offset' => 0]);
        return new ViewModel(array('countries' => $countriesList, "languages" => $languagesList));
    }
    public function editPlaceAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            set_time_limit(0);
            ini_set('mysql.connect_timeout', '0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $request = $this->getRequest()->getPost();
            $countryName = $request['country_id'];
            $stateName = $request['state_id'];
            $cityName = $request['city_id'];
            $placeId = $request['place_id'];
            $placeName = $request['place_name'];
            $description = $request['description'];
            $fileDetails = $request['file_details'];
            $audioFiles = array('mp3', 'wav', 'mpeg');
            $fileDetails = json_decode($fileDetails, true);
            $uploadFileDetails = array();
            $deletedImages = json_decode($request['deleted_images'], true);
            $deletedAudio = json_decode($request['deleted_audio'], true);
            $deleteFiles = array_merge($deletedAudio, $deletedImages);
            $placeName = trim($placeName);
            if ($placeName == '') {
                return new JsonModel(array("success" => false, "message" => "Please enter Place name"));
            }
            if ($stateName == '') {
                $stateName = 0;
            }
            $fileIds = explode(",", $request['file_Ids']);
            $getFiles = $this->temporaryFiles->getFiles($fileIds);
            $uploadFiles = array();
            foreach ($getFiles['images'] as $images) {
                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_places,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image,
                    'file_extension' => $images['file_extension'],
                    'display' => 1,
                    'duration' => 0,
                    'file_language_id' => 0,
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
                    $updateData = array('file_name' => $fileName, 'file_language_id' => $fileLanaguage);
                    if (isset($fileDetail['file_id']) && $fileDetail['file_id']) {
                        $filePath = $audioFiles[$fileDetail['file_id']]['file_path'];
                        $ext = $audioFiles[$fileDetail['file_id']]['file_extension'];
                        $duration = $audioFiles[$fileDetail['file_id']]['duration'];
                        $hash = $audioFiles[$fileDetail['file_id']]['hash'];
                        $fileType = $audioFiles[$fileDetail['file_id']]['file_extension_type'];
                        if ($audioFiles[$fileDetail['file_id']]['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                            $uploadUpdateFiles[] = array(
                                'old_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'new_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'id' => $audioFiles[$fileDetail['file_id']]['temporary_files_id']
                            );
                            $copyFiles = $this->copypushFiles($uploadUpdateFiles);
                            if (count($copyFiles['copied'])) {
                                $this->temporaryFiles->updateCopiedFiles($copyFiles['copied']);
                            }
                            if (!$copyFiles['status']) {
                                return new JsonModel(array('success' => false, 'message' => 'unable to update Place'));
                            }
                        }
                        $updateData['file_path'] = $filePath;
                        $updateData['file_extension_type'] = $fileType;
                        $updateData['file_extension'] = $ext;
                        $updateData['duration'] = $duration;
                        $updateData['hash'] = $hash;
                    }
                    $tourismFileId = $fileDetail['edit_id'];
                    $updateResponse = $this->tourismFilesTable->updatePlaceFiles($updateData, array('tourism_file_id' => $tourismFileId));
                } else {
                    $filePath = $audioFiles[$fileDetail['file_id']]['file_path'];
                    $ext = $audioFiles[$fileDetail['file_id']]['file_extension'];
                    $duration = $audioFiles[$fileDetail['file_id']]['duration'];
                    $hash = $audioFiles[$fileDetail['file_id']]['hash'];
                    $fileType = $audioFiles[$fileDetail['file_id']]['file_extension_type'];
                    if ($audioFiles[$fileDetail['file_id']]['status'] == \Admin\Model\TemporaryFiles::status_file_not_copied) {
                        $uploadFiles[] = array(
                            'old_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'new_path' => $audioFiles[$fileDetail['file_id']]['file_path'], 'id' => $audioFiles[$fileDetail['file_id']]['temporary_files_id']
                        );
                    }
                    $uploadFileDetails[] = array(
                        'file_path' => $filePath,
                        'file_data_type' => \Admin\Model\TourismFiles::file_data_type_places,
                        'file_extension_type' => $fileType,
                        'file_extension' => $ext,
                        'display' => 1,
                        'duration' => $duration,
                        'file_language_id' => $fileLanaguage,
                        'hash' => $hash,
                        'file_name' => $fileName
                    );
                }
            }
            $copyFiles = $this->copypushFiles($uploadFiles);
            if (count($copyFiles['copied'])) {
                $this->temporaryFiles->updateCopiedFiles($copyFiles['copied']);
            }
            if (!$copyFiles['status']) {
                return new JsonModel(array('success' => false, 'message' => 'unable to update Place'));
            }
            $data = array(
                'country_id' => $countryName,
                'state_id' => $stateName,
                'city_id' => $cityName,
                'place_name' => $placeName,
                'place_description' => $description,
                'display' => 1
            );
            $response = $this->placesTable->updatePlace($data, array('id' => $placeId));
            if ($response) {
                $counter = -1;
                if (count($uploadFileDetails)) {
                    foreach ($uploadFileDetails as $details) {
                        $counter++;
                        $uploadFileDetails[$counter]['file_data_id'] = $placeId;
                        $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                        $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                    }
                    $this->tourismFilesTable->addMutipleTourismFiles($uploadFileDetails);
                }
                if (count($deleteFiles)) {
                    $this->tourismFilesTable->deletePlaceFiles($deleteFiles);
                }
                return new JsonModel(array('success' => true, 'message' => 'updated successfully'));
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to update place'));
            }
        }

        $countriesList = $this->countriesTable->getCountries();
        $languagesList = $this->languageTable->getActiveLanguagesList(['limit' => 0, 'offset' => 0]);
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $placeIdString = rtrim($paramId, "=");
        $placeIdString = base64_decode($placeIdString);
        $placeIdString = explode("=", $placeIdString);
        $placeId = array_key_exists(1, $placeIdString) ? $placeIdString[1] : 0;
        $placeDetails = $this->placesTable->getPlaceDetails($placeId);
        $imageFiles = array();
        $audioFiles = array();
        $imageCounter = -1;
        $audioCounter = -1;
        $placeInfo = array();
        foreach ($placeDetails as $place) {
            $placeInfo['country_id'] = $place['country_id'];
            $placeInfo['state_id'] = $place['state_id'];
            $placeInfo['city_id'] = $place['city_id'];
            $placeInfo['place_id'] = $place['id'];
            $placeInfo['place_name'] = $place['place_name'];
            $placeInfo['place_description'] = $place['place_description'];
            $placeInfo['country_name'] = $place['country_name'];
            if ($place['file_extension_type'] != 1) {
                $audioCounter++;
                $audioFiles[$audioCounter]['file_path'] = $place['file_path'];
                $audioFiles[$audioCounter]['file_language_id'] = $place['file_language_id'];
                $audioFiles[$audioCounter]['tourism_file_id'] = $place['tourism_file_id'];
                $audioFiles[$audioCounter]['file_name'] = $place['file_name'];
            } else {
                $imageCounter++;
                $imageFiles[$imageCounter]['file_path'] = $place['file_path'];
                $imageFiles[$imageCounter]['file_language_id'] = $place['file_language_id'];
                $imageFiles[$imageCounter]['tourism_file_id'] = $place['tourism_file_id'];
                $imageFiles[$imageCounter]['file_name'] = $place['file_name'];
            }
        }
        $stateList = array();
        $citiesList = array();
        if (strtolower($placeInfo['country_name']) == 'india') {
            $stateList = $this->statesTable->getStates(array('display' => 1, 'country_id' => $placeInfo['country_id']));
            $citiesList = $this->citiesTable->getCities(array('state_id' => $placeInfo['state_id']));
        } else {
            $citiesList = $this->citiesTable->getCities(array('country_id' => $placeInfo['country_id']));
        }
        return new ViewModel(array('countries' => $countriesList, "languages" => $languagesList, 'stateList' => $stateList, 'cityList' => $citiesList, 'imageUrl' => $this->filesUrl(), 'placeDetails' => $placeInfo, 'audioFiles' => $audioFiles, 'imageFiles' => $imageFiles));
    }

    public function deletePlaceAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            //  $request = json_decode(file_get_contents("php://input"),true);
            $request = $this->getRequest()->getPost();
            $placeId = $request['id'];
            $response = $this->placesTable->updatePlace(array('display' => 0), array('id' => $placeId));
            if ($response) {
                $toursUpdate = $this->tourTalesTable->deleteTourTale($placeId);
                $response = $this->tourismFilesTable->updatePlaceFiles(array('display' => 0), array('file_data_id' => $placeId, 'file_data_type' => \Admin\Model\TourismFiles::file_data_type_places));
                return new JsonModel(array('success' => true, "message" => 'Deleted successfully'));
            } else {
                return new JsonModel(array('success' => false, "message" => 'unable to update'));
            }
        }
    }

    public function getCitiesAction()
    {
        $request = $this->getRequest()->getPost();
        $countryId = $request['country_id'];
        $stateId = $request['state_id'];
        if ($stateId) {
            $where = array('state_id' => $stateId);
        } else {
            $where = array("country_id" => $countryId);
        }
        $citiesList = $this->citiesTable->getCities($where);
        return new JsonModel(array('success' => true, 'cities' => $citiesList));
    }

    // Places - End

    // Upload files - start
    public function uploadFilesAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $files = $this->getRequest()->getFiles();

            $validFiles = array('mp3', 'mp4', 'wav', 'mpeg', 'avi');
            $audioFiles = array('mp3', 'wav', 'mpeg');
            $validImageFiles = array('png', 'jpg', 'jpeg');
            $uploadFileDetails = array();
            $aes = new Aes();

            if (isset($files['image_files'])) {
                $attachment = $files['image_files'];
                $filename = $attachment['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";
                $ext = strtolower($ext);
                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                $filename = $filenameWithoutExt . "." . $ext;
                $filePath = "data/images";
                //@mkdir(getcwd() . "/public/" . "tmp/".$filePath, 0777, true);

                $filePath = $filePath . "/" . $filename;

                if (!in_array(strtolower($ext), $validImageFiles)) {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }
                //$flagImagePath = $filePath;
                $uploadStatus = $this->pushFiles("tmp/" . $filePath, $attachment['tmp_name'], $attachment['type']);
                if (!$uploadStatus) {
                    return new JsonModel(array('success' => false, "messsage" => 'something went wrong try agian'));
                }
                $uploadFileDetails = array(
                    'file_path' => $filePath,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image, 'file_extension' => $ext, 'status' => \Admin\Model\TemporaryFiles::status_file_not_copied, 'duration' => 0, 'hash' => '', 'file_name' => $attachment['name']
                );
            }

            if (isset($files['attachments'])) {
                //return new JsonModel(array('success' => false, "messsage" => $files));
                $uploadFile = $files['attachments'];
                $filename = $uploadFile['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";
                $ext = strtolower($ext);
                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                $filename = $filenameWithoutExt;
                $filePath = "data/attachments";
                $filePath = $filePath . "/" . $filename;
                if (!in_array(strtolower($ext), $validFiles)) {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }
                $duration = '00:00:00';
                $fileDuration = $this->getDuration($uploadFile['tmp_name']);
                if ($fileDuration) {
                    $duration = $fileDuration;
                }
                $fp = fopen(getcwd() . "/public/" . $filePath, 'w');
                $encodeContent = $aes->encrypt(file_get_contents($uploadFile['tmp_name']));
                $encodeString = $encodeContent['password'];
                $hash = $encodeContent['hash'];
                fwrite($fp, $encodeString);
                fclose($fp);
                $ext = strtolower($ext);
                $uploadStatus =  $this->pushFiles("tmp/" . $filePath, getcwd() . "/public/" . $filePath, $ext);
                if (!$uploadStatus) {
                    return new JsonModel(array('success' => false, "messsage" => 'something went wrong try agian'));
                }
                if (in_array($ext, $audioFiles)) {
                    $fileType = \Admin\Model\TourismFiles::file_extension_type_audio;
                } else {
                    $fileType = \Admin\Model\TourismFiles::file_extension_type_video;
                }
                $uploadFileDetails = array(
                    'file_path' => $filePath,
                    'file_name' => $filename,
                    'file_extension' => $ext,
                    'file_extension_type' => $fileType,
                    'hash' => $hash,
                    'status' => \Admin\Model\TemporaryFiles::status_file_not_copied,
                    'duration' => $duration
                );
            }

            $response = $this->temporaryFiles->addTemporaryFile($uploadFileDetails);
            if ($response['success']) {
                return new JsonModel(array('success' => true, "messsage" => 'uploaded', 'id' => $response['id']));
            } else {
                return new JsonModel(array('success' => false, "messsage" => 'something went wrong try agian'));
            }
        }
        return $this->redirect()->toUrl($this->getBaseUrl() . "/a_dMin/login");
    }
    //Upload files - end

    // India Tales - Start
    public function indiaTourListAction()
    {
        $this->checkAdmin();
        $placesList = $this->tourTalesTable->getPlacesList(array('tour_type' => \Admin\Model\TourTales::tour_type_India_tour, 'limit' => 10, 'offset' => 0));
        $totalCount = $this->tourTalesTable->getPlacesList(array('tour_type' => \Admin\Model\TourTales::tour_type_India_tour, 'limit' => 0, 'offset' => 0), 1);
        return new ViewModel(array('placesList' => $placesList, 'totalCount' => $totalCount));
    }

    public function loadIndiaTourListAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $limit = 10;

            $offset = 0;
            $searchData = array('limit' => $limit, 'offset' => $offset, 'tour_type' => \Admin\Model\TourTales::tour_type_India_tour);
            $type = $request['type'];
            $filterData = $request['filter'];
            if ($filterData) {
                $filterData = json_decode($filterData, true);
                if (isset($filterData['country'])) {
                    if (isset($filterData['country']['text']) && $filterData['country']['text']) {
                        $searchData['country'] = $filterData['country']['text'];
                    }
                    if (isset($filterData['country']['order']) && $filterData['country']['order']) {
                        $searchData['country_order'] = $filterData['country']['order'];
                    }
                }
                if (isset($filterData['state'])) {
                    if (isset($filterData['state']['text']) && $filterData['state']['text']) {
                        $searchData['state'] = $filterData['state']['text'];
                    }
                    if (isset($filterData['state']['order']) && $filterData['state']['order']) {
                        $searchData['state_order'] = $filterData['state']['order'];
                    }
                }
                if (isset($filterData['city'])) {

                    if (isset($filterData['city']['text']) && $filterData['city']['text']) {
                        $searchData['city'] = $filterData['city']['text'];
                    }
                    if (isset($filterData['city']['order']) && $filterData['city']['order']) {
                        $searchData['city_order'] = $filterData['city']['order'];
                    }
                }
                if (isset($filterData['place_name'])) {

                    if (isset($filterData['place_name']['text']) && $filterData['place_name']['text']) {
                        $searchData['place_name'] = $filterData['place_name']['text'];
                    }
                    if (isset($filterData['place_name']['order']) && $filterData['place_name']['order']) {
                        $searchData['place_name_order'] = $filterData['place_name']['order'];
                    }
                }
            }

            if (isset($request['page_number'])) {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $searchData['offset'] = $offset;
                $searchData['limit'] = $limit;
            }
            $totalCount = 0;
            if ($type && $type == 'search') {
                $totalCount = $this->tourTalesTable->getPlacesList($searchData, 1);
            }
            $placesList = $this->tourTalesTable->getPlacesList($searchData);
            $view = new ViewModel(array('tourismList' => $placesList, 'type' => $type, 'offset' => $offset, 'totalCount' => $totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function addIndiaTourAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $place = $request['place_name'];
            $stateId = $request['state_id'];
            $cityId = $request['city_id'];
            $ft = $request['free'] ? $request['free'] : '0';
            if ($stateId == '') {
                return new JsonModel(array("success" => false, "message" => "Please select state"));
            }
            $where = array('state_id' => $stateId, 'tour_type' => \Admin\Model\TourTales::tour_type_India_tour);
            if ($cityId) {
                $where['city_id'] = $cityId;
            }
            if (is_null($place)) {
                $place = '';
            }
            $checkTaleAdded = $this->tourTalesTable->checkTaleAdded($where);
            if ($checkTaleAdded)
                return new JsonModel(array("success" => false, "message" => "Tale already added"));
            $data = array('tour_type' => \Admin\Model\TourTales::tour_type_India_tour, 'display' => 1, 'state_id' => $stateId, 'free' => $ft);
            if ($checkTaleAdded && $place) {
                $placesList = array_unique(array_merge(explode(',', $place), explode(",", $checkTaleAdded['place_id'])));
                $data['place_id'] = "," . implode(",", $placesList) . ",";
            } else if ($place) {
                $data['place_id'] = "," . $place . ",";
            }
            if ($cityId) {
                $data['city_id'] = $cityId;
            } else {
                $data['city_id'] = 0;
            }
            if (!$place) {
                $data['place_id'] = '';
            }
            $countryId = $this->countriesTable->getField(array('country_name' => 'india', 'display' => 1), 'id');
            $data['country_id'] = $countryId;
            $success = true;
            if (!count($checkTaleAdded)) {
                $saveData = $this->tourTalesTable->addTourTale($data);
                if (!$saveData['success']) {
                    $success = false;
                }
            } /* else {
                $updateData = $this->tourTalesTable->updateTourTale($data, array('id' => $checkTaleAdded['id']));
                if (!$updateData) {
                    $success = false;
                }
            } */
            if ($success) {
                return new JsonModel(array('success' => true, 'message' => 'India Tale added Successfully'));
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to add India tale'));
            }
        }
        $statesList = $this->statesTable->getActiveIndianStates();
        return new ViewModel(array('statesList' => $statesList));
    }

    public function deleteTourTaleAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $taleId = $request['tale_id'];
            $placeId = $request['place_id'];
            if (!$placeId) {
                $response = $this->tourTalesTable->updateTourTale(array('display' => 0), array('id' => $taleId));
            } else {
                $response = $this->tourTalesTable->deletePlace($placeId, $taleId);
                $checkCountry = $this->tourTalesTable->getField(array('id' => $taleId), 'place_id');
                $placeList = array_filter(explode(",", $checkCountry));
                if (!count($placeList)) {
                    $this->tourTalesTable->updateTourTale(array('display' => 0), array('id' => $taleId));
                }
            }
            if ($response) {
                return new JsonModel(array('success' => true, "message" => 'Deleted successfully'));
            } else {
                return new JsonModel(array('success' => false, "message" => 'unable to update'));
            }
        }
    }

    public function addTourGetPlacesAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $cityId = $request['city_id'];
            $tourType = $request['tour_type'];
            $where = array('city_id' => $cityId, 'tour_type' => $tourType);
            $placesList = $this->placesTable->getPlacesAdmin($where);
            return new JsonModel(array('success' => true, 'places' => $placesList));
        }
    }
    // India Tales - End

    // World Tales - Start
    public function worldTourListAction()
    {
        $this->checkAdmin();
        $placesList = $this->tourTalesTable->getPlacesList(array('tour_type' => \Admin\Model\TourTales::tour_type_World_tour, 'limit' => 10, 'offset' => 0));
        $totalCount = $this->tourTalesTable->getPlacesList(array('tour_type' => \Admin\Model\TourTales::tour_type_World_tour, 'limit' => 0, 'offset' => 0), 1);
        return new ViewModel(array('placesList' => $placesList, 'totalCount' => $totalCount));
    }

    public function loadWorldTourListAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $limit = 10;
            $offset = 0;
            $searchData = array('limit' => $limit, 'offset' => $offset, 'tour_type' => \Admin\Model\TourTales::tour_type_World_tour);
            $type = $request['type'];
            $filterData = $request['filter'];
            if ($filterData) {
                $filterData = json_decode($filterData, true);
                if (isset($filterData['country'])) {
                    if (isset($filterData['country']['text']) && $filterData['country']['text']) {
                        $searchData['country'] = $filterData['country']['text'];
                    }
                    if (isset($filterData['country']['order']) && $filterData['country']['order']) {
                        $searchData['country_order'] = $filterData['country']['order'];
                    }
                }
                if (isset($filterData['state'])) {
                    if (isset($filterData['state']['text']) && $filterData['state']['text']) {
                        $searchData['state'] = $filterData['state']['text'];
                    }
                    if (isset($filterData['state']['order']) && $filterData['state']['order']) {
                        $searchData['state_order'] = $filterData['state']['order'];
                    }
                }
                if (isset($filterData['city'])) {
                    if (isset($filterData['city']['text']) && $filterData['city']['text']) {
                        $searchData['city'] = $filterData['city']['text'];
                    }
                    if (isset($filterData['city']['order']) && $filterData['city']['order']) {
                        $searchData['city_order'] = $filterData['city']['order'];
                    }
                }
                if (isset($filterData['place_name'])) {
                    if (isset($filterData['place_name']['text']) && $filterData['place_name']['text']) {
                        $searchData['place_name'] = $filterData['place_name']['text'];
                    }
                    if (isset($filterData['place_name']['order']) && $filterData['place_name']['order']) {
                        $searchData['place_name_order'] = $filterData['place_name']['order'];
                    }
                }
            }
            if (isset($request['page_number'])) {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $searchData['offset'] = $offset;
                $searchData['limit'] = $limit;
            }
            $totalCount = 0;
            if ($type && $type == 'search') {
                $totalCount = $this->tourTalesTable->getPlacesList($searchData, 1);
            }

            $placesList = $this->tourTalesTable->getPlacesList($searchData);

            $view = new ViewModel(array('tourismList' => $placesList, 'offset' => $offset, "type" => $type, 'totalCount' => $totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function addWorldTourAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $countryId = $request['country_id'];
            $cityId = $request['city_id'];
            $place = $request['place_id'];
            $ft = $request['free'] ? $request['free'] : '0';
            if ($countryId == '') {
                return new JsonModel(array("success" => false, "message" => "Please select country"));
            }
            $where = array('country_id' => $countryId, 'tour_type' => \Admin\Model\TourTales::tour_type_World_tour);
            if ($cityId) {
                $where['city_id'] = $cityId;
            }
            $checkCountryAdded = $this->tourTalesTable->checkTaleAdded($where);
            $data = array('tour_type' => \Admin\Model\TourTales::tour_type_World_tour, 'display' => 1, 'country_id' => $countryId, 'free' => $ft);
            if (is_null($place)) {
                $place = '';
            }
            if ($checkCountryAdded && $place) {
                $placesList = array_unique(array_merge(explode(',', $place), explode(",", $checkCountryAdded['place_id'])));
                $data['place_id'] = "," . implode(",", $placesList) . ",";
            } else if ($place) {
                $data['place_id'] = "," . $place . ",";
            }
            if ($cityId) {
                $data['city_id'] = $cityId;
            } else {
                $data['city_id'] = 0;
            }
            if (!$place) {
                $data['place_id'] = '';
            }
            $success = true;
            if (!count($checkCountryAdded)) {
                $saveData = $this->tourTalesTable->addTourTale($data);
                if (!$saveData['success']) {
                    $success = false;
                }
            } else {
                $updateData = $this->tourTalesTable->updateTourTale($data, array('id' => $checkCountryAdded['id']));
                if (!$updateData) {
                    $success = false;
                }
            }
            if ($success) {
                return new JsonModel(array('success' => true, 'message' => 'World Tour added Successfully'));
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to add world tour'));
            }
        }
        $countryList = $this->countriesTable->getCountries();
        return new ViewModel(array('countryList' => $countryList));
    }
    // World Tales - End

    // Bunched Tales - Start

    public function bunchedTourListAction()
    {
        $this->checkAdmin();
        $placesList = $this->tourTalesTable->getPlacesList(array('tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour, 'limit' => 10, 'offset' => 0));
        $totalCount = $this->tourTalesTable->getPlacesList(array('tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour, 'limit' => 0, 'offset' => 0), 1);
        return new ViewModel(array('placesList' => $placesList, 'totalCount' => $totalCount));
    }
    public function loadBunchedTourListAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $limit = 10;
            $offset = 0;
            $searchData = array('limit' => $limit, 'offset' => $offset, 'tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour);
            $type = $request['type'];
            $filterData = $request['filter'];
            if ($filterData) {
                $filterData = json_decode($filterData, true);
                if (isset($filterData['country'])) {
                    if (isset($filterData['country']['text']) && $filterData['country']['text']) {
                        $searchData['country'] = $filterData['country']['text'];
                    }
                    if (isset($filterData['country']['order']) && $filterData['country']['order']) {
                        $searchData['country_order'] = $filterData['country']['order'];
                    }
                }
                if (isset($filterData['state'])) {
                    if (isset($filterData['state']['text']) && $filterData['state']['text']) {
                        $searchData['state'] = $filterData['state']['text'];
                    }
                    if (isset($filterData['state']['order']) && $filterData['state']['order']) {
                        $searchData['state_order'] = $filterData['state']['order'];
                    }
                }
                if (isset($filterData['city'])) {

                    if (isset($filterData['city']['text']) && $filterData['city']['text']) {
                        $searchData['city'] = $filterData['city']['text'];
                    }
                    if (isset($filterData['city']['order']) && $filterData['city']['order']) {
                        $searchData['city_order'] = $filterData['city']['order'];
                    }
                }
                if (isset($filterData['place_name'])) {

                    if (isset($filterData['place_name']['text']) && $filterData['place_name']['text']) {
                        $searchData['place_name'] = $filterData['place_name']['text'];
                    }
                    if (isset($filterData['place_name']['order']) && $filterData['place_name']['order']) {
                        $searchData['place_name_order'] = $filterData['place_name']['order'];
                    }
                }
            }
            if (isset($request['page_number'])) {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $searchData['offset'] = $offset;
                $searchData['limit'] = $limit;
            }
            $totalCount = 0;
            if ($type && $type == 'search') {
                $totalCount = $this->tourTalesTable->getPlacesList($searchData, 1);
            }
            $placesList = $this->tourTalesTable->getPlacesList($searchData);
            $view = new ViewModel(array('tourismList' => $placesList, 'offset' => $offset, "type" => $type, 'totalCount' => $totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function addBunchedTourAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $countryId = $request['country_id'];
            $cityId = $request['city_id'];
            $place = $request['place_name'];
            if ($countryId == '') {
                return new JsonModel(array("success" => false, "message" => "Please select country"));
            }
            $where = array('country_id' => $countryId, 'tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour);
            if (is_null($place)) {
                $place = '';
            }
            if ($cityId) {
                $where['city_id'] = $cityId;
            }
            $checkCountryAdded = $this->tourTalesTable->checkTaleAdded($where);

            if ($cityId && count($checkCountryAdded)) {
                return new JsonModel(array('success' => false, 'message' => 'city already added'));
            }
            $data = array('tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour, 'display' => 1, 'country_id' => $countryId);
            if ($checkCountryAdded) {
                $data['place_id'] = implode(",", array_unique(explode(',', $place)));
            } else if ($place) {
                $data['place_id'] = "," . $place . ",";
            }
            if ($cityId) {
                $data['city_id'] = $cityId;
            } else {
                $data['city_id'] = 0;
            }
            if ($place) {
                $data['place_id'] = "," . $place . ",";
            } else {
                $data['place_id'] = '';
            }
            $success = true;
            if (!count($checkCountryAdded)) {
                $saveData = $this->tourTalesTable->addTourTale($data);
                if (!$saveData['success']) {
                    $success = false;
                }
            } else {
                $updateData = $this->tourTalesTable->updateTourTale($data, array('id' => $checkCountryAdded['id']));
                if (!$updateData) {
                    $success = false;
                }
            }
            if ($success) {
                return new JsonModel(array('success' => true, 'message' => 'Added to Bunched Tales Successfully'));
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to add Bunched Tour'));
            }
        }
        $countryList = $this->countriesTable->getCountries();
        return new ViewModel(array('countryList' => $countryList));
    }

    public function getPlacesAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $cityId = $request['city_id'];
            $where = array('city_id' => $cityId);
            $placesList = $this->placesTable->getPlaces($where);
            return new JsonModel(array('success' => true, 'places' => $placesList));
        }
    }

    public function editBunchedTourAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $countryId = $request['country_id'];
            $cityId = $request['city_id'];
            $place = $request['place_name'];
            if ($countryId == '') {
                return new JsonModel(array("success" => false, "message" => "Please select country"));
            }
            $where = array('country_id' => $countryId, 'tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour);
            if ($cityId) {
                $where['city_id'] = $cityId;
            }
            $checkTaleAdded = $this->tourTalesTable->checkTaleAdded($where);
            if ($cityId && count($checkTaleAdded) /* &&  $checkTaleAdded['place_price_id']!=$priceId */) {
                return new JsonModel(array('success' => false, 'message' => 'city already added'));
            }
            $data = array('tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour, 'display' => 1, 'country_id' => $countryId);
            if ($checkTaleAdded) {
                $data['place_id'] = implode(",", array_unique(explode(',', $place)));
            }
            if ($cityId) {
                $data['city_id'] = $cityId;
            } else {
                $data['city_id'] = 0;
            }
            if ($place) {
                $data['place_id'] = "," . $place . ",";
            } else {
                $data['place_id'] = '';
            }
            $success = true;
            if ($success) {
                return new JsonModel(array('success' => true, 'message' => 'Updated City Tour Successfully'));
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to add city tour'));
            }
        }
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $taleIdString = rtrim($paramId, "=");
        $taleIdString = base64_decode($taleIdString);
        $taleIdString = explode("=", $taleIdString);
        $taleId = array_key_exists(1, $taleIdString) ? $taleIdString[1] : 0;

        $taleDetails = $this->tourTalesTable->getTourTales(array('id' => $taleId));
        if (!count($taleDetails)) {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/bunched-tour-list');
        }
        $countryList = $this->countriesTable->getActiveCountriesListAdmin(array('tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour));
        $citiesList = $this->citiesTable->getActiveCitiesListAdmin(array('country_id' => $taleDetails[0]['country_id'], 'tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour));
        $placesList = $this->placesTable->getPlacesAdmin(array('city_id' => $taleDetails[0]['city_id'], 'tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour));
        $taleDetails = $taleDetails[0];
        $taleDetails['place_id'] = array_filter(explode(',', $taleDetails['place_id']));

        return new ViewModel(array('countryList' => $countryList, 'cityList' => $citiesList, 'placesList' => $placesList, 'tourDetails' => $taleDetails));
    }

    // Bunched Tales - End

    // Free Tales - Start

    public function freeTourListAction()
    {
        $this->checkAdmin();
        $placesList = $this->tourTalesTable->getPlacesList(array('tour_type' => \Admin\Model\TourTales::tour_type_Free_tour, 'limit' => 10, 'offset' => 0));
        $totalCount = $this->tourTalesTable->getPlacesList(array('tour_type' => \Admin\Model\TourTales::tour_type_Free_tour, 'limit' => 10, 'offset' => 0), 1);
        return new ViewModel(array('placesList' => $placesList, 'totalCount' => $totalCount));
    }

    public function loadFreeTourListAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $offset = 0;
            $searchData = array('limit' => 10, 'offset' => 0, 'tour_type' => \Admin\Model\TourTales::tour_type_Free_tour);
            $type = $request['type'];
            $filterData = $request['filter'];
            if ($filterData) {
                $filterData = json_decode($filterData, true);
                if (isset($filterData['country'])) {
                    if (isset($filterData['country']['text']) && $filterData['country']['text']) {
                        $searchData['country'] = $filterData['country']['text'];
                    }
                    if (isset($filterData['country']['order']) && $filterData['country']['order']) {
                        $searchData['country_order'] = $filterData['country']['order'];
                    }
                }
                if (isset($filterData['state'])) {
                    if (isset($filterData['state']['text']) && $filterData['state']['text']) {
                        $searchData['state'] = $filterData['state']['text'];
                    }
                    if (isset($filterData['state']['order']) && $filterData['state']['order']) {
                        $searchData['state_order'] = $filterData['state']['order'];
                    }
                }
                if (isset($filterData['city'])) {

                    if (isset($filterData['city']['text']) && $filterData['city']['text']) {
                        $searchData['city'] = $filterData['city']['text'];
                    }
                    if (isset($filterData['city']['order']) && $filterData['city']['order']) {
                        $searchData['city_order'] = $filterData['city']['order'];
                    }
                }
                if (isset($filterData['place_name'])) {

                    if (isset($filterData['place_name']['text']) && $filterData['place_name']['text']) {
                        $searchData['place_name'] = $filterData['place_name']['text'];
                    }
                    if (isset($filterData['place_name']['order']) && $filterData['place_name']['order']) {
                        $searchData['place_name_order'] = $filterData['place_name']['order'];
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
                $totalCount = $this->tourTalesTable->getPlacesList($searchData, 1);
            }
            $placesList = $this->tourTalesTable->getPlacesList($searchData);
            $view = new ViewModel(array('tourismList' => $placesList, 'offset' => $offset, "type" => $type, 'totalCount' => $totalCount));
            $view->setTerminal(true);
            return $view;
        }
    }

    // Free Tales - End

    // Subcription Plans - START
    public function subscriptionPlansAction()
    {
        $this->checkAdmin();
        $plansList = $this->subscriptionPlanTable->getPlans(['active' => \Admin\Model\SubscriptionPlan::ActivePlans], ['limit' => 10, 'offset' => 0]);
        $totalCount = $this->subscriptionPlanTable->getPlansCount(\Admin\Model\SubscriptionPlan::ActivePlans);
        return new ViewModel(array('splansList' => $plansList, 'totalCount' => $totalCount));
    }
    // Subcription Plans - END

    // Parameters - Start
    public function parametersAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $ssc = $request['ssc'];
            $adp = $request['adp'];
            $mtl = $request['mtl'];
            $data = array('SSC' => $ssc, 'ADP' => $adp, 'MTL' => $mtl);
            $response = $this->appParameterTable->setParameters($data, array('id' => 1));
            if ($response) {
                return new JsonModel(array('success' => true, 'message' => 'updated successfully'));
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to update'));
            }
        }
        $parameters = $this->appParameterTable->getAllParameters(['id' => 1]);
        $count = $this->userTable->getSubcribersCount();
        $view = new ViewModel(array('parameters' => $parameters, 'count' => $count));
        return $view;
    }
    // Parameters - End

    // Change Password - Start
    public function changePasswordAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $loggedinUser = $this->getLoggedInUser();
            $userId = $loggedinUser['id'];
            $currentPassword = $request['current_password'];
            $newPassword = $request['new_password'];
            $checkPassword = $this->userTable->checkPasswordWithUserId($userId, $currentPassword);
            if (count($checkPassword)) {
                $aes = new Aes();
                $encodeContent = $aes->encrypt($newPassword);
                $encodeString = $encodeContent['password'];
                $hash = $encodeContent['hash'];
                $currentPasswordInsert = $this->userTable->updateUser(['password' => $encodeString, 'hash' => $hash], ['id' => $userId]);
                if ($currentPasswordInsert) {
                    return new JsonModel(array('success' => false, 'message' => 'Updated successfully'));
                } else {
                    return new JsonModel(array('success' => false, 'message' => 'Something went worng. Try again after sometime'));
                }
            } else {
                return new JsonModel(array('success' => false, 'message' => 'current password does not match'));
            }
        }
    }
    // Change Password - End

    // Login - START
    public function logoutAction()
    {
        if ($this->authService->hasIdentity()) {
            $this->authService->clearIdentity();
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function loginAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $this->authService->getAdapter()
                ->setIdentity($request['user_name'])
                ->setCredential($request['password']);

            $result = $this->authService->authenticate();
            if ($result->isValid()) {
                // Authentication successful - Redirect to a protected resource or set a session variable
                return new JsonModel(array("success" => true, "message" => "Valid Credentials"));
            } else {
                // Authentication failed - Handle the error, e.g., display a login form with an error message
                return new JsonModel(array('success' => false, 'message' => 'invalid credentials'));
            }
        }
    }
    // Login - END


    /*********************-- TWISTT Login - START --************************/


    /*********************-- TWISTT Login - END --************************/
}
