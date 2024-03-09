<?php

namespace Admin\Model;

use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class TourTalesTable extends  BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("p" => "tour_tale");
    }

    public function addTourTale(array $data)
    {
        try {
            $insert = $this->insert($data);
            if ($insert) {
                return array("success" => true, "id" => $this->tableGateway->lastInsertValue);
            } else {
                return array("success" => false);
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit;
            return array("success" => false);
        }
    }
    public function checkTaleAdded($data)
    {
        try {
            $where = new Where();
            $where = $where->equalTo('p.display', 1)->equalTo('p.tour_type', $data['tour_type']);
            if ($data['tour_type'] == \Admin\Model\TourTales::tour_type_India_tour) {
                $where->equalTo('p.state_id', $data['state_id']);
            } else {
                $where->equalTo('p.country_id', $data['country_id']);
            }
            if (!isset($data['city_id'])) {
                $where->equalTo('p.city_id', 0);
            } else if (isset($data['city_id'])) {
                $where->equalTo('p.city_id', $data['city_id']);
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", 'city_id', 'place_id'))
                ->where($where);
            $field = array();
            /*echo $sql->getSqlStringForSqlObject($query);
            exit;*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row;
            }
            return $field;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getPlacesForPrivilageUser($data)
    {
        try {
            $where = new Where();
            $where->equalTo('p.tour_type', $data['tour_type'])->equalTo('p.status', 1);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "price", 'place_id'));
            if ($data['tour_type'] == \Admin\Model\TourTales::tour_type_City_tour) {
                //   $query=$query->join(array('tp' => 'place'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name' => new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"), 'tourism_place_id'));

            } else {
                $query = $query->join(array('tp' => 'place'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name', 'tourism_place_id'));
            }
            $query = $query->join(array('c' => 'country'), 'p.country_id=c.id', array('country_name'))
                ->join(array('s' => 'state'), 'p.state_id=s.id', array('state_name'), Select::JOIN_LEFT)
                ->join(array('ci' => 'city'), 'p.city_id=ci.id', array('city_name'))
                ->where($where);
            if ($data['tour_type'] == \Admin\Model\TourTales::tour_type_City_tour) {
                $query = $query->group('p.id');
            }
            $query = $query->order(array('p.updated_at desc'));



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
    public function getPlacesList($data = array('limit' => 10, 'offset' => 0), $gc = 0)
    {
        try {
            $where = new Where();
            if ($data['tour_type'] == \Admin\Model\TourTales::tour_type_Free_tour) {
                $where->equalTo('p.display', 1)->and->equalTo('free', '1');
            } else {
                $where->equalTo('p.display', 1)->and->equalTo('tour_type', $data['tour_type']);
            }
            $order = array();
            if (array_key_exists('country', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"), '%' . strtolower($data['country']) . "%");
            }
            if (array_key_exists('state', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"), '%' . strtolower($data['state']) . "%");
            }
            if (array_key_exists('city', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ci.city_name)"), '%' . strtolower($data['city']) . "%");
            }
            if (array_key_exists('place_name', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"), '%' . strtolower($data['place_name']) . "%");
            }

            if (array_key_exists('country_order', $data)) {
                if ($data['country_order'] == 1) {
                    $order[] = 'c.country_name asc';
                } else if ($data['country_order'] == -1) {
                    $order[] = 'c.country_name desc';
                }
            }
            if (array_key_exists('state_order', $data)) {
                if ($data['state_order'] == 1) {
                    $order[] = 's.state_name asc';
                } else if ($data['state_order'] == -1) {
                    $order[] = 's.state_name desc';
                }
            }
            if (array_key_exists('city_order', $data)) {
                if ($data['city_order'] == 1) {
                    $order[] = 'ci.city_name asc';
                } else if ($data['city_order'] == -1) {
                    $order[] = 'ci.city_name desc';
                }
            }
            if (array_key_exists('place_name_order', $data)) {
                if ($data['place_name_order'] == 1) {
                    $order[] = 'tp.place_name asc';
                } else if ($data['place_name_order'] == -1) {
                    $order[] = 'tp.place_name desc';
                }
            }
            if (!count($order)) {
                $order = array('p.updated_at desc');
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id"));
            if ($data['tour_type'] == \Admin\Model\TourTales::tour_type_Bunched_tour) {
                /* $query = $query->join(array('tp' => 'place'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`id`,`p`.`place_id`)"), array('place_name' => new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"), 'place_id' => 'id'), Select::JOIN_LEFT); */
                $query = $query->join(array('tp' => 'place'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`id`,`p`.`place_id`)"), array('place_name' => new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"), 'place_id' => new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`id`)")), Select::JOIN_LEFT);
            } else {
                $query = $query->join(array('tp' => 'place'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`id`,`p`.`place_id`)"), array('place_name' => 'place_name', 'place_id' => 'id'), Select::JOIN_LEFT);
            }
            $query = $query->join(array('c' => 'country'), 'p.country_id=c.id', array('country_name'), Select::JOIN_LEFT)
                ->join(array('s' => 'state'), 'p.state_id=s.id', array('state_name'), Select::JOIN_LEFT)
                ->join(array('ci' => 'city'), 'p.city_id=ci.id', array('city_name'), Select::JOIN_LEFT)
                ->where($where);
            if ($data['tour_type'] == \Admin\Model\TourTales::tour_type_Bunched_tour) {
                $query = $query->group('p.id');
            }
            $query = $query->order($order);
            if ($data['limit'] != -1) {
                if ($gc == 0) {
                    $query->limit($data['limit'])
                        ->offset($data['offset']);
                }
            }
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            if ($gc == 1)
                return count($resultSet);
            $countries = array();
            foreach ($resultSet as $row) {
                $countries[] = $row;
            }
            return $countries;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit;
            return array();
        }
    }
    public function getPlacesListCount($data = array())
    {
        try {
            $where = new Where();
            $where->and->equalTo('p.status', 1)->and->equalTo('tour_type', $data['tour_type']);
            $order = array();
            if (array_key_exists('country', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"), '%' . strtolower($data['country']) . "%");
            }
            if (array_key_exists('state', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"), '%' . strtolower($data['state']) . "%");
            }
            if (array_key_exists('city', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(ci.city_name)"), '%' . strtolower($data['city']) . "%");
            }
            if (array_key_exists('place_name', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(tp.place_name)"), '%' . strtolower($data['place_name']) . "%");
            }

            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "price"));
            if ($data['tour_type'] == \Admin\Model\TourTales::tour_type_City_tour) {
                $query = $query->join(array('tp' => 'place'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name' => new \Laminas\Db\Sql\Expression("GROUP_CONCAT(`tp`.`place_name`)"), 'tourism_place_id'), Select::JOIN_LEFT);
            } else {
                $query = $query->join(array('tp' => 'place'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array('place_name', 'tourism_place_id'), Select::JOIN_LEFT);
            }
            $query = $query->join(array('c' => 'country'), 'tp.country_id=c.id', array('country_name'))
                ->join(array('s' => 'state'), 'tp.state_id=s.id', array('state_name'), Select::JOIN_LEFT)
                ->join(array('ci' => 'city'), 'tp.city_id=ci.id', array('city_name'))
                ->where($where);
            if ($data['tour_type'] == \Admin\Model\TourTales::tour_type_City_tour) {
                $query = $query->group('p.id');
            }


            $query = $query->order('p.updated_at desc');
            /*echo $sql->getSqlStringForSqlObject($query);
                        exit;*/

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
    public function getCityTourPrice($cityIds)
    {
        try {
            $sql = $this->getSql();
            $where = new Where();

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('price' => new \Laminas\Db\Sql\Expression('sum(`p`.`price`)')))
                ->where($where->in('city_id', $cityIds));

            $field = array();
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $field = $row['price'];
            }

            if ($field) {
                return $field;
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            return 0;
        }
    }
    public function getTourTales($where)
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", 'country_id', 'city_id', "place_id"))
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $placePrices[] = $row;
            }
            return $placePrices;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit;
            return array();
        }
    }
    public function updateTourTale($data, $where)
    {
        try {
            return $this->update($data, $where);
        } catch (\Exception $e) {
            return false;
        }
    }
    public function deleteTourTale($deleteIds)
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $query = $sql->update('tour_tale')->set(array('display' => 0, 'updated_at' => date("Y-m-d H:i:s")))->where($where->like('place_id', "%," . $deleteIds . ",%"));
            /* echo $sql->getSqlStringForSqlObject($query);
             exit;*/
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function deletePlace($placeId, $taleId)
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $query = $sql->update('tour_tale')->set(array('place_id' => new \Laminas\Db\Sql\Expression("REPLACE(`place_id`,'" . ',' . $placeId . ',' . "', ',')"), 'updated_at' => date("Y-m-d H:i:s")))->where($where->equalTo('id', $taleId));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            return true;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit;
            return false;
        }
    }
}
