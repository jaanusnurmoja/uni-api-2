<?php

declare(strict_types=1);

namespace Model;

class HasMany extends RelationSettings
{

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

    /**
    * @param Table $tableIamIn
    */
    public function setTableIamIn(Table $tableIamIn): void {
        parent::setOne($tableIamIn);
    	$this->tableIamIn = $this->one;
    }

    public function getRole() {
        $this->role = $this->mode;
    	return $this->role;
    }

    public function getThisTable() {
        $this->thisTable = $this->oneTable;
    	return $this->thisTable;
    }

    public function getKeyField() {
        $this->keyField = $this->onePk;
    	return $this->keyField;
    }

    public function getOtherKeyField() {
    	return $this->otherKeyField;
    }

    /**
    * @param $otherKeyField
    */
    public function setOtherKeyField($otherKeyField) {
        parent::setManyFk($otherKeyField);
    	$this->otherKeyField = $this->manyFk;
    }
    public function getOtherTable() {
    	return $this->otherTable;
    }


    /**
    * @param $otherTable
    */
    public function setOtherTable($otherTable) {
        parent::setManyTable($otherTable);
    	$this->otherTable = $this->manyTable;
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
        parent::setMany($other);
    	$this->other = $this->many;
    }
}

