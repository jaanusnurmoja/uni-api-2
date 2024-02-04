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
    public array $joins;
    public array $fields;
    public array $pks;
    public array $rows;
    public array $origRows;
    public array $joinsWithData;
    protected function cnn() {
        $cnf = parse_ini_file(__DIR__ . '/../../../config/connection.ini');
        $mysqli = new \mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    }

    public function anySelect($query) {
        $db = $this->cnn();
        $rows = [];
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
            }
            $parts = explode('__', $field->table);
            if (count($parts) == 6) {
                $joinId = $parts[3];
                $mode = $parts[2];
                $thisTable = $parts[0];
                $keyField = $parts[1];
                $otherKeyField = $parts[4];
                $otherTable = $parts[5];
                $join = new Join($joinId, $mode, $thisTable, $keyField, $otherKeyField, $otherTable);
                $joins['this'][$thisTable][$mode][$joinId] = $join;
                $joins['other'][$otherTable][$mode][$joinId] = $join;
                $this->joins = $joins;
            }
            $fields[$field->name] = $field;
            $tables[$field->orgtable]['fields'][$field->apiName] = $field;
            $tables[$field->orgtable]['parent']['table'] = $thisTable;
            $tables[$field->orgtable]['parent']['pk'] = $pks[$thisTable];
            $this->tables = $tables;
            $this->fields = $fields;
            $this->pks = $pks;
        }
        while ($row = $res->fetch_object()) {
            if (isset($row->rowid)) {
                $this->origRows[$row->rowid][] = $row;
                $this->rows[0]['joins'] = $joins;
                $this->origRows[0]['joins'] = $joins;
                $this->processRow($row);
            } else {
                $this->rows[] = $row;
            }
        }
        $db->close();
    }

    public function processRow($row) {
        $rowData = [];
        $joins = $this->joins;
        foreach ($row as $key => $value) {
            $table = $this->fields[$key]->orgtable;
            $pk = $this->pks[$table];
            $parentPk = $this->tables[$table]['parent']['pk'];
            $parentTable = $this->tables[$table]['parent']['table'];
            $rowData[$table][$row->$pk][$this->fields[$key]->apiName] = $value;
            $thisEntity = new Entity($table); 
            $thisEntity->setPk(new Pk($table, $this->fields[$pk]->apiName, $row->$pk))->setData(new Data($table, $rowData[$table][$row->$pk], [$this->fields[$pk]->apiName]));
            $this->rows[$row->rowid][$parentTable][$row->$parentPk][$table][$row->$pk] = $thisEntity;
        }
        /*
        foreach ($this->rows[$row->rowid] as $thisTable => $thisRowSets) {
            foreach ($thisRowSets as $parentPkValue => $thisRows) {
                foreach ($thisRows as $thisPkValue => $thisEntity) {
                    foreach ($this->rows[$row->rowid] as $otherTable => $otherRowSets) {
                        if (isset($joins['other'][$otherTable])) {
                            foreach ($joins['other'][$otherTable] as $otherMode => $otherJoinList) {
                                foreach ($otherJoinList as $otherJoinId => $otherJoin) {
                                    foreach ($otherRowSets[$thisPkValue] as $otherPkValue => $otherEntity) {
                                        $otherJoin->addItem($otherEntity);
                                    }
                                    $thisEntity->$otherMode[$otherJoinId] = $otherJoin;
                                }
                            }
                        }
                    }
                }
            }
        }
        */
        /*
        echo '<pre>';
        print_r($this->joinsWithData);
        echo '</pre>';
        */
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