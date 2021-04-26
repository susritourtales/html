<?php

namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class PaymentsTable extends BaseTable
{

    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("p" => "payments");
    }

    public function addPayment(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {

            return false;
        }
    }

    public function getPayment($where)
    {
        try{
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("payment_response_id","payment_id",'booking_id','status')) // 'status' - added by Manjary
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $payments = array();

            foreach($resultSet as $row){
                $payments[] = $row;
            }

            return $payments;
        }catch(\Exception $e){
            print_r($e->getMessage());
            exit;
            return array();
        }
    }
    public function getFinancialStatements($data)
    {
        try
        {
            $sql = $this->getSql();
            $where=new Where();
            $where=$where->equalTo('p.status',1);
            $order=array();
            /* if($data['month'])
            {
                $where->and->equalTo(new \Laminas\Db\Sql\Expression('month(p.created_at)'), $data['month']);
                $order[]='p.created_at asc';
            }
            if($data['year'])
            {
                $where->and->equalTo(new \Laminas\Db\Sql\Expression('year(p.created_at)'), $data['year']);
                $order[]='p.created_at asc';
            } */

            if($data['start_date'])
            {
                $where->and->greaterThan('p.created_at', date('Y-m-d', strtotime($data['start_date'] . ' - 1 days')));
                $order[]='p.created_at asc';
            }
            if($data['end_date'])
            {
                $where->and->lessThan('p.created_at', date('Y-m-d', strtotime($data['end_date'] . ' + 1 days')));
                $order[]='p.created_at asc';
            }
            
            $order=array('p.created_at asc');
            /* $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("payment_id",'booking_id','created_at'))
                ->join(array('b'=>'booking_tour_details'),'b.booking_id=p.booking_id',array('amount'=>'price'))
                ->where($where)
                ->group(array('p.payment_id'))
                ->order($order); */
            if($data['optType'] == 'i')
            {
                $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("payment_id",'booking_id','currency','created_at'))
                ->join(array('b'=>'booking_tour_details'),'b.booking_id=p.booking_id',array('amount'=>'price'))
                ->join(array('bk'=>'bookings'),'bk.booking_id=p.booking_id',array('user_id'=>'user_id'))
                ->join(array('u'=>'users'),new \Laminas\Db\Sql\Expression("bk.user_id=u.user_id and u.mobile_country_code='91'"),array('cc'=>'mobile_country_code'))
                ->where($where)
                ->group(array('p.payment_id'))
                ->order($order);
            }
            else
            {
                $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("payment_id",'booking_id','currency','created_at'))
                ->join(array('b'=>'booking_tour_details'),'b.booking_id=p.booking_id',array('amount'=>'price'))
                ->join(array('bk'=>'bookings'),'bk.booking_id=p.booking_id',array('user_id'=>'user_id'))
                ->join(array('u'=>'users'),new \Laminas\Db\Sql\Expression("bk.user_id=u.user_id and u.mobile_country_code<>'91'"),array('cc'=>'mobile_country_code'))
                ->where($where)
                ->group(array('p.payment_id'))
                ->order($order);
            }
