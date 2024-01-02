<?php namespace Model;

use Common\Model\CreatedModified;
use Dto\TableDTO;
use Dto\TableItem;

/**
 * Andmeseose täiendavad üksikasjad
 *
 * @todo osa üksikasju võiksid tegelikult pärineda Relation relation mudelist
 *
 */
class RelationSettings
{

    public ?int $id;
    private Relation $relation;
    public $role;
    public bool $hasMany = false;
    public TableItem $table;
    public $tableId;
    public $keyField;
    public $otherKeyField;
    public $otherTable;
    public $mode;
    public Table $many;
    public $manyId;
    public $manyTable;
    public $manyFk;
    public $manyMany;
    public $manyManyIds;
    public $anyId;
    public $anyAny;
    public ?Table $any;
    public $anyTable;
    public $anyPk;
    public $oneAny;
    public $onePk;
    public $oneTable;
    public $oneId;
    public Table $one;
    public CreatedModified $createdModified;

    public function __construct(?int $id = 0)
    {
        $this->id = $id;

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
    public function getTable(): TableItem
    {
        return $this->table;
    }

    /**
     * Set the value of table
     */
    public function setTable(TableItem $table): self
    {
        $this->table = $table;

        return $this;
    }

     /**
     * Get the value of tableId
     */
    public function getTableId() {
        return $this->tableId;
    }
       /**
     * Set the value of tableId
     */
    public function setTableId($tableId): self {
        $this->tableId = $tableId;
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
        return $this;
    }

    public function getMode() {
    	return $this->mode;
    }

    /**
    * @param $mode
    */
    public function setMode($mode) {
        $this->mode = $mode;
        return $this;
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
    public function setMany(Table $many) {
    	$this->many = $many;
        return $this;
    }

    public function getManyTable() {
    	return $this->manyTable;
    }

    /**
    * @param $manyTable
    */
    public function setManyTable($manyTable) {
    	$this->manyTable = $manyTable;
        return $this;
    }

    public function getManyFk() {
    	return $this->manyFk;
    }

    /**
    * @param $manyFk
    */
    public function setManyFk($manyFk) {
    	$this->manyFk = $manyFk;
        return $this;
    }

    public function getManyMany() {
    	return $this->manyMany;
    }

    /**
    * @param $manyMany
    */
    public function setManyMany($manyMany) {
    	$this->manyMany = $manyMany;
        return $this;
    }

    public function getOneAny() {
    	return $this->oneAny;
    }

    /**
    * @param $oneAny
    */
    public function setOneAny($oneAny) {
    	$this->oneAny = $oneAny;
        return $this;
    }

    public function getOnePk() {
    	return $this->onePk;
    }

    /**
    * @param $onePk
    */
    public function setOnePk($onePk) {
    	$this->onePk = $onePk;
        return $this;
    }

    public function getOneTable() {
    	return $this->oneTable;
    }

    /**
    * @param $oneTable
    */
    public function setOneTable($oneTable) {
    	$this->oneTable = $oneTable;
        return $this;
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
    public function setOne(Table $one) {
    	$this->one = $one;
        return $this;
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

    public function getManyManyIds() {
    	return $this->manyManyIds;
    }

    /**
    * @param $manyManyIds
    */
    public function setManyManyIds($manyManyIds) {
    	$this->manyManyIds = $manyManyIds;
        return $this;
    }

    /**
     * Get the value of anyAny
     */
    public function getAnyAny() {
        return $this->anyAny;
    }

    /**
     * Set the value of anyAny
     */
    public function setAnyAny($anyAny): self {
        $this->anyAny = $anyAny;
        return $this;
    }

    /**
     * Get the value of any
     *
     * @return ?Table
     */
    public function getAny(): ?Table {
        return $this->any;
    }

    /**
     * Set the value of any
     *
     * @param ?Table $any
     *
     * @return self
     */
    public function setAny(?Table $any): self {
        $this->any = $any;
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
    public function setAnyTable($anyTable): self {
        $this->anyTable = $anyTable;
        return $this;
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
    public function setAnyPk($anyPk): self {
        $this->anyPk = $anyPk;
        return $this;
    }

    /**
     * Get the value of manyId
     */
    public function getManyId() {
        return $this->manyId;
    }

    /**
     * Set the value of manyId
     */
    public function setManyId($manyId): self {
        $this->manyId = $manyId;
        return $this;
    }

    /**
     * Get the value of anyId
     */
    public function getAnyId() {
        return $this->anyId;
    }

    /**
     * Set the value of anyId
     */
    public function setAnyId($anyId): self {
        $this->anyId = $anyId;
        return $this;
    }

    /**
     * Get the value of oneId
     */
    public function getOneId() {
        return $this->oneId;
    }

    /**
     * Set the value of oneId
     */
    public function setOneId($oneId): self {
        $this->oneId = $oneId;
        return $this;
    }

    /**
     * Get the value of otherKeyField
     */
    public function getOtherKeyField() {
        return $this->otherKeyField;
    }

    /**
     * Set the value of otherKeyField
     */
    public function setOtherKeyField($otherKeyField): self {
        $this->otherKeyField = $otherKeyField;
        return $this;
    }

    public function rewriteMode($mode, $tableId, $manyId, $oneId) {
        //hasMany__one_many__belongs_to
        $currentMode = explode('__one_many__', $mode);
        $this->setMode($currentMode[0]);
        if ($tableId == $oneId) {
            $this->setMode($currentMode[1]);
        } else {
            $this->setMode($currentMode[0]);
        }
        return $this;
    }

    /**
     * @param $otherTable
     */
    public function setTablesAndKeyFields()
    {
        $manyMany = json_decode($this->manyMany);
        if (!empty($manyMany)) {
            if (is_array($manyMany)) {
                foreach($manyMany as $mm) {
                    if ($mm->id != $this->tableId) {
                        $this->setKeyField($mm->pk);
                        $this->setOtherKeyField($mm->pk);
                        $this->setOtherTable($mm->table);
                    }
                }
            } else {
                if ($manyMany->id == $this->tableId) {
                    $this->setKeyField($manyMany->pk);
                    $this->setOtherKeyField($manyMany->pk);
                    $this->setOtherTable($manyMany->table);
                }
            }
        } else {
            if (!empty($this->anyId)) {
                $this->setKeyField($this->anyPk);
                $this->setOtherKeyField($this->anyPk);
                $this->setOtherTable($this->anyTable);
            } else {
                if (!empty($this->oneId) && !empty($this->manyId)) {
                    if ($this->oneId == $this->tableId) {
                        $this->setKeyField($this->manyFk);
                        $this->setOtherKeyField($this->onePk);
                        $this->setOtherTable($this->oneTable);        
                    }
                    if ($this->manyId == $this->tableId) {
                        $this->setKeyField($this->onePk);
                        $this->setOtherKeyField($this->manyFk);
                        $this->setOtherTable($this->manyTable);        
                    }
                }
            }
        }
        return $this;
    }

}