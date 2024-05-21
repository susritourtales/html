<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class ExecutiveDetailsTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "executive_details");
  }

  public function addExecutive(array $data)
  {
    try {
      return $this->insert($data);
    } catch (\Exception $e) {
      return false;
    }
  }
  public function executiveExists($userId){
    try {
        $sql = $this->getSql();
        $query = $sql->select()
            ->from($this->tableName)
            ->columns(array("id"))
            ->where(['user_id' => $userId]);
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

  public function getExecutiveDetails($where)
  {
    try {
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where);
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      $plans = array();
      foreach ($resultSet as $row) {
        $plans[] = $row;
      }
      return $plans;
    } catch (\Exception $e) {
      return array();
    }
  }

  public function setExecutiveDetails($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return false;
    }
  }
}
