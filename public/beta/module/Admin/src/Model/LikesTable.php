<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\TableGateway\TableGateway;

class LikesTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("l" => "likes");
    }

    public function addLike(Array $data)
    {
        try{
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
    public function updateLike($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e)
        {
            return false;
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