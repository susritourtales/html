<?php


namespace Admin\Model;


use Application\Handler\Aes;
use Application\Model\BaseTable;
use Laminas\Crypt\BlockCipher;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class PasswordTable  extends BaseTable
{

    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("p" => "password");
    }

    public function addPassword(Array $data)
    {
        try {
            $insert= $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e){
            return array("success" => false);
        }
    }
    public function addMutiplePasswords(Array $data)
    {
        try {
            return $this->multiInsert($data);
        } catch (\Exception $e) {
            echo 'password';
             print_r($e->getMessage());
            exit;
            return false;
        }
    }
    public function getPassword()
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","password","hash"));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $languages = array();

            foreach($resultSet as $row){
                $languages[] = $row;
            }

            return $languages;
        }catch(\Exception $e){
            return array();
        }
    }

    public function lastestPassword()
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","password","hash"))
                ->where(array('status'=>1));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();

            foreach($resultSet as $row){
                $user[] = $row;
            }

            return $user;
        }catch(\Exception $e){
            return array();
        }
    }
     public function updatePassword($data,$where)
     {
         try{
              return $this->update($data,$where);
         }catch (\Exception $e)
         {
             return false;
         }
     }
    public function checkPasswordForSubscription($data)
    {
        try
        {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","password","hash","password_expiry_date",'password_first_used_date','user_id'))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id = p.booking_id',array('booking_tour_id','no_of_days','expiry_date','sponsered_users'),Select::JOIN_LEFT)
                ->join(array('b'=>'bookings'),'b.booking_id=bt.booking_id',array('booking_id','tour_type'),Select::JOIN_LEFT)
                ->join(array('u'=>'users'),'u.user_id=p.user_id',array('subscription_end_date', 'subscription_count'),Select::JOIN_LEFT)
                ->where(array('p.status'=>1,'p.id'=>$data['password_id']))
            ->group(array('p.booking_id'));
            
            //echo $sql->getSqlStringForSqlObject($query);exit;

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();

            foreach($resultSet as $row)
            {
                $user[] = $row;
            }

            if (count($user))
            {
                $aes=new Aes();
                if ($user[0]['hash'] == "")
                {
                    return array();
                }

                $decryptedPassword = $aes->decrypt($user[0]['password'],$user[0]['hash']);

                if ($decryptedPassword == $data['password']) {
                    return $user;
                } else {
                    return array();
                }
            }else
            {
                return array();
            }

        }catch(\Exception $e){
             /* print_r($e->getMessage());
              exit;*/
            return array();
        }
    }

    public function checkPasswordForBooking($data)
    {
        try
        {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","password","hash",'password_first_used_date','password_expiry_date'))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id = p.booking_id',array('booking_tour_id'))
                ->where(array('p.status'=>1,'p.booking_id'=>$data['booking_id'],'p.user_id'=>$data['user_id'],'p.id'=>$data['password_id']));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();

            foreach($resultSet as $row){
                $user[] = $row;
            }

            if (count($user))
            {
               $aes=new Aes();
                if ($user[0]['hash'] == "") {
                    return array();
                }
               
                $decryptedPassword = $aes->decrypt($user[0]['password'],$user[0]['hash']);

                if ($decryptedPassword == $data['password'])
                {
                    return $user;
                } else {
                    return array();
                }
            } else
            {
                return array();
            }
        }catch(\Exception $e)
        {
                  /*print_r($e->getMessage());
                  exit;*/
            return array();
        }
    }
    public function checkPassword($password)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","password","hash"))
            ->where(array('status'=>1,'invalidated'=>false));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();

            foreach($resultSet as $row){
                $user[] = $row;
            }

            if (count($user))
            {
                $cipher = BlockCipher::factory('mcrypt',
                    array('algorithm' => 'aes')
                );
                if ($user[0]['hash'] == "") {
                    return array();
                }
                $cipher->setKey($user[0]['hash']);

                $decryptedPassword = $cipher->decrypt($user[0]['password']);

                if ($decryptedPassword == $password) {
                    return $user;
                } else {
                    return array();
                }
            } else
            {
                return array();
            }
        }catch(\Exception $e){

            return array();
        }
    }
    public function invalidatePasswords($userId){
        try {
            $updateQry = "update password p, booking_tour_details bt, bookings b set p.invalidated=1 where p.booking_id = bt.booking_id and p.booking_id = b.booking_id and p.user_id = 0 and b.booking_type=4 and b.user_id=".$userId;
            
            $adapter = $this->getAdapter();
            $statement = $adapter->query($updateQry);
            $result = $statement->execute();
            return $result;
        }catch (\Exception $e)
        {
            return false;
        }
    }

    public function getPasswordDetails($data, $redeemed){
        try {
            $sql=$this->getSql();
            $where=new Where();

            if($redeemed == "1")             
                $where->equalTo('b.booking_type',\Admin\Model\Bookings::booking_Buy_Passwords)->equalTo('b.payment_status',\Admin\Model\Payments::payment_success)->equalTo('b.user_id',$data['user_id'])->lessThanOrEqualTo('p.password_first_used_date', date("Y-m-d",strtotime(date("Y-m-d"))))->notEqualTo('password_first_used_date', date("Y-m-d", date("0000-00-00 00:00:00")));
            else if($redeemed == "0")
                $where->equalTo('b.booking_type',\Admin\Model\Bookings::booking_Buy_Passwords)->equalTo('b.payment_status',\Admin\Model\Payments::payment_success)->equalTo('b.user_id',$data['user_id'])->equalTo('invalidated',false)->equalTo('b.user_id',$data['user_id']);
               // $where->equalTo('b.booking_type',\Admin\Model\Bookings::booking_Buy_Passwords)->equalTo('b.user_id',$data['user_id'])->equalTo('password_first_used_date' ,date("Y-m-d",strtotime(date("0000-00-00 00:00:00")))); 
            
            
            if($redeemed == "1")
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("id","hash","password","created_at", "password_expiry_date",
                    "password_first_used_date","user_id", "sold"))
                    ->join(array('b'=>'bookings'),'b.booking_id=p.booking_id',array('booking_id','booking_type'))
                    ->join(array('u'=>'users'),'u.user_id=p.user_id',array('mobile','mobile_country_code'))
                    ->where($where)
                    ->order(array('created_at asc'))
                    /* ->limit($data['limit'])
                    ->offset($data['offset']) */;
                    //->where(array('b.booking_type'=>\Admin\Model\Bookings::booking_Buy_Passwords, 'b.user_id'=>$data['user_id']));
            else if($redeemed == "0")
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("id","hash","password","created_at", "password_expiry_date",
                    "password_first_used_date","user_id", "sold"))
                    ->join(array('b'=>'bookings'),'b.booking_id=p.booking_id',array('booking_id','booking_type'))
                    ->where($where)
                    ->where('(`password_first_used_date` IS NULL OR `password_first_used_date`="0000-00-00 00:00:00")')
                    ->order(array('created_at asc'))
                    /* ->limit($data['limit'])
                    ->offset($data['offset']) */;
            
            if($data['limit']!=-1) {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            }
            //echo $sql->getSqlStringForSqlObject($query); exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $passwords = array();
            $aes=new Aes();
            $i=0;
            foreach ($resultSet as $row)
            {
                $decryptedPassword = $aes->decrypt($row['password'],$row['hash']);
                $passwords[$i]['password'] = $decryptedPassword.$row['id'];
                $passwords[$i]['id'] = $row['id'];
                $passwords[$i]['created_at'] = $row['created_at'];
                $passwords[$i]['password_expiry_date'] = $row['password_expiry_date'];
                $passwords[$i]['password_first_used_date'] = $row['password_first_used_date'];
                $passwords[$i]['sold'] = $row['sold'];
                if($redeemed == "1")
                    $passwords[$i]['mobile'] = "+$row[mobile_country_code] $row[mobile]";
                $i++;
            }
            return $passwords;
        }catch (\Exception $e)
        {
            return array();
        }
    }
    public function checkPasswordWithUserId($userId,$password)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","password","hash"))
                ->where(array('status'=>1,'user_id'=>$userId));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();

            foreach($resultSet as $row){
                $user[] = $row;
            }

            if (count($user))
            {
                $aes=new Aes();
                if ($user[0]['hash'] == "") {
                    return array();
                }

                $decryptedPassword = $aes->decrypt($user[0]['password'],$user[0]['hash']);

                if ($decryptedPassword == $password) {
                    return $user[0];
                } else {
                    return array();
                }
            } else
            {
                return array();
            }
        }catch(\Exception $e){

            return array();
        }
    }
}