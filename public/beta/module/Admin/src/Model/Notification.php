<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 23/8/19
 * Time: 12:43 PM
 */

namespace Admin\Model;


class Notification
{
    public $notification_id;
    public $notification_text;
    public $notification_type;
    public $notification_recevier_id;
    public $notification_data_id;
    public $created_at;
    public $updated_at;


      const NOTIFICATION_TYPE_BOOKING_NOTIFICATION=1;
      const NOTIFICATION_TYPE_BOOKING_ACTIVATE=2;
      const NOTIFICATION_TYPE_DISCOUNT=2;

       const STATUS_UNREAD=1;
       const STATUS_READ=2;

    public function exchangeArray($data)
    {
        $this->notification_id = (!empty($data['notification_id'])) ? $data['notification_id'] : null;
        $this->notification_text = (!empty($data['notification_text'])) ? $data['notification_text'] : null;
        $this->notification_type = (!empty($data['notification_type'])) ? $data['notification_type'] : null;
        $this->notification_recevier_id = (!empty($data['notification_recevier_id'])) ? $data['notification_recevier_id'] : null;
        $this->notification_data_id = (!empty($data['notification_data_id'])) ? $data['notification_data_id'] : null;
        $this->created_at = (!empty($data['created_at'])) ? $data['created_at'] : null;
        $this->updated_at = (!empty($data['updated_at'])) ? $data['updated_at'] : null;
    }
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}