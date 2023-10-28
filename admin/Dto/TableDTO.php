<?php namespace DTO;

use \Model\Table;

class TableDTO
{
    public $id;
    public $name;
    public $pk;
    public $data;
    public array $belongsTo = [];
    public array $hasMany = [];
    public array $hasManyAndBelongsTo = [];

    public function __construct(Table $model)
    {
        $this->id = $model->getId() ? $model->getId() : null;
        $this->name = $model->getName() ? $model->getName() : null;
        $this->pk = $model->getPk() ? $model->getPk() : null;
        $this->data = $model->getData() ? $model->getData() : null;
        unset($this->data->table);
        foreach ($model->getRelationDetails() as $rdRow) {

            unset($rdRow->table);
            if ($rdRow->getRole() == 'belongsTo') {

                array_push($this->belongsTo, $rdRow);
            }
            if ($rdRow->getRole() == 'hasMany') {
                array_push($this->hasMany, $rdRow);
            }
            if ($rdRow->getRole() == 'hasManyAndBelongsTo') {
                array_push($this->hasManyAndBelongsTo, $rdRow);
            }
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

    public function getData()
    {
        return $this->data;
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
}