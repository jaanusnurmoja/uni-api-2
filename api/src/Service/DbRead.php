<?php namespace Api\Service;

include_once __DIR__ . '/../Model/CreatedModified.php';
include_once __DIR__ . '/../Model/Data.php';
include_once __DIR__ . '/../Model/Entity.php';
include_once __DIR__ . '/../Model/Pk.php';
include_once __DIR__ . '/../Model/Join.php';
use Api\Model\CreatedModified;
use Api\Model\Data;
use Api\Model\Entity;
use Api\Model\Pk;
use \Api\Model\Join;
use Common\Helper;

class DbRead
{
    public array $tables;
    public array $pks;
    public array $rows;
    public array $origRows;
    public array $fields;
    public array $joins;

    protected function cnn() {
        $cnf = parse_ini_file(__DIR__ . '/../../../config/connection.ini');
        $mysqli = new \mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    }

    public function anySelect($query) {
        $db = $this->cnn();
        $rows = [];
        //$results = new Result();
        $res = $db->query($query);
        $fields = [];
        $joins = [];
        $tables = [];
        $pks = [];
        foreach($res->fetch_fields() as $field) {
            $field->apiName = Helper::camelize($field->orgname, true);
            if ($field->orgname == $this->getPk($field->orgtable)) {
                $pks[$field->orgtable] = $field->name;
                $tables[$field->orgtable]['tableAlias'] = $field->table;
                $tables[$field->orgtable]['pk'] = $field->apiName;
                $parts = explode('__', $field->table);
                $joinId = $parts[3];
                $mode = $parts[2];
                $thisTable = $parts[0];
                $keyField = $parts[1];
                $otherKeyField = $parts[4];
                $otherTable = $parts[5];
            }
            if (count($parts) == 6) {
                $join = new Join($joinId, $mode, $thisTable, $keyField, $otherKeyField, $otherTable);
                //$joins['all'][$parts[3]][] = $join;
                $joins['this'][$thisTable][$mode][$joinId] = $join;
                $joins['other'][$otherTable][$mode][$joinId] = $join;
                $this->joins = $joins;
            }
            $fields[$field->name] = $field;
            $tables[$field->orgtable]['fields'][$field->apiName] = $field;
            $this->tables = $tables;
            $this->fields = $fields;
        }
        while ($row = $res->fetch_object()) {
            if (isset($row->rowid)) {
            $this->origRows[$row->rowid][] = $row;
                //$rows[0]['pks'] = $pks;
                //$rows[0]['tables'] = $tables;
                //$rows[0]['fields'] = $fields;
                $this->rows[0]['joins'] = $joins;
                $this->origRows[0]['joins'] = $joins;
                $this->rows[$row->rowid] = $this->makeDataRows($row);
            } else {
                $this->rows[] = $row;
            }
        }
        $db->close();
        //return $rows;
    }


    public function makeDataRows($row){
        foreach ($row as $key => $value) {
            $table = $this->fields[$key]->orgtable;
            $pk = $this->pks[$table];
            $rows[$table][$row->$pk][$this->fields[$key]->apiName] = $value;
        }
        return $rows;
    }

    public function getPk($table)
    {
        $keys = $this->getColumns($table);
        return $keys->pk;
    }

    public function getColumns($table)
    {
        $db = $this->cnn();
        $cols = new \stdClass();
        $sql = "SHOW COLUMNS FROM `$table`";
        if ($result = $db->query($sql)) {
            while ($column = $result->fetch_object()) {
                if ($column->Key == 'PRI') {
                    $cols->pk = $column->Field;
                } else {
                    $cols->{$column->Field} = $column;
                }
            }
        }
        $db->close();
        return $cols;
    }

    
}