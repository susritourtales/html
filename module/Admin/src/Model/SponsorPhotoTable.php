<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Having;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Predicate;

class SponsorPhotoTable extends  BaseTable
{  protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("sp" => "sponsor_photo");
    }

    public function addSponsorPhoto(Array $data)
    {
        try{
            $chk = $this->getFields(array('file_data_id'=>$data['file_data_id']), array('id'));
            if(count($chk)){
                $ret = $this->updateSponsorPhoto($data, array('id'=>$chk['id']));
                if($ret)
                return array("success" => true,"id" => $chk['id']);
            }else {
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

    public function updateSponsorPhoto($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e)
        {
            return false;
        }
    }
    public function getSponsorPhoto($where)
    {
        try{
            $where['status']=1;
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                //->columns(array("tourism_place_id",'place_name','place_description','country_id','state_id','city_id'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourisms = array();
            foreach($resultSet as $row){
                $tourisms[] = $row;
            }
            return $tourisms;
        }catch(\Exception $e)
        {
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
    public function getFields($where,$columns)
    {
        try
        {
            $sql=$this->getSql();
            $values=array();

            $query = $sql->select()
                ->columns($columns)
                ->from($this->tableName)
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values=$row;
            }
            return $values;
        }catch (\Exception $e)
        {
            return array();
        }
    }
}