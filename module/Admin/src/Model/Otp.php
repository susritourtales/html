<?php

namespace Admin\Model;

class Otp
{
  public $id;
  public $user_id;
  public $otp;
  public $otp_type_id;
  public $ifsc_code;
  public $verification_mode;
  public $sent_status_id;
  public $otp_requested_by;
  const Login = '1';
  const Forgot_Password =  '2';
  const Change_Password = '3';
  const User_Registration = '4';
  const Executive_Registration = '5';
  const Mobile_Verification = '1';
  const Email_Verification = '2';
  const Not_verifed=0;
  const Is_verifed=1;
  const Expired=2;
  const Admin_Request = "1";
  const App_Request = "2";
  const TWISTT_Request = "3";

  public function exchangeArray(array $data)
  {
    $this->id = !empty($data['id']) ? $data['id'] : null;
    $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
    $this->otp  = !empty($data['otp']) ? $data['otp'] : null;
    $this->otp_type_id = !empty($data['otp_type_id']) ? $data['otp_type_id'] : null;
    $this->ifsc_code = !empty($data['ifsc_code']) ? $data['ifsc_code'] : null;
    $this->verification_mode = !empty($data['verification_mode']) ? $data['verification_mode'] : null;
    $this->sent_status_id = !empty($data['sent_status_id']) ? $data['sent_status_id'] : null;
    $this->otp_requested_by = !empty($data['otp_requested_by']) ? $data['otp_requested_by'] : null;
  }
}
