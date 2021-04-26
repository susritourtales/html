<?php

namespace Admin\Model;


class TourOperatorDetails
{
    public $tour_operator_id;
    public $user_id;
    public $company_name;
    public $contact_person;
    public $pan_number;
    public $registration_certificate;
    public $status;
    public $created_at;
    public $updated_at;

    const Pending_status=1;
    const Approved_status=2;
    const Rejected_status=3;

    const apply_discount_not_applicable=0;
    const apply_discount_applicable=1;

    const status=array(self::Pending_status=>'pending',self::Approved_status=>'approved',self::Rejected_status=>'rejected');

    public function exchangeArray(array $data)
    {
        $this->tour_operator_id = !empty($data['tour_operator_id']) ? $data['tour_operator_id'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->company_name = !empty($data['company_name']) ? $data['company_name'] : null;
        $this->contact_person = !empty($data['contact_person']) ? $data['contact_person'] : null;
        $this->pan_number = !empty($data['pan_number']) ? $data['pan_number'] : null;
        $this->registration_certificate = !empty($data['registration_certificate']) ? $data['registration_certificate'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}