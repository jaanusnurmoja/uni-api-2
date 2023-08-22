<?php namespace Service;

use mysqli;
use \DTO\ListDTO;
use \DTO\TableDTO;
use \Model\Data;
use \Model\Field;

class Read
{

    protected function cnn()
    {
        require_once __DIR__ . '/../../api/config.php';
        // return mysqli_cnn($host, $user, $pass, $dbname);
        return new mysqli($host, $user, $pass, $dbname);
    }

    public function getTables($model = null, $params = null)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $where = '';
        $rowList = [];
        if ($params) {
            $w = [];
            foreach ($params as $key => $value) {
                $w[] = " $key = '$value'";
            }
            $where = ' WHERE' . implode(' AND', $w);
        }

        $query = "SELECT t.*, f.name as field FROM models t
        LEFT JOIN fields f ON f.models_id = t.id
        LEFT JOIN relation_details rd ON rd.models_id = t.id
        LEFT JOIN relations r ON r.id = rd.relations_id
        $where";

        $q = $db->query($query);

        while ($row = $q->fetch_assoc()) {
            //$r = new \Model\Table();
            $model->setId($row['id']);
            $model->setName($row['name']);
            $model->setPk('id');
            $data = new Data();
            $getData = $this->getData($model, $data);
            $model->setData();
            //print_r($model);

            $tableDTO = new TableDTO;
            $tableDTO->setId($model->getId());
            $tableDTO->setName($model->getName());
            $tableDTO->setPk($model->getPk());

            $rowList[$row['id']] = $tableDTO;
        }
//   \mysqli_free_result($q);

        $dto = new ListDTO($rowList);
        return $dto;

    }

    public function getData($model, $data)
    {
        $fields = [];
        for ($i = 0; $i < count($fields); $i++) {
            $field = new Field();
            $field->setName();

        }
    }

    public function req($r)
    {
        $r['debug'] = 'ohoohhooi';
        return $r;
    }

}