<?php

namespace Admin\Model;

class ExecutiveTransaction
{
  public $id;
  public $user_id;
  public $coupon_id;
  public $transaction_type;
  public $transaction_date;
  public $paid_amount;
  public $balance_outstanding;
  public $created_at;
  public $updated_at;


  const transaction_due = 0;
  const transaction_paid = 1;

  public function exchangeArray(array $data)
  {
    $this->id = !empty($data['id']) ? $data['id'] : null;
    $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
    $this->coupon_id = !empty($data['coupon_id']) ? $data['coupon_id'] : null;
    $this->transaction_type  = !empty($data['transaction_type']) ? $data['transaction_type'] : null;
    $this->transaction_date = !empty($data['transaction_date']) ? $data['transaction_date'] : null;
    $this->paid_amount = !empty($data['paid_amount']) ? $data['paid_amount'] : null;
    $this->balance_outstanding = !empty($data['balance_outstanding']) ? $data['balance_outstanding'] : null;
    $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
    $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
  }
}
