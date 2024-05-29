<?php
namespace Admin\Model;

class QuesttSubscription
{
    public $id;
    public $start_date;
    public $end_date;
    public $discount;
    public $price;
    public $user_id;
    public $notification_id;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->start_date = !empty($data['start_date']) ? $data['start_date'] : null;
        $this->end_date  = !empty($data['end_date']) ? $data['end_date'] : null;
        $this->discount = !empty($data['discount']) ? $data['discount'] : null;
        $this->price  = !empty($data['price']) ? $data['price'] : null;
        $this->user_id  = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->notification_id = !empty($data['notification_id']) ? $data['notification_id'] : null;
    }
}
?>