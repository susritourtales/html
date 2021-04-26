<?php

namespace Admin\Model;


use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class TourCoordinatorDetailsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("to" => "tour_coordinator_details");
    }

    public function addTourCoordinator(Array $data)
    {
        try{
            $insert = $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e){
                      print_r($e->getMessage());
                      exit;
            return array("success" => false);
        }
    }
    public function addMutipleTourCoordinator(Array $data)
    {
        try {
            return $this->multiInsert($data);
        } catch (\Exception $e) {

            return false;
        }
    }
    public function updateCoordinator($data,$where)
    {
        try{
            return $this->update($data,$where);
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
    public function getStates($where)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","state_name",'country_id'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $states = array();

            foreach($resultSet as $row){
                $states[] = $row;
            }

            return $states;
        }catch(\Exception $e){
            return array();
        }
    }
}