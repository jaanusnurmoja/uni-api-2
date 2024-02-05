<?php namespace Api\Model;
#[\AllowDynamicProperties]
class Join
{
    public $id;
    public $thisTable;
    public $mode;
    private $keyField;
    private $keyValue;
    private $otherKeyField;
    public $otherTable;
    private ?Entity $item;
    private ?array $items;

    public function __construct($id=null, $mode=null, $thisTable=null, $keyField = null, $otherKeyField = null, $otherTable=null) {
        $this->id = $id;
        $this->mode = $mode;
        $this->thisTable = $thisTable;
        $this->keyField = $keyField;
        $this->otherKeyField = $otherKeyField;
        $this->otherTable = $otherTable;
        if (in_array($mode, ['belongsTo', 'hasAny'])) {
            $this->item = new Entity($otherTable);
            $this->$otherTable = $this->item;
        } else {
            $this->items = [];
            $this->$otherTable = $this->items;
        }
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set the value of mode
     */
    public function setMode($mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Get the value of table
     */
    /**
     * Get the value of item
     */
    public function getItem(): ?Entity
    {
        return $this->item;
    }

    /**
     * Set the value of item
     */
    public function setItem(?Entity $item): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get the value of items
     */
    public function getItems(): ?array
    {
        return $this->items;
    }

    /**
     * Set the value of items
     */
    public function setItems(?array $items): self
    {
        $this->items = $items;

        return $this;
    }
    public function addItem(?Entity $item): self
    {
        if (isset($item)) {
            if (in_array($this->mode, ['belongsTo', 'hasAny'])) {
                $this->item = $item;
            } else {
                //array_push($this->items, $item);
                if (isset($item->pk)) {
                $this->items[$item->pk->value] = $item;
                }
            }
        }

        return $this;
    }

    /**
     * Get the value of thisTable
     */
    public function getThisTable()
    {
        return $this->thisTable;
    }

    /**
     * Set the value of thisTable
     */
    public function setThisTable($thisTable): self
    {
        $this->thisTable = $thisTable;

        return $this;
    }

    public function getKeyField() {
        return $this->keyField;
    }

    public function setKeyField($keyField) {
        $this->keyField = $keyField;
        return $this;
    }
    /**
     * Get the value of otherTable
     */
    public function getOtherTable()
    {
        return $this->otherTable;
    }

    /**
     * Set the value of otherTable
     */
    public function setOtherTable($otherTable): self
    {
        $this->otherTable = $otherTable;

        return $this;
    }



    /**
     * Get the value of keyValue
     *
     * @return ?int
     */
    public function getKeyValue()
    {
        return $this->keyValue;
    }

    /**
     * Set the value of keyValue
     *
     * @param ?int $keyValue
     *
     * @return self
     */
    public function setKeyValue($keyValue): self
    {
        if (isset($this->items)){
            if (!isset($this->keyValue)){
                $this->keyValue = [];
            }
            if (!in_array($keyValue, $this->keyValue)){
                array_push($this->keyValue, $keyValue);
            }
        }
        if (isset($this->item)){
            $this->keyValue = $keyValue;
        }

        return $this;
    }

    

    /**
     * Get the value of otherKeyField
     */
    public function getOtherKeyField()
    {
        return $this->otherKeyField;
    }

    /**
     * Set the value of otherKeyField
     */
    public function setOtherKeyField($otherKeyField): self
    {
        $this->otherKeyField = $otherKeyField;

        return $this;
    }
}