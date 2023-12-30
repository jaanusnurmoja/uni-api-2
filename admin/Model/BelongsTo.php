<?php namespace Model;
use Model\RelationSettings;

class BelongsTo extends RelationSettings {

    public Table $tableIamIn;
    public $role;
    public $thisTable;

    public $keyField;
    public $otherKeyField;
    public $otherTable;
    public Table $other;

    public function __construct($id = null) {
        if ($this->id == $id) {
            parent::__construct();
    }
    }
    /**
    * @return Table
    */
    public function getTableIamIn(): Table {
    	return $this->tableIamIn;
    }

    public function setTableIamIn(Table $table) {
        parent::setMany($table);
        $this->tableIamIn = $this->many;
    }


    public function getRole() {
        $this->role = $this->mode;
    	return $this->role;
    }

    public function getThisTable() {
    	return $this->thisTable;
    }

        /**
    * @param $thisTable
    */
    public function setThisTable($thisTable): void {
        parent::setManyTable($thisTable);
    	$this->thisTable = $this->manyTable;
    }

    public function getKeyField() {
        $this->keyField = $this->manyFk;
    	return $this->keyField;
    }

    public function getOtherKeyField() {
    	return $this->otherKeyField;
    }

    public function getOtherTable() {
    	return $this->otherTable;
    }

    /**
    * @return Table
    */
    public function getOther(): Table {
    	return $this->other;
    }

    /**
    * @param Table $other
    */
    public function setOther(Table $other): void {
        parent::setOne($other);
    	$this->other = $this->one;
    }


    /**
    * @param $otherKeyField
    */
    public function setOtherKeyField($otherKeyField) {
        parent::setOnePk($otherKeyField);
    	$this->otherKeyField = $this->onePk;
    }

    /**
    * @param $otherTable
    */
    public function setOtherTable($otherTable) {
        parent::setOneTable($otherTable);
    	$this->otherTable = $this->oneTable;
    }

}
