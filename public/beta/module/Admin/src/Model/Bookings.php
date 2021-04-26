<?php


namespace Admin\Model;


class Bookings
{
    public $booking_id;
    public $booking_type;
    public $user_id;
    public $status;
    public $created_at;
    public $updated_at;
    const booking_By_Admin=0;
    const booking_by_User=1;
    const booking_Subscription=2;
    const booking_Sponsorship=3;
    const booking_Buy_Passwords=4;
    const booking_Sponsored_Subscription=5;

    const Status_Active=1;
    const Status_Ongoing=2;
    const Status_Expired=3;

      const payment_status_not_paid=0;
      const payment_status_paid=1;
      const payment_status_failed=2;
      const payment_status_payment_not_required = 3;

    const status=array(self::Status_Active=>'Active',self::Status_Ongoing=>'ongoing',self::Status_Expired=> 'expired' );

    public function exchangeArray(array $data)
    {
        $this->booking_id = !empty($data['booking_id']) ? $data['booking_id'] : null;
        $this->booking_type = !empty($data['booking_type']) ? $data['booking_type'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->place_ids = !empty($data['place_ids']) ? $data['place_ids'] : null;
        $this->tour_date = !empty($data['tour_date']) ? $data['tour_date'] : null;
        $this->sponsered_users = !empty($data['sponsered_users']) ? $data['sponsered_users'] : null;
        $this->no_of_days = !empty($data['no_of_days']) ? $data['no_of_days'] : null;
        $this->expiry_date = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}