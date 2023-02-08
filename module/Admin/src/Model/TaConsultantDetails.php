<?php


namespace Admin\Model;


class TaConsultantDetails
{
    public $id;
    public $name;
    public $mobile;
    public $address;
    public $bank_name;
    public $ifsc_code;
    public $bank_ac_no;
    public $pic;
    public $commission;
        
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->mobile = !empty($data['mobile']) ? $data['mobile'] : null;
        $this->address = !empty($data['address']) ? $data['address'] : null;
        $this->bank_name = !empty($data['bank_name']) ? $data['bank_name'] : null;
        $this->ifsc_code = !empty($data['ifsc_code']) ? $data['ifsc_code'] : null;
        $this->bank_ac_no = !empty($data['bank_ac_no']) ? $data['bank_ac_no'] : null;
        $this->pic = !empty($data['pic']) ? $data['pic'] : null;
        $this->commission = !empty($data['commission']) ? $data['commission'] : null;
    }
}