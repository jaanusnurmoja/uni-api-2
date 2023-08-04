<?php namespace Admin;
spl_autoload_register(function ($class) {
    include $class . '.php';
});
use Model;
echo 'Hello world!';
echo '<hr>';
$model = new Model\Table;
echo $model->hello;
