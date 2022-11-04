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
            foreach ($structure['belongsTo'] as $parentTable => $paramList) {
                foreach ($paramList as $params) {
                    if ($field == $params['fk']) {
                        $keys['fks'][] = $key;
                    }
                }
            }
        }

    }
    return $keys;
}

function getValues($keys, $dataRows)
{
    global $request;
    $colsData = [];
    foreach ($keys['ids'] as $idKey) {
        $idColon = strrpos($idKey, ':');
        $idTable = substr($idKey, 0, $idColon);
        $colsData['ids'][$idTable] = array_unique(array_column($dataRows, $idKey));
        foreach ($keys['all'] as $rKey) {
            $colColon = strrpos($rKey, ':');
            $colTable = substr($rKey, 0, $colColon);
            $colField = $colColon ? substr($rKey, $colColon + 1) : $rKey;
            foreach ($keys['fks'] as $fKey) {
                $fkColon = strrpos($fKey, ':');
                $fkTable = substr($fKey, 0, $fkColon);
                $fkField = $fkColon ? substr($fKey, $fkColon + 1) : $fKey;
                $parentKeyField = null;
                if (empty($fkTable)) {
                    $fkTable = $request[1];
                }

                $structure = getDataStructure($fkTable);
                foreach ($structure['belongsTo'] as $parentTable => $paramList) {
                    foreach ($paramList as $params) {
                        $parentKeyField == $parentTable . ':' . $params['parentKey'];
                        if ($fkTable == $request[1]) {
                            $colsData['fks'][null]['belongsTo'][$parentTable] = array_column($dataRows, $fKey, $parentKeyField);
                        } else {
                            if ($parentTable == $request[1]) {
                                $parentTable = null;
                            }
                            if ($parentTable == $colTable) {
                                $colsData['fks'][$parentTable]['hasMany'][$fkTable] = array_column($dataRows, $fKey, $parentKeyField);
                            }
                        }
                    }

                }

                if ($colTable == $idTable) {
                    $colsData['all'][$colTable][$colField] = array_column($dataRows, $rKey, $idKey);
                }
            }
        }
    }
    return $colsData;
}

function splitKey($currentKey, $cKeyPart2, $allColValues, $currentIdList, $joinedCol = [], $splitted = [])
{

    $cKeyParts = explode(':', $cKeyPart2, 2);
    foreach ($allColValues as $cKey => $cList) {
        if ($cKey == $currentKey) {
            foreach ($cList as $id => $cVal) {
                if (!strpos($cKeyParts[1], ':')) {
                    $joinedCol[$cKeyParts[0]][$id][$cKeyParts[1]] = $cVal;
                } else {
                    $splitted += splitKey($currentKey, $cKeyParts[1], $allColValues, $currentIdList);
                    foreach ($splitted as $sKey => $sVals) {
                        foreach ($sVals as $i => $v) {
                            $joinedCol[$cKeyParts[0]][$id]['hasMany'][$sKey][$i] = $v;
                        }
                    }
                }
            }

        }
    }

    return $joinedCol;
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
    if (isset($dataStructure['belongsTo'][$lookup])) {
        return true;
    }
}

function getTablesThisBelongsTo($table = null)
{
    $dataStructure = getDataStructure($table);
    if (isset($dataStructure['belongsTo'])) {
        foreach ($dataStructure['belongsTo'] as $parentTable => $fks) {
            foreach ($fks as $i => $fkWithParams) {
                $belongsTo[$i]['table'] = $parentTable;
                $belongsTo[$i]['fkWithParams'] = $fkWithParams;
            }
        }
        return $belongsTo;
    }

}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return substr($haystack, 0, $length) == $needle;
}

function buildQueryResults($data)
{
    global $request;
    $d = [];
    $keys = getKeys($data[1][0]);
    $related = [];
    $hasMany = [];
    foreach ($data as $rowid => $dataRows) {
        $newItem = [];
        foreach ($dataRows as $row) {
            $newRow = array_filter(
                $row, function ($key) {
                    return !strpos($key, ':');
                },
                ARRAY_FILTER_USE_KEY
            );
            $d[$rowid] = $newRow;
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
            }
        }

        $d[$rowid]['hasMany'] = $hasMany[$rowid]['hasMany'];
    }

    return $d;
}

function buildJoinedDataOfResults(
    $dataRows,
    $parentTable,
    $currentTable,
    $fKeyFromArray,
    $idKeyFromArray,
    $keys
) {
    foreach ($dataRows as $row) {
        foreach ($row as $initialKey => $value) {
            if (!startsWith($initialKey, $tbl . ':')) {
                unset($row[$initialKey]);
            } else {
                $normalKey = str_replace($tbl . ':', '', $initialKey);
                $row['data'][$normalKey] = $value;
                if (in_array($initialKey, $keys['ids'])) {
                    $row['pk']['name'] = $normalKey;
                    $row['pk']['value'] = $value;
                }
                if (in_array($initialKey, $keys['fks'])) {
                    $fkValue = $value;
                    $belongsTo = getTablesThisBelongsTo($tbl);
                    foreach ($belongsTo as $params) {
                        if ($params['fkWithParams']['fk'] == $normalKey) {
                            $fk['fk'] = $normalKey;
                            $fk['value'] = $value;
                            $row['belongsTo'][$request[1]][] = $fk;

                        }
                    }
                }
                //$d[$fkValue]['hasmany'][$tbl][$pkValue] = $relatedInitial;

            }
        }
        $rowfiltered = array_filter($row, function ($key) {
            return !strpos($key, ':');
        }, ARRAY_FILTER_USE_KEY);
        $dataRows[$row[$fKey]]['hasMany'][$tbl][$row['pk']['value']] = $rowfiltered;
    }
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
