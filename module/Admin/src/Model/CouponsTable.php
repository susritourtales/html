<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class CouponsTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "coupons");
  }

  public function addCoupon(array $data)
  {
    try {
      return $this->insert($data);
    } catch (\Exception $e) {
      return false;
    }
  }
  public function addMutipleCoupons(Array $data)
    {
        try {
            return $this->multiInsert($data);
        } catch (\Exception $e) {
            return false;
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

  public function getCoupons($where)
  {
    try {
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where);
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      $coupons = [];
      foreach ($resultSet as $row) {
        $coupons[] = $row;
      }
      return $coupons;
    } catch (\Exception $e) {
      return array();
    }
  }

  public function setCoupons($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return false;
    }
  }
}
