<?php namespace Api\Model;
use Model\Data;

use stdClass;

/**
 * class_alias('Entity', $className)
 * new $className();
 * https: //urielwilson.com/how-to-dynamically-generate-classes-at-runtime-in-php/

 */
class Entity
{
    public $table;
    public $pk;
    public Data $data;
    public $belongsTo = [];
    public $hasMany = [];
    public $hasManyAndBelongsTo = [];

    public function __construct($table = null, $pkValue = 0, $pkName = null)
    {
        if (!empty($table) && $table == $this->table && (empty($pkValue) || $this->pk['value'] == $pkValue) && (empty($pkName) || $pkName == $this->pk['name'])) {
            return $this;
        } else {
            $this->table = $table;
            /*
        $this->setPk(['name' => $pkName,
        'value' => $pkValue]);
         */
        }
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
    public function setPk($pk = []): self
    {
        $this->pk = $pk;

        return $this;
    }

    /**
     * Get the value of data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get the value of belongsTo
     */
    public function getBelongsTo()
    {
        return $this->belongsTo;
    }

    /**
     * Set the value of belongsTo
     */
    public function setBelongsTo($belongsTo): self
    {
        /*
        $belongsToTable, $belongsTo = [
        'keyField' => null,
        'parentKey' => null,
        'label' => null,
        'table' => null,
        'data' => null]
         */

        foreach ($belongsTo as $i => $btTable) {
            unset($btTable->createdModified);
            $belongsTo[$i] = $btTable;
            //unset($belongsTo[$i]);
        }
        $this->belongsTo = $belongsTo;

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
        foreach ($hasMany as $i => $hmTable) {
            unset($hmTable->createdModified);
            $hasMany[$i] = $hmTable;
        }
        $this->hasMany = $hasMany;

        return $this;
    }

    /**
     * Get the value of hasManyAndBelongsTo
     */
    public function getHasManyAndBelongsTo()
    {
        return $this->hasManyAndBelongsTo;
    }

    /**
     * Set the value of hasManyAndBelongsTo
     */
    public function setHasManyAndBelongsTo($table1, $value1, $value2, $table2 = null): self
    {
        $hasManyAndBelongsTo = new stdClass;
        if (empty($table2) || $table2 == $table1) {
            $hasManyAndBelongsTo->ids[$table1] = [$value1, $value2];
        } else {
            $hasManyAndBelongsTo->ids[$table1] = $value1;
            $hasManyAndBelongsTo->ids[$table2] = $value2;
        }
        $hasManyAndBelongsTo->item[$table2] = $value2;
        array_push($this->hasManyAndBelongsTo, $hasManyAndBelongsTo);

        return $this;
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
}
