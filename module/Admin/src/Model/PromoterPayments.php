<?php


namespace Admin\Model;


class PromoterPayments
{
    public $id;
    public $due_date;
    public $due_amount;
    public $promoter_id;
    public $sponsor_id;
    public $paid_date;
    public $paid_amount;
    public $transaction_ref;
    public $status;
    public $created_at;
    public $updated_at;

    // status types
    const status_due = 0;
    const status_paid = 1;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->ref_by = !empty($data['due_date']) ? $data['due_date'] : null;
        $this->ref_by = !empty($data['due_amount']) ? $data['due_amount'] : null;
        $this->user_id = !empty($data['promoter_id']) ? $data['promoter_id'] : null;
        $this->ref_by = !empty($data['sponsor_id']) ? $data['sponsor_id'] : null;
        $this->ref_by = !empty($data['paid_date']) ? $data['paid_date'] : null;
        $this->ref_by = !empty($data['paid_amount']) ? $data['paid_amount'] : null;
        $this->ref_by = !empty($data['transaction_ref']) ? $data['transaction_ref'] : null;
        $this->ref_by = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}