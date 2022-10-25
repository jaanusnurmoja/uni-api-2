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
            $cols->list[] = `$table` . `{$column['Field']}`;
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



switch (count($request)) {

    case 2:
    case 3:
        require_once './core/single_table.php';
        break;
        // echo("NO RELAZIONE");
        //$sql = buildQuery();
       //echo "<pre>$sql</pre>";
        //echo json_encode(getDataWithRelations());
        //$result = mysqli_query($link, $sql);
        //$rows = [];
/*         while($row = $result->fetch_assoc()) {
            $rows[$row['rowid']][] = $row;
        }
        echo json_encode($rows);
 */     
    case 4:
    case 5:
        require_once './core/multi_table.php';
        break;
    default:
        echo (json_encode(array('error' => 'Welcome on Uni-API!')));
        break;
}

/*
function buildQueryResults($data)
{
    $d = [];
    $newItem = [];
    $table = getRequest()[1];

    $structure = getDataWithRelations();
    print_r($structure);
     foreach ($data as $rowid => $rowData) {
        foreach ($rowData as $key => $item) {
            $recursive = [];
            $itemRowId = [];
            foreach ($item as $fieldKey => $fieldItem) {
                if (strpos($fieldKey, ':id')) {
                    $itemRowId[$fieldKey] = $fieldItem;
                }

            }
            foreach ($item as $fieldKey => $fieldItem) {
                if (!strpos($fieldKey, ':')) {
                    $special = false;
                    //unset($fieldItem);
                    if ($fieldKey == $structure[$table]['pk']) {
                        $d[$rowid]['id'][$fieldKey] = $fieldItem;
                        $special = true;
                    }
                    if (isset($structure['belongsTo'])) {
                        foreach ($structure[$table]['belongsTo'] as $belongsToTable => $belongsTo) {
                            if ($fieldKey == $belongsTo['fk']) {
                                $d[$rowid]['belongsTo'][$belongsToTable]['fk'][$fieldKey] = $fieldItem;
                            }
                        }
                        $special = true;
                    }
                    if ($special === false) {
                        $d[$rowid]['data'][$fieldKey] = $fieldItem;
                    }
                } else {
 */                    /*                     if (strpos($fieldKey, ':id')) {
                    $id = $fieldItem;
                    }
                     */
/*                     $fieldArr = explode(':', $fieldKey, 2);
                    $d[$rowid]['hasMany'][$fieldArr[0]] = [];
                    foreach ($rowData as $relKey => $relItem) {
                        foreach ($relItem as $rKey => $rData) {
                            if ($rKey == $fieldKey) {
                                $d[$rowid]['hasMany'][$fieldArr[0]][$relKey][$fieldArr[1]] = get_recursive_var($fieldArr, $recursive, $rData, $itemRowId[$fieldKey]);
                            } // $related = $recursive[$fieldArr[0]];
                        }
 */                        /*                     if (!strpos($fieldArr[1], ':'))
                        {
                        if ($fieldArr[1] == 'id' && !empty($fieldItem)) $id = $fieldItem;
                        $d[$rowid]['hasMany'][$fieldArr[0]][$id][$fieldArr[1]] = $fieldItem;
                        //$d[$rowid]['hasMany'][$fieldArr[0]] = array_unique($d[$rowid]['hasMany'][$fieldArr[0]]);
                        }
                         *///$d[$rowid][$key][$fieldKey]['v'] = $fieldItem;                        //$d[$rowid][$key][$fieldKey]['fieldKeys'] = $fieldArr;
/*                 }
            }
            // $d[$rowid]['hasMany'] = $recursive;
        }
        //$mainData = array_combine($mainData);
    }
    // foreach (getDataWithRelations() as $table => $tableData) {

    // }
    return $d;
} 
*/

/*
function get_recursive_var($keys, $arr, $value, $id = null)
{
    $finalArray = [];
    if (strpos($keys[1], ':')) {
        $nextKeys = explode(':', $keys[1]);
        $newArr = array();
        $newArr['hasMany'] = get_recursive_var($nextKeys, $newArr[$nextKeys[0]], $value, $id);
        array_push($arr[$keys[0]], $newArr);
    } else {
        //$arr[$keys[0]][][$keys[1]] = $value;
        $arr[$keys[1]] = $value;
        array_push($finalArray, $arr);
    }
    return $finalArray[$keys[1]];

}
*/
// close mysql connection
mysqli_close($link);