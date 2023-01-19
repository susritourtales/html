<?php


namespace Admin\Model;

use Application\Handler\Aes;
use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TbeDetailsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("tb" => "tbe_details");
    }

    public function addTbe(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }

   public function setTbeDetails($data,$where)
   {
        try{
            return $this->update($data, $where);
        }catch (\Exception $e)
        {
            return false;
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

            return $e;
        }
    }

    public function getTaName($tbeId){
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('ta_id'))
                ->join(array('tad'=>'ta_details'), 'tad.id=tb.ta_id',array("ta_name"),Select::JOIN_LEFT)
                ->where(array("tb.user_id" => $tbeId));

            $field = array();
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row['ta_name'];
            }

            if ($field) {
                return $field;
            } else {
                return '';
            }

        } catch (\Exception $e) {
            return '';
        }
    }

    public function getTasTbeCount($taId){
        try {
            $sql = $this->getSql();
            $query = $sql->select()
            ->columns(array('count'=>new \Laminas\Db\Sql\Expression('COUNT(`tb`.`user_id`)')))
                ->from($this->tableName)
                ->where(array("tb.ta_id" => $taId, "active"=>1, "status"=>1));

            $field = array();
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row['count'];
            }

            if ($field) {
                return $field;
            } else {
                return 0;
            }

        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getTasTbeList($taId){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->join(array('tad'=>'ta_details'), 'tad.id=tb.ta_id',array("ta_name"),Select::JOIN_LEFT)
                ->where(array("tb.ta_id" => $taId, "tb.status"=>1, "tb.active"=>1));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tbeData = array();
            foreach ($resultSet as $row){
                $tbeData[] = $row;
            }
            return $tbeData;

        }catch (\Exception $e){
            
            return array();
        }
    }

    public function getTbeDetails($tbeId)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->where(array("tb.user_id" => $tbeId));
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

    public function getTasMTbeList($tbeMobile, $taId)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->join(array('tad'=>'ta_details'), 'tad.id=tb.ta_id',array("ta_name"),Select::JOIN_LEFT)
                ->where(array("tb.tbe_mobile" => $tbeMobile, "tb.ta_id" => $taId, "tb.status"=>1, "tb.active"=>1));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tbeData = array();
            foreach ($resultSet as $row){
                $tbeData[] = $row;
            }

            if(count($tbeData)){
                return $tbeData;
            }

            return $tbeData;

        }catch (\Exception $e){
            
            return array();
        }
    }

    public function getMobileTas($tbeMobile)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->join(array('tad'=>'ta_details'), 'tad.id=tb.ta_id',array("ta_name"),Select::JOIN_LEFT)
                ->where(array("tb.tbe_mobile" => $tbeMobile, "tb.status"=>1, "tb.active"=>1));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tbeData = array();
            foreach ($resultSet as $row){
                $tbeData[] = $row;
            }

            if(count($tbeData)){
                return $tbeData;
            }

            return $tbeData;

        }catch (\Exception $e){
            
            return array();
        }
    }

    public function getUPCList($tbeMobile, $taId=null){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo("tb.tbe_mobile", $tbeMobile);
            $where->equalTo("tb.ta_id", $taId);
            $where->equalTo("tb.status", \Admin\Model\TbeDetails::TBE_Active);
            $where->equalTo("tb.active", \Admin\Model\TbeDetails::TBE_Enabled);
            $where->and->greaterThan(new \Laminas\Db\Sql\Expression('DATEDIFF(`tp`.`doe`, NOW())'), 0);
            $where->and->greaterThan('tp.tourists_count', 0);
            $query = $sql->select()
                ->columns(array("tbe_name", "tbe_mobile", "ta_id"))
                ->from($this->tableName)
                ->join(array('tp'=>'ta_purchases'), 'tp.ta_id=tb.ta_id',array('id', 'upc', 'tourists_count'),Select::JOIN_LEFT)
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $ulData = array();
            foreach ($resultSet as $row){
                $ulData[] = $row;
            }

            if(count($ulData)){
                return $ulData;
            }

            return $ulData;

        }catch (\Exception $e){
            
            return array();
        }
    }

    /* public function authenticateUser($username, $password='')
    {
        try {

            $isEmail = explode("@", $username);
            $column = "tbe_email";
            if (count($isEmail) == 1) {
                $column = "tbe_mobile";
            }

            $sql = $this->getSql();
            $user = array();
            $query = $sql->select()
                ->columns(array('user_id', 'tbe_mobile','tbe_name', 'hash','pwd', 'role'))
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
                ->columns(array("user_id","pwd","hash"))
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
    } */

    public function getTbeAdmin($data=array('limit'=>10,'offset'=>0), $gtc=0, $taId){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('status',1)->equalTo('tb.ta_id',$taId);
            $order=array();
            
            if(array_key_exists('tbe_name',$data))
            {
                $where->and->like("tb.tbe_name",'%'.$data['tbe_name']."%");
            }
            if(array_key_exists('tbe_mobile',$data))
            {
                $where->and->like("tb.tbe_mobile",'%'.$data['tbe_mobile']."%");
            }
            if(array_key_exists('tbe_email',$data))
            {
                $where->and->like("tb.tbe_email",'%'.$data['tbe_email']."%");
            }
            if(array_key_exists('role',$data))
            {
                $where->and->like("tb.role",'%'.$data['role']."%");
            }
            

            if(array_key_exists('tbe_name_order',$data))
            {
                if($data['tbe_name_order']==1)
                {
                    $order[]='tb.tbe_name asc';
                }else if($data['tbe_name_order']==-1)
                {
                    $order[]='tb.tbe_name desc';
                }
            }
            if(array_key_exists('tbe_mobile_order',$data))
            {
                if($data['tbe_mobile_order']==1)
                {
                    $order[]='tb.tbe_mobile asc';
                }else if($data['tbe_mobile_order']==-1)
                {
                    $order[]='tb.tbe_mobile desc';
                }
            }
            if(array_key_exists('tbe_email_order',$data))
            {
                if($data['tbe_email_order']==1)
                {
                    $order[]='tb.tbe_email asc';
                }else if($data['tbe_email_order']==-1)
                {
                    $order[]='tb.tbe_email desc';
                }
            }

            if(array_key_exists('role_order',$data))
            {
                if($data['role_order']==1)
                {
                    $order[]='tb.role asc';
                }else if($data['role_order']==-1)
                {
                    $order[]='tb.role desc';
                }
            }
            

            if(!count($order))
            {
                $order='tb.created_at desc';
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
            $tbeData = array();
            foreach ($resultSet as $row){
                $tbeData[] = $row;
            }
            if($gtc)
                return count($tbeData);
            else
                return $tbeData;
        }catch (\Exception $e){            
            return array();
        }
    }
}