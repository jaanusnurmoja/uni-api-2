<?php namespace Model;

use Model\RelationSettings;

class HasAny extends RelationSettings
{
    public Table $table;
    public $role;
    public $anyAny;
    public $keyField;
    public $thisTable;

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
    public function setTable(Table $table): void {
        parent::setAny($table);
    	$this->table = $this->getAny();
    }

    public function getRole() {
    	return $this->role;
    }

    public function setRole($role) {
        parent::setMode($role);
    	$this->role = $this->getMode();
        return $this;
    }

   public function getAnyAny() {
    	return $this->anyAny;
    }

    /**
    * @param $anyAny
    */
    public function setAnyAny($anyAny) {
        parent::setAnyAny($anyAny);
    	$this->anyAny = parent::getAnyAny();
        return $this;
    }

    /**
     * Get the value of anyPk
     */
    public function getKeyField() {
        return $this->keyField;
    }

    /**
     * Set the value of anyPk
     */
    public function setKeyField($keyField) {
        parent::setAnyPk($keyField);
        $this->keyField = $this->getAnyPk();
        return $this;
    }

    /**
     * Get the value of anyTable
     */
    public function getThisTable() {
        return $this->thisTable;
    }

    /**
     * Set the value of anyTable
     */
    public function setThisTable($thisTable) {
        parent::setAnyTable($thisTable);
        $this->thisTable = $this->getAnyTable();
        return $this;
    }
}
