<?php namespace Admin;

require_once 'Autoload.php';

use \Controller\Table as TableController;
use \Model\Table;

$request = explode('/', $_SERVER['PATH_INFO']);

echo 'Hello world!';
echo '<hr>';

$tc = new TableController();

$model = new Table(true);
echo $model->hello;
echo '<hr>';
echo '<pre>';
print_r($tc->pathParams($request));
echo '</pre>';
echo '<pre>';
print_r($tc->getTables($model));
echo '</pre>';
/*
$model->setId(1);
$model->setName('table');
$data = new Data;
$fields = [];
for ($i = 0; $i < 3; $i++) {
$f = new Field;
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
$relations = [];

for ($i = 0; $i < 3; $i++) {
$relations[$i] = new Relations;
for ($d = 0; $d < 5; $d++) {
$bt[$d] = new RelationDetails;
$rel = new Relation;
$rel->setId($d + 1);
$rel->setType('belongsTo');
$bt[$d]->setId($d + 10);
$bt[$d]->setRelation($rel);
$bt[$d]->setRole('roll' . $d);
}
$relations[$i]->setRelationDetails($bt);

}

$cleanModel = $model->clean();
$data->setTable($cleanModel);
$data->setFields($fields);
$model->setData($data);
$model->setBelongsTo($relations[0]);
$model->setHasMany($relations[1]);
$model->setHasManyAndBelongsTo($relations[2]);
echo '<hr>';
echo '<pre>';
print_r($model);
echo '</pre>';
 */