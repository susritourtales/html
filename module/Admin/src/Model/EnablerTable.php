<?php

namespace Admin\Model;

use Application\Handler\Aes;
use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class EnablerTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("p" => "enabler");
    }

    public function addEnabler(array $data)
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
    public function enablerExists($loginId){
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id"))
                ->where(['user_login_id' => $loginId]);
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
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("" . $column . ""))
                ->where($where)
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
            return "";
        }
    }

    public function getEnablerDetails($where){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                //->columns(array("id","name","email","country_phone_code", "mobile", "country", "city", "photo_url", "oauth_provider"))
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();
            foreach($resultSet as $row){
                $user[] = $row;
            }
            return $user[0];
        }catch(\Exception $e){
            return array();
        }
    }

    public function checkPasswordWithId($userId, $password)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "password", "hash"))
                ->where(array('display' => 1, 'user_login_id' => $userId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();
            foreach ($resultSet as $row) {
                $user[] = $row;
            }
            if (count($user)) {
                $aes = new Aes();
                if ($user[0]['hash'] == "") {
                    return "";
                }
                $decryptedPassword = $aes->decrypt($user[0]['password'], $user[0]['hash']);
                if ($decryptedPassword == $password) {
                    return $user[0];
                } else {
                    return "";
                }
            } else {
                return "";
            }
        } catch (\Exception $e) {
            return "";
        }
    }

    public function checkPasswordWithUserId($userId, $password)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "password", "hash"))
                ->where(array('display' => 1, 'user_login_id' => $userId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();
            foreach ($resultSet as $row) {
                $user[] = $row;
            }
            var_dump($user); exit();
            if (count($user)) {
                $aes = new Aes();
                if ($user[0]['hash'] == "") {
                    return "";
                }
                $decryptedPassword = $aes->decrypt($user[0]['password'], $user[0]['hash']);
                if ($decryptedPassword == $password) {
                    return $user[0];
                } else {
                    return "";
                }
            } else {
                return "";
            }
        } catch (\Exception $e) {
            return "";
        }
    }
    
    public function updateEnabler($data, $where)
    {
        try {
            return $this->update($data, $where);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getAdminEnablersList($data = array('limit' => 10, 'offset' => 0), $gc = 0){
        try {
          $where = new Where();
          $where->equalTo('p.display', \Admin\Model\Enabler::display_yes);
          
          if (array_key_exists('username', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.username)"), '%' . $data['username'] . "%");
          }
          if (array_key_exists('company_name', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.company_name)"), '%' . $data['company_name'] . "%");
          }
          if (array_key_exists('country_phone_code', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.country_phone_code)"), '%' . $data['country_phone_code'] . "%");
          }
          if (array_key_exists('mobile_number', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.mobile_number)"), '%' . $data['mobile_number'] . "%");
          }
          if (array_key_exists('email', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.email)"), '%' . $data['email'] . "%");
          }
          if (array_key_exists('country', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.country)"), '%' . $data['country'] . "%");
          }
          if (array_key_exists('city', $data)) {
              $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.city)"), '%' . $data['city'] . "%");
          }
          if (array_key_exists('stt_disc', $data)) {
            $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(p.stt_disc)"), '%' . $data['stt_disc'] . "%");
          }
          $order = array();
          if (array_key_exists('username_order', $data)) {
              if ($data['username_order'] == 1) {
                  $order[] = 'p.username asc';
              } else if ($data['username_order'] == -1) {
                  $order[] = 'p.username desc';
              }
          }
          if (array_key_exists('company_name_order', $data)) {
              if ($data['company_name_order'] == 1) {
                  $order[] = 'p.company_name asc';
              } else if ($data['company_name_order'] == -1) {
                  $order[] = 'p.company_name desc';
              }
          }
          if (array_key_exists('country_phone_code_order', $data)) {
              if ($data['country_phone_code_order'] == 1) {
                  $order[] = 'p.country_phone_code asc';
              } else if ($data['country_phone_code_order'] == -1) {
                  $order[] = 'p.country_phone_code desc';
              }
          }
          if (array_key_exists('mobile_number_order', $data)) {
            if ($data['mobile_number_order'] == 1) {
                $order[] = 'p.mobile_number asc';
            } else if ($data['mobile_number_order'] == -1) {
                $order[] = 'p.mobile_number desc';
            }
          }
          if (array_key_exists('email_order', $data)) {
            if ($data['email_order'] == 1) {
                $order[] = 'p.email asc';
            } else if ($data['email_order'] == -1) {
                $order[] = 'p.email desc';
            }
        }
        if (array_key_exists('country_order', $data)) {
            if ($data['country_order'] == 1) {
                $order[] = 'p.country asc';
            } else if ($data['country_order'] == -1) {
                $order[] = 'p.country desc';
            }
        }
        if (array_key_exists('city_order', $data)) {
            if ($data['city_order'] == 1) {
                $order[] = 'p.city asc';
            } else if ($data['city_order'] == -1) {
                $order[] = 'p.city desc';
            }
        }
        if (array_key_exists('stt_disc_order', $data)) {
          if ($data['stt_disc_order'] == 1) {
              $order[] = 'p.stt_disc asc';
          } else if ($data['stt_disc_order'] == -1) {
              $order[] = 'p.stt_disc desc';
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
    
}
