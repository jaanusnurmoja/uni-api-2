<?php namespace Controller;

use \Service\Read;
use \View\Table as TableListOrDetails;

//use function View\tableDetails;

class Table
{

    public function getTables($api = false)
    {
        $read = new Read;
        $tableList = new TableListOrDetails($read->getTables()->list);
        if ($api === true) {
            return $read->getTables()->list;
        } else {
            $tableList->tableList();
        }
    }

    public function getTableByIdOrName($api = false)
    {
        global $request;
        foreach ($this->getTables(true) as $table) {
            $tableDetails = new TableListOrDetails($table);
            if (in_array($request[2], [$table->getId(), $table->getName()])) {
                if ($api === true) {
                    return $table;
                } else {
                    $tableDetails->tableDetails();
                }
            }
        }
    }

    public function getField($api = false)
    {
        global $request;
        $table = $this->getTableByIdOrName(true);

        if ($request[3] == "fields") {
            foreach ($table->data->getFields() as $field) {
                if ($field->getName() == $request[4]) {
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