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

getColumns($table, $parent = null, $tableAlias = null) + getJoinColumns() koostavad väljade loetelu,

buildQuery() ja buildQueryJoin() koostavad SELECT päringu

reorganize($table, $item, $forBelongsTo = false) paigutab kirje andmed soovitud viisil (alajaotustesse pk, belongsTo, hasManyAndBelongsTo ja data). 

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
