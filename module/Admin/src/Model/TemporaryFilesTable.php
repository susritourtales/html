<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TemporaryFilesTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("t" => "temporary_files");
    }

    public function addTemporaryFile(Array $data)
    {
        try{
            $insert = $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e){
                 print_r($e->getMessage());
                 exit;
            return array("success" => false);
        }
    }
    public function updateTemporary($data,$where)
    {
        try{

            return $this->update($data,$where);
        }catch (\Exception $e)
        {
            return false;
        }
    }
    public function updateCopiedFiles($copyiedIds)
    {
        try
        {
            $sql=$this->getSql();
            $where=new Where();
            $query=$sql->update('temporary_files')->set(array('status'=>\Admin\Model\TemporaryFiles::status_file_copied,'updated_at'=>date("Y-m-d H:i:s")))->where($where->in('temporary_files_id',$copyiedIds));
            /* echo $sql->getSqlStringForSqlObject($query);
             exit;*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            return true;
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return false;
        }
    }
    public function getFiles($fileIds)
    {
        try
        {
            $sql=$this->getSql();
            $values=array();
            $where=new Where();
            $where->in('temporary_files_id',$fileIds);
            $query = $sql->select()
                ->from($this->tableName)
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $imageFiles=array();
            $audioFiles=array();
            foreach ($resultSet as $row)
            {
                if($row['file_extension_type']==\Admin\Model\TourismFiles::file_extension_type_image)
                {
                    $imageFiles[]=$row;
                }else{
                    $audioFiles[$row['temporary_files_id']]=$row;
                }

            }
            return array('images'=>$imageFiles,'audioFiles'=>$audioFiles);
        }catch (\Exception $e)
        {
                 print_r($e->getMessage());
                 exit;
        }
    }


}