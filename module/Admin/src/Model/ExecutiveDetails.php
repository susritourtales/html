<?php

namespace Admin\Model;

class ExecutiveDetails
{
  public $id;
  public $user_id;
  public $commission_percentage;
  public $bank_account_no;
  public $ifsc_code;
  public $bank_name;
  public $created_at;
  public $updated_at;
  const DefaultCommission = '20';
  const Is_Verified = '1';
  const Not_Verified = '0';
  const Is_Banned = '1';
  const Not_Banned = '0';

  public function exchangeArray(array $data)
  {
    $this->id = !empty($data['id']) ? $data['id'] : null;
    $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
    $this->commission_percentage  = !empty($data['commission_percentage']) ? $data['commission_percentage'] : null;
    $this->bank_account_no = !empty($data['bank_account_no']) ? $data['bank_account_no'] : null;
    $this->ifsc_code = !empty($data['ifsc_code']) ? $data['ifsc_code'] : null;
    $this->bank_name = !empty($data['bank_name']) ? $data['bank_name'] : null;
    $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
    $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
  }
}
