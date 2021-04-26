<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Predicate;

use Laminas\Db\TableGateway\TableGateway;

class TourismFilesTable extends  BaseTable
{  protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("tf" => "tourism_files");
    }

    public function addTourismFiles(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {

            return false;
        }
    }
    public function addMutipleTourismFiles(Array $data)
    {
        try {
            return $this->multiInsert($data);
        } catch (\Exception $e) {
             print_r($e->getTraceAsString());
            exit;
            return false;
        }
    }
    public function getTourismAudioFilesList()
    {
        try
        {

            $where=new Where();
            $where->equalTo('tf.status',1)->equalTo('tf.file_data_type',\Admin\Model\TourismFiles::file_data_type_sample_files);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_file_id",'file_path',"duration",'file_language_type','file_name'))
                ->join(array('l'=>'languages'),'l.id=tf.file_language_type',array('language_name'=>'name'))
                ->where($where)
                ->order('l.name asc');

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourismFiles = array();
                  $languageID=array();
            $counter=-1;
            foreach($resultSet as $row){
                   if(in_array($row['file_language_type'],$languageID))
                   {
                       $languageCounter=array_search($row['file_language_type'],$languageID);

                       $tourismFiles[$languageCounter]['files'][]=array('file_path'=>$row['file_path'],'file_name'=>$row['file_name'],'duration'=>$row['duration']);

                   }else{
                       $counter++;
                       $tourismFiles[$counter]['language_name'] = $row['language_name'];
                       $tourismFiles[$counter]['files'][]=array('file_path'=>$row['file_path'],'file_name'=>$row['file_name'],'duration'=>$row['duration']);
                       array_push($languageID,$row['file_language_type']);
                   }

            }
            return $tourismFiles;
        }catch(\Exception $e){

            return array();
        }
    }
    public function getTourismAudioFiles($data=array('limit'=>10,'offset'=>0))
    {
        try
        {

            $where=new Where();
            $where->equalTo('tf.status',1)->equalTo('tf.file_data_type',\Admin\Model\TourismFiles::file_data_type_sample_files);
            if(array_key_exists('language',$data))
            {
                $where->and->like("l.name",'%'.$data['language']."%");
            }
            $order=array();

            if(array_key_exists('language_order',$data))
            {
                if($data['language_order']==1)
                {
                    $order[]='l.name asc';
                }else if($data['language_order']==-1)
                {
                    $order[]='l.name desc';
                }
            }
              if(!count($order))
              {
                  $order=array('tf.updated_at desc');
              }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_file_id",'file_path',"duration",'file_language_type','file_name'))
                ->join(array('l'=>'languages'),'l.id=tf.file_language_type',array('language_name'=>'name'))
                ->where($where)
            ->limit($data['limit'])
            ->offset($data['offset'])
            ->order($order);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourismFiles = array();

            foreach($resultSet as $row){
                $tourismFiles[] = $row;
            }
            return $tourismFiles;
        }catch(\Exception $e){
                   print_r($e->getMessage());
                   exit;
            return array();
        }
    }
    public function getTourismAudioFilesCount($data=array())
    {
        try
        {
            $where=new Where();
            $where->equalTo('tf.status',1)->equalTo('tf.file_data_type',\Admin\Model\TourismFiles::file_data_type_sample_files);
            if(array_key_exists('language',$data))
            {
                $where->and->like("l.name",'%'.$data['language']."%");
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_file_id",'file_path',"duration",'file_language_type','file_name'))
                ->join(array('l'=>'languages'),'l.id=tf.file_language_type',array('language_name'=>'name'))
                ->where($where)

            ->order('tf.updated_at desc');

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourismFiles = array();

            foreach($resultSet as $row){
                $tourismFiles[] = $row;
            }
                return $tourismFiles;
        }catch(\Exception $e){
                    print_r($e->getMessage());exit;
            return array();
        }
    }
    public function getTourismFiles($where)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_file_id",'file_upload_type','file_path',"duration",'file_type','file_language_type','file_name'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourismFiles = array();

            foreach($resultSet as $row){
                $tourismFiles[] = $row;
            }

        }catch(\Exception $e){
          
            return array();
        }
    }   public function getTourismFilesList($date)
    {
        try{
            $sql = $this->getSql();
            $where=new Where();
            if($date!='')
            {
                $where->greaterThan('updated_at',$date);
            }else{
                $where->equalTo('status',1);
            }
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_file_id","tourism_place_id",'file_upload_type','file_path',"duration",'file_type','file_language_type','file_name','status','updated_at'))
                ->where($where);


            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourismFiles = array();

            foreach($resultSet as $row){
                $tourismFiles[] = $row;
            }

            return $tourismFiles;
        }catch(\Exception $e)
        {
          
            return array();
        }
    }
      public function updatePlaceFiles($data,$where)
      {
           try{
                  return $this->update($data,$where);
           }catch (\Exception $e)
           {
               return false;
           }
      }
      public function deletePlaceFiles($deleteIds)
      {
          try
          {
              $sql=$this->getSql();
              $where=new Where();
              $query=$sql->update('tourism_files')->set(array('status'=>0,'updated_at'=>date("Y-m-d H:i:s")))
                  ->where($where->in('tourism_file_id',$deleteIds));
                /* echo $sql->getSqlStringForSqlObject($query);
                 exit;*/
              $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
                     return true;
          }catch (\Exception $e)
          {
              print_r($e->getMessage());
              exit;
              return false;
          }
      }
    public function tourisFilesList($countryId,$stateId,$cityId,$placeId,$packageId,$languages,$fileType)
    {
        try
        {

            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tourism_file_id",'file_data_id','file_data_type','file_path',"file_extension","file_extension_type",'file_language_type','file_name','hash','duration'))
                ->join(array('l'=>'languages'),'tf.file_language_type=l.id',array('language_name'=>new \Laminas\Db\Sql\Expression('IFNULL(`name`,"")')),Select::JOIN_LEFT);

            $countryPredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_AND); // for future nesting

            $countryPredicateSet->andPredicate(new Predicate\Expression('FIND_IN_SET (tf.file_data_id, ? )', implode(",",$countryId)));
            $countryPredicateSet->andPredicate(new Predicate\Expression('tf.file_data_type = ? ', \Admin\Model\TourismFiles::file_data_type_country ));

                      $countryLanguagePredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting
            if(in_array(\Admin\Model\TourismFiles::file_extension_type_image,$fileType)){
                $countryLanguagePredicateSet->orPredicate(new Predicate\Expression('tf.file_extension_type = ?', \Admin\Model\TourismFiles::file_extension_type_image));
            }
            $countryLanguageAudioPredict=new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_AND);
            $countryLanguageAudioPredict->andPredicate(new Predicate\Expression('tf.file_extension_type = ?', 3));

            $countryLanguageAudioPredict->andPredicate(new Predicate\Expression('FIND_IN_SET (tf.file_language_type, ? )', implode(",",$languages)));
            $countryLanguagePredicateSet->orPredicate($countryLanguageAudioPredict);


                $countryPredicateSet->andPredicate($countryLanguagePredicateSet);

            $countryPredicateSet->andPredicate(new Predicate\Expression('tf.status = ? ', 1 ));
         //   $countryPredicateSet->andPredicate(new Predicate\Expression('FIND_IN_SET (tf.file_language_type, ? )', implode(",",$languages) ));


            $statePredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_AND); // for future nesting


            $statePredicateSet->andPredicate(new Predicate\Expression('FIND_IN_SET (tf.file_data_id, ? )', implode(",",$stateId)));
            $statePredicateSet->andPredicate(new Predicate\Expression('tf.file_data_type = ? ', \Admin\Model\TourismFiles::file_data_type_state ));

                $statePredicateSet->andPredicate(new Predicate\Expression('tf.status = ? ', 1));


            $stateLanguagePredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting
            if(in_array(\Admin\Model\TourismFiles::file_extension_type_image,$fileType)) {
                $stateLanguagePredicateSet->orPredicate(new Predicate\Expression('tf.file_extension_type = ?', 1));
            }
            $stateLanguageAudioPredict=new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_AND);
            $stateLanguageAudioPredict->andPredicate(new Predicate\Expression('tf.file_extension_type = ?', 3));

            $stateLanguageAudioPredict->andPredicate(new Predicate\Expression('FIND_IN_SET (tf.file_language_type, ? )', implode(",",$languages)));
            $stateLanguagePredicateSet->orPredicate($stateLanguageAudioPredict);
            $statePredicateSet->andPredicate($stateLanguagePredicateSet);





            $cityPredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_AND); // for future nesting


            $cityPredicateSet->andPredicate(new Predicate\Expression('FIND_IN_SET (tf.file_data_id, ? )', implode(",",$cityId)));
            $cityPredicateSet->andPredicate(new Predicate\Expression('tf.file_data_type = ? ', \Admin\Model\TourismFiles::file_data_type_city ));
            $cityPredicateSet->andPredicate(new Predicate\Expression('tf.status = ? ', 1 ));


            $cityLanguagePredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting
            if(in_array(\Admin\Model\TourismFiles::file_extension_type_image,$fileType)) {
                $cityLanguagePredicateSet->orPredicate(new Predicate\Expression('tf.file_extension_type = ?', 1));
            }
            $cityLanguageAudioPredict=new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_AND);
            $cityLanguageAudioPredict->andPredicate(new Predicate\Expression('tf.file_extension_type = ?', 3));

            $cityLanguageAudioPredict->andPredicate(new Predicate\Expression('FIND_IN_SET (tf.file_language_type, ? )', implode(",",$languages)));
            $cityLanguagePredicateSet->orPredicate($cityLanguageAudioPredict);
            $cityPredicateSet->andPredicate($cityLanguagePredicateSet);



            $placePredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_AND); // for future nesting


            $placePredicateSet->andPredicate(new Predicate\Expression('FIND_IN_SET (tf.file_data_id, ? )', implode(",",$placeId)));
            $placePredicateSet->andPredicate(new Predicate\Expression('tf.file_data_type = ? ', \Admin\Model\TourismFiles::file_data_type_places ));

            $placePredicateSet->andPredicate(new Predicate\Expression('tf.status = ? ', 1 ));


            $placeLanguagePredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting
            if(in_array(\Admin\Model\TourismFiles::file_extension_type_image,$fileType)) {
                $placeLanguagePredicateSet->orPredicate(new Predicate\Expression('tf.file_extension_type = ?', 1));
            }
            $placeLanguageAudioPredict=new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_AND);
            $placeLanguageAudioPredict->andPredicate(new Predicate\Expression('tf.file_extension_type = ?', 3));

            $placeLanguageAudioPredict->andPredicate(new Predicate\Expression('FIND_IN_SET (tf.file_language_type, ? )', implode(",",$languages)));
            $placeLanguagePredicateSet->orPredicate($placeLanguageAudioPredict);
            $placePredicateSet->andPredicate($placeLanguagePredicateSet);




            if($packageId)
              {
                  $seasonalPredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_AND); // for future nesting
                  $seasonalPredicateSet->andPredicate(new Predicate\Expression('tf.file_data_id = ?', $packageId));
                  $seasonalPredicateSet->andPredicate(new Predicate\Expression('tf.file_data_type = ? ', \Admin\Model\TourismFiles::file_data_type_seasonal_files ));
                  $seasonalPredicateSet->andPredicate(new Predicate\Expression('tf.status = ? ', 1 ));
              }

           /* $languagePredicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting*/


           /* $languagePredicateSet->andPredicate(new Predicate\Expression('tf.file_extension_type = ?',\Admin\Model\TourismFiles::file_extension_type_image) );
            $languagePredicateSet->andPredicate(new Predicate\Expression('tf.file_data_type in  '  ));*/

            $whereData=array($countryPredicateSet,$statePredicateSet,$cityPredicateSet,$placePredicateSet);
                 if($packageId)
                 {
                     array_push($whereData,$seasonalPredicateSet);
                 }
            $query->where($whereData,Predicate\PredicateSet::OP_OR);
            // $query->where(array($countryPredicateSet,$statePredicateSet,$cityPredicateSet,$placePredicateSet),Predicate\PredicateSet::OP_AND);
                   /*if(count($fileType)==1) {


                       echo $sql->getSqlStringForSqlObject($query);
                       exit;
                   }*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourismFiles = array();

            foreach($resultSet as $row)
            {
                $tourismFiles[] = $row;
            }
            return $tourismFiles;
        }catch(\Exception $e){
             /*print_r($e->getMessage());
            exit;*/
            return array();
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

}