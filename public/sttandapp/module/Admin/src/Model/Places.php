<?php


namespace Admin\Model;


class Places
{
    public $id;
    public $place_name;
    public $place_description;
    public $country_id;
    public $state_id;
    public $city_id;
    public $created_at;
    public $updated_at;
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->place_name = !empty($data['place_name']) ? $data['place_name'] : null;
        $this->place_description = !empty($data['place_description']) ? $data['place_description'] : null;
        $this->country_id = !empty($data['country_id']) ? $data['country_id'] : null;
        $this->state_id = !empty($data['state_id']) ? $data['state_id'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}
