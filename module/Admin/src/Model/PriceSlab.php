<?php

namespace Admin\Model;


class PriceSlab
{
    public $price_slab_id;
    public $price_days;
    public $price;
    public $status;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {
        $this->price_slab_id = !empty($data['price_slab_id']) ? $data['price_slab_id'] : null;
        $this->price = !empty($data['price']) ? $data['price'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}