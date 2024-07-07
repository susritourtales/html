<?php

namespace Admin\Model;

class EnablerPurchaseRequest
{
  public $id;
  public $enabler_id;
  public $plan_id;
  public $amount;
  public $currency;
  public $purchase_date;
  public $stt_disc;
  public $executive_name;
  public $executive_mobile;
  public $created_at;
  public $updated_at;

  public function exchangeArray(array $data)
  {
    $this->id = !empty($data['id']) ? $data['id'] : null;
    $this->enabler_id = !empty($data['enabler_id']) ? $data['enabler_id'] : null;
    $this->plan_id = !empty($data['plan_id']) ? $data['plan_id'] : null;
    $this->amount  = !empty($data['amount']) ? $data['amount'] : null;
    $this->currency = !empty($data['currency']) ? $data['currency'] : null;
    $this->purchase_date = !empty($data['purchase_date']) ? $data['purchase_date'] : null;
    $this->invoice = !empty($data['invoice']) ? $data['invoice'] : null;
    $this->razorpay_signature = !empty($data['razorpay_signature']) ? $data['razorpay_signature'] : null;
    $this->executive_name = !empty($data['executive_name']) ? $data['executive_name'] : null;
    $this->executive_mobile = !empty($data['executive_mobile']) ? $data['executive_mobile'] : null;
    $this->stt_disc = !empty($data['stt_disc']) ? $data['stt_disc'] : null;
    $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
    $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
  }
}
