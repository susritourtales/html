<?php

namespace Application\Model;

use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGateway;

class BaseTable
{

    protected $tableGateway;
    protected $logger;
    protected $sql;
    protected $select;

    function __construct(TableGateway $tablegateway)
    {
        $this->tableGateway = $tablegateway;
    }

    public function getSql()
    {
        $dbAdapter = $this->tableGateway->adapter;
        $sql = new Sql($dbAdapter);

        return $sql;
    }

    public function getSelect()
    {
        $dbAdapter = $this->tableGateway->adapter;
        $select = new Select($dbAdapter);
        return $select;
    }

    public function getAdapter(){
        return $this->tableGateway->adapter;
    }

    protected function queryToString($query)
    {
        return $query->getSqlString($this->tableGateway->adapter->getPlatform());
    }
    protected function multiInsert(array $data)
    {
         try{
             if(count($data))
             {
                 $adapter = $this->tableGateway->adapter;
                 $columns = (array)current($data);
                 $columns = array_keys($columns);
                 $columnsCount = count($columns);
                 $platform = $adapter->getPlatform();
                 array_filter($columns, function (&$item) use ($platform)
                 {
                     $item = $platform->quoteIdentifier($item);
                 });
                 $columns = "(" . implode(',', $columns) . ")";

                 $placeholder = array_fill(0, $columnsCount, '?');
                 $placeholder = "(" . implode(',', $placeholder) . ")";
                 $placeholder = implode(',', array_fill(0, count($data), $placeholder));

                 $values = array();
                 foreach ($data as $row)
                 {
                     foreach ($row as $key => $value)
                     {
                         $values[] = $value;
                     }
                 }

                 $table = $platform->quoteIdentifier($this->tableGateway->getTable());
                 $query = "INSERT INTO $table $columns VALUES $placeholder";
                 $this->logSqlQuery("");
                 $this->tableGateway->adapter->query($query)->execute($values);
                 return true;

             }else
             {
                 return false;
             }
         }catch (\Exception $e)
         {
             print_r($e->getMessage());
             exit;
         }

    }

    public function insert($data)
    {
        $data["created_at"] = date("Y-m-d H:i:s");
        $data["updated_at"] = date("Y-m-d H:i:s");
        print_r($data);exit;
        $insert = $this->tableGateway->insert($data);
        return $insert;
    }

    protected function update($data, $where)
    {
        try {
            $data["updated_at"] = date("Y-m-d H:i:s");
            $this->logSqlQuery("");
            $update = $this->tableGateway->update($data, $where);
            return $update;
        } catch (\Exception $e) {
            
            return false;
        }

    }

    protected function insertOrUpdate(array $insertData, array $updateData)
    {
        $sqlStringTemplate = 'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s';
        $adapter = $this->tableGateway->adapter; /* Get adapter from tableGateway */
        $driver = $adapter->getDriver();
        $platform = $adapter->getPlatform();

        $tableName = $platform->quoteIdentifier($this->tableGateway->getTable());
        $parameterContainer = new \Laminas\Db\Adapter\ParameterContainer();
        $statementContainer = $adapter->createStatement();
        $statementContainer->setParameterContainer($parameterContainer);

        /* Preparation insert data*/
        $insertQuotedValue = array();
        $insertQuotedColumns = array();
        foreach ($insertData as $column => $value)
        {
            $insertQuotedValue[] = $driver->formatParameterName($column);
            $insertQuotedColumns[] = $platform->quoteIdentifier($column);
            $parameterContainer->offsetSet($column, $value);
        }

        /* Preparation update data*/
        $updateQuotedValue = array();
        foreach ($updateData as $column => $value)
        {
            $updateQuotedValue[] = $platform->quoteIdentifier($column) . '=' . $driver->formatParameterName('update_' . $column);
            $parameterContainer->offsetSet('update_' . $column, $value);
        }

        /* Preparation sql query*/
        $query = sprintf(
            $sqlStringTemplate,
            $tableName,
            implode(',', $insertQuotedColumns),
            implode(',', array_values($insertQuotedValue)),
            implode(',', $updateQuotedValue)
        );

        $statementContainer->setSql($query);
        //$this->logSqlQuery($statementContainer->queryToString());
        return $statementContainer->execute();
    }

    public function logSqlQuery($logString){
        $mY = date("m-Y");
        $fullPath = "/var/www/html/public/beta/logs/sql-$mY.log";
        $timestamp = "\n\n". date("d-m-Y H:i:s") . " >> \n";
        $myfile = file_put_contents($fullPath, $timestamp. $logString.PHP_EOL , FILE_APPEND | LOCK_EX);
        /* print_r(error_get_last());
        return $myfile; */
    }
}