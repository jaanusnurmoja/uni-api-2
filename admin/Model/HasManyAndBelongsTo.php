<?php namespace Model;

class HasManyAndBelongsTo extends RelationSettings
{
    public Table $tableIamIn;
    public $role;
    public $manyMany;

    
    public function __construct($id = null) {
            parent::__construct($id);
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
    public function setTableIamIn(Table $tableIamIn){
    	$this->tableIamIn = $tableIamIn;
        return $this;
    }

    public function getRole() {
    	return $this->role;
    }

    /**
    * @param $role
    */
    
    public function setRole($role) {
        parent::setMode($role);
    	$this->role = $this->getMode();
    }

    public function getManyMany() {
    	return $this->manyMany;
    }

    /**
    * @param $manyMany
    */
    public function setManyMany($manyMany) {
        parent::setManyMany($manyMany);
    	$this->manyMany = parent::getManyMany();
        return $this;
    }
}
