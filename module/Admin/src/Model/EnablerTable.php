<?php

namespace Admin\Model;

use Application\Handler\Aes;
use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class EnablerTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("u" => "enabler");
    }

    public function addEnabler(array $data)
    {
        try {
            $insert = $this->insert($data);
            if ($insert) {
                return array("success" => true, "id" => $this->tableGateway->lastInsertValue);
            } else {
                return array("success" => false);
            }
          } catch (\Exception $e) {
              return array("success" => false);
          }
    }
    public function enablerExists($loginId){
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id"))
                ->where(['user_login_id' => $loginId]);
            $field = array();
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row['id'];
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
    public function getField($where, $column)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("" . $column . ""))
                ->where($where)
                ->limit(1);
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

    public function getEnablerDetails($where){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                //->columns(array("id","name","email","country_phone_code", "mobile", "country", "city", "photo_url", "oauth_provider"))
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();
            foreach($resultSet as $row){
                $user[] = $row;
            }
            return $user[0];
        }catch(\Exception $e){
            return array();
        }
    }

    public function checkPasswordWithId($userId, $password)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "password", "hash"))
                ->where(array('display' => 1, 'user_login_id' => $userId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();
            foreach ($resultSet as $row) {
                $user[] = $row;
            }
            if (count($user)) {
                $aes = new Aes();
                if ($user[0]['hash'] == "") {
                    return "";
                }
                $decryptedPassword = $aes->decrypt($user[0]['password'], $user[0]['hash']);
                if ($decryptedPassword == $password) {
                    return $user[0];
                } else {
                    return "";
                }
            } else {
                return "";
            }
        } catch (\Exception $e) {
            return "";
        }
    }

    public function updateEnabler($data, $where)
    {
        try {
            return $this->update($data, $where);
        } catch (\Exception $e) {
            return false;
        }
    }  
}
