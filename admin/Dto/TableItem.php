<?php namespace Dto;

use Model\Table;

class TableItem
{
    public $id;
    public $tableName;
    public $pk;
    private $data;
    public function __construct(Table $table) {
        
        $this->id = $table->id;
        $this->tableName = $table->tableName;
        $this->pk = $table->pk;
        $this->data = $table->data;
    }

    /**
     * Get the value of data
     */
    public function getData()
    {
        return $this->data;
    }
}
