<?php


namespace Application\Handler;


class Instamojo
{

    protected $curl;


    // live

    /* protected $endpoint = 'https://api.instamojo.com/';
     protected $api_key = 'N978EV43wRdrp60dn4sTncWHHlz1JlcY0ib3moYs';
     protected $api_secret = 'u2EupimvatDdV9DQOkDPsaav6CBz57zqzfLX6wkBwz6tE2VpmEY6a9AblB35sRkn2KBcyBuHfJro0h7AOMOCr2WLYfxcgOkVn2TLoMREH7gQmX8qbSEdccswjQvMnfgU';*/


    // test
      protected $endpoint = 'https://test.instamojo.com/';
      protected $api_key = 'test_eMB5oJfYYQowmBXSviVWfprOODIRa9h4Coo';
       protected $api_secret = 'test_CXPNCI2QuQdBxuqDYN0Gm7Zouo22kUaIhmb9Yktd0kqW5ozFUvEe6bKEBOiYRCbqVf9AZvryas8Zyj0NTIk5iSGuOx3PATWuPKkyJ2AiKv988KFXQ57MzrfjukW';



    protected $auth_token = null;


    /**
     *
     * @param string $api_key
     * @param string $auth_token is available on the d
     * @param string $endpoint can be set if you are working on an alternative server.
     * @return array AuthToken object.
     *
     */

    public function __construct($endpoint=null)
    {
        if(!is_null($endpoint))
        {
            $this->endpoint = (string) $endpoint;
        }
        $this->auth();
    }
    /**
     * @return array headers with Authentication tokens added
     */
    private function build_curl_headers()
    {
        $headers = array("Authorization: Bearer $this->auth_token");
        return $headers;
    }

    /**
     * @param string $path
     * @return string adds the path to endpoint with.
     */
    private function build_api_call_url($path)
    {

        if (strpos($path, '/?') === false and strpos($path, '?') === false)
        {
            return $this->endpoint . $path . '/';
        }
        return $this->endpoint . $path;
    }
    public function auth()
    {
       $args = array('grant_type'=> 'client_credentials',
                   'client_id'=> $this->api_key,
                   'client_secret'=>$this->api_secret);
        $response = $this->api_call('POST', 'oauth2/token', $args);

        $this->auth_token = $response['access_token'];

        return $this->auth_token;
    }

    public function paymentRequestCreate(array $payment_request)
    {
       try{
           //   $payment_request['redirect_url']='https://test.instamojo.com/integrations/android/redirect/';
               $payment_request['send_email']=false;
               $payment_request['allow_repeated_payments'] = false;

           $response = $this->api_call('POST', 'v2/payment_requests', $payment_request);
           return $response;

       }catch (\Exception $e)
       {
           echo $e->getMessage();
           exit;
       }

    }
    public function paymentOrderCreate(array $payment_request){
       try{

           $response = $this->api_call('POST', 'v2/gateway/orders/payment-request', $payment_request);
           return $response;

       }catch (\Exception $e)
       {
           /*echo $e->getMessage();
           exit;*/
       }
     }

    private function api_call($method, $path, array $data=null)
     {
      try
      {
          $path = (string) $path;
          $method = (string) $method;
          $data = (array) $data;

           $headers = $this->build_curl_headers();

          $request_url = $this-> build_api_call_url($path);

          $options = array();
          $options[CURLOPT_HTTPHEADER] = $headers;
          $options[CURLOPT_RETURNTRANSFER] = true;

          if($method == 'POST')
          {
              $options[CURLOPT_POST] = 1;
              $options[CURLOPT_POSTFIELDS] = http_build_query($data);

          } else if($method == 'DELETE')
          {

              $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';

          } else if($method == 'PATCH')
          {

              $options[CURLOPT_POST] = 1;
              $options[CURLOPT_POSTFIELDS] = http_build_query($data);
              $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';

          } else if ($method == 'GET' or $method == 'HEAD')
          {
              if (!empty($data))
              {
                  /* Update URL to container Query String of Paramaters */
                  $request_url .= '?' . http_build_query($data);
              }
          }
          // $options[CURLOPT_VERBOSE] = true;
          $options[CURLOPT_URL] = $request_url;
          // $options[CURLOPT_SSL_VERIFYPEER] = true;
          // $options[CURLOPT_CAINFO] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cacert.pem';

          $this->curl = curl_init();
          $setopt = curl_setopt_array($this->curl, $options);
          $response = curl_exec($this->curl);
          $headers = curl_getinfo($this->curl);

          $error_number = curl_errno($this->curl);
          $error_message = curl_error($this->curl);


          $response_obj = json_decode($response, true);

          if($error_number != 0)
          {

              if($error_number == 60)
              {
                  throw new \Exception("Something went wrong. cURL raised an error with number: $error_number and message: $error_message. " .
                      "Please check http://stackoverflow.com/a/21114601/846892 for a fix." . PHP_EOL);
              }else
              {
                  throw new \Exception("Something went wrong. cURL raised an error with number: $error_number and message: $error_message." . PHP_EOL);
              }
          }
          /*var_dump($response_obj);
exit;*/

          return $response_obj;
       }catch (\Exception $e)
      {
          print_r($e->getMessage());
          exit;
      }

    }
}