<?php namespace Model;

class Relation
{
    private $id;
    private $type;
    private bool $allowHasMany;
    private bool $isInner;

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

    /**
     * @return bool
     */
    public function getAllowHasMany(): bool
    {
        return $this->allowHasMany;
    }

    /**
     * @param bool $allowHasMany
     */
    public function setAllowHasMany(bool $allowHasMany): void
    {
        $this->allowHasMany = $allowHasMany;
    }

    /**
     * Get the value of isInner
     */
    public function isInner(): bool
    {
        return $this->isInner;
    }

    /**
     * Set the value of isInner
     */
    public function setIsInner(bool $isInner)
    {
        $this->isInner = $isInner;
    }

    /**
     * Get the value of type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }
}
