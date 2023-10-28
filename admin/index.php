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
            echo json_encode($tc->getField($r[2]), JSON_PRETTY_PRINT);
        } else {
            $key = is_numeric($r[2]) ? 'id' : 'name';
            $tc->getTableByIdOrName($key, $r[2]);
        }
    } else {
        echo $tc->getTables();
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
