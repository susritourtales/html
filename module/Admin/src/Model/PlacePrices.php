<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 28/8/19
 * Time: 5:14 PM
 */

namespace Admin\Model;


class PlacePrices
{
    public $place_price_id;
    public $password;
    public $tour_type;
    public $price;
    public $discount_price;
    public $status;
    public $created_at;
    public $updated_at;


    const tour_type_All_tour=0; // -- added and removed by Manjary
    const tour_type_India_tour=1;
    const tour_type_World_tour=2;
    const tour_type_City_tour=3;
    const tour_type_Spiritual_tour=4;
    const tour_type_Seasonal_special=5;

     const tour_type=[self::tour_type_All_tour=>'All Tour Tales', self::tour_type_India_tour=>'India Tour',self::tour_type_World_tour=>'World Tour',self::tour_type_City_tour=>'City Tour',self::tour_type_Spiritual_tour=>'Spiritual Tour',self::tour_type_Seasonal_special=>'Festival Tour'];
     const tour_type_pdf=[self::tour_type_All_tour=>'All Tour Tales',self::tour_type_India_tour=>'India Tour Tales',self::tour_type_World_tour=>'World Tour Tales',self::tour_type_City_tour=>'City Tour Tales',
         self::tour_type_Spiritual_tour=>'Spiritual Tour Tales',self::tour_type_Seasonal_special=>'Festival Tour Tales'];
    const tour_type_text=[
        'all'=>self::tour_type_All_tour,
        'indiatour'=>self::tour_type_India_tour,
        'worldtour'=>self::tour_type_World_tour,
        'citytour'=>self::tour_type_City_tour,
        'spiritualtour'=>self::tour_type_Spiritual_tour,
        'seasonalspecial'=>self::tour_type_Seasonal_special
    ];
    const tour_type_url=[self::tour_type_All_tour=>'all-tour-tales',self::tour_type_India_tour=>'india-tour',self::tour_type_World_tour=>'world-tour',self::tour_type_City_tour=>'city-tour',
        self::tour_type_Spiritual_tour=>'spiritual-tour',self::tour_type_Seasonal_special=>'seasonal-special'];
    public function exchangeArray(array $data)
    {
        $this->place_price_id = !empty($data['place_price_id']) ? $data['place_price_id'] : null;
        $this->place_id = !empty($data['place_id']) ? $data['place_id'] : null;
        $this->tour_type = !empty($data['tour_type']) ? $data['tour_type'] : null;
        $this->price = !empty($data['price']) ? $data['price'] : null;
        $this->discount_price = !empty($data['discount_price']) ? $data['discount_price'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}