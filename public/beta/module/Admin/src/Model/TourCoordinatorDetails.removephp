<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 28/9/19
 * Time: 3:57 PM
 */

namespace Admin\Model;


class TourCoordinatorDetails
{
    public $coordinator_id;
    public $company_id;
    public $user_id;
    public $user_name;
    public $designation;
    public $mobile_number;
    public $coordinator_order;
    public $mobile_country_code;
    public $status;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {
        $this->coordinator_id = !empty($data['coordinator_id']) ? $data['coordinator_id'] : null;
        $this->company_id = !empty($data['company_id']) ? $data['company_id'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->user_name = !empty($data['user_name']) ? $data['user_name'] : null;
        $this->designation = !empty($data['designation']) ? $data['designation'] : null;
        $this->coordinator_order = !empty($data['coordinator_order']) ? $data['coordinator_order'] : null;
        $this->mobile_country_code = !empty($data['mobile_country_code']) ? $data['mobile_country_code'] : null;
        $this->mobile_number = !empty($data['mobile_number']) ? $data['mobile_number'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
    }
}