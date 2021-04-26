<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 27/9/19
 * Time: 4:22 PM
 */

namespace Admin\Model;


class BookingTourDetails
{
    public $booking_id;
    public $booking_tour_id;
    public $place_ids;
    public $tour_date;
    public $no_of_days;
    public $expiry_date;
    public $no_of_users;
    public $sponsered_users;
    public $status;

    const Status_Active=1;
    const Status_Ongoing=2;
    const Status_Expired=3;


    public function exchangeArray(array $data)
    {
        $this->booking_id = !empty($data['booking_id']) ? $data['booking_id'] : null;
        $this->booking_tour_id = !empty($data['booking_tour_id']) ? $data['booking_tour_id'] : null;
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