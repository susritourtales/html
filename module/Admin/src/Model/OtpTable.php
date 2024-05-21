<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class OtpTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "otp");
  }

  public function addOtpDetails(array $data)
  {
    try {
      return $this->insert($data);
    } catch (\Exception $e) {
      return false;
    }
  }
  public function verifyOtp($data){
    try {
        $sql = $this->getSql();
        $query = $sql->select()
            ->from($this->tableName)
            ->columns(array("id"))
            ->where($data);
        $result = array();
        $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
        foreach ($resultSet as $row) {
            $result = $row;
        }
        if ($result) {
            return $result;
        } else {
            return false;
        }
    } catch (\Exception $e) {
        return [];
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

  public function getOtpDetails($where)
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

  public function setOtpDetails($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return false;
    }
  }
}
