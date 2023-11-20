<?php
namespace Model;

class Field
{
    public $id;
    public $name;
    public $type;
    public bool $defOrNull = false;
    public $defaultValue;
    public $htmlDefaults;

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

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Table
     */
    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get the value of defOrNull
     */
    public function getDefOrNull(): bool
    {
        return $this->defOrNull;
    }

    /**
     * Set the value of defOrNull
     */
    public function setDefOrNull($defOrNull): self
    {
        $this->defOrNull = $defOrNull;

        return $this;
    }

    
    /**
     * Get the value of defaultValue
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * Set the value of defaultValue
     */
    public function setDefaultValue($defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getHtmlDefaults(): ?array
    {
        return $this->htmlDefaults;
    }

    /**
     * @param $htmlDefaults
     */
    public function setHtmlDefaults($htmlDefaults)
    {
        $this->htmlDefaults = $htmlDefaults;
    }

}