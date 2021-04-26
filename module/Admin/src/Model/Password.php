<?php


namespace Admin\Model;


class Password
{
    public $id;
    public $password;
    public $status;
    public $created_at;
    public $updated_at;

    const passowrd_type_booking=0;
    const passowrd_type_login=1;
    const passowrd_type_city_booking=2;
    const passowrd_type_bonus=3;
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->password = !empty($data['password']) ? $data['password'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}