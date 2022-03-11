<?php


namespace Admin\Model;


class PromoterParameters
{
    public $id;
    public $pwd_ceiling;
    public $amt_per_pwd;
    public $redeem_ceiling;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->pwd_ceiling = !empty($data['pwd_ceiling']) ? $data['pwd_ceiling'] : null;
        $this->amt_per_pwd = !empty($data['amt_per_pwd']) ? $data['amt_per_pwd'] : null;
        $this->redeem_ceiling = !empty($data['redeem_ceiling']) ? $data['redeem_ceiling'] : null;
    }
}