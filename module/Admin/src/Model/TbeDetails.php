<?php


namespace Admin\Model;


class TbeDetails
{
    public $id;
    public $ta_id;
    public $tbe_name;
    public $tbe_mobile;
    public $tbe_email;
    public $login_id;
    public $pwd;
    public $role;

    const Twistt_TA_role='A';
    const Twistt_TBE_role='B';
    const TBE_Disabled = 0;
    const TBE_Deleted = 0;
    const TBE_Enabled = 1;
    const TBE_Active = 1;
        
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->ta_id = !empty($data['ta_id']) ? $data['ta_id'] : null;
        $this->tbe_name = !empty($data['tbe_name']) ? $data['tbe_name'] : null;
        $this->tbe_mobile = !empty($data['tbe_mobile']) ? $data['tbe_mobile'] : null;
        $this->tbe_email = !empty($data['tbe_email']) ? $data['tbe_email'] : null;
        $this->login_id = !empty($data['login_id']) ? $data['login_id'] : null;
        $this->pwd = !empty($data['pwd']) ? $data['pwd'] : null;
        $this->pwd = !empty($data['role']) ? $data['role'] : null;
    }
}