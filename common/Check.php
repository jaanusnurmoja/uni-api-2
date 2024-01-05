<?php namespace Common;

class Check {

    public $hasMany = [];

    public function makeHasManyList($tableName) {
        if (!in_array($tableName, $this->hasMany)) {
            array_push($this->hasMany, $tableName);
            return true;
        }
    }
    
}