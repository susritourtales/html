<?php


namespace Admin\Model;


class TbeLogin
{
    public $id;
    public $mobile;
    public $login_id;
    public $pwd;
    public $hash;
        
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->mobile = !empty($data['mobile']) ? $data['mobile'] : null;
        $this->login_id = !empty($data['login_id']) ? $data['login_id'] : null;
        $this->pwd = !empty($data['pwd']) ? $data['pwd'] : null;
        $this->hash = !empty($data['hash']) ? $data['hash'] : null;
    }
}