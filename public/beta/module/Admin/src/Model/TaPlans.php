<?php


namespace Admin\Model;


class TaPlans
{
    public $id;
    public $plan_name;
    public $mtc;
    public $duration;
    public $cost;
    public $active;
    
    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->plan_name = !empty($data['plan_name']) ? $data['plan_name'] : null;
        $this->mtc = !empty($data['mtc']) ? $data['mtc'] : null;
        $this->duration = !empty($data['duration']) ? $data['duration'] : null;
        $this->cost = !empty($data['cost']) ? $data['cost'] : null;
        $this->active = !empty($data['active']) ? $data['active'] : null;
    }
}