<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class PromoterParametersTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("pp" => "promoter_parameters");
    }

    
   public function setPromoterParameters($data,$where)
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

   public function getPromoterParameters(){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->where(array("pp.id" => 1));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $ppData = array();
            foreach ($resultSet as $row){
                $ppData[] = $row;
            }
            return $ppData;
        }catch (\Exception $e){            
            return array();
        }
    }
}