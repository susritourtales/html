<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 9/9/19
 * Time: 3:24 PM
 */

namespace Admin\Model;


use Application\Handler\Aes;
use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class BookingsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("b" => "bookings");
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
                ->columns(array("user_id","booking_id","tour_type","booking_type"))

                ->from(array("b"=>"bookings"))
                ->join(array('u'=>'users'),"u.user_id = b.user_id",array('user_name'))

                ->where(array('b.user_id'=>$data['user_id'],'b.booking_id'=>$data['booking_id']));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row)
            {
                $values=$row;
            }



            return $values;
        }catch (\Exception $e)
        {
            return array();
        }
    }
    public function getActivateBookingUserList($data=array())
    {
        try
        {
            $where=new Where();
            $sql=$this->getSql();

              $query=$sql->select()->from($this->tableName)
                  ->columns(array('booking_id'))
                  ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array('tour_date','expiry_date','no_of_days','no_of_users','price','sponsered_users'))
                  ->join(array('u'=>'users'),new \Laminas\Db\Sql\Expression('FIND_IN_SET(`u`.`user_id`,`b`.`sponsered_users`)'),array('user_id','mobile_country_code','mobile'))
                 ->where($where->equalTo('tour_date',date("Y-m-d",strtotime( date("Y-m-d"). ' +1 day')))->equalTo('b.status',\Admin\Model\Bookings::Status_Active));
                     /* echo $sql->getSqlStringForSqlObject($query);
                      exit;*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
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
    public function updateMultipleBookings($deleteIds)
    {
        try
        {
            $sql=$this->getSql();
            $where=new Where();
            $data=array('status'=>\Admin\Model\Bookings::Status_Expired,'updated_at'=>date("Y-m-d H:i:s"));
            $query=$sql->update('bookings')->set($data)->where($where->in('booking_id',$deleteIds));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            return true;
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return false;
        }
    }
    public function getDeActivateBookingUserList($data=array())
    {
        try{
            $where=new Where();
            $sql=$this->getSql();

            $query=$sql->select()->from($this->tableName)
                ->columns(array('booking_id'))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array('tour_date','expiry_date','no_of_days','no_of_users','price','sponsered_users'))
                ->where($where->lessThan('expiry_date',date("Y-m-d",strtotime( date("Y-m-d"). ' +1 day')))
                    ->equalTo('b.status',\Admin\Model\Bookings::Status_Active)
                    ->equalTo('b.payment_status',1));
            /* echo $sql->getSqlStringForSqlObject($query);
             exit;*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
                $bookings[] = $row;
            }
            return $bookings;
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return array();
        }
    }
    public function bookingsListPrivilageUserList($data=array('limit'=>10,'offset'=>0))
    {
        try
        {
            $where=new Where();

            $where->equalTo('b.status',1)->equalTo('b.booking_type',\Admin\Model\Bookings::booking_By_Admin);
            if(array_key_exists('date_of_registration',$data))
            {
                $where->and->like("u.created_at",'%'.$data['date_of_registration']."%");
            }


            if(array_key_exists('activation_date',$data))
            {
                $where->and->like("bt.tour_date",'%'.$data['activation_date']."%");
            }
            if(array_key_exists('duartion',$data))
            {
                $where->and->like("bt.no_of_days",'%'.$data['duartion']."%");
            } if(array_key_exists('tour_type',$data))
        {
            $where->and->equalTo("b.tour_type",$data['tour_type']);
        }
            if(array_key_exists('place_name',$data))
            {

                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"),'%'.strtolower($data['place_name'])."%");
            }
            $order=array();
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }
            if(array_key_exists('mobile_order',$data))
            {
                if($data['mobile_order']==1)
                {
                    $order[]='u.mobile asc';
                }else if($data['mobile_order']==-1)
                {
                    $order[]='u.mobile desc';
                }
            }
            if(array_key_exists('date_of_registration_order',$data))
            {
                if($data['date_of_registration_order']==1)
                {
                    $order[]='u.created_at asc';
                }else if($data['date_of_registration_order']==-1)
                {
                    $order[]='u.created_at desc';
                }
            }
            if(array_key_exists('activation_date_order',$data))
            {
                if($data['activation_date_order']==1)
                {
                    $order[]='b.tour_date asc';
                }else if($data['activation_date_order']==-1)
                {
                    $order[]='b.tour_date desc';
                }

            }

            if(array_key_exists('duartion_order',$data))
            {
                if($data['duartion_order']==1)
                {
                    $order[]='bt.no_of_days asc';
                }else if($data['members_count_order']==-1)
                {
                    $order[]='bt.no_of_days desc';
                }

            }
            if(array_key_exists('place_name_order',$data))
            {
                if($data['place_name_order']==1)
                {
                    $order[]='tp.place_name asc';
                }else if($data['place_name_order']==-1)
                {
                    $order[]='tp.place_name desc';
                }

            }
            if(!count($order))
            {
                $order[]='b.updated_at desc';
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("user_type","status",'tour_type','booking_id','created_at'))

                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array('tour_date','expiry_date','no_of_days','no_of_users','price'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`bt`.`place_ids`)"),array('place_name'=>new \Laminas\Db\Sql\Expression('GROUP_CONCAT(place_name)')))
                ->join(array('u'=>'users'),'b.user_id=u.user_id',array('user_name','mobile'))
                ->where($where)
                ->group('b.booking_id')
                ->order($order)
                ->limit($data['limit'])
                ->offset($data['offset']);
               /*echo $sql->getSqlStringForSqlObject($query);
               exit;*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
                $bookings[] = $row;
            }
            return $bookings;
        }catch (\Exception $e)
        {
             print_r($e->getMessage());
            exit;
            return array();
        }
    }
    public function bookingsListPrivilageUserListCount($data=array())
    {
        try{
            $where=new Where();

            $where->equalTo('b.status',1)->and->equalTo('b.booking_type',\Admin\Model\Bookings::booking_By_Admin);
            if(array_key_exists('date_of_registration',$data))
            {
                $where->and->like("u.created_at",'%'.$data['date_of_registration']."%");
            }


            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }
            if(array_key_exists('activation_date',$data))
            {
                $where->and->like("bt.tour_date",'%'.$data['activation_date']."%");
            }
            if(array_key_exists('duartion',$data))
            {
                $where->and->like("bt.no_of_days",'%'.$data['duartion']."%");
            } if(array_key_exists('tour_type',$data))
            {
                $where->and->equalTo("b.tour_type",$data['tour_type']);
            }
            if(array_key_exists('place_name',$data))
            {

                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"),'%'.strtolower($data['place_name'])."%");
            }

            $order=array();

            if(array_key_exists('mobile_order',$data))
            {
                if($data['mobile_order']==1)
                {
                    $order[]='u.mobile asc';
                }else if($data['mobile_order']==-1)
                {
                    $order[]='u.mobile desc';
                }
            }
            if(array_key_exists('activation_date_order',$data))
            {
                if($data['activation_date_order']==1)
                {
                    $order[]='b.tour_date asc';
                }else if($data['activation_date_order']==-1)
                {
                    $order[]='b.tour_date desc';
                }

            }

            if(array_key_exists('duartion_order',$data))
            {
                if($data['duartion_order']==1)
                {
                    $order[]='bt.no_of_days asc';
                }else if($data['members_count_order']==-1)
                {
                    $order[]='bt.no_of_days desc';
                }

            }
            if(array_key_exists('place_name_order',$data))
            {
                if($data['place_name_order']==1)
                {
                    $order[]='tp.place_name asc';
                }else if($data['place_name_order']==-1)
                {
                    $order[]='tp.place_name desc';
                }

            }
            if(!count($order))
            {
                $order[]='b.updated_at desc';
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("user_type","status",'tour_type','booking_id','created_at'))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array('tour_date','expiry_date','no_of_days','no_of_users','price'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`bt`.`place_ids`)"),array('place_name'=>new \Laminas\Db\Sql\Expression('GROUP_CONCAT(place_name)')))

                ->join(array('u'=>'users'),'b.user_id=u.user_id',array('user_name','mobile'))
                ->where($where)
                ->group('b.booking_id')
                ->order($order)
              ;
           /* if(array_key_exists('place_name',$data))
            {

                 echo $sql->getSqlStringForSqlObject($query);
                             exit;            }*/


            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();


            foreach($resultSet as $row){
                $bookings[] = $row;
            }
            return $bookings;
        }catch (\Exception $e)
        {
            return array();
        }
    }
    public function bookingsListAdmin($data=array('limit'=>10,'offset'=>0))
    {
        try
        {
            $where=new Where();

            $where->equalTo('b.booking_type',\Admin\Model\Bookings::booking_by_User)->equalTo('b.payment_status',1);
                   if(array_key_exists('booking_id',$data))
                   {
                       $where->and->like("b.booking_id",'%'.$data['booking_id']."%");
                   }
            if(array_key_exists('booking_date',$data))
                   {
                       $where->and->like("b.created_at",'%'.$data['booking_date']."%");
                   }
            if(array_key_exists('amount',$data))
                   {
                       $where->and->like("bt.price",'%'.$data['amount']."%");
                   }
            if(array_key_exists('user',$data))
                   {
                       $where->and->like("u.user_name",'%'.$data['user']."%");
                   }
            if(array_key_exists('activation_date',$data))
            {
                $where->and->like("bt.tour_date",'%'.$data['activation_date']."%");
            }
            if(array_key_exists('expiry_date',$data))
            {
                $where->and->like("bt.expiry_date",'%'.$data['expiry_date']."%");
            }
            if(array_key_exists('members_count',$data))
            {
                $where->and->like("bt.no_of_users",'%'.$data['members_count']."%");
            } if(array_key_exists('tour_type',$data))
            {
                $where->and->equalTo("b.tour_type",$data['tour_type']);
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }

            $order=array();
            if(array_key_exists('booking_id_order',$data))
            {
                if($data['booking_id_order']==1)
                {
                    $order[]='b.booking_id asc';
                }else if($data['booking_id_order']==-1)
                {
                    $order[]='b.booking_id desc';
                }

            }
            if(array_key_exists('mobile_order',$data))
            {
                if($data['mobile_order']==1)
                {
                    $order[]='u.mobile asc';
                }else if($data['mobile_order']==-1)
                {
                    $order[]='u.mobile desc';
                }
            }
            if(array_key_exists('booking_date_order',$data))
            {
                if($data['booking_date_order']==1)
                {
                    $order[]='b.created_at asc';
                }else if($data['booking_date_order']==-1)
                {
                    $order[]='b.created_at desc';
                }

            }if(array_key_exists('amount_order',$data))
            {
                if($data['amount_order']==1)
                {
                    $order[]='b.price asc';
                }else if($data['amount_order']==-1)
                {
                    $order[]='b.price desc';
                }

            }
            if(array_key_exists('user_order',$data))
            {
                if($data['user_order']==1)
                {
                    $order[]='u.user_name asc';
                }else if($data['user_order']==-1)
                {
                    $order[]='u.user_name desc';
                }

            }
            if(array_key_exists('activation_date_order',$data))
            {
                if($data['activation_date_order']==1)
                {
                    $order[]='b.tour_date asc';
                }else if($data['activation_date_order']==-1)
                {
                    $order[]='b.tour_date desc';
                }

            }
            if(array_key_exists('activation_date_order',$data))
            {
                if($data['activation_date_order']==1)
                {
                    $order[]='b.tour_date asc';
                }else if($data['activation_date_order']==-1)
                {
                    $order[]='b.tour_date desc';
                }

            }
            if(array_key_exists('expiry_date_order',$data))
            {
                if($data['expiry_date_order']==1)
                {
                    $order[]='b.expiry_date asc';
                }else if($data['expiry_date_order']==-1)
                {
                    $order[]='b.expiry_date desc';
                }

            }
            if(array_key_exists('members_count_order',$data))
            {
                if($data['members_count_order']==1)
                {
                    $order[]='b.no_of_users asc';
                }else if($data['members_count_order']==-1)
                {
                    $order[]='b.no_of_users desc';
                }

            }
            if(!count($order))
            {
                $order[]='b.updated_at desc';
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("user_type","status",'tour_type','booking_id','created_at'))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array('tour_date','expiry_date','no_of_days','no_of_users','price'))
                ->join(array('u'=>'users'),'b.user_id=u.user_id',array('user_name','mobile_country_code','mobile','role','res_state'))
                /* ->join(array('to'=>'tour_operator_details'),new \Laminas\Db\Sql\Expression('b.user_id=to.user_id  and u.role = 2'),array('company_name','contact_person'),Select::JOIN_LEFT)
                ->join(array('tc'=>'tour_coordinator_details'),new \Laminas\Db\Sql\Expression('b.user_id=tc.user_id and u.role = 5'),array('coordinator_name'=>'user_name'),Select::JOIN_LEFT)
                ->join(array('tcc'=>'tour_operator_details'),new \Laminas\Db\Sql\Expression('tc.company_id=tcc.tour_operator_id and u.role = 5'),array('coordinator_company_name'=>'company_name'),Select::JOIN_LEFT) */
                ->where($where)
                ->order($order)
                ->limit($data['limit'])
                ->offset($data['offset']);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
                $bookings[] = $row;
            }

            return $bookings;
        }catch (\Exception $e){
            /* print_r($e->getMessage());
            exit;*/
            return array();
        }
    }
    public function bookingsListAdminCount($data=array())
    {
        try
        {
            $where=new Where();
            $where->equalTo('b.booking_type',\Admin\Model\Bookings::booking_by_User)->equalTo('b.payment_status',1);
            if(array_key_exists('booking_id',$data))
            {
                $where->and->like("b.booking_id",'%'.$data['booking_id']."%");
            }
            if(array_key_exists('booking_date',$data))
            {
                $where->and->like("b.created_at",'%'.$data['booking_date']."%");
            }
            if(array_key_exists('amount',$data))
            {
                $where->and->like("bt.price",'%'.$data['amount']."%");
            }
            if(array_key_exists('user',$data))
            {
                $where->and->like("u.user_name",'%'.$data['user']."%");
            }
            if(array_key_exists('activation_date',$data))
            {
                $where->and->like("bt.tour_date",'%'.$data['activation_date']."%");
            }
            if(array_key_exists('expiry_date',$data))
            {
                $where->and->like("bt.expiry_date",'%'.$data['expiry_date']."%");
            }
            if(array_key_exists('members_count',$data))
            {
                $where->and->like("bt.no_of_users",'%'.$data['members_count']."%");
            }
            if(array_key_exists('tour_type',$data))
            {
                $where->and->equalTo("b.tour_type",$data['tour_type']);
            }
            if(array_key_exists('mobile',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile']."%");
            }

            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('booking_count'=>new \Laminas\Db\Sql\Expression('count(`b`.`booking_id`)')))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array())
                ->join(array('u'=>'users'),'b.user_id=u.user_id',array())
                /* ->join(array('to'=>'tour_operator_details'),new \Laminas\Db\Sql\Expression('b.user_id=to.user_id  and b.user_type = 2'),array('company_name','contact_person'),Select::JOIN_LEFT)
                ->join(array('tc'=>'tour_coordinator_details'),new \Laminas\Db\Sql\Expression('b.user_id=tc.user_id and b.user_type = 5'),array('coordinator_name'=>'user_name'),Select::JOIN_LEFT)
                ->join(array('tcc'=>'tour_operator_details'),new \Laminas\Db\Sql\Expression('tc.company_id=tcc.tour_operator_id and b.user_type = 5'),array('coordinator_company_name'=>'company_name'),Select::JOIN_LEFT) */

                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
                $bookings = $row;
            }

            return $bookings;
        }catch (\Exception $e)
        {
            return array();
        }
    }
    public function getUserDownloadsAdmin($data=array('limit'=>10,'offset'=>0))
    {
        try
        {
            $where=new Where();

            $where->equalTo('b.booking_type',\Admin\Model\Bookings::booking_by_User)->equalTo('b.user_id', $data['user_id']); //->equalTo('b.payment_status',1)
            if(array_key_exists('booking_id',$data))
            {
                $where->and->like("b.booking_id",'%'.$data['booking_id']."%");
            }       
            if(array_key_exists('booking_date',$data))
            {
                $where->and->like("b.created_at",'%'.$data['booking_date']."%");
            }
            if(array_key_exists('bookings_count',$data))
            {
                $where->and->like("b.created_at",'%'.$data['bookings_count']."%");
            } if(array_key_exists('tour_type',$data))
            {
                $where->and->equalTo("b.tour_type",$data['tour_type']);
            }
            $order[]='b.created_at desc';  //new \Laminas\Db\Sql\Expression("(LENGTH(bt.place_ids) - LENGTH(REPLACE(bt.place_ids, ',', '')) + 1)")
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('tour_type','booking_id','created_at',new \Laminas\Db\Sql\Expression("(LENGTH(bt.place_ids) - LENGTH(REPLACE(bt.place_ids, ',', '')) + 1)")))
                ->join(array('bt'=>'booking_tour_details'),new \Laminas\Db\Sql\Expression('bt.booking_id=b.booking_id and b.booking_type='.\Admin\Model\Bookings::booking_by_User),array())
                ->join(array('u'=>'users'),new \Laminas\Db\Sql\Expression('u.user_id=b.user_id'),array('user_name'))
                ->where($where)
                ->order($order)
                ->group(array('b.created_at', 'b.tour_type'))
                ->limit($data['limit'])
                ->offset($data['offset']);

            //echo $sql->getSqlStringForSqlObject($query);exit; 
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
                $bookings[] = $row;
            }

            return $bookings;
        }catch (\Exception $e){
            /* print_r($e->getMessage());
            exit;*/
            return array();
        }
    }
    public function getUserDownloadsAdminCount($data=array())
    {
        try
        {
            $where=new Where();
            $where->equalTo('booking_type',\Admin\Model\Bookings::booking_by_User)->equalTo('user_id', $data['user_id']); //->equalTo('payment_status',1)
            if(array_key_exists('booking_id',$data))
            {
                $where->and->like("booking_id",'%'.$data['booking_id']."%");
            }
            if(array_key_exists('booking_date',$data))
            {
                $where->and->like("created_at",'%'.$data['booking_date']."%");
            }
            if(array_key_exists('bookings_count',$data))
            {
                $where->and->like("created_at",'%'.$data['bookings_count']."%");
            } if(array_key_exists('tour_type',$data))
            {
                $where->and->equalTo("tour_type",$data['tour_type']);
            }

            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('booking_count'=>new \Laminas\Db\Sql\Expression('count(`booking_id`)')))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
                $bookings = $row;
            }

            return $bookings;
        }catch (\Exception $e)
        {
            return array();
        }
    }

    public function getsponsorPasswordsAdmin($data=array('limit'=>10,'offset'=>0))
    {
        try
        {
            $where=new Where();
            $where->equalTo('b.booking_type',\Admin\Model\Bookings::booking_Buy_Passwords)->equalTo('b.user_id', $data['user_id']); 
            if(array_key_exists('booking_id',$data))
            {
                $where->and->like("b.booking_id",'%'.$data['booking_id']."%");
            }       
            if(array_key_exists('booking_date',$data))
            {
                $where->and->like("b.created_at",'%'.$data['booking_date']."%");
            }
            $order[]='b.created_at desc';
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('booking_id','created_at'))
                ->join(array('bt'=>'booking_tour_details'),new \Laminas\Db\Sql\Expression('bt.booking_id=b.booking_id and b.payment_status=' . \Admin\Model\Payments::payment_success),array('no_of_users','price','discount_percentage'))
                ->join(array('u'=>'users'),new \Laminas\Db\Sql\Expression('u.user_id=b.user_id'),array('user_name'))
                ->join(array('p'=>'password'),new \Laminas\Db\Sql\Expression('p.booking_id=b.booking_id and p.password_type='.\Admin\Model\Password::passowrd_type_booking),array('pcount'=>new \Laminas\Db\Sql\Expression('COUNT(`p`.`id`)')))
                ->where($where)
                ->order($order)
                ->group(array('b.created_at'))
                ->limit($data['limit'])
                ->offset($data['offset']);

            //echo $sql->getSqlStringForSqlObject($query);exit; 
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
                $bookings[] = $row;
            }

            return $bookings;
        }catch (\Exception $e){
            /* print_r($e->getMessage());
            exit;*/
            return array();
        }
    }
    public function getsponsorPasswordsAdminCount($data=array())
    {
        try
        {
            $where=new Where();
            $where->equalTo('b.booking_type',\Admin\Model\Bookings::booking_Buy_Passwords)->equalTo('b.user_id', $data['user_id']); 
            if(array_key_exists('booking_id',$data))
            {
                $where->and->like("booking_id",'%'.$data['booking_id']."%");
            }
            if(array_key_exists('booking_date',$data))
            {
                $where->and->like("created_at",'%'.$data['booking_date']."%");
            }
            $order[]='b.created_at desc';
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('booking_id','created_at'))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array('no_of_users','price','discount_percentage'))
                ->where($where)
                ->order($order)
                ->group(array('b.created_at'));
            /* ->join(array('bt'=>'booking_tour_details'),new \Laminas\Db\Sql\Expression('bt.booking_id=b.booking_id and b.booking_type='.\Admin\Model\Bookings::booking_Buy_Passwords),array('no_of_users','price','discount_percentage')) */
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
                $bookings[] = $row;
            }

            return $bookings;
        }catch (\Exception $e)
        {
            return array();
        }
    }

    public function getField($where, $column)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("" . $column . ""))
                ->where($where);

            $field = array();
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row['' . $column . ''];
            }

            if ($field) {
                return $field;
            } else {
                return "";
            }

        } catch (\Exception $e) {

            return "";
        }
    }
      public function bookingsList($data=array())
      {
        try{
            $where=new Where();
            $where->equalTo('user_id',$data['user_id'])->equalTo('booking_type' ,\Admin\Model\Bookings::booking_by_User)->equalTo('payment_status',\Admin\Model\Payments::payment_success)->notEqualTo('tour_type', \Admin\Model\PlacePrices::tour_type_All_tour); // modified by Manjary
            /* $where->equalTo('user_id',$data['user_id'])->equalTo('booking_type' ,\Admin\Model\Bookings::booking_by_User)->equalTo('payment_status',\Admin\Model\Payments::payment_success); 
            
            greaterThanOrEqualTo('booking_type' ,\Admin\Model\Bookings::booking_by_User)->lessThanOrEqualTo('booking_type' ,\Admin\Model\Bookings::booking_Subscription)->*/
            
            if(array_key_exists('type',$data) && $data['type']==1) 
            {
                $where->lessThan('bt.expiry_date',date("Y-m-d",strtotime( date("Y-m-d"))));
                //$where->equalTo('status',\Admin\Model\Bookings::Status_Expired);
            }else{
                $where->greaterThanOrEqualTo('bt.expiry_date',date("Y-m-d",strtotime( date("Y-m-d"))));
                //$where->equalTo('status',\Admin\Model\Bookings::Status_Active);
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("user_type",'tour_type',"status",'booking_id','created_at','payment_status'))
                ->join(array('bt'=>'booking_tour_details'),'b.booking_id=bt.booking_id',(array('tour_date','expiry_date')))
                ->where($where)
                ->order(array('created_at desc'))
                ->limit($data['limit'])
                ->offset($data['offset']);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();

            foreach($resultSet as $row){
                $bookings[] = $row;
            }

            return $bookings;
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return array();
        }
      }
      public function sponsoredBookingsList($data=array())
      {
           try
           {

               $where=new Where();
               $where->like('bt.sponsered_users',"%,".$data['user_id'].",%")
                   ->lessThanOrEqualTo('tour_date',date("Y-m-d",strtotime( date("Y-m-d"). ' +1 day')))
                   ->equalTo('b.status',\Admin\Model\Bookings::Status_Active)
                   ->greaterThanOrEqualTo('expiry_date',date("Y-m-d",strtotime( date("Y-m-d"))));

                if(isset($data['tour_type']))
                {
                    $where->equalTo('tour_type',$data['tour_type']);
                }

               $sql = $this->getSql();
               $query = $sql->select()
                   ->from($this->tableName)
                   ->columns(array("user_type",'tour_type',"status",'booking_id','created_at'))
                   ->join(array('bt'=>'booking_tour_details'),'b.booking_id=bt.booking_id',(array('tour_date','expiry_date')))
                   ->join(array('u'=>'users'),'b.user_id=u.user_id',(array('user_name')))
                   ->where($where)
                   ->order(array('b.created_at desc'))
                   ->limit($data['limit'])
                   ->offset($data['offset']);
                 /* echo $sql->getSqlStringForSqlObject($query);
                  exit;*/
               $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
               $bookings = array();

               foreach($resultSet as $row){
                   $bookings[] = $row;
               }

               return $bookings;
           }catch (\Exception $e)
           {
                  print_r($e->getMessage());
                  exit;
               return array();
           }
      }
    public function bookingsDetailsEmail($bookingId)
    {
        try{
            $where=new Where();
            $where=$where->equalTo('b.booking_id',$bookingId);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("user_type","status",'tour_type',"booking_id",'created_at','user_id'))
                ->join(array('bt'=>'booking_tour_details'),'b.booking_id=bt.booking_id',(array('discount_percentage','tour_date','no_of_days','no_of_users','expiry_date','sponsered_users','price','booking_tour_id')))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`bt`.`place_ids`)"),array('place_name'))
                ->join(array('sp'=>'seasonal_specials'),"bt.package_id=sp.seasonal_special_id",array('seasonal_name'),Select::JOIN_LEFT)
                ->join(array('tf'=>'tourism_files'),new \Laminas\Db\Sql\Expression("tf.file_data_id =sp.seasonal_special_id and tf.file_data_type=5 and tf.file_extension_type=1 and tf.status = 1"),array('file_path'),Select::JOIN_LEFT)
                ->join(array('tfi'=>'tourism_files'),new \Laminas\Db\Sql\Expression("tfi.file_data_id =b.booking_id and tfi.file_data_type=7 and tfi.file_extension_type=1 and tfi.status = 1"),array('itinerary_image'=>'file_path'),Select::JOIN_LEFT)
                ->join(array('c'=>'countries'),'c.id=tp.country_id',array('country_name'))
                ->join(array('s'=>'states'),'s.id=tp.state_id',array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),'ci.id=tp.city_id',array('city_name'),Select::JOIN_LEFT)
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();
            $placeDetails=array();
            $counter=0;
            $bookingCounter=0;
            $tourDetails=array();
            $bookingTourDetails=array();
            foreach($resultSet as $row)
            {
                $bookings['user_type'] = $row['user_type'];
                $bookings['price'] = floatval($row['price']);

                $bookings['tax'] = ($row['price']?($row['price']-($row['price']/1.18)):0);
                $bookings['discount_percentage'] = ($row['discount_percentage']==""?0:$row['discount_percentage']);
                $bookings['discount_price'] = ($row['discount_percentage']==""?0:$row['discount_percentage']);
                $bookings['booking_id'] = $row['booking_id'];
                $bookings['created_at'] = $row['created_at'];
                $bookings['tour_type'] = $row['tour_type'];
                $bookings['user_id'] = $row['user_id'];

                if($row['tour_type']==\Admin\Model\PlacePrices::tour_type_Seasonal_special)
                {
                    $bookings['tour_date'] = $row['tour_date'];
                    $bookings['no_of_days'] = intval($row['no_of_days']);
                    $bookings['no_of_users'] = intval($row['no_of_users']);
                    $bookings['expiry_date'] = $row['expiry_date'];
                    $bookings['seasonal_special_details']=array('seasonal_name'=>$row['seasonal_name'],'file_path'=>$row['file_path']);

                }else
                {
                    $bookings['tour_date'] = $row['tour_date'];
                    $bookings['no_of_days'] = intval($row['no_of_days']);
                    $bookings['no_of_users'] = intval($row['no_of_users']);
                    $bookings['expiry_date'] = $row['expiry_date'];
                }
                if($row['itinerary_image'])
                {
                    $bookings['itinerary_image']=$row['itinerary_image'];
                }

                $bookings['sponsered_users'] = $row['sponsered_users'];
                $bookings['status'] = $row['status'];
                $placeDetails[$counter]['place_name']=$row['place_name'];
                $placeDetails[$counter]['country_name']=$row['country_name'];
                $placeDetails[$counter]['state_name']=$row['state_name'];
                $placeDetails[$counter]['city_name']=$row['city_name'];
                $counter++;
            }
            if(count($bookings))
            {
                $bookings['user_list']=array();
                $bookings['passwords']=self::bookingPasswords($row['booking_id']);

                unset($bookings['sponsered_users']);
                if($bookings['tour_type'] != \Admin\Model\PlacePrices::tour_type_Seasonal_special)
                {
                    $bookings['place_details']=$placeDetails;
                }

                if($bookings['tour_type']==\Admin\Model\PlacePrices::tour_type_City_tour)
                {
                    $bookings['tour_users']=$tourDetails;
                }
            }

            return $bookings;
        }catch (\Exception $e){
            print_r($e->getMessage());
            exit;
            return array();
        }

    }
    public function bookingUserDetails($user)
    {
        try{
            $sql=$this->getSql();
            $role=$user['role'];
            if($role == \Admin\Model\User::Tour_coordinator_role)
            {
                $query=$sql->select()
                    ->columns(array('user_id','user_name','email'))
                    ->from(array('tc'=>'tour_coordinator_details'))
                    ->join(array('t'=>'tour_operator_details'),'t.tour_operator_id=tc.company_id',array('status','company_name'))
                    ->join(array('p'=>'password'),new \Laminas\Db\Sql\Expression('p.user_id=tc.user_id and p.password_type=1 and p.status=1'),array('hash','password'))

                    ->where(array('tc.user_id'=>$user['user_id']));
            }else
            {
                $query=$sql->select()
                    ->columns(array('status','user_id','company_name','contact_person','email_id'))
                    ->from(array('t'=>'tour_operator_details'))

                    ->where(array('t.user_id'=>$user['user_id']));
            }
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings=array();
            foreach($resultSet as $row){
                   if($role==\Admin\Model\User::Tour_Operator_role)
                   {
                       $bookings['company_name'] = $row['company_name'];
                       $bookings['contact_person'] = $row['contact_person'];
                   }else
                   {
                       $bookings['contact_person'] = $row['user_name'];
                       $bookings['company_name'] = $row['company_name'];
                   }
            }
            return $bookings;
        }catch (\Exception $e)
        {

            return array();
        }


    }
    public function bookingsDetails($bookingId)
    {
        try{
            $where=new Where();
            $where=$where->equalTo('b.booking_id',$bookingId);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("user_type","status",'tour_type',"booking_id",'created_at','user_id','payment_status'))
                ->join(array('bt'=>'booking_tour_details'),'b.booking_id=bt.booking_id',(array('tour_date','expiry_date',/* 'no_of_days','no_of_users','discount_percentage','expiry_date','sponsered_users','price', */'booking_tour_id')))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`bt`.`place_ids`)"),array('place_name'))
                ->join(array('sp'=>'seasonal_specials'),"bt.package_id=sp.seasonal_special_id",array('seasonal_name'),Select::JOIN_LEFT)
                ->join(array('tf'=>'tourism_files'),new \Laminas\Db\Sql\Expression("tf.file_data_id =sp.seasonal_special_id and tf.file_data_type=5 and tf.file_extension_type=1 and tf.status = 1"),array('file_path'),Select::JOIN_LEFT)
                ->join(array('tfi'=>'tourism_files'),new \Laminas\Db\Sql\Expression("tfi.file_data_id =b.booking_id and tfi.file_data_type=7 and tfi.file_extension_type=1 and tfi.status = 1"),array('itinerary_image'=>'file_path'),Select::JOIN_LEFT)
                ->join(array('c'=>'countries'),'c.id=tp.country_id',array('country_name'))
                ->join(array('s'=>'states'),'s.id=tp.state_id',array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),'ci.id=tp.city_id',array('city_name'),Select::JOIN_LEFT)
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();
               $placeDetails=array();
            $counter=0;
            $bookingCounter=0;
            $tourDetails=array();
            $bookingTourDetails=array();
            foreach($resultSet as $row)
            {
                $bookings['user_type'] = $row['user_type'];
                //if($bookings['user_type']==\Admin\Model\User::Tour_Operator_role || $bookings['user_type']==\Admin\Model\User::Tour_coordinator_role) // - removed by Manjary for STT-SV
                if($bookings['user_type']==\Admin\Model\User::Subscriber_role)
                {
                    $bookings['user_details']=$this->bookingUserDetails(array("role"=>$row['user_type'],'user_id'=>$row['user_id']));
                }else
                {
                    $bookings['user_details']=null;
                }

                /* $bookings['price'] = floatval($row['price']);
                $bookings['tax'] = ($row['price']?($row['price']-($row['price']/1.18)):0);
                $bookings['discount_percentage'] = ($row['discount_percentage']==""?0:intval($row['discount_percentage'])); */
                $bookings['booking_id'] = $row['booking_id'];
                $bookings['created_at'] = $row['created_at'];
                $bookings['tour_type'] = $row['tour_type'];
                //$bookings['payment_status'] = $row['payment_status'];

                if($row['tour_type']==\Admin\Model\PlacePrices::tour_type_Seasonal_special)
                {
                    $bookings['tour_date'] = $row['tour_date'];
                    /* $bookings['no_of_days'] = intval($row['no_of_days']);
                    $bookings['no_of_users'] = intval($row['no_of_users']); */
                    $bookings['expiry_date'] = $row['expiry_date'];
                    $bookings['seasonal_special_details']=array('seasonal_name'=>$row['seasonal_name'],'file_path'=>$row['file_path']);

                }else
                {
                    $bookings['tour_date'] = $row['tour_date'];
                    /* $bookings['no_of_days'] = intval($row['no_of_days']);
                    $bookings['no_of_users'] = intval($row['no_of_users']); */
                    $bookings['expiry_date'] = $row['expiry_date'];
                }
                /* if($row['itinerary_image'])
                {
                    $bookings['itinerary_image']=$row['itinerary_image'];
                } */

                //$bookings['sponsered_users'] = $row['sponsered_users'];
                $bookings['status'] = $row['status'];
                $placeDetails[$counter]['place_name']=$row['place_name'];
                $placeDetails[$counter]['country_name']=$row['country_name'];
                $placeDetails[$counter]['state_name']=$row['state_name'];
                $placeDetails[$counter]['city_name']=$row['city_name'];
                $counter++;
            }
             if(count($bookings))
             {
                 //$bookings['user_list']=array();

                 //$bookings['passwords']=self::bookingPassword($bookings['booking_id']);

                 //unset($bookings['sponsered_users']);
                 if($bookings['tour_type'] != \Admin\Model\PlacePrices::tour_type_Seasonal_special)
                 {
                     $bookings['place_details']=$placeDetails;
                 }

                 if($bookings['tour_type']==\Admin\Model\PlacePrices::tour_type_City_tour)
                 {
                     $bookings['tour_users']=$tourDetails;
                 }
             }

            return $bookings;
        }catch (\Exception $e){
            print_r($e->getMessage());
            exit;
            return array();
        }

    }
    public function userDetails($userIds,$bookingId)
    {
        try
        {
            $where=new Where();
            $where=$where->in('u.user_id',$userIds)->equalTo('p.booking_id',$bookingId);

            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array('u'=>'users'))
                ->columns(array("mobile","mobile_country_code"))
                ->join(array('p'=>'password'),'u.user_id=p.user_id',array('password','hash'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();
            $counter=0;
            $aes=new Aes();
            foreach($resultSet as $row){
                $bookings[$counter]['mobile_country_code'] = $row['mobile_country_code'];
                $bookings[$counter]['mobile'] = $row['mobile'];
                $password=$aes->decrypt($row['password'],$row['hash']);
                $bookings[$counter]['password'] = $password;
                $counter++;
            }

            return $bookings;
        }catch (\Exception $e)
        {
             /* print_r($e->getMessage());
              exit;*/
            return array();
        }
    }

    public function bookingPassword($bookingId)
    {
        try{
            $where=new Where();
            $where=$where->equalTo('p.booking_id',$bookingId);

            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array('p'=>'password'))
                ->columns(array('password','hash','id'))
                ->where($where)
            ->order('created_at asc')
            ->limit(1);


            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();
            $counter=0;
            $aes=new Aes();
            $password=[];
            foreach($resultSet as $row)
            {
                $password = $aes->decrypt($row['password'],$row['hash']);
                   // for city tour password plus password id
                $password = $password.$row['id'];
            }
            return $password;
        }catch (\Exception $e)
        {
            return '';
        }
    }

    public function bookingPasswords($bookingId)
    {
        try{
            $where=new Where();
            $where=$where->equalTo('p.booking_id',$bookingId);

            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array('p'=>'password'))
                ->columns(array('password','hash','id'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();
            $counter=0;
            $aes=new Aes();
            $password=[];
            foreach($resultSet as $row)
            {
                $password = $aes->decrypt($row['password'],$row['hash']);
                   // for city tour password plus password id
                $passwords[] = $password.$row['id'];
            }

            return $passwords;
        }catch (\Exception $e)
        {

            return '';
        }
    }

    public function bookingsDetailsAdmin($bookingId)
    {
        try
        {
            $where=new Where();
            $where=$where->equalTo('b.booking_id',$bookingId);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("user_type","status","booking_id",'created_at','tour_type'))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array('tour_date','discount_percentage','no_of_days','no_of_users','expiry_date','sponsered_users','price'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`bt`.`place_ids`)"),array('place_name'))
                ->join(array('c'=>'countries'),'c.id=tp.country_id',array('country_name'))
                ->join(array('s'=>'states'),'s.id=tp.state_id',array('state_name'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),'ci.id=tp.city_id',array('city_name'),Select::JOIN_LEFT)
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();
            $placeDetails=array();
            $counter=0;
            foreach($resultSet as $row)
            {
                $bookings['user_type'] = $row['user_type'];
                $bookings['price'] = floatval($row['price']);
                $bookings['booking_id'] = $row['booking_id'];
                $bookings['created_at'] = $row['created_at'];
                $bookings['tour_type'] = $row['tour_type'];
                $bookings['status'] = $row['status'];
                $bookings['tour_date'] = $row['tour_date'];
                $bookings['no_of_days'] = intval($row['no_of_days']);
                $bookings['no_of_users'] = intval($row['no_of_users']);
                $bookings['expiry_date'] = $row['expiry_date'];
                $bookings['sponsered_users'] = $row['sponsered_users'];


                $bookings['discount_percentage'] = ($row['discount_percentage']==""?0:intval($row['discount_percentage']));

                $placeDetails[$counter]['place_name']=$row['place_name'];
                $placeDetails[$counter]['country_name']=$row['country_name'];
                $placeDetails[$counter]['state_name']=$row['state_name'];
                $placeDetails[$counter]['city_name']=$row['city_name'];
                $counter++;
            }
            $bookings['user_list']=array();
            if($bookings['sponsered_users'])
            {
                $bookings['user_list']=self::userDetailsAdmin(explode(",",$bookings['sponsered_users']),$bookingId);
            }
            unset($bookings['sponsered_users']);
            $bookings['place_details']=$placeDetails;
            return $bookings;
        }catch (\Exception $e)
        {
            return array();
        }

    }
    public function bookingFiles($bookingId)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("booking_id",'tour_type','user_type','user_id'))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array('tour_date','no_of_days','expiry_date','package_id','booking_tour_id'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`bt`.`place_ids`)"),array('place_id'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tourism_place_id`)"),'country_id'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`country_id`)"),'state_id'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`state_id`)"),'city_id'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`city_id`)")))
                ->join(array('tf'=>'tourism_files'),new \Laminas\Db\Sql\Expression("tf.file_data_id =b.booking_id and tf.file_data_type=7 and tf.file_extension_type=1 and tf.status = 1"),array('itinerary_image'=>'file_path'),Select::JOIN_LEFT)
                ->where(array('bt.booking_id'=>$bookingId));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $details=array();
            foreach($resultSet as $row)
            {
                 $details=$row;
            }

            return $details;
            
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
              return array();
        }
    }
    public function bookingFilesCityTour($bookingId)
    {
        try
        {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("booking_id",'tour_type','user_type','user_id'))
                ->join(array('bt'=>'booking_tour_details'),'bt.booking_id=b.booking_id',array('tour_date','no_of_days','expiry_date','package_id','booking_tour_id'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`bt`.`place_ids`)"),array('place_id'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tourism_place_id`)"),'country_id'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`country_id`)"),'state_id'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`state_id`)"),'city_id'=>new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`city_id`)")))
                ->join(array('tf'=>'tourism_files'),new \Laminas\Db\Sql\Expression("tf.file_data_id =b.booking_id and tf.file_data_type=7 and tf.file_extension_type=1 and tf.status = 1"),array('itinerary_image'=>'file_path'),Select::JOIN_LEFT)

                ->where(array('bt.booking_tour_id'=>$bookingId));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $details=array();
            foreach($resultSet as $row)
            {
                $details=$row;
            }
            return $details;

        }catch (\Exception $e)
        {
            return array();
        }
    }
    public function bookingFilesDetails($bookingId)
    {
        try{



            $where=new Where();
            $where=$where->equalTo('booking_id',$bookingId);
            $sql = $this->getSql();


            $placeFiles= $sql->select()
                ->from(array('pf'=>'tourism_files'))
                ->columns(array("file_path",'file_data_id','file_data_type'))
                ->where(array('pf.status'=>1,'pf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_places));

            $countryFiles= $sql->select()
                ->from(array('cf'=>'tourism_files'))
                ->columns(array("file_path",'file_data_id','file_data_type'))
                ->where(array('cf.status'=>1,'cf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_country));
            $stateFiles= $sql->select()
                ->from(array('sf'=>'tourism_files'))
                ->columns(array("file_path",'file_data_id','file_data_type'))
                ->where(array('sf.status'=>1,'sf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_state));
            $cityFiles= $sql->select()
                ->from(array('cf'=>'tourism_files'))
                ->columns(array("file_path",'file_data_id','file_data_type'))
                ->where(array('cf.status'=>1,'cf.file_data_type'=>\Admin\Model\TourismFiles::file_data_type_city));


            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("user_type","status",'tour_date','no_of_days','no_of_users','expiry_date','sponsered_users','tour_type',"booking_id",'created_at','price'))
                ->join(array('tp'=>'tourism_places'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`b`.`place_ids`)"),array('place_name','tourism_place_id'))
                ->join(array('tf'=>$placeFiles),"tp.tourism_place_id = tf.file_data_id",array("file_path",'file_data_type'))
                ->join(array('tfc'=>$countryFiles),"tfc.file_data_id = tp.country_id ",array("file_path",'file_data_type'))
                ->join(array('tfs'=>$stateFiles),"tfs.file_data_id = tp.state_id ",array("file_path",'file_data_type'),Select::JOIN_LEFT)
                ->join(array('tfci'=>$cityFiles),"tfci.file_data_id  = tp.city_id ",array("file_path",'file_data_type'))

                ->join(array('c'=>'countries'),'c.id=tp.country_id',array('country_name','country_id'=>'id'))
                ->join(array('s'=>'states'),'s.id=tp.state_id',array('state_name','state_id'=>'id'),Select::JOIN_LEFT)
                ->join(array('ci'=>'cities'),'ci.id=tp.city_id',array('city_name','city_id'=>'id'),Select::JOIN_LEFT)
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();
            $placeDetails=array();
            $counter=0;
              $placeIds=array();

            foreach($resultSet as $row)
            {
                $bookings['booking_id'] = $row['booking_id'];
                $bookings['tour_type'] = $row['tour_type'];
                $bookings['status'] = $row['status'];
                $bookings['tour_date'] = $row['tour_date'];
                $bookings['no_of_days'] = intval($row['no_of_days']);
                $bookings['expiry_date'] = $row['expiry_date'];

                if(!in_array($row['tourism_place_id'],$placeIds))
                {
                    $placeIds[$counter]=$row['tourism_place_id'];

                    $placeDetails[$counter]['tourism_place_id']=$row['tourism_place_id'];
                    $placeDetails[$counter]['place_name']=$row['place_name'];
                    $placeDetails[$counter]['country_name']=$row['country_name'];
                    $placeDetails[$counter]['state_name']=$row['state_name'];
                    $placeDetails[$counter]['city_name']=$row['city_name'];
                    $placeDetails[$counter]['file_data_type']=$row['file_data_type'];
                    $placeDetailsIndex=array_search($row['tourism_place_id'],$placeIds);
                    if(intval($row['file_data_type'])==\Admin\Model\TourismFiles::file_data_type_places) {
                        $placeDetails[$placeDetailsIndex]['place_files'][] = $row['file_path'];
                    }
                    if(intval($row['file_data_type'])==\Admin\Model\TourismFiles::file_data_type_country) {
                        $placeDetails[$placeDetailsIndex]['country_files'][] = $row['file_path'];
                    }
                    $counter++;
                }

                 if(intval($row['file_data_type'])==\Admin\Model\TourismFiles::file_data_type_places)
                 {
                     $placeDetailsIndex=array_search($row['tourism_place_id'],$placeIds);
                     $placeDetails[$placeDetailsIndex]['place_files'][]=$row['file_path'];
                 }
                   if(intval($row['file_data_type'])==\Admin\Model\TourismFiles::file_data_type_country)
                 {
                     $placeDetailsIndex=array_search($row['tourism_place_id'],$placeIds);
                     $placeDetails[$placeDetailsIndex]['country_files'][]=$row['file_path'];
                 }
                if(intval($row['file_data_type'])==\Admin\Model\TourismFiles::file_data_type_city)
                {
                    $placeDetailsIndex=array_search($row['tourism_place_id'],$placeIds);
                    $placeDetails[$placeDetailsIndex]['city_files'][]=$row['file_path'];
                }

            }
            $bookings['place_details']=$placeDetails;
            return $bookings;
        }catch (\Exception $e)
        {

            return array();
        }

    }
    public function getLanguages($bookingId)
    {
        try{
            $where=new Where();
            $where=$where->equalTo('booking_id',$bookingId)->and->equalTo('tf.file_data_type',\Admin\Model\TourismFiles::file_data_type_places);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array())
                ->join(array('tf'=>'tourism_files'),new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tf`.`file_data_id`,`b`.`place_ids`)"),array(),Select::JOIN_LEFT)
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
    public function userDetailsAdmin($userIds,$bookingId)
    {
        try
        {
            $where=new Where();
            $where=$where->in('u.user_id',$userIds)->and->equalTo('p.booking_id',$bookingId);

            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array('u'=>'users'))
                ->columns(array("mobile","mobile_country_code"))
                ->join(array('p'=>'password'),'u.user_id=p.user_id',array('password','hash','password_expiry_date','password_first_used_date','id'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $bookings = array();
                 $counter=0;
            $aes=new Aes();
            foreach($resultSet as $row){
                $bookings[$counter]['mobile'] = $row['mobile_country_code'].$row['mobile'];
                $password=$aes->decrypt($row['password'],$row['hash']);
                $bookings[$counter]['password'] = $password.$row['id'];
                $bookings[$counter]['password_first_used_date'] = $row['password_first_used_date'];
                $bookings[$counter]['password_expiry_date'] = $row['password_expiry_date'];
                $counter++;
            }

            return $bookings;
        }catch (\Exception $e)
        {
            
            return array();
        }
    }
}