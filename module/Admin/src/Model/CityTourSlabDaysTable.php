<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\TableGateway\TableGateway;

class CityTourSlabDaysTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("c" => "city_tour_slab_days");
    }

    public function addSlabDays(Array $data)
    {
        try
        {
            $insert = $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e)
        {
            return array("success" => false);
        }
    }
    public function updateSlabDays($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e)
        {

            return false;
        }

    }

    public function getSlabDays(){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("days"))
                ->where(array('c.status'=>1));


            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $days = '';

            foreach($resultSet as $row){
                $days = $row['days'];
            }

            return $days;
        }catch(\Exception $e){
            return '';
        }
    }
}