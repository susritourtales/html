<?php


namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TaConsultantTransactionsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("tt" => "ta_cons_transactions");
    }

    public function addTaConsultantTransaction(Array $data)
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
   public function setTaConsultantTransaction($data,$where)
   {
        try{
            return $this->update($data, $where);
        }catch (\Exception $e)
        {
            return false;
        }
   } 
   public function getTaConsultantTransactions($tacId)
   {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->where(array("tt.id" => $tacId, "pic_counter"=>1));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $conData = array();
            foreach ($resultSet as $row){
                $conData[] = $row;
            }

            if(count($conData)){
                return $conData;
            }

            return $conData;

        }catch (\Exception $e){
            
            return array();
        }
   } 

   public function getPicCount($where){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $conData = array();
            foreach ($resultSet as $row){
                $conData[] = $row;
            }
            $count = count($conData);            
            return $count;
        }catch (\Exception $e){ 
            return -1;
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

    public function getTaConsTxAdmin($data=array('limit'=>10,'offset'=>0), $gtc=0){
        try
        {
            $sql = $this->getSql();
            $where=new Where();
            if($data['ta_id'])
                $where->equalTo('ta_id',$data['ta_id']);
            if($data['ta_cons_id'])
                $where->equalTo('ta_cons_id',$data['ta_cons_id']);
            $where->notEqualTo('amount', "0");
            $order=array();

            if(!count($order))
            {
                //$order='pt.created_at desc';
                $order='tt.created_at asc';
            }
            
            $query = $sql->select()
            ->from($this->tableName)
            ->columns(array("upc","ta_id","ta_cons_id","transaction_type", "ta_cons_id", "transaction_date", "amount", "transaction_ref"))
            ->join(array('tad'=>'ta_details'), 'tad.id=tt.ta_id',array("ta_name"),Select::JOIN_LEFT)
            ->where($where)
            ->order($order);
            
            //echo $sql->getSqlStringForSqlObject($query);exit;
        
            $result = $sql->prepareStatementForSqlObject($query)->execute();

            $da=0;
            $pa=0;
            $transArr = [];
            if($gtc){
                foreach ($result as $row){ 
                    $transArr[] = $row;
                }
                return count($transArr);
            }else{
                foreach ($result as $row){ 
                    $temp = $row;
                    if($temp['transaction_type'] == \Admin\Model\PromoterTransactions::transaction_paid){
                        $pa = $temp['cpamt'] = $pa + $row['amount'];
                        $da = $temp['cdamt'] = $da - $row['amount'];
                    }else {
                        $da = $temp['cdamt'] = $row['amount'] + $da;
                        $pa = $temp['cpamt'] = $pa;
                    }
                    //echo "da=$da, pa=$pa <br> ";
                    $p = $temp['cpwds'] = $row['no_of_pwds'] + $p;
                    $temp['sr'] = 0;
                    $transArr[] = $temp;
                }
                //var_dump($transArr); exit;
                if($gtc)
                    return count($transArr);
                else
                    return array_slice($transArr, $data['offset'], $data['limit']);
                //return $transArr;
            }
        }
        catch (\Exception $e)
        {
            /* print_r($e->getMessage());
            exit; */ 
            return array();
        }
    }
}