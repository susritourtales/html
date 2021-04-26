<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class LanguagesTable extends BaseTable
{

    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("l" => "languages");
    }

    public function addLanguages(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getLanguages(){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","name","language_type"))
            ->where(array('language_type'=>2));

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
    public function getInactiveLanguages(){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id","name","language_type"))
                ->where(array('language_type'=>1));

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
    public function activateLanguage($data, $where)
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
                ->where($where->like("name","%hindi%")->or->like("name","%english%"));

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