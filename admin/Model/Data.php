<?php namespace Model;

use Common\Model\CreatedModified;

class Data
{
    public $table;
    public $fields = [];
    public CreatedModified $createdModified;

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

    /**
     * Get the value of fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set the value of fields
     */
    public function setFields($fields): self
    {
        $this->fields = $fields;

        return $this;
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