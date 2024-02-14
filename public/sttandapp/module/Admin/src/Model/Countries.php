<?php

namespace Admin\Model;

class Countries
{
    public $id;
    public $country_name;
    public $country_code;
    public $country_description;
    public $flag_image;
    public $phone_code;
    public $display;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->country_name = !empty($data['country_name']) ? $data['country_name'] : null;
        $this->country_code  = !empty($data['country_code']) ? $data['country_code'] : null;
        $this->country_description = !empty($data['country_description']) ? $data['country_description'] : null;
        $this->flag_image  = !empty($data['flag_image']) ? $data['flag_image'] : null;
        $this->phone_code  = !empty($data['phone_code']) ? $data['phone_code'] : null;
        $this->display  = !empty($data['display']) ? $data['display'] : null;
    }
}
