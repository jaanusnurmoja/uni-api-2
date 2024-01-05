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
                
                $this->seq ++;
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