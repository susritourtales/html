<?php

namespace User\Controller;

use Application\Controller\BaseController;
use Application\Handler\Aes;
use Instamojo\Instamojo;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;

class IndexController extends BaseController 
{ //AbstractActionController
  public function indexAction()
  {
    $marqText = "Awareness enhances enjoyment... Know about the place you visit by listening to Susri Tour Tales...";
    $banners=$this->bannerTable->getBanners();
    $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_HOME_PAGE);
    return new ViewModel(['marqText'=>$marqText,'banners'=>$banners, 'imageUrl'=>$this->filesUrl()]);
  }
  public function aboutUsAction() {
    $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_ABOUT_PAGE);
    return new ViewModel();
}

public function contactAction() {
  $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_CONTACT_PAGE);
  return new ViewModel();
}

  public function twisttAction()
  {
    return new ViewModel();
  }

  public function executiveRegisterAction()
  {
    return new ViewModel();
  }

  public function executiveLoginAction()
  {
    return new ViewModel();
  }
  public function executiveAuthAction()
  {
    return new ViewModel();
  }
  public function executiveHomeAction()
  {
    return new ViewModel();
  }

  public function executiveForgotPasswordAction()
  {
    return new ViewModel();
  }

  public function enablerRegisterAction()
  {
    /* echo "TWISTT Enabler Register";
    exit; */
    return new ViewModel();
  }

  public function enablerLoginAction()
  {
    /* echo "TWISTT Enabler Login";
    exit; */
    return new ViewModel();
  }

  public function termsPrivacyAction() {
    $this->layout()->setVariable('activeTab', \Application\Constants\Constants::MAIN_SITE_TERMS_PAGE);
      return new ViewModel();
  }
}
