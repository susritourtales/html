<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\TableGateway;

class ExecutiveDetailsTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "executive_details");
  }

  public function addExecutive(array $data)
  {
    try {
      return $this->insert($data);
    } catch (\Exception $e) {
      return false;
    }
  }
  public function executiveExists($userId){
    try {
        $sql = $this->getSql();
        $query = $sql->select()
            ->from($this->tableName)
            ->columns(array("id"))
            ->where(['user_id' => $userId, 'deleted' => '0']);
        $field = array();
        $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
        foreach ($resultSet as $row) {
            $field = $row['id'];
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

  public function getField($where, $column)
  {
    try {
      $where['deleted'] = '0';
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

  public function getExecutiveDetails($where)
  {
    try {
      $where['deleted'] = '0';
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where);
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      $executive = array();
      foreach ($resultSet as $row) {
        $executive[] = $row;
      }
      return $executive[0];
    } catch (\Exception $e) {
      return array();
    }
  }

  public function getExecutiveDetails4Admin($data = array(), $gc = 0)
  {
    try {
      $where = new Where();
      $where->equalTo('p.deleted', '0');
      $order = ['p.created_at desc'];
      if (array_key_exists('username', $data)) {
        $where->and->like(new Expression("LOWER(u.username)"), '%' . $data['username'] . "%");
      }
      if (array_key_exists('username_order', $data)) {
        if ($data['username_order'] == 1) {
            $order[] = 'u.username asc';
        } else if ($data['username_order'] == -1) {
            $order[] = 'u.username desc';
        }
    }
      $sql = $this->getSql();
      // Create the subquery for the latest `questt_subscription`
      $subQuery = new Select('questt_subscription');
      $subQuery->columns([
          'user_id',
          'end_date' => new Expression('MAX(end_date)')
      ]);
      $subQuery->group('user_id');
      // Main query
      $query = $sql->select()
          ->from($this->tableName)
          ->columns([
              '*',
              'd_coupon_count' => new Expression('(SELECT COUNT(*) FROM `coupons` WHERE `coupons`.`executive_id` = `p`.`id` AND `coupons`.`coupon_type` = "D")'),
              'c_coupon_count' => new Expression('(SELECT COUNT(*) FROM `coupons` WHERE `coupons`.`executive_id` = `p`.`id` AND `coupons`.`coupon_type` = "C")')
            ])
          ->where($where)
          ->join(['u' => 'user'], 'u.id = p.user_id', ['username', 'mobile_number', 'country_phone_code'], Select::JOIN_LEFT)
          ->join(['q' => $subQuery], 'q.user_id = p.user_id', ['end_date'], Select::JOIN_LEFT)
          ->join(['t' => 'executive_transaction'], 't.id = p.last_txn_id', ['total_earnings', 'stt_paid_amount','balance_outstanding'], Select::JOIN_LEFT)
          ->order($order);
      if ($data['limit'] != -1) {
        $query->offset($data['offset'])
            ->limit($data['limit']);
      }
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      if ($gc == 1)
        return count($resultSet);

      $executive = array();
      foreach ($resultSet as $row) {
        $executive[] = $row;
      }
      return $executive;
    } catch (\Exception $e) {
      return array();
    }
  }

  public function setExecutiveDetails($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return false;
    }
  }
}
