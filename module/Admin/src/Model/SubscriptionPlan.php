<?php

namespace Admin\Model;

class SubscriptionPlan
{
  public $id;
  public $plan_name;
  public $qsp_inr;
  public $qsp_usd;
  public $sqsp_inr;
  public $sqsp_usd;
  public $sqs_start_date;
  public $sqs_end_date;
  public $qrp_inr;
  public $qrp_usd;
  public $questt_duration;
  public $twistt_duration;
  public $topp_inr;
  public $topp_usd;
  public $stsp_inr;
  public $stsp_usd;
  public $sts_start_date;
  public $sts_end_date;
  public $tppp_inr;
  public $tppp_usd;
  public $max_pwds;
  public $dp_inr;
  public $dp_usd;
  public $tax;
  public $app_text;
  public $web_text;
  public $cd_percentage;
  public $active;

  const ActivePlans = '1';
  const InActivePlans = '2';
  const AllPlans = '0';

  public function exchangeArray(array $data)
  {
    $this->id = !empty($data['id']) ? $data['id'] : null;
    $this->plan_name = !empty($data['plan_name']) ? $data['plan_name'] : null;
    $this->qsp_inr  = !empty($data['qsp_inr']) ? $data['qsp_inr'] : null;
    $this->qsp_usd = !empty($data['qsp_usd']) ? $data['qsp_usd'] : null;
    $this->sqs_start_date = !empty($data['sqs_start_date']) ? $data['sqs_start_date'] : null;
    $this->sqs_end_date = !empty($data['sqs_end_date']) ? $data['sqs_end_date'] : null;
    $this->qrp_inr = !empty($data['qrp_inr']) ? $data['qrp_inr'] : null;
    $this->qrp_usd = !empty($data['qrp_usd']) ? $data['qrp_usd'] : null;
    $this->questt_duration = !empty($data['questt_duration']) ? $data['questt_duration'] : null;
    $this->twistt_duration = !empty($data['twistt_duration']) ? $data['twistt_duration'] : null;
    $this->topp_inr = !empty($data['topp_inr']) ? $data['topp_inr'] : null;
    $this->topp_usd = !empty($data['topp_usd']) ? $data['topp_usd'] : null;
    $this->max_pwds = !empty($data['max_pwds']) ? $data['max_pwds'] : null;
    $this->dp_inr = !empty($data['dp_inr']) ? $data['dp_inr'] : null;
    $this->dp_usd = !empty($data['dp_usd']) ? $data['dp_usd'] : null;
    $this->tax = !empty($data['tax']) ? $data['tax'] : null;
    $this->app_text = !empty($data['app_text']) ? $data['app_text'] : null;
    $this->web_text = !empty($data['web_text']) ? $data['web_text'] : null;
    $this->cd_percentage = !empty($data['cd_percentage']) ? $data['cd_percentage'] : null;
    $this->active = !empty($data['active']) ? $data['active'] : null;
  }
}
