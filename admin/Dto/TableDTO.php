<?php namespace Dto;

use Common\Helper;
use Controller\Table as ControllerTable;
use \Model\Table;
use Service\Read;
use user\model\User;

/**
 * Põhimudeli töötleja, sh on seosed teiste tabelitega jaotatud vastavalt tüübile
 */
class TableDTO
{
    public $id;
    public $tableName;
    public $pk;
    public $data;
    public $createdModified;
    public $belongsTo = [];
    public $hasMany = [];
    public $hasManyAndBelongsTo = [];
    private $sql;
    private $joins = [];
    protected $model;

    public function __construct(Table $model)
    {
        $this->model = $model;
        $this->id = $model->getId() ? $model->getId() : null;
        $this->tableName = $model->getTableName() ? $model->getTableName() : null;
        $this->pk = $model->getPk() ? $model->getPk() : null;
        $this->data = $model->getData() ? $model->getData() : null;
        $this->createdModified = $model->getCreatedModified() ? $model->getCreatedModified() : null;
        unset($this->data->table);
        //$this->makeSql();
        foreach ($model->getRelationDetails() as $rdRow) {

            unset($rdRow->table);
            if ($rdRow->getRole() == 'belongsTo') {

                array_push($this->belongsTo, $rdRow);
            }
            if ($rdRow->getRole() == 'hasMany') {
                array_push($this->hasMany, $rdRow);
            }
            if ($rdRow->getRole() == 'hasManyAndBelongsTo') {
                array_push($this->hasManyAndBelongsTo, $rdRow);
            }
        }
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of name
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set the value of name
     */
    public function setTableName($tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get the value of pk
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * Set the value of pk
     */
    public function setPk($pk): self
    {
        $this->pk = $pk;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the value of createdModified
     */
    public function getCreatedModified()
    {
        return $this->createdModified;
    }

    /**
     * Set the value of createdModified
     */
    public function setCreatedModified($createdModified): self
    {
        $this->createdModified = $createdModified;

        return $this;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function getBelongsTo()
    {
        return $this->belongsTo;
    }

    /**
     * @param $belongsTo
     */
    public function setBelongsTo($belongsTo)
    {
        $this->belongsTo = $belongsTo;
    }

    public function getHasMany()
    {
        return $this->hasMany;
    }

    /**
     * @param $hasMany
     */
    public function setHasMany($hasMany)
    {
        $this->hasMany = $hasMany;
    }

    public function getHasManyAndBelongsTo()
    {
        return $this->hasManyAndBelongsTo;
    }

    /**
     * @param $hasManyAndBelongsTo
     */
    public function setHasManyAndBelongsTo($hasManyAndBelongsTo)
    {
        $this->hasManyAndBelongsTo = $hasManyAndBelongsTo;
    }

    private function getModel() {
        return $this->model;
    }

    public function getSql() {
    	return $this->sql;
    }

    /**
    * @param $sql
    */
    public function setSql($sql) {
    	$this->sql = $sql;
    }

    public function makeSql() {
        $model = $this->getModel();
        $sql = 'SELECT ';
        $sql .= $this->queryFields($model, $this->tableName);
        $sql .= " FROM $this->tableName";
        $sql .= implode('
         ', $this->joins);
        return $sql;
        //$this->setSql($sql);
    }
    public function queryFields(
            $model = null,
            $mainTable = null,
            $parentTable = null,
            $i = null,
            $parentRelDetail = null,
            $parentModel = null
        ) {
        $read = new Read();
        $mainTablePref = '';
        $mainTableAlias = '';
        $sql = '';
        $joinStmt = [];
        $comma = '';

        if ($mainTable) {
            if ($parentTable) {
                $mainTablePref = $mainTable . ':';
                $mainTableAlias = $mainTable . '`.`';
                $comma =', ';
            } 
        $pref = !isset($i) ? '' : "{$i}::";
       // echo "$i parent: $parentTable, main: $mainTable";
        //echo '<hr>';

        // väljade loomise tingimuste algus
        /* 
        if (empty($parentModel) 
        || ((isset($parentModel->relationDetails[$i-1]) && $parentModel->relationDetails[$i-1]->otherTable == $mainTable)
        && (isset($parentModel->relationDetails[$i]) && $parentModel->relationDetails[$i]->otherTable != $mainTable))    )
        {
            */
            if(empty($parentModel) || (isset($parentModel->relationDetails) && next($parentModel->relationDetails))) {
            $sql .= "$comma'$model->pk' AS `$pref{$mainTablePref}pk_name`, 
            `$pref{$mainTableAlias}{$model->pk}` AS `$pref{$mainTablePref}pk_value`, 
            `$pref{$mainTableAlias}{$model->tableName}` AS `$pref{$mainTablePref}table_name`
            ";
            foreach ($model->data->fields as $dtoField => $dtoValue) {
                $dtofSqlName = Helper::uncamelize($dtoField);
                $sql .= ", `$pref{$mainTableAlias}$dtofSqlName` AS `$pref{$mainTablePref}$dtofSqlName`
                ";
            }
            if (!isset($u)) $u = 0;
            foreach($model->data->dataCreatedModified as $cmKey => $cmValue) {
                $cmSqlName = Helper::uncamelize($cmKey);
                $sql .= ", `$pref{$mainTableAlias}$cmSqlName` AS `$pref{$mainTablePref}$cmSqlName`";
                if ($cmValue instanceof User) {
                    $sql .= ", $u::users.id AS $u::users:id, $u::users.user_name AS $u::users:user_name
                    ";
                    $u++;
                }
            }
        }
            /*
        }
        */
        // välja loomise tingimuste lõpp
            
            if (!empty($model->getRelationDetails())){
                
                if (isset($parentRelDetail->role) && $parentRelDetail->role == 'belongsTo'){
                    unset($model->relationDetails);
                }
                if (isset($model->relationDetails)) {
                foreach($model->relationDetails as $n => $relDetail) {
                    $pref = "{$n}::";
                        
                    $otherTable = Helper::uncamelize($relDetail->otherTable);
                    $dto = $read->getTables(null, ['table_name' => $otherTable]);
                    if ($relDetail->role == 'belongsTo') {
                        if (!empty($parentTable) && $otherTable == $parentTable) {
                            
                                array_push($this->joins, " LEFT JOIN $mainTable $pref{$mainTable} ON $pref{$mainTable}.{$relDetail->keyField} = $parentTable.{$parentModel->pk}");
                            
                        } else {
                            if (!in_array($otherTable, [$mainTable, $parentTable])) {
                                array_push($this->joins, " LEFT JOIN $otherTable $pref{$otherTable} ON $pref{$otherTable}.{$dto->pk} = $mainTable.{$relDetail->keyField}");
                            }
                        }

                    }
                    
                        $sql .= $this->queryFields(
                            $dto->getModel(),
                            $dto->getModel()->tableName, 
                            $mainTable,
                            $n,
                            $relDetail,
                            $model
                        );
                        //$i++;
                    }
                }
            }
    }

    //print_r( $this->joins);

        return $sql;
    }

    //public function joins($mainTable, $mainKey, $otherTable, $otherKey, $i) {}


}