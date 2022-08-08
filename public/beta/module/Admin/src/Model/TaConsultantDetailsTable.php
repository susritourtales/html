<?php


namespace Admin\Model;

use Application\Handler\Aes;
use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TaConsultantDetailsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("tc" => "ta_consultant_details");
    }

    public function addTaConsultant(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }
   public function setTaConsultantDetails($data,$where)
   {
        try{
            return $this->update($data, $where);
        }catch (\Exception $e)
        {
            return false;
        }
   } 
   public function getTaConsultantDetails($tacId)
   {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->where(array("tc.id" => $tacId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $conData = array();
            foreach ($resultSet as $row){
                $conData[] = $row;
            }

            if(count($conData)){
                return $conData[0];
            }

            return $conData;

        }catch (\Exception $e){
            
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
            //echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row;
            }

             return $field;

        } catch (\Exception $e) {

            return array();
        }
    }

    public function getTaConsAdmin($data=array('limit'=>10,'offset'=>0), $gtc=0){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('status',1);
            $order=array();
            
            if(array_key_exists('name',$data))
            {
                $where->and->like("tc.name",'%'.$data['name']."%");
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("tc.mobile",'%'.$data['mobile']."%");
            }
            if(array_key_exists('email',$data))
            {
                $where->and->like("tc.email",'%'.$data['email']."%");
            }
            if(array_key_exists('address',$data))
            {
                $where->and->like("tc.address",'%'.$data['address']."%");
            }
            if(array_key_exists('bank_name',$data))
            {
                $where->and->like("tc.bank_name",'%'.$data['bank_name']."%");
            }
            if(array_key_exists('ifsc_code',$data))
            {
                $where->and->like("tc.ifsc_code",'%'.$data['ifsc_code']."%");
            }
            if(array_key_exists('bank_ac_no',$data))
            {
                $where->and->like("tc.bank_ac_no",'%'.$data['bank_ac_no']."%");
            }
            if(array_key_exists('pic',$data))
            {
                $where->and->like("tc.pic",'%'.$data['pic']."%");
            }
            if(array_key_exists('commission',$data))
            {
                $where->and->like("tc.commission",'%'.$data['commission']."%");
            }

            if(array_key_exists('name_order',$data))
            {
                if($data['name_order']==1)
                {
                    $order[]='tc.name asc';
                }else if($data['name_order']==-1)
                {
                    $order[]='tc.name desc';
                }
            }
            if(array_key_exists('mobile_order',$data))
            {
                if($data['mobile_order']==1)
                {
                    $order[]='tc.mobile asc';
                }else if($data['mobile_order']==-1)
                {
                    $order[]='tc.mobile desc';
                }
            }
            if(array_key_exists('email_order',$data))
            {
                if($data['email_order']==1)
                {
                    $order[]='tc.email asc';
                }else if($data['email_order']==-1)
                {
                    $order[]='tc.email desc';
                }
            }

            if(array_key_exists('address_order',$data))
            {
                if($data['address_order']==1)
                {
                    $order[]='tc.address asc';
                }else if($data['address_order']==-1)
                {
                    $order[]='tc.address desc';
                }
            }
            if(array_key_exists('bank_name_order',$data))
            {
                if($data['bank_name_order']==1)
                {
                    $order[]='tc.bank_name asc';
                }else if($data['bank_name_order']==-1)
                {
                    $order[]='tc.bank_name desc';
                }
            }
            if(array_key_exists('ifsc_code_order',$data))
            {
                if($data['ifsc_code_order']==1)
                {
                    $order[]='tc.ifsc_code asc';
                }else if($data['ifsc_code_order']==-1)
                {
                    $order[]='tc.ifsc_code desc';
                }
            }

            if(array_key_exists('bank_ac_no_order',$data))
            {
                if($data['bank_ac_no_order']==1)
                {
                    $order[]='tc.bank_ac_no asc';
                }else if($data['bank_ac_no_order']==-1)
                {
                    $order[]='tc.bank_ac_no desc';
                }
            }
            if(array_key_exists('pic_order',$data))
            {
                if($data['pic_order']==1)
                {
                    $order[]='tc.pic asc';
                }else if($data['pic_order']==-1)
                {
                    $order[]='tc.pic desc';
                }
            }
            if(array_key_exists('commission_order',$data))
            {
                if($data['commission_order']==1)
                {
                    $order[]='tc.commission asc';
                }else if($data['commission_order']==-1)
                {
                    $order[]='tc.commission desc';
                }
            }

            if(!count($order))
            {
                $order='tc.created_at desc';
            }

            if($gtc){
                $query = $sql->select()
                        ->from($this->tableName)
                        ->where($where)
                        ->order($order);
            }else{
                $query = $sql->select()
                        ->from($this->tableName)
                        ->where($where)
                        ->limit($data['limit'])
                        ->offset($data['offset'])
                        ->order($order);
            }
            // echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tpData = array();
            foreach ($resultSet as $row){
                $tpData[] = $row;
            }
            if($gtc)
                return count($tpData);
            else
                return $tpData;
        }catch (\Exception $e){            
            return array();
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
                ->columns(array('id', 'mobile','name', 'hash','pwd'))
                ->from($this->tableName)
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
                    $decryptedPassword = $aes->decrypt($user[0]["pwd"], $user[0]["hash"]);
                    //print_r($decryptedPassword);exit;
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

    public function checkPasswordWithUserId($userId,$password)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","pwd","hash"))
                ->where(array('status'=>1,'id'=>$userId));

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

                $decryptedPassword = $aes->decrypt($user[0]['pwd'],$user[0]['hash']);

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