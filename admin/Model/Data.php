<?php namespace Model;

class Data {
    private $id;
    private Table $table;
    private $name;
    private $type;
    private $htmlDefaults;

    public function getId() {
    	return $this->id;
    }

    /**
    * @param $id
    */
    public function setId($id) {
    	$this->id = $id;
    }

    public function getName() {
    	return $this->name;
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
    	$this->table = $table;
    }

    /**
    * @param $name
    */
    public function setName($name) {
    	$this->name = $name;
    }

    public function getType() {
    	return $this->type;
    }

    /**
    * @param $type
    */
    public function setType($type) {
    	$this->type = $type;
    }

    public function getHtmlDefaults() {
    	return $this->htmlDefaults;
    }

    /**
    * @param $htmlDefaults
    */
    public function setHtmlDefaults($htmlDefaults) {
    	$this->htmlDefaults = $htmlDefaults;
    }
}