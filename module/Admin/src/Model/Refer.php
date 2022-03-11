<?php


namespace Admin\Model;


class Refer
{
    public $id;
    public $user_id;
    public $ref_id;
    public $ref_by;
    public $ref_mobile;
    public $sponsor_status;
    public $pwds_purchased;
    public $created_at;
    public $updated_at;

    // sponsor statuses
    const sponsor_active = 1; // sponsor who is working actively under a promoter - default status
    const sponsor_successful = 2; // sponsor who bought 40 passwords
    const sponsor_terminated = 0; // sponsor who is terminated

    // discount
    const discount_50 = 1; // 50% discount
    const discount_0 = 0;  // no discount

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->ref_by = !empty($data['ref_id']) ? $data['ref_id'] : null;
        $this->ref_by = !empty($data['ref_by']) ? $data['ref_by'] : null;
        $this->ref_by = !empty($data['ref_mobile']) ? $data['ref_mobile'] : null;
        $this->ref_by = !empty($data['sponsor_status']) ? $data['sponsor_status'] : null;
        $this->ref_by = !empty($data['pwds_purchased']) ? $data['pwds_purchased'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}