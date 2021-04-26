<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\TableGateway\TableGateway;

class BannersTable
    extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("b" => "banners");
    }

    public function addBanners(Array $data)
    {
        try{
            $insert = $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e){


            return array("success" => false);
        }
    }
    public function updateBanner($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch(\Exception $e){

            return false;
        }
    }
    public function getBanners(){
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('image_path','banner_id'))
                ->where(array('b.status'=>1));


            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $banners = array();

            foreach($resultSet as $row){
                $banners[] = $row;
            }

            return $banners;
        }catch(\Exception $e){
            return array();
        }
    }
}