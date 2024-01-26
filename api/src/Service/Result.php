<?php namespace Api\Service;

use Api\Model\Entity;
use Api\Service\DbRead;

class Result extends QueryMaker
{
    public Entity $entity;
    public array $resultsFromQuery;

    public function __construct($tableName = null)
    {
        parent::__construct($tableName);
    }

    public function getDataSetsFromQuery() {
        return (new DbRead)->anySelect($this->__toString());
    }
    
}