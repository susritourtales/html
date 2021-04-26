<?php


namespace Admin\Model;


class Ssc
{
    public $id;
    public $ssc;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->ssc = !empty($data['ssc']) ? $data['ssc'] : null;
    }
}