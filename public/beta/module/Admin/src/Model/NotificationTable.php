<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 23/8/19
 * Time: 12:44 PM
 */

namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class NotificationTable extends BaseTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function saveData($data)
    {
        try{
            $insert = $this->insert($data);
            if ($insert) {
                return array("success" => true, "id" => $this->tableGateway->lastInsertValue);
            } else {
                return array("success" => false);
            }

        } catch (\Exception $e){
            return false;
        }
    }
    public function addMutipleData(Array $data)
    {
        try{
            return $this->multiInsert($data);
        } catch (\Exception $e){
           /* print_r($e->getMessage());
            exit;*/

            return false;
        }
    }
    public function getNotifications($data=array('limit'=>10,'offset'=>0)){
        try
        {
            $sql = $this->getSql();
            $where=new Where();
              $where->equalTo('notification_recevier_id',$data['user_id']);
           $query=  $sql->select()
                ->from(array("n" => "notification"))
               ->columns(array('notification_text','notification_type','notification_data_id','created_at','notification_id'))
                 ->where($where)
                 ->order("updated_at DESC")
           ->limit($data['limit'])
           ->offset($data['offset']);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $data = array();
            foreach ($resultSet as $row) 
            {
               $data[]=$row;
            }
            return $data;
        }catch(\Exception $e){
           /* print_r($e->getMessage());
            exit;*/
            return array();
        }
    }

}