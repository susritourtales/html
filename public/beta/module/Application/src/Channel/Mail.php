<?php

namespace Application\Channel;

use Laminas\View\Model\ViewModel;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Part as MimePart;
use Laminas\Log\Logger;
use Laminas\Mime\Mime;

class Mail
{

    protected $transport;
    protected $logger;

    public function __construct(SmtpTransport $transport)
    {

        $this->transport = $transport;
        /*$this->logger = $logger;*/
    }

    public function send($from, $to, $subject, $template, $data ,$filePath = array(),$attach = array(),$filename = "")
    {
        try {
            echo '$from, $to, $subject, $template, $data ,$filePath, $attach,$filename';
            $content = $this->getContentFromTemplate($template, $data);
            $body = $this->getMailBodyFromHtml($content,$attach,$filename,$filePath,$data);
            $fromName = "Susri tour tales";
            if(isset($data['sender'])){
                if(trim($data['sender']) != ""){
                    $fromName = $data['sender'];
                }
            }

            $message = new Message();
            $message->addTo($to)
                ->setFrom($from,$fromName)
                // ->setSender($from, "Votocrat")
                ->setSubject($subject)
                ->setBody($body)
                ->setEncoding('UTF-8');
            if(isset($data['ccTo']))
            {
                if(count($data['ccTo'])){
                    $message->addCc($data['ccTo']);
                }
            }
            if(isset($data['bcc']))
            {
                if(count($data['bcc'])){
                    $message->addBcc($data['bcc']);
                }
            }
            if(isset($data['replay'])){
                if(count($data['replay'])){
                    $message->addReplyTo($data['replay']);
                }
            }
            $message->getHeaders()->get('content-type')->setType('multipart/alternative');

          $this->transport->send($message);
            return true;
        } catch (\Exception $e) {
            /*$this->logger->err('Error in sending Mail: ' . $e->getMessage());*/
            print_r($e->getMessage());

             exit;
            return false;
        }
    }

    public function getContentFromTemplate($template, $data)
    {
        $view = new PhpRenderer();
        $view->getHelperPluginManager()->get('basePath')->setBasePath('');
        $resolver = new TemplatePathStack();
        $resolver->setPaths(array(
            'mailTemplate' => __DIR__ . '/../../view/email',
            //'mailTemplate' => __DIR__ . '/../../../../Application/view/email',
        ));
        $view->setResolver($resolver);
        $viewModel = new ViewModel();
        $viewModel->setTemplate($template)
            ->setVariables($data);
        return $view->render($viewModel);
    }

    public function getMailBodyFromHtml($content, $attach,$filename,$filePath,$data)
    {
        $html = new MimePart($content);
        $html->type = "text/html";
        $text = new MimePart(strip_tags(str_replace(array("<br />", "<br/>", "<br>"), '\r\n', $content)));
        $text->type = "text/plain";
        $body = new MimeMessage();
        if (count($attach)||count($filePath)) {
            $alternatives = new MimeMessage();
            $alternatives->setParts(array($text, $html));
            $alternativesPart = new MimePart($alternatives->generateMessage());
            $alternativesPart->type = "multipart/alternative;\n boundary=\"" . $alternatives->getMime()->boundary() . "\"";
            $body->addPart($alternativesPart);
        }else{
            $body->setParts(array($text, $html));
        }
        if(count($attach)){
            if ($attach['size'] != 0) {
                $attachment = new MimePart( file_get_contents($attach['tmp_name']) );
                $attachment->type = Mime::TYPE_OCTETSTREAM;
                $attachment->filename = basename($attach['tmp_name']);
                $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding = Mime::ENCODING_BASE64;
                $body->addPart($attachment);
            }
        }else
            if(count($filePath)){
                foreach($filePath as $row){
                    $attachment = new MimePart( file_get_contents($row) );
                    $attachment->type = Mime::TYPE_OCTETSTREAM;
                    $attachment->filename = basename($row);
                    $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                    $attachment->encoding = Mime::ENCODING_BASE64;
                    $body->addPart($attachment);
                }
            }
        return $body;
    }
}
