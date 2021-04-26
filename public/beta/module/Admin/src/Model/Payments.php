<?php


namespace Admin\Model;


class Payments
{
    public $payment_id;
    public  $booking_id;
    public  $payment_request_id;
    public  $payment_response_id;
    public $status;
    public $created_at;
    public $updated_at;

      const payment_in_process=0;
      const payment_success=1;
      const payment_failed=2;
    public function exchangeArray(array $data)
    {
        $this->payment_id = !empty($data['payment_id']) ? $data['payment_id'] : null;
        $this->booking_id = !empty($data['booking_id']) ? $data['booking_id'] : null;
        $this->payment_response_id = !empty($data['payment_response_id']) ? $data['payment_response_id'] : null;
        $this->payment_request_id = !empty($data['payment_request_id']) ? $data['payment_request_id'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}