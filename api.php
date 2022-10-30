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
    }
        if (isset($request[2])) {
            $pkValue = $request[2];
            $relations[$table]['rowid'] = $pkValue;
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

function getDataStructure($table = null) {
    global $request;

    if ($table == null) {
        $table = $request[1];
    }
    
    return getDataWithRelations($table)[$table];
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
            $newParent = $t;
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

function getKeys($data) {
    
    $keys = [];
    $keys['all'] = array_keys($data);
    foreach ($keys['all'] as $key) {
        if ($key == 'id' || str_ends_with($key, ':id')) {
            $idKey = $key;
            $keys['ids'][] = $key;
        }
    }
    return $keys;
}

function getValues($keys, $dataRows) {
    
    $colsData = [];
    foreach ($keys['ids'] as $idKey) {
        $idColon = strrpos($idKey, ':');
        $idParent = substr($idKey, 0, $idColon);
        $colsData['ids'][$idParent] = array_unique(array_column($dataRows, $idKey));
        foreach ($keys['all'] as $rKey) {
            $colColon = strrpos($rKey, ':');
            $colParent = substr($rKey, 0, $colColon);
            if ($colParent == $idParent) {
                $colsData['all'][$rKey] = array_column($dataRows, $rKey, $idKey);
            }
        }
    }
    return $colsData;
}

function splitKey($currentKey, $cKeyPart2, $allColValues, $currentIdList, $joinedCol=[], $splitted = []) {

    $cKeyParts = explode(':', $cKeyPart2, 2);
    foreach ($allColValues as $cKey => $cList) {
        if ($cKey == $currentKey) {
            foreach ($cList as $id => $cVal) {
                if (!str_contains($cKeyParts[1], ':')) {
                    $joinedCol[$cKeyParts[0]][$id][$cKeyParts[1]] = $cVal;
                } else {
                    $splitted += splitKey($currentKey, $cKeyParts[1], $allColValues, $currentIdList);
                    foreach($splitted as $sKey => $sVals) {
                        foreach($sVals as $i => $v) {
                            $joinedCol[$cKeyParts[0]][$id]['hasMany'][$sKey][$i] = $v;
                        }
                    }
                }
            }

        }
    }


    return $joinedCol;
}

function isInHasManyOf($lookup, $table = null) {

    $dataStructure = getDataStructure($table);
    if (isset($dataStructure['hasMany'][$lookup])) {
        return true;
    }
}

function buildQueryResults($data)
{
    global $request;
    $d = [];
    $keys = getKeys($data[1][0]);
 
    foreach ($data as $rowid => $dataRows) {
        $d[$rowid] = [];
        $cols = [];

        $colValues = getValues($keys, $dataRows);
                //print_r($colValues['all']);

        foreach ($colValues['ids'] as $key => $idList) {
            foreach ($colValues['all'] as $cKey => $cList) {
            $splitted = [];
                $colParts = explode(':', $cKey, 2);
                $colField = $colParts[1];
                    foreach ($cList as $id => $cVal) {
                        //$cVal = $cList[$id];
                //print_r($colValues['all'][$colParts[0].':'.$colParts[1]][$id] . ' / ');
                        $cKeyParts = explode(':', $cKey, 2);
                        if (!str_contains($cKey, ':')) {
                            $cols[$cKey] = $cVal;
                        } else {
                        if ($colParts[0] == $key || isInHasManyOf($cKeyParts[0], $key)) {
                            $cTable = $cKeyParts[0];
                            $relations = getDataStructure();
                            $prevId = $id;
                            if (isInHasManyOf($key, $request[1])) {
                                $cols['hasMany'][$key][$id][$cKeyParts[1]] = $cVal;
                            } 
                            if (!empty($key) && !empty($cVal) && isInHasManyOf($cKeyParts[0], $key)) {
                                $cols['hasMany'][$key][$id]['hasMany'][$cKeyParts[0]] = $request[1] . " alamtabelis $key on olemas alamtabel $cKeyParts[0]";
                            }
                        } 
                    }
                    
                }
            }
        }
       

        $d[$rowid] = $cols;
    }
    return $d;
}

function buildJoinedDataOfResults() {
    return;
}

function keySplitter($idKey, $rKey, $i, $value, $arrayCols, $origrKey, $newCol = [])
{
    $rKeyParts = explode(':', $rKey, 2);
    $idKeyParts = explode(':', $idKey, 2);
        foreach ($arrayCols[$origrKey] as $origVal) {
            foreach ($origVal as $key => $v) {
    if (!strpos($rKeyParts[1],':')) {
        $newCol[$rKeyParts[0]][$key][$rKeyParts[1]] = $value;
    }
    else {
        $newCol[$rKeyParts[0]][$key]['hasMany'] = keySplitter($idKeyParts[1], $rKeyParts[1], $idKeyParts[0], $key, $origVal, $arrayCols, $origrKey);
    }}
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