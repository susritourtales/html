<?php


namespace Admin\Model;


class TbeOldPasswords
{
    public $id;
    public $tbe_id;
    public $old_pwd;
    public $old_hash;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->tbe_id = !empty($data['tbe_id']) ? $data['tbe_id'] : null;
        $this->old_pwd = !empty($data['old_pwd']) ? $data['old_pwd'] : null;
        $this->old_hash = !empty($data['old_hash']) ? $data['old_hash'] : null;
    }
}