//('amount'=>new \Laminas\Db\Sql\Expression('IF((discount_percentage=0),b.price,b.discount_percentage)'))
            if($data['limit'])
            {
                $query->limit($data['limit'])
                ->offset($data['offset']);
            }
            //  echo $sql->getSqlStringForSqlObject($query) . "\n"; exit;

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $payments = array();
            $pqry = $sql->select()
                    ->from(array("pt" => "pricing"))
                    ->columns(array('GST'))
                    ->where(array('plantype'=>'0'));
            $GST = 0;
            $gstRS = $sql->prepareStatementForSqlObject($pqry)->execute();
            foreach ($gstRS as $grs) {
                $GST = $grs['GST'];
            }
            foreach($resultSet as $row)
            {
                $row['GST'] = $GST;
                $payments[] = $row;
            }
            return $payments;
        }catch(\Exception $e)
        {
            print_r($e->getMessage());exit;
            return array();
        }
    }

    public function getFinancialStatementsCount($data=array()){
        try
        {
            $sql = $this->getSql();
            $where=new Where();
            $where=$where->equalTo('p.status',1);
            $order=array();
            if($data['month'])
            {
                $where->and->equalTo(new \Laminas\Db\Sql\Expression('month(p.created_at)'), $data['month']);
                $order[]='p.created_at asc';
            }
            if($data['year'])
            {
                $where->and->equalTo(new \Laminas\Db\Sql\Expression('year(p.created_at)'), $data['year']);
                $order[]='p.created_at asc';
            }

            $order=array('p.created_at asc');
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("payment_id",'booking_id','created_at'))
                ->join(array('b'=>'booking_tour_details'),'b.booking_id=p.booking_id',array('amount'=>'price'))
                ->where($where)
                ->group(array('p.payment_id'))
                ->order($order);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $payments = array();
            foreach($resultSet as $row)
            {
                $payments[] = $row;
            }
            return $payments;
        }catch(\Exception $e)
        {
            print_r($e->getMessage());exit;
            return array();
        }
    }

    public function getPayments($data)
    {
        try
        {
            $sql = $this->getSql();
            $where=new Where();
            $where=$where->equalTo('p.status',1);
            if(array_key_exists('booking_id',$data))
            {
                $where->and->like("p.booking_id",'%'.$data['booking_id']."%");
            }

            if(array_key_exists('transaction_id',$data))
            {
                $where->and->like("p.payment_request_id",'%'.$data['transaction_id']."%");
            }
            if(array_key_exists('transaction_date',$data))
            {
                $where->and->like("p.created_at",'%'.$data['transaction_date']."%");
            }
            if(array_key_exists('transaction_id',$data))
            {
                $where->and->like("p.payment_request_id",'%'.$data['transaction_id']."%");
            }
            $order=array();
            if(array_key_exists('booking_id_order',$data))
            {
                if($data['booking_id_order']==1)
                {
                    $order[]='p.booking_id asc';
                }else if($data['booking_id_order']==-1)
                {
                    $order[]='p.booking_id desc';
                }
            }

            if(array_key_exists('transaction_id_order',$data))
            {
                if($data['transaction_id_order']==1)
                {
                    $order[]='p.payment_request_id asc';

                }else if($data['transaction_id_order']==-1)
                {
                    $order[]='p.payment_request_id desc';
                }
            }
            if(array_key_exists('transaction_date_order',$data))
            {
                if($data['transaction_date_order']==1)
                {
                    $order[]='p.created_at asc';

                }else if($data['transaction_date_order']==-1)
                {
                    $order[]='p.created_at desc';
                }
            }
              if(!count($order))
              {
                  $order=array('p.created_at desc');
              }

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("payment_request_id","payment_response_id",'payment_id',"payment_id",'booking_id','currency', 'created_at'))
                ->join(array('b'=>'booking_tour_details'),'b.booking_id=p.booking_id',array('amount'=>'price'))
                ->where($where)
                ->group(array('p.payment_id'))
                ->order($order);
//'amount'=>new \Laminas\Db\Sql\Expression('IF((discount_percentage=0),b.price,b.discount_percentage)')
            if($data['limit'])
            {
                $query->limit($data['limit'])
                ->offset($data['offset']);
            }
            // echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $payments = array();
            $pqry = $sql->select()
                    ->from(array("pt" => "pricing"))
                    ->columns(array('GST'))
                    ->where(array('plantype'=>'0'));
            $GST = 0;
            $gstRS = $sql->prepareStatementForSqlObject($pqry)->execute();
            foreach ($gstRS as $grs) {
                $GST = $grs['GST'];
            }
            foreach($resultSet as $row)
            {
                $row['GST'] = $GST;
                $payments[] = $row;
            }
            return $payments;
        }catch(\Exception $e)
        {
            print_r($e->getMessage());exit;
            return array();
        }
    }
    public function getPaymentCount($data=array())
    {
        try{
            $sql = $this->getSql();
            $where=new Where();

            $where=$where->equalTo('p.status',1);
            if(array_key_exists('booking_id',$data))
            {
                $where->and->like("p.booking_id",'%'.$data['booking_id']."%");
            }

            if(array_key_exists('transaction_id',$data))
            {
                $where->and->like("p.payment_request_id",'%'.$data['transaction_id']."%");
            }
            if(array_key_exists('transaction_date',$data))
            {
                $where->and->like("p.created_at",'%'.$data['transaction_date']."%");
            }
            if(array_key_exists('transaction_id',$data))
            {
                $where->and->like("p.payment_request_id",'%'.$data['transaction_id']."%");
            }
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("payment_request_id","payment_response_id",'payment_id',"payment_id",'booking_id','created_at'))
                ->join(array('b'=>'booking_tour_details'),'b.booking_id=p.booking_id',array('amount'=>'price'))
                ->where($where)
                ->group(array('p.payment_id'));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            return count($resultSet);


            /* $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("payment_request_id","payment_response_id",'payment_id',"payment_id",'booking_id','created_at','count'=>new \Laminas\Db\Sql\Expression('count("payment_response_id")')))
                ->join(array('b'=>'booking_tour_details'),'b.booking_id=p.booking_id',array('amount'=>'price'))
                ->where($where)
                ->group(array('p.payment_id')); */
            
            //echo $sql->getSqlStringForSqlObject($query);exit;
            
//'amount'=>new \Laminas\Db\Sql\Expression('IF((discount_percentage=0),b.price,b.discount_percentage)')
            /* 
            $payments = array();
            foreach($resultSet as $row){
                $payments = $row;
            } 
            return $payments;*/
            
        }catch(\Exception $e){
                  print_r($e->getMessage());
                  exit;
            return array();
        }
    }
    public function updatePayment($data,$where)
    {
        try{

            return $this->update($data,$where);
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return false;
        }
    }
}