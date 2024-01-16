<?php namespace Api\Service;

include_once __DIR__.'/../../../admin/Controller/Table.php';
include_once __DIR__.'/../../../common/Check.php';
include_once __DIR__.'/DbRead.php';
use \Common\Helper;
use \Api\Service\DbRead;
use Service\Read;
use stdClass;

class QueryMaker
{
    public $model;
    public $seq = 0;
    public $select;
    public $from;
    public $join = [];
    public array $where;
    public array $whereOpts;
    public $orderBy;
    public $limit;

    public function __construct($tableName = null)
    {
        $aCtrl = new \Controller\Table;
        $this->model = $aCtrl->getTableByIdOrName(true, $tableName);
        if (!empty($tableName)) {
            $this->getQueryDataFromModels($tableName);
            $this->where = [];
            $this->whereOpts = [];
        }
    }
    
    public function getQueryDataFromModels($tableName, $parentName = null, $seqPref = null, $noHasMany = false, $any = false, $dataFields = null) {
        if (!isset($check)) $check = new \Common\Check;
        $mainTable = null;
        if ($tableName == $this->model->tableName && $tableName != $parentName) {
            $model = $this->model;
            $mainTable = $tableName;
            $check->makeHasManyList($mainTable);
            $pkSelect = Helper::sqlQuotes($model->getPkSelect());
            $this->select = "SELECT `$mainTable`.`$model->pk` AS `rowid`, `$mainTable`.`$model->pk` AS `entity__$mainTable:$model->pk`, {$this->getFieldsForQuery($model->data, true, 'entity__'.$mainTable)}" ;
            $this->from = "FROM `$mainTable`";
        } else {
            $aCtrl = new \Controller\Table;
            $model = $aCtrl->getTableByIdOrName(true, $tableName);
            if ($any) {
                $pkToSelect = $tableName . '.' . $dataFields->pk;
                $pkAlias = $tableName . ':' . $dataFields->pk;

            } else {
                $pkToSelect = $model->getPkSelect();
                $pkAlias = $model->getPkAlias();
            }
            $pkSelect = Helper::sqlQuotes($seqPref . $pkToSelect);
            $fields = $any ? $dataFields : $model->data;

            $this->select .= ", $pkSelect AS `$seqPref{$pkAlias}`, {$this->getFieldsForQuery($fields, false, $seqPref)}";
        }

        if (isset($model->hasMany) && $model->hasMany != [] && !$noHasMany) {
            $this->makeHasMany($tableName, $model->hasMany, $check, $seqPref);
        }
        
        if (isset($model->belongsTo) && $model->belongsTo != []) {
            $this->makeBelongsTo($tableName, $model->belongsTo, $parentName, $seqPref, $mainTable);
        }
        if (isset($model->hasManyAndBelongsTo) && $model->hasManyAndBelongsTo != []) {
            $this->makeHasManyAndBelongsTo($model->hasManyAndBelongsTo, $parentName, $seqPref);
        }
        if (isset($model->hasAny) && $model->hasAny != [] && !$noHasMany) {
            $this->makeHasAny($model->hasAny, $tableName);
        }

    }

