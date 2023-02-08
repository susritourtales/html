<?php

namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\TableGateway\TableGateway;

class TwisttOtpTable extends BaseTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("to" => "twistt_otp");
    }
    public function insertData($data)
    {
        try{
            $insert = $this->insert($data);
            if($insert){
                return true;
            }else{
                return false;
            }
        }catch(\Exception $e){

            return false;
        }
    }
    public function multiInsertData($data)
    {
        try{
            $insert = $this->multiInsert($data);
            if($insert){
                return true;
            }else{
                return false;
            }
        }catch(\Exception $e){

            return false;
        }
    }
    public function updateData($data,$where)
    {
        try
        {

            return $this->update($data,$where);
        }catch (\Exception $e)
        {
            return false;
        }
    }
    public function verfiy($data)
    {
        try
        {
            $sql=$this->getSql();
            $values=array();
            $query = $sql->select()
                ->columns(array("otp_id"))
                ->from($this->tableName)
                ->where($data);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values[]=$row;
            }
            if($values)
            {
                return true;
            }else
            {
                return false;
            }

        }catch (\Exception $e)
        {
           return array();
        }
    }
}