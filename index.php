<!DOCTYPE html>
<html lang="et">

<head>
    <title>
        Jaanus Nurmoja uni-api projekt
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
        integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <h2>Jaanus Nurmoja: uni-api</h2>
            <div class="col-sm">
                <?php
echo 'Uuri asja lähemalt siit: <a href="README.md">UNI-API RAKENDUS</a>';
$rel = file_get_contents('api/relations.json'); ?>
                <h3>Näidisandmed</h3>
                <table class="table table-striped">
                    <?php
foreach (json_decode($rel) as $table => $params) {
    if ($table != 'hasManyAndBelongsTo') {

        echo "<tr><td class='warning'><a href='api/$table'>$table</a></td><td>$params->description</td></tr>";
    }
}
?>
                </table>
            </div>
            <div class="col-md">
                <p>Eesmärk on luua universaalne php & mysql crud api, mis edastab andmeid kuitahes
                    keerukate ja mitmekihiliste seostega, võimalikult väheste päringute hulgaga ning võimalikult
                    lihtsama ja napima seadistusega.
                    Aluseks on võetud üks "lihtsa crud api" <a
                        href="https://github.com/darioTecchia/uni-api">projekt</a> ,
                    mida
                    paraku pole mitu aastat edasi arendatud. Osa funktsionaalsusi võtan kindlasti sealt üle.
                </p>
                <div>
                    <h3>Tähtsamad erisused:</h3>
                    <ol>
                        <li>
                            json seadistusfailis <a href='api/relations.json'>relations.json</a> on defineeritud vaid
                            tabelite nimed ning iga
                            tabeli many-to-one või many-to-many seosed. Väljade nimedest on ära toodud vaid primaar- ja
                            võõrvõtmed. many-to-one põhjal genereeritakse omakorda one-to-many seosed. </li>
                        <li>
                            päringu väljade loetelud genereeritakse üldise seadistuse põhjal dünaamiliselt, kasutades
                            päringut
                            SHOW
                            COLUMNS FROM tabeli_nimi. Peamise kirje alamtabelite väljanimed on aliasega `tabel:väli`
                        </li>
                        <li>
                            Üks universaalne vahendustabel kõigi many-to-many seoste tarvis (orignaalis nt beers_events
                            ja
                            events_beers)
                        </li>
                    </ol>
                    <h4>Peamine probleem hetkel: php jõudlus andmete
                        ümberkorraldamisel ja edastamisel.</h3>
                        <h6>Selle lahendamine eeldab ilmselt lühiajalist kursust, päädigu see kasvõi järeldusega, et
                            püüan
                            täita võimatut missiooni.</h6>
                        <p>Suurim erinevus
                            MySQL päringu
                            tulemuste tagastamise ja töödeldud andmete laadimise kiiruse vahel minu koduarvutis: MySQL:
                            0.007775068283081055,
                            php: 5.6625449657440186 sekundit (orchestras, 4(!) andmerida koos kõigi alamate ja alamate
                            alamatega)
                        </p>
                </div>

            </div>
        </div>
    </div>
    </div>
</body>

</html>