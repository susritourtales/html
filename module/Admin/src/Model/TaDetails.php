<?php


namespace Admin\Model;


class TaDetails
{
    public $id;
    public $ta_name;
    public $ta_mobile;
    public $ta_email;
    public $ae_name;
    public $ae_mobile;
    public $ae_email;
    public $ta_logo;
    public $consultant_id;
    public $tac;
        
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->ta_name = !empty($data['ta_name']) ? $data['ta_name'] : null;
        $this->ta_mobile = !empty($data['ta_mobile']) ? $data['ta_mobile'] : null;
        $this->ta_email = !empty($data['ta_email']) ? $data['ta_email'] : null;
        $this->ae_name = !empty($data['ae_name']) ? $data['ae_name'] : null;
        $this->ae_mobile = !empty($data['ae_mobile']) ? $data['ae_mobile'] : null;
        $this->ae_email = !empty($data['ae_email']) ? $data['ae_email'] : null;
        $this->ta_logo = !empty($data['ta_logo']) ? $data['ta_logo'] : null;
        $this->consultant_id = !empty($data['consultant_id']) ? $data['consultant_id'] : null;
        $this->tac = !empty($data['tac']) ? $data['tac'] : null;
    }
}