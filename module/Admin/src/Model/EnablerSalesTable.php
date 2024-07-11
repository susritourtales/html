<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;

class EnablerSalesTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "enabler_sales");
  }

  public function addEnablerSale(array $data)
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

  public function getEnablerSalesList($data = array('limit' => 10, 'offset' => 0), $gc = 0)
  {
    try {
      $where = new Where();
      $where->equalTo('p.enabler_id', $data['enabler_id']);
      $order = ['p.created_at desc'];

      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where)
        ->join(array('e' => 'enabler_plans'), 'e.id=p.plan_id', array('plan_name'), Select::JOIN_LEFT)
        ->order($order);
        if ($gc == 0) {
          $query->offset($data['offset'])
              ->limit($data['limit']);
        }
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      if ($gc == 1)
        return count($resultSet);
      $sales = array();
      foreach ($resultSet as $row) {
        $sales[] = $row;
      }
      return $sales;
    } catch (\Exception $e) {
      return array();
    }
  }
  public function getAdminEnablerSalesList($data = array('limit' => 10, 'offset' => 0), $gc = 0)
  {
    try {
      $where = new Where();
      $where->equalTo('p.purchase_id', $data['purchase_id']);
      //$order = ['p.created_at desc'];
      if (array_key_exists('sale_date', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.sale_date)"), '%' . $data['sale_date'] . "%");
      }
      if (array_key_exists('plan_name', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.plan_name)"), '%' . $data['plan_name'] . "%");
      }
      if (array_key_exists('tourist_name', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.tourist_name)"), '%' . $data['tourist_name'] . "%");
      }
      if (array_key_exists('tourist_mobile', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.tourist_mobile)"), '%' . $data['tourist_mobile'] . "%");
      }
      if (array_key_exists('tourist_email', $data)) {
        $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.tourist_email)"), '%' . $data['tourist_email'] . "%");
      }
      if (array_key_exists('twistt_start_date', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.twistt_start_date)"), '%' . $data['twistt_start_date'] . "%");
      }
      if (array_key_exists('lic_bal', $data)) {
          $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.lic_bal)"), '%' . $data['lic_bal'] . "%");
      }
      
      $order = array();
      if (array_key_exists('sale_date_order', $data)) {
          if ($data['sale_date_order'] == 1) {
              $order[] = 'p.sale_date asc';
          } else if ($data['sale_date_order'] == -1) {
              $order[] = 'p.sale_date desc';
          }
      }
      if (array_key_exists('plan_name_order', $data)) {
          if ($data['plan_name_order'] == 1) {
              $order[] = 'p.plan_name asc';
          } else if ($data['plan_name_order'] == -1) {
              $order[] = 'p.plan_name desc';
          }
      }
      if (array_key_exists('tourist_name_order', $data)) {
          if ($data['tourist_name_order'] == 1) {
              $order[] = 'p.tourist_name asc';
          } else if ($data['tourist_name_order'] == -1) {
              $order[] = 'p.tourist_name desc';
          }
      }
      if (array_key_exists('tourist_mobile_order', $data)) {
        if ($data['tourist_mobile_order'] == 1) {
            $order[] = 'p.tourist_mobile asc';
        } else if ($data['tourist_mobile_order'] == -1) {
            $order[] = 'p.tourist_mobile desc';
        }
      }if (array_key_exists('tourist_email_order', $data)) {
        if ($data['tourist_email_order'] == 1) {
            $order[] = 'p.tourist_email asc';
        } else if ($data['tourist_email_order'] == -1) {
            $order[] = 'p.tourist_email desc';
        }
    }
    if (array_key_exists('twistt_start_date_order', $data)) {
        if ($data['twistt_start_date_order'] == 1) {
            $order[] = 'p.twistt_start_date asc';
        } else if ($data['twistt_start_date_order'] == -1) {
            $order[] = 'p.twistt_start_date desc';
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
        ->order($order);
        if ($gc == 0) {
          $query->offset($data['offset'])
              ->limit($data['limit']);
        }
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      if ($gc == 1)
        return count($resultSet);
      $sales = array();
      foreach ($resultSet as $row) {
        $sales[] = $row;
      }
      return $sales;
    } catch (\Exception $e) {
      return array();
    }
  }

  public function setEnablerSales($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return false;
    }
  }

  /* public function getTransactionsList($data = array('limit' => 10, 'offset' => 0), $gc = 0)
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
  } */
}
