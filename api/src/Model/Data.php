<?php 
namespace Api\Model;

#[\AllowDynamicProperties]
class Data
{
    private $table;

    public function __construct($table = null) {
        $this->table = $table;
    }
    
    public function __set($name, $value = null) {
        $this->$name = $value;
    }

    public function __get($name = null)
    {
        if ($name === null) {
            return $this;
        } else {
            return $this->$name;
        }
    }



    /**
     * Get the value of table
     */
    public function getTable()
    {
        return $this->table;
    }
}