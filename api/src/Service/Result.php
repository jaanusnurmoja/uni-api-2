<?php namespace Api\Service;

use Api\Model\Entity;
use Api\Service\DbRead;
use stdClass;

class Result extends QueryMaker
{
    public Entity $entity;
    public array $resultsFromQuery;

    public function __construct($tableName = null)
    {
        parent::__construct($tableName);
    }

    public function getDataSetsFromQuery() {
        $read = new DbRead;
        $read->anySelect($this->__toString());
        return $read;
    }
    
    public function varDumpOfDataSetsFromQuery() {
        $varDump = new stdClass;
        $varDump->url = 'http://localhost/uni-api/api/orchestras/1?testApi';
        $varDump->mainTableInThisExampleQuery = 'orchestras';
        $varDump->hiararchyOfInvolvedTables = ['orchestras' => ['has many' => ['conductors' => '', 'instruments' => ['has many' => 'players']]]];
        $varDump->query = $this->__toString();
        $varDump->currentVarDumpOfResultsRepresentation = var_dump($this->getDataSetsFromQuery()->rows);
        $varDump->desiredVarDumpOfResultsRepresentation = file_get_contents(__DIR__.'/../../expectedQueryResult.txt');
        return $varDump;
    }
}