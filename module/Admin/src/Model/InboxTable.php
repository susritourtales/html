<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Predicate;

class InboxTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("i" => "inbox");
    }
    public function addInbox(Array $data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getInboxMessages($data){
        try{
            $sql = $this->getSql();
            $where=new Where();

            $order=array();
             $where=$where->equalTo('i.status',0);

            if(array_key_exists('message',$data) && $data['message']!="")
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(i.message)"),'%'.$data['message']."%");
            }
            if(array_key_exists('received_date',$data)&& $data['received_date']!="")
            {
                $where->and->like(new \Laminas\Db\Sql\Expression("i.created_at"),'%'.$data['received_date']."%");
            }

            if(array_key_exists('user_name_order',$data))
            {
                if($data['user_name_order']==1)
                {
                    $order[]='u.user_name asc ';
                    $order[]='tc.user_name asc ';
                    $order[]='to.contact_person asc ';
                }else if($data['user_name_order']==-1)
                {
                    $order[]='u.user_name desc';
                    $order[]='tc.user_name desc';
                    $order[]='to.contact_person desc';
                }
            }
            if(array_key_exists('received_date_order',$data))
            {
                if($data['received_date_order']==1)
                {
                    $order[]='i.created_at asc ';

                }else if($data['received_date_order']==-1)
                {
                    $order[]='i.created_at desc';

                }
            }
            
            if(array_key_exists('message_order',$data))
            {
                if($data['message_order']==1)
                {
                    $order[]='i.message asc ';

                }else if($data['message_order']==-1)
                {
                    $order[]='i.message desc';

                }
            }
            if(!count($order))
            {
                $order=array('i.created_at desc');
            }
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("inbox_id","message",'created_at'))
                ->join(array('u'=>'users'),'u.user_id=i.user_id',array('role','user_id','user_name'))
                ->join(array('tc'=>'tour_coordinator_details'),new \Laminas\Db\Sql\Expression('tc.user_id=u.user_id and u.role='.\Admin\Model\User::Tour_coordinator_role),array('coordinator_name'=>'user_name'),Select::JOIN_LEFT)
                ->join(array('to'=>'tour_operator_details'),new \Laminas\Db\Sql\Expression('to.user_id=u.user_id and u.role='.\Admin\Model\User::Tour_Operator_role),array('contact_person'),Select::JOIN_LEFT)
                ->where($where)
                ->order($order) ;
            if($data['limit'] )
            {
                $query->limit($data['limit'])
                    ->offset($data['offset']);
            }

            if(array_key_exists('user_name',$data))
            {
                $predicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting
                $predicateSet->orPredicate(new Predicate\Expression('LOWER(u.user_name) like ?', '%'.$data['user_name']."%" ));
                $predicateSet->orPredicate(new Predicate\Expression('LOWER(tc.user_name) like ?', '%'.$data['user_name']."%" ));
                $predicateSet->orPredicate(new Predicate\Expression('LOWER(to.contact_person) like ?', '%'.$data['user_name']."%" ));
                $query->where($predicateSet);
            }

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $inboxList = array();
            $counter=0;
            foreach($resultSet as $row){
                $inboxList[$counter] = $row;
                $role=$row['role'];
                if($role == \Admin\Model\User::Tour_coordinator_role)
                {
                    $inboxList[$counter]['user_name']=$row['coordinator_name'];

                }else if($role==\Admin\Model\User::Tour_Operator_role)
                {
                   $inboxList[$counter]['user_name']=$row['contact_person'];
                }else{
                    $inboxList[$counter]['user_name']=$row['user_name'];

                }
                $counter++;
            }
                   /* echo '<pre>';
            print_r($inboxList);
            exit;*/
            return $inboxList;
        }catch(\Exception $e){
            print_r($e->getMessage());exit;
            return array();
        }
    }
    public function getCoordinatorDetails($userId)
    {
        try{
            $sql = $this->getSql();

            $query=$sql->select()
                ->columns(array('user_name','email'))
                ->from(array('tc'=>'tour_coordinator_details'))
                ->where(array('tc.user_id'=>$userId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
               $userName='';
               foreach ($resultSet as $row)
               {
                   $userName=$row['user_name'];
               }
               return $userName;
        }catch (\Exception $e)
        {
            print_r($e->getMessage());
            exit;
            return '';
        }
    }
    public function getOperatorDetails($userId)
    {
    try{
        $sql = $this->getSql();

        $query=$sql->select()
            ->columns(array('discount_percentage','apply_discount','discount_days','discount_end_date','contact_person','email_id'))
            ->from(array('t'=>'tour_operator_details'))
            ->where(array('t.user_id'=>$userId));
        $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
        $userName='';
        foreach ($resultSet as $row)
        {
            $userName=$row['contact_person'];
        }
        return $userName;
    }catch (\Exception $e)
    {print_r($e->getMessage());
        exit;
        return '';
    }
    }

    public function updateInbox($data,$where)
    {
        try{
            return $this->update($data,$where);
        }catch (\Exception $e)
        {

            return false;
        }

    }
    public function getInboxMessageDetails($data){
        try{
            $sql = $this->getSql();


            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("inbox_id","message",'created_at','reply'))
                ->join(array('u'=>'users'),'u.user_id=i.user_id',array('user_name','role','user_id','email'))
                ->where(array('inbox_id'=>$data['inbox_id']));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $inboxDetails = array();

            foreach($resultSet as $row){
                $inboxDetails = $row;
            }

            $role=$inboxDetails['role'];
            if($role!=\Admin\Model\User::Individual_role) {
                if ($role == \Admin\Model\User::Tour_coordinator_role) {
                    $query = $sql->select()
                        ->columns(array('user_id', 'user_name', 'email'))
                        ->from(array('tc' => 'tour_coordinator_details'))
                        ->join(array('t' => 'tour_operator_details'), 't.tour_operator_id=tc.company_id', array('status'))
                        ->join(array('p' => 'password'), new \Laminas\Db\Sql\Expression('p.user_id=tc.user_id and p.password_type=1 and p.status=1'), array('hash', 'password'))
                        ->where(array('tc.user_id' => $inboxDetails['user_id']));
                } else {
                    $query = $sql->select()
                        ->columns(array('status', 'user_id', 'company_name', 'contact_person', 'email_id'))
                        ->from(array('t' => 'tour_operator_details'))
                        ->join(array('p' => 'password'), new \Laminas\Db\Sql\Expression('p.user_id=t.user_id and p.password_type=1 and p.status=1'), array('hash', 'password'))
                        ->where(array('t.user_id' => $inboxDetails['user_id']));
                }
                $result = $sql->prepareStatementForSqlObject($query)->execute();
                $user=array();
                foreach ($result as $row) {
                      if($role==\Admin\Model\User::Tour_Operator_role)
                      {
                          $inboxDetails['user_name'] = $row['contact_person'];
                          $inboxDetails['email'] = $row['email_id'];
                      }else{
                          $inboxDetails['user_name'] = $row['user_name'];
                          $inboxDetails['email'] = $row['email'];
                      }

                }
            }
            
            return $inboxDetails;
        }catch(\Exception $e)
        {
            return array();
        }
    }
    public function getInboxMessagesCount($data=array()){
        try{
            $sql = $this->getSql();
            $where=new Where();
            $where=$where->equalTo('i.status',0);

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("inbox_id","message",'inboxCount'=>new \Laminas\Db\Sql\Expression('count("inbox_id")')))
                ->join(array('u'=>'users'),'u.user_id=i.user_id',array('role','user_id','user_name'))
                ->join(array('tc'=>'tour_coordinator_details'),new \Laminas\Db\Sql\Expression('tc.user_id=u.user_id and u.role='.\Admin\Model\User::Tour_coordinator_role),array('coordinator_name'=>'user_name'),Select::JOIN_LEFT)
                ->join(array('to'=>'tour_operator_details'),new \Laminas\Db\Sql\Expression('to.user_id=u.user_id and u.role='.\Admin\Model\User::Tour_Operator_role),array('contact_person'),Select::JOIN_LEFT)
                ->where($where)
                ->order('i.created_at desc') ;
            if(array_key_exists('user_name',$data))
            {
                $predicateSet = new Predicate\PredicateSet(array(), Predicate\PredicateSet::COMBINED_BY_OR); // for future nesting
                $predicateSet->orPredicate(new Predicate\Expression('LOWER(u.user_name) like ?', '%'.$data['user_name']."%" ));
                $predicateSet->orPredicate(new Predicate\Expression('LOWER(tc.user_name) like ?', '%'.$data['user_name']."%" ));
                $predicateSet->orPredicate(new Predicate\Expression('LOWER(to.contact_person) like ?', '%'.$data['user_name']."%" ));
                $query->where($predicateSet);
            }

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $inboxList = array();

            foreach($resultSet as $row){
                $inboxList = $row;
            }

            return $inboxList;
        }catch(\Exception $e){

            return array();
        }
    }

}