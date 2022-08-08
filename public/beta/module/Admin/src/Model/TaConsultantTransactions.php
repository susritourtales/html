<?php


namespace Admin\Model;


class TaConsultantTransactions
{
    public $id;
    public $ta_id;
    public $ta_cons_id;
    public $transaction_type;
    public $transaction_date;
    public $ifsc_code;
    public $bank_ac_no;
    public $pic_counter;
    public $amount;
    public $transaction_ref;

    // transaction types
    const transaction_due = 0; // occurs when a ta under a consultant makes a plan purchase
    const transaction_paid = 1; // occurs when stt pays the consultant
        
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->ta_id = !empty($data['ta_id']) ? $data['ta_id'] : null;
        $this->ta_cons_id = !empty($data['ta_cons_id']) ? $data['ta_cons_id'] : null;
        $this->transaction_type = !empty($data['transaction_type']) ? $data['transaction_type'] : null;
        $this->transaction_date = !empty($data['transaction_date']) ? $data['transaction_date'] : null;
        $this->ifsc_code = !empty($data['ifsc_code']) ? $data['ifsc_code'] : null;
        $this->bank_ac_no = !empty($data['bank_ac_no']) ? $data['bank_ac_no'] : null;
        $this->pic_counter = !empty($data['pic_counter']) ? $data['pic_counter'] : null;
        $this->amount = !empty($data['amount']) ? $data['amount'] : null;
        $this->transaction_ref = !empty($data['transaction_ref']) ? $data['transaction_ref'] : null;
    }
}