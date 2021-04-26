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

class SponsorTypesTable extends  BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("st" => "sponsor_types");
    }

    public function newSponsorType($data)
    {
        try{
            $insert = $this->insert($data);
            if($insert)
            {
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else
            {
                return array("success" => false);
            }
        }catch(\Exception $e){
                     print_r($e->getMessage());exit;
            return array("success" => false);
        }
    }

    public function getSponsorTypes($data)
    {
        try {
            $sql=$this->getSql();
            $values=array();
            $where=new Where();
            $query = $sql->select()
                ->columns(array("type_id","type_name"))
                ->from(array("st"=>"sponsor_types"))
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
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row;
            }

             return $field;

        } catch (\Exception $e) {

            return array();
        }
    }
    
    public function updateSponsorType($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e){
            return false;
        }
    }
  
    public function getAllSponsorTypes()
    {
        try{
            $sql = $this->getSql();
            $user = array();
            $query = $sql->select()
                ->columns(array("type_id","type_name"))
                ->from(array('st' => 'sponsor_types'))
                ->order('type_id asc');
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
}