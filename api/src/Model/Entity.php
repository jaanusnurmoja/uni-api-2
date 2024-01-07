<?php namespace Api\Model;


/**
 * class_alias('Entity', $className)
 * new $className();
 * https: //urielwilson.com/how-to-dynamically-generate-classes-at-runtime-in-php/

 */
class Entity
{
    public $table;
    public $pk;
    public ?object $data;
    public ?object $createdModified;
    public ?array $belongsTo;
    public ?array $hasMany;
    public ?array $hasManyAndBelongsTo;

}