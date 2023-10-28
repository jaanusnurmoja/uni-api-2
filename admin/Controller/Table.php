<?php namespace Controller;

use \Service\Read;
use \View\Table as TableDetails;

//use function View\tableDetails;

class Table
{
    
    public function getTables($r = null)
    {
        $read = new Read;
        return $read->getTables($r);
        //return 'Siin pole midagi';
    }

    public function getTableByIdOrName() {
        global $request;
        foreach($this->getTables()->list as $table) {
        $tableDetails = new TableDetails($table);
            if(in_array($request[2], [$table->getId(), $table->getName()])) {
                if (!isset($request[3])) {
                    return $tableDetails->tableDetails();
                }
                else {
                    return $table;
                }
            }
        }
    }

    public function getField($table = null) {
        global $request;
        if ($table == null) $table = $this->getTableByIdOrName();
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