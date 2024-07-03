<?php

namespace Admin\Model;
class EnablerSales
{
  public $id;
  public $enabler_id;
  public $plan_id;
  public $sale_date;
  public $tourist_name;
  public $tourist_mobile;
  public $tourist_email;
  public $lic_bal;
  public $created_at;
  public $updated_at;

  const transaction_due = 0;
  const transaction_paid = 1;

  public function exchangeArray(array $data)
  {
    $this->id = !empty($data['id']) ? $data['id'] : null;
    $this->enabler_id = !empty($data['enabler_id']) ? $data['enabler_id'] : null;
    $this->plan_id = !empty($data['plan_id']) ? $data['plan_id'] : null;
    $this->sale_date = !empty($data['sale_date']) ? $data['sale_date'] : null;
    $this->tourist_name  = !empty($data['tourist_name']) ? $data['tourist_name'] : null;
    $this->tourist_mobile = !empty($data['tourist_mobile']) ? $data['tourist_mobile'] : null;
    $this->tourist_email = !empty($data['tourist_email']) ? $data['tourist_email'] : null;
    $this->lic_bal = !empty($data['lic_bal']) ? $data['lic_bal'] : null;
    $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
    $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
  }
}
