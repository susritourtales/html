<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class PromoterPaymentsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("pp" => "promoter_payments");
    }
    
    public function addPromoterPayment(Array $data)
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

    public function updatePromoterPayments($data,$where)
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

    public function setSponsorPaymentDetails($data){
        try{
            $ret = false;
            if($data['transaction_type'] == \Admin\Model\PromoterTransactions::transaction_due){
                $sql = $this->getSql();
                $where=new Where();
                $where->equalTo("sponsor_id",$data['sponsor_id']);
                $order='pp.id desc';
    
                $squery = $sql->select()
                        ->columns(array("id","due_amount","status"))
                        ->from(array('pp' => 'promoter_payments'))
                        ->where($where)
                        ->order($order); 
                
                //echo $sql->getSqlStringForSqlObject($squery);exit;
                $sresult = $sql->prepareStatementForSqlObject($squery)->execute();
                $status = \Admin\Model\PromoterPayments::status_due;
                $latest_payment_status = "";
                $ppid = "";
                $i = 0;
                $dueAmt = 0;
                foreach ($sresult as $row) {
                    if($i == 0){
                        $latest_payment_status = $row['status'];
                        $ppid = $row['id'];
                        $dueAmt = $row['due_amount'];
                    }
                    $i++;
                } 
    
                if(!is_null($latest_payment_status) && $ppid != ""){
                    if($latest_payment_status == '0'){
                        // update row
                        $this->updatePromoterPayments(array("due_date"=>$data['due_date'], "due_amount"=>$dueAmt + $data['due_amount']), array("id"=>$ppid));
                    }elseif($latest_payment_status == '1'){
                        // insert row
                        $this->addPromoterPayment(array("due_date"=>$data['due_date'], "due_amount"=>$data['due_amount'], "promoter_id"=>$data['promoter_id'], "sponsor_id"=>$data['sponsor_id'], "status"=>$status));
                    }
                }else{
                    $this->addPromoterPayment(array("due_date"=>$data['due_date'], "due_amount"=>$data['due_amount'], "promoter_id"=>$data['promoter_id'], "sponsor_id"=>$data['sponsor_id'], "status"=>$status));
                }
                $ret = true;
            }elseif($data['transaction_type'] == \Admin\Model\PromoterTransactions::transaction_paid){
                if(!is_null($data['id'])){   
                    $ppid = $data['id'];
                    if($data['due_amount'] - $data['paid_amount'] >= 0) 
                        $status = \Admin\Model\PromoterPayments::status_due;
                    else 
                        $status = \Admin\Model\PromoterPayments::status_paid;
                    
                    $this->updatePromoterPayments(array("paid_date"=>$data['paid_date'], "paid_amount"=>$data['paid_amount'], "trans_ref"=>$data['trans_ref'], "status"=>\Admin\Model\PromoterPayments::status_paid), array("id"=>$ppid));
                    if($status == \Admin\Model\PromoterPayments::status_due){
                        $this->addPromoterPayment(array("due_date"=>$data['paid_date'], "due_amount"=>$data['due_amount'] - $data['paid_amount'], "promoter_id"=>$data['promoter_id'], "sponsor_id"=>$data['sponsor_id'], "status"=>$status));
                    }
                }
                $ret = true;
            }
            return $ret;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function getPromotersPaymentsDetails($data=array('limit'=>10,'offset'=>0)){ 
        try
        {
            $sql = $this->getSql();
            $where=new Where();         
            $order=array();
            
            if(array_key_exists('promoter_name',$data))
            {
                $where->and->like("r.ref_by",'%'.$data['promoter_name']."%");
            }
            if(array_key_exists('promoter_mobile',$data))
            {
                $where->and->like("r.ref_mobile",'%'.$data['promoter_mobile']."%");
            }
            if(array_key_exists('sponsor_mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['sponsor_mobile']."%");
            }

            if(array_key_exists('promoter_name_order',$data))
            {
                if($data['promoter_name_order']==1)
                {
                    $order[]='r.ref_by asc';
                }else if($data['promoter_name_order']==-1)
                {
                    $order[]='r.ref_by desc';
                }
            }
            if(array_key_exists('promoter_mobile_order',$data))
            {
                if($data['promoter_mobile_order']==1)
                {
                    $order[]='r.ref_mobile asc';
                }else if($data['promoter_mobile_order']==-1)
                {
                    $order[]='r.ref_mobile desc';
                }
            }
            if(array_key_exists('sponsor_mobile_order',$data))
            {
                if($data['sponsor_mobile_order']==1)
                {
                    $order[]='u.mobile asc';
                }else if($data['sponsor_mobile_order']==-1)
                {
                    $order[]='u.mobile desc';
                }
            }

            if(!count($order))
            {
                $order='pp.id desc';
            }
            //$where->and->equalTo('pd.redeem', 1);
            //$where->and->notEqualTo('pp.due_amount', 0);

            /* $query = $sql->select()
                ->columns(array("id", "due_date","due_amount", "promoter_id", "sponsor_id", "paid_date", "paid_amount", "trans_ref", "status"))
                ->from(array('pp' => 'promoter_payments'))
                ->join(array('r'=>'refer'), 'r.user_id=pp.sponsor_id',array("ref_by","ref_mobile"),'left')
                ->join(array('u'=>'users'), 'u.user_id=pp.sponsor_id',array("user_name","mobile","mobile_country_code"),'left')
                ->join(array('pr'=>'promoter_parameters'), 'pp.id<=pp.promoter_id', array("pay_after_pwds", "amt_per_pwd", "redeem_value"))
                ->join(array('pd'=>'promoter_details'), "pd.user_id=pp.promoter_id and (pd.trigger_payment >= pr.pay_after_pwds or pp.due_amount >= pr.amt_per_pwd * pr.pay_after_pwds or pr.redeem_value = pd.redeem)",array("trigger_payment", "latest_paid_date","bank_ac_no","ifsc_code"))
                ->where($where)
                ->limit($data['limit'])
                ->offset($data['offset'])
                ->order($order); */

            $query = $sql->select()
                ->columns(array("id", "due_date","due_amount", "promoter_id", "sponsor_id", "paid_date", "paid_amount", "trans_ref", "status"))
                ->from(array('pp' => 'promoter_payments'))
                ->join(array('r'=>'refer'), 'r.user_id=pp.sponsor_id',array("ref_by","ref_mobile","trigger_payment", "latest_paid_date"),'left')
                ->join(array('u'=>'users'), 'u.user_id=pp.sponsor_id',array("user_name","mobile","mobile_country_code"),'left')
                ->join(array('pr'=>'promoter_parameters'), 'pr.id<=pp.promoter_id', array("pay_after_pwds", "amt_per_pwd", "redeem_value"))
                /* ->join(array('pd'=>'promoter_details'), "pd.user_id=pp.promoter_id and (r.trigger_payment >= pr.pay_after_pwds)", array("bank_ac_no","ifsc_code", "redeem")) */
                ->join(array('pd'=>'promoter_details'), "pd.user_id=pp.promoter_id and (r.trigger_payment >= pr.pay_after_pwds or pp.due_amount >= pr.amt_per_pwd * pr.pay_after_pwds or pr.redeem_value = pd.redeem)", array("bank_ac_no","ifsc_code", "redeem"))
                ->where($where)
                ->limit($data['limit'])
                ->offset($data['offset'])
                ->order($order);

            // echo $sql->getSqlStringForSqlObject($query);exit;

            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $transArr = array();
            foreach ($result as $row) {
                $transArr[] = $row;
            }
            return $transArr;
        }
        catch (\Exception $e)
        {
            /* print_r($e->getMessage());
            exit; */ 
            return array();
        }
    }

    public function getPromotersPaymentsDetailsCount(){ 
        try
        {
            $sql = $this->getSql();
            $where=new Where();         
            $order=array();

            if(!count($order))
            {
                $order='pp.id desc';
            }

           /*  $query = $sql->select()
                ->columns(array("id","due_date","due_amount", "promoter_id", "sponsor_id", "paid_date", "paid_amount", "trans_ref"))
                ->from(array('pp' => 'promoter_payments'))
                ->join(array('r'=>'refer'), 'r.user_id=pp.sponsor_id',array("ref_by","ref_mobile"),'left')
                ->join(array('u'=>'users'), 'u.user_id=pp.sponsor_id',array("user_name","mobile","mobile_country_code"),'left')
                ->join(array('pr'=>'promoter_parameters'), 'pp.id<=pp.promoter_id', array("pay_after_pwds", "amt_per_pwd"))
                ->join(array('pd'=>'promoter_details'), "pd.user_id=pp.promoter_id and (pd.trigger_payment >= pr.pay_after_pwds or pp.due_amount >= pr.amt_per_pwd * pr.pay_after_pwds or pd.redeem == 1)",array("trigger_payment", "latest_paid_date"))
                ->where($where)
                ->order($order); */
               
                $query = $sql->select()
                ->columns(array("id", "due_date","due_amount", "promoter_id", "sponsor_id", "paid_date", "paid_amount", "trans_ref", "status"))
                ->from(array('pp' => 'promoter_payments'))
                ->join(array('r'=>'refer'), 'r.user_id=pp.sponsor_id',array("ref_by","ref_mobile","trigger_payment", "latest_paid_date"),'left')
                ->join(array('u'=>'users'), 'u.user_id=pp.sponsor_id',array("user_name","mobile","mobile_country_code"),'left')
                ->join(array('pr'=>'promoter_parameters'), 'pr.id<=pp.promoter_id', array("pay_after_pwds", "amt_per_pwd", "redeem_value"))
                /* ->join(array('pd'=>'promoter_details'), "pd.user_id=pp.promoter_id and (r.trigger_payment >= pr.pay_after_pwds)", array("bank_ac_no","ifsc_code", "redeem")) */
                ->join(array('pd'=>'promoter_details'), "pd.user_id=pp.promoter_id and (r.trigger_payment >= pr.pay_after_pwds or pp.due_amount >= pr.amt_per_pwd * pr.pay_after_pwds or pr.redeem_value = pd.redeem)",array("bank_ac_no","ifsc_code", "redeem"))
                ->where($where)
                ->order($order);

            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $transArr = array();
            foreach ($result as $row) {
                $transArr[] = $row;
            }
            return $transArr;
        }
        catch (\Exception $e)
        {
            return array();
        }
    }
}