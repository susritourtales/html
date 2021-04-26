<?php


namespace Admin\Model;


class Banners
{
    public $banner_id;
    public $image_path;
    public $status;
    public $created_at;
    public $updated_at;
    public function exchangeArray(array $data)
    {
        $this->banner_id = !empty($data['banner_id']) ? $data['banner_id'] : null;
        $this->image_path = !empty($data['image_path']) ? $data['image_path'] : null;
         $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}