<?php


namespace Application\Handler;
use Razorpay\Api\Api;

class Razorpay
{
     public $KeyId;
    public $KeySecret;
    public $api;

    /** Production Keys */
    const keyId = "rzp_live_8trOuK6q1abHxQ";
    const keySecret='AOOBstIE8U00XArEDLSYOy5F';

    /** Testing Keys **/
    /* const keyId= "rzp_test_dn58ZwDYwvA7U3";
    const keySecret= "HrVgTE3XS5LNMpD1dFdYoom4"; */

    public function __construct($endpoint=null)
    {


        /** Production Keys **/
        $this->KeyId= self::keyId;
        $this->KeySecret= self::keySecret;



        $this->api = new Api($this->KeyId, $this->KeySecret);

    }
    public function paymentRequestCreate($orderData)
    {
        try{
            $razorpayOrder = $this->api->order->create($orderData);
            return $razorpayOrder;
        }catch (\Exception $e)
        {
              return array();
        }

    }

    public function subscriptionRequestCreate($subscriptionData)
    {
        try{
            $razorpaySubscription = $this->api->subscription->create($subscriptionData);
            return $razorpaySubscription;
        }catch (\Exception $e)
        {
              return array();
        }

    }

    public function checkPaymentCaptureSecret($webhookBody,$webhookSignature) 
    {
        try
        {
            $secretKey = 'A1A768884C9D3443AAB5EEA5B974A';
            $fp = fopen(getcwd() . "/public/data/payment_response.txt" , 'w');
            fwrite($fp,json_encode(array('body'=>$webhookBody,'webhooksig'=>$webhookSignature,$secretKey)));
            fclose($fp);
            if ($this->api->utility->verifyWebhookSignature($webhookBody, $webhookSignature, $secretKey)) {
                return true;
            } else {
                return false;
            }
        }catch (\Exception $e)
        {
            return false;
        }

    }
    public function checkPaymentSignature($attributes)
    {
        try{
            $order = $this->api->utility->verifyPaymentSignature($attributes);
                return $order;
        }catch (\Exception $e)
        {
            return array();
        }
    }
}