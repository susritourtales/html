<?php


namespace Admin\Model;


class SeOldPasswords
{
    public $id;
    public $se_id;
    public $old_pwd;
    public $old_hash;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->se_id = !empty($data['se_id']) ? $data['se_id'] : null;
        $this->old_pwd = !empty($data['old_pwd']) ? $data['old_pwd'] : null;
        $this->old_hash = !empty($data['old_hash']) ? $data['old_hash'] : null;
    }
}