<?php

namespace Application\Channel;

use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class Sms
{   
    public function send($mobile, $template, $data)
    {
        try
        {
            if($data['action'] == "TEn_Registration_Otp" || $data['action'] == "TEx_Registration_Otp" || $data['action'] == "TEn_Password_Reset_Otp" || $data['action'] == "TEx_Password_Reset_Otp"){
                $content = $this->getContentFromTemplate($template, $data);
                $message = rawurlencode($content);
            }else {
                $content = $data['text'];
                $message = $content;
            }
            // Account details
            $apiKey = urlencode('LYD9m1xiwRQ-aUZo0QDyAW4qpZZhVgYjBVjXhSvsmw');
            $test = false;
            // Message details
            $numbers = array($mobile);
            $sender = urlencode('SSTRTL');
            $numbers = implode(',', $numbers);
        
            // Prepare data for POST request
            $chdata = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message, "test"=>$test, "receipt_url" => $data['receipt_url']);
            //echo "\n action: " . $data['action'] . "\n message: " . $message;
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
}