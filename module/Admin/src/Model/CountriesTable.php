<?php


namespace Admin\Model;


use Application\Model\BaseTable;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class CountriesTable extends  BaseTable
{
    protected $tableGateway;
    protected $tableName;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->tableName = array("c" => "country");
    }

    public function addCountry(array $data)
    {
        try {
            $insert = $this->insert($data);
            if ($insert)
                return array("success" => true, "id" => $this->tableGateway->lastInsertValue);
            else
                return array("success" => false);
        } catch (\Exception $e) {
            /* print_r($e->getMessage());
            exit; */
            return array("success" => false);
        }
    }
    // can be used for dropdowns - to get active/inactive countries list using $where=['display'=>1/0]
    public function getCountries($data = ['display' => 1])
    {
        try {
            $sql = $this->getSql();
            /* if($wt == 1){
                $where = new Where();
                $where->equalTo('display', 1);
                $where->and->notEqualTo("id", '101');
                $query = $sql->select()
                ->from($this->tableName)
                ->columns(["id", "country_name"])
                ->where($where)
                ->order('c.country_name asc');
            }else{ */
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(["id", "country_name"])
                ->where($data)
                ->order('c.country_name asc');
            /* } */
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

    public function getCountries4wt()
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('display', 1);
            $where->and->notEqualTo("id", '101');
            $query = $sql->select()
            ->from($this->tableName)
            ->columns(["id", "country_name"])
            ->where($where)
            ->order('c.country_name asc');
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

    // Function to get countries list to be displayed in admin/countries page
    public function getCountries4Admin($data = ['limit' => 10, 'offset' => 0], $gc = 0)
    {
        try {
            $where = new Where();
            $where->equalTo('display', 1);
            if (array_key_exists('country', $data)) {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"), '%' . $data['country'] . "%");
            }
            $order = array();
            if (array_key_exists('country_order', $data)) {
                if ($data['country_order'] == 1) {
                    $order[] = 'c.country_name asc';
                } else if ($data['country_order'] == -1) {
                    $order[] = 'c.country_name desc';
                }
            }
            if (!count($order)) {
                $order = array('country_name asc');
            }

            $sql = $this->getSql();
            if ($gc == 1) {
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("id", "country_name"))
                    ->where($where)
                    ->order($order);
            } else {
                $query = $sql->select()
                    ->from($this->tableName)
                    ->columns(array("id", "country_name"))
                    ->where($where)
                    ->order($order)
                    ->limit($data['limit'])
                    ->offset($data['offset']);
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
            return array();
        }
    }

    public function getActiveCountriesForPlaces()
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "country_name"))
                ->join(array('ci' => 'cities'), 'ci.country_id=c.id', array())
                ->group(array('c.id'))
                ->where(array('c.display' => 1, 'ci.status' => 1));

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
    public function getActiveCountriesListAdmin($data = array('limit' => 10, 'offset' => 0))
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('c.display', 1);
            if (array_key_exists('search', $data) && $data['search'] != '') {
                $where->and->like('c.country_name', "%" . $data['search'] . "%");
            }
            $placesWhere = new Where();
            $prices = $sql->select()
                ->from(array('p' => 'tour_tales'))
                ->columns(array('place_id'))
                ->where(array('p.tour_type' => $data['tour_type'], 'p.display' => 1));
            $placesWhere->equalTo('tp.display', 1)->and->notIn('tp.id', $prices);
            $places = $sql->select()
                ->from(array('tp' => 'places'))
                ->columns(array("country_id"))
                ->where($placesWhere)
                ->group(array('tp.country_id'));
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'file_data_id'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_country, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "country_name"))
                ->join(array('tpl' => $places), 'tpl.country_id=c.id', array())
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = c.id', array('file_path'), Select::JOIN_LEFT)
                ->where($where)
                ->order('c.country_name asc');
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

    public function getActiveCountriesList($data = array('limit' => 10, 'offset' => 0))
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $where->equalTo('c.display', 1);
            if (array_key_exists('search', $data) && $data['search'] != '') {
                $where->and->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"), new \Laminas\Db\Sql\Expression('LOWER("%' . $data['search'] . '%")'));
            }

            $places = $sql->select()
                ->from(array('tp' => 'tourism_places'))
                ->columns(array("country_id"))
                ->join(array('p' => 'place_prices'), new \Laminas\Db\Sql\Expression("p.country_id=tp.country_id and  p.status =1 and p.tour_type=" . $data['tour_type']), array("tour_type", 'countcountry' => new \Laminas\Db\Sql\Expression("SUM(CASE WHEN `place_id` IS NULL || `place_id`='' THEN 0 ELSE 1 END)")))
                ->where(array('tp.status' => 1, 'p.tour_type' => $data['tour_type'], 'p.status' => 1))
                ->group(array('tp.country_id'));

            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'file_data_id'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_country, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image))
                ->group(array('tf.file_data_id'));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('country_id' => "id", "country_name", 'flag_image'))
                ->join(array('tpl' => $places), 'tpl.country_id=c.id', array('place_country_id' => 'country_id', 'countcountry'))
                ->join(array('tfl' => $placeFiles), 'tfl.file_data_id = c.id', array('file_path'), Select::JOIN_LEFT)
                ->where($where)
                ->order('c.country_name asc');
            if ($data['limit'] != -1) {
                $query->offset($data['offset'])
                    ->limit($data['limit']);
            }
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            $counter = -1;
            foreach ($resultSet as $row) {
                $counter++;
                $countries[$counter]['country_id'] = $row['country_id'];
                $countries[$counter]['country_name'] = ucfirst($row['country_name']);
                $countries[$counter]['flag_image'] = $row['flag_image'];
                $countries[$counter]['file_path'] = $row['file_path'];
                if ($row['countcountry'] == 0) {
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



    public function updateCountry($data, $where)
    {
        try {
            return $this->update($data, $where);
        } catch (\Exception $e) {
            return false;
        }
    }
    public function getField($where, $column)
    {
        try {
            if (array_key_exists('country_name', $where)) {
                $condtions = $where;
                $where = new Where();
                $where->like(new \Laminas\Db\Sql\Expression("LOWER(c.country_name)"), new \Laminas\Db\Sql\Expression('LOWER("%' . $condtions['country_name'] . '%")'));
                unset($condtions['country_name']);
                foreach ($condtions as $key => $condtion) {
                    $where->equalTo($key, $condtion);
                }
            }
            $sql = $this->getSql();
            $query = $sql->select()
                ->from(array("c" => "country"))
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
    public function getCountriesByMaxId($maxId)
    {
        try {
            $sql = $this->getSql();
            $where = new Where();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "country_name"))
                ->where($where->greaterThan('id', $maxId));

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
    public function getCountriesDetails($countryId)
    {
        try {
            $sql = $this->getSql();

            $where = new Where();
            $where->equalTo('c.display', 1)->equalTo('c.id', $countryId);
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'tourism_file_id', 'file_data_id', 'file_extension_type', 'file_language_id', 'file_name'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_country));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('country_id' => "id", "country_name", "country_description", 'flag_image'))
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
    public function countriesList($countryIds)
    {
        try {
            $where = new Where();
            $where->in('id', $countryIds);
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array('country_id' => "id", "country_name", 'country_description', 'flag_image'))
                ->where($where)//;
                ->order('country_name asc');
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            $counter = -1;
            foreach ($resultSet as $row) {
                $counter++;
                $countries[$counter]['country_id'] = $row['country_id'];
                $countries[$counter]['country_name'] = ucwords($row['country_name']);
                $countries[$counter]['country_description'] = $row['country_description'];
                $countries[$counter]['flag_image'] = $row['flag_image'];
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function countryCodesList()
    {
        try {
            $sql = $this->getSql();
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("country_name", "phone_code"));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countryCodes = array();
            $counter = -1;
            foreach ($resultSet as $row) {
                $counter++;
                $countryCodes[$counter]['country_name'] = ucwords($row['country_name']);
                $countryCodes[$counter]['phone_code'] = '+' . $row['phone_code'];
            }
            return $countryCodes;
        } catch (\Exception $e) {

            return array();
        }
    }
    public function getFields($data, $columns)
    {
        try {
            $sql = $this->getSql();
            $values = array();
            $where = new Where();
            $query = $sql->select()
                ->columns($columns)
                ->from(array("c" => "country"))
                ->where($data);
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $values = $row;
            }
            return $values;
        } catch (\Exception $e) {
            return array();
        }
    }
    public function getCountryId($countryName)
    {
        try {
            $sql = $this->getSql();
            $values = array();
            $where = new Where();
            $query = $sql->select()
                ->columns(array('id'))
                ->from(array("c" => "country"))
                ->where($where->equalTo("country_name", $countryName));
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            foreach ($resultSet as $row) {
                $values = $row['id'];
            }
            return $values;
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getCountries4App($data = array('limit' => 10, 'offset' => 0), $gc = 0){
        try {
            $where = new Where();
            $where->equalTo('c.display', 1) ->equalTo('cc.display', 1); //->equalTo('p.display', 1);
            $where->and->notEqualTo("c.id", '101');
            $order = array('country_name asc');
            $sql = $this->getSql();
            $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array(
                    "file_path", 
                    'tourism_file_id', 
                    'file_data_id', 
                    'file_extension_type', 
                    'file_language_id', 
                    'file_name'
                ))
                ->where(array(
                    'tf.display' => 1, 
                    'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_country, 
                    'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image
                ));
            /*
            $citiesCountSubquery = $sql->select()
                ->from(array('ci' => 'city'))
                ->columns(array('country_id', 'display', 'city_count' => new \Laminas\Db\Sql\Expression('COUNT(ci.id)')))
                ->group('ci.country_id')
                ->having(new \Laminas\Db\Sql\Predicate\Expression('COUNT(ci.id) > 1'));
                */
            
            $placesCountSubquery = $sql->select()
                ->from(array('p' => 'place'))
                ->columns(array('country_id', 'display', 'places_count' => new \Laminas\Db\Sql\Expression('COUNT(p.id)')))
                ->group('p.country_id')
                ->having(new \Laminas\Db\Sql\Predicate\Expression('COUNT(p.id) > 0'));

            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array(
                    "id", 
                    "name" => "country_name"
                ))
                ->join(
                    array('cc' => $placesCountSubquery), 
                    'cc.country_id = c.id', 
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

            /* $placeFiles = $sql->select()
                ->from(array('tf' => 'tourism_files'))
                ->columns(array("file_path", 'tourism_file_id', 'file_data_id', 'file_extension_type', 'file_language_id', 'file_name'))
                ->where(array('tf.display' => 1, 'tf.file_data_type' => \Admin\Model\TourismFiles::file_data_type_country, 'tf.file_extension_type' => \Admin\Model\TourismFiles::file_extension_type_image));
            $query = $sql->select()
                ->from($this->tableName)
                ->columns(array("id", "name" => "country_name"))
                ->join(array('ci' => 'city'), 'ci.country_id = c.id', array('city_name'))
                ->join(array('p' => 'place'), 'p.city_id = ci.id', array('place_name'))
                ->join(
                    array('tfl' => $placeFiles), 
                    'tfl.file_data_id = c.id', 
                    array(
                        'file_path' => new \Laminas\Db\Sql\Expression("COALESCE(tfl.file_path, 'data/images/ph150x150.png')")
                    ), 
                    Select::JOIN_LEFT
                )
                ->where($where)
                ->order($order); */
            //    echo $sql->getSqlStringForSqlObject($query);exit;
            if ($gc == 0) {
                $query->limit($data['limit'])
                        ->offset($data['offset']);
            }
            $resultSet = $sql->prepareStatementForSqlObject($query)->execute();
            $countries = array();
            foreach ($resultSet as $row) {
                $row['full_name'] = "";
                $countries[] = $row;
            }
            return $countries;
        } catch (\Exception $e) {
            return array();
        }
    }
}
