<?php


namespace Admin\Model;


class TaSds
{
    public $id;
    public $exec_id;
    public $role;
    public $tourist_name;
    public $tourist_mobile;
    public $upc;
        
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->exec_id = !empty($data['exec_id']) ? $data['exec_id'] : null;
        $this->role = !empty($data['role']) ? $data['role'] : null;
        $this->tourist_name = !empty($data['tourist_name']) ? $data['tourist_name'] : null;
        $this->tourist_mobile = !empty($data['tourist_mobile']) ? $data['tourist_mobile'] : null;
        $this->upc = !empty($data['upc']) ? $data['upc'] : null;
    }
}