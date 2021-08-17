<?php


namespace Admin\Model;


use Application\Handler\Aes;
use Application\Model\BaseTable;
use Laminas\Crypt\BlockCipher;
use Laminas\Db\Sql\Having;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Predicate;

use Laminas\Db\TableGateway\TableGateway;

class UserTable extends  BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("u" => "users");
    }

    public function addUser(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function verfiy($data)
    {
        try{
            $sql=$this->getSql();
            $values=array();
            $query = $sql->select()
                ->columns(array("user_id"))
                ->from(array("u"=>"users"))
                ->where($data);
            //  echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();

            foreach ($resultSet as $row){
                $values[]=$row;
            }
            if($values){
                return true;
            }else
            {
                return false;
            }

        }catch (\Exception $e){
            return false;
        }
    }

    public function checkMobileRegistred($data)
    {
        try {
            $where=new Where();
             //  $predicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting
            // $predicateSet->orPredicate(new Predicate\Expression('u.user_type =?', \Admin\Model\User::Tour_Operator_role));
            //  $predicateSet->orPredicate(new Predicate\Expression('u.user_type =?', \Admin\Model\User::Tour_coordinator_role));

            $where->equalTo('u.mobile_country_code',$data['mobile_country_code'])->equalTo('u.mobile',$data['mobile'])->notEqualTo('u.role',\Admin\Model\User::Individual_role);

            $sql=$this->getSql();
            $values=array();
            $query = $sql->select()
                ->columns(array("user_id",'role','company_role'))
                ->from(array("u"=>"users"))
                ->where($where);
          //  $query->where($predicateSet);

            //  echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values[]=$row;
            }
               return $values;

        }catch(\Exception $e) {

            return array();
        }
    }
    public function verfiyUser($data)
    {
        try {
            $sql=$this->getSql();
            $values=array();
            $where=new Where();
            $query = $sql->select()
                ->columns(array("user_id"))
                ->from(array("u"=>"users"))
                ->where($data);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values[]=$row;
            }
                return $values;
        }catch (\Exception $e) {
            return array();
        }
    }
    public function userDetails($data)
    {
        try {
            $sql=$this->getSql();
            $values=array();
            $where=new Where();
            $query = $sql->select()
                ->columns(array("user_id","hash","password","mobile","mobile_country_code"))
                ->from(array("u"=>"users"))
                ->where($data);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();

            foreach ($resultSet as $row)
            {
                $values[]=$row;
            }
            return $values;
        }catch (\Exception $e)
        {
            return array();
        }
    }
    public function isHashExists($hash)
    {
        try {

            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array('u' => 'users'))
                ->columns(array('user_id' => new \Laminas\Db\Sql\Expression('COUNT(user_id)')))
                ->where(array("hash" => $hash));

            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $count = 0;
            foreach ($result as $row) {
                $count = $row['user_id'];
            }
            if ($count) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    public function authenticateUser($username, $password='')
    {
        try {

            $isEmail = explode("@", $username);
            $column = "email";
            if (count($isEmail) == 1) {
                $column = "mobile";
            }

            $sql = $this->getSql();
            $user = array();
            $query = $sql->select()
                ->columns(array("user_id", "mobile_country_code",'email','user_name'))
                ->from(array('u' => 'users'))
                ->join(array('p'=>'password'),new \Laminas\Db\Sql\Expression('p.user_id=u.user_id and p.status= 1'),array('hash','password'))
                ->where(array('' . $column . '' => $username));


            $result = $sql->prepareStatementForSqlObject($query)->execute();

            foreach ($result as $row) {
                $user[] = $row;
            }

            if (count($user))
            {
                if($password!='')
                {
                    if ($user[0]['hash'] == "")
                    {
                        return array();
                    }
                    $aes = new Aes();
                     /*echo '<pre>';
                         print_r($user);
                            exit;*/
                    $decryptedPassword = $aes->decrypt($user[0]["password"], $user[0]["hash"]);

                    if ($decryptedPassword == $password) {
                        return $user[0];
                    } else {
                        return array();
                    }
                }else{
                    return array();
                }
            }else{
                return array();
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());exit;
            // $this->logger->err($e->getMessage());
            return array();
        }
    }
    public function checkUser($username, $password='')
    {
        try {

            $isEmail = explode("@", $username);
            $column = "email";
            if (count($isEmail) == 1) {
                $column = "mobile";
            }

            $sql = $this->getSql();
            $user = array();
            $query = $sql->select()
                ->columns(array("user_id","hash","password","mobile",
                    "mobile_country_code"))
                ->from(array('u' => 'users'))
                ->where(array('' . $column . '' => $username));


            $result = $sql->prepareStatementForSqlObject($query)->execute();

            foreach ($result as $row) {
                $user[] = $row;
            }

            if (count($user))
            {
                if($password!='')
                {
                  if ($user[0]['hash'] == "")
                 {
                    return array();
                  }
                $aes = new Aes();
                $decryptedPassword = $aes->decrypt($user[0]["password"], $user[0]["hash"]);

                if ($decryptedPassword == $password) {
                    return $user;
                } else {
                    return array();
                }
                }else{
                    return $user;
                }
            }else{
                return array();
            }
        } catch (\Exception $e) {
            //print_r($e->getMessage());exit;
            // $this->logger->err($e->getMessage());
            return array();
        }
    }
    public function getField($where, $column)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("" . $column . ""))
                ->where($where);

            $field = array();
            //echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row['' . $column . ''];
            }

            if ($field) {
                return $field;
            } else {
                return "";
            }

        } catch (\Exception $e) {

            return "";
        }
    }
    public function getFields($where, $column)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns($column )
                ->where($where);

            $field = array();
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row;
            }

             return $field;

        } catch (\Exception $e) {

            return array();
        }
    }
    public function checkUserWithMobile($mobileNumber,$countryCode,$password="")
    {
        try
        {
            $sql = $this->getSql();
            $user = array();
            $query = $sql->select()
                ->columns(array("user_id","hash","password","mobile",
                    "mobile_country_code"))
                ->from(array('u' => 'users'))
                ->where(array('mobile'=>$mobileNumber,'mobile_country_code'=>$countryCode));
              /*echo $sql->getSqlStringForSqlObject($query);exit;*/
            $result = $sql->prepareStatementForSqlObject($query)->execute();

            foreach ($result as $row) {
                $user[] = $row;
            }

            if (count($user))
            {
                if($password!=''){
                    if ($user[0]['hash'] == "")
                    {
                        return array();
                    }
                    $aes = new Aes();
                    $decryptedPassword = $aes->decrypt($user[0]["password"], $user[0]["hash"]);
                    // print_r($decryptedPassword);exit;
                    if ($decryptedPassword == $password) {
                        return $user;
                    } else {
                        return array();
                    }
                }else{
                    return $user;
                }
            }else
            {
                return array();
            }
        }catch(\Exception $e)
        {

            return array();
        }
    }
