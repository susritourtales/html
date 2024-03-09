<?php

namespace Admin\Model;

class AppParameter
{
  public $id;
  public $ADP;
  public $MTL;
  public $SSC;

  public function exchangeArray(array $data)
  {
    $this->id = !empty($data['id']) ? $data['id'] : null;
    $this->ADP = !empty($data['ADP']) ? $data['ADP'] : null;
    $this->MTL  = !empty($data['MTL']) ? $data['MTL'] : null;
    $this->SSC = !empty($data['SSC']) ? $data['SSC'] : null;
  }
}
