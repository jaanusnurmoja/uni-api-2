<?php

declare(strict_types=1);

namespace Model;

class HasManyAndBelongsTo extends RelationSettings
{
    private Table $tableIamIn;
    public $role;
    public $manyMany;

    
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
    	$this->tableIamIn = $tableIamIn;
    }

    public function getRole() {
        $this->role = $this->mode;
    	return $this->role;
    }

    /**
    * @param $role
    */
    /*
    public function setRole($role) {
    	$this->role = $role;
    }
    */
    public function getManyMany() {
    	return $this->manyMany;
    }

    /**
    * @param $manyMany
    */
    public function setManyMany($manyMany) {
    	$this->manyMany = $manyMany;
    }
}
