<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class PromoterTransactionsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("pt" => "promoter_transactions");
    }
    
    public function addPromoterTransaction(Array $data)
    {
        try{
            $insert = $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e){
            /* print_r($e->getMessage());
            exit; */
            return array("success" => false);
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

    public function updatePromoterTransactions($data,$where)
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

    public function getSponsorwiseTransactions($data){ 
        try
        {
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('pt.sponsor_id',$data['sponsorId']);
            $where->notEqualTo('pt.amount', "0");
            $order=array();

            if(!count($order))
            {
                //$order='pt.created_at desc';
                $order='pt.created_at asc';
            }
            
            $query = $sql->select()
            ->columns(array("id","promoter_id","transaction_type", "sponsor_id", "account_no", "ifsc_code", "transaction_date", "no_of_pwds","amount", "transaction_ref"))
            ->from(array('pt' => 'promoter_transactions'))
            ->join(array('r'=>'refer'), 'r.user_id=pt.sponsor_id',array("ref_by","ref_mobile"),'left')
            ->join(array('u'=>'users'), 'u.user_id=pt.sponsor_id',array("user_name","mobile","mobile_country_code"),'left')
            ->where($where)
            ->order($order);
            
            //echo $sql->getSqlStringForSqlObject($query);exit;
        
            $result = $sql->prepareStatementForSqlObject($query)->execute();

            $da=0;
            $pa=0;
            $transArr = [];
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
                /* if($temp['transaction_type'] == \Admin\Model\PromoterTransactions::transaction_paid){
                    $dtemp = $temp;
                    $dtemp['transaction_type'] = \Admin\Model\PromoterTransactions::transaction_due;
                    $dtemp['transaction_ref'] = "";
                    $dtemp['amount'] = $da;
                    $dtemp['no_of_pwds'] = "";
                    $dtemp['sr'] = 1; // to be used to not display summary row inserted after stt payment
                    $transArr[] = $dtemp;
                } */
            }
            //var_dump($transArr); exit;
            return array_slice($transArr, $data['offset'], $data['limit']);
            //return $transArr;
        }
        catch (\Exception $e)
        {
            /* print_r($e->getMessage());
            exit; */ 
            return array();
        }
    }

    public function getSponsorwiseAllTransactions($data){ 
        try
        {
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('pt.sponsor_id',$data['sponsorId']);            
            $order=array();

            if(!count($order))
            {
                //$order='pt.created_at desc';
                $order='pt.created_at asc';
            }

            $query = $sql->select()
                ->columns(array("id","promoter_id","transaction_type", "sponsor_id", "account_no", "ifsc_code", "transaction_date", "no_of_pwds","amount", "transaction_ref"))
                ->from(array('pt' => 'promoter_transactions'))
                ->join(array('r'=>'refer'), 'r.user_id=pt.sponsor_id',array("ref_by","ref_mobile"),'left')
                ->join(array('u'=>'users'), 'u.user_id=pt.sponsor_id',array("user_name","mobile","mobile_country_code"),'left')
                ->where($where)
                ->order($order);
            //echo $sql->getSqlStringForSqlObject($query);exit;

            $result = $sql->prepareStatementForSqlObject($query)->execute();

            $da=0;
            $pa=0;
            $transArr = [];
            foreach ($result as $row){ 
                $transArr[] = $row;
                /* $temp = $row;
                if($temp['transaction_type'] == \Admin\Model\PromoterTransactions::transaction_paid){
                    $pa = $temp['cpamt'] = $pa + $row['amount'];
                    $da = $temp['cdamt'] = $da - $row['amount'];
                }else {
                    $da = $temp['cdamt'] = $row['amount'] + $da;
                    $pa = $temp['cpamt'] = $pa;
                }
                //echo "da=$da, pa=$pa <br> ";
                $p = $temp['cpwds'] = $row['no_of_pwds'] + $p;
                $transArr[] = $temp;
                if($temp['transaction_type'] == \Admin\Model\PromoterTransactions::transaction_paid){
                    $dtemp = $temp;
                    $dtemp['transaction_type'] = \Admin\Model\PromoterTransactions::transaction_due;
                    $dtemp['transaction_ref'] = "";
                    $dtemp['amount'] = $da;
                    $transArr[] = $dtemp;
                } */
            }
            //var_dump($transArr); exit;

            return $transArr;
        }
        catch (\Exception $e)
        {
            return array();
        }
    }
}