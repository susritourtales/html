<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class FcmTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("f" => "fcm");
    }
    /* public function saveFcm($fcmId,$deviceId,$userId){
        try{
            $sql = $this->getSql();
            // $query = $sql->select()
            //     ->from(array("f" => "fcm"))
            //     ->columns(array("count" => new \Laminas\Db\Sql\Expression('COUNT(id)')))
            //     ->where(array("user_id" => $userId))
            //     ->where(array("fcm_id" => $fcmId))
            //     ->where(array("device_id"=>$deviceId));
            $query = $sql->select()
                ->from(array("f" => "fcm"))
                ->columns(array("count" => new \Laminas\Db\Sql\Expression('COUNT(fcm_id)')))
                ->where(array("device_id"=>$deviceId));
            
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $count = 0;
            foreach ($resultSet as $row){
                $count = $row['count'];
            }

            if($count){
                $deviceIds = $this->fcmTable()->getDeviceIds($userId);
                foreach ($deviceIds as $did) {
                    if($did != $deviceId)
                        $this->deleteFcmToken($deviceId);
                }
            }
            $save = $this->checkAndUpdateFcm($fcmId,$deviceId, $userId);
            return $save;
        }catch (\Exception $e){
            //  $this->logger->err($e->getMessage());
            print_r($e->getMessage());
            exit;
            return false;
        }
    } */
    public function saveFcm($fcmToken,$deviceId,$userId){
        try{
            $fcmTks = $this->getDeviceIds($userId);
            if(count($fcmTks)>1){
                $activefts = $this->getLoginCount($userId);
                if(count($activefts) == 0){
                    $this->update(array("logout"=> false),array("fcm_token" => $fcmToken));
                    $save = $this->checkAndUpdateFcm($fcmToken,$deviceId, $userId);
                    return false;
                }else{
                    $logout = $this->getField(array("fcm_token" => $fcmToken),"logout");
                    $save = $this->checkAndUpdateFcm($fcmToken,$deviceId, $userId);
                    if($logout[0] == "1") return true;
                    else return false;
                }
            }else{
                echo "else";
                $save = $this->checkAndUpdateFcm($fcmToken,$deviceId, $userId);
                return false;
            }
        }catch (\Exception $e){
            //  $this->logger->err($e->getMessage());
            print_r($e->getMessage());
            exit;
            return false;
        }
    }
    public function deleteFcmToken($deviceId)
    {
        try
        {
            return  $this->tableGateway->delete(array("device_id" => $deviceId));

        }catch (\Exception $e)
        {

            return array();
        }

    }
    public function clearFCMOnLogin($userId){
        try
        {
            $deviceIds = $this->getDeviceIds($userId);
            if(count($deviceIds)){
                //return  $this->tableGateway->delete(array("user_id" => $userId));
                return $this->update(array("logout"=> true),array("user_id" => $userId));
            }
        }catch (\Exception $e)
        {
            return array();
        }
    }
    public function getLoginCount($userId){
        try {
            $where = new Where();
            $where = $where->equalTo("user_id", $userId);
            $where->and->equalTo("logout", false);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array("f" => "fcm"))
                ->columns(array("fcm_token"))
                ->where($where);
            /*echo $sql->getSqlStringForSqlObject($query);exit;*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $data = array();
            foreach ($resultSet as $row) {
                $data[] = $row["fcm_token"];
            }
            return $data;

        } catch (\Exception $e) {
            return array();
        }
    }

    public function getDeviceIds($userId)
    {
        try {
                $where = new Where();
                $sql = $this->getSql();
                $query = $sql->select()
                    ->from(array("f" => "fcm"))
                    ->columns(array("fcm_token"))
                    ->where($where->equalTo("user_id", $userId));
                /*echo $sql->getSqlStringForSqlObject($query);exit;*/
                $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
                $data = array();
                foreach ($resultSet as $row) {
                    $data[] = $row["fcm_token"];
                }
                return $data;

        } catch (\Exception $e) {
            return array();
        }
    }

    public function checkAndUpdateFcm($fcmToken,$deviceId,$userId){
        try{
            $fcmId = $this->getField(array("device_id"=>$deviceId),"fcm_id");
            $data = array("device_id"=>$deviceId,"fcm_token"=>$fcmToken,"user_id"=>$userId);
            
            if($fcmId[0])
            {
                echo "update"; exit;
                $response = $this->update($data,array("fcm_id"=>$fcmId[0]));

            }else{
                echo "insert"; exit;
                $response = $this->insert($data);
            }
            
            return $response;
        }catch (\Exception $e){
            return false;
        }
    }
    public function getField($where, $column){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array("f" => "fcm"))
                ->columns(array("" . $column . ""))
                ->where($where);

            $field = array();
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field[] = $row['' . $column . ''];
            }

            if (count($field)) {
                return $field;
            } else {
                return "";
            }

        }catch (\Exception $e){
            return "";
        }
    }

}