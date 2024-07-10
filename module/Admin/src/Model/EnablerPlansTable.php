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
      
      if (array_key_exists('plan_name', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.plan_name)"), '%' . $data['plan_name'] . "%");
      }
      if (array_key_exists('plan_type', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.plan_type)"), '%' . $data['plan_type'] . "%");
      }
      if (array_key_exists('price_inr', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.price_inr)"), '%' . $data['price_inr'] . "%");
      }
      if (array_key_exists('price_usd', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.price_usd)"), '%' . $data['price_usd'] . "%");
      }
      $order = array();
      if (array_key_exists('plan_name_order', $data)) {
          if ($data['plan_name_order'] == 1) {
              $order[] = 'p.plan_name asc';
          } else if ($data['plan_name_order'] == -1) {
              $order[] = 'p.plan_name desc';
          }
      }
      if (array_key_exists('plan_type_order', $data)) {
          if ($data['plan_type_order'] == 1) {
              $order[] = 'p.plan_type asc';
          } else if ($data['plan_type_order'] == -1) {
              $order[] = 'p.plan_type desc';
          }
      }
      if (array_key_exists('price_inr_order', $data)) {
          if ($data['price_inr_order'] == 1) {
              $order[] = 'p.price_inr asc';
          } else if ($data['price_inr_order'] == -1) {
              $order[] = 'p.price_inr desc';
          }
      }
      if (array_key_exists('price_usd_order', $data)) {
        if ($data['price_usd_order'] == 1) {
            $order[] = 'p.price_usd asc';
        } else if ($data['price_usd_order'] == -1) {
            $order[] = 'p.price_usd desc';
        }
      }
      if (!count($order)) {
        $order = ['p.created_at desc'];
      }

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
