<?php namespace Admin;

require_once 'Autoload.php';

use \Controller\Table as TableController;

$request = isset($_SERVER['PATH_INFO']) ?  explode('/', $_SERVER['PATH_INFO']) : [];

$tc = new TableController();

//echo '<pre>';
//print_r($tc->pathParams($request));
//echo '</pre>';
$r = $tc->pathParams($request);

if (isset($r[1]) && $r[1] == 'tables') {
    echo json_encode($tc->getTables());
}
else {
    echo '{"request":';
        echo json_encode($tc->pathParams($request));
        echo ',';
        echo '"data":';
    echo json_encode($tc->getTables());
    echo '}';

}
