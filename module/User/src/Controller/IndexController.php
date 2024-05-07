<?php

namespace User\Controller;

use Application\Handler\Aes;
use Instamojo\Instamojo;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
  public function indexAction()
  {
    echo "User Index";
    exit;
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
}
