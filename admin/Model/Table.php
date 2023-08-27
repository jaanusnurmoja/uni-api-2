<?php namespace Model;

class Table
{
    public $hello;

    public $id;
    public $name;
    public $pk = 'id';
    public $fieldData;
    public $canBelongTo;
    public $canHmabt;
    public Data $data;
    public Relations $belongsTo;
    public Relations $hasMany;
    public Relations $hasManyAndBelongsTo;

    public function __construct($id = null, $hello = false)
    {
        if ($id == $this->id || $hello === true) {
            $this->hello = 'I am an admin.';
            return $this;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPk()
    {
        return $this->pk;
    }

    /**
     * @param $pk
     */
    public function setPk($pk)
    {
        $this->pk = $pk;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @param Data $data
     */
    public function setData(Data $data): void
    {
        $this->data = $data;
    }

    /**
     * @return Relations
     */
    public function getBelongsTo(): Relations
    {
        return $this->belongsTo;
    }

    /**
     * @param Relations $belongsTo
     */
    public function setBelongsTo(Relations $belongsTo): void
    {
        $this->belongsTo = $belongsTo;
    }

    /**
     * @return Relations
     */
    public function getHasMany(): Relations
    {
        return $this->hasMany;
    }

    /**
     * @param Relations $hasMany
     */
    public function setHasMany(Relations $hasMany): void
    {
        $this->hasMany = $hasMany;
    }

    /**
     * @return Relations
     */
    public function getHasManyAndBelongsTo(): Relations
    {
        return $this->hasManyAndBelongsTo;
    }

    /**
     * @param Relations $hasManyAndBelongsTo
     */
    public function setHasManyAndBelongsTo(Relations $hasManyAndBelongsTo): void
    {
        $this->hasManyAndBelongsTo = $hasManyAndBelongsTo;
    }

    public function clean()
    {
        unset($this->belongsTo, $this->hasMany, $this->hasManyAndBelongsTo);
        return $this;
    }

    public function getFieldData() {
    	return $this->fieldData;
    }

    /**
    * @param $fieldData
    */
    public function setFieldData($fieldData) {
    	$this->fieldData = $fieldData;
    }

    public function getCanBelongTo() {
    	return $this->canBelongTo;
    }

    /**
    * @param $canBelongTo
    */
    public function setCanBelongTo($canBelongTo) {
    	$this->canBelongTo = $canBelongTo;
    }

    public function getCanHmabt() {
    	return $this->canHmabt;
    }

    /**
    * @param $canHmabt
    */
    public function setCanHmabt($canHmabt) {
    	$this->canHmabt = $canHmabt;
    }
}