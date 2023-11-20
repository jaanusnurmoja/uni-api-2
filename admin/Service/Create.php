<?php namespace Service;

use mysqli;

include_once __DIR__ . '/../Model/RelationDetails.php';
include_once __DIR__ . '/../Model/Relation.php';
include_once __DIR__ . '/../Model/Table.php';
include_once __DIR__ . '/../Model/Field.php';
include_once __DIR__ . '/../Dto/TableDTO.php';
include_once __DIR__ . '/../Dto/ListDTO.php';
include_once __DIR__ . '/../../common/Helper.php';

class Create
{
    protected function cnn()
    {
        // require __DIR__ . '/../../api/config.php';
        // return new mysqli($host, $user, $pass, $dbname);
        $cnf = parse_ini_file(__DIR__ . '/../../config/connection.ini');
        return new mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);
    }

    public function addTableToDB($input)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $indexes = [];

        $sqlCreate = "CREATE TABLE IF NOT EXISTS " . $input['tableName'] . "(
            `$input[pk]` int(11) NOT NULL AUTO_INCREMENT";
            
            if (isset($input['belongsTo']) && !empty($input['belongsTo'])) {
                foreach ($input['belongsTo'] as $fk) {
                $sqlCreate .= ",
                `$fk[keyField]` int(11) DEFAULT NULL";
                $indexes[] = "KEY `$fk[otherTable]` (`$fk[keyField]`)";
                }
            }
            foreach ($input['data']['fields'] as $column) {
                $sqlCreate .= ",
                `$column[name]` $column[type]";
                if (empty($column['defOrNull'])) {
                    $sqlCreate .= " NOT NULL";
                } 
                if (!empty($column['defaultValue'])) {
                    $sqlCreate .= " DEFAULT '$column[defaultValue]'";
                } else {
                    if ($column['defOrNull']) {
                        $sqlCreate .= " DEFAULT NULL";
                    }
                }
            }
           $sqlCreate .= ",
           PRIMARY KEY (`$input[pk]`),
           " . implode(',
           ', $indexes) . "
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_estonian_ci";
            //print_r($sqlCreate);
        $db->execute_query($sqlCreate);
    }

    public function addTableToList($input)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        unset($input->id);
        $props = [];
        $vals = [];
        foreach ($input as $key => $value) {
            if (!is_array($value) && !is_object($value)) {
                $props[] = \Common\Helper::uncamelize($key);
                $vals[] = $value;
            }
        }
        $propsList = implode(", ", $props);
        $valsList = implode("', '", $vals);
        $fields = $input["data"]["fields"];
        $dbFields = [];

        foreach ($fields as $field) {
            $dbFields[] = \Common\Helper::uncamelize($field["name"]);
        }

        $sql = "INSERT INTO `models` ($propsList)
        VALUES ('$valsList');
        ";
        $db->execute_query($sql);
        //print_r($db->insert_id);

        //$stmt = $db->prepare($sql);
        //$stmt->execute();
    }
    /** 
     * CREATE TABLE `test`.`katseloom` 
     * (
     * `id` INT NOT NULL AUTO_INCREMENT , 
     * `onju` BOOLEAN NOT NULL DEFAULT FALSE , 
     * `pealkiri` VARCHAR(255) NOT NULL , 
     * `kirjeldus` LONGTEXT NULL , 
     * PRIMARY KEY (`id`)
     * ) ENGINE = InnoDB;
    */
}