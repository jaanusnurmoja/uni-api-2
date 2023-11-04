<?php namespace Controller;

use \Service\Read;
use \View\Form\NewTable;
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
        if ($request[2] != 'new') {
        $key = is_numeric($request[2]) ? 'rowid' : 'table_name';
        // $table = array_pop($read->getTables(null, [$key => $request[2]])->list);
        $table = $read->getTables(null, [$key => $request[2]]);
        $tableDetails = new TableListOrDetails($table);
        if ($api === true) {
            return $table;
        } else {
            if (isset($request[3]) && $request[3] == 'edit') {
                $tableDetails->edit->editTableForm();
            }
            else {
                $tableDetails->tableDetails();
            }
        }
            
        }
    }

    public function newTable(){
        $t = new \Model\Table();
        $t->setId(0);
            $newTable = new NewTable($t);
            $newTable->newTableForm();
    }

    public function getField()
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

    public function getRelationsList() {
        $read = new Read();
        return $read->getRelations();
    }
    
    public function pathParams()
    {
        global $request;
        $read = new Read;
        return $read->req($request);
    }

}