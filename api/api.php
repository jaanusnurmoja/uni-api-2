<?php

include_once './src/Service/DbRead.php';
include_once './src/Service/QueryMaker.php';
include_once './src/Service/Result.php';

use \Api\Service\DbRead;
use \Api\Service\QueryMaker;
use Api\Service\Result;

ini_set('always_populate_raw_post_data', -1);
ini_set('display_errors', 0);

//error_reporting(E_ALL);

//require_once 'config.php';
$thisDir = dirname($_SERVER['SCRIPT_NAME']);
if (isset($_SERVER['PATH_INFO'])) $path = $_SERVER['PATH_INFO'];

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
if (isset($_SERVER['PATH_INFO'])) $request = explode('/', $_SERVER['PATH_INFO']);

    //$qMaker = new QueryMaker($request[1]);
    //$dbRead = new DbRead();
    //$testRes = $dbRead->anySelect($testSql);
    $result = new Result($request[1]);
    if (isset($request[2])) {
        $result->byId($request[2]);
    }

    $testSql = $result->__toString();

//temp debug
if (isset($_GET['testapi'])) {
    echo json_encode([
        'sql' => $testSql, 'res' => $result->getDataSetsFromQuery()
    ]);
    exit;
}
//end temp debug



$input = json_decode(file_get_contents('php://input'), true);

// connect to the mysql database
//$link = mysqli_connect($host, $user, $pass, $dbname);
$cnf = parse_ini_file('../config/connection.ini');
$link = new mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);

mysqli_set_charset($link, 'utf8mb4');

/**
 * Set response status code and print an JS Object with error's info
 *
 * @param int $status_code  Status code
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

/**
 * Andmeseoste seadistus json failist
 * @return mixed
 */
function getRelations()
{
    return json_decode(file_get_contents('relations.json'), true);
}
/**
 * Ristviidete mall
 * @param mixed $relation
 * @param mixed $relations
 * @param mixed $table
 * @return mixed
 *
 */
function hasManyAndBelongsTo($relation, $relations, $table)
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
            }
        }
    }
    return $xrefTables;

}

/**
 * Andmestruktuuri moodustaja seadistusfaili põhjal
 * @param mixed $table
 * @param mixed $pkValue
 * @param mixed $origTable
 * @return array
 *
 */
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

/**
 * Tagastab getDataWithRelations andmed ilma peatabeli nimega indeksita
 * @param mixed $table
 * @return mixed
 *
 */
function getDataStructure($table = null)
{
    global $request;

    if ($table == null) {
        $table = $request[1];
    }

    return getDataWithRelations($table)[$table];
}

/**
 * Päringus kasutatavad väljad
 * @param mixed $table
 * @param mixed $parent
 * @param mixed $tableAlias
 * @return stdClass
 *
 * Tagastab päringus kasutatavate väljade ja ka primaarvõtmete nimed kolmel erineval moel:
 * table.field
 * table.field AS alias
 * alias
 *
 * pk
 * table.pk
 * pkAlias
 *
 * alias võib olla kas field või table:field
 */
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

/**
 * Rekursiivne funktsioon ühendatud tabelite väljanimede kogumiseks
 * @param mixed $table
 * @param mixed $tableData
 * @param mixed $parent
 * @param mixed $tableAlias
 * @param mixed $cols
 * @return string
 *
 */
function getJoinColumns($table, $tableData, $parent, $tableAlias = null, $cols = '')
{
    $cols .= implode(', ', getColumns($table, $parent, $tableAlias)->withAlias);

    if (isset($tableData['hasMany'])) {
        foreach ($tableData['hasMany'] as $t => $d) {
            $newParent = $t;
            $cols .= ', ';
            $cols .= getJoinColumns($t, $d, $newParent);
        }
    }
    return $cols;
}

function newBuildQuery() {
    global $request;
    $qMaker = new QueryMaker($request[1]);
    return $qMaker;
}
/**
 * Andmebaasipäringu keskne moodustaja.
 * @param mixed $rowid
 * @return string
 *
 * Siin kutsutakse esile ka ühendatud andmete päringuosa, v.a. many-to-one
 */
