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

  public function addExecutiveTransaction(array $data)
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
        ->where($where)
        ->order('p.created_at desc');
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
          if(array_key_exists('executive_id', $data))
            $where->equalTo('p.executive_id', $data['executive_id']);
          
          $where->equalTo('ed.banned', '0');
          $where->and->equalTo('ed.verified', '1');
          //$order = ['p.created_at desc'];
          if (array_key_exists('transaction_date', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.transaction_date)"), '%' . $data['transaction_date'] . "%");
          }
          if (array_key_exists('total_earnings', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.total_earnings)"), '%' . $data['total_earnings'] . "%");
          }
          if (array_key_exists('stt_paid_amount', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.stt_paid_amount)"), '%' . $data['stt_paid_amount'] . "%");
          }
          if (array_key_exists('balance_outstanding', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.balance_outstanding)"), '%' . $data['balance_outstanding'] . "%");
          }
          if (array_key_exists('username', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(u.username)"), '%' . $data['username'] . "%");
          }
          if (array_key_exists('bank_name', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ed.bank_name)"), '%' . $data['bank_name'] . "%");
          }
          if (array_key_exists('bank_ac_no', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ed.bank_ac_no)"), '%' . $data['bank_ac_no'] . "%");
          }
          if (array_key_exists('ifsc_code', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ed.ifsc_code)"), '%' . $data['ifsc_code'] . "%");
          }
          if (array_key_exists('transaction_ref', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.transaction_ref)"), '%' . $data['transaction_ref'] . "%");
          }
          
          $order = array();
          if (array_key_exists('transaction_date_order', $data)) {
              if ($data['transaction_date_order'] == 1) {
                  $order[] = 'p.transaction_date asc';
              } else if ($data['transaction_date_order'] == -1) {
                  $order[] = 'p.transaction_date desc';
              }
          }
          if (array_key_exists('total_earnings_order', $data)) {
              if ($data['total_earnings_order'] == 1) {
                  $order[] = 'p.total_earnings asc';
              } else if ($data['total_earnings_order'] == -1) {
                  $order[] = 'p.total_earnings desc';
              }
          }
          if (array_key_exists('stt_paid_amount_order', $data)) {
              if ($data['stt_paid_amount_order'] == 1) {
                  $order[] = 'p.stt_paid_amount asc';
              } else if ($data['stt_paid_amount_order'] == -1) {
                  $order[] = 'p.stt_paid_amount desc';
              }
          }
          if (array_key_exists('balance_outstanding_order', $data)) {
            if ($data['balance_outstanding_order'] == 1) {
                $order[] = 'p.balance_outstanding asc';
            } else if ($data['balance_outstanding_order'] == -1) {
                $order[] = 'p.balance_outstanding desc';
            }
          }
          if (array_key_exists('username_order', $data)) {
            if ($data['username_order'] == 1) {
                $order[] = 'u.username asc';
            } else if ($data['username_order'] == -1) {
                $order[] = 'u.username desc';
            }
          }
          if (array_key_exists('bank_name_order', $data)) {
            if ($data['bank_name_order'] == 1) {
                $order[] = 'ed.bank_name asc';
            } else if ($data['bank_name_order'] == -1) {
                $order[] = 'ed.bank_name desc';
            }
          }
          if (array_key_exists('bank_ac_no_order', $data)) {
            if ($data['bank_ac_no_order'] == 1) {
                $order[] = 'ed.bank_ac_no asc';
            } else if ($data['bank_ac_no_order'] == -1) {
                $order[] = 'ed.bank_ac_no desc';
            }
          }
          if (array_key_exists('ifsc_code_order', $data)) {
              if ($data['ifsc_code_order'] == 1) {
                  $order[] = 'ed.ifsc_code asc';
              } else if ($data['ifsc_code_order'] == -1) {
                  $order[] = 'ed.ifsc_code desc';
              }
          }
          if (array_key_exists('transaction_ref_order', $data)) {
              if ($data['transaction_ref_order'] == 1) {
                  $order[] = 'p.transaction_ref asc';
              } else if ($data['transaction_ref_order'] == -1) {
                  $order[] = 'p.transaction_ref desc';
              }
          }
          if (!count($order)) {
            $order = ['p.created_at desc'];
          }
          
          $sql = $this->getSql();
          $query = $sql->select()
                  ->from($this->tableName)
                  ->where($where)
                  ->join(array('c' => 'coupons'), new \Laminas\Db\Sql\Expression("`c`.`id`=`p`.`coupon_id` AND `c`.`coupon_status`=1"), array('coupon_code', 'coupon_type', 'coupon_status','redeemer_name', 'redeemer_mobile'))
                  ->join(array('ep' => 'executive_purchase'), 'ep.id=c.purchase_id', array('pid' => 'id'))
                  ->join(array('u' => 'user'), 'u.id=p.user_id', array('username','mobile_number', 'country_phone_code'), Select::JOIN_LEFT)
                  ->join(array('ed' => 'executive_details'), 'ed.id=p.executive_id', array('bank_account_no','ifsc_code', 'bank_name'), Select::JOIN_LEFT)
                  ->order($order);
          if ($gc == 0) {
            $query->limit($data['limit'])
                  ->offset($data['offset']);
          }
          $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
          if ($gc == 1)
              return count($resultSet);
          $txns = array();
          foreach ($resultSet as $row) {
              $txns[] = $row;
          }
          return $txns;
      } catch (\Exception $e) {
          return array();
      }
  }

  public function getPendingPaymentsList($data = array('limit' => 10, 'offset' => 0), $gc = 0)
  {
      try {
          $where = new Where();
          if(array_key_exists('executive_id', $data))
            $where->equalTo('p.executive_id', $data['executive_id']);
          
          $where->equalTo('ed.banned', '0');
          $where->and->equalTo('ed.verified', '1');
          //$order = ['p.created_at desc'];
          if (array_key_exists('transaction_date', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.transaction_date)"), '%' . $data['transaction_date'] . "%");
          }
          if (array_key_exists('total_earnings', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.total_earnings)"), '%' . $data['total_earnings'] . "%");
          }
          if (array_key_exists('stt_paid_amount', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.stt_paid_amount)"), '%' . $data['stt_paid_amount'] . "%");
          }
          if (array_key_exists('balance_outstanding', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.balance_outstanding)"), '%' . $data['balance_outstanding'] . "%");
          }
          if (array_key_exists('username', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(u.username)"), '%' . $data['username'] . "%");
          }
          if (array_key_exists('bank_name', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ed.bank_name)"), '%' . $data['bank_name'] . "%");
          }
          if (array_key_exists('bank_ac_no', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ed.bank_ac_no)"), '%' . $data['bank_ac_no'] . "%");
          }
          if (array_key_exists('ifsc_code', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ed.ifsc_code)"), '%' . $data['ifsc_code'] . "%");
          }
          if (array_key_exists('transaction_ref', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.transaction_ref)"), '%' . $data['transaction_ref'] . "%");
          }
          
          $order = array();
          if (array_key_exists('transaction_date_order', $data)) {
              if ($data['transaction_date_order'] == 1) {
                  $order[] = 'p.transaction_date asc';
              } else if ($data['transaction_date_order'] == -1) {
                  $order[] = 'p.transaction_date desc';
              }
          }
          if (array_key_exists('total_earnings_order', $data)) {
              if ($data['total_earnings_order'] == 1) {
                  $order[] = 'p.total_earnings asc';
              } else if ($data['total_earnings_order'] == -1) {
                  $order[] = 'p.total_earnings desc';
              }
          }
          if (array_key_exists('stt_paid_amount_order', $data)) {
              if ($data['stt_paid_amount_order'] == 1) {
                  $order[] = 'p.stt_paid_amount asc';
              } else if ($data['stt_paid_amount_order'] == -1) {
                  $order[] = 'p.stt_paid_amount desc';
              }
          }
          if (array_key_exists('balance_outstanding_order', $data)) {
            if ($data['balance_outstanding_order'] == 1) {
                $order[] = 'p.balance_outstanding asc';
            } else if ($data['balance_outstanding_order'] == -1) {
                $order[] = 'p.balance_outstanding desc';
            }
          }
          if (array_key_exists('username_order', $data)) {
            if ($data['username_order'] == 1) {
                $order[] = 'u.username asc';
            } else if ($data['username_order'] == -1) {
                $order[] = 'u.username desc';
            }
          }
          if (array_key_exists('bank_name_order', $data)) {
            if ($data['bank_name_order'] == 1) {
                $order[] = 'ed.bank_name asc';
            } else if ($data['bank_name_order'] == -1) {
                $order[] = 'ed.bank_name desc';
            }
          }
          if (array_key_exists('bank_ac_no_order', $data)) {
            if ($data['bank_ac_no_order'] == 1) {
                $order[] = 'ed.bank_ac_no asc';
            } else if ($data['bank_ac_no_order'] == -1) {
                $order[] = 'ed.bank_ac_no desc';
            }
          }
          if (array_key_exists('ifsc_code_order', $data)) {
              if ($data['ifsc_code_order'] == 1) {
                  $order[] = 'ed.ifsc_code asc';
              } else if ($data['ifsc_code_order'] == -1) {
                  $order[] = 'ed.ifsc_code desc';
              }
          }
          if (array_key_exists('transaction_ref_order', $data)) {
              if ($data['transaction_ref_order'] == 1) {
                  $order[] = 'p.transaction_ref asc';
              } else if ($data['transaction_ref_order'] == -1) {
                  $order[] = 'p.transaction_ref desc';
              }
          }
          if (!count($order)) {
            $order = ['p.created_at desc'];
          }
          
          $sql = $this->getSql();
          $query = $sql->select()
                  ->from($this->tableName)
                  ->where($where)
                  ->join(array('c' => 'coupons'), new \Laminas\Db\Sql\Expression("`c`.`id`=`p`.`coupon_id` AND `c`.`coupon_status`=1"), array('coupon_code', 'coupon_type', 'coupon_status','redeemer_name', 'redeemer_mobile'))
                  ->join(array('ep' => 'executive_purchase'), 'ep.id=c.purchase_id', array('pid' => 'id'))
                  ->join(array('u' => 'user'), 'u.id=p.user_id', array('username','mobile_number', 'country_phone_code'), Select::JOIN_LEFT)
                  ->join(array('ed' => 'executive_details'), 'ed.last_txn_id=p.id', array('bank_account_no','ifsc_code', 'bank_name'), Select::JOIN_LEFT)
                  ->order($order);
          if ($gc == 0) {
            $query->limit($data['limit'])
                  ->offset($data['offset']);
          }
          $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
          if ($gc == 1)
              return count($resultSet);
          $txns = array();
          foreach ($resultSet as $row) {
              $txns[] = $row;
          }
          return $txns;
      } catch (\Exception $e) {
          return array();
      }
  }
}
