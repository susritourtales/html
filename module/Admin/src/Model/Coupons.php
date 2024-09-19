<?php
namespace Admin\Model;

class Coupons
{
    public $id;
    public $executive_id;
    public $purchase_id;
    public $coupon_type;
    public $coupon_code;
    public $validity_end_date;
    public $enabler_id;
    public $user_id;
    public $coupon_status;
    public $created_at;
    public $updated_at;

    const Coupon_Type_Discount = 'D';
    const Coupon_Type_Complimentary = 'C';
    const Coupon_Status_Active = '0';
    const Coupon_Status_Redeemed = '1';
    const Coupon_Status_All = '2';



    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->executive_id = !empty($data['executive_id']) ? $data['executive_id'] : null;
        $this->purchase_id  = !empty($data['purchase_id']) ? $data['purchase_id'] : null;
        $this->coupon_type = !empty($data['coupon_type']) ? $data['coupon_type'] : null;
        $this->coupon_code  = !empty($data['coupon_code']) ? $data['coupon_code'] : null;
        $this->validity_end_date = !empty($data['validity_end_date']) ? $data['validity_end_date'] : null;
        $this->enabler_id = !empty($data['enabler_id']) ? $data['enabler_id'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->coupon_status = !empty($data['coupon_status']) ? $data['coupon_status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}
?>