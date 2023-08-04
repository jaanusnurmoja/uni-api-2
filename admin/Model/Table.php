<?php namespace Model;

class Table {
    public $hello;

    private $id;
    private $name;
    private $pk = 'id';
    private Data $data;
    private Relations $belongsTo;
    private Relations $hasMany;
    private Relations $hasManyAndBelongsTo;
    
    public function __construct($table = null)
    {
        $this->hello = 'I am an admin.';
        if ($table && $table == $this->name) {
            return $this;
        }
    }

    public function getId() {
    	return $this->id;
    }

    /**
    * @param $id
    */
    public function setId($id) {
    	$this->id = $id;
    }

    public function getName() {
    	return $this->name;
    }

    /**
    * @param $name
    */
    public function setName($name) {
    	$this->name = $name;
    }

    public function getPk() {
    	return $this->pk;
    }

    /**
    * @param $pk
    */
    public function setPk($pk) {
    	$this->pk = $pk;
    }

    /**
    * @return Data
    */
    public function getData(): Data {
    	return $this->data;
    }

    /**
    * @param Data $data
    */
    public function setData(Data $data): void {
    	$this->data = $data;
    }

    /**
    * @return Relations
    */
    public function getBelongsTo(): Relations {
    	return $this->belongsTo;
    }

    /**
    * @param Relations $belongsTo
    */
    public function setBelongsTo(Relations $belongsTo): void {
    	$this->belongsTo = $belongsTo;
    }

    /**
    * @return Relations
    */
    public function getHasMany(): Relations {
    	return $this->hasMany;
    }

    /**
    * @param Relations $hasMany
    */
    public function setHasMany(Relations $hasMany): void {
    	$this->hasMany = $hasMany;
    }

    /**
    * @return Relations
    */
    public function getHasManyAndBelongsTo(): Relations {
    	return $this->hasManyAndBelongsTo;
    }

    /**
    * @param Relations $hasManyAndBelongsTo
    */
    public function setHasManyAndBelongsTo(Relations $hasManyAndBelongsTo): void {
    	$this->hasManyAndBelongsTo = $hasManyAndBelongsTo;
    }
}