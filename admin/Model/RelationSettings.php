<?php namespace Model;

use Common\Model\CreatedModified;
use Dto\TableItem;

/**
 * Andmeseose täiendavad üksikasjad
 *
 * @todo osa üksikasju võiksid tegelikult pärineda Relation relation mudelist
 *
 */
class RelationSettings
{

    public ?int $id;
    private Relation $relation;
    private $role;
    public $keyField;
    private bool $hasMany = false;
    public TableItem $table;
    public $tableId;
    public $otherTable;
    public $mode;
    private Table $many;
    private $manyId;
    private $manyTable;
    private $manyFk;
    public $manyMany;
    public $manyManyIds;
    private $anyId;
    public $anyAny;
    private ?Table $any;
    private $anyTable;
    private $anyPk;
    private $onePk;
    private $oneTable;
    private $oneId;
    private Table $one;
    public CreatedModified $createdModified;

    public function __construct(?int $id = 0)
    {
        $this->id = $id;

    }
    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of relation
     */
    public function getRelation(): Relation
    {
        return $this->relation;
    }

    /**
     * Set the value of relation
     */
    public function setRelation(Relation $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Get the value of role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the value of role
     */
    public function setRole($role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of keyField
     */
    public function getKeyField()
    {
        return $this->keyField;
    }

    /**
     * Set the value of keyField
     */
    public function setKeyField($keyField): self
    {
        $this->keyField = $keyField;

        return $this;
    }

    /**
     * Get the value of hasMany
     */
    public function getHasMany(): bool
    {
        return $this->hasMany;
    }

    /**
     * Set the value of hasMany
     */
    public function setHasMany(bool $hasMany): self
    {
        $this->hasMany = $hasMany;

        return $this;
    }

    /**
     * Get the value of table
     */
    public function getTable(): TableItem
    {
        return $this->table;
    }

    /**
     * Set the value of table
     */
    public function setTable(TableItem $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the value of tableId
     */
    public function getTableId()
    {
        return $this->tableId;
    }
    /**
     * Set the value of tableId
     */
    public function setTableId($tableId): self
    {
        $this->tableId = $tableId;
        return $this;
    }

    public function getOtherTable()
    {
        return $this->otherTable;
    }

    /**
     * @param $otherTable
     */
    public function setOtherTable($otherTable)
    {
        $this->otherTable = "/$otherTable";
        return $this;
    }

    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return Table
     */
    public function getMany(): Table
    {
        return $this->many;
    }

    /**
     * @param Table $many
     */
    public function setMany(Table $many)
    {
        $this->many = $many;
        return $this;
    }

    public function getManyTable()
    {
        return $this->manyTable;
    }

    /**
     * @param $manyTable
     */
    public function setManyTable($manyTable)
    {
        if ($this->oneId == $this->tableId) {
            $this->otherTable = "/$manyTable";
        }
        $this->manyTable = $manyTable;
        return $this;
    }

    public function getManyFk()
    {
        return $this->manyFk;
    }

    /**
     * @param $manyFk
     */
    public function setManyFk($manyFk)
    {
        if ($this->manyId == $this->tableId) {
            $this->keyField = $manyFk;
        }
        $this->manyFk = $manyFk;
        return $this;
    }

    public function getManyMany()
    {
        return $this->manyMany;
    }

    /**
     * @param $manyMany
     */
    public function setManyMany($manyMany)
    {

        $this->manyMany = $manyMany;
        if (!empty($manyMany)) {
            if (is_array($manyMany)) {
                $tableId = $this->tableId;

                if ($manyMany[0]->id == $tableId) {
                    $this->otherTable = '/' . $manyMany[1]->table;
                }
                if ($manyMany[1]->id == $tableId) {
                    $this->otherTable = '/' . $manyMany[0]->table;
                }
            } else {
                if (is_object($manyMany)) {$this->otherTable = '/' . $manyMany->table;}
            }
        } else {
            unset($this->manyMany);
        }
        return $this;
    }

    public function getOnePk()
    {
        return $this->onePk;
    }

    /**
     * @param $onePk
     */
    public function setOnePk($onePk)
    {
        if ($this->oneId == $this->tableId) {
            $this->keyField = $onePk;
        }
        $this->onePk = $onePk;
        return $this;
    }

    public function getOneTable()
    {
        return $this->oneTable;
    }

    /**
     * @param $oneTable
     */
    public function setOneTable($oneTable)
    {
        if ($this->manyId == $this->tableId) {
            $this->otherTable = '/' . $oneTable;
        }
        $this->oneTable = $oneTable;
        return $this;
    }

    /**
     * @return Table
     */
    public function getOne(): Table
    {
        return $this->one;
    }

    /**
     * @param Table $one
     */
    public function setOne(Table $one)
    {
        $this->one = $one;
        return $this;
    }

    /**
     * @return CreatedModified
     */
    public function getCreatedModified(): CreatedModified
    {
        return $this->createdModified;
    }

    /**
     * @param CreatedModified $createdModified
     */
    public function setCreatedModified(CreatedModified $createdModified)
    {
        $this->createdModified = $createdModified;
        return $this;
    }

    public function getManyManyIds()
    {
        return $this->manyManyIds;
    }

    /**
     * @param $manyManyIds
     */
    public function setManyManyIds($manyManyIds)
    {
        $this->manyManyIds = $manyManyIds;
        return $this;
    }

    /**
     * Get the value of anyAny
     */
    public function getAnyAny()
    {
        return $this->anyAny;
    }

    /**
     * Set the value of anyAny
     */
    public function setAnyAny($anyAny): self
    {
        $this->anyAny = $anyAny;
        if (empty($anyAny)) {
            unset($this->anyAny);
        }
        return $this;
    }

    /**
     * Get the value of any
     *
     * @return ?Table
     */
    public function getAny(): ?Table
    {
        return $this->any;
    }

    /**
     * Set the value of any
     *
     * @param ?Table $any
     *
     * @return self
     */
    public function setAny(?Table $any): self
    {
        $this->any = $any;
        return $this;
    }

    /**
     * Get the value of anyTable
     */
    public function getAnyTable()
    {
        return $this->anyTable;
    }

    /**
     * Set the value of anyTable
     */
    public function setAnyTable($anyTable): self
    {
        if (!empty($anyTable)) {
            $this->otherTable = "/$anyTable";
        }
        $this->anyTable = $anyTable;
        return $this;
    }

    /**
     * Get the value of anyPk
     */
    public function getAnyPk()
    {
        return $this->anyPk;
    }

    /**
     * Set the value of anyPk
     */
    public function setAnyPk($anyPk): self
    {
        $this->anyPk = $anyPk;
        return $this;
    }

    /**
     * Get the value of manyId
     */
    public function getManyId()
    {
        return $this->manyId;
    }

    /**
     * Set the value of manyId
     */
    public function setManyId($manyId): self
    {
        $this->manyId = $manyId;
        return $this;
    }

    /**
     * Get the value of anyId
     */
    public function getAnyId()
    {
        return $this->anyId;
    }

    /**
     * Set the value of anyId
     */
    public function setAnyId($anyId): self
    {
        $this->anyId = $anyId;
        return $this;
    }

    /**
     * Get the value of oneId
     */
    public function getOneId()
    {
        return $this->oneId;
    }

    /**
     * Set the value of oneId
     */
    public function setOneId($oneId): self
    {
        $this->oneId = $oneId;
        return $this;
    }

    public function rewriteMode($mode)
    {
        $tableId = $this->tableId;
        $manyId = $this->manyId;
        $currentMode = explode('__one_many__', $mode);
        $this->mode = $currentMode[0];
        if ($tableId == $manyId) {
            $this->mode = $currentMode[1];
        } else {
            $this->mode = $currentMode[0];
        }
        return $this;
    }

}