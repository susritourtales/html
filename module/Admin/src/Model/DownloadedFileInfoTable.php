<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 3/10/19
 * Time: 4:53 PM
 */

namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\TableGateway\TableGateway;

class DownloadedFileInfoTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("d" => "downloaded_file_info");
    }

    public function addDownload(Array $data)
    {
        try
        {
            $insert = $this->insert($data);
            if($insert)
            {
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else{
                return array("success" => false);
            }
        }catch(\Exception $e)
        {
            return array("success" => false);
        }
    }
    public function getFields($where, $column)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns($column )
                ->where($where);

            $field = array();
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row;
            }

            return $field;

        } catch (\Exception $e) {

            return array();
        }
    }

}