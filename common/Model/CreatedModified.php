<?php namespace common\Model;
use \user\model\User;

class CreatedModified {

    public int $tableId = 0;
    public string $tableName;
    public User $createdBy;
    public string $createdWhen;
    public ?int $modifiedBy = null;
    public ?string $modifiedWhen = null;
    
    public function __construct(?int $tableId, ?string $tableName){
        
        if (!empty($tableId)) {
            if ($this->tableId == $tableId && $this->tableName == $tableName) {
                return $this;
            } else {
                $this->setTableId($tableId);
                $this->setTableName($tableName);
            }
        } 
    }
    /**
     * Get the value of tableName
     */
    
        /**
     * Get the value of tableId
     *
     * @return int
     */
    public function getTableId(): int
    {
        return $this->tableId;
    }

    /**
     * Set the value of tableId
     *
     * @param int $tableId
     *
     * @return self
     */
    public function setTableId(int $tableId): self
    {
        $this->tableId = $tableId;

        return $this;
    }
    
    
     public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Set the value of tableName
     */
    public function setTableName($tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get the value of createdBy
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * Set the value of createdBy
     */
    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the value of createdWhen
     */
    public function getCreatedWhen()
    {
        return $this->createdWhen;
    }

    /**
     * Set the value of createdWhen
     */
    public function setCreatedWhen($createdWhen): self
    {
        $this->createdWhen = $createdWhen;

        return $this;
    }

    /**
     * Get the value of modifiedBy
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Set the value of modifiedBy
     */
    public function setModifiedBy($modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Get the value of modifiedWhen
     */
    public function getModifiedWhen()
    {
        return $this->modifiedWhen;
    }

    /**
     * Set the value of modifiedWhen
     */
    public function setModifiedWhen($modifiedWhen): self
    {
        $this->modifiedWhen = $modifiedWhen;

        return $this;
    }


}