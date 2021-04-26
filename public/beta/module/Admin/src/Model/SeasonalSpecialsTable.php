<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 19/9/19
 * Time: 12:25 PM
 */

namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Predicate;

class SeasonalSpecialsTable extends  BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("sp" => "seasonal_specials");
    }

    public function addSeasonalSpecials(Array $data)
    {
        try
        {
            $insert = $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e)
        {
                 /*print_r($e->getMessage());
                 exit;*/
            return array("success" => false);
        }
    }
    public function updateSeasonalSpecials($data,$where)
    {
        try
        {
            return $this->update($data,$where);

        }catch (\Exception $e)
        {
            return false;
        }

    }
    public function getSeasonalSpecialsList($data=array('limit'=>10,'offset'=>0))
    {
        try
        {
            $where=new Where();
            $where->equalTo('sp.status',1);
            $order=array();
            if(array_key_exists('country',$data))
            {
                $where->and->like("c.country_name",'%'.$data['country']."%");
            }
            if(array_key_exists('state',$data))
            {
                $where->and->like("s.state_name",'%'.$data['state']."%");
            }
            if(array_key_exists('city',$data))
            {
                $where->and->like("ci.city_name",'%'.$data['city']."%");
            }
            if(array_key_exists('place_name',$data))
            {
                $where->and->like("tp.place_name",'%'.$data['place_name']."%");
            }
            if(array_key_exists('price',$data))
            {
                $where->and->like("sp.price",'%'.$data['price']."%");
            }
            if(array_key_exists('start_date',$data))
            {
                $where->and->like("sp.start_date",'%'.$data['start_date']."%");
            }
            if(array_key_exists('seasonal_name',$data))
            {
                $where->and->like("sp.seasonal_name",'%'.$data['seasonal_name']."%");
            }
            if(array_key_exists('end_date',$data))
            {
                $where->and->like("sp.end_date",'%'.$data['end_date']."%");
            }
            if(array_key_exists('discount_price',$data))
            {
                $where->and->like("sp.discount_price",'%'.$data['discount_price']."%");
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
            if(array_key_exists('price_order',$data))
            {
                if($data['price_order']==1)
                {
                    $order[]='sp.price asc';
                }else if($data['price_order']==-1)
                {
                    $order[]='sp.price desc';
                }

            }
                  if(array_key_exists('discount_price_order',$data))
            {
                if($data['discount_price_order']==1)
                {
                    $order[]='sp.discount_price asc';
                }else if($data['discount_price_order']==-1)
                {
                    $order[]='sp.discount_price desc';
                }

            }

            if(array_key_exists('start_date_order',$data))
            {
                if($data['start_date_order']==1)
                {
                    $order[]='sp.start_date asc';
                }else if($data['start_date_order']==-1)
                {
                    $order[]='sp.start_date desc';
                }

            }  if(array_key_exists('seasonal_name_order',$data))
            {
                if($data['seasonal_name_order']==1)
                {
                    $order[]='sp.seasonal_name asc';
                }else if($data['seasonal_name_order']==-1)
                {
                    $order[]='sp.seasonal_name desc';
                }

            }if(array_key_exists('end_date_order',$data))
            {
                if($data['end_date_order']==1)
                {
                    $order[]='sp.end_date asc';
                }else if($data['end_date_order']==-1)
                {
                    $order[]='sp.end_date desc';
                }

            }

            if(!count($order))
            {
                $order=array('sp.updated_at desc');
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("seasonal_name","seasonal_type",'no_of_days','seasonal_description','price','discount_price','seasonal_special_id','start_date','end_date'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`sp`.`place_ids`)"),array('place_name'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"),'tourism_place_id'))
                ->where($where)
                ->group('sp.seasonal_special_id')
                ->order($order);
            if($data['limit']!=-1)
            {
                $query->limit($data['limit'])
                    ->offset($data['offset']);
            }

                 /* echo '<pre>';
            print_r($sql->getSqlStringForSqlObject($query));
            exit;*/
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
    public function getSeasonalSpecialsListCount($data=array())
    {
        try
        {
            $where=new Where();
            $where->and->equalTo('sp.status',1);
            $order=array();
            if(array_key_exists('country',$data))
            {
                $where->and->like("c.country_name",'%'.$data['country']."%");
            }
            if(array_key_exists('state',$data))
            {
                $where->and->like("s.state_name",'%'.$data['state']."%");
            }
            if(array_key_exists('city',$data))
            {
                $where->and->like("ci.city_name",'%'.$data['city']."%");
            }
            if(array_key_exists('place_name',$data))
            {
                $where->and->like("tp.place_name",'%'.$data['place_name']."%");
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("seasonal_name","seasonal_type",'seasonal_description','price','discount_price','seasonal_special_id','start_date','end_date'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`sp`.`place_ids`)"),array('place_name'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"),'tourism_place_id'))
                ->where($where)
                ->group('sp.seasonal_special_id')
                ->order($order);
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
    public function seasonalSpecialDetails($seasonalSpecialId)
    {
        try
        {
            $where=new Where();
            $where->and->equalTo('sp.status',1)->equalTo('sp.seasonal_special_id',$seasonalSpecialId);
            $order=array();

            $sql = $this->getSql();
            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_seasonal_files,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("seasonal_name",'no_of_days',"seasonal_type",'seasonal_description','price','discount_price','seasonal_special_id','start_date','end_date','place_ids'))
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = sp.seasonal_special_id',array('file_path'),Select::JOIN_LEFT)
                ->where($where)
                ->group('sp.seasonal_special_id')
                ->order($order);
            /*echo $sql->getSqlStringForSqlObject($query);
                       exit;*/

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $seasonalSpecial = array();

            foreach($resultSet as $row){
                $seasonalSpecial = $row;
            }

            return $seasonalSpecial;
        }catch(\Exception $e){
            return array();
        }
    }
    public function seasonalSpecials($data=array('limit'=>10,'offset'=>0)) //public function seasonalSpecials($data=array('limit'=>10,'offset'=>0))
    {
        try
        {
            $where=new Where();
            $where->equalTo('sp.status',1);
            //$where->and->equalTo('sp.status',1)->greaterThanOrEqualTo('sp.end_date',date("Y-m-d")); // - Manjary
            $order=array();

                 if(array_key_exists('tour_type',$data)&& $data['tour_type']!='' && $data['tour_type'])
                 {
                     $where->equalTo('seasonal_type',$data['tour_type']);
                 }
                 if(array_key_exists('country_id',$data) && $data['country_id']!='' && $data['country_id'])
                 {
                     $where->equalTo('country_id',$data['country_id']);
                 }
            if(array_key_exists('search',$data) && $data['search']!='' && $data['search'])
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(seasonal_name)"),new \Laminas\Db\Sql\Expression('LOWER("%'.$data['search'].'%")'));
                 //    $where->like('seasonal_name',"%".$data['search']."%");
            }
            $sql = $this->getSql();

            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_seasonal_files,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("seasonal_name",'no_of_days',"seasonal_type",'seasonal_description','price','discount_price','seasonal_special_id','start_date','end_date'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`sp`.`place_ids`)"),array('place_name'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"),'tourism_place_id'))
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = sp.seasonal_special_id',array('file_path'),Select::JOIN_LEFT)

                ->where($where)
                ->group('sp.seasonal_special_id')
                ->order($order);
            /*echo $sql->getSqlStringForSqlObject($query);
                       exit;*/
           /*  if($data['limit']!=-1) {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            } */
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $seasonalsList = array();
                 $counter=-1;
            foreach($resultSet as $row)
            {
                $counter++;
                $seasonalsList[$counter]['seasonal_name'] = $row['seasonal_name'];
                $seasonalsList[$counter]['seasonal_type'] = $row['seasonal_type'];
                $seasonalsList[$counter]['seasonal_description'] = $row['seasonal_description'];
                $seasonalsList[$counter]['price'] = floatval($row['price']);
                $seasonalsList[$counter]['discount_price'] = floatval($row['discount_price']);
                $seasonalsList[$counter]['seasonal_special_id'] = $row['seasonal_special_id'];
                $seasonalsList[$counter]['start_date'] = $row['start_date'];
                $seasonalsList[$counter]['end_date'] = $row['end_date'];
                $seasonalsList[$counter]['tourism_place_id'] = $row['tourism_place_id'];
                $seasonalsList[$counter]['file_path'] = $row['file_path'];
            }

            return $seasonalsList;
        }catch(\Exception $e){
            return array();
        }
    }
    public function getFields($data,$columns)
    {
        try
        {
            $sql=$this->getSql();
            $values=array();
            $where=new Where();
            $query = $sql->select()
                ->columns($columns)
                ->from($this->tableName)
                ->where($data);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values=$row;
            }

            return $values;
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return array();
        }
    }
    public function seasonalSpecialsCountryList($data=array())
    {
        try
        {
            $where=new Where();
            $where->and->equalTo('sp.status',1)->greaterThanOrEqualTo('sp.end_date',date("Y-m-d"));


            $sql = $this->getSql();


            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_seasonal_files,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image));
            $countryFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_country,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("seasonal_name","seasonal_type",'seasonal_description','price','discount_price','seasonal_special_id','start_date','end_date'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`sp`.`place_ids`)"),array('place_name'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"),'tourism_place_id'))
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = sp.seasonal_special_id',array('file_path'),Select::JOIN_LEFT)
                ->join(array('c'=>'countries'),'tp.country_id = c.id',array('flag_image','country_name','id'))
                ->join(array('tflc'=>$countryFiles),'tflc.file_data_id = c.id',array('country_image'=>'file_path'),Select::JOIN_LEFT)


                ->where($where)
                ->group('c.id')
                ->order('c.country_name asc');
            /*echo $sql->getSqlStringForSqlObject($query);
                       exit;*/

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $seasonalsList = array();
            $counter=-1;
            foreach($resultSet as $row)
            {
                $counter++;

                $seasonalsList[$counter]['country_name'] = $row['country_name'];
                $seasonalsList[$counter]['flag_image'] = $row['flag_image'];
                $seasonalsList[$counter]['file_path'] = $row['country_image'];
                $seasonalsList[$counter]['country_id'] = $row['id'];
            }

            return $seasonalsList;
        }catch(\Exception $e){
            print_r($e->getMessage());
            exit;
            return array();
        }
    }
    public function seasonalList($data=array())
    {
        try
        {
            $where=new Where();
            $where->and->equalTo('sp.status',1);
            $order=array();

            $sql = $this->getSql();


            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_seasonal_files,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("seasonal_name","seasonal_type",'seasonal_description','price','discount_price','seasonal_special_id','start_date','end_date','place_ids'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`sp`.`place_ids`)"),array('place_name'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"),'tourism_place_id'))
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = sp.seasonal_special_id',array('file_path'),Select::JOIN_LEFT)

                ->where($where)
                ->group('sp.seasonal_special_id')
                ->order($order);
            /*echo $sql->getSqlStringForSqlObject($query);
                       exit;*/

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $seasonalsList = array();
            $counter=-1;
            foreach($resultSet as $row)
            {
                $counter++;
                $seasonalsList[$counter]['seasonal_name'] = $row['seasonal_name'];
                $seasonalsList[$counter]['seasonal_special_id'] = $row['seasonal_special_id'];

                $seasonalsList[$counter]['place_ids'] = $row['place_ids'];
            }

            return $seasonalsList;
        }catch(\Exception $e){
            return array();
        }
    }
    public function getAvailableFT($data=array())
    {
        try
        {
            $where=new Where();
            $where->and->equalTo('sp.status',1); //->equalTo('sp.seasonal_special_id',$seasonalSpecialId);
            $order = array('s.state_name asc', 'ci.city_name asc','tp.place_name asc');

            $sql = $this->getSql();

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("seasonal_name"))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`sp`.`place_ids`)"),array('place_name','tourism_place_id','country_id','city_id'))
                ->join(array('c'=>'countries'),"c.id=tp.country_id",array('country_name'))
                ->join(array('s'=>'states'),"s.id=tp.state_id",array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),"ci.id=tp.city_id",array('city_name'))
                ->join(array('tf'=>'tourism_files'),new \Laminas\Db\Sql\Expression("`tf`.`file_data_id`=`tp`.`tourism_place_id` AND file_extension_type=" . \Admin\Model\TourismFiles::file_extension_type_audio),array('language_name'=>new \Laminas\Db\Sql\Expression('group_concat(`tf`.`file_name`)')))
                ->group('tp.tourism_place_id')
                ->where($where)
                ->order($order);

            if($data['limit']!=-1)
            {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            }
            //echo $sql->getSqlStringForSqlObject($query) . "\n";
            //exit;

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourisms = array();
            foreach($resultSet as $row){
                $tourisms[] = $row;
            }
            return $tourisms;
        }catch(\Exception $e){

            return array();
        }
    }
    public function seasonalSpecialDetailsWithPlaces($seasonalSpecialId)
    {
        try
        {
            $where=new Where();
            $where->and->equalTo('sp.status',1)->equalTo('sp.seasonal_special_id',$seasonalSpecialId);
            $order=array();

            $sql = $this->getSql();

            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_seasonal_files,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("seasonal_name","seasonal_type",'no_of_days','seasonal_description','price','discount_price','seasonal_special_id','start_date','end_date','place_ids'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`sp`.`place_ids`)"),array('place_name','tourism_place_id','country_id','city_id'))
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = sp.seasonal_special_id',array('file_path'),Select::JOIN_LEFT)
                ->join(array('c'=>'countries'),"c.id=tp.country_id",array('country_name'))
                ->join(array('s'=>'states'),"s.id=tp.state_id",array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),"ci.id=tp.city_id",array('city_name'))
                ->where($where)

                ->order($order);
           /* echo $sql->getSqlStringForSqlObject($query);
                       exit;*/

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $seasonalSpecial = array();
            $cityList=array();

            $countryList=array();
            $countryCount = 0;
            $cityCount=0;
            $placesCount=0;
            $counter=-1;
            $cityIdKey=-1;
            $placeCounter=0;
            $seasonalsList=array();
            foreach($resultSet as $row)
            {
                $placesCount++;
                $seasonalsList['seasonal_name'] = $row['seasonal_name'];
                $seasonalsList['seasonal_type'] = $row['seasonal_type'];
                $seasonalsList['no_of_days'] = intval($row['no_of_days']);
                $seasonalsList['seasonal_description'] = $row['seasonal_description'];
                $seasonalsList['price'] = floatval($row['price']);
                $seasonalsList['discount_price'] = floatval($row['discount_price']);
                $seasonalsList['seasonal_special_id'] = $row['seasonal_special_id'];
                $seasonalsList['start_date'] = $row['start_date'];
                $seasonalsList['end_date'] = $row['end_date'];
                $seasonalsList['tourism_place_id'] = $row['tourism_place_id'];
                $seasonalsList['file_path'] = $row['file_path'];

                   $cityIdKey=array_search($row['city_id'],$cityList);

                  /* if($row['tourism_place_id']==7)
                   {
                       print_r($seasonalsList);
                       exit;

                   }*/

                      if(in_array($row['city_id'],$cityList))
                      {
                          array_push($seasonalsList['places_list'][$cityIdKey]['places_list'],$row['place_name']);
                      }else
                      {
                          array_push($cityList,$row['city_id']);
                          $cityCount++;
                          $counter++;
                          $seasonalsList['places_list'][$counter]['city_id']=$row['city_id'];
                          $seasonalsList['places_list'][$counter]['country_name']=$row['country_name'];
                          $seasonalsList['places_list'][$counter]['city_name']=$row['city_name'];
                          $seasonalsList['places_list'][$counter]['state_name']=$row['state_name'];
                          $seasonalsList['places_list'][$counter]['places_list']=array($row['place_name']);
                          $seasonalsList['places_list'][$counter]['place_id']=$row['tourism_place_id'];
                      }

                     if(!in_array($row['country_id'],$countryList))
                     {
                            array_push($countryList,$row['country_id']);
                         $countryCount++;
                     }

                $seasonalsList['places_count'] = $placesCount;
                $seasonalsList['city_count'] = $cityCount;
                $seasonalsList['country_count'] = $countryCount;
            }
            return $seasonalsList;
        }catch(\Exception $e){

            return array();
        }
    }
    public function seasonalDetails($seasonalSpecialId)
    {
        try
        {
            $where=new Where();
            $where->and->equalTo('sp.status',1)->equalTo('sp.seasonal_special_id',$seasonalSpecialId);
            $order=array();

            $sql = $this->getSql();
            $fileLanguageWhere=new Where();

            $placeLanguages= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array('file_data_id'))
                ->join(array('l'=>'languages'),'tf.file_language_type=l.id',array('language_name'=>new \Laminas\Db\Sql\Expression('group_concat(`l`.`name`)')))
                ->where($fileLanguageWhere->equalTo('tf.status',1)->and->equalTo('tf.file_data_type',\Admin\Model\TourismFiles::file_data_type_places))
                ->group('tf.file_data_id');


            $predicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting
            $placeLanguages->where($predicateSet);


            $predicateSet->orPredicate(new Predicate\Expression('tf.file_extension_type = ? ', \Admin\Model\TourismFiles::file_extension_type_audio ));
            $predicateSet->orPredicate(new Predicate\Expression('tf.file_extension_type = ? ', \Admin\Model\TourismFiles::file_extension_type_video ));


            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_seasonal_files,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image));
            $placeImage= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image))
            ->group('tf.file_data_id');

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("seasonal_name","seasonal_type",'seasonal_description','price','discount_price','seasonal_special_id','start_date','end_date','place_ids'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`sp`.`place_ids`)"),array('place_name','tourism_place_id','country_id','city_id'))
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = sp.seasonal_special_id',array('file_path'),Select::JOIN_LEFT)
                ->join(array('tfi'=>$placeImage),'tfi.file_data_id = tp.tourism_place_id',array('place_image'=>'file_path'),Select::JOIN_LEFT)
                ->join(array('tl'=>$placeLanguages),'tl.file_data_id = tp.tourism_place_id',array('language_name'))

                ->join(array('c'=>'countries'),"c.id=tp.country_id",array('country_name'))
                ->join(array('s'=>'states'),"s.id=tp.state_id",array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),"ci.id=tp.city_id",array('city_name'))

                ->where($where)
                ->order($order);
            /*echo $sql->getSqlStringForSqlObject($query);
                       exit;*/

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $seasonalsList = array();
            $counter=-1;
            foreach($resultSet as $row)
            {
                $counter++;
                $seasonalsList['seasonal_name'] = $row['seasonal_name'];
                $seasonalsList['seasonal_special_id'] = $row['seasonal_special_id'];
                $seasonalsList['place_ids'] = $row['place_ids'];
                $seasonalsList['places_list'][$counter]['city_id']=$row['city_id'];
                $seasonalsList['places_list'][$counter]['country_name']=$row['country_name'];
                $seasonalsList['places_list'][$counter]['city_name']=$row['city_name'];
                $seasonalsList['places_list'][$counter]['state_name']=$row['state_name'];
                $seasonalsList['places_list'][$counter]['place_name']=$row['place_name'];
                $seasonalsList['places_list'][$counter]['place_id']=$row['tourism_place_id'];
                $seasonalsList['places_list'][$counter]['place_image']=$row['place_image'];
                $seasonalsList['places_list'][$counter]['languages'] = array_values(array_unique(array_filter(explode(",",$row['language_name']))));
            }

            return $seasonalsList;
        }catch(\Exception $e){
            return array();
        }
    }
}