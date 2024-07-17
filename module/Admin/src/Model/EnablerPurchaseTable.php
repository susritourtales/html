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

      if (array_key_exists('purchase_date', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.purchase_date)"), '%' . $data['purchase_date'] . "%");
      }
      if (array_key_exists('plan_name', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.plan_name)"), '%' . $data['plan_name'] . "%");
      }
      if (array_key_exists('actual_price', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.actual_price)"), '%' . $data['actual_price'] . "%");
      }
      if (array_key_exists('price_after_disc', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.price_after_disc)"), '%' . $data['price_after_disc'] . "%");
      }
      if (array_key_exists('coupon_code', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.coupon_code)"), '%' . $data['coupon_code'] . "%");
      }
      if (array_key_exists('executive_name', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.executive_name)"), '%' . $data['executive_name'] . "%");
      }
      if (array_key_exists('executive_mobile', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.executive_mobile)"), '%' . $data['executive_mobile'] . "%");
      }
      if (array_key_exists('invoice', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.invoice)"), '%' . $data['invoice'] . "%");
      }
      if (array_key_exists('receipt', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.receipt)"), '%' . $data['receipt'] . "%");
      }
      if (array_key_exists('lic_bal', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.lic_bal)"), '%' . $data['lic_bal'] . "%");
      }
      
      $order = array();
      if (array_key_exists('purchase_date_order', $data)) {
          if ($data['purchase_date_order'] == 1) {
              $order[] = 'p.purchase_date asc';
          } else if ($data['purchase_date_order'] == -1) {
              $order[] = 'p.purchase_date desc';
          }
      }
      if (array_key_exists('plan_name_order', $data)) {
          if ($data['plan_name_order'] == 1) {
              $order[] = 'p.plan_name asc';
          } else if ($data['plan_name_order'] == -1) {
              $order[] = 'p.plan_name desc';
          }
      }
      if (array_key_exists('actual_price_order', $data)) {
          if ($data['actual_price_order'] == 1) {
              $order[] = 'p.actual_price asc';
          } else if ($data['actual_price_order'] == -1) {
              $order[] = 'p.actual_price desc';
          }
      }
      if (array_key_exists('price_after_disc_order', $data)) {
        if ($data['price_after_disc_order'] == 1) {
            $order[] = 'p.price_after_disc asc';
        } else if ($data['price_after_disc_order'] == -1) {
            $order[] = 'p.price_after_disc desc';
        }
      }if (array_key_exists('coupon_code_order', $data)) {
        if ($data['coupon_code_order'] == 1) {
            $order[] = 'p.coupon_code asc';
        } else if ($data['coupon_code_order'] == -1) {
            $order[] = 'p.coupon_code desc';
        }
    }
    if (array_key_exists('executive_name_order', $data)) {
        if ($data['executive_name_order'] == 1) {
            $order[] = 'p.executive_name asc';
        } else if ($data['executive_name_order'] == -1) {
            $order[] = 'p.executive_name desc';
        }
    }
    if (array_key_exists('executive_mobile_order', $data)) {
        if ($data['executive_mobile_order'] == 1) {
            $order[] = 'p.executive_mobile asc';
        } else if ($data['executive_mobile_order'] == -1) {
            $order[] = 'p.executive_mobile desc';
        }
    }
    if (array_key_exists('invoice_order', $data)) {
      if ($data['invoice_order'] == 1) {
          $order[] = 'p.invoice asc';
      } else if ($data['invoice_order'] == -1) {
          $order[] = 'p.invoice desc';
      }
    }
    if (array_key_exists('receipt_order', $data)) {
        if ($data['receipt_order'] == 1) {
            $order[] = 'p.receipt asc';
        } else if ($data['receipt_order'] == -1) {
            $order[] = 'p.receipt desc';
        }
    }
    if (array_key_exists('lic_bal_order', $data)) {
        if ($data['lic_bal_order'] == 1) {
            $order[] = 'p.lic_bal asc';
        } else if ($data['lic_bal_order'] == -1) {
            $order[] = 'p.lic_bal desc';
        }
    }
      if (!count($order)) {
        $order = ['p.created_at desc'];
      }
      
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

  public function getEnablerPurchasedPlansList($enabler_id)
  {
    try {
      $where = new Where();
      $where->equalTo('p.enabler_id', $enabler_id);
      $where->and->equalTo('payment_status', \Admin\Model\EnablerPurchase::payment_success);
      $order = ['p.created_at desc'];
      
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where)
        ->join(array('e' => 'enabler_plans'), 'e.id=p.plan_id', array('plan_name'), Select::JOIN_LEFT)
        ->join(array('r' => 'enabler_purchase_request'), 'r.invoice=p.invoice', array('prid' => 'id'), Select::JOIN_LEFT)
        ->order($order);
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
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
      $where->and->equalTo('payment_status', \Admin\Model\EnablerPurchase::payment_success);
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
