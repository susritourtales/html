<?php


namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class LanguageTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("l" => "language");
    }

    public function addLanguages(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getActiveLanguagesCount(){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                        ->from($this->tableName)
                        ->columns(['row_count' => new \Laminas\Db\Sql\Expression('COUNT(*)')])
                        ->where(['display' => '1']);
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $rowCount = $result->current()['row_count'];
            return $rowCount;

        }catch(\Exception $e){
            return array();
        }
    }

    public function getActiveLanguagesList($data=['limit'=>10,'offset'=>0]){
        try{
            $sql = $this->getSql();
            if($data['limit'] != 0){
                $query = $sql->select()
                            ->from($this->tableName)
                            ->where(['display' => '1'])
                            ->limit($data['limit'])
                            ->offset($data['offset']);
            }else{
                $query = $sql->select()
                            ->from($this->tableName)
                            ->where(['display' => '1']);
            }
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $languages = array();
            foreach($resultSet as $row){
                $languages[] = $row;
            }
            return $languages;
        }catch(\Exception $e){
            return array();
        }
    }
    public function getInactiveLanguagesList(){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(["id","language_name"])
                ->where(['display'=>0]);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $languages = array();
            foreach($resultSet as $row){
                $languages[] = $row;
            }
            return $languages;
        }catch(\Exception $e){
            return array();
        }
    }
    public function updateLanguage($data, $where)
   {
        try{
            return $this->update($data, $where);
        }catch (\Exception $e)
        {
            return false;
        }
   }
    public function getDefaultLanguages(){
        try{
            $where=new Where();
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","name","language_type"))
                ->where(['language_type'=>1]);
                //->where($where->like("name","%hindi%")->or->like("name","%english%"));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $languages = array();

            foreach($resultSet as $row){
                $languages[] = $row['id'];
            }

            return $languages;
        }catch(\Exception $e){
            return array();
        }
    }
    public function getLanguagesByMaxId($maxId){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","name","language_type"))
            ->where($where->greaterThan('id',$maxId));


            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $languages = array();

            foreach($resultSet as $row){
                $languages[] = $row;
            }

            return $languages;
        }catch(\Exception $e){
            return array();
        }
    }
}