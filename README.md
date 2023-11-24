# uni-api

Nagu öeldud, toetub valmiv projekt samanimelisele lihtsa **REST API** php kriptile, mis kasutab MySQL andmebaasi.

Oodatava lõpptulemusena on võimalik kasutada kõiki http meetodeid  **GET**, **POST**, **PATCH**, **PUT** and **DELETE**, kuid hetkel keskendun ainult GETile.

# Paigaldamine

- Tõsta kõik failid saidi juurkausta

- seadista `config/connection.ini` suhtlema andmebaasiga

- Jäta `models.json` nagu ta on (see on originaal) - meil on vaja hoopis relations.json faili

- mine oma andmebaasi ja täida see <https://test.nurmoja.net.ee/uni-api/uni-api.sql.txt> sisuga (tabelid koos andmetega)

# API

põhifail api.php asub kaustas api, mis impordib kaustast core ka crud toimingute skriptid.

Meid huvitab neist hetkel ainult üks - veidi uues kuues SELECT.

Päringute skeem on järgmine: <https://sinusait/juurkaust/api/tabelinimi> näitab peatabeli ja alamate loetelu.

Number urli lõpus (tabelinimi/1) näitab konkreetse id-ga kirjet.

# Olulisemad meetodid

getRelations() tõlgib seadistuse jsoni php keelde`,

getDataWithRelations
töötleb seadistust edasi, lisades ka hasMany,

```
/**
 * Summary of getDataWithRelations
 * @param mixed $table
 * @param mixed $pkValue
 * @param mixed $origTable
 * @return array
 *
 * Andmestruktuuri moodustaja seadistusfaili põhjal
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
 * Summary of getDataStructure
 * @param mixed $table
 * @return mixed
 *
 * Hiljem loodud abifunktsioon, mis tagastab getDataWithRelations andmed ilma peatabeli nimega indeksita
 */
function getDataStructure($table = null)
{
    global $request;

    if ($table == null) {
        $table = $request[1];
    }

    return getDataWithRelations($table)[$table];
}
```

getColumns + getJoinColumns koostavad väljade loetelu,

```
/**
 * Summary of getColumns
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
 * Summary of getJoinColumns
 * @param mixed $table
 * @param mixed $tableData
 * @param mixed $parent
 * @param mixed $tableAlias
 * @param mixed $cols
 * @return string
 *
 * Rekursiivne funktsioon ühendatud tabelite väljanimede kogumiseks
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
```

buildQuery() ja buildQueryJoin() koostavad SELECT päringu

```
/**
 * Summary of buildQuery
 * @param mixed $rowid
 * @return string
 *
 * Andmebaasipäringu keskne moodustaja. Siin kutsutakse esile ka ühendatud andmete päringuosa, v.a. many-to-one
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
 * Summary of buildQueryJoins
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
                $sql .= "JSON_CONTAINS(JSON_EXTRACT({$xref['field']}, '$.{$refData['thisTable']}'), 
    `$table`.`{$tableData['pk']}`)
             LEFT JOIN {$refData['asAlias']} ON
             (JSON_CONTAINS(JSON_EXTRACT(`{$xref['field']}`, '$.{$refData['otherTable']}'), 
    `{$refData['alias']}`.`{$tableData['pk']}`)
             AND `{$refData['alias']}`.`{$tableData['pk']}` <> `{$refData['thisTable']}`.`{$tableData['pk']}`)";
            } else {
                $sql .= "JSON_CONTAINS_PATH(`{$xref['field']}`, 'ALL','$.{$refData['thisTable']}',
    '$.{$refData['otherTable']}')
            AND JSON_EXTRACT(`{$xref['field']}`, '$.{$refData['thisTable']}') = 
   `{$refData['thisTable']}`.`{$refData['thisPk']['name']}`
            LEFT JOIN {$refData['asAlias']} ON 
   JSON_EXTRACT(`{$xref['field']}`, 
   '$.{$refData['otherTable']}') = `{$refData['alias']}`.`{$refData['otherPk']['name']}`
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
 * Summary of getValueOrListFromSubQuery
 * @param mixed $table
 * @param mixed $where
 * @param mixed $value
 * @return mixed
 *
 * Moodustab üksikpäringuid many-to-one väljade jaoks.
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

```

reorganize paigutab ja lahterdab kirje andmed soovitud viisil

```
/**
 * Summary of reorganize
 * @param mixed $table
 * @param mixed $item
 * @param mixed $forBelongsTo
 * @return array
 *
 * Abifunktsioon: mall andmete ümberpaigutamiseks
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

```

buildQueryResults($data, $starttime = null, $mySQLtime = null) - nagu nimigi ütleb, töötleb see andmebaasist saadud ridu.

```
/**
 * Summary of buildQueryResults
 * @param mixed $data
 * @param mixed $starttime
 * @param mixed $mySQLtime
 * @return array
 * Andmebaasist päritud andmete kuvamiseks pöördutakse selle funktsiooni poole.
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

```

Alammeetodid - buildResultsOfHMABT($dataRows, $tbl, $tblAlias) ja buildJoinedDataOfResults(
 $dataRows, $currentTable, $fKeyFromArray, $idKeyFromArray, $keys, $parentTable,$d = []), lisaks on tarvitusel mitmed abifunktsioonid.

Päringuga tagastatud read saadetakse sellele meetodile nii, et ühtaegu mõõdetakse nii mysql kui ka php laadimise aega:

```
$starttime = microtime(true);
$result = mysqli_query($link, $sql);

$dataRows = [];
while ($row = mysqli_fetch_assoc($result)) {
 $dataRows[$row['rowid']][] = $row;
}

$end = microtime(true);
$mySQLtime = $end - $starttime;

echo json_encode(buildQueryResults($dataRows, $starttime, $mySQLtime));
```
