<?php
namespace Admin\Model;

class Enabler
{
    public $id;
    public $name;
    public $company_name;
    public $user_login_id;
    public $password;
    public $hash;
    public $email;
    public $mobile;
    public $country_phone_code;
    public $country;
    public $city;
    public $user_type_id;
    public $login_type;
    public $oauth_provider;
    public $oauth_provider_user_id;

    const Admin=1;
    const New_User=2;
    const QUESTT_Subscriber=3;
    const TWISTT_Subscriber=4;
    const TWISTT_Enabler=5;
    const TWISTT_Executive=6;
   
    const User_mobile_verify=0;
    const User_email_verfiy=1;
    const Is_user_verified=1;
    const Is_user_not_verified=0;

    const login_type_mobile = 'm';
    const login_type_email = 'e';
    const login_type_social = 's';

    const display_yes = '1';
    const display_no = '0';

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->company_name = !empty($data['company_name']) ? $data['company_name'] : null;
        $this->user_login_id  = !empty($data['user_login_id']) ? $data['user_login_id'] : null;
        $this->password = !empty($data['password']) ? $data['password'] : null;
        $this->hash  = !empty($data['hash']) ? $data['hash'] : null;
        $this->email = !empty($data['email']) ? $data['email'] : null;
        $this->mobile = !empty($data['mobile']) ? $data['mobile'] : null;
        $this->country_phone_code = !empty($data['country_phone_code']) ? $data['country_phone_code'] : null;
        $this->country = !empty($data['country']) ? $data['country'] : null;
        $this->city = !empty($data['city']) ? $data['city'] : null;
        $this->user_type_id = !empty($data['user_type_id']) ? $data['user_type_id'] : null;
        $this->oauth_provider = !empty($data['oauth_provider']) ? $data['oauth_provider'] : null;
        $this->oauth_provider_user_id = !empty($data['oauth_provider_user_id']) ? $data['oauth_provider_user_id'] : null;
    }
}
?>