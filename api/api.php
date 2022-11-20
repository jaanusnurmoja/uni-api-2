<?php
ini_set('always_populate_raw_post_data', -1);
ini_set('display_errors', 0);

error_reporting(0);

require_once 'config.php';

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', $_SERVER['PATH_INFO']);

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

function hasManyAndBelongsTo($relation, $relations, $table, $thisTableData)
{
    $xrefTables = $relation['xref'];
    foreach ($relation['tables'] as $i => $tables) {
        $count = count($tables);
        if (isset($tables[$table])) {
            if ($count == 2) {
                foreach ($tables as $xRefTable => $params) {
                    if ($xRefTable == $table) {
                        $tableValues['thisTable'] = $table;
                    }
                    if ($xRefTable != $table) {
                        $tableValues['otherTable'] = $xRefTable;

                    }
                }
                $xrefTables['refTables'][$i] = $tableValues;
                $xrefTables['refTables'][$i]['values'] = $tables;
                $xrefTables['refTables'][$i]['alias'] = 'related_' . $tableValues['otherTable'];
                $xrefTables['refTables'][$i]['asAlias'] = "`{$tableValues['otherTable']}` AS `related_{$tableValues['otherTable']}`";
                $xrefTables['refTables'][$i]['thisPk'] = getPk($tableValues['thisTable']);
                $xrefTables['refTables'][$i]['otherPk'] = getPk($tableValues['otherTable']);
                //$thisTableData['hasManyAndBelongsTo']['xref'] = $xrefTables;

            }
            if ($count == 1) {
                foreach ($tables as $xRefTable => $params) {
                    $xrefTables = $relations['hasManyAndBelongsTo']['xref'];
                    $params['inner'] = true;
                    $params['thisTable'] = $xRefTable;
                    $params['asAlias'] = "`$xRefTable` AS `{$params['alias']}`";
                    $params['otherTable'] = $xRefTable;
                    $xrefTables['refTables'][$i] = $params;
                }
                // $thisTableData['hasManyAndBelongsTo']['xref'] = $xrefTables['xref'];
            }
        }
    }
    return $xrefTables;

}
function getDataWithRelations($table = null, $pkValue = null, $origTable = null)
{
    $d = [];
    $thisTableData = [];
    global $request;
    $relations = getRelations();
    if (empty($table)) {
        $table = $request[1];
    }
    $thisTableData = $origTable ? $relations[$origTable] : $relations[$table];
    $r = [];

    foreach ($relations as $rtbl => $relation) {
        if ($rtbl == 'hasManyAndBelongsTo' && empty($origTable)) {
            $xref = hasManyAndBelongsTo($relation, $relations, $table, $thisTableData);
            if (!empty($xref)) {
                $thisTableData['hasManyAndBelongsTo']['xref'] = $xref;

                foreach ($xref['refTables'] as $ref) {
                    $referred = $ref['otherTable'];
                    $refAlias = $ref['alias'];

                    $refContent = getDataWithRelations($refAlias, null, $referred);
                    unset($refContent['hasManyAndBelongsTo']);
                    $thisTableData['hasManyAndBelongsTo']['tables'][$referred] = $refContent[$referred];
                }
            }
        }
        if (isset($relation['belongsTo'])) {
            foreach ($relation['belongsTo'] as $fkField => $params) {
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

function getColumns($table, $parent = null, $tableAlias = null)
{
    global $link;
    $cols = new stdClass();
    $cols->list = [];
    $cols->withAlias = [];
    $cols->pk = [];
    if (!empty($tableAlias)) {
        $tableOrAlias = $tableAlias;
        $alias = "$tableAlias:";
    } else {
        $tableOrAlias = $table;
        $alias = $parent ? "$parent:" : '';
    }
    $sql = "SHOW COLUMNS FROM `$table`";
    if ($result = $link->query($sql)) {
        while ($column = $result->fetch_assoc()) {
            $cols->list[] = "`$tableOrAlias`.`{$column['Field']}`";
            $cols->withAlias[] = "`$tableOrAlias`.`{$column['Field']}` AS `$alias{$column['Field']}`";
            $cols->aliasOnly[] = "$alias{$column['Field']}";
            if ($column['Key'] == 'PRI') {
                $cols->pk['name'] = $column['Field'];
                $cols->pk['fullName'] = "`$tableOrAlias`.`{$column['Field']}`";
                $cols->pk['alias'] = "$alias{$column['Field']}";
            }
        }
    }
    return $cols;
}

function getJoinColumns($table, $tableData, $parent, $tableAlias = null, $cols = '')
{
    $cols .= implode(', ', getColumns($table, $parent, $tableAlias)->withAlias);
    // esialgu ei anna mingit nähtavat tulemust, kuigi lisab päringusse vajalikke välju
    /*
    if (isset($tableData['belongsTo'])) {
    foreach ($tableData['belongsTo'] as $fk => $d) {
    $newParent = $d['table'];
    $cols .= ', ';
    $cols .= getJoinColumns($d['table'], $d['data'], $newParent);
    }
    }
     */
    // muudatuse lõpp

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

        global $request;
        $columns = "$table.{$tableData['pk']} AS `rowid`, ";
        $columns .= implode(', ', getColumns($table)->withAlias);
/*
if (isset($tableData['belongsTo']) && $table == $request[1]) {
foreach ($tableData['belongsTo'] as $fk => $d) {
$newParent = $d['table'];
$columns .= ', ' . getJoinColumns($d['table'], $d['data'], $newParent);
}
}
 */// $joinTable, $joinTableData, $table, $tableData, $xref = null, $sql = null
        if (isset($tableData['hasManyAndBelongsTo'])) {
            $xref = $tableData['hasManyAndBelongsTo']['xref'];
            foreach ($xref['refTables'] as $ref) {
                $columns .= ', ' . getJoinColumns($ref['otherTable'], $xref, null, $ref['alias']);
            }
        }

        if (isset($tableData['hasMany'])) {
            foreach ($tableData['hasMany'] as $jt => $jtData) {
                $columns .= ', ' . getJoinColumns($jt, $jtData, $jt);
            }
        }

        $sql = "SELECT $columns FROM `$table`
        ";
/*
if (isset($tableData['belongsTo']) && $table == $request[1]) {
foreach ($tableData['belongsTo'] as $fk => $d) {
$sql .= buildQueryJoins($d['table'], $d, $table, $tableData, null, $fk);
}
}
 */
        if (isset($tableData['hasManyAndBelongsTo'])) {
            $xref = $tableData['hasManyAndBelongsTo']['xref'];
            foreach ($tableData['hasManyAndBelongsTo']['tables'] as $refTable => $refTableData) {
                $sql .= buildQueryJoins($refTable, $refTableData, $table, $tableData, $xref);
            }
        }

        if (isset($tableData['hasMany'])) {
            foreach ($tableData['hasMany'] as $joinTable => $joinTableData) {
                $sql .= buildQueryJoins($joinTable, $joinTableData, $table, $tableData);
            }
        }
        if (!empty($rowid)) {
            $sql .= "WHERE `$table`.`{$tableData['pk']}` = $rowid";
        }

        return $sql;

    }
}

/**
 * Summary of buildQueryJoins
 * @param mixed $joinTable
 * @param mixed $joinTableData
 * @param mixed $table
 * @param mixed $tableData
 * @param mixed $sql
 * @return mixed
 *
 * meelespidamiseks ja spikriks tulevase many_to_many tarvis
 * 1) ristviited ainsa tabeli raames
 * SELECT * FROM `products`
 * LEFT JOIN crossref ON JSON_CONTAINS(JSON_EXTRACT(table_value, '$.products'), products.id)
 * LEFT JOIN products AS related_products
 * ON (JSON_CONTAINS(JSON_EXTRACT(table_value, '$.products'), related_products.
 * id) AND related_products.id <> products.id)
 *
 * 2) kahe tabeliga ristviited
 *
 * SELECT * FROM `events`
 * LEFT JOIN crossref ON JSON_CONTAINS_PATH(table_value, 'ALL','$.events','$.beers')
 * AND  JSON_EXTRACT(table_value, '$.events') = events.id
 * LEFT JOIN beers ON JSON_EXTRACT(table_value, '$.beers') = beers.id
 */
function buildQueryJoins($joinTable, $joinTableData, $table, $tableData, $xref = null, $fk = null, $sql = null)
{
    if (!empty($fk)) {
        $sql .= "LEFT JOIN `$joinTable`
        ON `$joinTable`.`{$joinTableData['parentKey']}` = `$table`.`$fk`
        ";
    }
    if (isset($joinTableData['belongsTo'])) {
        foreach ($joinTableData['belongsTo'] as $fkField => $params) {
            if ($params['table'] == $table) {
                $sql .= "LEFT JOIN `$joinTable` ON
                `$joinTable`.`$fkField` = `$table`.`{$tableData['pk']}`
        ";
            }
        }
/*
foreach ($joinTableData['belongsTo'] as $fkField => $params) {
if ($params['table'] != $table) {
$sql .= "LEFT JOIN `{$params['table']}` AS `{$params['table']}` ON
`{$params['table']}`.`{$params['parentKey']}` = `$joinTable`.`$fkField`
";

}

}
 */
    }
    if ($xref != null) {
        $sql .= "LEFT JOIN {$xref['table']} ON ";
        foreach ($xref['refTables'] as $refData) {
            if ($refData['inner']) {
                $sql .= "JSON_CONTAINS(JSON_EXTRACT({$xref['field']}, '$.{$refData['thisTable']}'), `$table`.`{$tableData['pk']}`)
             LEFT JOIN {$refData['asAlias']} ON
             (JSON_CONTAINS(JSON_EXTRACT(`{$xref['field']}`, '$.{$refData['otherTable']}'), `{$refData['alias']}`.`{$tableData['pk']}`)
             AND `{$refData['alias']}`.`{$tableData['pk']}` <> `{$refData['thisTable']}`.`{$tableData['pk']}`)";
            } else {
                $sql .= "JSON_CONTAINS_PATH(`{$xref['field']}`, 'ALL','$.{$refData['thisTable']}','$.{$refData['otherTable']}')
            AND JSON_EXTRACT(`{$xref['field']}`, '$.{$refData['thisTable']}') = `{$refData['thisTable']}`.`{$refData['thisPk']['name']}`
            LEFT JOIN {$refData['asAlias']} ON JSON_EXTRACT(`{$xref['field']}`, '$.{$refData['otherTable']}') = `{$refData['alias']}`.`{$refData['otherPk']['name']}`
            ";
            }
        }
    }

    if (isset($joinTableData['hasMany'])) {
        foreach ($joinTableData['hasMany'] as $nextTable => $nextTableData) {
            $sql .= buildQueryJoins($nextTable, $nextTableData, $joinTable, $joinTableData);
        }

    }
    return $sql;
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

function getPk($table, $data = null)
{
    if ($data) {
        $keys = getKeys($data);
        foreach ($keys['ids'] as $id) {
            if (keySplitter($id)['table'] == $table) {
                return $id;
            }
        }
    } else {
        $keys = getColumns($table);
        return $keys->pk;
    }
}

function isInHasManyOf($lookup, $table = null)
{

    $dataStructure = getDataStructure($table);
    if (isset($dataStructure['hasMany'][$lookup])) {
        return true;
    }
}

function isInHasManyAndBelongsTo($lookupAlias, $table = null, $realName = false)
{

    $dataStructure = getDataStructure($table);
    if (isset($dataStructure['hasManyAndBelongsTo'])) {
        foreach ($dataStructure['hasManyAndBelongsTo']['xref']['refTables'] as $ref) {
            if ($ref['alias'] == $lookupAlias) {
                if ($realName) {
                    return $ref['otherTable'];
                } else {
                    return true;
                }
            }
        }

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

function reorganize($table, $item, $forBelongsTo = false)
{
    $newItem = array();
    $structure = getDataStructure($table);
    foreach ($item as $key => $value) {
        if ($forBelongsTo === true) {
            $newItem[$key] = $value;
        } else {
            if ($key == $structure['pk']) {
                $newItem['pk']['name'] = $key;
                $newItem['pk']['value'] = $value;
            } elseif ($key == 'rowid') {
                $newItem[$key] = $value;
            } else {
                $belongsTo = getTablesThisBelongsTo($table, $key, 'check');
                if (!empty($belongsTo)) {
                    $belongsTo[$key]['value'] = $value;
                    //$belongsTo[$key]['data'] = reorganize($belongsTo[$key]['table'], $item, true);
                    $newItem['belongsTo'][$key] = $belongsTo[$key];
                } else {
                    $newItem['data'][$key] = $value;
                }
            }
        }
    }
    return $newItem;
}
function buildQueryResults($data, $starttime = null, $mySQLtime = null)
{
    global $request;
    $structure = getDataStructure($request[1]);
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
            if (isset($d[$rowid]['belongsTo'])) {

                foreach ($d[$rowid]['belongsTo'] as $fk => $fkData) {
                    $tbl = $fkData['table'];
                    $cols = getColumns($tbl);
                    foreach ($cols->aliasOnly as $i => $field) {
                        $fkRow[$field] = $row["$tbl:$field"];
                    }
                    $d[$rowid]['belongsTo'][$fk]['data'] = reorganize($tbl, $fkRow, true);
                }
            }
        }

        foreach ($keys['ids'] as $idKeyFromArray) {
            $idSubKeys = keySplitter($idKeyFromArray);
            $tblAlias = $idSubKeys['table'];

            if (isInHasManyAndBelongsTo($tblAlias, $request[1])) {
                $tbl = isInHasManyAndBelongsTo($tblAlias, $request[1], true);
                $idKey = getPk($tbl);
                $hasManyAndBelongsTo = buildResultsOfHMABT(
                    $dataRows,
                    $tbl,
                    $tblAlias,
                    $idKeyFromArray
                );
                if (!empty($hasManyAndBelongsTo)) {
                    $d[$rowid]['hasManyAndBelongsTo'][$tbl] = $hasManyAndBelongsTo;
                }
            }
        }

        foreach ($keys['fks'] as $fKeyFromArray) {
            $fkSubKeys = keySplitter($fKeyFromArray);
            $tbl = $fkSubKeys['table'];
            if (isInHasManyOf($tbl, $request[1])) {
                $idKey = getPk($tbl);
                $idKeyFromArray = $idKey['alias'];
                $hasMany = buildJoinedDataOfResults(
                    $dataRows,
                    $tbl,
                    $fKeyFromArray,
                    $idKeyFromArray,
                    $keys
                );
                if (!empty($hasMany[$rowid]['hasMany'][$tbl])) {
                    $d[$rowid]['hasMany'][$tbl] = $hasMany[$rowid]['hasMany'][$tbl];
                }

            }
        }

    }
    $end = microtime(true);
    $phpTime = $end - $starttime;
    $results = array();
    $results['loadTime']['MySQL'] = $mySQLtime;
    $results['loadTime']['php'] = $phpTime;
    $results['data'] = $d;
    return $results;
}
function buildResultsOfHMABT(
    $dataRows,
    $tbl,
    $tblAlias,
    $idKeyFromArray,
    $d = []

) {
    foreach ($dataRows as $row) {
        foreach ($row as $initialKey => $value) {
            if (!startsWith($initialKey, $tblAlias . ':') || empty($value)) {
                unset($row[$initialKey]);
            } else {
                $normalKey = str_replace($tblAlias . ':', '', $initialKey);
                $row[$normalKey] = $value;
            }
        }
        $rowFiltered = array_filter($row, function ($key) {
            return !strpos($key, ':');
        }, ARRAY_FILTER_USE_KEY);
        $rowFiltered = reorganize($tbl, $rowFiltered);
        if (!empty($rowFiltered)) {
            $d[$rowFiltered['pk']['value']] = $rowFiltered;
        }
    }

    return $d;
}

function buildJoinedDataOfResults(
    $dataRows,
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
/*
if (isset($rowFiltered['belongsTo'])) {
foreach ($rowFiltered['belongsTo'] as $fk => $fkData) {
$tbl = $fkData['table'];
$cols = getColumns($tbl);
foreach ($cols->aliasOnly as $i => $field) {
$fkRow[$field] = $row["$tbl:$field"];
}

//$d[$rowid]['belongsTo'][$fk]['data'] = reorganize($tbl, $fkRow, true);
$d[$rowid]['belongsTo'][$fk]['data'] = "on olemas";
}
}
 */

        foreach ($keys['fks'] as $newFKeyFromArray) {
            $fkSubKeys = keySplitter($newFKeyFromArray);
            $tbl = $fkSubKeys['table'];

            if (isInHasManyOf($tbl, $currentTable)) {
                $idKey = getPk($tbl);
                $newIdKeyFromArray = $idKey['alias'];
                $newHasMany = buildJoinedDataOfResults(
                    $dataRows,
                    $tbl,
                    $newFKeyFromArray,
                    $newIdKeyFromArray,
                    $keys
                );
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
        $result['table'] = $keyParts[0];
        $result['field'] = $keyParts[1];

    }
    return $result;

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