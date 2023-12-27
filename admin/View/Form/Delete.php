<?php namespace View\Form;

include_once __DIR__ . '/../../Controller/Table.php';
use Controller\Table;
$tc = new \Controller\Table;
if (!empty($_POST)) {
    $redirect = $_POST['callback'];
    $id = $_POST['delId'];
    if (isset($_POST['remove'])) {
        $tc->deleteTable($id, true);
        header("Location:$redirect");
    }
}