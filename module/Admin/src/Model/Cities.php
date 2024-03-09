<?php


namespace Admin\Model;


class Cities
{
    public $id;
    public $city_name;
    public $state_id;
    public $country_id;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->city_name = !empty($data['city_name']) ? $data['city_name'] : null;
        $this->state_id = !empty($data['state_id']) ? $data['state_id'] : null;
        $this->country_id = !empty($data['country_id']) ? $data['country_id'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}
