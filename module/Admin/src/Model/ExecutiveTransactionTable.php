<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;

class ExecutiveTransactionTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "executive_transaction");
  }

  public function addExecutivePurchase(array $data)
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

  public function getExecutiveTransaction($where)
  {
    try {
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where);
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      $transaction = array();
      foreach ($resultSet as $row) {
        $transaction[] = $row;
      }
      return $transaction[0];
    } catch (\Exception $e) {
      return array();
    }
  }

  public function setExecutiveTransaction($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return false;
    }
  }

  public function getTransactionsList($data = array('limit' => 10, 'offset' => 0), $gc = 0)
  {
      try {
          $where = new Where();
          $where->equalTo('p.executive_id', $data['executive_id']);
          $order = ['p.created_at desc'];
          
          $sql = $this->getSql();
          if ($gc == 1) {
            $query = $sql->select()
                  ->from($this->tableName)
                  ->where($where)
                  ->join(array('ep' => 'executive_purchase'), 'ep.executive_id=p.executive_id', array('id'))
                  ->join(array('c' => 'coupons'), new \Laminas\Db\Sql\Expression("`c`.`purchase_id`=`p`.`id` AND `c`.`coupon_status`=1"), array('coupon_code', 'coupon_type', 'coupon_status'))
                  ->join(array('u' => 'user'), 'u.id=p.user_id', array('username','mobile_number', 'country_phone_code'), Select::JOIN_LEFT)
                  ->order($order);
          } else {
            $query = $sql->select()
                  ->from($this->tableName)
                  ->where($where)
                  ->join(array('ep' => 'executive_purchase'), 'ep.executive_id=p.executive_id', array('id'))
                  ->join(array('c' => 'coupons'), new \Laminas\Db\Sql\Expression("`c`.`purchase_id`=`ep`.`id` AND `c`.`coupon_status`=1"), array('coupon_code', 'coupon_type', 'coupon_status'))
                  ->join(array('u' => 'user'), 'u.id=p.user_id', array('username','mobile_number', 'country_phone_code'), Select::JOIN_LEFT)
                  ->order($order)
                  ->limit($data['limit'])
                  ->offset($data['offset']);
          }
          $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
          if ($gc == 1)
              return count($resultSet);
          $coupons = array();
          foreach ($resultSet as $row) {
              $coupons[] = $row;
          }
          return $coupons;
      } catch (\Exception $e) {
          return array();
      }
  }
}
