<?php namespace Controller;

include_once __DIR__.'/../Service/Read.php';
include_once __DIR__.'/../Service/Create.php';
include_once __DIR__.'/../View/Table.php';
include_once __DIR__.'/../View/Form/NewTable.php';
use \DTO\ListDTO;
use \Dto\TableDTO;
use \Service\Create;
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

    public function addTable($input){

        $create = new Create();
        foreach ($input as $k => $v) {
            if (empty($v)) {
                unset($input[$k]);
            }
        }
        echo '<hr>';
        print_r($input);
        $create->addTableToDB($input);
    }

    public function getField()
    {
        global $request;
        $table = $this->getTableByIdOrName(true);

        if ($request[3] == "fields") {
            foreach ($table->data->getFields() as $field) {
                if ($field->getTableName() == $request[4]) {
                    return $field;
                }
            }
        }
    }

    public function getRelationsList(ListDTO $listDTO) {
        $read = new Read();
        $listDTO->__construct($read->getRelations());
    }
    
    public function pathParams()
    {
        global $request;
        $read = new Read();
        return $read->req($request);
    }

}