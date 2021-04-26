<?php


namespace Admin\Model;


class States
{
    public $id;
    public $name;
    public $country_id;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->country_id = !empty($data['country_id']) ? $data['country_id'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}