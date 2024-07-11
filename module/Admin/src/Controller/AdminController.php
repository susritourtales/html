<?php

namespace Admin\Controller;

use Application\Controller\BaseController;
use Application\Handler\Aes;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\Constraint\IsEmpty;


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
            $totalCount = $this->countriesTable->getCountries4Admin(['limit' => -1, 'offset' => 0], 1);
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
            foreach ($getFiles['thumbnails'] as $images) {
                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_places,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_thumbnail,
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
            $deletedThumbnails = json_decode($request['deleted_thumbnails'], true);
            $deletedAudio = json_decode($request['deleted_audio'], true);
            $deleteFiles = array_merge($deletedAudio, $deletedImages, $deletedThumbnails);
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
            foreach ($getFiles['thumbnails'] as $images) {
                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_places,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_thumbnail,
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
        $tnFiles = array();
        $audioFiles = array();
        $imageCounter = -1;
        $tnCounter = -1;
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
            if ($place['file_extension_type'] == \Admin\Model\TourismFiles::file_extension_type_image) {
                $imageCounter++;
                $imageFiles[$imageCounter]['file_path'] = $place['file_path'];
                $imageFiles[$imageCounter]['file_language_id'] = $place['file_language_id'];
                $imageFiles[$imageCounter]['tourism_file_id'] = $place['tourism_file_id'];
                $imageFiles[$imageCounter]['file_name'] = $place['file_name'];
            } elseif ($place['file_extension_type'] == \Admin\Model\TourismFiles::file_extension_type_thumbnail) {
                $tnCounter++;
                $tnFiles[$tnCounter]['file_path'] = $place['file_path'];
                $tnFiles[$tnCounter]['file_language_id'] = $place['file_language_id'];
                $tnFiles[$tnCounter]['tourism_file_id'] = $place['tourism_file_id'];
                $tnFiles[$tnCounter]['file_name'] = $place['file_name'];
            } else {
                $audioCounter++;
                $audioFiles[$audioCounter]['file_path'] = $place['file_path'];
                $audioFiles[$audioCounter]['file_language_id'] = $place['file_language_id'];
                $audioFiles[$audioCounter]['tourism_file_id'] = $place['tourism_file_id'];
                $audioFiles[$audioCounter]['file_name'] = $place['file_name'];
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
        return new ViewModel(array('countries' => $countriesList, "languages" => $languagesList, 'stateList' => $stateList, 'cityList' => $citiesList, 'imageUrl' => $this->filesUrl(), 'placeDetails' => $placeInfo, 'audioFiles' => $audioFiles, 'imageFiles' => $imageFiles, 'tnFiles' => $tnFiles));
    }

    public function deletePlaceAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
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
            //$request = $this->getRequest()->getPost();
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
                $filePath = $filePath . "/" . $filename;
                if (!in_array(strtolower($ext), $validImageFiles)) {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }
                $uploadStatus = $this->pushFiles("tmp/" . $filePath, $attachment['tmp_name'], $attachment['type']);
                if (!$uploadStatus) {
                    return new JsonModel(array('success' => false, "message" => 'something went wrong try agian'));
                }
                $uploadFileDetails = array(
                    'file_path' => $filePath,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image, 'file_extension' => $ext, 'status' => \Admin\Model\TemporaryFiles::status_file_not_copied, 'duration' => 0, 'hash' => '', 'file_name' => $attachment['name']
                );
            }

            if (isset($files['thumbnail'])) {
                $attachment = $files['thumbnail'];
                $filename = $attachment['name'];
                $fileExt = explode(".", $filename);
                $ext = end($fileExt) ? end($fileExt) : "";
                $ext = strtolower($ext);
                $filenameWithoutExt = $this->generateRandomString() . "_" . strtotime(date("Y-m-d H:i:s"));
                $filename = $filenameWithoutExt . "." . $ext;
                $filePath = "data/images";
                $filePath = $filePath . "/" . $filename;
                if (!in_array(strtolower($ext), $validImageFiles)) {
                    return new JsonModel(array("success" => false, "message" => $ext . " file format is not supported !"));
                }
                $uploadStatus = $this->pushFiles("tmp/" . $filePath, $attachment['tmp_name'], $attachment['type']);
                if (!$uploadStatus) {
                    return new JsonModel(array('success' => false, "message" => 'something went wrong try agian'));
                }
                $uploadFileDetails = array(
                    'file_path' => $filePath,
                    'file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_thumbnail, 'file_extension' => $ext, 'status' => \Admin\Model\TemporaryFiles::status_file_not_copied, 'duration' => 0, 'hash' => '', 'file_name' => $attachment['name']
                );
            }

            if (isset($files['attachments'])) {
                //return new JsonModel(array('success' => false, "message" => $files));
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
                    return new JsonModel(array('success' => false, "message" => 'something went wrong try agian'));
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
                return new JsonModel(array('success' => true, "message" => 'uploaded', 'id' => $response['id']));
            } else {
                return new JsonModel(array('success' => false, "message" => 'something went wrong try agian'));
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
            $placeId = $request['place_id'];
            $stateId = $request['state_id'];
            $cityId = $request['city_id'];
            $ft = $request['free'] ? $request['free'] : '0';
            if ($stateId == '') {
                return new JsonModel(array("success" => false, "message" => "Please select state"));
            }
            if ($cityId == '') {
                return new JsonModel(array("success" => false, "message" => "Please select city"));
            }
            if (is_null($placeId)) {
                return new JsonModel(array("success" => false, "message" => "Please select the place"));
            }
            $placeIdArr = explode(",", $placeId);
            $where = array('place_id' => $placeIdArr, 'tour_type' => \Admin\Model\TourTales::tour_type_India_tour, 'display' => 1);
            $checkTaleAdded = $this->tourTalesTable->checkTaleAdded($where);
            if ($checkTaleAdded)
                return new JsonModel(array("success" => false, "message" => "Tale already added"));
            $countryId = $this->countriesTable->getField(array('country_name' => 'india', 'display' => 1), 'id');
            $data = [];
            foreach ($placeIdArr as $pid) {
                $data[] = ['tour_type' => \Admin\Model\TourTales::tour_type_India_tour, 'display' => 1, 'country_id' => $countryId, 'state_id' => $stateId, 'city_id' => $cityId, 'free' => $ft, 'place_id' => $pid];
            }
            if (!count($checkTaleAdded)) {
                $saveData = $this->tourTalesTable->addMulipleTourTales($data);
                if ($saveData['success']) {
                    return new JsonModel(array('success' => true, 'message' => 'India Tale added Successfully'));
                } else {
                    return new JsonModel(array('success' => false, 'message' => 'unable to add India tale'));
                }
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
            $response = $this->tourTalesTable->updateTourTale(array('display' => 0), array('id' => $taleId));
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
            $placeId = $request['place_id'];
            $ft = $request['free'] ? $request['free'] : '0';
            if ($countryId == '') {
                return new JsonModel(array("success" => false, "message" => "Please select country"));
            }
            if ($cityId == '') {
                return new JsonModel(array("success" => false, "message" => "Please select city"));
            }
            if (is_null($placeId)) {
                return new JsonModel(array("success" => false, "message" => "Please select the place"));
            }
            $placeIdArr = explode(",", $placeId);
            $where = array('place_id' => $placeIdArr, 'tour_type' => \Admin\Model\TourTales::tour_type_World_tour, 'display' => 1);
            $checkTaleAdded = $this->tourTalesTable->checkTaleAdded($where);
            if ($checkTaleAdded)
                return new JsonModel(array("success" => false, "message" => "Tale already added"));
            $data = [];
            foreach ($placeIdArr as $pid) {
                $data[] = ['tour_type' => \Admin\Model\TourTales::tour_type_World_tour, 'display' => 1, 'country_id' => $countryId, 'city_id' => $cityId, 'free' => $ft, 'place_id' => $pid];
            }
            if (!count($checkTaleAdded)) {
                $saveData = $this->tourTalesTable->addMulipleTourTales($data);
                if ($saveData['success']) {
                    return new JsonModel(array('success' => true, 'message' => 'World Tale added Successfully'));
                } else {
                    return new JsonModel(array('success' => false, 'message' => 'unable to add World tale'));
                }
            }
        }
        $countryList = $this->countriesTable->getCountries4wt();
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
                if (isset($filterData['tale_name'])) {

                    if (isset($filterData['tale_name']['text']) && $filterData['tale_name']['text']) {
                        $searchData['tale_name'] = $filterData['tale_name']['text'];
                    }
                    if (isset($filterData['tale_name']['order']) && $filterData['tale_name']['order']) {
                        $searchData['tale_name'] = $filterData['tale_name']['order'];
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
            set_time_limit(0);
            ini_set('mysql.connect_timeout', '0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $taleType = $request['tale_type'];
            $taleName = $request['tale_name'];
            $taleDesc = $request['tale_desc'];
            $talesList = $request['tales_list'];
            $country = $request['country'];
            $state = $request['state'];
            $city = $request['city'];
            if ($taleType == '') {
                return new JsonModel(array("success" => false, "message" => "Please select tale type"));
            }
            if (is_null($talesList)) {
                return new JsonModel(array("success" => false, "message" => "Please select tales to be added to bunched tale"));
            }
            if ($taleType == \Admin\Model\TourTales::tour_type_India_tour) {
                if ($state == '') {
                    return new JsonModel(array('success' => false, 'message' => 'Please select Provincial State'));
                }
                $country = '101';
            } else {
                if ($country == '') {
                    return new JsonModel(array('success' => false, 'message' => 'Please select Provincial Country'));
                }
                $state = null;
            }
            if ($city == '') {
                return new JsonModel(array('success' => false, 'message' => 'Please select Provincial City'));
            }
            if ($taleName == '') {
                return new JsonModel(array("success" => false, "message" => "Please select tale name"));
            }
            if ($taleDesc == '') {
                return new JsonModel(array("success" => false, "message" => "Please select tale description"));
            }
            $fileIds = explode(",", $request['file_Ids']);
            $getFiles = $this->temporaryFiles->getFiles($fileIds);
            $uploadFiles = array();
            foreach ($getFiles['images'] as $images) {
                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_bunched_tales,
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
                return new JsonModel(array('success' => false, 'message' => 'unable to add bunched tale'));
            }
            $btId = "";
            $data = array(
                'tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour,
                'place_id' => $talesList,
                'city_id' => $city,
                'country_id' => $country,
                'state_id' => $state,
                'tale_name' => $taleName,
                'tale_description' => $taleDesc,
                'free' => 0,
                'display' => 1
            );
            $response = $this->tourTalesTable->addTourTale($data);
            if ($response['success']) {
                $btId = $response['id'];
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to add Bunched Tale'));
            }
            $counter = -1;
            if (count($uploadFileDetails)) {
                foreach ($uploadFileDetails as $details) {
                    $counter++;
                    $uploadFileDetails[$counter]['file_data_id'] = "BT_" . $btId;
                    $uploadFileDetails[$counter]["created_at"] = date("Y-m-d H:i:s");
                    $uploadFileDetails[$counter]["updated_at"] = date("Y-m-d H:i:s");
                }
                $this->tourismFilesTable->addMutipleTourismFiles($uploadFileDetails);
            }
            return new JsonModel(array('success' => true, 'message' => 'added successfully'));
        }
        $countryList = $this->countriesTable->getCountries4wt();
        $statesList = $this->statesTable->getActiveIndianStates();
        return new ViewModel(array('countryList' => $countryList, 'statesList' => $statesList));
    }

    public function getTalesAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $taleType = $request['tt'];
            $talesList = $this->tourTalesTable->getPlacesList4BT($taleType);
            return new JsonModel(array('success' => true, 'tales' => $talesList));
        }
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
            set_time_limit(0);
            ini_set('mysql.connect_timeout', '0');
            ini_set('max_execution_time', '0');
            ini_set("memory_limit", "-1");
            $taleType = $request['tale_type'];
            $taleId = $request['id'];
            $taleName = $request['tale_name'];
            $taleDesc = $request['tale_desc'];
            //$talesList = $request['tales_list'];
            $placesList = $request['tales_list'];
            $country = $request['country'];
            $state = $request['state'];
            $city = $request['city'];
            $deletedImages = json_decode($request['deleted_images'], true);
            $deleteFiles = array_merge($deletedImages);
            $uploadFileDetails = array();
            if (is_null($placesList)) {
                return new JsonModel(array("success" => false, "message" => "Please select tales to be added to bunched tale"));
            }
            if ($taleType == \Admin\Model\TourTales::tour_type_India_tour) {
                if ($state == '') {
                    return new JsonModel(array('success' => false, 'message' => 'Please select Provincial State'));
                }
                $country = '101';
            } else {
                if ($country == '') {
                    return new JsonModel(array('success' => false, 'message' => 'Please select Provincial Country'));
                }
                $state = null;
            }
            if ($city == '') {
                return new JsonModel(array('success' => false, 'message' => 'Please select Provincial City'));
            }
            if ($taleName == '') {
                return new JsonModel(array("success" => false, "message" => "Please select tale name"));
            }
            if ($taleDesc == '') {
                return new JsonModel(array("success" => false, "message" => "Please select tale description"));
            }
            $where = array('id' => $taleId, 'tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour);
            $checkTaleAdded = $this->tourTalesTable->checkBTAdded($where);
            $deletedImages = json_decode($request['deleted_images'], true);
            $fileIds = explode(",", $request['file_Ids']);
            $getFiles = $this->temporaryFiles->getFiles($fileIds);
            $uploadFiles = array();
            foreach ($getFiles['images'] as $images) {
                $uploadFileDetails[] = array(
                    'file_path' => $images['file_path'],
                    'file_data_type' => \Admin\Model\TourismFiles::file_data_type_bunched_tales,
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
                return new JsonModel(array('success' => false, 'message' => 'unable to add bunched tale'));
            }
            $talesList = $this->tourTalesTable->getTaleIdsFromPlacesList(explode(',', $placesList));
            $data = array(
                'tour_type' => \Admin\Model\TourTales::tour_type_Bunched_tour,
                'place_id' => $talesList,
                'city_id' => $city,
                'country_id' => $country,
                'state_id' => $state,
                'tale_name' => $taleName,
                'tale_description' => $taleDesc,
                'free' => 0,
                'display' => 1
            );
            if ($checkTaleAdded) {
                $response = $this->tourTalesTable->updateTourTale($data, ['id' => $taleId]);
                if ($response) {
                    $counter = -1;
                    if (count($uploadFileDetails)) {
                        foreach ($uploadFileDetails as $details) {
                            $counter++;
                            $uploadFileDetails[$counter]['file_data_id'] = "BT_" . $taleId;
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
                    return new JsonModel(array('success' => false, 'message' => 'unable to modify Bunched Tale'));
                }
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
        $tourDetails = $this->tourTalesTable->getBunchedTaleDetailsById($taleId);
        $countryList = $this->countriesTable->getCountries4wt();
        $statesList = $this->statesTable->getActiveIndianStates();
        $citiesList = array();
        $tltype = \Admin\Model\TourTales::tour_type_World_tour;
        if ($tourDetails[0]['country_id'] == "101")
            $tltype = \Admin\Model\TourTales::tour_type_India_tour;
        $talesList = $this->tourTalesTable->getPlacesList4BT($tltype);
        /* var_dump($tourDetails);
        exit; */
        if (strtolower($tourDetails[0]['country_name']) == 'india') {
            $citiesList = $this->citiesTable->getCities(array('state_id' => $tourDetails[0]['state_id']));
        } else {
            $citiesList = $this->citiesTable->getCities(array('country_id' => $tourDetails[0]['country_id']));
        }
        return new ViewModel(array('tourDetails' => $tourDetails, 'countryList' => $countryList, 'statesList' => $statesList, 'citiesList' => $citiesList, 'talesList' => $talesList,  'imageUrl' => $this->filesUrl()));
    }

    // Bunched Tales - End

    // Free Tales - Start

    public function freeTourListAction()
    {
        $this->checkAdmin();
        $ftt = \Admin\Model\TourTales::tour_type_Free_World_tour;
        $placesList = $this->tourTalesTable->getPlacesList(array('tour_type' => $ftt, 'limit' => 10, 'offset' => 0));
        $totalCount = $this->tourTalesTable->getPlacesList(array('tour_type' => $ftt, 'limit' => -1, 'offset' => 0), 1);
        return new ViewModel(array('placesList' => $placesList, 'totalCount' => $totalCount, 'ftt'=>$ftt));
    }

    public function loadFreeTourListAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $offset = 0;
            $type = $request['type'];
            $filterData = $request['filter'];
            $ftt = $request['ftt'];
            $searchData = array('tour_type' => $ftt);
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
            $totalCount = 0;
            if ($type && $type == 'search') {
                $totalCount = $this->tourTalesTable->getPlacesList($searchData, 1);
            }
            $searchData['offset'] = 0;
            $searchData['limit'] = 10;
            if (isset($request['page_number'])) {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset'] = $offset;
                $searchData['limit'] = $limit;
            }
            $placesList = $this->tourTalesTable->getPlacesList($searchData);
            $view = new ViewModel(array('placesList' => $placesList, 'offset' => $offset, "type" => $type, 'totalCount' => $totalCount, 'ftt'=>$ftt));
            $view->setTerminal(true);
            return $view;
        }
    }

    public function addFreeTourAction()
    {
        $this->checkAdmin();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $ttString = rtrim($paramId, "=");
        $ttString = base64_decode($ttString);
        $ttString = explode("=", $ttString);
        $ttc = array_key_exists(1, $ttString) ? $ttString[1] : 0;
        $placesList = $this->tourTalesTable->getPlacesList(array('tour_type' => $ttc, 'limit' => 10, 'offset' => 0));
        $totalCount = $this->tourTalesTable->getPlacesList(array('tour_type' => $ttc, 'limit' => -1, 'offset' => 0), 1);
        if ($ttc == \Admin\Model\TourTales::tour_type_World_tour) {
            $ttq = \Admin\Model\TourTales::tour_type_India_tour;
        } else {
            $ttq = \Admin\Model\TourTales::tour_type_World_tour;
        }
        return new ViewModel(array('placesList' => $placesList, 'totalCount' => $totalCount, 'ttc' => $ttc, 'ttq' => $ttq));
    }

    public function addFreeTourListAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $ttc = $request['tt'];
            $offset = 0;
            $searchData = array('tour_type' => $ttc);
            $type = $request['type'];
            $filterData = $request['filter'];
            if ($request['edit'] == '1') {
                $id = base64_decode($request['tale_id']);
                $free = $request['free'] == 'true' ? '1' : '0';
                $editResp = $this->tourTalesTable->updateTourTale(['free' => $free], ['id' => $id]);
                if ($editResp)
                    return new JsonModel(array('success' => true, 'message' => 'updated successfully'));
                else
                    return new JsonModel(array('success' => false, 'message' => 'unable to update'));
            }
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
            $totalCount = 0;
            if ($type && $type == 'search') {
                $totalCount = $this->tourTalesTable->getPlacesList($searchData, 1);
            }
            $searchData['offset'] = 0;
            $searchData['limit'] = 10;
            if (isset($request['page_number'])) {
                $pageNumber = $request['page_number'];
                $offset = ($pageNumber * 10 - 10);
                $limit = 10;
                $searchData['offset'] = $offset;
                $searchData['limit'] = $limit;
            }
            $placesList = $this->tourTalesTable->getPlacesList($searchData);
            if ($ttc == \Admin\Model\TourTales::tour_type_World_tour) {
                $ttq = \Admin\Model\TourTales::tour_type_India_tour;
            } else {
                $ttq = \Admin\Model\TourTales::tour_type_World_tour;
            }
            $view = new ViewModel(array('tourismList' => $placesList, 'offset' => $offset, "type" => $type, 'totalCount' => $totalCount, 'ttc' => $ttc, 'ttq' => $ttq));
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
    public function editSubscriptionPlanAction()
    {
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $pid = $request['pid'];
            $data['qsp_inr'] = $request['qsp_inr'];
            $data['qsp_usd'] = $request['qsp_usd'];
            $data['sqsp_inr'] = $request['sqsp_inr'];
            $data['sqsp_usd'] = $request['sqsp_usd'];
            $data['sqs_start_date'] = date('Y-m-d', strtotime($request['sqs_sd']));
            $data['sqs_end_date'] = date('Y-m-d', strtotime($request['sqs_ed']));
            $data['qrp_inr'] = $request['qrp_inr'];
            $data['qrp_usd'] = $request['qrp_usd'];
            $data['questt_duration'] = $request['qd'];
            $data['twistt_duration'] = $request['td'];
            $data['MTL'] = $request['mtl'];
            $data['ADP'] = $request['adp'];
            $data['topp_inr'] = $request['topp_inr'];
            $data['topp_usd'] = $request['topp_usd'];
            $data['stsp_inr'] = $request['stsp_inr'];
            $data['stsp_usd'] = $request['stsp_usd'];
            $data['sts_start_date'] = date('Y-m-d', strtotime($request['sts_sd']));
            $data['sts_end_date'] = date('Y-m-d', strtotime($request['sts_ed']));
            $data['tppp_inr'] = $request['tppp_inr'];
            $data['tppp_usd'] = $request['tppp_usd'];
            $data['max_pwds'] = $request['max_pwds'];
            $data['dp_inr'] = $request['dp_inr'];
            $data['dp_usd'] = $request['dp_usd'];
            $data['ccp_inr'] = $request['ccp_inr'];
            $data['ccp_usd'] = $request['ccp_usd'];
            $data['tax'] = $request['tax'];
            $data['cd_percentage'] = $request['cdp'];
            $data['web_text'] = $request['wt'];
            $data['app_text'] = $request['at'];

            // $data = $request->getArrayCopy();
            $nullKeys = [];
            foreach ($data as $key => $value) {
                if ($value === null || $value === '') {
                    $nullKeys[] = $key;
                }
            }
            $csvString = implode(',', $nullKeys);
            if ($csvString)
                return new JsonModel(array('success' => false, 'message' => 'please provide all the required values')); //'please provide values for ' . $csvString));
            $response = $this->subscriptionPlanTable->setPlan($data, array('id' => $pid));
            if ($response) {
                return new JsonModel(array('success' => true, 'message' => 'updated successfully'));
            } else {
                return new JsonModel(array('success' => false, 'message' => 'unable to update'));
            }
        }
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $planIdString = rtrim($paramId, "=");
        $planIdString = base64_decode($planIdString);
        $planIdString = explode("=", $planIdString);
        $planId = array_key_exists(1, $planIdString) ? $planIdString[1] : 0;
        $plansDetails = $this->subscriptionPlanTable->getPlans(['active' => \Admin\Model\SubscriptionPlan::ActivePlans, 'id' => $planId], ['limit' => -1, 'offset' => 0]);
        return new ViewModel(array('plan' => $plansDetails[0]));
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

    // Twistt Execitives - Start

    public function executivesAction(){
        if ($this->authService->hasIdentity()) {
            $identity = $this->authService->getIdentity();
            $executivesList = $this->executiveDetailsTable->getExecutiveDetails4Admin(['limit' => 10, 'offset' => 0]);
            $totalCount = $this->executiveDetailsTable->getExecutiveDetails4Admin(['limit' => 10, 'offset' => 0], 1);
            return new ViewModel(['executivesList' => $executivesList, 'totalCount' => $totalCount]);
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function loadExecutivesListAction()
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
                    if (isset($filterData['username'])) {
                        if (isset($filterData['username']['text']) && !empty($filterData['username']['text'])) {
                            $searchData['username'] = $filterData['username']['text'];
                        }
                        if (isset($filterData['username']['order']) && $filterData['username']['order']) {
                            $searchData['username_order'] = $filterData['username']['order'];
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
                    $totalCount = $this->executiveDetailsTable->getExecutiveDetails4Admin($searchData, 1);
                }
                $executivesList = $this->executiveDetailsTable->getExecutiveDetails4Admin($searchData);
                $totalCount = $this->executiveDetailsTable->getExecutiveDetails4Admin($searchData, 1);
                $view = new ViewModel(['executivesList' => $executivesList, 'totalCount' => $totalCount, 'offset' => $offset, 'type' => $type]);
                $view->setTerminal(true);
                return $view;
            }
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function deleteExecutiveAction(){
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $executiveId = $request['id'];
            $response = $this->executiveDetailsTable->setExecutiveDetails(array('deleted' => 1), array('id' => $executiveId));
            if ($response) {
                return new JsonModel(array('success' => true, "message" => 'Deleted successfully'));
            } else {
                return new JsonModel(array('success' => false, "message" => 'unable to delete'));
            }
        }
    }
    public function modifyExecutiveAction(){
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $rId = explode("_", $request['id']);
            $executiveId = $rId[1];
            if(str_starts_with($rId[0], 'v')){
                $response = $this->executiveDetailsTable->setExecutiveDetails(array('verified' => $request['val']), array('id' => $executiveId));
            }elseif(str_starts_with($rId[0], 'b')){
                $response = $this->executiveDetailsTable->setExecutiveDetails(array('banned' => $request['val']), array('id' => $executiveId));
            }
            if ($response) {
                return new JsonModel(array('success' => true, "message" => 'Updated successfully'));
            } else {
                return new JsonModel(array('success' => false, "message" => 'unable to update'));
            }
        }
    }

    public function couponsCommissionsAction(){
        $this->checkAdmin();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $executiveIdString = rtrim($paramId, "=");
        $executiveIdString = base64_decode($executiveIdString);
        $executiveIdString = explode("=", $executiveIdString);
        $executiveId = array_key_exists(1, $executiveIdString) ? $executiveIdString[1] : 0;
        $coupons = $this->couponsTable->getCouponsList(['executive_id' => $executiveId, 'limit' => 10, 'offset' => 0]);
        $totalCount = $this->couponsTable->getCouponsList(['executive_id' => $executiveId], 1);
        return new ViewModel(['coupons' => $coupons, 'totalCount' => $totalCount, 'id' => $executiveId]);
    }

    public function loadCouponsCommissionsListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
        $request = $this->getRequest()->getPost();
        $searchData = array('limit' => 10, 'offset' => 0);
        $type = $request['type'];
        $paramId = $request['id'];
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $executiveIdString = rtrim($paramId, "=");
        $executiveIdString = base64_decode($executiveIdString);
        $executiveIdString = explode("=", $executiveIdString);
        $executiveId = array_key_exists(1, $executiveIdString) ? $executiveIdString[1] : 0;
        $offset = 0;
        if (isset($request['page_number'])) {
            $pageNumber = $request['page_number'];
            $offset = ($pageNumber * 10 - 10);
            $limit = 10;
            $searchData['offset'] = $offset;
            $searchData['limit'] = $limit;
        }
        $searchData['executive_id'] = $executiveId;
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

    public function commissionsPaymentsAction(){
        $this->checkAdmin();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $executiveIdString = rtrim($paramId, "=");
        $executiveIdString = base64_decode($executiveIdString);
        $executiveIdString = explode("=", $executiveIdString);
        $executiveId = array_key_exists(1, $executiveIdString) ? $executiveIdString[1] : 0;
        $transactions = $this->executiveTransactionTable->getTransactionsList(['executive_id' => $executiveId, 'limit' => 10, 'offset' => 0]);
        $totalCount = $this->executiveTransactionTable->getTransactionsList(['executive_id' => $executiveId], 1);
        return new ViewModel(['transactions' => $transactions, 'totalCount' => $totalCount, 'id' => $executiveId]);
    }

    public function loadCommissionsPaymentsListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
        $request = $this->getRequest()->getPost();
        $searchData = array('limit' => 10, 'offset' => 0);
        $type = $request['type'];
        $paramId = $request['id'];
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $executiveIdString = rtrim($paramId, "=");
        $executiveIdString = base64_decode($executiveIdString);
        $executiveIdString = explode("=", $executiveIdString);
        $executiveId = array_key_exists(1, $executiveIdString) ? $executiveIdString[1] : 0;
        $offset = 0;
        if (isset($request['page_number'])) {
            $pageNumber = $request['page_number'];
            $offset = ($pageNumber * 10 - 10);
            $limit = 10;
            $searchData['offset'] = $offset;
            $searchData['limit'] = $limit;
        }
        $searchData['executive_id'] = $executiveId;
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
    public function editExecutiveAction(){
        $this->checkAdmin();
        if ($this->getRequest()->isPost()) {
            $request = $this->getRequest()->getPost();
            $executiveId = $request['id'];
            $userId = $this->executiveDetailsTable->getField(['id' => $executiveId], 'user_id');
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
                        } 
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
                $bankDetails['commission_percentage'] = $postData['comm'];
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
        }
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $executiveIdString = rtrim($paramId, "=");
        $executiveIdString = base64_decode($executiveIdString);
        $executiveIdString = explode("=", $executiveIdString);
        $executiveId = array_key_exists(1, $executiveIdString) ? $executiveIdString[1] : 0;
        $bankDetails = $this->executiveDetailsTable->getExecutiveDetails(['id' => $executiveId]);
        $userDetails = $this->userTable->getUserDetails(['id' => $bankDetails['user_id']]);
        return new ViewModel(['bankDetails' => $bankDetails, 'userDetails' => $userDetails, 'imageUrl' => $this->filesUrl()]);
    }

    // Twistt Execitives - End

    // Twistt Enablers - End

    public function enablerPlansAction(){
        $this->checkAdmin();
        $searchData = ['limit' => 10, 'offset' => 0];
        $plansList = $this->enablerPlansTable->getAdminEnablerPlans($searchData);
        $totalCount = $this->enablerPlansTable->getAdminEnablerPlans($searchData, 1);
        return new ViewModel(['plansList' => $plansList, 'totalCount' => $totalCount]);
    }

    public function loadEnablerPlansAction()
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
                    if (isset($filterData['plan_name'])) {
                        if (isset($filterData['plan_name']['text']) && !empty($filterData['plan_name']['text'])) {
                            $searchData['plan_name'] = $filterData['plan_name']['text'];
                        }
                        if (isset($filterData['plan_name']['order']) && $filterData['plan_name']['order']) {
                            $searchData['plan_name_order'] = $filterData['plan_name']['order'];
                        }
                    }
                    if (isset($filterData['plan_type'])) {
                        if (isset($filterData['plan_type']['text']) && !empty($filterData['plan_type']['text'])) {
                            $searchData['plan_type'] = $filterData['plan_type']['text'];
                        }
                        if (isset($filterData['plan_type']['order']) && $filterData['plan_type']['order']) {
                            $searchData['plan_type_order'] = $filterData['plan_type']['order'];
                        }
                    }
                    if (isset($filterData['price_inr'])) {
                        if (isset($filterData['price_inr']['text']) && !empty($filterData['price_inr']['text'])) {
                            $searchData['price_inr'] = $filterData['price_inr']['text'];
                        }
                        if (isset($filterData['price_inr']['order']) && $filterData['price_inr']['order']) {
                            $searchData['price_inr_order'] = $filterData['price_inr']['order'];
                        }
                    }
                    if (isset($filterData['price_usd'])) {
                        if (isset($filterData['price_usd']['text']) && !empty($filterData['price_usd']['text'])) {
                            $searchData['price_usd'] = $filterData['price_usd']['text'];
                        }
                        if (isset($filterData['price_usd']['order']) && $filterData['price_usd']['order']) {
                            $searchData['price_usd_order'] = $filterData['price_usd']['order'];
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
                    $totalCount = $this->enablerPlansTable->getAdminEnablerPlans($searchData, 1);
                }
                $plansList = $this->enablerPlansTable->getAdminEnablerPlans($searchData);
                $view = new ViewModel(['plansList' => $plansList, 'totalCount' => $totalCount, 'offset' => $offset, 'type' => $type]);
                $view->setTerminal(true);
                return $view;
            }
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function addEnablerPlanAction(){
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $plan['plan_name'] = $request['pn'];
            $plan['plan_type'] = $request['pt'];
            $plan['price_inr'] = $request['ppinr'];
            $plan['price_usd'] = $request['ppusd'];
            $plan['status'] = \Admin\Model\EnablerPlans::status_active;
            $addRes = $this->enablerPlansTable->addEnablerPlan($plan);
            if(!$addRes)
                return new JsonModel(array('success' => false, 'message' => 'unable to add'));
            return new JsonModel(array('success' => true, 'message' => 'Added successfully'));
        }
        return new ViewModel();
    }

    public function editEnablerPlanAction(){
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $planId = $request['id'];
            $plan['plan_name'] = $request['pn'];
            $plan['plan_type'] = $request['pt'];
            $plan['price_inr'] = $request['ppinr'];
            $plan['price_usd'] = $request['ppusd'];
            $plan['status'] = \Admin\Model\EnablerPlans::status_active;
            $res = $this->enablerPlansTable->setEnablerPlans($plan,['id' => $planId]);
            if(!$res)
                return new JsonModel(array('success' => false, 'message' => 'unable to update'));
            return new JsonModel(array('success' => true, 'message' => 'updated successfully'));
        }
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $prIdString = rtrim($paramId, "=");
        $prIdString = base64_decode($prIdString);
        $prIdString = explode("=", $prIdString);
        $planId = array_key_exists(1, $prIdString) ? $prIdString[1] : 0;
        $plan = $this->enablerPlansTable->getEnablerPlans(['id' => $planId]);
        if($plan[0]['plan_type'] == \Admin\Model\EnablerPlans::Paid_Plan)
            $planNameArr = explode(\Admin\Model\EnablerPlans::Paid_Plan, $plan[0]['plan_name']);
        else
            $planNameArr = explode(\Admin\Model\EnablerPlans::Complimentary_Plan, $plan[0]['plan_name']);
        $plan[0]['duration'] = $planNameArr[0];
        $plan[0]['mtl'] = $planNameArr[1];
        return new ViewModel(['plan' => $plan[0]]);
    }

    public function deleteEnablerPlanAction(){
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $planId = $request['id'];
            $res = $this->enablerPlansTable->setEnablerPlans(['status' => \Admin\Model\EnablerPlans::status_inactive],['id' => $planId]);
            if(!$res)
                return new JsonModel(array('success' => false, 'message' => 'unable to delete'));
            return new JsonModel(array('success' => true, 'message' => 'deleted successfully'));
        }
    }

    public function enablersListAction(){
        $this->checkAdmin();
        $searchData = ['limit' => 10, 'offset' => 0];
        $enbList = $this->enablerTable->getAdminEnablersList($searchData);
        $totalCount = $this->enablerTable->getAdminEnablersList($searchData, 1);
        return new ViewModel(['enbList' => $enbList, 'totalCount' => $totalCount]);
    }

    public function loadEnablersListAction()
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
                    if (isset($filterData['username'])) {
                        if (isset($filterData['username']['text']) && !empty($filterData['username']['text'])) {
                            $searchData['username'] = $filterData['username']['text'];
                        }
                        if (isset($filterData['username']['order']) && $filterData['username']['order']) {
                            $searchData['username_order'] = $filterData['username']['order'];
                        }
                    }
                    if (isset($filterData['company_name'])) {
                        if (isset($filterData['company_name']['text']) && !empty($filterData['company_name']['text'])) {
                            $searchData['company_name'] = $filterData['company_name']['text'];
                        }
                        if (isset($filterData['company_name']['order']) && $filterData['company_name']['order']) {
                            $searchData['company_name_order'] = $filterData['company_name']['order'];
                        }
                    }
                    if (isset($filterData['country_code'])) {
                        if (isset($filterData['country_code']['text']) && !empty($filterData['country_code']['text'])) {
                            $searchData['country_phone_code'] = $filterData['country_code']['text'];
                        }
                        if (isset($filterData['country_code']['order']) && $filterData['country_code']['order']) {
                            $searchData['country_phone_code_order'] = $filterData['country_code']['order'];
                        }
                    }
                    if (isset($filterData['mobile'])) {
                        if (isset($filterData['mobile']['text']) && !empty($filterData['mobile']['text'])) {
                            $searchData['mobile_number'] = $filterData['mobile']['text'];
                        }
                        if (isset($filterData['mobile']['order']) && $filterData['mobile']['order']) {
                            $searchData['mobile_number_order'] = $filterData['mobile']['order'];
                        }
                    }
                    if (isset($filterData['country'])) {
                        if (isset($filterData['country']['text']) && !empty($filterData['country']['text'])) {
                            $searchData['country'] = $filterData['country']['text'];
                        }
                        if (isset($filterData['country']['order']) && $filterData['country']['order']) {
                            $searchData['country_order'] = $filterData['country']['order'];
                        }
                    }
                    if (isset($filterData['city'])) {
                        if (isset($filterData['city']['text']) && !empty($filterData['city']['text'])) {
                            $searchData['city'] = $filterData['city']['text'];
                        }
                        if (isset($filterData['city']['order']) && $filterData['city']['order']) {
                            $searchData['city_order'] = $filterData['city']['order'];
                        }
                    }
                    if (isset($filterData['email'])) {
                        if (isset($filterData['email']['text']) && !empty($filterData['email']['text'])) {
                            $searchData['email'] = $filterData['email']['text'];
                        }
                        if (isset($filterData['email']['order']) && $filterData['email']['order']) {
                            $searchData['email_order'] = $filterData['email']['order'];
                        }
                    }
                    if (isset($filterData['stt_disc'])) {
                        if (isset($filterData['stt_disc']['text']) && !empty($filterData['stt_disc']['text'])) {
                            $searchData['stt_disc'] = $filterData['stt_disc']['text'];
                        }
                        if (isset($filterData['stt_disc']['order']) && $filterData['stt_disc']['order']) {
                            $searchData['stt_disc_order'] = $filterData['stt_disc']['order'];
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
                    $totalCount = $this->enablerTable->getAdminEnablersList($searchData, 1);
                }
                $enbList = $this->enablerTable->getAdminEnablersList($searchData);
                $view = new ViewModel(['enbList' => $enbList, 'totalCount' => $totalCount, 'offset' => $offset, 'type' => $type]);
                $view->setTerminal(true);
                return $view;
            }
        } else {
            return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
        }
    }

    public function editEnablerAction(){
        $this->checkAdmin();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $request = $this->getRequest()->getPost();
            $id = base64_decode($request['id']);
            $csd = $request['csd'];
            $res = $this->enablerTable->updateEnabler(['stt_disc' => $csd], ['id' => $id]);
            if(!$res)
                return new JsonModel(array('success' => false, 'message' => 'unable to update'));
            return new JsonModel(array('success' => true, 'message' => 'updated successfully'));
        }
    }

    public function enablerPurchasesAction(){
        $this->checkAdmin();
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $prIdString = rtrim($paramId, "=");
        $prIdString = base64_decode($prIdString);
        $prIdString = explode("=", $prIdString);
        $eId = array_key_exists(1, $prIdString) ? $prIdString[1] : 0;
        $purchases = $this->enablerPurchaseTable->getEnablerPurchasesList(['enabler_id' => $eId, 'limit' => 10, 'offset' => 0]);
        $totalCount = $this->enablerPurchaseTable->getEnablerPurchasesList(['enabler_id' => $eId], 1);
        return new ViewModel(['purchases' => $purchases, 'totalCount'=>$totalCount]);
    }
    public function loadPurchasesListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
        $paramId = $this->params()->fromRoute('id', '');
        if (!$paramId) {
            return $this->redirect()->toUrl($this->getBaseUrl());
        }
        $prIdString = rtrim($paramId, "=");
        $prIdString = base64_decode($prIdString);
        $prIdString = explode("=", $prIdString);
        $eId = array_key_exists(1, $prIdString) ? $prIdString[1] : 0;
        $request = $this->getRequest()->getPost();
        $searchData = array('limit' => 10, 'offset' => 0);
        $type = $request['type'];
        $offset = 0;
        $filterData = $request['filter'];
        if ($filterData) {
            $filterData = json_decode($filterData, true);
            if (isset($filterData['purchase_date'])) {
                if (isset($filterData['purchase_date']['text']) && !empty($filterData['purchase_date']['text'])) {
                    $searchData['purchase_date'] = $filterData['purchase_date']['text'];
                }
                if (isset($filterData['purchase_date']['order']) && $filterData['purchase_date']['order']) {
                    $searchData['purchase_date_order'] = $filterData['purchase_date']['order'];
                }
            }
            if (isset($filterData['plan_name'])) {
                if (isset($filterData['plan_name']['text']) && !empty($filterData['plan_name']['text'])) {
                    $searchData['plan_name'] = $filterData['plan_name']['text'];
                }
                if (isset($filterData['plan_name']['order']) && $filterData['plan_name']['order']) {
                    $searchData['plan_name_order'] = $filterData['plan_name']['order'];
                }
            }
            if (isset($filterData['actual_price'])) {
                if (isset($filterData['actual_price']['text']) && !empty($filterData['actual_price']['text'])) {
                    $searchData['actual_price'] = $filterData['actual_price']['text'];
                }
                if (isset($filterData['actual_price']['order']) && $filterData['actual_price']['order']) {
                    $searchData['actual_price_order'] = $filterData['actual_price']['order'];
                }
            }
            if (isset($filterData['price_after_disc'])) {
                if (isset($filterData['price_after_disc']['text']) && !empty($filterData['price_after_disc']['text'])) {
                    $searchData['price_after_disc'] = $filterData['price_after_disc']['text'];
                }
                if (isset($filterData['price_after_disc']['order']) && $filterData['price_after_disc']['order']) {
                    $searchData['price_after_disc_order'] = $filterData['price_after_disc']['order'];
                }
            }
            if (isset($filterData['coupon_code'])) {
                if (isset($filterData['coupon_code']['text']) && !empty($filterData['coupon_code']['text'])) {
                    $searchData['coupon_code'] = $filterData['coupon_code']['text'];
                }
                if (isset($filterData['coupon_code']['order']) && $filterData['coupon_code']['order']) {
                    $searchData['coupon_code_order'] = $filterData['coupon_code']['order'];
                }
            }
            if (isset($filterData['executive_name'])) {
                if (isset($filterData['executive_name']['text']) && !empty($filterData['executive_name']['text'])) {
                    $searchData['executive_name'] = $filterData['executive_name']['text'];
                }
                if (isset($filterData['executive_name']['order']) && $filterData['executive_name']['order']) {
                    $searchData['executive_name_order'] = $filterData['executive_name']['order'];
                }
            }
            if (isset($filterData['executive_mobile'])) {
                if (isset($filterData['executive_mobile']['text']) && !empty($filterData['executive_mobile']['text'])) {
                    $searchData['executive_mobile'] = $filterData['executive_mobile']['text'];
                }
                if (isset($filterData['executive_mobile']['order']) && $filterData['executive_mobile']['order']) {
                    $searchData['executive_mobile_order'] = $filterData['executive_mobile']['order'];
                }
            }
            if (isset($filterData['invoice'])) {
                if (isset($filterData['invoice']['text']) && !empty($filterData['invoice']['text'])) {
                    $searchData['invoice'] = $filterData['invoice']['text'];
                }
                if (isset($filterData['invoice']['order']) && $filterData['invoice']['order']) {
                    $searchData['invoice_order'] = $filterData['invoice']['order'];
                }
            }
            if (isset($filterData['receipt'])) {
                if (isset($filterData['receipt']['text']) && !empty($filterData['receipt']['text'])) {
                    $searchData['receipt'] = $filterData['receipt']['text'];
                }
                if (isset($filterData['receipt']['order']) && $filterData['receipt']['order']) {
                    $searchData['receipt_order'] = $filterData['receipt']['order'];
                }
            }
            if (isset($filterData['lic_bal'])) {
                if (isset($filterData['lic_bal']['text']) && !empty($filterData['lic_bal']['text'])) {
                    $searchData['lic_bal'] = $filterData['lic_bal']['text'];
                }
                if (isset($filterData['lic_bal']['order']) && $filterData['lic_bal']['order']) {
                    $searchData['lic_bal_order'] = $filterData['lic_bal']['order'];
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
        $searchData['enabler_id'] = $eId;
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

  public function enablerSalesAction(){
    $this->checkAdmin();
    $paramId = $this->params()->fromRoute('id', '');
    if (!$paramId) {
        return $this->redirect()->toUrl($this->getBaseUrl());
    }
    $prIdString = rtrim($paramId, "=");
    $prIdString = base64_decode($prIdString);
    $prIdString = explode("=", $prIdString);
    $id = array_key_exists(1, $prIdString) ? $prIdString[1] : 0;
    $sales = $this->enablerSalesTable->getAdminEnablerSalesList(['purchase_id' => $id, 'limit' => 10, 'offset' => 0]);
    $totalCount = $this->enablerSalesTable->getAdminEnablerSalesList(['purchase_id' => $id], 1);
    return new ViewModel(['sales' => $sales, 'totalCount'=>$totalCount]);
}
public function loadSalesListAction()
{
if ($this->getRequest()->isXmlHttpRequest()) {
   $paramId = $this->params()->fromRoute('id', '');
   if (!$paramId) {
      return $this->redirect()->toUrl($this->getBaseUrl());
   }
  $prIdString = rtrim($paramId, "=");
  $prIdString = base64_decode($prIdString);
  $prIdString = explode("=", $prIdString);
  $id = array_key_exists(1, $prIdString) ? $prIdString[1] : 0;
  $request = $this->getRequest()->getPost();
  $searchData = array('limit' => 10, 'offset' => 0);
  $type = $request['type'];
  $offset = 0;
  $filterData = $request['filter'];
    if ($filterData) {
        $filterData = json_decode($filterData, true);
        if (isset($filterData['sale_date'])) {
            if (isset($filterData['sale_date']['text']) && !empty($filterData['sale_date']['text'])) {
                $searchData['sale_date'] = $filterData['sale_date']['text'];
            }
            if (isset($filterData['sale_date']['order']) && $filterData['sale_date']['order']) {
                $searchData['sale_date_order'] = $filterData['sale_date']['order'];
            }
        }
        if (isset($filterData['plan_name'])) {
            if (isset($filterData['plan_name']['text']) && !empty($filterData['plan_name']['text'])) {
                $searchData['plan_name'] = $filterData['plan_name']['text'];
            }
            if (isset($filterData['plan_name']['order']) && $filterData['plan_name']['order']) {
                $searchData['plan_name_order'] = $filterData['plan_name']['order'];
            }
        }
        if (isset($filterData['tourist_name'])) {
            if (isset($filterData['tourist_name']['text']) && !empty($filterData['tourist_name']['text'])) {
                $searchData['tourist_name'] = $filterData['tourist_name']['text'];
            }
            if (isset($filterData['tourist_name']['order']) && $filterData['tourist_name']['order']) {
                $searchData['tourist_name_order'] = $filterData['tourist_name']['order'];
            }
        }
        if (isset($filterData['tourist_mobile'])) {
            if (isset($filterData['tourist_mobile']['text']) && !empty($filterData['tourist_mobile']['text'])) {
                $searchData['tourist_mobile'] = $filterData['tourist_mobile']['text'];
            }
            if (isset($filterData['tourist_mobile']['order']) && $filterData['tourist_mobile']['order']) {
                $searchData['tourist_mobile_order'] = $filterData['tourist_mobile']['order'];
            }
        }
        if (isset($filterData['tourist_email'])) {
            if (isset($filterData['tourist_email']['text']) && !empty($filterData['tourist_email']['text'])) {
                $searchData['tourist_email'] = $filterData['tourist_email']['text'];
            }
            if (isset($filterData['tourist_email']['order']) && $filterData['tourist_email']['order']) {
                $searchData['tourist_email_order'] = $filterData['tourist_email']['order'];
            }
        }
        if (isset($filterData['twistt_start_date'])) {
            if (isset($filterData['twistt_start_date']['text']) && !empty($filterData['twistt_start_date']['text'])) {
                $searchData['twistt_start_date'] = $filterData['twistt_start_date']['text'];
            }
            if (isset($filterData['twistt_start_date']['order']) && $filterData['twistt_start_date']['order']) {
                $searchData['twistt_start_date_order'] = $filterData['twistt_start_date']['order'];
            }
        }
        if (isset($filterData['lic_bal'])) {
            if (isset($filterData['lic_bal']['text']) && !empty($filterData['lic_bal']['text'])) {
                $searchData['lic_bal'] = $filterData['lic_bal']['text'];
            }
            if (isset($filterData['lic_bal']['order']) && $filterData['lic_bal']['order']) {
                $searchData['lic_bal_order'] = $filterData['lic_bal']['order'];
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
  $searchData['purchase_id'] = $id;
  $totalCount = 0;

  if ($type && $type == 'search') {
    $totalCount = $this->enablerSalesTable->getAdminEnablerSalesList($searchData, 1);
  }
  $sales = $this->enablerSalesTable->getAdminEnablerSalesList($searchData);
  $view = new ViewModel(array('sales' => $sales, 'totalCount' => $totalCount));
  $view->setTerminal(true);
  return $view;
}
}
  public function enablerInvoiceAction(){
    if ($this->authService->hasIdentity()) {
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
        $enablerDetails = $this->enablerTable->getEnablerDetails(['id' => $prDetails['enabler_id']]);
        $prRes = $this->enablerPurchaseTable->getField(['invoice' => $prDetails['invoice']], 'id');
        $show = 1;
        if($prRes)
          $show = 0;
      }else{
        return new JsonModel(array('success' => false, "message" => 'invoice not found..'));
      } 
      return new ViewModel(['enablerDetails' => $enablerDetails,'prDetails' => $prDetails, 'imageUrl' => $this->filesUrl()]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

  public function enablerReceiptAction(){
    if ($this->authService->hasIdentity()) {
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
      return new ViewModel(['purchase' => $purchase, 'prId'=>$paramId, 'planName'=>$planName]);
    } else {
      $this->redirect()->toUrl($this->getBaseUrl() . '/twistt/enabler/login');
    }
  }

    // Twistt Enablers - End

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
                    return new JsonModel(array('success' => true, 'message' => 'Updated successfully'));
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
        $this->authService->clearIdentity();
        return $this->redirect()->toUrl($this->getBaseUrl() . '/a_dMin/login');
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
}
