<?php namespace Service;

use mysqli;
use \DTO\ListDTO;
use \DTO\TableDTO;
use \Model\Data;
use \Model\Field;
use Model\RelationDetails;
use Model\Relations;
use Model\Relation;

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

        $query = "SELECT * FROM models 
        LEFT JOIN fields ON fields.models_id = models.id
        LEFT JOIN relation_details ON relation_details.models_id = models.id
        LEFT JOIN relations ON relations.id = relation_details.relations_id
        $where";

        $q = $db->query($query);

        $rowList = [];
        $i = 0;

        while ($row = $q->fetch_assoc()) {
            $arrangedRow = [];
            $fields = [];
            $rd = [];
            $finfo = $q->fetch_fields();
            $modelId = null;
            //$r = new \Model\Table();
            foreach ($finfo as $f) {
                //$modelName = ucwords($f->table);

                if ($f->table == 'models'){
                    $arrangedRow['models'][$f->name] = $row[$f->name];
                }

                if ($f->table == 'fields'){
                    $fr = [];
                    for ($fn=0;$fn<count($fr);$fn++){
                        $arrangedRow['models']['fields'][$fn][$f->name] = $row[$f->name];
                        $fr[$fn] = $row;
                    }
                }

                if ($f->table == 'relation_details') {
                    $x = [];
                    for ($rn = 0;$rn < count($x);$rn++) {
                        $arrangedRow['models']['relation_details'][$rn][$f->name] = $row[$f->name];
                        if ($f->table == 'relations') {
                            $arrangedRow['models']['relation_details'][$rn]['relations'][$f->name] = $row[$f->name];
                        }
                        $x[$rn] = $row;
                    }
                }
           }

            $rowList[$i] = $arrangedRow;
            
            //$rowList[$row['id']] = $tableDTO;

            $i++;
        }
//   \mysqli_free_result($q);

        return new ListDTO($rowList);

    }

    public function setModels($model) {
        $relationDetails = new RelationDetails();
        $rel = new Relation();
        $relations = new Relations();
        $data = new Data();
        $data->setTable($model);
        
        $model->setData($data);

        $relations->setTable($model);
        $relations->setRelationDetails($relationDetails);

        if ($relationDetails->getRole() == 'belongsTo') {
            $model->setBelongsTo($relations);
        }            

        $tableDTO = new TableDTO;
        $tableDTO->setId($model->getId());
        $tableDTO->setName($model->getName());
        $tableDTO->setPk($model->getPk());
        $tableDTO->setData($model->getData()->getFields());
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