<?php

declare(strict_types=1);

namespace Model;
use Model\RelationSettings;

class HasAny extends RelationSettings
{
    public Table $one;
    public $role;
    public $oneAny;

    public function __construct($id = null) {
        if ($this->id == $id) {
            parent::__construct();
        }
    }
    /**
    * @return Table
    */
    public function getOne(): Table {
    	return $this->one;
    }

    /**
    * @param Table $one
    */
    public function setOne(Table $one): void {
    	$this->one = $one;
    }

    public function getRole() {
        $this->role = $this->mode;
    	return $this->role;
    }

    public function getOneAny() {
    	return $this->oneAny;
    }

    /**
    * @param $oneAny
    */
    public function setOneAny($oneAny) {
    	$this->oneAny = $oneAny;
    }
}
