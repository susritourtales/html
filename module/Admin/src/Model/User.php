<?php

namespace Admin\Model;

class User
{
    public $mobile;
    public $mobile_country_code;
    public $flag_code;
    public $role;
    //public $user_type; // added by Manjary for STT subscription version - undone
    public $status;
    public $created_at;
    public $updated_at;

    const Admin_role=0;
    const Individual_role=1;
    const Subscriber_role=2; //const Tour_Operator_role=2;
    const Sponsor_role=3;
    const Privilage_user=4;
    const Tour_coordinator_role=5;  // to be removed later
    const Tour_Operator_role=6;  // to be removed later
    // added by Manjary for STT subscription version - start - undone
    /*const Individual_type = "0";
    const Subscriber_type = "1";
    const Sponsor_type = "2";*/
    // added by Manjary for STT subscription version - end - undone
    const User_mobile_verify=0;
    const User_email_verfiy=1;

    const Is_user_verified=1;
    const Is_user_not_verified=0;

    const Is_Not_Promoter = 0;
    const Is_Promoter = 1;
    const Is_terminated_Promoter = 2;
    const Is_resigned_Promoter = 3;

    public function exchangeArray(array $data)
    {
        $this->mobile = !empty($data['mobile']) ? $data['mobile'] : null;
        $this->mobile_country_code = !empty($data['mobile_country_code']) ? $data['mobile_country_code'] : null;
        $this->flag_code = !empty($data['flag_code']) ? $data['flag_code'] : null;
        $this->role = !empty($data['role']) ? $data['role'] : null;
        //$this->role = !empty($data['user_type']) ? $data['user_type'] : null;  // added by Manjary for STT-SV - undone
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}