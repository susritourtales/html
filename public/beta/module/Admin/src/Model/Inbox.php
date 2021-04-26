<?php


namespace Admin\Model;


class Inbox
{
    public $inbox_id;
    public  $fcm_token;
    public $device_id;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {
        $this->inbox_id = !empty($data['inbox_id']) ? $data['inbox_id'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->message = !empty($data['message']) ? $data['message'] : null;
        $this->reply = !empty($data['reply']) ? $data['reply'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}