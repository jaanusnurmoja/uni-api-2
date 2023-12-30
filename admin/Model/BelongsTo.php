<?php namespace Model;
use Model\RelationSettings;

class BelongsTo extends RelationSettings {

    public Table $table;
    public $role;
    public $thisTable;

    public $keyField;
    public $otherKeyField;
    public $otherTable;
    public Table $other;


    public function __construct($id = null) {
            parent::__construct($id);
        
    }
    /**
    * @return Table
    */
    public function getTable(): Table {
    	return $this->table;
    }

    /**
    * @param Table $table
    */
    public function setTable(Table $table) {
        parent::setMany($table);
    	$this->table = $this->getMany();
        return $this;
    }


    public function getRole() {
        $this->role = $this->getMode();
    	return $this->role;
    }

    public function setRole($role) {
        parent::setMode($role);
        $this->role = $this->getMode();
        return $this;
    }

   public function getThisTable() {
    	return $this->thisTable;
    }

    /**
    * @param $thisTable
    */
    public function setThisTable($thisTable) {
        parent::setManyTable($thisTable);
    	$this->thisTable = $this->getManyTable();
        return $this;
    }

    public function getKeyField() {
    	return $this->keyField;
    }

    public function setKeyField($keyField) {
        parent::setManyFk($keyField);
        $this->keyField = $this->getManyFk();
    	return $this;
    }

    public function getOtherKeyField() {
    	return $this->otherKeyField;
    }

    public function setOtherKeyField($otherKeyField) {
        parent::setOnePk($otherKeyField);
        $this->otherKeyField = $this->getOnePk();
    	return $this;
    }

    public function getOtherTable() {
    	return $this->otherTable;
    }

    /**
    * @param $otherTable
    */
    public function setOtherTable($otherTable) {
        parent::setOneTable($otherTable);
    	$this->otherTable = $this->getOneTable();
        return $this;
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
    public function setOther(Table $other) {
        parent::setOne($other);
    	$this->other = $this->getOne();
        return $this;
    }
}
