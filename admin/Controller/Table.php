<?php namespace Controller;

use \Service\Read;
use \View\Table as TableListOrDetails;

//use function View\tableDetails;

class Table
{
    
    public function getTables($r = null, $view = true)
    {
        $read = new Read;
        $tableList = new TableListOrDetails($read->getTables());
        if ($view === false) {
            return $read->getTables();
        }
        else {
            return $tableList->tableList();
        }
    }

    public function getTableByIdOrName($key, $value, $view = true) {
        global $request;
        $read = new Read;
        foreach($read->getTables(null, [$key => $value])->list as $table) {
        $tableDetails = new TableListOrDetails($table);
        if(in_array($request[2], [$table->getId(), $table->getName()])) {
            if ($view === false) {
                return $table;
            } else {
                $tableDetails->tableDetails();
            }
        }
        }
    }

    public function getField($value, $tableIdOrName = 'name') {
        global $request;
        $table = $this->getTableByIdOrName($tableIdOrName, $value, false);
        if ($request[3] == "fields")  {
            foreach($table->getData()->getFields() as $field) {
                if($field->getName() == $request[4]) {
                    return $field;
                }
            }
        }
    }
    public function pathParams()
    {
        global $request;
        $read = new Read;
        return $read->req($request);
    }

}