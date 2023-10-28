<?php namespace Admin;

require_once 'Autoload.php';

use \Controller\Table as TableController;

$request = isset($_SERVER['PATH_INFO']) ? explode('/', $_SERVER['PATH_INFO']) : [];
$api = isset($_GET['api']) ? true : false;
$tc = new TableController();

//echo '<pre>';
//print_r($tc->pathParams($request));
//echo '</pre>';

?>
<!DOCTYPE html>
<html lang="et">

<head>
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</head>

<body>
    <div class="container">
        <?php
$r = $tc->pathParams();

if (isset($r[1]) && $r[1] == 'tables') {
    if (isset($r[2])) {
        if (isset($r[3])) {
            if ($r[3] == 'fields' && isset($r[4])) {
                echo json_encode($tc->getField(), JSON_PRETTY_PRINT);
            }
        } else {
            echo json_encode($tc->getTableByIdOrName($api), JSON_PRETTY_PRINT);
        }
    } else {
        echo json_encode($tc->getTables($api));
    }
} else {
    echo '{
        "juhend":"lisa url-ile tables/tabelinimi/fields/väljanimi",
        "request":';
    echo json_encode($tc->pathParams($request));
    echo ',';
    echo '"data":';
    echo json_encode($tc->getTables());
    echo '}';

}
?>
    </div>
</body>

</html>