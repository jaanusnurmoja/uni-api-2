<?php namespace Model;

use Common\Model\DataCreatedModified;
use Dto\TableDTO;
use Dto\TableItem;

class Data
{
    public TableItem $table;
    public $fields = [];
    public DataCreatedModified $dataCreatedModified;

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
