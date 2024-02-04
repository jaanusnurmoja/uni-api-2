<?php namespace Api\Model;


/**
 * class_alias('Entity', $className)
 * new $className();
 * https: //urielwilson.com/how-to-dynamically-generate-classes-at-runtime-in-php/

 */
#[\AllowDynamicProperties]
class Entity
{
    public $table;
    public $pk;
    public object $data;
    public ?object $createdModified;
    public ?array $belongsTo;
    public ?array $hasMany;
    public ?array $hasManyAndBelongsTo;

    public function __construct( $table = null) {
        $this->table = $table;
    }

    /**
     * Get the value of table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set the value of table
     */
    public function setTable($table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the value of pk
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * Set the value of pk
     */
    public function setPk($pk): self
    {
        $this->pk = $pk;

        return $this;
    }

    /**
     * Get the value of data
     */
    public function getData(): ?object
    {
        return $this->data;
    }

    /**
     * Set the value of data
     */
    public function setData(?object $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the value of createdModified
     */
    public function getCreatedModified(): ?object
    {
        return $this->createdModified;
    }

    /**
     * Set the value of createdModified
     */
    public function setCreatedModified(?object $createdModified): self
    {
        $this->createdModified = $createdModified;

        return $this;
    }

    /**
     * Get the value of belongsTo
     */
    public function getBelongsTo(): ?array
    {
        return $this->belongsTo;
    }

    /**
     * Set the value of belongsTo
     */
    public function setBelongsTo(?array $belongsTo): self
    {
        $this->belongsTo = $belongsTo;

        return $this;
    }

    /**
     * Get the value of hasMany
     */
    public function getHasMany(): ?array
    {
        return $this->hasMany;
    }

    /**
     * Set the value of hasMany
     */
    public function setHasMany(?array $hasMany): self
    {
        $this->hasMany = $hasMany;

        return $this;
    }

    /**
     * Get the value of hasManyAndBelongsTo
     */
    public function getHasManyAndBelongsTo(): ?array
    {
        return $this->hasManyAndBelongsTo;
    }

    /**
     * Set the value of hasManyAndBelongsTo
     */
    public function setHasManyAndBelongsTo(?array $hasManyAndBelongsTo): self
    {
        $this->hasManyAndBelongsTo = $hasManyAndBelongsTo;

        return $this;
    }
}