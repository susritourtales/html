<?php


namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TaConsPaymentsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("tcp" => "ta_cons_payments");
    }

    public function addTaConsPayment(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }
    
   public function setTaConsPayments($data,$where)
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

    public function getFields($where, $column)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns($column )
                ->where($where);

            $field = array();
            //echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row;
            }

             return $field;

        } catch (\Exception $e) {

            return array();
        }
    }

    public function setTaPaymentDetails($data){
        try{
            $ret = false;
            if($data['transaction_type'] == \Admin\Model\TaConsultantTransactions::transaction_due){
                $sql = $this->getSql();
                $where=new Where();
                $where->equalTo("ta_cons_id", $data['ta_cons_id'])->and->equalTo("ta_id", $data['ta_id']);
                $order='tcp.id desc';
    
                $squery = $sql->select()
                        ->columns(array("id","due_amt","status"))
                        ->from($this->tableName)
                        ->where($where)
                        ->order($order); 
                
                //echo $sql->getSqlStringForSqlObject($squery);exit;
                $sresult = $sql->prepareStatementForSqlObject($squery)->execute();
                $status = \Admin\Model\TaConsPayments::status_due;
                $latest_payment_status = "";
                $tcpid = "";
                $i = 0;
                $dueAmt = 0;
                foreach ($sresult as $row) {
                    if($i == 0){
                        $latest_payment_status = $row['status'];
                        $tcpid = $row['id'];
                        $dueAmt = $row['due_amt'];
                    }
                    $i++;
                } 
    
                if(!is_null($latest_payment_status) && $tcpid != ""){
                    if($latest_payment_status == \Admin\Model\TaConsPayments::status_due){
                        // update row
                        $this->setTaConsPayments(array("due_date"=>$data['due_date'], "due_amt"=>$dueAmt + $data['due_amt']), array("id"=>$tcpid));
                    }elseif($latest_payment_status == \Admin\Model\TaConsPayments::status_paid){
                        // insert row
                        $this->addTaConsPayment(array("due_date"=>$data['due_date'], "due_amt"=>$data['due_amt'], "ta_id"=>$data['ta_id'], "ta_cons_id"=>$data['ta_cons_id'], "status"=>$status));
                    }
                }else{
                    $this->addTaConsPayment(array("due_date"=>$data['due_date'], "due_amt"=>$data['due_amt'], "ta_id"=>$data['ta_id'], "ta_cons_id"=>$data['ta_cons_id'], "status"=>$status));
                }
                $ret = true;
            }elseif($data['transaction_type'] == \Admin\Model\TaConsultantTransactions::transaction_paid){
                if(!is_null($data['id'])){   
                    $tcpid = $data['id'];
                    if($data['due_amt'] - $data['paid_amt'] >= 0) 
                        $status = \Admin\Model\TaConsPayments::status_due;
                    else 
                        $status = \Admin\Model\TaConsPayments::status_paid;
                    
                    $this->setTaConsPayments(array("paid_date"=>$data['paid_date'], "paid_amt"=>$data['paid_amt'], "trans_ref"=>$data['trans_ref'], "status"=>\Admin\Model\TaConsPayments::status_paid), array("id"=>$tcpid));
                    if($status == \Admin\Model\TaConsPayments::status_due){
                        $this->addTaConsPayment(array("due_date"=>$data['paid_date'], "due_amt"=>$data['due_amt'] - $data['paid_amt'], "ta_cons_id"=>$data['ta_cons_id'], "ta_id"=>$data['ta_id'], "status"=>$status));
                    }
                    $ret = true;
                }
            }
            return $ret;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function getTaConsPaymentsAdmin($data=array('limit'=>10,'offset'=>0), $gtc=0){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where= $where->greaterThan('tcp.due_amt',0);
            $order=array();
            
            if(array_key_exists('tcp.tac',$data))
            {
                $where->and->like("tad.tac",'%'.$data['tac']."%");
            }
            if(array_key_exists('ta_mobile',$data))
            {
                $where->and->like("tad.ta_mobile",'%'.$data['ta_mobile']."%");
            }
            if(array_key_exists('name',$data))
            {
                $where->and->like("tcd.name",'%'.$data['name']."%");
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("tcd.mobile",'%'.$data['mobile']."%");
            }

            if(array_key_exists('tac_order',$data))
            {
                if($data['tac_order']==1)
                {
                    $order[]='tad.tac asc';
                }else if($data['tac_order']==-1)
                {
                    $order[]='tad.tac desc';
                }
            }
            if(array_key_exists('ta_mobile_order',$data))
            {
                if($data['ta_mobile_order']==1)
                {
                    $order[]='tad.ta_mobile asc';
                }else if($data['ta_mobile_order']==-1)
                {
                    $order[]='tad.ta_mobile desc';
                }
            }
            if(array_key_exists('name_order',$data))
            {
                if($data['name_order']==1)
                {
                    $order[]='tcd.name asc';
                }else if($data['name_order']==-1)
                {
                    $order[]='tcd.name desc';
                }
            }
            if(array_key_exists('mobile_order',$data))
            {
                if($data['mobile_order']==1)
                {
                    $order[]='tcd.mobile asc';
                }else if($data['mobile_order']==-1)
                {
                    $order[]='tcd.mobile desc';
                }
            }

            if(!count($order))
            {
                $order='tcp.id desc';
            }

            if($gtc){
                $query = $sql->select()
                ->columns(array("id", "due_date","due_amt", "ta_id", "ta_cons_id", "paid_date", "paid_amt", "trans_ref", "status"))
                ->from($this->tableName)
                ->join(array('tcd'=>'ta_consultant_details'), 'tcd.id=tcp.ta_cons_id',array("name","mobile","bank_name", "bank_ac_no","ifsc_code"),'left')
                ->join(array('tad'=>'ta_details'), 'tad.id=tcp.ta_id',array("tac","ta_mobile"),'left')
                ->where($where)
                ->order($order);
            }else{
                $query = $sql->select()
                ->columns(array("id", "due_date","due_amt", "ta_id", "ta_cons_id", "paid_date", "paid_amt", "trans_ref", "status"))
                ->from($this->tableName)
                ->join(array('tcd'=>'ta_consultant_details'), 'tcd.id=tcp.ta_cons_id',array("name","mobile","bank_name", "bank_ac_no","ifsc_code"),'left')
                ->join(array('tad'=>'ta_details'), 'tad.id=tcp.ta_id',array("tac","ta_mobile"),'left')
                ->where($where)
                ->limit($data['limit'])
                ->offset($data['offset'])
                ->order($order);
            }

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tpData = array();
            foreach ($resultSet as $row){
                $tpData[] = $row;
            }
            if($gtc)
                return count($tpData);
            else
                return $tpData;
        }catch (\Exception $e){            
            return array();
        }
    }
}