<?php

namespace Admin\Model;
class EnablerPlans
{
  public $id;
  public $plan_name;
  public $price;
  public $status;
  public $created_at;
  public $updated_at;

  const status_active = 1;
  const status_inactive = 0;

  const Paid_Plan = "P";
  const Complimentary_Plan = "C";

  public function exchangeArray(array $data)
  {
    $this->id = !empty($data['id']) ? $data['id'] : null;
    $this->plan_name = !empty($data['plan_name']) ? $data['plan_name'] : null;
    $this->price = !empty($data['price']) ? $data['price'] : null;
    $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
    $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
  }
}
