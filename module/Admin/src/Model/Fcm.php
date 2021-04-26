<?php


namespace Admin\Model;


class Fcm
{

    public $fcm_id;
    public  $fcm_token;
    public $device_id;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {

        $this->fcm_id = !empty($data['fcm_id']) ? $data['fcm_id'] : null;
        $this->fcm_token = !empty($data['fcm_token']) ? $data['fcm_token'] : null;
        $this->device_id = !empty($data['device_id']) ? $data['device_id'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}