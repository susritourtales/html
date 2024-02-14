<?php
namespace Admin\Model;

use Application\Handler\Aes;
use RuntimeException;
use Laminas\Db\TableGateway\TableGatewayInterface;

class UserTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    public function getUserByID($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }

        return $row;
    }

    public function getUserByLoginId($user_login_id)
    {
        $userLoginID = $user_login_id;
        $rowset = $this->tableGateway->select(['user_login_id' => $userLoginID]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $user_login_id
            ));
        }

        return $row;
    }

    public function saveUser(User $user)
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

    /* public function authenticateUser($loginID, $password='')
    {
        try {
            $user = $this->getUserByLoginId($loginID);
            $userArr = json_decode(json_encode($user), true);
            
            if (count($userArr))
            {
                if($userArr['password']!='')
                {
                    if ($userArr['hash'] == "")
                    {
                        return array();
                    }
                    $aes = new Aes();
                    // echo '<pre>';
                    // print_r($userArr);
                    // exit;
                    $decryptedPassword = $aes->decrypt($userArr['password'], $userArr['hash']);
                    //print_r($decryptedPassword);exit;
                    if ($decryptedPassword == $password) {
                        return $userArr;
                    } else {
                        return array();
                    }
                }else{
                    return array();
                }
            }else{
                return array();
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());exit;
            // $this->logger->err($e->getMessage());
            return array();
        }
    } */

    public function deleteUser($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
  }
?>