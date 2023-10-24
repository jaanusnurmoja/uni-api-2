<?php namespace Model;

class RelationDetails
{

    private ?int $id = 0;
    private Relation $relation;
    private $role;
    private $keyField;
    private $hasMany;
    public Table $table;

    public function __construct(?int $id = null) {
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
    public function getHasMany()
    {
        return $this->hasMany;
    }

    /**
     * Set the value of hasMany
     */
    public function setHasMany($hasMany): self
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
}
