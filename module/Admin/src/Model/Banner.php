<?php


namespace Admin\Model;


class Banner
{
    public $banner_id;
    public $image_path;
    public $display;
    public $created_at;
    public $updated_at;
    public function exchangeArray(array $data)
    {
        $this->banner_id = !empty($data['id']) ? $data['id'] : null;
        $this->image_path = !empty($data['image_path']) ? $data['image_path'] : null;
         $this->display = !empty($data['display']) ? $data['display'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}