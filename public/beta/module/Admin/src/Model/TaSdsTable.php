<?php


namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TaSdsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("ts" => "ta_sds");
    }

    public function addTaSDS(Array $data)
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
    
   public function setTaSds($data,$where)
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
                ->columns($column)
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

    public function getTouristSds($data){
        try {
            $sql = $this->getSql();
            $order=array();
            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_ta_logo));
            if((!is_null($data['limit'])) && (!is_null($data['offset']))){
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array('id', 'doj'=>new \Laminas\Db\Sql\Expression('DATE(`ts`.`travel_date`)')))
                    ->join(array('tbd'=>'tbe_details'), 'ts.tbe_id=tbd.user_id',array("ta_id"),Select::JOIN_LEFT)
                    ->join(array('tad'=>'ta_details'), 'tad.id=tbd.ta_id',array("ta_name"),Select::JOIN_LEFT)
                    ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = tad.id',array('file_path','tourism_file_id','file_extension_type','file_language_type','file_name'),Select::JOIN_LEFT)
                    ->where(array('tourist_mobile'=>$data['mobile']))
                    ->limit($data['limit'])
                    ->offset($data['offset']);
            }else{
                $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('id', 'doj'=>new \Laminas\Db\Sql\Expression('DATE(`ts`.`travel_date`)')))
                ->join(array('tbd'=>'tbe_details'), 'ts.tbe_id=tbd.user_id',array("ta_id"),Select::JOIN_LEFT)
                ->join(array('tad'=>'ta_details'), 'tad.id=tbd.ta_id',array("ta_name"),Select::JOIN_LEFT)
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = tad.id',array('file_path','tourism_file_id','file_extension_type','file_language_type','file_name'),Select::JOIN_LEFT)
                ->where(array('tourist_mobile'=>$data['mobile']))
                ->order('id asc');;
            }

            $field = array();
            //echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $sdsData = array();
            foreach ($resultSet as $row){
                $temp['id'] = $row['id'];
                $temp['travel_date'] = date('d-m-Y', strtotime($row['doj']));
                $temp['sds_start_date'] = date('d-m-Y', strtotime($row['doj'] . " - 3 days"));
                $temp['sds_end_date'] = date('d-m-Y', strtotime($row['doj'] . " + 15 days"));
                $temp['ta_name'] = $row['ta_name'];
                $temp['file_path'] = $row['file_path'];
                $temp['file_name'] = $row['file_name'];
                $sdsData[] = $temp;
            }

            return $sdsData;

        } catch (\Exception $e) {
            return array();
        }
    }

    public function getTBETouristsdetails($data=array('limit'=>10,'offset'=>0), $gtc=0, $warr){
        try{
            $sql = $this->getSql();
            $where=new Where();
            //$where->equalTo(key($warr),array_values($warr)[0]);
            foreach($warr as $key => $value) {
                //echo "$key is at $value";
                $where->equalTo($key, $value);
              }
            //$where->equalTo("ts.tbe_id",$tbeId);

            if(array_key_exists('tourist_name',$data))
            {
                $where->and->like("ts.tourist_name",'%'.$data['tourist_name']."%");
            }
            if(array_key_exists('tourist_mobile',$data))
            {
                $where->and->like("ts.tourist_mobile",'%'.$data['tourist_mobile']."%");
            }
            if(array_key_exists('upc',$data))
            {
                $where->and->like("ts.upc",'%'.$data['upc']."%");
            }
            if(array_key_exists('tbe_id',$data))
            {
                $where->and->like("ts.tbe_id",'%'.$data['tbe_id']."%");
            }
            if(array_key_exists('travel_date',$data))
            {
                $where->and->like("ts.travel_date",'%'.$data['travel_date']."%");
            }

            if(array_key_exists('tourist_name_order',$data))
            {
                if($data['tourist_name_order']==1)
                {
                    $order[]='ts.tourist_name asc';
                }else if($data['tourist_name_order']==-1)
                {
                    $order[]='ts.tourist_name desc';
                }
            }
            if(array_key_exists('tourist_mobile_order',$data))
            {
                if($data['tourist_mobile_order']==1)
                {
                    $order[]='ts.tourist_mobile asc';
                }else if($data['tourist_mobile_order']==-1)
                {
                    $order[]='ts.tourist_mobile desc';
                }
            }
            if(array_key_exists('upc_order',$data))
            {
                if($data['upc_order']==1)
                {
                    $order[]='ts.upc asc';
                }else if($data['upc_order']==-1)
                {
                    $order[]='ts.upc desc';
                }
            }
            if(array_key_exists('tbe_id_order',$data))
            {
                if($data['tbe_id_order']==1)
                {
                    $order[]='ts.tbe_id asc';
                }else if($data['tbe_id_order']==-1)
                {
                    $order[]='ts.tbe_id desc';
                }
            }

            if(array_key_exists('travel_date_order',$data))
            {
                if($data['travel_date_order']==1)
                {
                    $order[]='ts.travel_date asc';
                }else if($data['travel_date_order']==-1)
                {
                    $order[]='ts.travel_date desc';
                }
            }

            if(!count($order))
            {
                $order='ts.created_at desc';
            }

            if($gtc){
                $query = $sql->select()
                        ->columns(array('id', 'tourist_name', 'mobile_country_code', 'tourist_mobile', 'travel_date'=>new \Laminas\Db\Sql\Expression('DATE(`ts`.`travel_date`)'), 'upc', 'tbe_id'))
                        ->join(array('tbd'=>'tbe_details'), 'ts.tbe_id=tbd.user_id',array("tbe_name", "role"),Select::JOIN_LEFT)
                        ->from($this->tableName)
                        ->where($where)
                        ->order($order);
            }else{
                $query = $sql->select()
                        ->columns(array('id', 'tourist_name', 'mobile_country_code', 'tourist_mobile', 'travel_date'=>new \Laminas\Db\Sql\Expression('DATE(`ts`.`travel_date`)'), 'upc', 'tbe_id'))
                        ->join(array('tbd'=>'tbe_details'), 'ts.tbe_id=tbd.user_id',array("tbe_name", "role"),Select::JOIN_LEFT)
                        ->from($this->tableName)
                        ->where($where)
                        ->limit($data['limit'])
                        ->offset($data['offset'])
                        ->order($order);
            }
            // echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $ulData = array();
            foreach ($resultSet as $row){
                $ulData[] = $row;
            }

            if($gtc)
                return count($ulData);
            else
                return $ulData;

        }catch (\Exception $e){
            
            return array();
        }
    }
}