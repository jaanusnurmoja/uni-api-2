<?php namespace Api\Model;

include_once __DIR__.'/../../../admin/Controller/Table.php';
include_once __DIR__.'/../../../common/Check.php';
use Common\Helper;

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
        if ($tableName == $this->model->tableName && $tableName != $parentName) {
            $model = $this->model;
            $mainTable = $tableName;
            $check->makeHasManyList($mainTable);
            $pkSelect = Helper::sqlQuotes($model->getPkSelect());
            $this->select = "SELECT $pkSelect, {$this->getFieldsForQuery($model->data, true)}" ;
            $this->from = "FROM `$mainTable`";
        } else {
            $aCtrl = new \Controller\Table;
            $model = $aCtrl->getTableByIdOrName(true, $tableName);
            $pkSelect = Helper::sqlQuotes($seqPref . $model->getPkSelect());
            $this->select .= ", $pkSelect AS `$seqPref{$model->getPkAlias()}`, {$this->getFieldsForQuery($model->data, false, $seqPref)}";
        }

        if ($model->hasMany != [] && !$noHasMany) {
            foreach ($model->hasMany as $hmItem) {
                if ($check->makeHasManyList($hmItem->getManyTable()) === true && $hmItem->getOneTable() == $tableName) {
                    // $this->select .= ", {$hmItem->getManyTable()}.*";
                    array_push($this->join, "LEFT JOIN `{$hmItem->getManyTable()}`
                    ON `{$hmItem->getManyTable()}`.`{$hmItem->getManyFk()}`
                    = `$tableName`.`{$hmItem->getOnePk()}`");
                    $this->getQueryDataFromModels($hmItem->getManyTable(), $tableName);
                }
            }
        }
        
        if ($model->belongsTo != []) {
            foreach($model->belongsTo as $btItem) {
                $asAlias = isset($mainTable) ? null : " AS `{$seqPref}$tableName:{$btItem->keyField}`";
                $this->select .= ", `{$seqPref}{$tableName}`.`{$btItem->keyField}`$asAlias";
                
                if (!in_array($btItem->getOneTable(), [$mainTable, $tableName, $parentName])) {
                    array_push($this->join, "LEFT JOIN `{$btItem->getOneTable()}` `{$this->seq}__{$btItem->getOneTable()}`
                    ON `{$this->seq}__{$btItem->getOneTable()}`.`{$btItem->getOnePk()}`
                    = `{$seqPref}$tableName`.`{$btItem->getManyFk()}`");
                    $this->getQueryDataFromModels($btItem->getOneTable(), $tableName, $this->seq . '__', true);
                }
                
                $this->seq++;
            }
        }
        if ($model->hasManyAndBelongsTo != []) {
            $seq = $this->seq;
            foreach ($model->hasManyAndBelongsTo as $hmabtItem) {
                $thisTable = $hmabtItem->table->tableName;
                $thisPk = $hmabtItem->table->pk;
                if ($hmabtItem->otherTable != '/'.$parentName) {
                    if (is_object($hmabtItem->manyMany)) {
                            array_push($this->join, "LEFT JOIN `uasys_crossref` ON JSON_CONTAINS(JSON_EXTRACT(`table_value`, '$.$thisTable'), `$thisTable`.`$thisPk`)
                            LEFT JOIN `$thisTable` `{$seq}__related_$thisTable`
                            ON (JSON_CONTAINS(JSON_EXTRACT(`table_value`, '$.$thisTable'), `{$seq}__related_$thisTable`.`$thisPk`) 
                            AND `{$seq}__related_$thisTable`.`$thisPk` <> `$thisTable`.`$thisPk`)");
                            $this->getQueryDataFromModels($tableName, $tableName, $seq . '__related_');
                    } else {
                                $otherTable = null;
                                $otherPk = null;
                            foreach ($hmabtItem->manyMany as $manyManyPart) {
                                if ($manyManyPart->table != $hmabtItem->table->tableName) {
                                    $otherTable = $manyManyPart->table;
                                    $otherPk = $manyManyPart->pk;
                                }
                                print_r($manyManyPart);
                            }
                        array_push($this->join, "LEFT JOIN `uasys_crossref` ON JSON_CONTAINS_PATH(`table_value`, 'ALL','$.$thisTable','$.$otherTable')
                        AND JSON_EXTRACT(table_value, '$.$thisTable') = `$thisTable`.`$thisPk`
                        LEFT JOIN `$otherTable` `{$seq}__$otherTable` ON JSON_EXTRACT(`table_value`, '$.$otherTable') = `{$seq}__$otherTable`.`$otherPk`");
                        $this->getQueryDataFromModels($otherTable, $thisTable, $seq . '__');
                    }

                }
                $seq++;
        }        
        }


    }

    public function getFieldsForQuery($data, $start = false, $seqPref = null) {
        $fields = [];
        foreach ($data->fields as $fname => $field ) {
            $alias = Helper::sqlQuotes($seqPref.$field->sqlAlias);
            $aliasOrNot = $start ? null : " AS $alias";
            $fields[] = Helper::sqlQuotes($seqPref.$field->sqlSelect) . $aliasOrNot;
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