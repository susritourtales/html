<?php


namespace Admin\Model;


class TaPurchases
{
    public $id;
    public $ta_id;
    public $ta_plan_id;
    public $dop;
    public $upc;
    public $ta_cons_id;
    public $ta_plan_cost;
    public $ta_cons_due_amt;
    public $ta_cons_latest_due_dt	;
    public $ta_cons_paid_amt;
    public $ta_cons_latest_paid_dt;
    public $trigger_payment;
    public $tourists_count;
        
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->ta_id = !empty($data['ta_id']) ? $data['ta_id'] : null;
        $this->ta_plan_id = !empty($data['ta_plan_id']) ? $data['ta_plan_id'] : null;
        $this->dop = !empty($data['dop']) ? $data['dop'] : null;
        $this->upc = !empty($data['upc']) ? $data['upc'] : null;
        $this->ta_cons_id = !empty($data['ta_cons_id']) ? $data['ta_cons_id'] : null;
        $this->ta_plan_cost = !empty($data['ta_plan_cost']) ? $data['ta_plan_cost'] : null;
        $this->ta_cons_due_amt = !empty($data['ta_cons_due_amt']) ? $data['ta_cons_due_amt'] : null;
        $this->ta_cons_latest_due_dt	 = !empty($data['ta_cons_latest_due_dt	']) ? $data['ta_cons_latest_due_dt	'] : null;
        $this->ta_cons_paid_amt = !empty($data['ta_cons_paid_amt']) ? $data['ta_cons_paid_amt'] : null;
        $this->ta_cons_latest_paid_dt = !empty($data['ta_cons_latest_paid_dt']) ? $data['ta_cons_latest_paid_dt'] : null;
        $this->trigger_payment = !empty($data['trigger_payment']) ? $data['trigger_payment'] : null;
        $this->tourists_count = !empty($data['tourists_count']) ? $data['tourists_count'] : null;
    }
}