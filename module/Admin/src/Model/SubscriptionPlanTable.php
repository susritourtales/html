<?php

namespace Admin\Model;

use Admin\Controller\AdminController;
use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class SubscriptionPlanTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "subscription_plan");
  }

  public function addPlan(array $data)
  {
    try {
      return $this->insert($data);
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

  public function getPlans($where, $data)
  {
    try {
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where);
      if ($data['limit'] != -1) {
        $query->limit($data['limit'])
          ->offset($data['offset']);
      }
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      $plans = array();
      foreach ($resultSet as $row) {
        $plans = $row;
      }
      return $plans;
    } catch (\Exception $e) {
      return array();
    }
  }

  public function getPlansCount($active)
  {
    try {
      $sql = $this->getSql();
      $where = new Where();
      if ($active != \Admin\Model\SubscriptionPlan::AllPlans) {
        $where->equalTo('active', $active);
        $query = $sql->select()
          ->columns(array('count' => new \Laminas\Db\Sql\Expression('COUNT(`p`.`id`)')))
          ->from($this->tableName)
          ->where($where)
          ->order('created_at desc');
      } else {
        $query = $sql->select()
          ->columns(array('count' => new \Laminas\Db\Sql\Expression('COUNT(`p`.`id`)')))
          ->from($this->tableName)
          ->order('created_at desc');
      }
      //echo $sql->getSqlStringForSqlObject($query);exit;

      $result = $sql->prepareStatementForSqlObject($query)->execute();
      $count = 0;
      foreach ($result as $row) {
        $count = $row['count'];
      }
      return $count;
    } catch (\Exception $e) {
      return array();
    }
  }

  public function setPlan($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return false;
    }
  }
}
