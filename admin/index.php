<?php namespace Admin;

require_once 'Autoload.php';

use \Controller\Table as TableController;

$request = isset($_SERVER['PATH_INFO']) ?  explode('/', $_SERVER['PATH_INFO']) : [];

$tc = new TableController();

//echo '<pre>';
//print_r($tc->pathParams($request));
//echo '</pre>';
$r = $tc->pathParams();

if (isset($r[1]) && $r[1] == 'tables') {
    if (isset($r[2])) {
        if (isset($r[3]) && $r[3] == 'fields' && isset($r[4])) {
            echo json_encode($tc->getField(), JSON_PRETTY_PRINT);
        } else {
            echo json_encode($tc->getTableByIdOrName(), JSON_PRETTY_PRINT);
        }
    } else {
        echo json_encode($tc->getTables());
    }
}
else {
    echo '{
        "juhend":"lisa url-ile tables/tabelinimi/fields/väljanimi",
        "request":';
        echo json_encode($tc->pathParams($request));
        echo ',';
        echo '"data":';
    echo json_encode($tc->getTables());
    echo '}';

}