/*     // Removed by Manjary for STT subscription version - start
    public function checkTourOperatorWithMobile($mobileNumber,$countryCode,$password="")
    {
        try
        {
            $sql = $this->getSql();
            $user = array();

            $where=new Where();

            $where->equalTo('u.mobile',$mobileNumber)->and->equalTo('u.mobile_country_code',$countryCode);
            $checkMobile=$sql->select()
                ->columns(array('mobile','mobile_country_code','role','user_id','company_role'))
            ->from(array('u'=>'users'))
            ->where($where);
            $predicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting
            $checkMobile->where($predicateSet);


            $predicateSet->orPredicate(new Predicate\Expression('u.role = ? ', \Admin\Model\User::Tour_Operator_role ));
            $predicateSet->orPredicate(new Predicate\Expression('u.role = ? ', \Admin\Model\User::Tour_coordinator_role ));

            $result = $sql->prepareStatementForSqlObject($checkMobile)->execute();

            foreach ($result as $row) {
                $user = $row;
            }
             if(!count($user))
             {
                 return array();
             }
             $role=$user['company_role'];
               if($role == \Admin\Model\User::Tour_coordinator_role)
               {
                   $query=$sql->select()
                       ->columns(array('user_id','user_name','email'))
                   ->from(array('tc'=>'tour_coordinator_details'))
                       ->join(array('t'=>'tour_operator_details'),'t.tour_operator_id=tc.company_id',array('status'))
                       ->join(array('p'=>'password'),new \Laminas\Db\Sql\Expression('p.user_id=tc.user_id and p.password_type=1 and p.status=1'),array('hash','password'))

                       ->where(array('tc.user_id'=>$user['user_id']));
               }else
               {
                   $query=$sql->select()
                       ->columns(array('status','user_id','company_name','contact_person','email_id'))
                       ->from(array('t'=>'tour_operator_details'))
                       ->join(array('p'=>'password'),new \Laminas\Db\Sql\Expression('p.user_id=t.user_id and p.password_type=1 and p.status=1'),array('hash','password'))

                       ->where(array('t.user_id'=>$user['user_id']));
               }


            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $user=array();
            foreach ($result as $row) {
                $user[] = $row;
            }

            if (count($user))
            {

                    if ($user[0]['hash'] == "")
                    {
                        return array();
                    }
                    $aes = new Aes();
                       if($password)
                       {
                           $decryptedPassword = $aes->decrypt($user[0]["password"], $user[0]["hash"]);
                           if ($decryptedPassword == $password) {
                               $user[0]['company_role']=$role;
                               return $user[0];
                           } else {
                               return array();
                           }

                       }else{
                           $user[0]['company_role']=$role;
                           return $user[0];
                       }
            }else
            {
                return array();
            }
        }catch(\Exception $e)
        {

            return array();
        }
    } 
    Removed by Manjary for STT subscription version - end */
    public function getUserDetails($userId)
    {
        try
        {
            $sql = $this->getSql();
            $user = array();

            $checkMobile=$sql->select()
                ->columns(array('mobile','mobile_country_code','role','user_id','company_role'))
                ->from(array('u'=>'users'))
                ->where(array('u.user_id'=>$userId));
            $result = $sql->prepareStatementForSqlObject($checkMobile)->execute();

            foreach ($result as $row) {
                $user = $row;
            }
            if(!count($user))
            {
                return array();
            }
            $role=$user['role'];
            if($role == \Admin\Model\User::Tour_coordinator_role)
            {
                $query=$sql->select()
                    ->columns(array('user_name','email'))
                    ->from(array('tc'=>'tour_coordinator_details'))
                    ->join(array('t'=>'tour_operator_details'),'t.tour_operator_id=tc.company_id',array('company_name','discount_percentage','apply_discount','discount_days','discount_end_date'))
                    ->where(array('tc.user_id'=>$user['user_id']));
            }else
            {
                $query=$sql->select()
                    ->columns(array('discount_percentage','apply_discount','discount_days','discount_end_date','contact_person','email_id','company_name'))
                    ->from(array('t'=>'tour_operator_details'))
                    ->where(array('t.user_id'=>$user['user_id']));
            }


            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $user=array();
            foreach ($result as $row) {
                $user[] = $row;
            }

            if (count($user))
            {
                    $user[0]['company_role']=$role;
                    return $user[0];
            }else
            {
                return array();
            }

        }catch(\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return array();
        }
    }
    public function updateUser($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e){
            return false;
        }
    }
    
    public function newUser($data)
    {
        try{
             
            $insert = $this->insert($data);
            if($insert)
            {
                return array("success" => true,"user_id" => $this->tableGateway->lastInsertValue);
            }else
            {
                return array("success" => false);
            }
        }catch(\Exception $e){
                     print_r($e->getMessage());exit;
            return array("success" => false);
        }
    }

    public function getUserByUserId($userId){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array("u" => "users"))
                ->where(array("u.user_id" => $userId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $userData = array();
            foreach ($resultSet as $row){
                $userData[] = $row;
            }

            if(count($userData)){
                return $userData[0];
            }

            return $userData;

        }catch (\Exception $e){
            
            return array();
        }
    }
    public function getAllUsersAdmin($data=array('limit'=>10,'offset'=>0))
    {
        try{
            $sql = $this->getSql();
            $user = array();
            $where=new Where();
            //$where->equalTo('u.role',1);
            
            if(array_key_exists('user_name',$data))
            {
                $where->and->like("u.user_name",'%'.$data['user_name']."%");
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }
            if(array_key_exists('subscription_type',$data))
            {
                $where->and->like("u.subscription_type",'%'.$data['subscription_type']."%");
            }
            if(array_key_exists('bookings_count',$data))
            {
                $where->and->like("b.booking_count",'%'.$data['bookings_count']."%");
            }
            if(array_key_exists('booking_total_payment',$data))
            {
                $where->and->like("b.booking_price",'%'.$data['booking_total_payment']."%");
            }
            //$where->and->equalTo('b.booking_type',\Admin\Model\Bookings::booking_by_User);


            $order=array();
            if(array_key_exists('user_name_order',$data))
            {
                if($data['user_name_order']==1)
                {
                    $order[]='u.user_name asc';
                }else if($data['user_name_order']==-1)
                {
                    $order[]='u.user_name desc';
                }

            }
            if(array_key_exists('mobile_order',$data))
            {
                if($data['mobile_order']==1)
                {
                    $order[]='u.mobile asc';
                }else if($data['mobile_order']==-1)
                {
                    $order[]='u.mobile desc';
                }

            } if(array_key_exists('bookings_count_order',$data))
            {
                if($data['bookings_count_order']==1)
                {
                    $order[]='b.booking_count asc';
                }else if($data['bookings_count_order']==-1)
                {
                    $order[]='b.booking_count desc';
                }

            }
            if(array_key_exists('booking_total_payment_order',$data))
            {
                if($data['booking_total_payment_order']==1)
                {
                    $order[]='b.booking_price asc';
                }else if($data['booking_total_payment_order']==-1)
                {
                    $order[]='b.booking_price desc';
                }

            }
             if(!count($order))
             {
                 $order='u.created_at desc';
             }

            $bookingList=$sql->select()
                ->columns(array('booking_count'=>new \Laminas\Db\Sql\Expression('SUM(`b`.`booking_type`=1)'),'user_id',"booking_price"
               // ->columns(array('booking_count'=>new \Laminas\Db\Sql\Expression('COUNT(`b`.`booking_id`)'),'user_id',"booking_price"
                =>new \Laminas\Db\Sql\Expression('IF((discount_percentage=0),SUM(`bt`.`price`),SUM(`bt`.`discount_percentage`))')))
                ->from(array('b' => 'bookings'))
                ->join(array('bt'=>'booking_tour_details'),new \Laminas\Db\Sql\Expression('bt.booking_id=b.booking_id and (b.booking_type='.\Admin\Model\Bookings::booking_by_User . ' or (b.booking_type='.\Admin\Model\Bookings::booking_Subscription . ' and b.payment_status=1) or b.booking_type='.\Admin\Model\Bookings::booking_Sponsored_Subscription . ')'))
            ->group(array('b.user_id'));
            $query = $sql->select()
                ->columns(array("user_id","mobile","email","res_state","subscription_start_date",
                    "mobile_country_code","user_name","subscription_type"))
                ->from(array('u' => 'users'))
                ->join(array('b'=>$bookingList),'b.user_id=u.user_id',array('booking_count','booking_price'))
                ->where($where)
                ->limit($data['limit'])
                ->offset($data['offset'])
                ->group(array('u.user_id'))
                ->order($order);
            // echo $sql->getSqlStringForSqlObject($query);exit;
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $users=array();
            foreach ($result as $row) {
                $users[] = $row;
            }
            return $users;
        }catch (\Exception $e)
        {
              print_r($e->getMessage());
              exit;
            return array();
        }
    } 
    
    public function getAllUsersAdminCount($data=array()){
        try{
            $sql = $this->getSql();
            $where=new Where();
            //$where->equalTo('u.role',1);
            if(array_key_exists('user_name',$data))
            {
                $where->and->like("u.user_name",'%'.$data['user_name']."%");
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }
            if(array_key_exists('bookings_count',$data))
            {
                $where->and->like("b.booking_count",'%'.$data['bookings_count']."%");
            }
            if(array_key_exists('booking_total_payment',$data))
            {
                $where->and->like("b.booking_price",'%'.$data['booking_total_payment']."%");
            }

            $bookingList=$sql->select()
                ->columns(array('booking_count'=>new \Laminas\Db\Sql\Expression('SUM(`b`.`booking_type`=1)'),'user_id',"booking_price"
                //->columns(array('booking_count'=>new \Laminas\Db\Sql\Expression('COUNT(`b`.`booking_id`)'),'user_id',"booking_price"
                =>new \Laminas\Db\Sql\Expression('IF((discount_percentage=0),SUM(`bt`.`price`),SUM(`bt`.`discount_percentage`))')))
                ->from(array('b' => 'bookings'))
                ->join(array('bt'=>'booking_tour_details'),new \Laminas\Db\Sql\Expression('bt.booking_id=b.booking_id and (b.booking_type='.\Admin\Model\Bookings::booking_by_User . ' or (b.booking_type='.\Admin\Model\Bookings::booking_Subscription .' and b.payment_status=1) or b.booking_type='.\Admin\Model\Bookings::booking_Sponsored_Subscription . ')'))
                ->group(array('b.user_id'));

            $query = $sql->select()
                ->columns(array("user_id","mobile","email","res_state","subscription_start_date",
                    "mobile_country_code","user_name","subscription_type"))
                ->from(array('u' => 'users'))
                ->join(array('b'=>$bookingList),'b.user_id=u.user_id')
                ->where($where)
                ->group(array('u.user_id'))
                ->order('u.created_at desc');
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $users=array();
            foreach ($result as $row) {
                $users[] = $row;
            }

            return $users;
        }catch (\Exception $e)
        {
              /*print_r($e->getMessage());
              exit;*/
            return array();
        }
    }

    public function getAllSponsorsAdmin($data=array('limit'=>10,'offset'=>0))
    {
        try{
            $sql = $this->getSql();
            $user = array();
            $where=new Where();
            $where->equalTo('u.role',\Admin\Model\User::Sponsor_role);
            
            if(array_key_exists('user_name',$data))
            {
                $where->and->like("u.user_name",'%'.$data['user_name']."%");
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }
            if(array_key_exists('sponsor_type',$data))
            {
                $where->and->like("u.sponsor_type",'%'.$data['sponsor_type']."%");
            }
            if(array_key_exists('bookings_count',$data))
            {
                $where->and->like("b.booking_count",'%'.$data['bookings_count']."%");
            }
            if(array_key_exists('booking_total_payment',$data))
            {
                $where->and->like("b.booking_price",'%'.$data['booking_total_payment']."%");
            }

            $order=array();
            if(array_key_exists('user_name_order',$data))
            {
                if($data['user_name_order']==1)
                {
                    $order[]='u.user_name asc';
                }else if($data['user_name_order']==-1)
                {
                    $order[]='u.user_name desc';
                }

            }
            if(array_key_exists('mobile_order',$data))
            {
                if($data['mobile_order']==1)
                {
                    $order[]='u.mobile asc';
                }else if($data['mobile_order']==-1)
                {
                    $order[]='u.mobile desc';
                }

            } if(array_key_exists('bookings_count_order',$data))
            {
                if($data['bookings_count_order']==1)
                {
                    $order[]='b.booking_count asc';
                }else if($data['bookings_count_order']==-1)
                {
                    $order[]='b.booking_count desc';
                }

            }
            if(array_key_exists('booking_total_payment_order',$data))
            {
                if($data['booking_total_payment_order']==1)
                {
                    $order[]='b.booking_price asc';
                }else if($data['booking_total_payment_order']==-1)
                {
                    $order[]='b.booking_price desc';
                }

            }
             if(!count($order))
             {
                 $order='u.created_at desc';
             }

            $chkDate = date('Y-m-d', strtotime(date("y-m-d") . " -  " . $data['npcd'] . " days"));

            $bookingList=$sql->select()
                ->columns(array('booking_count'=>new \Laminas\Db\Sql\Expression('COUNT(`b`.`booking_id`)'),'user_id',"booking_price"
                =>new \Laminas\Db\Sql\Expression('SUM(`bt`.`price`)')))
                ->from(array('b' => 'bookings'))
                ->join(array('bt'=>'booking_tour_details'),new \Laminas\Db\Sql\Expression('bt.booking_id=b.booking_id and b.booking_type='.\Admin\Model\Bookings::booking_Buy_Passwords . ' and b.payment_status=' . \Admin\Model\Payments::payment_success))
            ->group(array('b.user_id'));
            /* $redeemed = $sql->select()
            ->columns(array('redeemed_count'=>new \Laminas\Db\Sql\Expression('COUNT(`p`.`id`)')))
            ->from(array('b' => 'bookings'))
            ->join(array('p'=>'password'),new \Laminas\Db\Sql\Expression('b.booking_id=p.booking_id and p.password_first_used_date!="0000-00-00 00:00:00"'))
        ->group(array('b.user_id')); */
            $query = $sql->select()
                ->columns(array("user_id","user_name","mobile","mobile_country_code","email","res_state","sponsor_reg_date","subscription_end_date",'sponsor_type','discount_percentage'))
                ->from(array('u' => 'users'))
                ->join(array('bo'=>'bookings'), 'bo.user_id=u.user_id')
                ->join(array('pc'=>'password'),new \Laminas\Db\Sql\Expression('pc.booking_id=bo.booking_id and pc.password_type=0 and bo.booking_type='. \Admin\Model\Bookings::booking_Buy_Passwords . ' and bo.payment_status=' . \Admin\Model\Payments::payment_success),array('ptcount'=>new \Laminas\Db\Sql\Expression('COUNT(`pc`.`id`)')),'left')
                ->join(array('b'=>$bookingList),'b.user_id=u.user_id',array('booking_count','booking_price'),'left')
                ->join(array('r'=>'refer'), 'r.user_id=u.user_id',array('ref_by','ref_mobile'),'left')
               // ->join(array('r'=>$redeemed),'b.user_id=u.user_id',array('redeemed_count'))
                ->where($where)
                ->limit($data['limit'])
                ->offset($data['offset'])
                ->group(array('u.user_id'))
                ->order($order);
            //echo $sql->getSqlStringForSqlObject($query);exit;
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $users=array();
            foreach ($result as $row) {
                $rqry = $sql->select()
                    ->columns(array('redeemed_count'=>new \Laminas\Db\Sql\Expression('COUNT(`p`.`id`)')))
                    ->from(array('b' => 'bookings'))
                    ->join(array('p'=>'password'),new \Laminas\Db\Sql\Expression('b.booking_id=p.booking_id and p.password_first_used_date!="0000-00-00 00:00:00" and b.user_id=' . $row['user_id']));
                $res = $sql->prepareStatementForSqlObject($rqry)->execute();
                $redeemed = array();
                foreach($res as $r){
                    $redeemed[] = $r;
                }
                $row['redeemed_count'] = $redeemed[0]['redeemed_count'];
                $rqry = $sql->select()
                    ->columns(array('pcount'=>new \Laminas\Db\Sql\Expression('COUNT(`pc`.`id`)')))
                    ->from(array('b' => 'bookings'))
                    ->join(array('pc'=>'password'),new \Laminas\Db\Sql\Expression('pc.booking_id=b.booking_id and pc.password_type=0  and pc.created_at > "'. $chkDate . '" and b.booking_type='. \Admin\Model\Bookings::booking_Buy_Passwords .' and b.user_id=' . $row['user_id'] . ' and b.payment_status=' . \Admin\Model\Payments::payment_success));
                //echo $sql->getSqlStringForSqlObject($rqry); echo "<br>";//exit;
                $pres = $sql->prepareStatementForSqlObject($rqry)->execute();
                $pcnt = array();
                foreach($pres as $pr){
                    $pcnt[] = $pr;
                }
                $row['pcount'] = $pcnt[0]['pcount'];
                $row['npcp'] = $data['npcp'];
                $users[] = $row;
            }
            return $users;
        }catch (\Exception $e)
        {
              print_r($e->getMessage());
              exit;
            return array();
        }
    }

    public function getAllSponsorsAdminCount($data=array())
    {
        try{
            $sql = $this->getSql();
            $user = array();
            $where=new Where();
            $where->equalTo('u.role',\Admin\Model\User::Sponsor_role);
            
            if(array_key_exists('user_name',$data))
            {
                $where->and->like("u.user_name",'%'.$data['user_name']."%");
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }
            if(array_key_exists('sponsor_type',$data))
            {
                $where->and->like("u.sponsor_type",'%'.$data['sponsor_type']."%");
            }
            if(array_key_exists('bookings_count',$data))
            {
                $where->and->like("b.booking_count",'%'.$data['bookings_count']."%");
            }
            if(array_key_exists('booking_total_payment',$data))
            {
                $where->and->like("b.booking_price",'%'.$data['booking_total_payment']."%");
            }

            $order=array();
            if(array_key_exists('user_name_order',$data))
            {
                if($data['user_name_order']==1)
                {
                    $order[]='u.user_name asc';
                }else if($data['user_name_order']==-1)
                {
                    $order[]='u.user_name desc';
                }

            }
            if(array_key_exists('mobile_order',$data))
            {
                if($data['mobile_order']==1)
                {
                    $order[]='u.mobile asc';
                }else if($data['mobile_order']==-1)
                {
                    $order[]='u.mobile desc';
                }

            } if(array_key_exists('bookings_count_order',$data))
            {
                if($data['bookings_count_order']==1)
                {
                    $order[]='b.booking_count asc';
                }else if($data['bookings_count_order']==-1)
                {
                    $order[]='b.booking_count desc';
                }

            }
            if(array_key_exists('booking_total_payment_order',$data))
            {
                if($data['booking_total_payment_order']==1)
                {
                    $order[]='b.booking_price asc';
                }else if($data['booking_total_payment_order']==-1)
                {
                    $order[]='b.booking_price desc';
                }

            }
             if(!count($order))
             {
                 $order='u.created_at desc';
             }

            $chkDate = date('Y-m-d', strtotime(date("y-m-d") . " -  " . $data['npcd'] . " days"));

            $bookingList=$sql->select()
                ->columns(array('booking_count'=>new \Laminas\Db\Sql\Expression('COUNT(`b`.`booking_id`)'),'user_id',"booking_price"
                =>new \Laminas\Db\Sql\Expression('SUM(`bt`.`price`)')))
                ->from(array('b' => 'bookings'))
                ->join(array('bt'=>'booking_tour_details'),new \Laminas\Db\Sql\Expression('bt.booking_id=b.booking_id and b.booking_type='.\Admin\Model\Bookings::booking_Buy_Passwords . ' and b.payment_status=' . \Admin\Model\Payments::payment_success))
            ->group(array('b.user_id'));
            
            $query = $sql->select()
                ->columns(array("user_id","user_name","mobile","mobile_country_code","email","res_state","sponsor_reg_date","subscription_end_date",'sponsor_type','discount_percentage'))
                ->from(array('u' => 'users'))
                ->join(array('bo'=>'bookings'), 'bo.user_id=u.user_id')
                ->join(array('pc'=>'password'),new \Laminas\Db\Sql\Expression('pc.booking_id=bo.booking_id and pc.password_type=0 and bo.booking_type='. \Admin\Model\Bookings::booking_Buy_Passwords . ' and bo.payment_status=' . \Admin\Model\Payments::payment_success),array('ptcount'=>new \Laminas\Db\Sql\Expression('COUNT(`pc`.`id`)')),'left')
                ->join(array('b'=>$bookingList),'b.user_id=u.user_id',array('booking_count','booking_price'),'left')
                ->join(array('r'=>'refer'), 'r.user_id=u.user_id',array('ref_by','ref_mobile'),'left')
                ->where($where)
                /* ->limit($data['limit'])
                ->offset($data['offset']) */
                ->group(array('u.user_id'))
                ->order($order);
            //echo $sql->getSqlStringForSqlObject($query);exit;
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $users=array();
            foreach ($result as $row) {
                $rqry = $sql->select()
                    ->columns(array('redeemed_count'=>new \Laminas\Db\Sql\Expression('COUNT(`p`.`id`)')))
                    ->from(array('b' => 'bookings'))
                    ->join(array('p'=>'password'),new \Laminas\Db\Sql\Expression('b.booking_id=p.booking_id and p.password_first_used_date!="0000-00-00 00:00:00" and b.user_id=' . $row['user_id']));
                $res = $sql->prepareStatementForSqlObject($rqry)->execute();
                $redeemed = array();
                foreach($res as $r){
                    $redeemed[] = $r;
                }
                $row['redeemed_count'] = $redeemed[0]['redeemed_count'];
                $rqry = $sql->select()
                    ->columns(array('pcount'=>new \Laminas\Db\Sql\Expression('COUNT(`pc`.`id`)')))
                    ->from(array('b' => 'bookings'))
                    ->join(array('pc'=>'password'),new \Laminas\Db\Sql\Expression('pc.booking_id=b.booking_id and pc.password_type=0  and pc.created_at > "'. $chkDate . '" and b.booking_type='. \Admin\Model\Bookings::booking_Buy_Passwords .' and b.user_id=' . $row['user_id'] . ' and b.payment_status=' . \Admin\Model\Payments::payment_success));
                //echo $sql->getSqlStringForSqlObject($rqry); echo "<br>";//exit;
                $pres = $sql->prepareStatementForSqlObject($rqry)->execute();
                $pcnt = array();
                foreach($pres as $pr){
                    $pcnt[] = $pr;
                }
                $row['pcount'] = $pcnt[0]['pcount'];
                $row['npcp'] = $data['npcp'];
                $users[] = $row;
            }
            return $users;
        }catch (\Exception $e)
        {
              print_r($e->getMessage());
              exit;
            return array();
        }
    }

    public function getAllSponsorsAdminCount_old($data=array()){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('u.role',\Admin\Model\User::Sponsor_role);
            if(array_key_exists('user_name',$data))
            {
                $where->and->like("u.user_name",'%'.$data['user_name']."%");
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }
            if(array_key_exists('bookings_count',$data))
            {
                $where->and->like("b.booking_count",'%'.$data['bookings_count']."%");
            }
            if(array_key_exists('booking_total_payment',$data))
            {
                $where->and->like("b.booking_price",'%'.$data['booking_total_payment']."%");
            }

            $bookingList=$sql->select()
                ->columns(array('booking_count'=>new \Laminas\Db\Sql\Expression('COUNT(`b`.`booking_id`)'),'user_id',"booking_price"
                =>new \Laminas\Db\Sql\Expression('IF((discount_percentage=0),SUM(`bt`.`price`),SUM(`bt`.`discount_percentage`))')))
                ->from(array('b' => 'bookings'))
                ->join(array('bt'=>'booking_tour_details'),new \Laminas\Db\Sql\Expression('bt.booking_id=b.booking_id and b.booking_type='.\Admin\Model\Bookings::booking_Buy_Passwords . ' and b.payment_status=' . \Admin\Model\Payments::payment_success))
                ->group(array('b.user_id'));

            $query = $sql->select()
                ->columns(array("user_id","mobile","email","res_state","subscription_start_date",
                    "mobile_country_code","user_name","subscription_type"))
                ->from(array('u' => 'users'))
                ->join(array('b'=>$bookingList),'b.user_id=u.user_id')
                ->where($where)
                ->group(array('u.user_id'))
                ->order('u.created_at desc');
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $users=array();
            foreach ($result as $row) {
                $users[] = $row;
            }

            return $users;
        }catch (\Exception $e)
        {
            return array();
        }
    }

    public function getAllUsers()
    {
        try{
            $sql = $this->getSql();
            $user = array();
            $query = $sql->select()
                ->columns(array("user_id",/* "hash","password", */"mobile",
                    "mobile_country_code"))
                ->from(array('u' => 'users'))
                ->order('created_at desc');
            /*echo $sql->getSqlStringForSqlObject($query);exit;*/
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $users=array();
            foreach ($result as $row) {
                $users[] = $row;
            }
            return $users;
        }catch (\Exception $e)
        {
            return array();
        }
    }

    public function getAllSubscribers()
    {
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('u.role',\Admin\Model\User::Subscriber_role);
            $where->or->equalTo('u.role',\Admin\Model\User::Sponsor_role);
            $user = array();
            $query = $sql->select()
                ->columns(array("user_id","mobile"))
                ->from(array('u' => 'users'))
                ->where($where)
                ->order('created_at desc');
            //echo $sql->getSqlStringForSqlObject($query);exit;

            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $users=array();
            foreach ($result as $row) {
                $users[] = $row;
            }
            return $users;
        }catch (\Exception $e)
        {
            return array();
        }
    }

    public function getRenewalUsers(){
        try{
            $sql = $this->getSql();
            $users = array();
            $where=new Where();
            //$where->equalTo('u.user_id', '241');
            $where->lessThan(new \Laminas\Db\Sql\Expression('DATEDIFF(`u`.`subscription_end_date`,NOW())'), 31);
            $where->and->greaterThan(new \Laminas\Db\Sql\Expression('DATEDIFF(`u`.`subscription_end_date`,NOW())'), 0);
            $where->and->equalTo('u.mobile_country_code', '91');
            $query = $sql->select()
                ->columns(array("user_id","user_name","email","role","subscription_end_date","mobile_country_code","mobile"))
                ->from(array('u' => 'users'))
                ->where($where);
            
            // echo $sql->getSqlStringForSqlObject($query);exit; 
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $users=array();
            foreach ($result as $row) {
                $users[] = $row;
            }
            /* $usersM = array();
            for($x = 0; $x < 2; $x++){
                $usersM[] = $users[0];
            }
            return $usersM; */ 
            //print_r($users);
            return $users;
        }catch (\Exception $e)
        {
            return array();
        }
    }
}