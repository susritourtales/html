<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class BookingTourDetailsTable extends  BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("bt" => "booking_tour_details");
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
    public function addBooking(Array $data)
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
    public function getLanguages($bookingId)
    {
        try{

            $where = new Where();
            $where = $where->equalTo('booking_id',$bookingId)->and->equalTo('tf.file_data_type',\Admin\Model\TourismFiles::file_data_type_places);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array())
                ->join(array('tf'=>'tourism_files'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tf`.`file_data_id`,`bt`.`place_ids`) and tf.status=1"),array(),Select::JOIN_LEFT)
                ->join(array('l'=>'languages'),'l.id=tf.file_language_type',array('language_name'=>'name','language_id'=>'id'))
                ->where($where)
                ->group('l.id');

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row)
            {
                $bookings[] = $row;
            }
            return $bookings;
        }catch (\Exception $e)
        {
            /* print_r($e->getMessage());
        exit;*/
            return array();
        }
    }
    public function addMutipleBookings(Array $data)
    {
        try {
            return $this->multiInsert($data);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit;
            return false;
        }
    }
    public function updateBooking($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch(\Exception $e){

            return false;
        }
    }
    public function bookingDetails($data)
    {
        try
        {
            $sql=$this->getSql();
            $values=array();
            $where=new Where();
            $query = $sql->select()
                ->columns(array("sponsered_users","booking_id","expiry_date",
                    "tour_date"))

                ->from($this->tableName)
                ->join(array('b'=>'bookings'),"b.booking_id = bt.booking_id",array('tour_type'))
                ->join(array('u'=>'users'),"u.user_id = b.user_id",array('user_name'))
                ->where(array('b.booking_id'=>$data['booking_id']));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values=$row;
            }


            return $values;
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return array();
        }
    }

}