<?php


namespace Admin\Model;


use Application\Handler\Aes;
use Application\Model\BaseTable;
use Laminas\Crypt\BlockCipher;
use Laminas\Db\Sql\Having;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Predicate;

use Laminas\Db\TableGateway\TableGateway;

class PricingTable extends  BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("pt" => "pricing");
    }

    public function addPricing(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }
/* 
    public function verfiy($data)
    {
        try{
            $sql=$this->getSql();
            $values=array();
            $query = $sql->select()
                ->columns(array("user_id"))
                ->from(array("u"=>"users"))
                ->where($data);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();

            foreach ($resultSet as $row){
                $values[]=$row;
            }
            if($values){
                return true;
            }else
            {
                return false;
            }

        }catch (\Exception $e){
            return false;
        }
    } */

/*   public function checkPricing($data)
    {
        try {
            $where=new Where();
            $where->equalTo('u.mobile_country_code',$data['mobile_country_code'])->equalTo('u.mobile',$data['mobile'])->notEqualTo('u.role',\Admin\Model\User::Individual_role);

            $sql=$this->getSql();
            $values=array();
            $query = $sql->select()
                ->columns(array("user_id",'role','company_role'))
                ->from(array("u"=>"users"))
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values[]=$row;
            }
               return $values;

        }catch(\Exception $e) {

            return array();
        }
    }
    public function verfiyUser($data)
    {
        try {
            $sql=$this->getSql();
            $values=array();
            $where=new Where();
            $query = $sql->select()
                ->columns(array("user_id"))
                ->from(array("u"=>"users"))
                ->where($data);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values[]=$row;
            }
                return $values;
        }catch (\Exception $e) {
            return array();
        }
    } */
    public function getPricingDetails($data)
    {
        try {
            $sql=$this->getSql();
            $values=array();
            $where=new Where();
            $query = $sql->select()
                ->columns(array("id","plantype","planname","price","no_of_comp_pwds","no_of_days","maxdownloads","maxquantity","start_date","end_date","subscription_validity","sponsor_bonus_min","sponsor_comp_pwds","sponsor_pwd_validity","GST","first_rdp","second_rdp","npc_passwords","npc_days","app_text","web_text"))
                ->from(array("pt"=>"pricing"))
                ->where($data);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $values = array();
            foreach ($resultSet as $row)
            {
                $values[]=$row;
            }
            if (count($values))
            {
                    return $values[0];
            }else
            {
                return array();
            }
        }catch (\Exception $e)
        {
            return array();
        }
    }

    public function adminGetParameters($data)
    {
        try {
            $sql=$this->getSql();
            $values=array();
            $where=new Where();
            $query = $sql->select()
                ->columns(array("id","plantype","planname","price","no_of_comp_pwds","no_of_days","maxdownloads","maxquantity","start_date","end_date","subscription_validity","sponsor_bonus_min","sponsor_comp_pwds","sponsor_pwd_validity","GST","first_rdp","second_rdp","npc_passwords","npc_days","app_text","web_text"))
                ->from(array("pt"=>"pricing"))
                ->where($data);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $values = array();
            foreach ($resultSet as $row)
            {
                $values[]=$row;
            }
            if (count($values))
            {
                    return $values;
            }else
            {
                return array();
            }
        }catch (\Exception $e)
        {
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
    
    public function updatePricingPlan($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e){
            //return $e->getMessage();
            return false;
        }
    }
    
    public function newPricingPlan($data)
    {
        try{
            $insert = $this->insert($data);
            if($insert)
            {
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else
            {
                return array("success" => false);
            }
        }catch(\Exception $e){
                     print_r($e->getMessage());exit;
            return array("success" => false);
        }
    }

    /* public function getUserByUserId($userId){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array("u" => "users"))
                ->where(array("u.user_id" => $userId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $userData = array();
            foreach ($resultSet as $row){
                $userData[] = $row;
            }

            if(count($userData)){
                return $userData[0];
            }

            return $userData;

        }catch (\Exception $e){
            
            return array();
        }
    } */

    public function getPrefs(){
        try
        {
            $pricingDetails = $this->getPricingDetails(array('plantype'=>'0'));
            if(count($pricingDetails)) {
                $prefs = array("max_tales"=>$pricingDetails['maxdownloads'],"max_qty"=>$pricingDetails['maxquantity'],"time_frame"=>$pricingDetails['no_of_days']);                            
            }
            return $prefs;
            /* $pricingDetails = $this->getPricingDetails(array('plantype'=>'1'));
            $today = date('Y-m-d');
            $prefs = array();
            if(!empty($pricingDetails)){
                if($pricingDetails['end_date'] != null){
                    $planEndDate = date('Y-m-d',strtotime($pricingDetails['end_date']));
                    if($planEndDate < $today){
                        $pricingDetails = $this->getPricingDetails(array('plantype'=>'0'));
                        if(count($pricingDetails)) {
                            $prefs = array("max_tales"=>$pricingDetails['maxdownloads'],"max_qty"=>$pricingDetails['maxquantity'],"time_frame"=>$pricingDetails['no_of_days']);                            
                        } 
                    }
                    else {
                        $prefs = array("max_tales"=>$pricingDetails['maxdownloads'],"max_qty"=>$pricingDetails['maxquantity'],"time_frame"=>$pricingDetails['no_of_days']);  
                    }
                }
            }
            return $prefs; */
        }catch (\Exception $e)
        {
            return array();
        }
    }

    public function getNPCDetails(){
        try
        {
            $pricingDetails = $this->getPricingDetails(array('plantype'=>'0'));
            if(count($pricingDetails)) {
                $npc = array("npcp"=>$pricingDetails['npc_passwords'],"npcd"=>$pricingDetails['npc_days']);                            
            }
            return $npc;
            /* $pricingDetails = $this->getPricingDetails(array('plantype'=>'1'));
            $today = date('Y-m-d');
            $npc = array();
            if(!empty($pricingDetails)){
                if($pricingDetails['end_date'] != null){
                    $planEndDate = date('Y-m-d',strtotime($pricingDetails['end_date']));
                    if($planEndDate < $today){
                        $pricingDetails = $this->getPricingDetails(array('plantype'=>'0'));
                        if(count($pricingDetails)) {
                            $npc = array("npcp"=>$pricingDetails['npc_passwords'],"npcd"=>$pricingDetails['npc_days']);                            
                        } 
                    }
                    else {
                        $npc = array("npcp"=>$pricingDetails['npc_passwords'],"npcd"=>$pricingDetails['npc_days']);  
                    }
                }
            } 
            return $npc; */
        }catch (\Exception $e)
        {
            return array();
        }
    }

    public function getAppTexts($mcc){
        try{
            $sql = $this->getSql();
            $planid = ($mcc == '91')? '1' : '3';
            $texts = array();
            //$texts['annual_text'] = $this->getField(array('plantype'=>'0'),"app_text");
            $texts['annual_text'] = $this->getField(array('id'=>$planid),"app_text");
            $texts['promotional_text'] = "";
            $planid = ($mcc == '91')? '2' : '4';
            //$poEndDate = $this->getField(array('plantype'=>'1'),"end_date");
            $poEndDate = $this->getField(array('id'=>$planid),"end_date");
            $today = date('Y-m-d');
            if($poEndDate != null){
                $planEndDate = date('Y-m-d',strtotime($poEndDate));
                if($planEndDate > $today){
                    //$texts['promotional_text'] = $this->getField(array('plantype'=>'1'),"app_text");
                    $texts['promotional_text'] = $this->getField(array('id'=>$planid),"app_text");
                }
            }
            return $texts;
        }catch (\Exception $e)
        {
            return array();
        }
    }

    public function getWebText(){
        try{
            $sql = $this->getSql();
            $text = array();
            $text = $this->getField(array('id'=>'1'),"web_text");
            /* $text = $this->getField(array('plantype'=>'0'),"web_text"); */
            /* $poEndDate = $this->getField(array('plantype'=>'1'),"end_date");
            $today = date('Y-m-d');
            if($poEndDate != null){
                $planEndDate = date('Y-m-d',strtotime($poEndDate));
                if($planEndDate > $today){
                    $text = $this->getField(array('plantype'=>'1'),"web_text");
                }
            } */
            return $text;
        }catch (\Exception $e)
        {
            return "";
        }
    }
  
    public function getAllPricingPlans()
    {
        try{
            $sql = $this->getSql();
            $user = array();
            $query = $sql->select()
                ->columns(array("id","plantype","planname","price","no_of_comp_pwds","no_of_days","maxdownloads","maxquantity","start_date","end_date","subscription_validity","sponsor_comp_pwds","sponsor_pwd_validity","GST","first_rdp","second_rdp","npc_passwords","npc_days","app_text","web_text"))
                ->from(array('pt' => 'pricing'))
                ->order('created_at asc');
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $users=array();
            foreach ($result as $row) {
                $users[] = $row;
            }
            return $users;
        }catch (\Exception $e)
        {
            return array();
        }
    }
}