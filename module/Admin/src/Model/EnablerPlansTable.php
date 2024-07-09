<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;

class EnablerPlansTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "enabler_plans");
  }

  public function addEnablerPlan(array $data)
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

  public function getEnablerPlans($where)
  {
    try {
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where);
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      $plan = array();
      foreach ($resultSet as $row) {
        $plan[] = $row;
      }
      return $plan;
    } catch (\Exception $e) {
      return array();
    }
  }

  public function getAdminEnablerPlans($data = array('limit' => 10, 'offset' => 0), $gc = 0){
    try {
      $where = new Where();
      $where->equalTo('p.status', \Admin\Model\EnablerPlans::status_active);
      $order = ['p.created_at desc'];

      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where)
        ->order($order);
      if ($gc == 0) {
        $query->offset($data['offset'])
            ->limit($data['limit']);
      }
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      if ($gc == 1)
        return count($resultSet);
      $plan = array();
      foreach ($resultSet as $row) {
        $plan[] = $row;
      }
      return $plan;
    } catch (\Exception $e) {
      return array();
    }
  }

  public function setEnablerPlans($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return false;
    }
  }
}
