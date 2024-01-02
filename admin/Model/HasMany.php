<?php namespace Model;

class HasMany extends RelationSettings
{

    public Table $table;
    public $role;
    public $thisTable;
    public $keyField;
    public $otherKeyField;
    public $otherTable;
    public Table $other;



    public function __construct($id = null) {

        //if ($this->id == $id) {
            parent::__construct($id);
        //}
    }

    /**
    * @return Table
    */
    public function getTable(): Table {
        if (!$this->table) {
            $this->table == parent::getOne();
        }
    	return $this->table;
    }

    /**
    * @param Table $table
    */
    public function setTable(Table $table): void {
        parent::setOne($table);
    	$this->table = $this->getOne();
    }

    /**
    * @return string
    */
    public function getRole() {
        if (!$this->role) {
            $this->role = parent::getMode();
        }
    	return $this->role;
    }

    /**
    * @param string $role
    */
    public function setRole($role) {
        parent::setMode($role);
    	$this->role = $this->getMode();
        return $this;
    }

    /**
    * @return string
    */
    public function getThisTable() {
    	return $this->thisTable;
    }

     /**
    * @param string $thisTable
    */
    public function setThisTable($thisTable) {
        parent::setOneTable($thisTable);
    	$this->thisTable = $this->getOneTable();
        return $this;
    }


    /**
    * @return string
    */
    public function getKeyField() {
    	return $this->keyField;
    }

    /**
    * @param string $keyField
    */
    public function setKeyField($keyField) {
        parent::setOnePk($keyField);
    	$this->keyField = $this->getOnePk();
        return $this;
    }

    /**
    * @return string
    */
    public function getOtherKeyField() {
    	return $this->otherKeyField;
    }

    /**
    * @param $otherKeyField
    */
    public function setOtherKeyField($otherKeyField) {
        parent::setManyFk($otherKeyField);
    	$this->otherKeyField = $this->getManyFk();
        return $this;
    }
     /**
    * @return string
    */
    public function getOtherTable() {
    	return $this->otherTable;
    }

    /**
    * @param $otherTable
    */
    public function setOtherTable($otherTable) {
        parent::setManyTable($otherTable);
    	$this->otherTable = $this->getManyTable();
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
        parent::setMany($other);
    	$this->other = $this->getMany();
        return $this;
    }

}

