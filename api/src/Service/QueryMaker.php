<?php namespace Api\Model;

include_once __DIR__.'/../../../admin/Controller/Table.php';
include_once __DIR__.'/../../../common/Check.php';

class QueryMaker
{
    public $model;
    public $seq = 0;
    public $select;
    public $from;
    public $join = [];
    public $where;
    public $orderBy;
    public $limit;

    public function __construct($tableName = null)
    {
        $aCtrl = new \Controller\Table;
        $this->model = $aCtrl->getTableByIdOrName(true, $tableName);
        if (!empty($tableName)) {
            $this->getQueryDataFromModels($tableName);
        }
    }
    
    public function getQueryDataFromModels($tableName, $parentName = null, $seqPref = null, $noHasMany = false) {
        if (!isset($check)) $check = new \Common\Check;
        $mainTable = null;
        if ($tableName == $this->model->tableName) {
            $model = $this->model;
            $mainTable = $tableName;
            $check->makeHasManyList($mainTable);
            $this->select = "SELECT {$model->getPkSelect()}, {$this->getFieldsForQuery($model->data, true)}" ;
            $this->from = "FROM $mainTable";
        } else {
            $aCtrl = new \Controller\Table;
            $model = $aCtrl->getTableByIdOrName(true, $tableName);
            $this->select .= ", $seqPref{$model->getPkSelect()} AS $seqPref{$model->getPkAlias()}, {$this->getFieldsForQuery($model->data, false, $seqPref)}";
        }


        if ($model->hasMany != [] && !$noHasMany) {
            foreach ($model->hasMany as $hmItem) {
                if ($check->makeHasManyList($hmItem->getManyTable()) === true && $hmItem->getOneTable() == $tableName) {
                    // $this->select .= ", {$hmItem->getManyTable()}.*";
                    $this->getQueryDataFromModels($hmItem->getManyTable(), $tableName);
                    array_push($this->join, "LEFT JOIN {$hmItem->getManyTable()}
                    ON {$hmItem->getManyTable()}.{$hmItem->getManyFk()}
                    = $tableName.{$hmItem->getOnePk()}");
                }
            }
        }
        
        if ($model->belongsTo != []) {
            foreach($model->belongsTo as $btItem) {
                $asAlias = isset($mainTable) ? null : " AS $tableName:{$btItem->keyField}";
                $this->select .= ", $tableName.{$btItem->keyField}$asAlias";
                
                if (!in_array($btItem->getOneTable(), [$mainTable, $tableName, $parentName])) {
                    $this->getQueryDataFromModels($btItem->getOneTable(), $tableName, $this->seq . '__', true);
                    array_push($this->join, "LEFT JOIN {$btItem->getOneTable()} {$this->seq}__{$btItem->getOneTable()}
                    ON {$this->seq}__{$btItem->getOneTable()}.{$btItem->getOnePk()}
                    = $tableName.{$btItem->getManyFk()}");
                }
                
                $this->seq++;
            }
        }
        if ($model->hasManyAndBelongsTo != []) {
            $seq = $this->seq;
            foreach ($model->hasManyAndBelongsTo as $hmabtItem) {
                $thisTable = $hmabtItem->table->tableName;
                $thisPk = $hmabtItem->table->pk;
                $otherTable = null;
                $otherPk = null;
                if ($hmabtItem->otherTable != '/'.$parentName) {
                    switch(is_array($hmabtItem->manyMany)) {
                        case false:
                            $this->getQueryDataFromModels($thisTable, $tableName, $seq . '__related_');
                            array_push($this->join, "LEFT JOIN uasys_crossref ON JSON_CONTAINS(JSON_EXTRACT(table_value, '$.$thisTable'), $thisTable.$thisPk)
                            LEFT JOIN $thisTable {$seq}__related_$thisTable
                            ON (JSON_CONTAINS(JSON_EXTRACT(table_value, '$.$thisTable'), {$seq}__related_$thisTable.$thisPk) 
                            AND {$seq}__related_$thisTable.$thisPk <> $thisTable.$thisPk)");
                        case true:
                            foreach ($hmabtItem->manyMany as $manyManyPart) {
                                if ($manyManyPart->table != $hmabtItem->table->tableName) {
                                    $otherTable = $manyManyPart->table;
                                    $otherPk = $manyManyPart->pk;
                                }
                            }
                            $this->getQueryDataFromModels($otherTable, $thisTable, $seq . '__');
                            array_push($this->join, "LEFT JOIN uasys_crossref ON JSON_CONTAINS_PATH(table_value, 'ALL','$.$thisTable','$.$otherTable')
                            AND JSON_EXTRACT(table_value, '$.$thisTable') = $thisTable.$thisPk
                            LEFT JOIN $otherTable {$seq}__$otherTable ON JSON_EXTRACT(table_value, '$.$otherTable') = {$seq}__$otherTable.$otherPk");
                    }

                }
                $seq++;
           }
        }
    }

    public function getFieldsForQuery($data, $start = false, $seqPref = null) {
        $fields = [];
        foreach ($data->fields as $fname => $field ) {
        $aliasOrNot = $start ? null : ' AS ' . $seqPref . $field->sqlAlias;
            $fields[] = "$seqPref{$field->sqlSelect}$aliasOrNot";
        }
        return implode(', ', $fields);
    }

    /**
     * @return string
     */
    public function __toString() {
        $join = implode(' 
        ' ,$this->join);
    	return "{$this->select} {$this->from} $join";
    }
}