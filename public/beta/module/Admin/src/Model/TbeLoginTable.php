<?php


namespace Admin\Model;

use Application\Handler\Aes;
use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TbeLoginTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("tl" => "tbe_login");
    }

    public function addTbeLogin(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }

   public function setTbeLogin($data,$where)
   {
        try{
            return $this->update($data, $where);
        }catch (\Exception $e)
        {
            print_r($e->getMessage());exit;
            //return false;
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
                ->columns($column)
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

    public function authenticateUser($username, $password='')
    {
        try {

            $isEmail = explode("@", $username);
            if (count($isEmail) == 1) {
                $column = "login_id";
            }

            $sql = $this->getSql();
            $user = array();
            $query = $sql->select()
                /* ->columns(array('user_id', 'tbe_mobile','tbe_name', 'hash','pwd', 'role')) */
                ->columns(array('user_id', 'login_id', 'hash','pwd'))
                ->from($this->tableName)
                //->join(array('tbe'=>'tbe_details'), 'tbe.tbe_mobile=tl.login_id', array("user_id"), Select::JOIN_LEFT)
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
                    $decryptedPassword = $aes->decrypt($user[0]["pwd"], $user[0]["hash"]);
                    /* $user[0] = $decryptedPassword;
                    return $user[0]; */
                    print_r($decryptedPassword);exit;
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

    public function getLoginDetails($tbeMobile)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->where(array("tl.login_id" => $tbeMobile));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tbeData = array();
            foreach ($resultSet as $row){
                $tbeData[] = $row;
            }

            if(count($tbeData)){
                return $tbeData[0];
            }

            return $tbeData;

        }catch (\Exception $e){
            
            return array();
        }
    }

    public function checkPasswordWithUserId($userId,$password)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","user_id","pwd","hash"))
                ->where(array('user_id'=>$userId));

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