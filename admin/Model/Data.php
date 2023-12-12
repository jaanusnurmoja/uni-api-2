<?php namespace Model;

use Common\Model\DataCreatedModified;

class Data
{
    public $table;
    public $fields = [];
    public DataCreatedModified $dataCreatedModified;

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
    public function getDataCreatedModified(): DataCreatedModified
    {
        return $this->dataCreatedModified;
    }

    /**
     * Set the value of createdModified
     */
    public function setDataCreatedModified(DataCreatedModified $dataCreatedModified): self
    {
        $this->dataCreatedModified = $dataCreatedModified;

        return $this;
    }
}
