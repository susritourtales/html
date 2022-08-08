<?php

namespace Application\Channel;

use Laminas\View\Model\ViewModel;
use Laminas\Log\Logger;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class Sms
{   
    public function send($mobile, $template, $data)
    {
        try
        {
            if($data['action'] == "otp"){
                $content = $this->getContentFromTemplate($template, $data);
                $message = rawurlencode($content);
            }else {
                $content = $data['data']['text'];
                $message = $content;
            }
            // Account details
            $apiKey = urlencode('LYD9m1xiwRQ-aUZo0QDyAW4qpZZhVgYjBVjXhSvsmw');
            $test = false;
            // Message details
            $mobile = "917330781638";
            $numbers = array($mobile);
            $sender = urlencode('STTMSG');
            $numbers = implode(',', $numbers);
        
            // Prepare data for POST request
            $chdata = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message, "test"=>$test);
            //echo "\naction: " . $data['action'] . "\n message: " . $message;
            // Send the POST request with cURL
            $ch = curl_init('https://api.textlocal.in/send/');
            $url = 'https://api.textlocal.in/send/';
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $chdata);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            
            // Process your response here
            //echo $response;
            return $response; 
            
        }catch(\Exception $e)
        {
            return false;
        }
    }

    public function getContentFromTemplate($template, $data)
    {
        $view = new PhpRenderer();
        $view->getHelperPluginManager()->get('basePath')->setBasePath('');

        $resolver = new TemplatePathStack();
        $resolver->setPaths(array(
            'smsTemplate' => __DIR__ . '/../../view/sms',
            /*'mailTemplate' => __DIR__ . '/../../../../Application/view/email',*/
        ));
        $view->setResolver($resolver);

        $viewModel = new ViewModel();
        $viewModel->setTemplate($template)
            ->setVariables($data);

        return $view->render($viewModel);
    }

    public function sendold($mobile, $template, $data)
    {
        try
        {
            $content = $this->getContentFromTemplate($template, $data);
            echo "content: " . $content;
            // Account details
            $apiKey = urlencode('LYD9m1xiwRQ-aUZo0QDyAW4qpZZhVgYjBVjXhSvsmw');
            $test = true;
            // Message details
            $numbers = array($mobile);
            //$sender = urlencode('TXTLCL'); 
            $sender = urlencode('STTMSG');
            $message = rawurlencode($content);
            $numbers = implode(',', $numbers);
            
            // Prepare data for POST request
            $chdata = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message, "test"=>$test);
            echo "\naction: " . $data['action'] . "\n message: " . $message;
            // Send the POST request with cURL
            /* if($data['action'] == 'otp'){
                $ch = curl_init('https://api.textlocal.in/otp_send/');
                $url = 'https://api.textlocal.in/otp_send/';
            }
            else{ */
                $ch = curl_init('https://api.textlocal.in/send/');
                $url = 'https://api.textlocal.in/send/';
            /* } */
            //echo "url: " . $url . "\n";
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $chdata);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            
            // Process your response here
            //echo $response;
            return $response; 
            //return true;
            
        }catch(\Exception $e)
        {
            return false;
        }
    }
}

            /* */
            /* $url="http://api.smscountry.com/SMSCwebservice_bulk.aspx?User=Susri&passwd=susri@121&mobilenumber=".$mobile."&message=".urlencode($content)."&sid=STTMSG&mtype=N&DR=Y";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $output = curl_exec($ch);
            curl_close($ch);
            $output = str_replace(array("\n","\r","<br>"), '', $output);
            return true; */
