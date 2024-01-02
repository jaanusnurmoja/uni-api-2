<?php namespace Dto;

use Model\Table;

class TableItem
{
    public $id;
    public $tableName;
    public $pk;
    //public $data;
    public function __construct(Table $table) {
        
        $this->id = $table->id;
        $this->tableName = $table->tableName;
        $this->pk = $table->pk;
        //$this->data = $table->data;
    }
}
