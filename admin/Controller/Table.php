<?php namespace Controller;

use \Service\Read;
use \View\Table as TableListOrDetails;

//use function View\tableDetails;

class Table
{

    public function getTables($api = false, $props = array())
    {
        $read = new Read;
        $tableList = new TableListOrDetails($read->getTables(null, $props)->list);
        if ($api === true) {
            return $read->getTables()->list;
        } else {
            $tableList->tableList();
        }
    }

    public function getTableByIdOrName($api = false)
    {
        global $request;
        $read = new Read;
        $key = is_numeric($request[2]) ? 'rowid' : 'table_name';
        $table = array_pop($read->getTables(null, [$key => $request[2]])->list);
        $tableDetails = new TableListOrDetails($table);
        if ($api === true) {
            return $table;
        } else {
            $tableDetails->tableDetails();
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