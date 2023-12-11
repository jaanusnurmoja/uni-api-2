<?php namespace Model;
class Data
{
    public $table;
    public $fields = [];
    use Reuse\CreatedModifiedWhoWhen;

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
}