<?php
namespace Admin\Model;

class Language
{
    public $id;
    public $language_name;
    public $language_code;
    public $language_type;
    public $display;

    const primary_language = 2;
    const secondary_language = 1;


    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->language_name = !empty($data['language_name']) ? $data['language_name'] : null;
        $this->language_code  = !empty($data['language_code']) ? $data['language_code'] : null;
        $this->language_type = !empty($data['language_type']) ? $data['language_type'] : null;
        $this->display  = !empty($data['display']) ? $data['display'] : null;
    }
}
?>