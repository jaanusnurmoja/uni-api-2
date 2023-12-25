<?php namespace Common\Model;

use \user\model\User;

/**
 * Lisamis- ja muutmisinfot kandev väljade rühm halduskeskkonna jaoks
 */
class CreatedModified
{

    private int $tableId = 0;
    private string $tableName;
    public User $createdBy;
    public string $createdWhen;
    public ?int $modifiedBy = null;
    public ?string $modifiedWhen = null;

    public function __construct($tableId = null, $tableName = null)
    {

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

    /**
     * Get the value of tableName
     *
     * @return string
     */

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Set the value of tableName
     *
     * @param string  $tableName
     */
    public function setTableName($tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get the value of createdBy
     *
     * @return User
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * Set the value of createdBy
     *
     * @param User $createdBy
     */
    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the value of createdWhen
     *
     * @return string  (mysql: timestamp)
     */
    public function getCreatedWhen()
    {
        return $this->createdWhen;
    }

    /**
     * Set the value of createdWhen
     *
     * @param string  $createdWhen ('Y-m-d H:i:s')
     */
    public function setCreatedWhen($createdWhen): self
    {
        $this->createdWhen = $createdWhen;

        return $this;
    }

    /**
     * Get the value of modifiedBy
     *
     * @return int (user id)
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Set the value of modifiedBy
     *
     * $param int $modifiedBy
     */
    public function setModifiedBy($modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Get the value of modifiedWhen
     *
     *   @return string  (mysql: timestamp)
     */
    public function getModifiedWhen()
    {
        return $this->modifiedWhen;
    }

    /**
     * Set the value of modifiedWhen
     *
     * @param string  $modifiedWhen ('Y-m-d H:i:s')
     */
    public function setModifiedWhen($modifiedWhen): self
    {
        $this->modifiedWhen = $modifiedWhen;

        return $this;
    }

}
