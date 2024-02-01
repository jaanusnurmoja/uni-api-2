<?php 
namespace Api\Model;

#[\AllowDynamicProperties]
class Data
{
    private $table;

    public function __construct($table = null, $data = null, $excludedFields = []) {
        $this->table = $table;
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!in_array($key, $excludedFields)) {
                    $this->$key = $value;
                }
            }
        }

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