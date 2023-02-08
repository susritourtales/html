<?php


namespace Admin\Model;


class TaConsPayments
{
    public $id;
    public $due_date;
    public $due_amt;
    public $ta_id;
    public $ta_cons_id;
    public $paid_date;
    public $paid_amt;
    public $trans_ref;
    public $status;

    // status types
    const status_due = 0;
    const status_paid = 1;
        
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->due_date = !empty($data['due_date']) ? $data['due_date'] : null;
        $this->due_amt = !empty($data['due_amt']) ? $data['due_amt'] : null;
        $this->ta_id = !empty($data['ta_id']) ? $data['ta_id'] : null;
        $this->ta_cons_id = !empty($data['ta_cons_id']) ? $data['ta_cons_id'] : null;
        $this->paid_date = !empty($data['paid_date']) ? $data['paid_date'] : null;
        $this->paid_amt = !empty($data['paid_amt']) ? $data['paid_amt'] : null;
        $this->trans_ref = !empty($data['trans_ref']) ? $data['trans_ref'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
    }
}