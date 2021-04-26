<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 19/9/19
 * Time: 12:25 PM
 */

namespace Admin\Model;


class SeasonalSpecials
{
    public $seasonal_special_id;
    public $seasonal_name;
    public $tour_type;
    public $seasonal_description;
    public $place_ids;
    public $price;
    public $discount_price;
    public $status;
    public $created_at;
    public $updated_at;
    const seasonal_type_India_tour=1;
    const seasonal_type_World_tour=2;
    const seasonal_type_City_tour=3;
    const seasonal_type_Spiritual_tour=4;

    const seasonal_type=[self::seasonal_type_India_tour=>'india ',self::seasonal_type_World_tour=>'world ',self::seasonal_type_City_tour=>'city ',self::seasonal_type_Spiritual_tour=>'spiritual '];

    public function exchangeArray(array $data)
    {
        $this->seasonal_special_id = !empty($data['seasonal_special_id']) ? $data['seasonal_special_id'] : null;
        $this->seasonal_name = !empty($data['seasonal_name']) ? $data['seasonal_name'] : null;
        $this->tour_type = !empty($data['tour_type']) ? $data['tour_type'] : null;
        $this->seasonal_description = !empty($data['seasonal_description']) ? $data['seasonal_description'] : null;
        $this->place_ids = !empty($data['place_ids']) ? $data['place_ids'] : null;
        $this->price = !empty($data['price']) ? $data['price'] : null;
        $this->discount_price = !empty($data['discount_price']) ? $data['discount_price'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}