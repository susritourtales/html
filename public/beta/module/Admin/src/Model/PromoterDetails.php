<?php


namespace Admin\Model;


class PromoterDetails
{
    public $id;
    public $user_id;
    public $permanent_addr;
    public $bank_name;
    public $ifsc_code;
    public $bank_ac_no;
    public $terms_accepted;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->permanent_addr = !empty($data['permanent_addr']) ? $data['permanent_addr'] : null;
        $this->bank_name = !empty($data['bank_name']) ? $data['bank_name'] : null;
        $this->ifsc_code = !empty($data['ifsc_code']) ? $data['ifsc_code'] : null;
        $this->bank_ac_no = !empty($data['bank_ac_no']) ? $data['bank_ac_no'] : null;
        $this->terms_accepted = !empty($data['terms_accepted']) ? $data['terms_accepted'] : 0;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}