<?php namespace Model;

include 'Data.php';

use Common\Model\CreatedModified;
use Model\Data as DataFields;

/**
 * Halduskeskkonna peamine mudel
 */
class Table
{

    public $id;
    public $tableName;
    public $pk = 'id';
    public bool $isMain = false;
    public $data;
    public CreatedModified $createdModified;
    public $relationSettings = [];

    public function __construct($id = null)
    {
        if ($id == $this->id || $id == 0) {
            if ($id == 0 && empty($this->data)) {
                $this->data = new DataFields();
            }
            if (empty($this->createdModified)) {
                $this->createdModified = new CreatedModified();
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

    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param $name
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
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
    public function getData(): DataFields
    {
        return $this->data;
    }

    /**
     * @param Data $data
     */
    public function setData(DataFields $data): void
    {
        $this->data = $data;
    }

    /**
     * Get the value of createdModified
     */
    public function getCreatedModified(): CreatedModified
    {
        return $this->createdModified;
    }

    /**
     * Set the value of createdModified
     */
    public function setCreatedModified(CreatedModified $createdModified): self
    {
        $this->createdModified = $createdModified;

        return $this;
    }

    /**
     * @return array
     */
    public function getRelationSettings(): array
    {
        return $this->relationSettings;
    }

    /**
     * @param array $relationSettings
     */
    public function setRelationSettings(array $relationSettings): void
    {
        $this->relationSettings = $relationSettings;
    }

    public function addRelationSettings(RelationSettings $relationSettings)
    {
        array_push($this->relationSettings, $relationSettings);
    }

    /**
     * Get the value of isMain
     */
    public function getIsMain(): bool
    {
        return $this->isMain;
    }

    /**
     * Set the value of isMain
     */
    public function setIsMain($isMain): self
    {
        $this->isMain = $isMain;

        return $this;
    }
}
