<?php namespace Model;

class Table
{

    public $id;
    public $name;
    public $pk = 'id';
    public Data $data;
    public array $relationDetails = [];

    public function __construct($id = null)
    {
        if ($id == $this->id || $id == 0) {
            if ( $id == 0 && empty($this->data)) {
                $this->data = new Data();
            }
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
    * @return array
    */
    public function getRelationDetails(): array {
    	return $this->relationDetails;
    }

    /**
    * @param array $relationDetails
    */
    public function setRelationDetails(array $relationDetails): void {
    	$this->relationDetails = $relationDetails;
    }

    public function addRelationDetails(RelationDetails $relationDetails) {
        array_push($this->relationDetails, $relationDetails);
    }
}