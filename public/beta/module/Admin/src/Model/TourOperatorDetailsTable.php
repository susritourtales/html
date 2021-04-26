<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\TableGateway;

class TourOperatorDetailsTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("to" => "tour_operator_details");
    }

    public function addTourOperator(Array $data)
    {
        try
        {

            $insert = $this->insert($data);
            if($insert){
                return array("success" => true,"id" => $this->tableGateway->lastInsertValue);
            }else
                {
                return array("success" => false);
            }
        }catch(\Exception $e){
                   print_r($e->getMessage());
                   exit;
            return array("success" => false);
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
    public function getTourOperatorList($data=array('limit'=>10,'offset'=>0))
    {
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where=$where->notEqualTo('to.status',0);
            if(array_key_exists('tour_operator_name',$data))
            {
                $where->and->like("to.company_name",'%'.$data['tour_operator_name']."%");
            }
            if(array_key_exists('mobile_number',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile_number']."%");
            }
            if(array_key_exists('email',$data))
            {
                $where->and->like("to.email_id",'%'.$data['email']."%");
            }
            if(array_key_exists('contact_person',$data))
            {
                $where->and->like("to.contact_person",'%'.$data['contact_person']."%");
            }

            $order=array();
            if(array_key_exists('tour_operator_name_order',$data))
            {
                if($data['tour_operator_name_order']==1)
                {
                    $order[]='to.company_name asc';
                }else if($data['tour_operator_name_order']==-1)
                {
                    $order[]='to.company_name desc';
                }
            }

            if(array_key_exists('mobile_number_order',$data))
            {
                if($data['mobile_number_order']==1)
                {
                    $order[]='u.mobile asc';
                }else if($data['mobile_number_order']==-1)
                {
                    $order[]='u.mobile desc';
                }
            }

            if(array_key_exists('email_order',$data))
            {
                if($data['email_order']==1)
                {
                    $order[]='to.email_id asc';
                }else if($data['email_order']==-1)
                {
                    $order[]='to.email_id desc';
                }
            }
            if(!count($order))
            {
                $order=array('to.updated_at desc');
            }

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tour_operator_id","company_name",'email_id','pan_number','registration_certificate','contact_person','status'))
                ->join(array('u'=>'users'),new \Zend\Db\Sql\Expression('u.user_id = to.user_id'),array('mobile','mobile_country_code'))
                ->join(array('tc'=>'tour_coordinator_details'),new \Zend\Db\Sql\Expression('tc.company_id =to.tour_operator_id and tc.coordinator_order = 1'),array('user_name','designation'))
                ->where($where)
                ->group('to.tour_operator_id')
            ->limit($data['limit'])
            ->offset($data['offset'])
            ->order($order);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourOperator = array();

            foreach($resultSet as $row){
                $tourOperator[] = $row;
            }

            return $tourOperator;
        }catch(\Exception $e){

            return array();
        }
    } public function getTourOperatorListCount($data=array('limit'=>10,'offset'=>0))
    {
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where=$where->notEqualTo('to.status',0);
            if(array_key_exists('tour_operator_name',$data))
            {
                $where->and->like("to.company_name",'%'.$data['tour_operator_name']."%");
            }
            if(array_key_exists('mobile_number',$data))
            {
                $where->and->like("u.mobile",'%'.$data['mobile_number']."%");
            }
            if(array_key_exists('email',$data))
            {
                $where->and->like("to.email_id",'%'.$data['email']."%");
            }
            if(array_key_exists('contact_person',$data))
            {
                $where->and->like("to.contact_person",'%'.$data['contact_person']."%");
            }

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("tour_operator_id","company_name",'email_id','pan_number','registration_certificate','contact_person'))
                ->join(array('u'=>'users'),new \Zend\Db\Sql\Expression('u.user_id = to.user_id'),array('mobile','mobile_country_code'))
                ->join(array('tc'=>'tour_coordinator_details'),new \Zend\Db\Sql\Expression('tc.company_id =to.tour_operator_id and tc.coordinator_order = 1'),array('user_name','designation'))
                ->group('to.tour_operator_id')
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourOperator = array();

            foreach($resultSet as $row){
                $tourOperator[] = $row;
            }

            return $tourOperator;
        }catch(\Exception $e){
            return array();
        }
    }
    public function updateTourOperator($data,$where)
    {
        try
        {

            return $this->update($data,$where);
        }catch (\Exception $e)
        {


            return false;
        }
    }
    public function tourOperatorDetails($tourOperatorId)
    {
        try
        {
            $sql = $this->getSql();
            $where=new Where();
            $where=$where->equalTo('to.tour_operator_id',$tourOperatorId);

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('state','registration_date',"tour_operator_id","company_name",'email_id','pan_number','registration_certificate','contact_person','status','registration_certificate','discount_percentage','discount_days','discount_end_date','apply_discount','additional_details'))
                ->join(array('u'=>'users'),new \Zend\Db\Sql\Expression('u.user_id = to.user_id'),array('mobile','mobile_country_code'))
                ->join(array('tc'=>'tour_coordinator_details'),new \Zend\Db\Sql\Expression('tc.company_id =to.tour_operator_id'),array('user_name','designation','coordinator_order','certificate_date','coordinator_email'=>'email','coordinator_certificate'))
                ->join(array('cu'=>'users'),new \Zend\Db\Sql\Expression('cu.user_id = tc.user_id'),array('coordinator_mobile'=>'mobile','coordinator_country_code'=>'mobile_country_code'))

                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $tourOperator = array();

            foreach($resultSet as $row){
                $tourOperator[] = $row;
            }

            return $tourOperator;
        }catch(\Exception $e){
           /* print_r($e->getMessage());
            exit;*/
            return array();
        }
    }

}