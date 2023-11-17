<?php namespace Service;

use mysqli;

include_once __DIR__ . '/../Model/RelationDetails.php';
include_once __DIR__ . '/../Model/Relation.php';
include_once __DIR__ . '/../Model/Table.php';
include_once __DIR__ . '/../Model/Field.php';
include_once __DIR__ . '/../Dto/TableDTO.php';
include_once __DIR__ . '/../Dto/ListDTO.php';

class Create
{
    protected function cnn()
    {
        // require __DIR__ . '/../../api/config.php';
        // return new mysqli($host, $user, $pass, $dbname);
        $cnf = parse_ini_file(__DIR__ . '/../../config/connection.ini');
        return new mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);
    }

    public function addTableToList(\Dto\TableDTO $tableDTO)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        unset($tableDTO->id);
        $props = [];
        $vals = [];
        foreach ($tableDTO as $key => $value) {
            if (!is_array($value) && !is_object($value)) {
                $props[] = \Common\Helper::uncamelize($key);
                $vals[] = $value;
            }
        }
        $propsList = implode(", ", $props);
        $valsList = implode("', '", $vals);
        $fields = $tableDTO->getData()["fields"];
        $dbFields = [];

        foreach ($fields as $field) {
            $dbFields[] = \Common\Helper::uncamelize($field["name"]);
        }

        $sql = "INSERT INTO `models` ($propsList)
        VALUES ('$valsList');
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $stmt->insert_id;
    }
    private function addTableToDB(\Dto\TableDTO $tableDTO)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $where = '';

        $sqlCreate = "CREATE TABLE IF NOT EXISTS" . $tableDTO->getName() . "";
        $db->execute_query($sqlCreate);
    }

}