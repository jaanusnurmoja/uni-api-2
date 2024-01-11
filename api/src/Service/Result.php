<?php namespace Api\Service;

use Api\Model\Entity;

class Result extends QueryMaker
{
    public Entity $entity;
    public array $resultsFromQuery;

    public function __construct($tableName = null)
    {
        parent::__construct($tableName);
    }

    public function getDataSetsFromQuery() {
        $dbRead = new DbRead;
        $query = $this->__toString();
        $resultRows = $dbRead->anySelect($query);
        return $resultRows;
    }
}