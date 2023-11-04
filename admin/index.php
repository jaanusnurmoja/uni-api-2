<?php namespace Admin;

require_once 'Autoload.php';

use \Controller\Table as TableController;

$request = isset($_SERVER['PATH_INFO']) ? explode('/', $_SERVER['PATH_INFO']) : [];
$api = isset($_GET['api']) ? true : false;
$tc = new TableController();

//echo '<pre>';
//print_r($tc->pathParams($request));
//echo '</pre>';

if (!$api) {
    ?>
<!DOCTYPE html>
<html lang="et">

<head>
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</head>

<body>
    <nav class="navbar sticky-top navbar-dark bg-dark">
        <div class="container-fluid">
            <ul class="navbar-nav nav-pills list-group-horizontal">
                <li class="nav-item">
                    <a class="navbar-brand" href="/uni-api/admin">Admin</a>
                </li>
                <li class="nav-item">
                    <a class="navbar-brand" href="/uni-api/admin/tables">Tabelid</a>
                </li>
            </ul>
            <a class="navbar-brand" href="/uni-api">Sait</a>

        </div>
    </nav>

    <div class="container">
        <?php
}
$r = $tc->pathParams();

if (!empty($r['type']) && $r['type'] == 'tables') {
    if (!empty($r['item'])) {
        if (!empty($r['subtype'])) {
            if ($r['subtype'] == 'fields' && !empty($r['subitem'])) {
                echo json_encode($tc->getField(), JSON_PRETTY_PRINT);
            }
            else {
                if ($r['subtype'] == 'edit') {
                    $tc->getTableByIdOrName();
                }
            }
        } else {
                if ($r['item'] == 'new') {
                    $tc->newTable();
                }
                else {
                    if ($api){
                        echo json_encode($tc->getTableByIdOrName($api), JSON_PRETTY_PRINT);
                    } else {
                        $tc->getTableByIdOrName();
                    }
                }
        }
    } else {
        if ($api){
            echo json_encode($tc->getTables($api), JSON_PRETTY_PRINT);
        }
        else {
            $tc->getTables($api);
        }

    }
} else {
    if (empty($r['type'])) {
        $tc->getTables($api);
    }

}
if (!$api) {
    ?>
    </div>
</body>

</html>
<?php }?>