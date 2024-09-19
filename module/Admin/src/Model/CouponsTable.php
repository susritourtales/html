<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Select;
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

  public function getCouponsList($data = array('limit' => 10, 'offset' => 0), $gc = 0, $cs=2)
  {
      try {
          /* $where = new Where();
          $where->equalTo('p.executive_id', $data['executive_id']);
          if($cs != 2)
            $where->equalTo('p.coupon_status', $cs);
          $order = ['p.created_at desc'];
          $where = $where->expression(
            'ext.id = (
                SELECT MAX(id)
                FROM executive_transaction
                WHERE coupon_id = p.id
            ) OR ext.id IS NULL', []
          );
          
          $sql = $this->getSql();
          $query = $sql->select()
                  ->from($this->tableName)
                  ->where($where)
                  ->join(array('b' => 'executive_details'), 'b.id=p.executive_id', array('commission_percentage'))
                  ->join(array('enp' => 'enabler_purchase'), 'enp.coupon_code=p.coupon_code', array('purid' => 'id', 'enabler_id'), Select::JOIN_LEFT)
                  ->join(array('en' => 'enabler'), 'en.id=enp.enabler_id', array('username','company_name'), Select::JOIN_LEFT)
                  ->join(array('ext' => 'executive_transaction'), 'ext.coupon_id=p.id', array('total_earnings','transaction_date', 'txid' => 'id'), Select::JOIN_LEFT)
                  ->order($order); */
          
          $where = new Where();
          $where->equalTo('p.executive_id', $data['executive_id']);
          if ($cs != 2) {
              $where->equalTo('p.coupon_status', $cs);
          }
          
          // Use `nest()` to group the `ext.id` condition and apply `OR` correctly
          $where->nest()
              ->expression(
                  'ext.id = (SELECT MAX(id) FROM executive_transaction WHERE coupon_id = p.id)', []
              )
              ->or
              ->isNull('ext.id')
          ->unnest(); // Unnest after the group to close the condition
          
          // Define the order clause
          $order = ['p.redeemed_on desc'];
          
          // Build the query using the SQL object
          $sql = $this->getSql();
          $query = $sql->select()
              ->from($this->tableName)
              ->where($where) // Apply the where conditions
              ->join(
                  array('b' => 'executive_details'),
                  'b.id = p.executive_id',
                  array('commission_percentage')
              )
              ->join(
                  array('enp' => 'enabler_purchase'),
                  'enp.coupon_code = p.coupon_code',
                  array('purid' => 'id', 'enabler_id'),
                  Select::JOIN_LEFT
              )
              ->join(
                  array('en' => 'enabler'),
                  'en.id = enp.enabler_id',
                  array('username', 'company_name'),
                  Select::JOIN_LEFT
              )
              ->join(
                  array('ext' => 'executive_transaction'),
                  'ext.coupon_id = p.id',
                  array('total_earnings', 'transaction_date', 'txid' => 'id'),
                  Select::JOIN_LEFT // Ensure it's a LEFT JOIN
              )
              ->order($order); // Apply the order
                  
          if ($gc == 0) {
            $query->limit($data['limit'])
                  ->offset($data['offset']);
          }
          /* if ($gc == 1) {
            
          } else {
            $query = $sql->select()
                  ->from($this->tableName)
                  ->where($where)
                  ->join(array('b' => 'executive_details'), 'b.id=p.executive_id', array('commission_percentage'))
                  ->join(array('enp' => 'enabler_purchase'), 'enp.coupon_code=p.coupon_code', array('purid' => 'id', 'enabler_id'), Select::JOIN_LEFT)
                  ->join(array('en' => 'enabler'), 'en.id=enp.enabler_id', array('username','company_name'))
                  ->order($order)
                  ->limit($data['limit'])
                  ->offset($data['offset']);
          } */
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

  public function getCoupon($where){
    try {
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where)
        ->join(array('b' => 'executive_details'), 'b.id=p.executive_id', array('banned'))
        ->limit(1);
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
