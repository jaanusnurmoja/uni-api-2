# uni-api

Nagu öeldud, toetub valmiv projekt samanimelisele lihtsa **REST API** php kriptile, mis kasutab MySQL andmebaasi.

Oodatava lõpptulemusena on võimalik kasutada kõiki http meetodeid  **GET**, **POST**, **PATCH**, **PUT** and **DELETE**, kuid hetkel keskendun ainult GETile.

# Paigaldamine

- Tõsta kõik failid saidi juurkausta

- seadista `config.php` suhtlema andmebaasiga

- Jäta `models.json` nagu ta on (see on originaal) - meil on vaja hoopis relations.json faili

- mine oma andmebaasi ja täida see uni-api.sql sisuga (tabelid koos andmetega)

# API

põhifail api.php asub kaustas api, mis impordib kaustast core ka crud toimingute skriptid.

Meid huvitab neist hetkel ainult üks - veidi uues kuues SELECT.

Päringute skeem on järgmine: <https://sinusait/juurkaust/api/tabelinimi> näitab peatabeli ja alamate loetelu.

Number urli lõpus (tabelinimi/1) näitab konkreetse id-ga kirjet.

# Olulisemad meetodid

getRelations() tõlgib seadistuse jsoni php keelde`, 

getDataWithRelations($table = null, $pkValue = null, $origTable = null) 
töötleb seadistust edasi, lisades ka hasMany, 

getColumns + getJoinColumns koostavad väljade loetelu,
```
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


```

reorganize paigutab ja lahterdab kirje andmed soovitud viisil 
```
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

buildQueryResults($data, $starttime = null, $mySQLtime = null) - nagu nimigi ütleb, töötleb see andmebaasist saadud ridu. Alammeetodid - buildResultsOfHMABT($dataRows, $tbl, $tblAlias) ja buildJoinedDataOfResults(
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
