<?php
 // added by Manjary for STT subscription version
namespace Admin\Model;

class SponsorTypes
{
    public $typeid;
    public $typename;
   
    public function exchangeArray(array $data)
    {
        $this->typeid = !empty($data['type_id']) ? $data['type_id'] : null;
        $this->typename = !empty($data['type_name']) ? $data['type_name'] : null;
    }
}