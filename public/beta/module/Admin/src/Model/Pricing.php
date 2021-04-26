<?php
 // added by Manjary for STT subscription version
namespace Admin\Model;

class Pricing
{
    public $id;
    public $planname;
    public $price;
    public $discount_price;
    public $usertype;
    public $tourtype;
    public $no_of_comp_pwds;
    public $no_of_days;
    public $maxtales;
    public $created_date;
    public $updated_date;
    public $start_date;
    public $end_date;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->planname = !empty($data['planname']) ? $data['planname'] : null;
        $this->price = !empty($data['price']) ? $data['price'] : null;
        $this->discount_price = !empty($data['discount_price']) ? $data['discount_price'] : null;
        $this->usertype = !empty($data['usertype']) ? $data['usertype'] : null;
        $this->tourtype = !empty($data['tourtype']) ? $data['tourtype'] : null;
        $this->no_of_comp_pwds = !empty($data['no_of_comp_pwds']) ? $data['no_of_comp_pwds'] : null;
        $this->no_of_days = !empty($data['no_of_days']) ? $data['no_of_days'] : null;
        $this->maxtales = !empty($data['maxtales']) ? $data['maxtales'] : null;
        $this->start_date = !empty($data['start_date']) ? $data['start_date'] : null;
        $this->end_date = !empty($data['end_date']) ? $data['end_date'] : null;
        $this->created_date = !empty($data['created_date']) ? $data['created_date'] : null;
        $this->updated_date = !empty($data['updated_date']) ? $data['updated_date'] : null;
    }
}