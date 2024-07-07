<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;

class EnablerPurchaseTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "enabler_purchase");
  }

  public function addEnablerPurchase(array $data)
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

  public function getEnablerPurchase($where)
  {
    try {
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where);
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      $enabler = array();
      foreach ($resultSet as $row) {
        $enabler[] = $row;
      }
      return $enabler[0];
    } catch (\Exception $e) {
      return array();
    }
  }

  public function getEnablerPurchasesList($data = array('limit' => 10, 'offset' => 0), $gc = 0)
  {
    try {
      $where = new Where();
      $where->equalTo('p.enabler_id', $data['enabler_id']);
      $where->and->equalTo('payment_status', \Admin\Model\EnablerPurchase::payment_success);
      $order = ['p.created_at desc'];
      
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where)
        ->join(array('e' => 'enabler_plans'), 'e.id=p.plan_id', array('plan_name'), Select::JOIN_LEFT)
        ->join(array('r' => 'enabler_purchase_request'), 'r.invoice=p.invoice', array('prid' => 'id'), Select::JOIN_LEFT)
        ->order($order);
      if ($gc == 0) {
        $query->offset($data['offset'])
            ->limit($data['limit']);
      }
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      if ($gc == 1)
        return count($resultSet);
      $purchases = array();
      foreach ($resultSet as $row) {
        $purchases[] = $row;
      }
      return $purchases;
    } catch (\Exception $e) {
      return array();
    }
  }

  public function getEnablerLastPurchaseDate($enablerId){
    try {
      $where = new Where();
      $order = ['p.created_at desc'];
      $where->equalTo('p.enabler_id', $enablerId);
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->columns(array("purchase_date"))
        ->where($where)
        ->order($order)
        ->limit(1);
      $field = array();
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      foreach ($resultSet as $row) {
        $field = $row['purchase_date'];
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

  public function enablerCPAvailed($enablerId){
    try {
      $where = new Where();
      $order = ['p.created_at desc'];
      $where->equalTo('p.enabler_id', $enablerId);
      $where->and->equalTo('c.coupon_type', 'C');
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where)
        ->join(['c' => 'coupons'], 'c.coupon_code = p.coupon_code', ['coupon_type'], Select::JOIN_LEFT)
        ->order($order);
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      $enabler = array();
      foreach ($resultSet as $row) {
        $enabler[] = $row;
      }
      return count($enabler);
    } catch (\Exception $e) {
      return array();
    }
  }

  public function setEnablerPurchase($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return false;
    }
  }
}
