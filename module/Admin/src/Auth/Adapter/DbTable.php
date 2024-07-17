<?php

namespace Admin\Auth\Adapter;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Application\Handler\Aes;
use Laminas\Crypt\Password\Bcrypt;

class DbTable implements AdapterInterface
{
    protected $dbAdapter;
    protected $username;
    protected $password;

    public function __construct(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function setIdentity($username)
    {
        $this->username = $username;
        return $this;
    }

    public function setCredential($password)
    {
        $this->password = $password;
        return $this;
    }

    public function authenticate()
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select('user')
                      ->columns(['id', 'user_login_id', 'password', 'hash'])
                      ->where(['user_login_id' => $this->username]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $row = $result->current();

        if (!$row) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, ['Invalid credentials.']);
        }

        $aes = new Aes();
        $decryptedPassword = $aes->decrypt($row['password'], $row['hash']);
        if (!$decryptedPassword == $this->password) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['Invalid credentials.']);
        }

        /* $bcrypt = new Bcrypt();
        if (!$bcrypt->verify($this->password, $row['password'])) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['Invalid credentials.']);
        } */
        unset($row['password'], $row['hash']);
        return new Result(Result::SUCCESS, $row, ['Authentication successful.']);
    }
    public function authenticateEnabler()
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select('enabler')
                      ->columns(['id', 'user_login_id', 'password', 'hash'])
                      ->where(['user_login_id' => $this->username]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $row = $result->current();

        if (!$row) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, ['Invalid credentials.']);
        }

        $aes = new Aes();
        $decryptedPassword = $aes->decrypt($row['password'], $row['hash']);
        if (!$decryptedPassword == $this->password) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['Invalid credentials.']);
        }

        /* $bcrypt = new Bcrypt();
        if (!$bcrypt->verify($this->password, $row['password'])) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['Invalid credentials.']);
        } */
        unset($row['password'], $row['hash']);
        return new Result(Result::SUCCESS, $row, ['Authentication successful.']);
    }
}

?>