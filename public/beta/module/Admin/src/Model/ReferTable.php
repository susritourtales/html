<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

use function PHPUnit\Framework\isNull;

class ReferTable extends   BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("r" => "refer");
    }

    public function adminUpdateRefer(Array $data)
    {
        try{
            $exists = $this->getRefers(array("user_id"=>$data['user_id']));
            if(count($exists)>0){
                if($data['ref_by']){
                    $update = $this->updateRefer(array("ref_by"=>$data['ref_by']),array("user_id"=>$data['user_id']));
                }
                if($data['ref_mobile']){
                    $update = $this->updateRefer(array("ref_mobile"=> $data['ref_mobile']),array("user_id"=>$data['user_id']));
                }
                
                if($update){
                    return array("success" => true);
                }else{
                    return array("success" => false);
                }
            }
            else {
                $insert = $this->insert($data);
                if($insert){
                    return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
                }else{
                    return array("success" => false);
                }
            }
        }catch(\Exception $e){
            /* print_r($e->getMessage());
            exit; */
            return array("success" => false);
        }
    }

    public function addRefer(Array $data)
    {
        try{
            $exists = $this->getRefers(array("user_id"=>$data['user_id']));
            if(count($exists)>0){
                $rm = "";
                if($data['ref_mobile'])
                    $rm = $data['ref_mobile'];
                $update = $this->updateRefer(array("ref_by"=>$data['ref_by'], "ref_mobile"=> $rm, "ref_id"=>$data['ref_id']),array("user_id"=>$data['user_id']));
                if($update){
                    return array("success" => true);
                }else{
                    return array("success" => false);
                }
            }
            else {
                var_dump($data); exit;
                $insert = $this->insert($data);
                if($insert){
                    return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
                }else{
                    return array("success" => false);
                }
            }
        }catch(\Exception $e){
            /* print_r($e->getMessage());
            exit; */
            return array("success" => false);
        }
    }
    public function getRefers($where)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","ref_by","ref_mobile",'user_id')) // ->columns(array("id","ref_by",'user_id')) 
            ->where($where);
            //echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            
            $refers = array();
            foreach($resultSet as $row){
                $refers[] = $row;
            }
            /* if(count($resultSet)){
                foreach($resultSet as $row){
                    $refers[] = $row;
                }
            }else{
                $refers[] = array('id'=>"",'ref_by'=>"",'ref_mobile'=>"",'user_id'=>"");
            } */
            
            //print_r($refers);exit;
            return $refers;
        }catch(\Exception $e){
            return array();
        }
    }
    
    public function getField($where, $column)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)  //->from(array("s"=>"states"))
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
    
    public function updateRefer($data,$where)
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

    public function getTotalPromoterPasswords($promoterId){
        try
        {
            $sql = $this->getSql();
            $pwdqry = $sql->select()
                    ->columns(array('total_passwords'=>new \Laminas\Db\Sql\Expression('SUM(`r`.`pwds_purchased`)')))
                    ->from(array('r' => 'refer'))
                    ->where(array('r.ref_id'=> $promoterId));
                // echo $sql->getSqlStringForSqlObject($pwdqry);exit;
                $pwdres = $sql->prepareStatementForSqlObject($pwdqry)->execute();
                $pwdresArr = array();
                foreach($pwdres as $r){
                    $pwdresArr[] = $r;
                }
                return $pwdresArr[0]['total_passwords'];
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function getSponsorsPerfAdmin($data=array('limit'=>10,'offset'=>0)){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('r.ref_id',$data['promoterId']);            
            $order=array();
            
            if(array_key_exists('user_name',$data))
            {
                $where->and->like("u.user_name",'%'.$data['user_name']."%");
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }

            if(array_key_exists('user_name_order',$data))
            {
                if($data['user_name_order']==1)
                {
                    $order[]='u.user_name asc';
                }else if($data['user_name_order']==-1)
                {
                    $order[]='u.user_name desc';
                }
            }
            if(array_key_exists('mobile_order',$data))
            {
                if($data['mobile_order']==1)
                {
                    $order[]='u.mobile asc';
                }else if($data['mobile_order']==-1)
                {
                    $order[]='u.mobile desc';
                }
            }

            if(!count($order))
            {
                $order='r.created_at desc';
            }
            
            $query = $sql->select()
                ->columns(array("user_id","pwds_purchased", "sponsor_status", "disc50"))
                ->from(array('r' => 'refer'))
                ->join(array('u'=>'users'), 'u.user_id=r.user_id',array("user_id","user_name","mobile","mobile_country_code","subscription_end_date","is_promoter"),'left')
                ->where($where)
                ->limit($data['limit'])
                ->offset($data['offset'])
                ->order($order);
            //echo $sql->getSqlStringForSqlObject($query);exit;

            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $sponsorsPerf=array();
            foreach ($result as $row) {
                $spqry = $sql->select()
                    ->columns(array('amount_paid'=>new \Laminas\Db\Sql\Expression('SUM(`pt`.`amount`)')))
                    ->from(array('pt' => 'promoter_transactions'))
                    ->where(array('pt.sponsor_id'=> $row['user_id'], 'pt.transaction_type'=> \Admin\Model\PromoterTransactions::transaction_paid));
                $spres = $sql->prepareStatementForSqlObject($spqry)->execute();
                $spresArr = array();
                foreach($spres as $r){
                    $spresArr[] = $r;
                }
                $row['amount_paid'] = $spresArr[0]['amount_paid'];
                $sponsorsPerf[] = $row;
            }
            return $sponsorsPerf;
        }catch (\Exception $e)
        {
            /* print_r($e->getMessage());
            exit; */
            return array();
        }
    }

    public function getSponsorsPerfAdminCount($data=array()){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('r.ref_id', $data['promoterId']);            
                 
            $query = $sql->select()
                ->columns(array('sponsors_count'=>new \Laminas\Db\Sql\Expression('COUNT(`r`.`user_id`)')))
                ->from(array('r' => 'refer'))
                ->where($where);             
            // echo $sql->getSqlStringForSqlObject($query);exit;

            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $sponsorsCount = array();
            foreach ($result as $row) {
                $sponsorsCount[] = $row;
            }
            //var_dump($sponsorsCount); exit;
            return $sponsorsCount;
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return array();
        }
    }
}