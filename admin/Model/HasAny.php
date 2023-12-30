<?php namespace Model;

use Model\RelationSettings;

class HasAny extends RelationSettings
{
    public Table $table;
    public $role;
    public $anyAny;
    public $anyPk;
    public $anyTable;

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
    }

    /**
     * Get the value of anyPk
     */
    public function getAnyPk() {
        return $this->anyPk;
    }

    /**
     * Set the value of anyPk
     */
    public function setAnyPk($anyPk): parent {
        $this->anyPk = $this->getAnyPk();
        return $this;
    }

    /**
     * Get the value of anyTable
     */
    public function getAnyTable() {
        return $this->anyTable;
    }

    /**
     * Set the value of anyTable
     */
    public function setAnyTable($anyTable): parent {
        $this->anyTable = $anyTable;
        return $this;
    }
}
