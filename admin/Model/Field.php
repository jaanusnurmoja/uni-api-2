<?php
namespace Model;

use Common\Helper;

class Field
{
    public $id;
    public $name;
    public $type = 'varchar(255)';
    public bool $defOrNull = false;
    public $defaultValue = null;
    public $htmlDefaults = [];
    public $table;
    public $sqlSelect;
    public $sqlAlias;

    public function __construct($name = null, $type = null, $table = null)
    {
        $this->name = $name;
        if (!empty($type)) {
            $this->type = $type;
        }

        if (in_array($name, ['createdBy', 'createdWhen', 'modifiedBy', 'modifiedWhen'])) {
            $this->defOrNull = in_array($name, ['modifiedBy', 'modifiedWhen']) ? true : false;
            if ($name == 'createdWhen') {
                $this->defaultValue = 'current_timestamp';
            }
            $this->htmlDefaults['form'] = true;
            $this->htmlDefaults['input'] = 'hidden';
            if ($name == 'modifiedBy') {
                $this->defOrNull = true;
            }
        }
        if (!empty($table)) {
            $this->table = $table;
        }
        $htmlName = !empty($name) ? Helper::uncamelize($name) : $name;
        $this->sqlSelect = "$table.$htmlName";
        $this->sqlAlias = "$table:$name";
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

    /**
     * Get the value of sqlSelect
     */
    public function getSqlSelect()
    {
        return $this->sqlSelect;
    }

    /**
     * Set the value of sqlSelect
     */
    public function setSqlSelect($sqlSelect = null): self
    {
        $this->sqlSelect = $sqlSelect;

        return $this;
    }

    /**
     * Get the value of sqlAlias
     */
    public function getSqlAlias()
    {
        return $this->sqlAlias;
    }

    /**
     * Set the value of sqlAlias
     */
    public function setSqlAlias($sqlAlias = null): self
    {
        $this->sqlAlias = $sqlAlias;

        return $this;
    }
}