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
function getDataWithRelations($table = null, $pkValue = null, $fkValue = null)
{
    $d = [];
    $thisTableData = [];
    global $request;
    $relations = getRelations();
    if (empty($table)) {
        $table = $request[1];
        if (isset($request[2])) {
            $pkValue = $request[2];
            $relations[$table]['rowid'] = $pkValue;
        }
    }

    $thisTableData = $relations[$table];

    $r = [];

    foreach ($relations as $rtbl => $relation) {
        if (isset($relation['belongsTo'][$table])) {
            //$childTableData = $relations[$rtbl];
            $r = getDataWithRelations($rtbl, null, $pkValue);
            $thisTableData['hasMany'][$rtbl] = $r[$rtbl];
        }
    }
    $d[$table] = $thisTableData;
    return $d;
}

function getColumns($table, $parent = null)
{
    global $link;
    $cols = new stdClass();
    $cols->list = [];
    $cols->withAlias = [];
    $alias = $parent ? "$parent:" : '';
    $sql = "SHOW COLUMNS FROM `$table`";
    if ($result = $link->query($sql)) {
        while ($column = $result->fetch_assoc()) {
            $cols->list[] = "`$table`.`{$column['Field']}`";
            $cols->withAlias[] = "`$table`.`{$column['Field']}` AS `$alias{$column['Field']}`";
        }
    }
    return $cols;
}

function getJoinColumns($table, $tableData, $parent, $cols = '')
{
    $cols .= implode(', ', getColumns($table, $parent)->withAlias);
    if (isset($tableData['hasMany'])) {
        foreach ($tableData['hasMany'] as $t => $d) {
            $newParent = "$parent:$t";
            $cols .= ', ';
            $cols .= getJoinColumns($t, $d, $newParent);
        }
    }
    return $cols;
}
function buildQuery()
{
    foreach (getDataWithRelations() as $table => $tableData) {

        $columns = "$table.{$tableData['pk']} AS `rowid`, ";
        $columns .= implode(', ', getColumns($table)->withAlias);
        if (isset($tableData['hasMany'])) {
            foreach ($tableData['hasMany'] as $jt => $jtData) {
                $columns .= ', ' . getJoinColumns($jt, $jtData, $jt);
            }
        }
        $sql = "SELECT $columns FROM `$table`
        ";
        if (isset($tableData['hasMany'])) {
            foreach ($tableData['hasMany'] as $joinTable => $joinTableData) {
                $sql .= buildQueryJoins($joinTable, $joinTableData, $table, $tableData);
            }
        }

        if (isset($tableData['rowid'])) {
            $sql .= "WHERE `$table`.`{$tableData['pk']}` = {$tableData['rowid']}";
        }
        return $sql;

    }
}

function buildQueryJoins($joinTable, $joinTableData, $table, $tableData, $sql = null)
{
    foreach ($joinTableData['belongsTo'][$table] as $join) {
        $sql .= "LEFT JOIN `$joinTable` ON
        `$joinTable`.`{$join['fk']}` = `$table`.`{$tableData['pk']}`
        ";
    }
    if (isset($joinTableData['hasMany'])) {
        foreach ($joinTableData['hasMany'] as $nextTable => $nextTableData) {
            $sql .= buildQueryJoins($nextTable, $nextTableData, $joinTable, $joinTableData);
        }

    }
    return $sql;
}

function splitColsBySeparator($col)
{

    $attribute = explode(':', $col, 2);
    if (count($attribute) == 1) {
        $subCols[$col] = 'value';
    } elseif (!strpos($attribute[1], ':')) {
        $subCols[$attribute[0]][$attribute[1]] = 'value';
    } else {
        $subCols[$attribute[0]] = splitColsBySeparator($col);
    }
    return $subCols;

}

function buildQueryResults($data)
{
    $d = [];

    foreach ($data as $rowid => $dataRows) {
        $d[$rowid] = [];
        $cols = [];
        $c = [];
        foreach (array_keys($dataRows[0]) as $idKey) {
            if ($idKey == 'id' || str_ends_with($idKey, ':id')) {
                foreach (array_keys($dataRows[0]) as $rKey) {
                    $c = cols($dataRows, $rKey, $idKey);
                    if ($c[$rKey]) $cols[$rKey] = $c[$rKey];
                }
            }
        }
        $d[$rowid] = $cols;
    }

    return $d;
}

function cols($dataRows, $rKey, $idKey, $oldRk = null, $cols=[], $arrayCols=[]){
    $colColon = strrpos($rKey, ':');
    if ($oldRk == null) {
        $oldRk = $rKey;
        if (!$colColon) {
            $cols[$rKey] = $dataRows[0][$oldRk];
        }
    }
    else {
        $idColon = strrpos($idKey, ':');
        $idKeyParts = explode(':',$idKey,2);
        $rKeyParts = explode(':',$rKey,2);

        if (substr($rKey, 0, $colColon) == substr($idKey, 0, $idColon)) {
            //$cols[$rKey] = array_column($dataRows, $rKey, $idKey);
            $arrayCols[$oldRk] = array_column($dataRows, $oldRk, $idKey);
            $arrayCols[$idKey] = array_column($dataRows, $idKey);
            foreach ($arrayCols[$idKey] as $i) {
                if (!$colColon) {
                    $cols[$i][$rKey] = $arrayCols[$oldRk][$i];
                }
            else {
                $cols[$i]['hasMany'][$rKeyParts[0]][$i] = cols($dataRows, $rKeyParts[1], $idKey, $rKey, $cols, $arrayCols);
                    //}
                }
            }
            $cols['hasMany'][$rKeyParts[0]] = $cols;
       }
    }
    return $cols;

}

function keySplitter($arrayCols, $keyPart1, $keyPart2, $idPart2, $newCol = [], $row = [])
{
    $newKeyParts = explode(':', $keyPart2, 2);
    $newIdParts = explode(':', $idPart2, 2);
    foreach ($arrayCols[$keyPart1.':'.$idPart2] as $id) {
        foreach ($arrayCols as $subKey => $subVals) {
            $subKeyParts = explode(':', $subKey, 2);
            if ($subKeyParts[1] == $newKeyParts[1]) {
                if (!strpos($subKeyParts[1],':')) {
                    $row[$id][$subKeyParts[1]] = 'debug 2 ' . $subVals[$id];
                    $newCol[$newKeyParts[0]][$id][$subKeyParts[1]] = $row[$id][$subKeyParts[1]];
                }
                else {
                    $newCol[$newKeyParts[0]][$id]['hasMany'] = keySplitter($arrayCols, $newKeyParts[0], $newKeyParts[1], $newIdParts[1]);
                }
            }
        }
    }
    
// kuidas näidata seotud alamridu?

    return $newCol;
}

switch (count($request)) {

    case 2:
    case 3:
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