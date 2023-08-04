<?php namespace Model;

use \Ds\Collection;
class Relations
{
    private Table $table;
    private $relations = [];


    public function getRelations() {
    	return $this->relations;
    }

    /**
    * @param $relations
    */
    public function setRelations($relations, $params) {
        foreach ($relations as $key => $relation) {
            $r = new Relation($params);
            $r->setId($relation->id);
            $r->setTable($this->table);
            $r->setFk($relation->fk);
            $r->setBelongsTo($relation->belongsTo);
            $r->setPk($relation->pk);
            
            $this->relations[] = $relation;
        }
    }

    /**
    * @return Table
    */
    public function getTable(): Table {
    	return $this->table;
    }

    /**
    * @param Table $table
    */
    public function setTable(Table $table): void {
    	$this->table = $table;
    }
}