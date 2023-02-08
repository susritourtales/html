<?php


namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TaDetailsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("td" => "ta_details");
    }

    public function addTa(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }

   public function setTaDetails($data,$where)
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

    public function getTaDetails($taId){
        try{
            $sql = $this->getSql();
            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_ta_logo));
            $query = $sql->select()
                ->from($this->tableName)
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = td.id',array('file_path','tourism_file_id','file_extension_type','file_language_type','file_name'),Select::JOIN_LEFT)
                ->join(array('c'=>'ta_consultant_details'),'td.cons_mobile=c.mobile',array('cons_name'=>'name', 'cons_mobile'=>'mobile'),Select::JOIN_LEFT)                
                ->where(array("td.id" => $taId, "td.status" => 1));

            //echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tadetails=array();
            foreach ($resultSet as $row) {
                $tadetails[] = $row;
            }
            return $tadetails;

        }catch (\Exception $e){
            
            return array();
        }
    }

    public function getTaListAdmin($data=array('limit'=>10,'offset'=>0), $gtc=0){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('td.status',1);
            $order=array();
            
            if(array_key_exists('ta_name',$data))
            {
                $where->and->like("td.ta_name",'%'.$data['ta_name']."%");
            }
            if(array_key_exists('ta_mobile',$data))
            {
                $where->and->like("td.ta_mobile",'%'.$data['ta_mobile']."%");
            }
            if(array_key_exists('ta_email',$data))
            {
                $where->and->like("td.ta_email",'%'.$data['ta_email']."%");
            }
            if(array_key_exists('ae_name',$data))
            {
                $where->and->like("td.ae_name",'%'.$data['ae_name']."%");
            }
            if(array_key_exists('ae_mobile',$data))
            {
                $where->and->like("td.ae_mobile",'%'.$data['ae_mobile']."%");
            }
            if(array_key_exists('ae_email',$data))
            {
                $where->and->like("td.ae_email",'%'.$data['ae_email']."%");
            }
            if(array_key_exists('cons_mobile',$data))
            {
                $where->and->like("td.cons_mobile",'%'.$data['cons_mobile']."%");
            }
            if(array_key_exists('tac',$data))
            {
                $where->and->like("td.tac",'%'.$data['tac']."%");
            }

            if(array_key_exists('ta_name_order',$data))
            {
                if($data['ta_name_order']==1)
                {
                    $order[]='td.ta_name asc';
                }else if($data['ta_name_order']==-1)
                {
                    $order[]='td.ta_name desc';
                }
            }
            if(array_key_exists('ta_mobile_order',$data))
            {
                if($data['ta_mobile_order']==1)
                {
                    $order[]='td.ta_mobile asc';
                }else if($data['ta_mobile_order']==-1)
                {
                    $order[]='td.ta_mobile desc';
                }
            }
            if(array_key_exists('ta_email_order',$data))
            {
                if($data['ta_email_order']==1)
                {
                    $order[]='td.ta_email asc';
                }else if($data['ta_email_order']==-1)
                {
                    $order[]='td.ta_email desc';
                }
            }

            if(array_key_exists('ae_name_order',$data))
            {
                if($data['ae_name_order']==1)
                {
                    $order[]='td.ae_name asc';
                }else if($data['ae_name_order']==-1)
                {
                    $order[]='td.ae_name desc';
                }
            }
            if(array_key_exists('ae_mobile_order',$data))
            {
                if($data['ae_mobile_order']==1)
                {
                    $order[]='td.ae_mobile asc';
                }else if($data['ae_mobile_order']==-1)
                {
                    $order[]='td.ae_mobile desc';
                }
            }
            if(array_key_exists('ae_email_order',$data))
            {
                if($data['ae_email_order']==1)
                {
                    $order[]='td.ae_email asc';
                }else if($data['ae_email_order']==-1)
                {
                    $order[]='td.ae_email desc';
                }
            }

            if(array_key_exists('cons_mobile_order',$data))
            {
                if($data['cons_mobile_order']==1)
                {
                    $order[]='td.cons_mobile asc';
                }else if($data['cons_mobile_order']==-1)
                {
                    $order[]='td.cons_mobile desc';
                }
            }
            if(array_key_exists('tac_order',$data))
            {
                if($data['tac_order']==1)
                {
                    $order[]='td.tac asc';
                }else if($data['tac_order']==-1)
                {
                    $order[]='td.tac desc';
                }
            }

            if(!count($order))
            {
                $order='td.created_at desc';
            }

            if($gtc){
                $query = $sql->select()
                        ->from($this->tableName)
                        ->join(array('c'=>'ta_consultant_details'),'td.cons_mobile=c.mobile',array('cons_name'=>'name', 'cons_mobile'=>'mobile', 'ta_cons_id'=>'id'),Select::JOIN_LEFT)
                        ->where($where)
                        ->order($order);
            }else{
                $query = $sql->select()
                        ->from($this->tableName)
                        ->join(array('c'=>'ta_consultant_details'),'td.cons_mobile=c.mobile',array('cons_name'=>'name', 'cons_mobile'=>'mobile', 'ta_cons_id'=>'id'),Select::JOIN_LEFT)
                        ->where($where)
                        ->limit($data['limit'])
                        ->offset($data['offset'])
                        ->order($order);
            }
            // echo $sql->getSqlStringForSqlObject($query);exit;
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

    public function getSETAsAdmin($data=array('limit'=>10,'offset'=>0), $gtc=0){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where->equalTo('td.cons_mobile',$data['cons_mobile']);            
            $order=array();
            
            if(array_key_exists('ta_name',$data))
            {
                $where->and->like("td.ta_name",'%'.$data['ta_name']."%");
            }
            if(array_key_exists('ta_mobile',$data))
            {
                $where->and->like("td.ta_mobile",'%'.$data['ta_mobile']."%");
            }

            if(array_key_exists('ta_name_order',$data))
            {
                if($data['ta_name_order']==1)
                {
                    $order[]='td.ta_name asc';
                }else if($data['ta_name_order']==-1)
                {
                    $order[]='td.ta_name desc';
                }
            }
            if(array_key_exists('ta_mobile_order',$data))
            {
                if($data['ta_mobile_order']==1)
                {
                    $order[]='td.ta_mobile asc';
                }else if($data['ta_mobile_order']==-1)
                {
                    $order[]='td.ta_mobile desc';
                }
            }

            if(!count($order))
            {
                $order='td.created_at desc';
            }

            if($gtc){
                $query = $sql->select()
                ->columns(array("ta_id"=>"id","ta_name","ta_mobile", "tac"))
                ->from($this->tableName)
                ->join(array('se'=>'ta_consultant_details'), 'se.mobile=td.cons_mobile',array("se_id"=>"id","name","mobile"),Select::JOIN_LEFT)
                ->where($where)
                ->order($order);

            }else{
                $query = $sql->select()
                ->columns(array("ta_id"=>"id","ta_name","ta_mobile", "tac"))
                ->from($this->tableName)
                ->join(array('se'=>'ta_consultant_details'), 'se.mobile=td.cons_mobile',array("se_id"=>"id","name","mobile"),Select::JOIN_LEFT)
                ->where($where)
                ->limit($data['limit'])
                ->offset($data['offset'])
                ->order($order);
            }
            //echo $sql->getSqlStringForSqlObject($query);exit;

            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $setaArr=array();
            foreach ($result as $row) {
                $spqry = $sql->select()
                    ->columns(array('amount_paid'=>new \Laminas\Db\Sql\Expression('SUM(`ct`.`amount`)')))
                    ->from(array('ct' => 'ta_cons_transactions'))
                    ->where(array('ct.ta_id'=> $row['ta_id'], 'ct.transaction_type'=> \Admin\Model\TaConsultantTransactions::transaction_paid));
                $stres = $sql->prepareStatementForSqlObject($spqry)->execute();
                $stresArr = array();
                foreach($stres as $r){
                    $stresArr[] = $r;
                }
                $row['amount_paid'] = $stresArr[0]['amount_paid'];
                $setaArr[] = $row;
            }

            if($gtc)
                return count($setaArr);
            else
                return $setaArr;
        }catch (\Exception $e)
        {
            /* print_r($e->getMessage());
            exit; */
            return array();
        }
    }
}