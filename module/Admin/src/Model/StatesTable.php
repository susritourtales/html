<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class StatesTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("s" => "state");
    }

    public function addState(array $data)
    {
        try {
            $insert = $this->insert($data);
            if ($insert) {
                return array("success" => true, "id" => $this->tableGateway->lastInsertValue);
            } else {
                return array("success" => false);
            }
        } catch (\Exception $e) {
            /* print_r($e->getMessage());
            exit; */
            return array("success" => false);
        }
    }
    public function getStates($where)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "state_name", 'country_id'))
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $states = array();
            foreach ($resultSet as $row) {
                $states[] = $row;
            }
            return $states;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getActiveStatesListUser($data = array('limit' => 10, 'offset' => 0))
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('s.display', 1);
            if (array_key_exists('search', $data) && $data['search'] != '') {
                $where->and->like('s.state_name', "%" . $data['search'] . "%");
            }
            $places = $sql->select()
                ->from(array('tp' => 'tourism_places'))
                ->columns(array("state_id"))
                ->join(array('p' => 'place_prices'), new \Laminas\Db\Sql\Expression("p.state_id =tp.state_id and  p.display =1"), array("tour_type", 'countstate' => new \Laminas\Db\Sql\Expression("SUM(CASE WHEN `place_id` IS NULL || `place_id`='' THEN 0 ELSE 1 END)")))
                ->join(array('tf' => 'tourism_files'), new \Laminas\Db\Sql\Expression("tf.tourism_file_id =tp.tourism_place_id and  tf.display =1 and tf.file_extension_type=3"), array('filesCount' => new \Laminas\Db\Sql\Expression("SUM(CASE WHEN `place_id` IS NULL || `place_id`='' THEN 0 ELSE 1 END)")))
                ->where(array('tp.display' => 1, 'p.tour_type' => $data['tour_type']))
                ->group('tp.state_id');
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'file_data_id'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_state, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('state_id' => "id", "state_name"))
                ->join(array('c' => 'countries'), 'c.id=s.country_id', array('country_name'))
                ->join(array('tpl' => $places), 'tpl.state_id = s.id', array('countstate', 'filesCount'))
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = s.id', array('file_path'))
                ->where($where)
                ->order('s.state_name asc');
            if ($data['limit'] != -1) {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            }
            //   echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            $counter = -1;
            foreach ($resultSet as $row) {
                $counter++;
                $countries[$counter]['state_id'] = $row['state_id'];
                $countries[$counter]['state_name'] =  ucfirst($row['state_name']);
                $countries[$counter]['file_path'] = $row['file_path'];
                $countries[$counter]['files_count'] = $row['filesCount'];
                if ($row['countstate'] == 0) {
                    $countries[$counter]['up_coming'] = 1;
                } else {
                    $countries[$counter]['up_coming'] = 2;
                }
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }

    public function getActiveStatesList($data = array('limit' => 10, 'offset' => 0))
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('s.display', 1)->and->equalTo('s.country_id', $data['country_id']);
            if (array_key_exists('search', $data) && $data['search'] != '') {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"), new \Laminas\Db\Sql\Expression('LOWER("%' . $data['search'] . '%")'));
                //$where->and->like('s.state_name',"%".$data['search']."%");
            }
            $places = $sql->select()
                ->from(array('tp' => 'tourism_places'))
                ->columns(array("state_id"))
                ->join(array('p' => 'place_prices'), new \Laminas\Db\Sql\Expression("p.state_id =tp.state_id and  p.display =1"), array("tour_type", 'countstate' => new \Laminas\Db\Sql\Expression("SUM(CASE WHEN `place_id` IS NULL || `place_id`='' THEN 0 ELSE 1 END)")))
                ->where(array('tp.display' => 1, 'p.tour_type' => $data['tour_type']))
                ->group('tp.state_id');
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'file_data_id'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_state, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('state_id' => "id", "state_name"))
                ->join(array('tpl' => $places), 'tpl.state_id = s.id', array('countstate'))
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = s.id', array('file_path'))
                ->where($where)
                ->order('s.state_name asc')
                ->offset($data['offset'])
                ->limit($data['limit']);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            $counter = -1;
            foreach ($resultSet as $row) {
                $counter++;
                $countries[$counter]['state_id'] = $row['state_id'];
                $countries[$counter]['state_name'] =  $row['state_name'];
                $countries[$counter]['file_path'] = $row['file_path'];
                if ($row['countstate'] == 0) {
                    $countries[$counter]['up_coming'] = 1;
                } else {
                    $countries[$counter]['up_coming'] = 2;
                }
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getActiveStatesListAdmin($data = array())
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('s.display', 1)->and->equalTo('s.country_id', $data['country_id']);
            if (array_key_exists('search', $data) && $data['search'] != '') {
                $where->and->like('s.state_name', "%" . $data['search'] . "%");
            }
            $placesWhere = new Where();
            $prices = $sql->select()
                ->from(array('p' => 'place_prices'))
                ->columns(array('place_id'))
                ->where(array('p.tour_type' => $data['tour_type'], 'p.display' => 1));
            $placesWhere->equalTo('tp.display', 1)->and->notIn('tp.tourism_place_id', $prices);
            $places = $sql->select()
                ->from(array('tp' => 'tourism_places'))
                ->columns(array("state_id"))
                ->where($placesWhere)
                ->group(array('tp.state_id'));
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'file_data_id'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_state, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "state_name"))
                ->join(array('tpl' => $places), 'tpl.state_id = s.id', array())
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = s.id', array('file_path'))
                ->where($where)
                ->order('s.state_name asc');
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();

            foreach ($resultSet as $row) {
                $countries[] = $row;
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getField($where, $column)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array("s" => "states"))
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
    public function getActiveStates()
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "state_name"))
                ->where(array('status' => 1));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            foreach ($resultSet as $row) {
                $countries[] = $row;
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getStateList($data = array('limit' => 10, 'offset' => 0), $gc = 0)
    {
        try {
            $where = new Where();
            $where->equalTo('display', 1);
            if (array_key_exists('state', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"), '%' . $data['state'] . "%");
            }
            $order = array();
            if (array_key_exists('state_order', $data)) {
                if ($data['state_order'] == 1) {
                    $order[] = 's.state_name asc';
                } else if ($data['state_order'] == -1) {
                    $order[] = 's.state_name desc';
                }
            }
            if (!count($order)) {
                $order = array('state_name asc');
            }
            $sql = $this->getSql();
            if ($gc == 1) {
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("id", "state_name"))
                    ->where($where);
            } else {
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("id", "state_name"))
                    ->where($where)
                    ->order($order)
                    ->limit($data['limit'])
                    ->offset($data['offset']);
            }
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            if ($gc == 1)
                return count($resultSet);
            $states = array();
            foreach ($resultSet as $row) {
                $states[] = $row;
            }
            return $states;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function updateState($data, $where)
    {
        try {
            return $this->update($data, $where);
        } catch (\Exception $e) {
            return false;
        }
    }
    public function getStatesCount($data = array())
    {
        try {
            $where = new Where();
            $where->equalTo('status', 1);
            if (array_key_exists('state', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"), '%' . $data['state'] . "%");
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "state_name"))
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            foreach ($resultSet as $row) {
                $countries[] = $row;
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }

    public function getActiveIndianStates()
    {
        try {
            $sql = $this->getSql();
            $countryTable = $sql->select()
                ->from(array('co' => 'country'))
                ->columns(array('id', 'country_name'))
                ->where(array('co.display' => 1, 'co.country_name' => 'india'));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "state_name"))
                ->join(array('c' => $countryTable), 's.country_id=c.id', array('country_name'))
                ->where(array('s.display' => 1))
                ->order('s.state_name asc');
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            foreach ($resultSet as $row) {
                $countries[] = $row;
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getIndiaStates($data)
    {
        try {
            $sql = $this->getSql();
            $countryTable = $sql->select()
                ->from(array('co' => 'countries'))
                ->columns(array('id', 'country_name'))
                ->where(array('co.display' => 1, 'co.country_name' => 'india'));
            $where = new Where();
            $where->equalTo('s.display', 1);
            if (array_key_exists('search', $data) && $data['search'] != '') {
                $where->and->like('s.state_name', "%" . $data['search'] . "%");
            }
            $placesWhere = new Where();
            $prices = $sql->select()
                ->from(array('p' => 'place_prices'))
                ->columns(array('place_id'))
                ->where(array('p.tour_type' => $data['tour_type'], 'p.display' => 1));
            $placesWhere->equalTo('tp.display', 1)->and->notIn('tp.tourism_place_id', $prices);
            $places = $sql->select()
                ->from(array('tp' => 'tourism_places'))
                ->columns(array("state_id"))
                ->where($placesWhere)
                ->group(array('tp.state_id'));
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'file_data_id'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_state, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "state_name"))
                ->join(array('c' => $countryTable), 's.country_id=c.id', array('country_name'))
                ->join(array('tpl' => $places), 'tpl.state_id = s.id', array())
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = s.id', array('file_path'))
                ->where($where)
                ->order('s.state_name asc');
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            foreach ($resultSet as $row) {
                $countries[] = $row;
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getStatesByMaxId($maxId)
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "state_name", 'country_id'))
                ->where($where->greaterThan("id", $maxId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $states = array();
            foreach ($resultSet as $row) {
                $states[] = $row;
            }
            return $states;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getStateDetails($stateId)
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('s.display', 1)->equalTo('s.id', $stateId);
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'tourism_file_id', 'file_data_id', 'file_extension_type', 'file_language_id', 'file_name'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_state));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('state_id' => "id", "state_name", "state_description"))
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = s.id', array('file_path', 'tourism_file_id', 'file_extension_type', 'file_language_id', 'file_name'), Select::JOIN_LEFT)
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $states = array();
            foreach ($resultSet as $row) {
                $states[] = $row;
            }
            return $states;
        } catch (\Exception $e) {
            return array();
        }
    }

    public function getStates4App($data = array('limit' => 10, 'offset' => 0), $gc = 0){
        try {
            $where = new Where();
            $where->equalTo('display', 1);
            $order = array('state_name asc');
        
            $sql = $this->getSql();
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'tourism_file_id', 'file_data_id', 'file_extension_type', 'file_language_id', 'file_name'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_state, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "name" => "state_name"))
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = s.id', array('file_path'), Select::JOIN_LEFT)
                ->where($where)
                ->order($order);
            if ($gc == 0) {
                $query->limit($data['limit'])
                        ->offset($data['offset']);
            }
            // echo $sql->getSqlStringForSqlObject($query);exit;
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            if ($gc == 1)
                return count($resultSet);
            $states = array();
            foreach ($resultSet as $row) {
                $states[] = $row;
            }
            return $states;
        } catch (\Exception $e) {
            return array();
        }
    }

    public function statesList($stateId)
    {
        try {
            $where = new Where();
            $where->in('id', $stateId);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('state_id' => "id", "state_name", 'country_id', 'state_description'))
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $states = array();
            $counter = -1;
            foreach ($resultSet as $row) {
                $counter++;
                $states[$counter]['state_id'] = $row['state_id'];
                $states[$counter]['state_name'] = $row['state_name'];
                $states[$counter]['country_id'] = $row['country_id'];
                $states[$counter]['state_description'] = $row['state_description'];
            }
            return $states;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getStateId($stateName)
    {
        try {
            $sql = $this->getSql();
            $values = array();
            $where = new Where();
            $query = $sql->select()
                ->columns(array('id'))
                ->from(array("s" => "states"))
                ->where($where->equalTo("state_name", $stateName));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $values = $row['id'];
            }
            return $values;
        } catch (\Exception $e) {
            return '';
        }
    }
}
