<?php namespace Service;

use Model\Relation;
use Model\RelationDetails;
use Model\Relations;
use mysqli;
use \DTO\ListDTO;
use \DTO\TableDTO;
use \Model\Data;
use \Model\Field;

class Read
{

    protected function cnn()
    {
        require __DIR__ . '/../../api/config.php';
        return new mysqli($host, $user, $pass, $dbname);
    }

    public function getTables($model = null, $params = null)
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

        $relations = new Relations();
        $rowList = [];

        while ($row = $q->fetch_assoc()) {
            $relationDetails = new RelationDetails();
            $rel = new Relation();
            //$r = new \Model\Table();
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
            if (!empty($row['rid'])) {
                $rel->setId($row['rid']);

                $rel->setType($row['type']);
                $rel->setAllowHasMany((bool) $row['allow_has_many']);
                $rel->setIsInner((bool) $row['is_inner']);
            }

            //$relations->setTable($model);
            $relations->setRelationDetails($relationDetails);

                    $relations->setTable($model);
                    $relations->setRelationDetails($relationDetails);

                    $model->setBelongsTo($relations);
                }
            }

//            $getData = $this->getData($model, $data);
            //           $model->setData();
            //print_r($model);

            $tableDTO = new TableDTO;
            $tableDTO->setId($model->getId());
            $tableDTO->setName($model->getName());
            $tableDTO->setPk($model->getPk());
            $tableDTO->setData($model->getData()->getFields());

            //$rowList[$row['id']] = $tableDTO;
            $rowList[$row['rowid']] = $tableDTO;
        }
//   \mysqli_free_result($q);

        return new ListDTO($rowList);

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