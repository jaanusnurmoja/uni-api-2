<?php namespace Service;

use mysqli;
include_once __DIR__ . '/../Model/RelationDetails.php';
include_once __DIR__ . '/../Model/Relation.php';
include_once __DIR__ . '/../Model/Table.php';
include_once __DIR__ . '/../Model/Field.php';
include_once __DIR__ . '/../Dto/TableDTO.php';
include_once __DIR__ . '/../Dto/ListDTO.php';

use \Dto\ListDTO;
use \Dto\TableDTO;
use \Model\Data;
use \Model\Field;
use \Model\Relation;
use \Model\RelationDetails;
use \Model\Table;

class Read
{

    protected function cnn()
    {
        // require __DIR__ . '/../../api/config.php';
        // return new mysqli($host, $user, $pass, $dbname);
        $cnf = parse_ini_file(__DIR__ . '/../../config/connection.ini');
        return new mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);

    }

    public function getTables(Table $model = null, $params = [], Relation $rel = null, RelationDetails $relationDetails = null, TableDTO $tableDTO = null)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $where = '';
        if (!empty($params)) {
            $w = [];
            foreach ($params as $key => $value) {
                $w[] = " $key = '$value'";
            }
            $where = ' WHERE' . implode(' AND', $w);
        }

        $query = "SELECT t.id as rowid, t.*, f.id as fid, f.name as field, rd.id as rd_id, rd.*, r.id as rid, r.* FROM models t
        LEFT JOIN fields f ON f.models_id = t.id
        LEFT JOIN relation_details rd ON rd.models_id = t.id
        LEFT JOIN relations r ON r.id = rd.relations_id
        $where";
        print_r($query);
        $q = $db->query($query);

        $rowList = [];
        $rowsDebug = [];
        $single = null;

        while ($row = $q->fetch_assoc()) {
            unset($row['id']);
            $rowsDebug[] = $row;
            while ($row['rd_id'] != null && (empty($relationDetails) || $relationDetails->getId() != $row['rd_id'])) {
                $relationDetails = new RelationDetails();
                $rel = new Relation();
                $rel->setId($row['rid']);
                $rel->setType($row['type']);
                $rel->setAllowHasMany((bool) $row['allow_has_many']);
                $rel->setIsInner((bool) $row['is_inner']);

                $relationDetails->setId($row['rd_id']);
                $relationDetails->setRelation($rel);
                $relationDetails->setRole($row['role']);
                $relationDetails->setKeyField($row['key_field']);
                $relationDetails->setHasMany((bool) $row['hasMany']);
                $relationDetails->setOtherTable($row['other_table']);
            }
            if (empty($model) || (empty($model->getId()) || $model->getId() != $row['rowid'])) {
                $model = new Table();
                $model->setId($row['rowid']);
                $model->setTableName($row['table_name']);
                $model->setPk($row['pk']);
                $data = new Data();
                $data->setTable($model);
                if ($row['field_data'] == 'default') {
                    $fields = $this->getDefaultFields($row['table_name']);
                    $data->setFields($fields);
                }
                $model->setData($data);

            }
            if (!empty($relationDetails)) {
                $relationDetails->setTable($model);
                if ($relationDetails->getTable()->getId() == $row['rowid'] && $relationDetails->getId() == $row['rd_id']) {

                    $model->addRelationDetails($relationDetails);
                }
            }

            $single = new TableDTO($model);
            $rowList[$row['rowid']] = $single;
        }
        if (!empty($params) && count($rowList) == 1) {
            return $single;
        } else {
            return new ListDTO($rowList);
        }

    }

    public function getDefaultFields($table)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $query = "SHOW COLUMNS FROM $table";
        $q = $db->query($query);
        $fields = [];
        while ($row = $q->fetch_assoc()) {
            if (empty($row['Key'])) {
                $field = new Field();
                $field->setName($row['Field']);
                $field->setType($row['Type']);
                $fields[$row['Field']] = $field;
            }
        }
        return $fields;
    }

    public function getRelations()
    {
        $relations = [];
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();

        $query = "SELECT * FROM relations";
        $q = $db->query($query);

        while ($row = $q->fetch_assoc()) {

            $rel = new Relation();
            $rel->setId($row['id']);
            $rel->setType($row['type']);
            $rel->setAllowHasMany((bool) $row['allow_has_many']);
            $rel->setIsInner($row['is_inner']);
            array_push($relations, $rel);

        }
        return $relations;
    }

    /**See https: //www.barattalo.it/coding/php-to-get-enum-set-values-from-mysql-field/
 */
    public function setAndEnumValues($table, $field)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $query = "SHOW COLUMNS FROM `$table` LIKE '$field'";
        $result = $db->query($query) or die('Error getting Enum/Set field ' . $db->error);
        $row = $result->fetch_array();
        if (stripos("." . $row[1], "enum(") > 0) {
            $row[1] = str_replace("enum('", "", $row[1]);
        } else {
            $row[1] = str_replace("set('", "", $row[1]);
        }

        $row[1] = str_replace("','", "\n", $row[1]);
        $row[1] = str_replace("')", "", $row[1]);
        $ar = explode("\n", $row[1]);
        for ($i = 0; $i < count($ar); $i++) {
            $arOut[str_replace("''", "'", $ar[$i])] = str_replace("''", "'", $ar[$i]);
        }

        return $arOut;
    }

    public function req($r = [])
    {
        $new = [];
        if (isset($r[1])) {
            $new['type'] = $r[1];
        }

        if (isset($r[2])) {
            $new['item'] = $r[2];
        }

        if (isset($r[3])) {
            $new['subtype'] = $r[3];
        }

        if (isset($r[4])) {
            $new['subitem'] = $r[4];
        }

        $new['debug'] = 'ohoohhooi';
        return $new;
    }

}