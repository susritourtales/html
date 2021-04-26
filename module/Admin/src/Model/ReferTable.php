<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class ReferTable extends   BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("r" => "refer");
    }

    public function adminUpdateRefer(Array $data)
    {
        try{
            $exists = $this->getRefers(array("user_id"=>$data['user_id']));
            if(count($exists)>0){
                if($data['ref_by']){
                    $update = $this->updateRefer(array("ref_by"=>$data['ref_by']),array("user_id"=>$data['user_id']));
                }
                if($data['ref_mobile']){
                    $update = $this->updateRefer(array("ref_mobile"=> $data['ref_mobile']),array("user_id"=>$data['user_id']));
                }
                
                if($update){
                    return array("success" => true);
                }else{
                    return array("success" => false);
                }
            }
            else {
                $insert = $this->insert($data);
                if($insert){
                    return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
                }else{
                    return array("success" => false);
                }
            }
        }catch(\Exception $e){
            print_r($e->getMessage());
            exit;
            return array("success" => false);
        }
    }

    public function addRefer(Array $data)
    {
        try{
            $exists = $this->getRefers(array("user_id"=>$data['user_id']));
            if(count($exists)>0){
                $rm = "";
                if($data['ref_mobile'])
                    $rm = $data['ref_mobile'];
                $update = $this->updateRefer(array("ref_by"=>$data['ref_by'], "ref_mobile"=> $rm),array("user_id"=>$data['user_id']));
                //$update = $this->updateRefer(array("ref_by"=>$data['ref_by']),array("user_id"=>$data['user_id']));
                if($update){
                    return array("success" => true);
                }else{
                    return array("success" => false);
                }
            }
            else {
                $insert = $this->insert($data);
                if($insert){
                    return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
                }else{
                    return array("success" => false);
                }
            }
        }catch(\Exception $e){
            print_r($e->getMessage());
            exit;
            return array("success" => false);
        }
    }
    public function getRefers($where)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","ref_by","ref_mobile",'user_id')) // ->columns(array("id","ref_by",'user_id')) 
            ->where($where);
            //echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            
            $states = array();
            foreach($resultSet as $row){
                $states[] = $row;
            }
            /* if(count($resultSet)){
                foreach($resultSet as $row){
                    $states[] = $row;
                }
            }else{
                $states[] = array('id'=>"",'ref_by'=>"",'ref_mobile'=>"",'user_id'=>"");
            } */
            
            //print_r($states);exit;
            return $states;
        }catch(\Exception $e){
            return array();
        }
    }
    
    public function getField($where, $column)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array("s"=>"states"))
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
    
    public function updateRefer($data,$where)
    {
        try
        {
            return $this->update($data,$where);
        }
        catch (\Exception $e)
        {
            return false;
        }
    }
}