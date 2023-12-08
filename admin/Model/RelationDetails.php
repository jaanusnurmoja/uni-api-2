<?php namespace Model;

use Common\Model\CreatedModified;

class RelationDetails
{

    public ?int $id = 0;
    public Relation $relation;
    public $role;
    public $keyField;
    public bool $hasMany = false;
    public Table $table;
    public $otherTable;
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

    /**
     * Get the value of createdModified
     */
    public function getCreatedModified(): CreatedModified
    {
        return $this->createdModified;
    }

    /**
     * Set the value of createdModified
     */
    public function setCreatedModified(CreatedModified $createdModified): self
    {
        $this->createdModified = $createdModified;

        return $this;
    }
}
