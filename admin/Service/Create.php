<?php namespace Service;

use mysqli;
use stdClass;

include_once __DIR__ . '/../Model/RelationDetails.php';
include_once __DIR__ . '/../Model/Relation.php';
include_once __DIR__ . '/../Model/Table.php';
include_once __DIR__ . '/../Model/Field.php';
include_once __DIR__ . '/../Dto/TableDTO.php';
include_once __DIR__ . '/../Dto/ListDTO.php';
include_once __DIR__ . '/../../common/Helper.php';

/**
 * Andmete / tabelite loomine (create table, insert into)
 */
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

        $sqlCreate = "CREATE TABLE " . $input['tableName'] . "(
            `$input[pk]` int(11) NOT NULL AUTO_INCREMENT";

        if (isset($input['belongsTo']) && !empty($input['belongsTo'])) {
            foreach ($input['belongsTo'] as $fk) {
                $sqlCreate .= ",
                `$fk[keyField]` int(11) DEFAULT NULL";
                $indexes[] = "KEY `$fk[otherTable]` (`$fk[keyField]`)";
            }
        }
        if (isset($input['data']['fields'])) {
            foreach ($input['data']['fields'] as $column) {
                $sqlCreate .= ",
                `$column[name]` $column[type]";
                if (empty($column['defOrNull'])) {
                    $sqlCreate .= " NOT NULL";
                }
                if (!empty($column['defaultValue'])) {
                    $sqlCreate .= " DEFAULT '$column[defaultValue]'";
                } else {
                    if (isset($column['defOrNull']) && $column['defOrNull'] === true) {
                        $sqlCreate .= " DEFAULT NULL";
                    }
                }
            }
        }
        $sqlCreate .= ",
           PRIMARY KEY (`$input[pk]`)";
        if (!empty($indexes)) {
            $sqlCreate .= ', ' . implode(',
           ', $indexes);
        }

        $sqlCreate .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_estonian_ci";
        ($sqlCreate);
        try {
            $db->query($sqlCreate);
            echo "<span class='bg-success'>Uus tabel $input[tableName] on loodud!</span>";
            $this->addTableToList($input, $db);
        } catch (\mysqli_sql_exception $e) {
            $err = $e->getMessage();
            echo "<span class='bg-warning'>$err: Tabel $input[tableName] näikse juba olemas olevat, uut sellenimelist igatahes ei loodud.</span>";
            $this->addTableToList($input, $db);
        }

    }

    public function addTableToList($input, $db = null)
    {
        if (empty($db)) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $db = $this->cnn();
        }
        unset($input['id']);
        $props = [];
        $vals = [];
        $dbFields = [];
        $dataForRdSql = [];
        foreach ($input as $key => $value) {
            if (!is_array($value) && !is_object($value)) {
                $props[] = \Common\Helper::uncamelize($key);
                $vals[] = is_numeric($value) ? $value : "'$value'";
            }
            if ($key == 'data') {
                foreach ($value['fields'] as $field) {
                    $dbFields[] = \Common\Helper::uncamelize($field["name"]);
                }
            }
            if ($key == 'createdModified') {
                $creMo = $this->createdModifiedTmpl($value);
                $props[] = $creMo->cols;
                $vals[] = $creMo->vals;
            }

            if (in_array($key, ['belongsTo', 'hasMany', 'hasManyAndBelongsto'])) {
                foreach ($value as $relationDetails) {
                    $rdCols = [];
                    $rdVals = [];
                    foreach ($relationDetails as $rdKey => $rdValue) {
                        if ($rdKey != 'id') {
                            if ($rdKey == 'createdModified') {
                                $creMoRel = $this->createdModifiedTmpl($rdValue);
                                $rdCols[] = $creMoRel->cols;
                                $rdVals[] = $creMoRel->vals;
                            } else {
                                $rdCols[] = $rdKey == 'relation' ? 'relations_id' : \Common\Helper::uncamelize($rdKey);
                                $rdVals[] = is_numeric($rdValue) ? $rdValue : "'$rdValue'";
                            }

                        }
                    }
                    $dataForRdSql[$relationDetails['otherTable']] = ['rdCols' => $rdCols, 'rdVals' => $rdVals];
                    //$this->addRelation(current($input), $db->insert_id);
                }
            }
        }
        $propsList = implode(", ", $props);
        $valsList = implode(", ", $vals);
        $sql = "INSERT INTO `models` ($propsList)
        VALUES ($valsList);
        ";
        $db->query($sql);

        if (!empty($db->insert_id) && !empty($dataForRdSql)) {
            $this->addRelation($dataForRdSql, $db->insert_id);
        }
        //$read = new Read();
        //$newTable = $read->getTables(null,['t.id' => $db->insert_id]);

        // ($db->insert_id);

        //$stmt = $db->prepare($sql);
        //$stmt->execute();
        $db->close();
    }

    public function addRelation($input, $tableId)
    {

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();

        foreach ($input as $lists) {
            array_push($lists['rdCols'], 'models_id');
            array_push($lists['rdVals'], $tableId);
        }
        $keyList = implode(',', $lists['rdCols']);
        $valList = implode(",", $lists['rdVals']);
        $sql = "INSERT INTO relation_details ($keyList)
                        VALUES ($valList);";
        $db->query($sql);
        $db->close();
    }

    public function createdModifiedTmpl($value)
    {
        $res = new stdClass;
        foreach ($value as $k => $v) {
            if ($k == 'createdBy') {
                $res->cols = 'created_by';
                $res->vals = $v['id'];
            }
            if ($k == 'modifiedBy') {
                $res->cols = 'modified_by';
                $res->vals = $v;
            }
        }
        return $res;
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
