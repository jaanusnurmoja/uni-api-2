<?php namespace Model;

class Relation {
    private $id;
    private Table $table;
    private $fk;
    private Table $belongsTo;
    private $pk;
    private bool $allowHasMany;
    private bool $hasMany;
    private bool $hasManyAndBelongsTo;

    public function __construct($params = null) {
    	if ($params != null) {
        $this->allowHasMany = $params->allowHasMany;
        $this->hasMany = $params->hasMany;
        $this->hasManyAndBelongsTo = $params->hasManyAndBelongsTo;}
    }

    public function getId() {
    	return $this->id;
    }

    /**
    * @param $id
    */
    public function setId($id) {
    	$this->id = $id;
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

    public function getFk() {
    	return $this->fk;
    }

    /**
    * @param $fk
    */
    public function setFk($fk) {
    	$this->fk = $fk;
    }

    /**
    * @return Table
    */
    public function getBelongsTo(): Table {
    	return $this->belongsTo;
    }

    /**
    * @param Table $belongsTo
    */
    public function setBelongsTo(Table $belongsTo): void {
    	$this->belongsTo = $belongsTo;
    }

    public function getPk() {
    	return $this->pk;
    }

    /**
    * @param $pk
    */
    public function setPk($pk) {
    	$this->pk = $pk;
    }

    /**
    * @return bool
    */
    public function getAllowHasMany(): bool {
    	return $this->allowHasMany;
    }

    /**
    * @param bool $allowHasMany
    */
    public function setAllowHasMany(bool $allowHasMany): void {
    	$this->allowHasMany = $allowHasMany;
    }

    /**
    * @return bool
    */
    public function getHasMany(): bool {
    	return $this->hasMany;
    }

    /**
    * @param bool $hasMany
    */
    public function setHasMany(bool $hasMany): void {
    	$this->hasMany = $hasMany;
    }

    /**
    * @return bool
    */
    public function getHasManyAndBelongsTo(): bool {
    	return $this->hasManyAndBelongsTo;
    }

    /**
    * @param bool $hasManyAndBelongsTo
    */
    public function setHasManyAndBelongsTo(bool $hasManyAndBelongsTo): void {
    	$this->hasManyAndBelongsTo = $hasManyAndBelongsTo;
    }
}