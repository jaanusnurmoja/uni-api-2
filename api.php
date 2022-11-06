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
    // vajadus on küsitav
    /*
    if (isset($request[2])) {
    $pkValue = $request[2];
    $relations[$table]['rowid'] = $pkValue;
    }
     */
    $thisTableData = $relations[$table];

    $r = [];

    foreach ($relations as $rtbl => $relation) {
        if (isset($relation['belongsTo'])) {
            foreach ($relation['belongsTo'] as $fkField => $params) {
                //$childTableData = $relations[$rtbl];
                if ($params['table'] == $table) {
                    $r = getDataWithRelations($rtbl, null, $pkValue);
                    $thisTableData['hasMany'][$rtbl] = $r[$rtbl];
                }
            }
        }
    }
    $d[$table] = $thisTableData;
    return $d;
}

function getDataStructure($table = null)
{
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
            $cols->aliasOnly[] = "$alias{$column['Field']}";
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
function buildQuery($rowid = null)
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
/*
if (isset($tableData['rowid'])) {
$sql .= "WHERE `$table`.`{$tableData['pk']}` = {$tableData['rowid']}";
}
 */
        if (!empty($rowid)) {
            $sql .= "WHERE `$table`.`{$tableData['pk']}` = $rowid";
        }
        return $sql;

    }
}

function buildQueryJoins($joinTable, $joinTableData, $table, $tableData, $sql = null)
{
    if (isset($joinTableData['belongsTo'])) {
        foreach ($joinTableData['belongsTo'] as $fkField => $params) {
            $sql .= "LEFT JOIN `$joinTable` ON
        `$joinTable`.`$fkField` = `$table`.`{$tableData['pk']}`
        ";
        }

    }
    if (isset($joinTableData['hasMany'])) {
        foreach ($joinTableData['hasMany'] as $nextTable => $nextTableData) {
            $sql .= buildQueryJoins($nextTable, $nextTableData, $joinTable, $joinTableData);
        }

    }
    return $sql;
}

function setKeysByDataStructure($keys, $table = null)
{
    global $request;
    if (!$table) {
        $table = $request[1];
    }

}
function getKeys($data)
{
    global $request;
    $keys = [];
    $keys['all'] = array_keys($data);
    foreach ($keys['all'] as $key) {
        if ($key == 'id' || substr($key, -3) == ':id') {
            $idKey = $key;
            $keys['ids'][] = $key;
        }
        $tf = explode(':', $key);
        $table = !empty($tf[1]) ? $tf[0] : $request[1];
        $field = !empty($tf[1]) ? $tf[1] : $tf[0];
        $structure = getDataStructure($table);
        if (isset($structure['belongsTo'])) {
            foreach ($structure['belongsTo'] as $fkField => $paramList) {
                if ($field == $fkField) {
                    $keys['fks'][] = $key;
                }
            }
        }

    }
    return $keys;
}

function getPk($table, $data)
{
    $keys = getKeys($data);
    foreach ($keys['ids'] as $id) {
        if (keySplitter($id)['table'] == $table) {
            return $id;
        }
    }
}

function isInHasManyOf($lookup, $table = null)
{

    $dataStructure = getDataStructure($table);
    if (isset($dataStructure['hasMany'][$lookup])) {
        return true;
    }
}

function doesTableBelongsTo($lookup, $table = null)
{
    $dataStructure = getDataStructure($table);
    if (isset($dataStructure['belongsTo'])) {
        foreach ($dataStructure['belongsTo'] as $fkField => $params) {
            if ($params['table'] == $lookup) {
                return true;
            }

        }

    }
}

function getTablesThisBelongsTo($table = null, $field = null, $check = null)
{
    $dataStructure = getDataStructure($table);
    if (isset($dataStructure['belongsTo'])) {
        foreach ($dataStructure['belongsTo'] as $fkField => $params) {
            if ($check == 'check') {
                if ($fkField == $field) {
                    $belongsTo[$field]['parentKey'] = $params['parentKey'];
                    $belongsTo[$field]['table'] = $params['table'];
                }
            } else {
                $belongsTo[] = $params['table'];
            }

        }
        return $belongsTo;
    }
}

function hasMany($table = null)
{
    $structure = getDataStructure($table);
    if (isset($structure['hasMany']) && !empty($structure['hasMany'])) {
        return $structure['hasMany'];
    }
}
function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return substr($haystack, 0, $length) == $needle;
}