function buildQuery($rowid = null)
{
    foreach (getDataWithRelations() as $table => $tableData) {

        //global $request;
        $columns = "$table.{$tableData['pk']} AS `rowid`, ";
        $columns .= implode(', ', getColumns($table)->withAlias);
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
 * Joinide moodustaja
 * @param mixed $joinTable
 * @param mixed $joinTableData
 * @param mixed $table
 * @param mixed $tableData
 * @param mixed $sql
 * @return mixed
 *
 * Päringute join osa (v.a. many-to-one) moodustub siin, sh many-to-many päringud
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

/**
 * Moodustab üksikpäringuid many-to-one väljade jaoks.
 * @param mixed $table
 * @param mixed $where
 * @param mixed $value
 * @return mixed
 *
 *
 * Arvestatud on võimalusega, et sama funktsiooni saaks kasutada ka
 * nt html vormis rippmenüü täitmiseks, seetõttu tagastatakse kas üksik rida või loetelu
 */
function getValueOrListFromSubQuery($table, $where = null, $value = null)
{
    global $link;
    $sql = "SELECT * FROM $table";
    if (!empty($where) && !empty($value)) {
        $sql .= "
        WHERE $where = $value";
    }
    $result = mysqli_query($link, $sql);
    $count = mysqli_num_rows($result);
    $row = [];
    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $count == 1 ? $data[0] : $data;
}
/**
 * Abifunktsioon primaar- või võõrvõtmete saamiseks
 * @param mixed $data
 * @return array<array>
 *
 * @todo Siin on primaarvõtme nimi "hardcoded". Tavaliselt on selle nimi id, kuid alati ei pruugi olla
 */
function getKeys($data)
{
    global $request;
    $keys = [];
    $keys['all'] = array_keys($data);
    foreach ($keys['all'] as $key) {
        if ($key == 'id' || substr($key, -3) == ':id') {
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

/**
 * Abifunktsioon: leia primaarvõti andmete hulgast või lihtsalt küsi, mis on selle tabeli primaarvõti
 * @param mixed $table
 * @param mixed $data
 * @return mixed
 *
 */
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

/**
 * Abifunktsioon: kas $lookup tabel on $table alam
 * @param mixed $lookup
 * @param mixed $table
 * @return bool
 *
 * Kui $table on null, siis mõeldakse ülemtabelina peamist tabelit
 */
function isInHasManyOf($lookup, $table = null)
{

    $dataStructure = getDataStructure($table);
    if (isset($dataStructure['hasMany'][$lookup])) {
        return true;
    }
}

/**
 * Abifunktsioon: kas see tabel või selle aliasega tabel on ristviidete loetelus (vt seadistusfaili)
 * @param mixed $lookupAlias
 * @param mixed $table
 * @param mixed $realName
 * @return mixed
 *
 */
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

/**
 * Abifunktsioon: leia tabelid, mille alam see tabel (või fk väli) on
 * @param mixed $table
 * @param mixed $field
 * @param mixed $check
 * @return array
 *
 */
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

/**
 * Abifunktsioon: tabeli alamtabelite loetelu.
 * @param mixed $table
 * @return mixed
 *
 */
function hasMany($table = null)
{
    $structure = getDataStructure($table);
    if (isset($structure['hasMany']) && !empty($structure['hasMany'])) {
        return $structure['hasMany'];
    }
}

/**
 * String algab sellega
 * @param mixed $haystack
 * @param mixed $needle
 * @return bool
 *
 * Abifunktsioon: string algab sellega. Vajalik juhul, kui tuleb eristada teatud tabeli väljad ülejäänutest.
 */
function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return substr($haystack, 0, $length) == $needle;
}

/**
 * Abifunktsioon: mall andmete ümberpaigutamiseks
 * @param mixed $table
 * @param mixed $item
 * @param mixed $forBelongsTo
 * @return array
 *
 */
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
                    $newItem['belongsTo'][$key] = $belongsTo[$key];
                } else {
                    $newItem['data'][$key] = $value;
                }
            }
        }
    }
    return $newItem;
}

/**
 * Andmebaasist päritud andmete kuvaja
 * @param mixed $data
 * @param mixed $starttime
 * @param mixed $mySQLtime
 * @return array
 *
 * Esialgne andmevoog on $data. Teised parameetrid - andmete laadimise algus
 * ning mysql päringu tagastamise kiirus sekundites.
 */
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

                    $fkRow = getValueOrListFromSubQuery($tbl, $fkData['parentKey'], $fkData['value']);

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
                    $tblAlias
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
                    $keys,
                    $request[1]
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
/**
 * Kahepoolsete many-to-many seoste andmete näitamise funktsioon
 * @param mixed $dataRows
 * @param mixed $tbl
 * @param mixed $tblAlias
 * @param mixed $d
 * @return mixed
 *
 */
function buildResultsOfHMABT(
    $dataRows,
    $tbl,
    $tblAlias,
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

/**
 * Korduvate alamandmete näitamise funktsioon.
 * @param mixed $dataRows
 * @param mixed $currentTable
 * @param mixed $fKeyFromArray
 * @param mixed $idKeyFromArray
 * @param mixed $keys
 * @param mixed $parentTable
 * @param mixed $d
 * @return mixed
 *
 */
function buildJoinedDataOfResults(
    $dataRows,
    $currentTable,
    $fKeyFromArray,
    $idKeyFromArray,
    $keys,
    $parentTable,
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

        if (isset($rowFiltered['belongsTo'])) {
            foreach ($rowFiltered['belongsTo'] as $fk => $fkData) {
                $tbl = $fkData['table'];
                if ($tbl != $parentTable) {
                    $fkRow = getValueOrListFromSubQuery($tbl, $fkData['parentKey'], $fkData['value']);

                    $rowFiltered['belongsTo'][$fk]['data'] = $fkRow;

                }
            }
        }

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
                    $keys,
                    $currentTable
                );
                $rowFiltered['hasMany'][$tbl] = $newHasMany[$row[$idKeyFromArray]]['hasMany'][$tbl];
            }
        }

        $d[$row[$fKeyFromArray]]['hasMany'][$currentTable][$rowFiltered['pk']['value']] = $rowFiltered;

    }
    return $d;
}

/**
 * Väljanimede pooleks jagaja
 * @param mixed $key
 * @return array<string>
 *
 */
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

/**
 * @OA\Get(
 *   tags={"Tag"},
 *   path="Path",
 *   summary="Summary",
 *   @OA\Parameter(ref="#/components/parameters/id"),
 *   @OA\Response(response=200, description="OK"),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=404, description="Not Found")
 * )
 *
 * Pärineb originaalist
 */
//echo count($request);
switch (count($request)) {

    case 2:
    case 3:
        require_once __DIR__ . '/core/single_table.php';
        break;
    case 4:
    case 5:
        require_once __DIR__ . '/core/multi_table.php';
        break;
    default:
        echo (json_encode(array('error' => 'Welcome on Uni-API!')));
        break;
}
// close mysql connection
mysqli_close($link);
