<?php

namespace Admin\Model;

class ExecutivePurchase
{
  public $id;
  public $user_id;
  public $executive_id;
  public $amount;
  public $currency;
  public $purchase_date;
  public $payment_request_id;
  public $payment_response_id;
  public $payment_status;
  public $created_at;
  public $updated_at;


  const payment_in_process = 0;
  const payment_success = 1;
  const payment_failure = 2;

  public function exchangeArray(array $data)
  {
    $this->id = !empty($data['id']) ? $data['id'] : null;
    $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
    $this->executive_id = !empty($data['executive_id']) ? $data['executive_id'] : null;
    $this->amount  = !empty($data['amount']) ? $data['amount'] : null;
    $this->currency = !empty($data['currency']) ? $data['currency'] : null;
    $this->purchase_date = !empty($data['purchase_date']) ? $data['purchase_date'] : null;
    $this->payment_request_id = !empty($data['payment_request_id']) ? $data['payment_request_id'] : null;
    $this->payment_response_id = !empty($data['payment_response_id']) ? $data['payment_response_id'] : null;
    $this->payment_status = !empty($data['payment_status']) ? $data['payment_status'] : null;
    $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
    $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
  }
}
