<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Having;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Predicate;

class TourismPlacesTable extends  BaseTable
{  protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("tp" => "tourism_places");
    }

    public function addTourism(Array $data)
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

    public function updateTourism($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e)
        {

            return false;
        }
    }
    public function getTourismPlaces($where)
    {
        try{
            $where['status']=1;
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_place_id",'place_name','place_description','country_id','state_id','city_id'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourisms = array();
            foreach($resultSet as $row){
                $tourisms[] = $row;
            }
            return $tourisms;
        }catch(\Exception $e)
        {
            return array();
        }
    }
    public function getTourismPlacesAdmin($data)
    {
        try
        {
            $sql = $this->getSql();
            $where=new Where();
            $prices = $sql->select()
                ->from(array('p'=>'place_prices'))
                ->columns(array('place_id'))
                ->where(array('p.tour_type'=>$data['tour_type'],'p.status'=>1));
            $where->equalTo('tp.status',1)->and->notIn('tp.tourism_place_id',$prices)->and->equalTo('tp.city_id',$data['city_id']);

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_place_id",'place_name','place_description','country_id','state_id','city_id'))
                ->where($where);
            /*echo $sql->getSqlStringForSqlObject($query);
         exit;*/

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourisms = array();
            foreach($resultSet as $row)
            {
                $tourisms[] = $row;
            }
            return $tourisms;
        }catch(\Exception $e)
        {
            return array();
        }
    }
    public function getTourismPlacesList($date)
    {
        try{
            $where=new Where();
            $sql = $this->getSql();
            if($date!='')
            {
                $where->greaterThan('updated_at',$date);
            }else
            {
                $where->equalTo('status',1);
            }
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_place_id",'place_name','place_description','country_id','state_id','city_id','status','updated_at'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourisms = array();
            foreach($resultSet as $row)
            {
                $tourisms[] = $row;
            }
            return $tourisms;
        }catch(\Exception $e){
            return array();
        }
    }
    public function getAvailableList($data=array())
    {
        try
        {
            $sql = $this->getSql();

            $fileLanguageWhere=new Where();
            $tourismFiles=$sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("files_count"=>new \Laminas\Db\Sql\Expression('COUNT(tourism_file_id)'),'file_data_id'))
                ->join(array('l'=>'languages'),'tf.file_language_type=l.id',array('language_name'=>new \Laminas\Db\Sql\Expression('group_concat(`l`.`name`)')))
                ->where($fileLanguageWhere->equalTo('tf.status',1)->and->equalTo('tf.file_data_type',\Admin\Model\TourismFiles::file_data_type_places))
                ->group(array('tf.file_data_id'));

            $where=new Where();
            $where->equalTo('tp.status',1);
            $order=array();

            if(array_key_exists('country',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"),'%'.$data['country']."%");
            }

            if(array_key_exists('state',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"),'%'.$data['state']."%");
            }

            if(array_key_exists('city',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ci.city_name)"),'%'.$data['city']."%");
            }

            if(array_key_exists('place_name',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"),'%'.$data['place_name']."%");
            }

            if(array_key_exists('country_name',$data) && $data['country_name']=='India')
            {
                $where->and->like("c.country_name",'India');
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
                if($data['city_order'] == 1 )
                {
                    $order[]='ci.city_name asc';
                }else if($data['city_order'] == -1 )
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
                $order=array('updated_at desc');
            }
            if($data['tour_type']==\Admin\Model\PlacePrices::tour_type_India_tour || $data['tour_type'] == \Admin\Model\PlacePrices::tour_type_Spiritual_tour){
                $order = array('s.state_name asc', 'ci.city_name asc','tp.place_name asc');
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("tourism_place_id",'place_name','country_id','state_id','city_id','created_at','updated_at'))
                    /* ->join(array('c'=>'countries'),new \Laminas\Db\Sql\Expression('tp.country_id=c.id and c.status=1'),array('country_name')) */
                    ->join(array('s'=>'states'),new \Laminas\Db\Sql\Expression('tp.state_id=s.id and s.status=1'),array('state_name'))//,Select::JOIN_LEFT)
                    ->join(array('ci'=>'cities'),new \Laminas\Db\Sql\Expression('tp.city_id=ci.id and ci.status=1'),array('city_name'))
                    ->join(array('p'=>'place_prices'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`) and p.status=1 and p.tour_type=".$data['tour_type']),array("tour_type"))
                    ->join(array('tpf'=>$tourismFiles),'tp.tourism_place_id=tpf.file_data_id',array('language_name','files_count'), Select::JOIN_LEFT)
                    ->group('tp.tourism_place_id')
                    ->where($where)
                    ->order($order);
            }
            elseif($data['tour_type']==\Admin\Model\PlacePrices::tour_type_World_tour){
                $order = array('c.country_name asc', 'ci.city_name asc','tp.place_name asc');
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("tourism_place_id",'place_name','country_id','state_id','city_id','created_at','updated_at'))
                    ->join(array('c'=>'countries'),new \Laminas\Db\Sql\Expression('tp.country_id=c.id and tp.country_id<>101 and c.status=1'),array('country_name'))
                    ->join(array('ci'=>'cities'),new \Laminas\Db\Sql\Expression('tp.city_id=ci.id and ci.status=1'),array('city_name'))
                    ->join(array('p'=>'place_prices'),new \Laminas\Db\Sql\Expression("p.status=1 and p.tour_type=".$data['tour_type']),array("tour_type"))
                    ->join(array('tpf'=>$tourismFiles),'tp.tourism_place_id=tpf.file_data_id',array('language_name','files_count'), Select::JOIN_LEFT)
                    ->group('tp.tourism_place_id')
                    ->where($where)
                    ->order($order);
            }
            elseif($data['tour_type']==\Admin\Model\PlacePrices::tour_type_City_tour){
                $order = array('c.country_name asc', 'ci.city_name asc','tp.place_name asc');
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("tourism_place_id",'place_name','country_id','state_id','city_id','created_at','updated_at'))
                    ->join(array('c'=>'countries'),new \Laminas\Db\Sql\Expression('tp.country_id=c.id and c.status=1'),array('country_name'))
                    ->join(array('ci'=>'cities'),new \Laminas\Db\Sql\Expression('tp.city_id=ci.id and ci.status=1'),array('city_name'))
                    ->join(array('p'=>'place_prices'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`) and p.status=1 and p.tour_type=".$data['tour_type']),array("tour_type"))
                    ->join(array('tpf'=>$tourismFiles),'tp.tourism_place_id=tpf.file_data_id',array('language_name','files_count'), Select::JOIN_LEFT)
                    ->group('tp.tourism_place_id')
                    ->where($where)
                    ->order($order);
            }
            
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
                //$tourisms['languages'] = explode(",",$row['language_name']);
            }

            return $tourisms;
        }catch (\Exception $e){
            print_r($e->getMessage());exit;
            return array();
        }
    }
    public function getTourismList($data=array())
    {
        try
        {
            $sql = $this->getSql();
            $likesCountHaving=new Having();

            if(array_key_exists('likes',$data))
            {
                $likesCountHaving->like('likes_count',"%".$data['likes'].'%');
            }

            $fileLanguageWhere=new Where();
            $tourismFiles=$sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("files_count"=>new \Laminas\Db\Sql\Expression('COUNT(tourism_file_id)'),'file_data_id'))
                ->join(array('l'=>'languages'),'tf.file_language_type=l.id',array('language_name'=>new \Laminas\Db\Sql\Expression('group_concat(`l`.`name`)')))
                ->where($fileLanguageWhere->equalTo('tf.status',1)->and->equalTo('tf.file_data_type',\Admin\Model\TourismFiles::file_data_type_places))
                ->group(array('tf.file_data_id'));//->group('tf.file_data_id');
                /* ->where(array('status'=>1))
                ->group(array('tf.file_data_id')); */
            $likesCount=$sql->select()
                ->from(array('fl'=>'likes'))
                ->columns(array("likes_count"=>new \Laminas\Db\Sql\Expression('COUNT(like_id)'),'file_data_id'))
                ->where(array('fl.status'=>1,'fl.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places))
                ->group(array('fl.file_data_id'));
            
            /* $placeLanguages= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array('file_data_id'))
                ->join(array('l'=>'languages'),'tf.file_language_type=l.id',array('language_name'=>new \Laminas\Db\Sql\Expression('group_concat(`l`.`name`)')))
                ->where($fileLanguageWhere->equalTo('tf.status',1)->and->equalTo('tf.file_data_type',\Admin\Model\TourismFiles::file_data_type_places))
                ->group('tf.file_data_id'); */
            if(array_key_exists('likes',$data))
            {
                $likesCount->having($likesCountHaving);
            }
            $where=new Where();
            $where->equalTo('tp.status',1);
            $order=array();

            if(array_key_exists('country',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"),'%'.$data['country']."%");
            }

            if(array_key_exists('state',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"),'%'.$data['state']."%");
            }

            if(array_key_exists('city',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ci.city_name)"),'%'.$data['city']."%");
            }

            if(array_key_exists('place_name',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"),'%'.$data['place_name']."%");
            }

            if(array_key_exists('tour_type',$data) && $data['tour_type']==\Admin\Model\SeasonalSpecials::seasonal_type_India_tour)
            {
                $where->and->like("c.country_name",'India');
            }

            if(array_key_exists('country_name',$data) && $data['country_name']=='India')
            {
                $where->and->like("c.country_name",'India');
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
                if($data['city_order'] == 1 )
                {
                    $order[]='ci.city_name asc';
                }else if($data['city_order'] == -1 )
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
            if(array_key_exists('download_order',$data))
            {
                if($data['download_order']==1)
                {
                    $order[]='download_count asc';
                }else if($data['place_name_order']==-1)
                {
                    $order[]='download_count desc';
                }
            }
            if(array_key_exists('likes_order',$data))
            {
                if($data['likes_order']==1)
                {
                    $order[]='likes_count asc';
                }else if($data['likes_order']==-1)
                {
                    $order[]='likes_count desc';
                }
            }

            if(!count($order))
            {
                $order=array('updated_at desc');
            }

            /* $predicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); 
            $tourismFiles->where($predicateSet); */

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_place_id",'place_name','country_id','state_id','city_id','created_at','updated_at'))
                ->join(array('c'=>'countries'),'tp.country_id=c.id',array('country_name'))
                ->join(array('s'=>'states'),'tp.state_id=s.id',array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),'tp.city_id=ci.id',array('city_name'))
                /* ->join(array('tl'=>$placeLanguages),'tl.file_data_id = tp.tourism_place_id',array('language_name')) 
                ->join(array('tpf'=>$tourismFiles),'tp.tourism_place_id=tpf.file_data_id',array('files_count'),Select::JOIN_LEFT)*/
                ->join(array('tpf'=>$tourismFiles),'tp.tourism_place_id=tpf.file_data_id',array('language_name','files_count'), Select::JOIN_LEFT)
                ->join(array('dfi'=>'downloaded_file_info'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`dfi`.`place_ids`)"),array('download_count'=>new \Laminas\Db\Sql\Expression('count(`dfi`.`download_id`)')),Select::JOIN_LEFT)
                ->join(array('l'=>$likesCount),'tp.tourism_place_id=l.file_data_id',array('likes_count'),Select::JOIN_LEFT)
                ->group('tp.tourism_place_id')
                ->where($where)
                ->order($order);


            $having=new Having();

            if(array_key_exists('likes',$data))
            {
                $having->like('likes_count',"%".$data['likes'].'%');
            }

            if(array_key_exists('download',$data))
            {

                $having->like('download_count',"%".$data['download'].'%');
            }
            if(array_key_exists('likes',$data) || array_key_exists('download',$data))
            {
                $query->having($having);
            }


            if($data['limit']!=-1)
            {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            }

            //echo $sql->getSqlStringForSqlObject($query);
            //exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourisms = array();
            foreach($resultSet as $row){
                $tourisms[] = $row;
                //$tourisms['languages'] = explode(",",$row['language_name']);
            }

            return $tourisms;
        }catch (\Exception $e){
            print_r($e->getMessage());exit;
            return array();
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function getActiveTourismPlacesList($data=array())
    {
        try
        {
            $sql = $this->getSql();

            $where=new Where();
            $where->equalTo('tp.status',1);
            if(array_key_exists('search',$data)&& $data['search']!='')
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"),new \Laminas\Db\Sql\Expression('LOWER("%'.$data['search'].'%")'));
                //    $where->and->like('tp.place_name',"%".$data['search']."%");
            }

            if(array_key_exists('city_id',$data)&& $data['city_id']!='')
            {
                $where->and->equalTo('tp.city_id',$data['city_id']);
            }

            $fileLanguageWhere=new Where();

            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'file_data_id'))

                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));

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


            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('place_id'=>"tourism_place_id","place_name"))
                ->join(array('p'=>'place_prices'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`) and p.status=1 and p.tour_type=".$data['tour_type']),array("price"))
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = tp.tourism_place_id',array('file_path'))
                ->join(array('tl'=>$placeLanguages),'tl.file_data_id = tp.tourism_place_id',array('language_name'))
                ->where($where)
                ->group(array('tp.tourism_place_id'))
                ->order('tp.place_name asc');
            if($data['limit']!=-1) {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            }
            /* echo $sql->getSqlStringForSqlObject($query);
            exit; */

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            $counter=0;
            foreach($resultSet as $row)
            {
                $countries[$counter]['place_id'] = $row['place_id'];
                $countries[$counter]['place_name'] = $row['place_name'];
                $countries[$counter]['file_path'] = $row['file_path'];
                $countries[$counter]['price'] = floatval($row['price']);
                $countries[$counter]['days'] = 0;
                /* if(array_key_exists('days',$data)) {
                    $countries[$counter]['days'] = $data['days'];
                } */
                $countries[$counter]['languages'] = array_values(array_unique(array_filter(explode(",",$row['language_name']))));
                $counter++;
            }
            return $countries;
        }catch (\Exception $e)
        {
            // print_r($e->getMessage());exit;
            return array();
        }
    }
    public function getActiveTourismPlacesUser($data=array())
    {
        try
        {
            $sql = $this->getSql();

            $where=new Where();
            $where->equalTo('tp.status',1);
            if(array_key_exists('search',$data)&& $data['search']!='')
            {
                $where->and->like('tp.place_name',"%".$data['search']."%");
            }


            $fileLanguageWhere=new Where();
            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'file_data_id'))

                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places,'tf.file_extension_type'=>\Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));
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


            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('place_id'=>"tourism_place_id","place_name"))
                ->join(array('c'=>'countries'),'tp.country_id=c.id',array('country_name'))
                ->join(array('s'=>'states'),'tp.state_id=s.id',array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),'tp.city_id=ci.id',array('city_name'),Select::JOIN_LEFT)
                ->join(array('tf'=>'tourism_files'),new \Laminas\Db\Sql\Expression('c.id=tf.file_data_id and tf.status=1 and tf.file_data_type='.\Admin\Model\TourismFiles::file_data_type_country.' and tf.file_extension_type='.\Admin\Model\TourismFiles::file_extension_type_image),array('country_image_path'=>'file_path'))
                ->join(array('p'=>'place_prices'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`) and p.status=1 and p.tour_type =".$data['tour_type']),array("price"))
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = tp.tourism_place_id',array('file_path'))
                ->join(array('tl'=>$placeLanguages),'tl.file_data_id = tp.tourism_place_id',array('language_name'))
                ->where($where)
                ->group(array('tp.tourism_place_id'))
                ->order('tp.updated_at desc');
                if($data['limit']!=-1){
                $query->offset($data['offset'])
                ->limit($data['limit']);
                }


            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            $counter=0;
            foreach($resultSet as $row){
                $countries[$counter]['place_id'] = $row['place_id'];
                $countries[$counter]['place_name'] = $row['place_name'];
                $countries[$counter]['country_name'] = $row['country_name'];
                $countries[$counter]['state_name'] = $row['state_name'];
                $countries[$counter]['city_name'] = $row['city_name'];
                $countries[$counter]['file_path'] = $row['file_path'];
                $countries[$counter]['country_image_path'] = $row['country_image_path'];
                $countries[$counter]['price'] = floatval($row['price']);
                $countries[$counter]['languages'] = array_values(array_unique(array_filter(explode(",",$row['language_name']))));
                $counter++;
            }
            return $countries;
        }catch (\Exception $e)
        {
          //  print_r($e->getMessage());exit;
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
    public function getFields($where,$columns)
    {
        try
        {
            $sql=$this->getSql();
            $values=array();

            $query = $sql->select()
                ->columns($columns)
                ->from($this->tableName)
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values=$row;
            }


            return $values;
        }catch (\Exception $e)
        {
            return array();
        }
    }
    public function getTourismListCount($data=array())
    {
        try
        {

            $sql = $this->getSql();
            $likesCountHaving=new Having();

            if(array_key_exists('likes',$data))
            {
                $likesCountHaving->like('likes_count',"%".$data['likes'].'%');
            }



            $tourismFiles=$sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("files_count"=>new \Laminas\Db\Sql\Expression('COUNT(tourism_file_id)'),'file_data_id'))
                ->where(array('status'=>1))
                ->group(array('tf.file_data_id'));
            $likesCount=$sql->select()
                ->from(array('fl'=>'likes'))
                ->columns(array("likes_count"=>new \Laminas\Db\Sql\Expression('COUNT(like_id)'),'file_data_id'))
                ->where(array('fl.status'=>1,'fl.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places))
                ->group(array('fl.file_data_id'));
            if(array_key_exists('likes',$data) || array_key_exists('download',$data))
            {
                $likesCount->having($likesCountHaving);
            }

            $where=new Where();
            $where->equalTo('tp.status',1);
            if(array_key_exists('country',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"),'%'.$data['country']."%");
            }

            if(array_key_exists('state',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"),'%'.$data['state']."%");
            }

            if(array_key_exists('city',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ci.city_name)"),'%'.$data['city']."%");
            }

            if(array_key_exists('place_name',$data))
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"),'%'.$data['place_name']."%");
            }


            $query = $sql->select()
                ->from($this->tableName)
                ->join(array('c'=>'countries'),'tp.country_id=c.id',array('country_name'))
                ->join(array('s'=>'states'),'tp.state_id=s.id',array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),'tp.city_id=ci.id',array('city_name'))
                ->join(array('l'=>$likesCount),'tp.tourism_place_id=l.file_data_id',array('likes_count'),Select::JOIN_LEFT)
                ->join(array('dfi'=>'downloaded_file_info'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`dfi`.`place_ids`)"),array('download_count'=>new \Laminas\Db\Sql\Expression('count(`dfi`.`download_id`)')),Select::JOIN_LEFT)
                ->join(array('tpf'=>$tourismFiles),'tp.tourism_place_id=tpf.file_data_id',array('files_count'),"left")
                ->group('tp.tourism_place_id')
                ->where($where);
            $having=new Having();

            if(array_key_exists('likes',$data))
            {
                $having->like('likes_count',"%".$data['likes'].'%');
            }

            if(array_key_exists('download',$data))
            {

                $having->like('download_count',"%".$data['download'].'%');
            }
            if(array_key_exists('likes',$data) || array_key_exists('download',$data))
            {
                $query->having($having);
            }

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourisms = array();
            foreach($resultSet as $row){
                $tourisms[] = $row;
            }
            return $tourisms;
        }catch (\Exception $e)
        {
                   print_r($e->getMessage());exit;
            return array();
        }
    }
    public function getTourismPlaceDetailsAction($placeId)
    {
        try{
            $sql = $this->getSql();

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_place_id",'place_name','place_description','country_id','state_id','city_id','created_at'))
                ->join(array('tpf'=>'tourism_place_files'),'tp.tourism_place_id=tpf.tourism_place_id',array('tourism_file_id','file_path','file_name','status','file_type','file_language_type','file_upload_type'),"left")
                ->where(array('tp.tourism_place_id'=>$placeId,'tpf.status'=>1));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();

            $tourisms = array();

            foreach($resultSet as $row){
                $tourisms[] = $row;
            }
            return $tourisms;
        }catch (\Exception $e)
        {
            /* print_r($e->getMessage());
             exit;*/
            return array();
        }
    }
    public function tourismPlacesList($placeId)
    {
        try
        {
            $where=new Where();
            $where->in('tourism_place_id',$placeId);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_place_id",'place_name','place_description','country_id','state_id','city_id'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourisms = array();
            $counter=-1;
            foreach($resultSet as $row){
                $counter++;
                $tourisms[$counter]['tourism_place_id'] = $row['tourism_place_id'];
                $tourisms[$counter]['place_name'] = $row['place_name'];
                $tourisms[$counter]['place_description'] = $row['place_description'];
                $tourisms[$counter]['country_id'] = $row['country_id'];
                $tourisms[$counter]['state_id'] = $row['state_id'];
                $tourisms[$counter]['city_id'] = $row['city_id'];
            }

            return $tourisms;
        }catch(\Exception $e)
        {
            return array();
        }
    }
    public function getPlaceDetails($placeId)
    {
        try
        {
            $sql = $this->getSql();

            $where=new Where();
            $where->equalTo('tp.status',1)->equalTo('tp.tourism_place_id',$placeId);

            $placeFiles= $sql->select()
                ->from(array('tf'=>'tourism_files'))
                ->columns(array("file_path",'tourism_file_id','file_data_id','file_extension_type','file_language_type','file_name'))
                ->where(array('tf.status'=>1,'tf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('tourism_place_id',"place_name","place_description","country_id",'state_id','city_id'))
                ->join(array('co'=>'countries'),'tp.country_id = co.id',array('country_name'))
                ->join(array('tfl'=>$placeFiles),'tfl.file_data_id = tp.tourism_place_id',array('file_path','tourism_file_id','file_extension_type','file_language_type','file_name'),Select::JOIN_LEFT)
                ->where($where);


            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();

            foreach($resultSet as $row){
                $countries[] = $row;
            }
            return $countries;
        }catch(\Exception $e)
        {

            return array();
        }
    }

}