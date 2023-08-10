<?php namespace Admin;

spl_autoload_register(function ($class) {
    include $class . '.php';
});

use \Model\Relation;
use \Model\RelationDetails;
use \Model\Relations;

$relations = new Relations;

echo 'Hello world!';
echo '<hr>';

$model = new \Model\Table(true);
echo $model->hello;
$model->setId(1);
$model->setName('table');
$data = new \Model\Data;
$fields = [];
for ($i = 0; $i < 3; $i++) {
    $f = new \Model\Field;
    $f->setId($i + 1);
    $f->setName('title ' . $i + 1);
    $f->setType('string');
    $f->setHtmlDefaults('{"field":"input","type":"text"}');
    $fields[$f->getName()] = $f;
}

$belongsTo = [];
$hasMany = [];
$hasManyAndBelongsTo = [];
$bt = [];
for ($i = 0; $i < 3; $i++) {
    for ($d = 0; $d < 5; $d++) {
        $bt[$d] = new RelationDetails;
        $rel = new Relation;
        $rel->setId($d + 1);
        $rel->setType('belongsTo');
        $bt[$d]->setId($d + 10);
        $bt[$d]->setRelation($rel);
        $bt[$d]->setRole('roll' . $d);
    }
    $relations->setRelationDetails($bt);

}

$cleanModel = $model->clean();
$data->setTable($cleanModel);
$data->setFields($fields);
$model->setData($data);
$model->setBelongsTo($relations);
echo '<hr>';
echo '<pre>';
print_r($model);
echo '</pre>';