<?php


namespace Admin\Model;


class Refer
{
    public $id;
    public $ref_by;
    public $user_id;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->ref_by = !empty($data['ref_by']) ? $data['ref_by'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}