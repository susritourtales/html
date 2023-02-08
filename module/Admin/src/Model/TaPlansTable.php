<?php


namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TaPlansTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("tp" => "ta_plans");
    }

    public function addTaPlan(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }
    
   public function setTaPlan($data,$where)
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

    public function getTaPlanDetails($planId){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->where(array("tp.id" => $planId));
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
    }

    public function getTaPlansAdmin($data=array('limit'=>10,'offset'=>0), $gtc=0){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('status',1);
            $order=array();
            
            if(array_key_exists('plan_name',$data))
            {
                $where->and->like("tp.plan_name",'%'.$data['plan_name']."%");
            }
            if(array_key_exists('mtc',$data))
            {
                $where->and->like("tp.mtc",'%'.$data['mtc']."%");
            }
            if(array_key_exists('duration',$data))
            {
                $where->and->like("tp.duration",'%'.$data['duration']."%");
            }
            if(array_key_exists('cost',$data))
            {
                $where->and->like("tp.cost",'%'.$data['cost']."%");
            }
            if(array_key_exists('active',$data))
            {
                $where->and->like("tp.active",'%'.$data['active']."%");
            }
            if(array_key_exists('plan_name_order',$data))
            {
                if($data['plan_name_order']==1)
                {
                    $order[]='tp.plan_name asc';
                }else if($data['plan_name_order']==-1)
                {
                    $order[]='tp.plan_name desc';
                }
            }
            if(array_key_exists('mtc_order',$data))
            {
                if($data['mtc_order']==1)
                {
                    $order[]='tp.mtc asc';
                }else if($data['mtc_order']==-1)
                {
                    $order[]='tp.mtc desc';
                }
            }
            if(array_key_exists('duration_order',$data))
            {
                if($data['duration_order']==1)
                {
                    $order[]='tp.duration asc';
                }else if($data['duration_order']==-1)
                {
                    $order[]='tp.duration desc';
                }
            }
            if(array_key_exists('cost_order',$data))
            {
                if($data['cost_order']==1)
                {
                    $order[]='tp.cost asc';
                }else if($data['cost_order']==-1)
                {
                    $order[]='tp.cost desc';
                }
            }
            if(array_key_exists('active_order',$data))
            {
                if($data['active_order']==1)
                {
                    $order[]='tp.active asc';
                }else if($data['active_order']==-1)
                {
                    $order[]='tp.active desc';
                }
            }

            if(!count($order))
            {
                $order='tp.created_at desc';
            }

            if($gtc){
                $query = $sql->select()
                        ->from($this->tableName)
                        ->where($where)
                        ->order($order);
            }else{
                $query = $sql->select()
                        ->from($this->tableName)
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