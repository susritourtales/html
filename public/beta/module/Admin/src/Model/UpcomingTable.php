<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class UpcomingTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("u" => "upcoming");
    }

    public function addUpcoming(Array $data)
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
   public function updateUpcoming($data,$where)
   {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e)
        {
            return false;
        }
   }  
   public function getUpcomingDetails($upcomingId){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array("u" => "upcoming"))
                ->where(array("u.id" => $upcomingId));
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
    public function getUpcomingList($data=array('limit'=>10,'offset'=>0))
    {
    try
    {
        $where=new Where();
        $where->equalTo('status',1);
        if(array_key_exists('tour_type',$data))
        {
            $where->and->equalTo("tour_type",$data['tour_type']);
        }
        if(array_key_exists('month_year',$data))
        {
            $where->and->like(new \Laminas\Db\Sql\Expression("date('Mon-YY',(month_year))"),'%'.$data['month_year']."%");
        }
        if(array_key_exists('country',$data))
        {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(country)"),'%'.$data['country']."%");
        }
        if(array_key_exists('state',$data))
        {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(state)"),'%'.$data['state']."%");
        }        
        if(array_key_exists('city',$data))
        {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(city)"),'%'.$data['city']."%");
        }
        if(array_key_exists('place',$data))
        {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(place)"),'%'.$data['place']."%");
        }
        if(array_key_exists('languages',$data))
        {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(languages)"),'%'.$data['languages']."%");
        }
        $order=array();
        if(array_key_exists('month_year_order',$data))
        {
            if($data['month_year_order']==1)
            {
                $order[]='month_year asc';
            }else if($data['month_year_order']==-1)
            {
                $order[]='month_year desc';
            }
        }
        if(array_key_exists('country_order',$data))
        {
            if($data['country_order']==1)
            {
                $order[]='country asc';
            }else if($data['country_order']==-1)
            {
                $order[]='country desc';
            }
        }
        if(array_key_exists('state_order',$data))
        {
            if($data['state_order']==1)
            {
                $order[]='state asc';
            }else if($data['state_order']==-1)
            {
                $order[]='state desc';
            }
        }
        
        if(array_key_exists('city_order',$data))
        {
            if($data['city_order']==1)
            {
                $order[]='city asc';
            }else if($data['city_order']==-1)
            {
                $order[]='city desc';
            }

        }
        if(array_key_exists('place_order',$data))
        {
            if($data['place_order']==1)
            {
                $order[]='place asc';
            }else if($data['place_order']==-1)
            {
                $order[]='place desc';
            }
        }
        if(array_key_exists('languages_order',$data))
        {
            if($data['languages_order']==1)
            {
                $order[]='languages asc';
            }else if($data['languages_order']==-1)
            {
                $order[]='languages desc';
            }
        }
        if(!count($order))
        {
            $order=array('id asc');
        }
        $sql = $this->getSql();
        if($data['api'] == '1'){
            $query = $sql->select()
            ->from($this->tableName)
            ->columns(array("tourism_place_id"=>"id","country_name"=>"country","state_name"=>"state","city_name"=>"city","place_name"=>"place","tour_type", "language_ids"=>"languages", "month_year"=>"month_year"))
            ->where($where)
            ->order($order);
            if($data['limit']!=-1)
            {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            }
           /* 
            ->limit($data['limit'])
            ->offset($data['offset'])
              */
        }
        else {
            $query = $sql->select()
            ->from($this->tableName)
            ->columns(array("id","country","state","city","place","tour_type", "languages", "month_year"))
            ->where($where)
            ->order($order);
            if($data['limit']!=-1)
            {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            }
            /* 
            ->limit($data['limit'])
            ->offset($data['offset'])
              */
        }        
        //echo $sql->getSqlStringForSqlObject($query);exit;
        $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
        $countries = array();

        foreach($resultSet as $row){
            if($data['api'] == '1'){
                $rqry = $sql->select()
                    ->columns(array('language_name'=>new \Laminas\Db\Sql\Expression('group_concat(`l`.`name`)')))
                    ->from(array('l' => 'languages'))
                    ->where('`l`.`id` in (' . $row['language_ids'] . ')');
                //echo $sql->getSqlStringForSqlObject($rqry) . "\n"; //exit;
                $lres = $sql->prepareStatementForSqlObject($rqry)->execute();
                $pcnt = array();
                foreach($lres as $lr){
                    $lnames = $lr;
                }
                
                $row['language_name'] = $lnames['language_name'];
                $countries[] = $row;
            }
            else
                $countries[] = $row;
        }

        return $countries;
    }catch(\Exception $e){
            print_r($e->getMessage());
        exit;
        return array();
    }
}
  
    public function getUpcomingCount($data=array()){
        try{
            $where=new Where();
            $where->equalTo('status',1);
            if(array_key_exists('tour_type',$data))
            {
                $where->and->equalTo("tour_type",$data['tour_type']);
            }
            if(array_key_exists('country',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(country)"),'%'.$data['country']."%");
            }
            if(array_key_exists('state',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(state)"),'%'.$data['state']."%");
            }        
            if(array_key_exists('city',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(city)"),'%'.$data['city']."%");
            }
            if(array_key_exists('place',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(place)"),'%'.$data['place']."%");
            }
            if(array_key_exists('languages',$data))
            {
                $where->and->like("languages",'%'.$data['languages']."%");
            }
           
            $sql = $this->getSql();
            $query = $sql->select()
            ->from($this->tableName)
            ->columns(array("id","country","state","city","place","tour_type", "languages"))
            ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();

            foreach($resultSet as $row){
                $countries[] = $row;
            }
            return $countries;
        }catch(\Exception $e){
            return array();
        }
    }
}