<?php namespace Common\Model;

use \Model\Field;
use \user\model\User;

/**
 * Lisamis- ja muutmisinfot kandev väljade rühm kasutaja loodavate andmetabelite jaoks
 */
class DataCreatedModified
{

    public string $tableName = '';
    public Field $createdBy;
    public Field $createdWhen;
    public Field $modifiedBy;
    public Field $modifiedWhen;

    public function __construct($tableName = null)
    {

        if (!empty($tableName)) {
            if ($this->tableName == $tableName) {
                return $this;
            } else {
                $this->setTableName($tableName);
            }
        }
        $this->setCreatedBy(new Field('createdBy', 'int'));
        $this->setCreatedWhen(new Field('createdWhen', 'timestamp'));
        $this->setModifiedBy(new Field('modifiedBy', 'int'));
        $this->setModifiedWhen(new Field('modifiedWhen', 'timestamp on update current_timestamp'));
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
    public function getCreatedBy(): Field
    {
        return $this->createdBy;
    }

    /**
     * Set the value of createdBy
     *
     * @param User $createdBy
     */
    public function setCreatedBy(Field $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the value of createdWhen
     *
     * @return string  (mysql: timestamp)
     */
    public function getCreatedWhen(): Field
    {
        return $this->createdWhen;
    }

    /**
     * Set the value of createdWhen
     *
     * @param string  $createdWhen ('Y-m-d H:i:s')
     */
    public function setCreatedWhen(Field $createdWhen): self
    {
        $this->createdWhen = $createdWhen;

        return $this;
    }

    /**
     * Get the value of modifiedBy
     *
     * @return int (user id)
     */
    public function getModifiedBy(): Field
    {
        return $this->modifiedBy;
    }

    /**
     * Set the value of modifiedBy
     *
     * $param int $modifiedBy
     */
    public function setModifiedBy(Field $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Get the value of modifiedWhen
     *
     *   @return string  (mysql: timestamp)
     */
    public function getModifiedWhen(): Field
    {
        return $this->modifiedWhen;
    }

    /**
     * Set the value of modifiedWhen
     *
     * @param string  $modifiedWhen ('Y-m-d H:i:s')
     */
    public function setModifiedWhen(Field $modifiedWhen): self
    {
        $this->modifiedWhen = $modifiedWhen;

        return $this;
    }

}