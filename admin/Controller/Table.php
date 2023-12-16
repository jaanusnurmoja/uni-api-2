<?php namespace Controller;

include_once __DIR__ . '/../Service/Read.php';
include_once __DIR__ . '/../Service/Update.php';
include_once __DIR__ . '/../Service/Delete.php';
include_once __DIR__ . '/../Service/Create.php';
include_once __DIR__ . '/../View/Table.php';
include_once __DIR__ . '/../View/Form/NewTable.php';
use \Dto\ListDTO;
use \Service\Create;
use \Service\Delete;
use \Service\Read;
use \Service\Update;
use \View\Form\NewTable;
use \View\Table as TableListOrDetails;

/**
 * Tabelite haldamisega seotud toimingud
 */

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

    public function getUnusedTables()
    {
        $read = new Read;
        $dataForUsed = $this->getTables(true);
        $used = [];
        foreach ($dataForUsed as $i => $d) {
            $used[] = $d->tableName;
        }

        return $read->getExistingTables($used);
    }

    public function getTableByIdOrName($api = false)
    {
        global $request;
        $read = new Read;
        if ($request[2] != 'new') {
            $key = is_numeric($request[2]) ? 't.id' : 't.table_name';
            // $table = array_pop($read->getTables(null, [$key => $request[2]])->list);
            ([$key => $request[2]]);
            $table = $read->getTables(null, [$key => $request[2]]);
            $tableDetails = new TableListOrDetails($table);
            if ($api === true) {
                return $table;
            } else {
                if (isset($request[3]) && $request[3] == 'edit') {
                    $tableDetails->edit->editTableForm();
                } else {
                    $tableDetails->tableDetails();
                }
            }

        }
    }

    public function newTable()
    {
        $t = new \Model\Table();
        $t->setId(0);
        $newTable = new NewTable($t);
        $newTable->newTableForm();
    }

    public function addTable($input, $existingToList = false)
    {

        $create = new Create();
        foreach ($input as $k => $v) {
            if (empty($v)) {
                unset($input[$k]);
            }
        }
        echo '<hr>';
        ($input);
        if ($existingToList === true) {
            $create->addTableToList($input);
        } else {
            $create->addTableToDB($input);
        }
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

    public function getPk($tableName)
    {
        $read = new Read();
        return $read->getDefaultFields($tableName)['pk'];
    }

    public function getRelationsList(ListDTO $listDTO)
    {
        $read = new Read();
        $listDTO->__construct($read->getRelations());
    }

    public function pathParams()
    {
        global $request;
        $read = new Read();
        return $read->req($request);
    }

    public function updateTable($table)
    {
        $update = new Update();
        $update->updateTable($table);
    }

    public function deleteTable($item, $confirmed = false)
    {
        if ($confirmed === true) {
            $del = new Delete();
            $del->removeFromList('models', $item);
        } else {
            $read = new Read();
            $table = $read->getTables(null, ['table_name' => $item]);
            $view = new TableListOrDetails($table);
            $view->tableDetails(true);
        }
    }

    public function dropColumn($table, $column)
    {
        $del = new Delete();
        $del->dropColumn($table, $column);
    }

    public function removeFromList($list, $idOrName)
    {
        $del = new Delete();
        $del->removeFromList($list, $idOrName);
    }

}