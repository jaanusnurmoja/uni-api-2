<?php
namespace Model;

class Field {
    private $id;
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