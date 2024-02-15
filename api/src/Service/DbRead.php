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
    public array $dataWithRelations;
    protected function cnn() {
        $cnf = parse_ini_file(__DIR__ . '/../../../config/connection.ini');
        $mysqli = new \mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    }

    public function anySelect($query) {
        $db = $this->cnn();
        $res = $db->query($query);
        $fields = [];
        $joins = [];
        $tables = [];
        $pks = [];
        foreach($res->fetch_fields() as $field) {
            $field->apiName = Helper::camelize($field->orgname, true);
            $field->finalTable = $field->orgtable;
            $parts = explode('__', $field->table);
            if (count($parts) == 6) {
                $joinId = $parts[3];
                $mode = $parts[2];
                $thisTable = $parts[0];
                $keyField = $parts[1];
                $otherKeyField = $parts[4];
                $otherTable = $parts[5];
                $field->finalTable = $otherTable;
                $join = new Join($joinId, $mode, $thisTable, $keyField, $otherKeyField, $otherTable);
                $joins['this'][$thisTable][$otherTable][$joinId] = $join;
                $joins['other'][$otherTable][$thisTable][$joinId] = $join;
                $tables[$field->finalTable]['parent']['table'] = $thisTable;
                $tables[$field->finalTable]['parent']['pk'] = $pks[$thisTable];
            }
            if ($field->orgname == $this->getPk($field->orgtable)) {
                $pks[$field->finalTable] = $field->name;
                $tables[$field->finalTable]['tableAlias'] = $field->table;
                $tables[$field->finalTable]['pk'] = $field->apiName;
            }
            $fields[$field->name] = $field;
            $tables[$field->finalTable]['fields'][$field->apiName] = $field;
            $this->joins = $joins;
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
            $table = $this->fields[$key]->finalTable;
            $pk = $this->pks[$table];
            $parentPk = $this->tables[$table]['parent']['pk'];
            $parentTable = $this->tables[$table]['parent']['table'];
            $rowData[$table][$row->$pk][$this->fields[$key]->apiName] = $value;
            //echo "table: " . $table . " pk: " . $pk . " parentPk: " . $parentPk . " parentTable: " . $parentTable . " value: " . $value . "<br>";
            $thisEntity = new Entity($table); 
            $thisEntity->setPk(new Pk($table, $this->fields[$pk]->apiName, $row->$pk))->setData(new Data($table, $rowData[$table][$row->$pk], [$this->fields[$pk]->apiName]));
            $this->rows[$row->rowid][$parentTable][$row->$parentPk]['related'][$table][$row->$pk] = $thisEntity;
            $this->rows[$row->rowid][$parentTable][$row->$parentPk]['related']['__properties'] = $joins['this'][$parentTable];
        }

        foreach($this->rows[$row->rowid] as $parentTable => $rowSets) {
            foreach ($rowSets as $parentPkValue => $tableRowSet) {
                foreach ($tableRowSet['related'] as $table => $thisRows) {
                    if ($table != '__properties') {
                        foreach ($thisRows as $pkValue => $entity) {
                            if (isset($this->rows[$row->rowid][$table])) {
                                foreach ($this->rows[$row->rowid][$table] as $otherParentPkValue => $otherTableSet) {
                                    if ($otherParentPkValue == $pkValue) {
                                        foreach ($otherTableSet['related'] as $otherTable => $otherRowSet) {
                                            if ($otherTable != '__properties') {
                                                if ((empty($parentTable) && empty($parentPkValue)) || $otherTable == $parentTable || strpos($otherTable, 'related_')) {
                                                    $entity->related[$otherTable] = $otherRowSet;
                                                }
                                            } else {
                                                $entity->related['__properties'] = $otherRowSet;
                                            }
                                        }
                                    }
                                }
                            }
                            if (empty($parentTable) && empty($parentPkValue) || $parentTable == $table) {
                                $this->dataWithRelations[$pkValue] = $thisRows[$pkValue];
                            }
                        }
                        
                
                    }
                }
            }
        }
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