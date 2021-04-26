<?php


namespace Admin\Model;


class Upcoming
{
    public $id;
    public $country;
    public $state;
    public $city;
    public $place;
    public $languages;
    public $tourtype;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->country = !empty($data['country']) ? $data['country'] : null;
        $this->state = !empty($data['state']) ? $data['state'] : null;
        $this->name = !empty($data['city']) ? $data['city'] : null;
        $this->place = !empty($data['place']) ? $data['place'] : null;
        $this->languages = !empty($data['languages']) ? $data['languages'] : null;
        $this->tourtype = !empty($data['tourtype']) ? $data['tourtype'] : null;
    }
}