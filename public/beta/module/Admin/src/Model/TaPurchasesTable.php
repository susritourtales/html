<?php


namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TaPurchasesTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("tp" => "ta_purchases");
    }

    public function addTaPurchase(Array $data)
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
    
   public function setTaPurchases($data,$where)
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

    public function getUPCDetails($taId){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->where(array("tp.ta_id" => $taId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tpData = array();
            foreach ($resultSet as $row){
                $tpData[] = $row;
            }

            if(count($tpData)){
                return $tpData[0];
            }

            return $tpData;

        }catch (\Exception $e){
            
            return array();
        }
    }
    /* public function getUPCList($taId){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo("tp.ta_id",$taId);
            $where->and->greaterThan(new \Laminas\Db\Sql\Expression('DATEDIFF(`tp`.`doe`, NOW())'), 0);
            $where->and->greaterThan('tourists_count', 0);
            $query = $sql->select()
                ->columns(array('id', 'upc', 'tourists_count'))
                ->from($this->tableName)
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $ulData = array();
            foreach ($resultSet as $row){
                $ulData[] = $row;
            }

            if(count($ulData)){
                return $ulData;
            }

            return $ulData;

        }catch (\Exception $e){
            
            return array();
        }
    } */

    public function getTaPurchasesAdmin($data=array('limit'=>10,'offset'=>0), $gtc=0){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('tp.status',1);
            $order=array();
            
            if(array_key_exists('dop',$data))
            {
                $where->and->like("tp.dop",'%'.$data['dop']."%");
            }
            if(array_key_exists('doe',$data))
            {
                $where->and->like("tp.doe",'%'.$data['doe']."%");
            }
            if(array_key_exists('upc',$data))
            {
                $where->and->like("tp.upc",'%'.$data['upc']."%");
            }
            if(array_key_exists('name',$data) && $data['name']!='')
            {
                $where->and->like("tc.name",'%'.$data['name']."%");
            }
            if(array_key_exists('ta_name',$data))
            {
                $where->and->like("ta.ta_name",'%'.$data['ta_name']."%");
            }
            if(array_key_exists('ta_plan_cost',$data))
            {
                $where->and->like("tp.ta_plan_cost",'%'.$data['ta_plan_cost']."%");
            }
            if(array_key_exists('tourists_count',$data))
            {
                $where->and->like("tp.tourists_count",'%'.$data['tourists_count']."%");
            }
            

            if(array_key_exists('dop_order',$data))
            {
                if($data['dop_order']==1)
                {
                    $order[]='tp.dop asc';
                }else if($data['dop_order']==-1)
                {
                    $order[]='tp.dop desc';
                }
            }
            if(array_key_exists('doe_order',$data))
            {
                if($data['doe_order']==1)
                {
                    $order[]='tp.doe asc';
                }else if($data['doe_order']==-1)
                {
                    $order[]='tp.doe desc';
                }
            }
            if(array_key_exists('upc_order',$data))
            {
                if($data['upc_order']==1)
                {
                    $order[]='tp.upc asc';
                }else if($data['upc_order']==-1)
                {
                    $order[]='tp.upc desc';
                }
            }

            if(array_key_exists('name_order',$data))
            {
                if($data['name_order']==1)
                {
                    $order[]='tc.name asc';
                }else if($data['name_order']==-1)
                {
                    $order[]='tc.name desc';
                }
            }
            if(array_key_exists('ta_name_order',$data))
            {
                if($data['ta_name_order']==1)
                {
                    $order[]='ta.ta_name asc';
                }else if($data['ta_name_order']==-1)
                {
                    $order[]='ta.ta_name desc';
                }
            }
            if(array_key_exists('ta_plan_cost_order',$data))
            {
                if($data['ta_plan_cost_order']==1)
                {
                    $order[]='tp.ta_plan_cost asc';
                }else if($data['ta_plan_cost_order']==-1)
                {
                    $order[]='tp.ta_plan_cost desc';
                }
            }

            if(array_key_exists('tourists_count_order',$data))
            {
                if($data['tourists_count_order']==1)
                {
                    $order[]='tp.tourists_count asc';
                }else if($data['tourists_count_order']==-1)
                {
                    $order[]='tp.tourists_count desc';
                }
            }

            if(!count($order))
            {
                $order='tp.created_at desc';
            }

            if($gtc){
                $query = $sql->select()
                        ->from($this->tableName)
                        ->join(array('ta'=>'ta_details'), 'ta.id=tp.ta_id',array("ta_name"), Select::JOIN_LEFT)
                        ->join(array('tc'=>'ta_consultant_details'), 'tc.id=tp.ta_cons_id',array("name"), Select::JOIN_LEFT)
                        ->where($where)
                        ->order($order);
            }else{
                $query = $sql->select()
                        ->from($this->tableName)
                        ->join(array('ta'=>'ta_details'), 'ta.id=tp.ta_id',array("ta_name"), Select::JOIN_LEFT)
                        ->join(array('tc'=>'ta_consultant_details'), 'tc.id=tp.ta_cons_id',array("name"), Select::JOIN_LEFT)
                        ->where($where)
                        ->limit($data['limit'])
                        ->offset($data['offset'])
                        ->order($order);
            }
            // echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tbeData = array();
            foreach ($resultSet as $row){
                $tbeData[] = $row;
            }
            if($gtc)
                return count($tbeData);
            else
                return $tbeData;
        }catch (\Exception $e){            
            return array();
        }
    }
}