    public function makeHasMany($tableName, $hasMany, $check, $seqPref) {
        foreach ($hasMany as $hmItem) {
            if ($check->makeHasManyList($hmItem->getManyTable()) === true && $hmItem->getOneTable() == $tableName) {
                // $this->select .= ", {$hmItem->getManyTable()}.*";
                array_push($this->join, "LEFT JOIN `{$hmItem->getManyTable()}` `{$tableName}__hasMany__{$hmItem->id}__{$hmItem->getManyTable()}`
                ON `{$tableName}__hasMany__{$hmItem->id}__{$hmItem->getManyTable()}`.`{$hmItem->getManyFk()}`
                = `{$seqPref}{$tableName}`.`{$hmItem->getOnePk()}`");
                $this->getQueryDataFromModels($hmItem->getManyTable(), $tableName, $tableName.'__hasMany__'.$hmItem->id.'__');
            }
        }

    }

    public function makeBelongsTo($tableName, $belongsTo, $parentName, $seqPref, $mainTable) {
        $seq = 0;
        foreach($belongsTo as $btItem) {
            $sp = isset($mainTable) ? 'entity__' : $seqPref;
            //$asAlias = isset($mainTable) ? null : " AS `{$seqPref}$tableName:{$btItem->keyField}`";
            $asAlias = " AS `{$sp}$tableName:{$btItem->keyField}`";
            $this->select .= ", `{$seqPref}{$tableName}`.`{$btItem->keyField}`$asAlias";
            
            if (!in_array($btItem->getOneTable(), [$mainTable, $tableName, $parentName])) {
                array_push($this->join, "LEFT JOIN `{$btItem->getOneTable()}` `{$tableName}__belongsTo__{$btItem->id}__{$btItem->getOneTable()}`
                ON `{$tableName}__belongsTo__{$btItem->id}__{$btItem->getOneTable()}`.`{$btItem->getOnePk()}`
                = `{$seqPref}$tableName`.`{$btItem->getManyFk()}`");
                $this->getQueryDataFromModels($btItem->getOneTable(), $tableName, $tableName.'__belongsTo__' . $btItem->id . '__', true);
            }
            
            //$this->seq++;
            $seq++;
        }

    }

    public function makeHasManyAndBelongsTo($hmAbt, $parentName, $seqPref) {
        //$seq = $this->seq;
        $seq = 0;
        foreach ($hmAbt as $hmabtItem) {
            $thisTable = $hmabtItem->table->tableName;
            $thisPk = $hmabtItem->table->pk;
            if ($hmabtItem->otherTable != '/'.$parentName) {
                if (is_object($hmabtItem->manyMany)) {
                        array_push($this->join, "LEFT JOIN `uasys_crossref` ON JSON_CONTAINS(JSON_EXTRACT(`table_value`, '$.$thisTable'), `$thisTable`.`$thisPk`)
                        LEFT JOIN `$thisTable` `{$thisTable}__hasManyAndBelongsTo__{$hmabtItem->id}__related_$thisTable`
                        ON (JSON_CONTAINS(JSON_EXTRACT(`table_value`, '$.$thisTable'), `{$thisTable}__hasManyAndBelongsTo__{$hmabtItem->id}__related_$thisTable`.`$thisPk`) 
                        AND `{$thisTable}__hasManyAndBelongsTo__{$hmabtItem->id}__related_$thisTable`.`$thisPk` <> `{$seqPref}{$thisTable}`.`$thisPk`)");
                        $this->getQueryDataFromModels($thisTable, $thisTable, $thisTable.'__hasManyAndBelongsTo__' . $hmabtItem->id . '__related_');
                } else {
                            $otherTable = null;
                            $otherPk = null;
                        foreach ($hmabtItem->manyMany as $manyManyPart) {
                            if ($manyManyPart->table != $hmabtItem->table->tableName) {
                                $otherTable = $manyManyPart->table;
                                $otherPk = $manyManyPart->pk;
                                array_push($this->join, "LEFT JOIN `uasys_crossref` ON JSON_CONTAINS_PATH(`table_value`, 'ALL','$.$thisTable','$.$otherTable')
                                AND JSON_EXTRACT(table_value, '$.$thisTable') = `{$seqPref}{$thisTable}`.`$thisPk`
                                LEFT JOIN `$otherTable` `{$thisTable}__hasManyAndBelongsTo__{$hmabtItem->id}__$otherTable` ON JSON_EXTRACT(`table_value`, '$.$otherTable') = `{$thisTable}__hasManyAndBelongsTo__{$hmabtItem->id}__$otherTable`.`$otherPk`");
                                $this->getQueryDataFromModels($otherTable, $thisTable, $thisTable.'__hasManyAndBelongsTo__' . $hmabtItem->id . '__');
                            }
                        }
                }

            }
            $seq++;
        }        
    }

    public function makeHasAny($hasAny, $tableName) {

        $dbRead = new DbRead;
        $adminRead = new Read;

        foreach ($hasAny as $hasAnyItem) {
            $items = $dbRead->anySelect("SELECT DISTINCT * FROM uasys_anyref WHERE uasys_anyref.orig_table = '$tableName' GROUP BY uasys_anyref.any_table" );
            foreach ($items as $i => $item) {
                    $anyFields = (object) $adminRead->getDefaultFields($item->any_table);
                    $anyFields->fields = (object) $anyFields->dataFields;
                    unset($anyFields->dataFields);
                    
                    $items[$i] = $item;
                    $sql = "LEFT JOIN `uasys_anyref`
                    ON `uasys_anyref`.`orig_table` = '$tableName'
                    AND `uasys_anyref`.`orig_pk` = `$tableName`.`{$hasAnyItem->table->pk}`
                    LEFT JOIN `{$item->any_table}` `{$tableName}__hasAny__{$hasAnyItem->id}_{$i}__{$item->any_table}`
                    ON `{$tableName}__hasAny__{$hasAnyItem->id}_{$i}__{$item->any_table}`.`{$anyFields->pk}` = `uasys_anyref`.`any_pk`";
                    array_push($this->join, $sql);
                    $this->getQueryDataFromModels($item->any_table, $tableName, $tableName.'__hasAny__'.$hasAnyItem->id.'_'.$i.'__', true, true, $anyFields);

            }
        }
}

    public function getFieldsForQuery($data, $start = false, $seqPref = null) {
        $fields = [];
        foreach ($data->fields as $fname => $field ) {
            $alias = Helper::sqlQuotes($seqPref.$field->sqlAlias);
            $aliasOrNot = $start ? " AS `{$seqPref}:$fname`" : " AS $alias";
            $sp = $start ? null : $seqPref;
            $fields[] = Helper::sqlQuotes($sp.$field->sqlSelect) . $aliasOrNot;
        }
        return implode(', 
        ', $fields);
    }

    public function byId($id) {
        if (!empty($id)) {
        $pk = $this->model->pk;
        $this->addWhere("{$this->model->tableName}.$pk = $id");}
        return $this;
    }

    public function addWhere($where) {
        array_push($this->where, $where);
    }

    public function whereStmt() {
        $where = 'WHERE ';
        if (!empty($this->where)) {
            $and = implode(' AND ', $this->where);
            $where .= "($and)";
            if (!empty($this->whereOpts)) {
                $where .= ' AND ';
            }
        }
        if (!empty($this->whereOpts)) {
            $opts = implode(' OR ', $this->whereOpts);
            $where = "($opts)";
        }

        return $where;

    }

    /**
     * @return string
     */
    public function __toString() {
        $join = implode(' 
        ' ,$this->join);
        $where = '';
        if (!empty($this->where) || !empty($this->whereOpts)) {
            $where = $this->whereStmt();
        }
    	return "{$this->select}
        {$this->from}
        $join
        $where
        $this->orderBy
        $this->limit";
    }
}