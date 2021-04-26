<?php


namespace Admin\Model;


class CityTourSlabDays
{
    public $city_tour_slab_id;
    public $days;
    public $status;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {
        $this->city_tour_slab_id = !empty($data['city_tour_slab_id']) ? $data['city_tour_slab_id'] : null;
        $this->days = !empty($data['days']) ? $data['days'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}