<?php
namespace Application\Handler;

class Aes
{
    public function encrypt($password){
        $hash = $this->generateHash();
        $iv = '1234567890123456';
        $encrypted = openssl_encrypt($password, 'aes-128-cbc', $hash, 0, $iv);
        $password = base64_encode($encrypted . '::' . $iv);

        return array(
            "password" => $password,
            "hash" => $hash
        );
    }
    public function decrypt($password , $hash){

        list($encrypted_data, $iv) = explode('::', base64_decode($password), 2);
             
        return openssl_decrypt($encrypted_data, 'aes-128-cbc', $hash, 0, $iv);
    }
    private function generateHash()
    {
        $hashLength = 16;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $hash = "";
        for ($i = 0; $i < $hashLength; $i++) 
        {
            $hash .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $hash;
    }

}