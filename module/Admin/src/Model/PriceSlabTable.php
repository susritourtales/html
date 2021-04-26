<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class PriceSlabTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("p" => "price_slab");
    }

    public function addPrice(Array $data)
    {
        try{
            $insert = $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e){

            return array("success" => false);
        }
    }
    public function updatePrice($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e)
        {

            return false;
        }

    }
    public function addMutiplePriceSlabs(Array $data)
    {
        try {
            return $this->multiInsert($data);
        } catch (\Exception $e) {
             print_r($e->getMessage());
            exit;
            return false;
        }
    }
      public function priceDetails($tourType)
      {
          try
          {
              $where=new Where();
              $sql = $this->getSql();

                  $where->equalTo('tour_type',$tourType)->equalTo('status',1);

              $query = $sql->select()
                  ->from($this->tableName)
                  ->columns(array('price'))
                  ->where($where);

              $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
              $tourisms = array();
              foreach($resultSet as $row){

                  $tourisms = json_decode($row['price'],true);
              }

              return $tourisms;
          }catch (\Exception $e)
          {
              return array();
          }
      } public function priceDetailsAdmin()
      {
          try
          {
              $where=new Where();
              $sql = $this->getSql();

              $where->equalTo('status',1);

              $query = $sql->select()
                  ->from($this->tableName)
                  ->columns(array('price','tour_type'))
                  ->where($where);

              $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
              $tourisms = array();
              foreach($resultSet as $row){
                  $tourisms[] = $row;
              }

              return $tourisms;
          }catch (\Exception $e)
          {
              return array();
          }
      }
}