<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class PromoterDetailsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("pd" => "promoter_details");
    }

    public function addUpdatePromoterDetails($data, $where)
    {
        try{
            $id = $this->getField($where, 'id');
            if($id != ""){
                $update = $this->updatePromoterDetails($data, $where);
                echo "update=" . $update;
                if($update){
                    return array("success" => true);
                }else{
                    return array("success" => false);
                }
            }
            else {
                $data['user_id'] = $where['user_id'];
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
    public function getPromoterDetails($where)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","user_id","state_city" ,"permanent_addr",'bank_name', 'ifsc_code', 'bank_ac_no', 'terms_accepted', 'redeem'))
            ->where($where);
            //echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $pd = array();
            foreach($resultSet as $row){
                $pd = $row;
            }
            //print_r($pd);exit;
            return $pd;
        }catch(\Exception $e){
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
            //echo $sql->getSqlStringForSqlObject($query);exit;
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
            
            //echo $sql->getSqlStringForSqlObject($query);exit;
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
    
    public function updatePromoterDetails($data, $where)
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