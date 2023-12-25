<?php namespace Api\Model;

use Common\Model\CreatedModified;
use UnexpectedValueException;

class Data
{
    private $table;
    public $content;
    public CreatedModified $createdModified;

    public function __construct($table)
    {
        if (empty($this->table)) {
            $this->setTable($table);
        } else {
            if ($this->table == $table) {
                return $this;
            }
        }
    }
    /**
     * Get the value of table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set the value of table
     */
    public function setTable($table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the value of content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content
     */
    public function setContent($content, $value = null): self
    {
        if (!empty($value)) {
            if (is_string($content) || is_numeric($content)) {
                $this->content[$content] = $value;
            } else {
                throw new UnexpectedValueException("First argument should be string or numeric, not an array/object");
            }
        } else {
            if (is_array($content)) {
                $this->content = $content;
            }
        }

        return $this;
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
    public function setCreatedModified($createdModified = new CreatedModified()): self
    {
        $this->createdModified = $createdModified;

        return $this;
    }
}
