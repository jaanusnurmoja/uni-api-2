<?php namespace Dto;

use \Model\Table;

/**
 * Põhimudeli töötleja, sh on seosed teiste tabelitega jaotatud vastavalt tüübile
 */
class TableDTO
{
    public $id;
    public $tableName;
    public $pk;
    public $data;
    public $createdModified;
    public $belongsTo = [];
    public $hasMany = [];
    public $hasManyAndBelongsTo = [];
    public $hasAny = [];

    public function __construct(Table $model, $mini = false)
    {
        $tableItem = new TableItem($model);
        $model->data->setTable($tableItem);
        $this->id = $model->getId() ? $model->getId() : null;
        $this->tableName = $model->getTableName() ? $model->getTableName() : null;
        $this->pk = $model->getPk() ? $model->getPk() : null;
        $this->data = $model->getData() ? $model->getData() : null;

        if ($mini === false) {
            $this->createdModified = $model->getCreatedModified() ? $model->getCreatedModified() : null;
            unset($this->data->table);
            foreach ($model->getRelationSettings() as $rdRow) {

                //unset($rdRow->table);
                if ($rdRow->getMode() == 'belongsTo') {
                    $rdRow->setTable($tableItem);

                    array_push($this->belongsTo, $rdRow);
                }
                if ($rdRow->getMode() == 'hasMany') {
                    array_push($this->hasMany, $rdRow);
                    $rdRow->setTable($tableItem);
                }
                if ($rdRow->getMode() == 'hasManyAndBelongsTo') {
                    array_push($this->hasManyAndBelongsTo, $rdRow);
                    $rdRow->setTable($tableItem);
                }
                if ($rdRow->getMode() == 'hasAny') {
                    array_push($this->hasAny, $rdRow);
                    $rdRow->setTable($tableItem);
                }
            }

        } else {
        unset($this->data, $this->createdModified, $this->belongsTo, $this->hasMany, $this->hasManyAndBelongsTo, $this->hasAny);
    }
    }

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
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set the value of name
     */
    public function setTableName($tableName): self
    {
        $this->tableName = $tableName;

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

    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the value of createdModified
     */
    public function getCreatedModified()
    {
        return $this->createdModified;
    }

    /**
     * Set the value of createdModified
     */
    public function setCreatedModified($createdModified): self
    {
        $this->createdModified = $createdModified;

        return $this;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function getBelongsTo()
    {
        return $this->belongsTo;
    }

    /**
     * @param $belongsTo
     */
    public function setBelongsTo($belongsTo)
    {
        $this->belongsTo = $belongsTo;
    }

    public function addBelongsTo($belongsTo)
    {
        array_push($this->belongsTo, $belongsTo);
    }

    public function getHasMany()
    {
        return $this->hasMany;
    }

    /**
     * @param $hasMany
     */
    public function setHasMany($hasMany)
    {
        $this->hasMany = $hasMany;
    }

    public function addHasMany($hasMany)
    {
        array_push($this->hasMany, $hasMany);
    }
    public function getHasManyAndBelongsTo()
    {
        return $this->hasManyAndBelongsTo;
    }

    /**
     * @param $hasManyAndBelongsTo
     */
    public function setHasManyAndBelongsTo($hasManyAndBelongsTo)
    {
        $this->hasManyAndBelongsTo = $hasManyAndBelongsTo;
    }


    public function getHasAny() {
    	return $this->hasAny;
    }

    /**
    * @param $hasAny
    */
    public function setHasAny($hasAny) {
    	$this->hasAny = $hasAny;
    }
}