<?php

namespace Admin\Model;

use Application\Handler\Aes;
use RuntimeException;
use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class UserTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("u" => "user");
    }

    public function addUser(array $data)
    {
        try {
            return $this->insert($data);
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

    public function setFields($data, $where)
    {
        try {
            return $this->update($data, $where);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getSubcribersCount()
    {
        try {
            $sql = $this->getSql();
            $inRoles = [\Admin\Model\User::QUESTT_Subscriber, \Admin\Model\User::TWISTT_Subscriber, \Admin\Model\User::TWISTT_Executive];
            $where = new Where();
            $where->in('u.user_type_id', $inRoles);
            $query = $sql->select()
                ->columns(array('count' => new \Laminas\Db\Sql\Expression('COUNT(`u`.`id`)')))
                ->from($this->tableName)
                ->where($where)
                ->order('created_at desc');
            //echo $sql->getSqlStringForSqlObject($query);exit;

            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $count = 0;
            foreach ($result as $row) {
                $count = $row['count'];
            }
            return $count;
        } catch (\Exception $e) {
            return array();
        }
    }

    public function checkPasswordWithUserId($userId, $password)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "password", "hash"))
                ->where(array('display' => 1, 'id' => $userId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $user = array();
            foreach ($resultSet as $row) {
                $user[] = $row;
            }
            if (count($user)) {
                $aes = new Aes();
                if ($user[0]['hash'] == "") {
                    return array();
                }
                $decryptedPassword = $aes->decrypt($user[0]['password'], $user[0]['hash']);
                if ($decryptedPassword == $password) {
                    return $user[0];
                } else {
                    return array();
                }
            } else {
                return array();
            }
        } catch (\Exception $e) {
            return array();
        }
    }

    public function updateUser($data, $where)
    {
        try {
            return $this->update($data, $where);
        } catch (\Exception $e) {
            return false;
        }
    }
}

    /* public function saveUser(User $user)
    {
        $data = [
            'username' => $user->username,
            'user_login_id'  => $user->user_login_id,
            'password'  => $user->password,
            'hash'  => $user->hash,
            'email' => $user->email,
            'mobile_number' => $user->mobile_number,
            'country_phone_code' => $user->country_phone_code,
            'user_location' => $user->user_location,
            'user_type_id' => $user->user_type_id,
            'oauth_provider' => $user->oauth_provider,
            'oauth_provider_user_id' => $user->oauth_provider_user_id,
        ];

        $id = (int) $user->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            return;
        }

        try {
            $this->getUser($id);
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf(
                'Cannot update user with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
    }

} */
