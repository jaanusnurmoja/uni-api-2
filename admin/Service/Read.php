<?php namespace Service;

use mysqli;
use \DTO\ListDTO;
use \DTO\TableDTO;
use \Model\Data;
use \Model\Field;
use Model\RelationDetails;
use Model\Relations;
use Model\Relation;
use Model\Table;

class Read
{

    protected function cnn()
    {
        require __DIR__ . '/../../api/config.php';
        return new mysqli($host, $user, $pass, $dbname);
    }

    public function getTables(Table $model = null, $params = null, Relation $rel = null, RelationDetails $relationDetails = null, TableDTO $tableDTO = null)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $where = '';
        if ($params) {
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
            }
            if (empty($model->getId()) || $model->getId() != $row['rowid']) {
                $model = new Table();
                $model->setId($row['rowid']);
                $model->setName($row['name']);
                $model->setPk($row['pk']);
                $data = new Data();
                $data->setTable($model);
                if ($row['field_data'] == 'default') {
                    $fields = $this->getDefaultFields($row['name']);
                    $data->setFields($fields);
                }
                $model->setData($data);

            }
                $relationDetails->setTable($model);
                if ($relationDetails->getTable()->getId() == $row['rowid'] && $relationDetails->getId() == $row['rd_id']) {

                    $model->addRelationDetails($relationDetails);
                }

/*
if (empty($tableDTO) || $tableDTO->getId() != $row['rowid']) {
    $tableDTO = new TableDTO();
    $tableDTO->setId($model->getId());
    $tableDTO->setName($model->getName());
    $tableDTO->setPk($model->getPk());
    $tableDTO->setData($model->getData()->getFields());
    $tableDTO->setBelongsTo($model->getRelationDetails());
}
*/
            $rowList[$row['rowid']] = $model;
        }
//   \mysqli_free_result($q);
/*
        foreach($rowList as $dtoRow) {
            $hasMany = [];
            foreach ($relations->getRelationDetails() as $rels) {
                print_r($dtoRow->id);
                if ($rels->getTable()->getId() == $dtoRow->id) {
                    array_push($hasMany, $rels);
                }
            }
            if (!empty($hasMany)) {
                $dtoRow->hasMany = $hasMany;
            }
        }
*/
        $listDTO = new ListDTO($rowList);
        return $listDTO;
        //return $relations;

    }

    public function getDefaultFields($table) {
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
/*     public function getData($model, $data)
    {
        $fields = [];
        for ($i = 0; $i < count($fields); $i++) {
            $field = new Field();
            $field->setName();

        }
    }
 */
    public function req($r)
    {
        $r['debug'] = 'ohoohhooi';
        return $r;
    }

}