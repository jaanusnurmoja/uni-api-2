<?php namespace Api\Dto;

use Api\Model\Entity as ModelEntity;

class Entity
{
    public $pk;
    public $data;

    public function __construct($newClass = null)
    {
        if (empty($newClass)) {
            $newClass = new ModelEntity();
        }
        unset($newClass->belongsTo, $newClass->hasMany, $newClass->hasManyAndBelongsTo);
        $this->pk = $newClass->pk;
        $this->data = $newClass->data;
    }

}