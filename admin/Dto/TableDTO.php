<?php namespace DTO;

class TableDTO
{
    public $id;
    public $name;
    public $pk;

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
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of pk
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * Set the value of pk
     */
    public function setPk($pk): self
    {
        $this->pk = $pk;

        return $this;
    }
}
