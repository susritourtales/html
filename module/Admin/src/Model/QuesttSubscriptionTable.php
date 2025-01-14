<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class QuesttSubscriptionTable extends BaseTable
{
  protected $tableGateway;
  protected $tableName;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
    $this->tableName = array("p" => "questt_subscription");
  }

  public function addQuesttSubscriptionDetails(array $data)
  {
    try {
      return $this->insert($data);
    } catch (\Exception $e) {
      return $e;
    }
  }

  public function getField($where, $column)
  {
    try {
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->columns(array("" . $column . ""))
        ->where($where)
        ->order('created_at desc')
        ->limit(1);
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
      return $e;
    }
  }

  public function isValidQuesttUser($id){
    try {
        $sql = $this->getSql();
        $query = $sql->select()
          ->from($this->tableName)
          ->where(['user_id' => $id])
          ->order('end_date desc')
          ->limit(1);
        $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
        $validity = false;
        $subscription = [];
        foreach ($resultSet as $row) {
          $subscription[] = $row;
        }   
        if($subscription){
            $endDate = date('Y-m-d',strtotime($subscription[0]['end_date']));
            $today = date('Y-m-d');
            if($endDate >= $today){
                $validity = true;
            }
        }
        return $validity;
      } catch (\Exception $e) {
        return $e;
      }
  }

  public function getQuesttSubscriptionDetails($where)
  {
    try {
      $sql = $this->getSql();
      $query = $sql->select()
        ->from($this->tableName)
        ->where($where);
      $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
      $plans = array();
      foreach ($resultSet as $row) {
        $plans[] = $row;
      }
      return $plans;
    } catch (\Exception $e) {
      return $e;
    }
  }

  public function setQuesttSubscriptionDetails($data, $where)
  {
    try {
      return $this->update($data, $where);
    } catch (\Exception $e) {
      return $e;
    }
  }
}
