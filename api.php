<?php
ini_set('always_populate_raw_post_data', -1);
ini_set('display_errors', 0);

error_reporting(0);

require_once 'config.php';

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', $_SERVER['PATH_INFO']);
function getRequest()
{
    return explode('/', $_SERVER['PATH_INFO']);
}
$input = json_decode(file_get_contents('php://input'), true);

// connect to the mysql database
$link = mysqli_connect($host, $user, $pass, $dbname);
mysqli_set_charset($link, 'utf8');

/**
 * Set response status code and print an JS Object with error's info
 *
 * @param Integer $status_code  Status code
 * @param String  $message      Error's info
 */
function error_response($status_code, $message)
{
    echo (json_encode(array('error' => $message)));
    http_response_code($status_code);
    exit();
}

/**
 * Token validation
 *
 * @param String  $token Token to validate
 * @return Boolean Return true if there is a user with this token
 *
 */
function check_token()
{
    global $link;
    if (isset(getallheaders()["Authorization"])) {
        $token_check = mysqli_query(
            $link,
            "SELECT COUNT(username) as res
          FROM user
          WHERE token = '" . getallheaders()["Authorization"] . "'"
        );
        if (mysqli_fetch_object($token_check)->res == 1) {
            return true;
        } else {
            error_response(403, 'Invalid Token!');
            return false;
        }
    } else {
        error_response(403, 'Unauthorized!');
        return false;
    }
}

function getRelations()
{
    return json_decode(file_get_contents('relations.json'), true);
}

// var_dump($request);
function getDataWithRelations($tableData = [], $table = null, $foreignKey = null, $parentKey = null)
{
    $d = [];
    $thisTableData = [];
    $request = getRequest();
    if (empty($tableData)) {
        $table = $request[1];
        $thisTableData = $d[$table];
    } else { $thisTableData = $tableData;}
    if (!empty($foreignKey)) {
        $thisTableData['belongsTo']['fk'] = $foreignKey['fk'];
    }
    // $d['table']['name'] = $table;
    $relations = getRelations();
    $r = [];

    foreach ($relations as $rtbl => $relation) {
        if (isset($relations[$rtbl]['belongsTo'][$table])) {
            $childTableData = $relations[$rtbl]['belongsTo'][$table];
            $r = getDataWithRelations($childTableData, $rtbl, $relation['fk'], $d['table']['pk']);
            $thisTableData['hasMany'][$rtbl] = $r[$rtbl];
        }
    }
    $d[$table] = $thisTableData;
    return $d;
}
print_r(getDataWithRelations([], $table));

switch (count($request)) {

    case 2:
    case 3:
        // echo("NO RELAZIONE");
        require_once './core/single_table.php';
        break;
    case 4:
    case 5:
        require_once './core/multi_table.php';
        break;
    default:
        echo (json_encode(array('error' => 'Welcome on Uni-API!')));
        break;
}

// close mysql connection
mysqli_close($link);