function reorganize($table, $item)
{
    $newItem = array();
    $structure = getDataStructure($table);
    foreach ($item as $key => $value) {
        if ($key == $structure['pk']) {
            $newItem['pk']['name'] = $key;
            $newItem['pk']['value'] = $value;
        } elseif ($key == 'rowid') {
            $newItem[$key] = $value;
        } else {
            $belongsTo = getTablesThisBelongsTo($table, $key, 'check');
            if (!empty($belongsTo)) {
                $belongsTo[$key]['value'] = $value;
                $newItem['belongsTo'][$key] = $belongsTo[$key];
            } else {
                $newItem[$key] = $value;
            }
        }
    }
    return $newItem;
}
function buildQueryResults($data)
{
    global $request;
    $d = [];
    $keys = getKeys(min($data)[0]);
    $hasMany = [];
    foreach ($data as $rowid => $dataRows) {
        foreach ($dataRows as $row) {
            $newRow = array_filter(
                $row, function ($key) {
                    return !strpos($key, ':');
                },
                ARRAY_FILTER_USE_KEY
            );
            $d[$rowid] = reorganize($request[1], $newRow);
        }
        //print_r(array_merge_recursive(...$dataRows));
        foreach ($keys['fks'] as $fKeyFromArray) {
            $fkSubKeys = keySplitter($fKeyFromArray);
            $tbl = $fkSubKeys['table'];
            if (isInHasManyOf($tbl, $request[1])) {
                foreach ($keys['ids'] as $idKeyFromArray) {
                    $idSubKeys = keySplitter($idKeyFromArray);
                    if ($idSubKeys['table'] == $tbl) {
                        $idKey = $idSubKeys['field'];
                        $hasMany = buildJoinedDataOfResults(
                            $dataRows,
                            $request[1],
                            $tbl,
                            $fKeyFromArray,
                            $idKeyFromArray,
                            $keys
                        );
                    }
                }
                if (!empty($hasMany[$rowid]['hasMany'][$tbl])) {
                    $d[$rowid]['hasMany'][$tbl] = $hasMany[$rowid]['hasMany'][$tbl];
                }

            }
        }

    }

    return $d;
}

function buildJoinedDataOfResults(
    $dataRows,
    $parentTable,
    $currentTable,
    $fKeyFromArray,
    $idKeyFromArray,
    $keys,
    $d = []
) {
    foreach ($dataRows as $row) {
        foreach ($row as $initialKey => $value) {
            if (!startsWith($initialKey, $currentTable . ':') || empty($value)) {
                unset($row[$initialKey]);
            } else {
                $normalKey = str_replace($currentTable . ':', '', $initialKey);
                $row[$normalKey] = $value;
            }
        }

        $rowFiltered = array_filter($row, function ($key) {
            return !strpos($key, ':');
        }, ARRAY_FILTER_USE_KEY);
        $rowFiltered = reorganize($currentTable, $rowFiltered);
        //print_r($rowFiltered);
        foreach ($keys['fks'] as $newFKeyFromArray) {
            $fkSubKeys = keySplitter($newFKeyFromArray);
            $tbl = $fkSubKeys['table'];
            if (isInHasManyOf($tbl, $currentTable)) {
                foreach ($keys['ids'] as $newIdKeyFromArray) {
                    $idSubKeys = keySplitter($newIdKeyFromArray);
                    if ($idSubKeys['table'] == $tbl) {
                        $idKey = $idSubKeys['field'];
                        $newHasMany = buildJoinedDataOfResults(
                            $dataRows,
                            $currentTable,
                            $tbl,
                            $newFKeyFromArray,
                            $newIdKeyFromArray,
                            $keys
                        );
                    }
                }
                $rowFiltered['hasMany'][$tbl] = $newHasMany[$row[$idKeyFromArray]]['hasMany'][$tbl];
            }
        }

        $d[$row[$fKeyFromArray]]['hasMany'][$currentTable][$rowFiltered['pk']['value']] = $rowFiltered;

    }
    return $d;
}

function keySplitter($key)
{
    $result = [];
    if (strpos($key, ':')) {
        $keyParts = explode(':', $key);
        // $result[$keyParts[0]][$id][$keyParts[1]] = $value;
        $result['table'] = $keyParts[0];
        $result['field'] = $keyParts[1];

    }
    return $result;

// kuidas näidata seotud alamridu?
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