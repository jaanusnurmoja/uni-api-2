<?php namespace Service;

use Model\Relation;
use Model\RelationDetails;
use Model\Relations;
use mysqli;
use \DTO\ListDTO;
use \DTO\TableDTO;
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
                $rel->setIsInner($row['is_inner']);

                $relationDetails->setId($row['rd_id']);
                $relationDetails->setRelation($rel);
                $relationDetails->setRole($row['role']);
                $relationDetails->setKeyField($row['key_field']);
                $relationDetails->setHasMany($row['hasMany']);
                $relationDetails->setOtherTable($row['other_table']);
            }
            if (empty($model) || (empty($model->getId()) || $model->getId() != $row['rowid'])) {
                $model = new Table();
                $model->setId($row['rowid']);
                $model->setName($row['table_name']);
                $model->setPk($row['pk']);
                $data = new Data();
                $data->setTable($model);
                if ($row['field_data'] == 'default') {
                    $fields = $this->getDefaultFields($row['table_name']);
                    $data->setFields($fields);
                }
                $model->setData($data);

            }
            $relationDetails->setTable($model);
            if ($relationDetails->getTable()->getId() == $row['rowid'] && $relationDetails->getId() == $row['rd_id']) {

                $model->addRelationDetails($relationDetails);
            }

            $single = new TableDTO($model);
            $rowList[$row['rowid']] =$single; 
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

 public function getRelations() {
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
    public function req($r = [])
    {
        $new = [];
        if (isset($r[1])) $new['type'] = $r[1];
        if (isset($r[2])) $new['item'] = $r[2];
        if (isset($r[3])) $new['subtype'] = $r[3];
        if (isset($r[4])) $new['subitem']= $r[4];
        $new['debug'] = 'ohoohhooi';
        return $new;
    }

}