<?php namespace Service;

use \Model\Table;
use \Service\Read;
use \Service\TableRepository;

class TableImpl implements TableRepository
{
    public Read $read;
    public array $tables;
    public Table $table;

    public function __construct(array $tables = [], ?Table $table = null)
    {
        if (!empty($tables)) {
            $this->tables = $tables;
        } else {
            if (empty($this->read)) {
                $this->read = new Read();
                $this->tables = $this->read->getTables();
            }
        }
        if (!empty($table)) {
            $this->table = $table;
        } else {
            if (empty($this->table)) {
                $this->table = new Table();
            }

        }
    }

    //public function add(Table $table) {}
    //public function remove(Table $table) {}
    //public function get(Table $table) {}

    public function findAll()
    {
        return $this->tables;
    }
    public function findById(int $id)
    {
        foreach ($this->tables as $table) {
            if ($table->getId() === $id) {
                return $table;
            }
        }
    }
    public function findBy(string $name, string $value)
    {}
}