<?php

namespace Admin\Model;

class TourTales
{
    public $id;
    public $place_id;
    public $city_id;
    public $state_id;
    public $country_id;
    public $tour_type;
    public $display;
    public $created_at;
    public $updated_at;

    const tour_type_All_tour = 0;
    const tour_type_India_tour = 1;
    const tour_type_World_tour = 2;
    const tour_type_Bunched_tour = 3;
    const tour_type_Free_tour = 4;
    const tour_type_Free_India_tour = 5;
    const tour_type_Free_World_tour = 6;

    const tour_type = [
        self::tour_type_All_tour => 'All Tour Tales', self::tour_type_India_tour => 'India Tour Tales', self::tour_type_World_tour => 'World Tour Tales', self::tour_type_Free_tour => 'Free Tour Tales'
    ];
    const tour_type_pdf = [
        self::tour_type_All_tour => 'All Tour Tales', self::tour_type_India_tour => 'India Tour Tales', self::tour_type_World_tour => 'World Tour Tales', self::tour_type_Free_tour => 'Free Tour Tales'
    ];
    const tour_type_text = [
        'all' => self::tour_type_All_tour,
        'indiatour' => self::tour_type_India_tour,
        'worldtour' => self::tour_type_World_tour,
        'freetour' => self::tour_type_Free_tour
    ];
    const tour_type_url = [
        self::tour_type_All_tour => 'all-tour-tales', self::tour_type_India_tour => 'india-tour', self::tour_type_World_tour => 'world-tour', self::tour_type_Free_tour => 'free-tour'
    ];
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->place_id = !empty($data['place_id']) ? $data['place_id'] : null;
        $this->city_id = !empty($data['city_id']) ? $data['city_id'] : null;
        $this->state_id = !empty($data['state_id']) ? $data['state_id'] : null;
        $this->country_id = !empty($data['country_id']) ? $data['country_id'] : null;
        $this->tour_type = !empty($data['tour_type']) ? $data['tour_type'] : null;
        $this->display = !empty($data['display']) ? $data['display'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}
