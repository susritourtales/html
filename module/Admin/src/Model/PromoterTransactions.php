<?php


namespace Admin\Model;


class PromoterTransactions
{
    public $id;
    public $promoter_id;
    public $transaction_type;
    public $sponsor_id;
    public $account_no;
    public $ifsc_code;
    public $transaction_date;
    public $amount;
    public $transaction_ref;
    public $created_at;
    public $updated_at;

    // transaction types
    const transaction_due = 0; // occurs when a sponsor under a promoter makes a password purchase
    const transaction_paid = 1; // occurs when stt pays the promoter

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->user_id = !empty($data['promoter_id']) ? $data['promoter_id'] : null;
        $this->ref_by = !empty($data['transaction_type']) ? $data['transaction_type'] : null;
        $this->ref_by = !empty($data['sponsor_id']) ? $data['sponsor_id'] : null;
        $this->ref_by = !empty($data['account_no']) ? $data['account_no'] : null;
        $this->ref_by = !empty($data['ifsc_code']) ? $data['ifsc_code'] : null;
        $this->ref_by = !empty($data['transaction_date']) ? $data['transaction_date'] : null;
        $this->ref_by = !empty($data['amount']) ? $data['amount'] : null;
        $this->ref_by = !empty($data['transaction_ref']) ? $data['transaction_ref'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}