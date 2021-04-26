<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class SscTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("s" => "ssc");
    }

    
   public function setSSC($data,$where)
   {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e)
        {
            return false;
        }
   }  
   public function getSSC(){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array("s" => "ssc"))
                ->where(array("s.id" => 1));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $sscData = "";
            foreach ($resultSet as $row){
                $sscData = $row['ssc'];
            }

            if($sscData){
                return $sscData;
            }

            return $sscData;

        }catch (\Exception $e){
            
            return "";
        }
    }
}