<?php namespace Model;

use Common\Model\CreatedModified;

/**
 * Andmeseose täiendavad üksikasjad
 *
 * @todo osa üksikasju võiksid tegelikult pärineda Relation relation mudelist
 *
 */
class RelationSettings
{

    public ?int $id = 0;
    public Relation $relation;
    public $role;
    public $keyField;
    public bool $hasMany = false;
    public Table $table;
    public $otherTable;
    public $mode;
    public Table $many;
    public $manyTable;
    public $manyFk;
    public $manyMany;
    public $oneAny;
    public $onePk;
    public $oneTable;
    public Table $one;
    public CreatedModified $createdModified;

    public function __construct(?int $id = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }
        if (isset($this->id) && is_numeric($this->id)) {
            return $this;
        }
    }
    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of relation
     */
    public function getRelation(): Relation
    {
        return $this->relation;
    }

    /**
     * Set the value of relation
     */
    public function setRelation(Relation $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Get the value of role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the value of role
     */
    public function setRole($role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of keyField
     */
    public function getKeyField()
    {
        return $this->keyField;
    }

    /**
     * Set the value of keyField
     */
    public function setKeyField($keyField): self
    {
        $this->keyField = $keyField;

        return $this;
    }

    /**
     * Get the value of hasMany
     */
    public function getHasMany(): bool
    {
        return $this->hasMany;
    }

    /**
     * Set the value of hasMany
     */
    public function setHasMany(bool $hasMany): self
    {
        $this->hasMany = $hasMany;

        return $this;
    }

    /**
     * Get the value of table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * Set the value of table
     */
    public function setTable(Table $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function getOtherTable()
    {
        return $this->otherTable;
    }

    /**
     * @param $otherTable
     */
    public function setOtherTable($otherTable)
    {
        $this->otherTable = $otherTable;
    }

    public function getMode() {
    	return $this->mode;
    }

    /**
    * @param $mode
    */
    public function setMode($mode) {
    	$this->mode = $mode;
    }

    /**
    * @return Table
    */
    public function getMany(): Table {
    	return $this->many;
    }

    /**
    * @param Table $many
    */
    public function setMany(Table $many): void {
    	$this->many = $many;
    }

    public function getManyTable() {
    	return $this->manyTable;
    }

    /**
    * @param $manyTable
    */
    public function setManyTable($manyTable) {
    	$this->manyTable = $manyTable;
    }

    public function getManyFk() {
    	return $this->manyFk;
    }

    /**
    * @param $manyFk
    */
    public function setManyFk($manyFk) {
    	$this->manyFk = $manyFk;
    }

    public function getManyMany() {
    	return $this->manyMany;
    }

    /**
    * @param $manyMany
    */
    public function setManyMany($manyMany) {
    	$this->manyMany = $manyMany;
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

    public function getOnePk() {
    	return $this->onePk;
    }

    /**
    * @param $onePk
    */
    public function setOnePk($onePk) {
    	$this->onePk = $onePk;
    }

    public function getOneTable() {
    	return $this->oneTable;
    }

    /**
    * @param $oneTable
    */
    public function setOneTable($oneTable) {
    	$this->oneTable = $oneTable;
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

    /**
    * @return CreatedModified
    */
    public function getCreatedModified(): CreatedModified {
    	return $this->createdModified;
    }

    /**
    * @param CreatedModified $createdModified
    */
    public function setCreatedModified(CreatedModified $createdModified) {
    	$this->createdModified = $createdModified;
        return $this;
    }
}