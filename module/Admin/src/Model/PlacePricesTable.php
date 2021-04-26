<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 28/8/19
 * Time: 5:16 PM
 */

namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class PlacePricesTable extends  BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("p" => "place_prices");
    }

    public function addPlacePrice(Array $data)
    {
        try{
            $insert = $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e){
             print_r($e->getMessage());
            exit;
            return array("success" => false);
        }
    }
    public function checkPriceAdded($data)
    {
        try {
            $where=new Where();

            $where=$where->equalTo('p.status',1)->equalTo('p.tour_type',$data['tour_type']);
               if($data['tour_type']==\Admin\Model\PlacePrices::tour_type_India_tour || $data['tour_type'] ==\Admin\Model\PlacePrices::tour_type_Spiritual_tour)
               {
                   $where->equalTo('p.state_id',$data['state_id']);

               }else{
                   $where->equalTo('p.country_id',$data['country_id']);

               }

               if(!isset($data['city_id']))
               {
                   $where->equalTo('p.city_id',0);
               }else if(isset($data['city_id']))
               {
                   $where->equalTo('p.city_id',$data['city_id']);
               }


            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("place_price_id",'city_id','place_id'))
                ->where($where);

            $field = array();
            /*echo $sql->getSqlStringForSqlObject($query);
            exit;*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row;
            }


                return $field;


        } catch (\Exception $e) {
            return array();
        }
    }
    public function getPlacesForPrivilageUser($data)
    {
        try{


        $where=new Where();
        $where->equalTo('p.tour_type',$data['tour_type'])->equalTo('p.status',1);
        $sql = $this->getSql();
        $query = $sql->select()
            ->from($this->tableName)
            ->columns(array("place_price_id","price",'place_id'));
        if($data['tour_type']==\Admin\Model\PlacePrices::tour_type_City_tour) {
         //   $query=$query->join(array('tp' => 'tourism_places'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name' => new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"), 'tourism_place_id'));

        }else{
            $query=$query->join(array('tp' => 'tourism_places'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name', 'tourism_place_id'));
        }
        $query=$query->join(array('c'=>'countries'),'p.country_id=c.id',array('country_name'))
            ->join(array('s'=>'states'),'p.state_id=s.id',array('state_name'),Select::JOIN_LEFT)
            ->join(array('ci'=>'cities'),'p.city_id=ci.id',array('city_name'))
            ->where($where);
        if($data['tour_type']==\Admin\Model\PlacePrices::tour_type_City_tour)
        {
            $query= $query->group('p.place_price_id');
        }
        $query=$query->order(array('p.updated_at desc'));



        $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
        $countries = array();

        foreach($resultSet as $row){
            $countries[] = $row;
        }
        return $countries;

     }catch (\Exception $e)
        {
            return array();
        }
    }
    public function getPlacesList($data=array('limit'=>10,'offset'=>0))
    {
        try
        {
            $where=new Where();
            $where->equalTo('p.status',1)->and->equalTo('tour_type',$data['tour_type']);
            $order=array();
            if(array_key_exists('country',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"),'%'.strtolower($data['country'])."%");
            }
            if(array_key_exists('state',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"),'%'.strtolower($data['state'])."%");
            }
            if(array_key_exists('city',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ci.city_name)"),'%'.strtolower($data['city'])."%");
            }
            if(array_key_exists('place_name',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"),'%'.strtolower($data['place_name'])."%");
            }

            if(array_key_exists('country_order',$data))
            {
                if($data['country_order']==1)
                {
                    $order[]='c.country_name asc';
                }else if($data['country_order']==-1)
                {
                    $order[]='c.country_name desc';
                }

            }
            if(array_key_exists('state_order',$data))
            {
                if($data['state_order']==1)
                {
                    $order[]='s.state_name asc';
                }else if($data['state_order']==-1)
                {
                    $order[]='s.state_name desc';
                }

            }
            if(array_key_exists('city_order',$data))
            {
                if($data['city_order']==1)
                {
                    $order[]='ci.city_name asc';
                }else if($data['city_order']==-1)
                {
                    $order[]='ci.city_name desc';
                }

            }
            if(array_key_exists('place_name_order',$data))
            {
                if($data['place_name_order']==1)
                {
                    $order[]='tp.place_name asc';
                }else if($data['place_name_order']==-1)
                {
                    $order[]='tp.place_name desc';
                }

            }



            if(!count($order))
            {
                $order=array('p.updated_at desc');
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("place_price_id","price"));
                if($data['tour_type']==\Admin\Model\PlacePrices::tour_type_City_tour) {
                 $query=$query->join(array('tp' => 'tourism_places'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name' => new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"), 'tourism_place_id'), Select::JOIN_LEFT);

               }else{
                    $query=$query->join(array('tp' => 'tourism_places'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name', 'tourism_place_id'), Select::JOIN_LEFT);

                }
                $query=$query->join(array('c'=>'countries'),'p.country_id=c.id',array('country_name'),Select::JOIN_LEFT)
                ->join(array('s'=>'states'),'p.state_id=s.id',array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),'p.city_id=ci.id',array('city_name'),Select::JOIN_LEFT)
                ->where($where);
                  if($data['tour_type']==\Admin\Model\PlacePrices::tour_type_City_tour)
                  {
                      $query= $query->group('p.place_price_id');
                  }
                $query=$query->order($order);
                  if($data['limit']!=-1)
                  {
                      $query->limit($data['limit'])
                          ->offset($data['offset']);
                  }
                  
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            foreach($resultSet as $row){
                $countries[] = $row;
            }

            return $countries;
        }catch(\Exception $e){
               print_r($e->getMessage());
            exit;
            return array();
        }
    }
    public function getPlacesListCount($data=array())
    {
        try
        {
            $where=new Where();
            $where->and->equalTo('p.status',1)->and->equalTo('tour_type',$data['tour_type']);
            $order=array();
            if(array_key_exists('country',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"),'%'.strtolower($data['country'])."%");
            }
            if(array_key_exists('state',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"),'%'.strtolower($data['state'])."%");
            }
            if(array_key_exists('city',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ci.city_name)"),'%'.strtolower($data['city'])."%");
            }
            if(array_key_exists('place_name',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"),'%'.strtolower($data['place_name'])."%");
            }

            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("place_price_id","price"));
  if($data['tour_type']==\Admin\Model\PlacePrices::tour_type_City_tour) {
      $query=$query->join(array('tp' => 'tourism_places'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name' => new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"), 'tourism_place_id'), Select::JOIN_LEFT);

  }else{
      $query=$query->join(array('tp' => 'tourism_places'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name', 'tourism_place_id'), Select::JOIN_LEFT);

  }                $query=$query->join(array('c'=>'countries'),'tp.country_id=c.id',array('country_name'))
                ->join(array('s'=>'states'),'tp.state_id=s.id',array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),'tp.city_id=ci.id',array('city_name'))
                ->where($where);
            if($data['tour_type']==\Admin\Model\PlacePrices::tour_type_City_tour)
            {
                $query= $query->group('p.place_price_id');
            }


                $query=$query->order('p.updated_at desc');
             /*echo $sql->getSqlStringForSqlObject($query);
                        exit;*/

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
    public function getCityTourPrice($cityIds)
    {
        try {
            $sql = $this->getSql();
            $where=new Where();

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('price'=>new \Laminas\Db\Sql\Expression('sum(`p`.`price`)')))
                ->where($where->in('city_id',$cityIds));

            $field = array();
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row['price'];
            }

            if ($field) {
                return $field;
            } else {
                return 0;
            }

        } catch (\Exception $e) {
            return 0;
        }
    }
    public function getPlacePrices($where)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("place_price_id",'country_id','city_id',"place_id",'price'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourismFiles = array();

            foreach($resultSet as $row){
                $placePrices[] = $row;
            }
                return $placePrices;
        }catch(\Exception $e){
                  print_r($e->getMessage());
                  exit;
            return array();
        }
    }
    public function updatePlacePrice($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e)
        {
            return false;
        }
    }
    public function deleteTourPlace($deleteIds)
    {
        try
        {
            $sql=$this->getSql();
            $where=new Where();
            $query=$sql->update('place_prices')->set(array('status'=>0,'updated_at'=>date("Y-m-d H:i:s")))->where($where->like('place_id',"%,".$deleteIds.",%"));
            /* echo $sql->getSqlStringForSqlObject($query);
             exit;*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            return true;
        }catch (\Exception $e)
        {
            return false;
        }
    }
    public function deletePlace($placeId,$priceId)
    {
        try
        {
            $sql=$this->getSql();
            $where=new Where();
            $query=$sql->update('place_prices')->set(array('place_id'=>new \Laminas\Db\Sql\Expression("REPLACE(`place_id`,'".','.$placeId.','."', ',')"),'updated_at'=>date("Y-m-d H:i:s")))->where($where->equalTo('place_price_id',$priceId));

             $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            return true;
        }catch (\Exception $e)
        {
              print_r($e->getMessage());
              exit;
            return false;
        }
    }
}