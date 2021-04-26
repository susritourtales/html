<?php


namespace Admin\Model;


class Otp
{
  public $id;
  public $user_id;
  public $otp;
  public $otp_type;
  public $created_at;
  public $updated_at;
  const LOGIN_OTP=1;
  const FORGOT_OTP=2;
  const REGISTER_EMAIL_OTP=3;
  const I_HAVE_OTP=4;


  const Not_verifed=0;
  const Is_verifed=1;
  const Expired=2;

    public function exchangeArray($data)
    {

        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->user_id = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->otp = (!empty($data['otp'])) ? $data['otp'] : null;
        $this->otp_type = (!empty($data['otp_type'])) ? $data['otp_type'] : null;
        $this->created_at = (!empty($data['created_at'])) ? $data['created_at'] : null;
        $this->updated_at = (!empty($data['updated_at'])) ? $data['updated_at'] : null;

    }
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}