<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class CitiesTable extends BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("c" => "city");
    }

    public function addCity(array $data)
    {
        try {
            $insert = $this->insert($data);
            if ($insert) {
                return array("success" => true, "id" => $this->tableGateway->lastInsertValue);
            } else {
                return array("success" => false);
            }
        } catch (\Exception $e) {
            return array("success" => false);
        }
    }
    public function updateCity($data, $where)
    {
        try {
            return $this->update($data, $where);
        } catch (\Exception $e) {
            return false;
        }
    }
    public function getCityList($data = array('limit' => 10, 'offset' => 0), $gc = 0)
    {
        try {
            $where = new Where();
            $where->equalTo('c.display', 1)->equalTo('co.display', 1);

            if (array_key_exists('country', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(co.country_name)"), '%' . $data['country'] . "%");
            }
            if (array_key_exists('state', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"), '%' . $data['state'] . "%");
            }

            if (array_key_exists('city', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.city_name)"), '%' . $data['city'] . "%");
            }
            $order = array();
            if (array_key_exists('country_order', $data)) {
                if ($data['country_order'] == 1) {
                    $order[] = 'co.country_name asc';
                } else if ($data['country_order'] == -1) {
                    $order[] = 'co.country_name desc';
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
                    $order[] = 'c.city_name asc';
                } else if ($data['city_order'] == -1) {
                    $order[] = 'c.city_name desc';
                }
            }
            if (!count($order)) {
                $order = array('co.country_name asc', 's.state_name asc', 'c.city_name asc');
            }
            $sql = $this->getSql();
            if ($gc == 1) {
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("id", "city_name"))
                    ->join(array('co' => 'country'), 'co.id=c.country_id', array('country_name'))
                    ->join(array('s' => 'state'), 's.id=c.state_id', array('state_name'), Select::JOIN_LEFT)
                    ->where($where)
                    ->order($order);
            } else {
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("id", "city_name"))
                    ->join(array('co' => 'country'), 'co.id=c.country_id', array('country_name'))
                    ->join(array('s' => 'state'), 's.id=c.state_id', array('state_name'), Select::JOIN_LEFT)
                    ->where($where)
                    ->order($order)
                    ->limit($data['limit'])
                    ->offset($data['offset']);
            }
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            if ($gc == 1)
                return count($resultSet);
            $cities = array();
            foreach ($resultSet as $row) {
                $cities[] = $row;
            }
            return $cities;
        } catch (\Exception $e) {
            return array();
        }
    }

    /* public function getCityCount($data = array()) //  can be replaced with the above function using $gc
    {
        try {
            $where = new Where();
            $where->equalTo('c.display', 1);

            if (array_key_exists('country', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(co.country_name)"), '%' . $data['country'] . "%");
            }

            if (array_key_exists('state', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(s.state_name)"), '%' . $data['state'] . "%");
            }

            if (array_key_exists('city', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.city_name)"), '%' . $data['city'] . "%");
            }

            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "city_name"))
                ->join(array('co' => 'country'), 'co.id=c.country_id', array('country_name'))
                ->join(array('s' => 'state'), 's.id=c.state_id', array('state_name'), Select::JOIN_LEFT)
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
    } */
    public function getCities($where)
    {
        try {
            $where['display'] = 1;
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "city_name", 'state_id', 'country_id'))
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $cities = array();
            foreach ($resultSet as $row) {
                $cities[] = $row;
            }
            return $cities;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getActiveCitiesList($data = array('limit' => 10, 'offset' => 0))
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('c.display', 1);
            if (array_key_exists('search', $data) && $data['search'] != '') {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.city_name)"), new \Laminas\Db\Sql\Expression('LOWER("%' . $data['search'] . '%")'));
                // $where->and->like('c.city_name',"%".$data['search']."%");
            }
            if (array_key_exists('state_id', $data) && $data['state_id'] != '') {
                $where->and->equalTo('c.state_id', $data['state_id']);
            } else if ($data['country_id'] != '') {
                $where->and->equalTo('c.country_id', $data['country_id']);
            }

            $fileLanguageWhere = new Where();
            $places = $sql->select()
                ->from(array('tp' => 'tourism_places'))
                ->columns(array("city_id"))
                ->join(array('p' => 'place_prices'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tp`.`tourism_place_id`,`p`.`place_id`)"), array("tour_type", 'place_id'))
                ->where(array('tp.display' => 1, 'p.tour_type' => $data['tour_type']))
                ->group(array('tp.city_id'));
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'file_data_id'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_city, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));
            $placeLanguages = $sql->select()
                ->from(array('tfl' => 'tourism_files'))
                ->columns(array("file_path", 'file_data_id'))
                ->join(array('p' => 'place_prices'), new \Laminas\Db\Sql\Expression("FIND_IN_SET(`tfl`.`file_data_id`,`p`.`place_id`) and p.display=1 and p.tour_type=" . $data['tour_type']), array("tour_type", 'city_id', 'place_id', 'price', 'place_price' => 'price'))
                ->join(array('l' => 'languages'), 'tfl.file_language_type = l.id', array('language_name' => new \Laminas\Db\Sql\Expression('group_concat(`l`.`name`)')))
                ->where($fileLanguageWhere->equalTo('tfl.display', 1)->equalTo('p.display', 1)->and->equalTo('tfl.file_data_type', \Admin\Model\TourismFiles::file_data_type_places))
                ->group(array('p.city_id'));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('city_id' => "id", "city_name"))
                ->join(array('tpl' => $places), 'tpl.city_id = c.id', array())
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = c.id', array('file_path'))
                ->join(array('tl' => $placeLanguages), 'tl.city_id = c.id', array('language_name', 'place_id', 'place_price'), Select::JOIN_LEFT)
                ->where($where)
                ->order('c.city_name asc');
            if ($data['limit'] != -1) {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            }

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $cities = array();
            $counter = -1;
            foreach ($resultSet as $row) {
                $counter++;
                $cities[$counter]['city_id'] = $row['city_id'];
                $cities[$counter]['city_name'] = ucfirst($row['city_name']);
                $cities[$counter]['file_path'] = $row['file_path'];
                if ($data['tour_type'] == \Admin\Model\TourTales::tour_type_City_tour) {
                    $cities[$counter]['price'] = floatval($row['place_price']);
                }

                $cities[$counter]['languages'] = array_values(array_unique(array_filter(explode(",", $row['language_name']))));
                $cities[$counter]['places'] = array_values(array_unique(array_filter(explode(",", $row['place_id']))));
                if (count($cities[$counter]['places'])) {
                    $cities[$counter]['up_coming'] = 0;
                } else {
                    $cities[$counter]['up_coming'] = 1;
                }
            }
            return $cities;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getActiveCitiesListAdmin($data = array())
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('c.display', 1);
            if (array_key_exists('search', $data) && $data['search'] != '') {
                $where->and->like('c.city_name', "%" . $data['search'] . "%");
            }
            if (array_key_exists('state_id', $data) && $data['state_id'] != '') {
                $where->and->equalTo('c.state_id', $data['state_id']);
            } else {
                $where->and->equalTo('c.country_id', $data['country_id']);
            }
            $placesWhere = new Where();
            $prices = $sql->select()
                ->from(array('p' => 'tour_tales'))
                ->columns(array('place_id'))
                ->where(array('p.tour_type' => $data['tour_type'], 'p.display' => 1));
            $placesWhere->equalTo('tp.display', 1);
            $places = $sql->select()
                ->from(array('tp' => 'places'))
                ->columns(array("city_id"))
                ->where($placesWhere)
                ->group(array('tp.city_id'));
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'file_data_id'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_city, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "city_name"))
                ->join(array('tpl' => $places), 'tpl.city_id = c.id', array())
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = c.id', array('file_path'))
                ->where($where)
                ->order('c.city_name asc');
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $cities = array();
            foreach ($resultSet as $row) {
                $cities[] = $row;
            }
            return $cities;
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
    public function getCitiesByMaxId($maxId)
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "name", 'state_id', 'country_id'))
                ->where($where->greaterThan("id", $maxId));

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $cities = array();
            foreach ($resultSet as $row) {
                $cities[] = $row;
            }
            return $cities;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function citiesList($cityIds)
    {
        try {
            $where = new Where();
            $where->in('id', $cityIds);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('city_id' => "id", "city_name", 'state_id', 'country_id', 'city_description'))
                ->where($where);

            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $cities = array();
            $counter = -1;
            foreach ($resultSet as $row) {
                $counter++;
                $cities[$counter]['city_id'] = $row['city_id'];
                $cities[$counter]['city_name'] = $row['city_name'];
                $cities[$counter]['state_id'] = $row['state_id'];
                $cities[$counter]['country_id'] = $row['country_id'];
                $cities[$counter]['city_description'] = $row['city_description'];
            }
            return $cities;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getCityInfo($cityId)
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('c.display', 1)->equalTo('c.id', $cityId);
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('city_id' => "id", "city_name", "city_description", "country_id", 'state_id'))
                ->join(array('co' => 'country'), 'c.country_id = co.id', array('country_name'))
                ->join(array('s' => 'state'), 'c.state_id = s.id', array('state_name'), Select::JOIN_LEFT)
                ->where($where);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            foreach ($resultSet as $row) {
                $countries = $row;
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getCityDetails($cityId)
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('c.display', 1)->equalTo('c.id', $cityId);
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'tourism_file_id', 'file_data_id', 'file_extension_type', 'file_language_id', 'file_name'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_city));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('city_id' => "id", "city_name", "city_description", "country_id", 'state_id'))
                ->join(array('co' => 'country'), 'c.country_id = co.id', array('country_name'))
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = c.id', array('file_path', 'tourism_file_id', 'file_extension_type', 'file_language_id', 'file_name'), Select::JOIN_LEFT)
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

    public function getWorldCities4App($data, $gc, $countryId)
    {
        try {
            $where = new Where();
            $where->equalTo('c.display', 1)->equalTo('co.id', $countryId);
            $order = array('city_name asc');
            $sql = $this->getSql();
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'tourism_file_id', 'file_data_id', 'file_extension_type', 'file_language_id', 'file_name'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_city, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image));
            
            $placesCountSubquery = $sql->select()
                ->from(array('p' => 'place'))
                ->columns(array('city_id', 'display', 'places_count' => new \Laminas\Db\Sql\Expression('COUNT(p.id)')))
                ->group('p.city_id')
                ->having(new \Laminas\Db\Sql\Predicate\Expression('COUNT(p.id) > 1'));
            
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "name" =>  "city_name"))
                ->join(array('co' => 'country'), 'c.country_id = co.id', array("country_name"))
                ->join(
                    array('cc' => $placesCountSubquery), 
                    'cc.city_id = c.id', 
                    array()
                )
                ->join(
                    array('tfl' => $placeFiles), 
                    'tfl.file_data_id = c.id', 
                    array(
                        'file_path' => new \Laminas\Db\Sql\Expression("COALESCE(tfl.file_path, 'data/images/ph150x150.png')")
                    ), 
                    Select::JOIN_LEFT
                )
                ->where($where)
                ->order($order);
            if ($gc == 0) {
                $query->limit($data['limit'])
                        ->offset($data['offset']);
            }
            // echo $sql->getSqlStringForSqlObject($query);exit;
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
    public function getIndiaCities4App($data, $gc, $stateId)
    {
        try {
            $where = new Where();
            $where->equalTo('c.display', 1)->equalTo('s.id', $stateId);
            $order = array('city_name asc');
            $sql = $this->getSql();
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'tourism_file_id', 'file_data_id', 'file_extension_type', 'file_language_id', 'file_name'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_city, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image));

            $placesCountSubquery = $sql->select()
                ->from(array('p' => 'place'))
                ->columns(array('city_id', 'display', 'places_count' => new \Laminas\Db\Sql\Expression('COUNT(p.id)')))
                ->group('p.city_id')
                ->having(new \Laminas\Db\Sql\Predicate\Expression('COUNT(p.id) > 0'));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "name" =>  "city_name"))
                ->join(array('s' => 'state'), 'c.state_id = s.id', array("state_id" => "id", "state_name"))
                // ->join(array('co' => 'country'), 'c.country_id = co.id', array())
                ->join(
                    array('cc' => $placesCountSubquery), 
                    'cc.city_id = c.id', 
                    array()
                )
                ->join(
                    array('tfl' => $placeFiles), 
                    'tfl.file_data_id = c.id', 
                    array(
                        'file_path' => new \Laminas\Db\Sql\Expression("COALESCE(tfl.file_path, 'data/images/ph150x150.png')")
                    ), 
                    Select::JOIN_LEFT
                )
                // ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = c.id', array('file_path'), Select::JOIN_LEFT)
                ->where($where)
                ->order($order);
            
            // echo $sql->getSqlStringForSqlObject($query);exit;
            if ($gc == 0) {
                $query->limit($data['limit'])
                        ->offset($data['offset']);
            }
              
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $cities = array();
            foreach ($resultSet as $row) {
                $cities[] = $row;
            }
            return $cities;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getCityId($cityName)
    {
        try {
            $sql = $this->getSql();
            $values = array();
            $where = new Where();
            $query = $sql->select()
                ->columns(array('id'))
                ->from(array("c" => "cities"))
                ->where($where->equalTo("city_name", $cityName)->equalTo('status', 1));
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
