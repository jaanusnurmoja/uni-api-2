<?php
/**
 * @package uniapiplus
 *
 * @see api
 * @see https://github.com/darioTecchia/uni-api
 * 1) frontend
 *
 * 2) rest api
 *
 * 3) halduskeskkond - @see admin
 *
 * 4) kasutajate autentimine ainult id-kaardi või sotsiaalkontodega
 *
 * @author Jaanus Nurmoja <jaanus.nurmoja@gmail.com>

 */

session_start();

$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
$request = !empty($path) ? explode('/', $path) : [];
$api = isset($_GET['api']) ? true : false;

$uri = trim($_SERVER['REQUEST_URI'], '/');
$http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$siteBase = str_replace(['/admin', $path, '/index.php'], '', $_SERVER['PHP_SELF']);

$siteBaseUrl = $http . $_SERVER['HTTP_HOST'] . $siteBase;

$_SESSION['loginstart'] = str_replace('/index.php', '', $_SERVER['PHP_SELF']);

if (!empty($_SERVER['QUERY_STRING'])) {
    $_SESSION['fromQueryString'] = $_SERVER['QUERY_STRING'];
}

$_SESSION['urlComingFrom'] = $uri;
$socialIni = parse_ini_file('config/social.ini', true);
$oneAllSubDomain = $socialIni['OneAll']['subDomain'];
$idCardAuthService = $socialIni['IdCard']['authService'];
$_SESSION['idCardAuthService'] = $idCardAuthService;
$cb = (bool) $socialIni['IdCard']['callback'] === true ? '?cb=' . urlencode($siteBaseUrl) : '';

include_once 'user/Session.php';

if (!empty([isset($_SESSION['currentPerson']), isset($_SESSION['userData']), isset($_SESSION['idCardData'])])) {
    new \user\Session();
    /**
     * Sisseloginud kasutaja, nagu ta avalikult kuvatakse
     *
     * PEREKONNANIMI,EESNIMI,34506070890 (10, eId:5)
     */
    function loggedIn()
    {
        if (isset($_SESSION['loggedIn'])) {
            $u = $_SESSION['loggedIn']['userData'];
            $sId = '';
            if (isset($u->person->id)) {
                $sId = ': ' . $u->person->id;
            }
            return $u->username . ' (' . $u->id . ', ' . $u->social . $sId . ')';

        }
    }
}
?>


<!DOCTYPE html>
<html lang="et">

<head>
    <title>
        Jaanus Nurmoja uni-api projekt
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
        integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"
        integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.cycle2/2.1.6/jquery.cycle2.min.js"
        integrity="sha512-lvcHFfj/075LnEasZKOkj1MF6aLlWtmpFEyd/Kc+waRnlulG5er/2fEBA5DBff4BZrcwfvnft0PiAv4cIpkjpw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/4.2.2/masonry.pkgd.min.js"
        integrity="sha512-JRlcvSZAXT8+5SQQAvklXGJuxXTouyq8oIMaYERZQasB8SBDHZaUbeASsJWpk0UUrf89DP3/aefPPrlMR1h1yQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"
        integrity="sha512-WMEKGZ7L5LWgaPeJtw9MBM4i5w5OSBlSjTjCtSnvFJGSVD26gE5+Td12qN5pvWXhuWaWcVwF++F7aqu9cvqP0A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="86dc64a4e19322c20a32dff2-text/javascript" src="<?=$siteBase?>/common/js/jquery.base64.js"></script>
    <!-- script type="86dc64a4e19322c20a32dff2-text/javascript" src="<?=$siteBase?>/common/js/jquery.masonry.min.js">
    </!-->
    <script type="86dc64a4e19322c20a32dff2-text/javascript" src="<?=$siteBase?>/common/js/frontend.js"></script>
    <script type="86dc64a4e19322c20a32dff2-text/javascript" src="<?=$siteBase?>/common/js/jquery.serializejson.min.js">
    </script>
    <script type="86dc64a4e19322c20a32dff2-text/javascript" src="<?=$siteBase?>/common/js/repeatable-fields.js">
    </script>

    <script type="text/javascript">
    var oa = document.createElement('script');
    oa.type = 'text/javascript';
    oa.async = true;
    oa.src = '//<?=$oneAllSubDomain?>.api.oneall.com/socialize/library.js'
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(oa, s);
    </script>
</head>

<body>
    <nav class="navbar sticky-top navbar-dark bg-dark">
        <div class="container-fluid">
            <ul class="navbar-nav nav-pills list-group-horizontal">
                <li class="nav-item">
                    <a class="navbar-brand" href="/uni-api">Avaleht</a>
                </li>
                <?php
if (loggedIn()) {?>
                <li><button class="btn btn-warning" style="margin-top:-2px;"
                        onclick="window.location.href='user/logout'"><?=loggedIn()?> | LOGOUT</button></li>
                <?php } else {?>
                <li class="nav-item navbar-brand">Sisene: </li>
                <li><button class="btn btn-warning" style="margin-top:-2px;"
                        onclick="window.location.href='<?=$idCardAuthService . $cb?>'">Eesti
                        ID kaardiga</button></li>
                <li class="nav-item"><button id="oa_social_login_link" class="btn btn-warning"
                        style="margin-top:-2px;"><img src="https://secure.oneallcdn.com/img/favicon.png"
                            style="max-height:16px"><span style="vertical-align:top; padding-left:2px">MUUL VIISIL |
                            OTHER</span></button>
                    <script type="text/javascript">
                    var _oneall = _oneall || [];
                    _oneall.push(['social_login', 'set_callback_uri',
                        '<?php echo $siteBaseUrl ?>/user/social/oneall/callback.php?cb=<?=urlencode($uri)?>'
                    ]);
                    _oneall.push(['social_login', 'set_providers', [
                        'google', 'facebook', 'twitter', 'windowslive', 'openid', 'github'
                    ]]);
                    _oneall.push(['social_login', 'set_custom_css_uri',
                        'https://secure.oneallcdn.com/css/api/themes/beveled_connect_w208_h30_wc_v1.css'
                    ]);
                    _oneall.push(['social_login', 'set_grid_sizes', [3, ]]);
                    _oneall.push(['social_login', 'attach_onclick_popup_ui', 'oa_social_login_link']);
                    </script>
                </li>
                <?php }?>
                <li class="nav-item">
                    <a class="navbar-brand" href="/uni-api/docs/php">Hetkeseisu dokumentatsioon</a>
                </li>
            </ul>
            <a class="navbar-brand" href="/uni-api/admin">Admin</a>

        </div>
    </nav>
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
                <h4>uni-api algsete funktsionaalsuste näide</h4>
                <table class="table table-striped">
                    <tr>
                        <td>Õllele id-ga 1 vastavad sündmused</td>
                        <td><a href="api/beers/1/events">/api/beers/1/events</a></td>
                    </tr>
                    <tr>
                        <td>Sündmusele id-ga 1 vastavad õlled</td>
                        <td><a href="api/events/1/beers">/api/events/1/beers</a></td>
                    </tr>
                </table>
            </div>
            <div class="col-md">
                <p>Eesmärk on luua universaalne php & mysql crud api, mis edastab andmeid kuitahes
                    keerukate ja mitmekihiliste seostega (nagu mu <a
                        href="https://test.nurmoja.net.ee/repeat/">varasemas näidisrakenduses</a>), võimalikult väheste
                    päringute hulgaga ning võimalikult
                    lihtsama ja napima seadistusega (sh nt ilma võõrvõtmete määramiseta andmebaasis).
                    Aluseks on võetud üks "lihtsa crud api" <a
                        href="https://github.com/darioTecchia/uni-api">projekt</a> ,
                    mida
                    paraku pole mitu aastat edasi arendatud. Osa funktsionaalsusi võtan kindlasti sealt üle.
                </p>
                <p>
                    Mõistagi oleksin tänulik heade vihjete eest. Usun, et sama eesmärgi täitmiseks on olemas ka
                    lihtsamaid vahendeid ning ka vajalikke php-teeke.
                </p>
                <div>
                    <h4 class="bg-warning">UUS: kasutajate automaatne registreerimine</h4>
                    <p>
                        Kui siiani oli kasutajal võimalik lihtsalt oma sotsiaalkontoga või id-kaardiga sisse logida, et
                        pääseda ligi valmivale halduskeskkonnale, siis alates 24.11.2023 registreeritakse sisseloginu
                        automaatselt ka saidi kasutajaks, kui ta seda juba ei ole. <span class="bg-warning">ID-kaardiga
                            sisseloginu kohta moodustatakse lisaks isikuprofiil, millega saab see kasutaja hiljem
                            seostada ka kõik oma teised kontod. </span>Traditsioonilist
                        "kasutajanimi:parool" tüüpi sisselogimist ning vormi kaudu kasutajaks registreerumist ma hetkel
                        ei plaani.
                    </p>
                    <p> &#xF33B; Lahenduse sai hiljaaegu probleem, kus esmasel sisselogimisel ID kaardiga tuli pärast
                        autentimist veel ühele "Jätka" lingile klikata, sest kasutaja moodustamise protsess seiskus. See
                        jätkamislink oli ajutine lahendus.</p>
                    <h4 class="bg-warning">TEOKSIL: halduskeskkond</h4>
                    <p>
                        Halduskeskkonda peaks tekkima võimalus luua api sisutüüpide loomiseks uusi andmebaasitabeleid
                        või kaasata olemasolevaid ning tekitada tabelite vahele soovitud andmeseoseid.
                    </p>
                    <p>
                        <mark>Seisuga 12.12.2023</mark> toimub sisuhaldussüsteemis uute tabelite loomine koos vaikimisi
                        väljadega, milleks on lisaks primaarvõtmele ka kirje loomise ja muutmise andmed (kasutaja id ja
                        vastavad ajatemplid)

                    </p>
                    <p>
                        <mark>Alates 29.11.2023</mark> on autori fookuses sisuhaldusse kaasatavate tabelite ja nende
                        juurde kuuluva haldamine (st muutmine) üheainsa vormi vahendusel sarnaselt uue tabeli
                        sisestamisega. Tõenäoliselt ei jää see üksainus vorm ainsaks variandiks, kuid tekib esimesena.
                    </p>
                    <p>
                        Oluline väljakutse - muudatuste sisestamise korral peavad $_POST andmetest käiku minema vaid
                        need, mida tegelikult soovitakse muuta (või ka lisada). Seda püüab autor lahendada javascripti
                        abil - et vormivälja
                        muuta, tuleb see kõigepealt aktiivseks teha, sest muutmisvormis on iga väli vaikimisi
                        'disabled'. Sel moel
                        kaasatakse $_POST muutujasse tõepoolest vaid vajalikud, st muudetavad või lisatavad väljad.
                        Iseküsimus, kuidas
                        talitada vormis toimetava kasutaja id-ga, sest selle jaoks ette nähtud väli on loomulikult
                        peidetud, kuid peab
                        samuti muutuma
                        aktiivseks, kui vähemalt üks sama tabeli väli on aktiivne. Sama kehtib tabeli primaarvõtme
                        väärtusega välja kohta.
                    </p>
                </div>
                <div>
                    <h4>Tähtsamad erisused:</h4>
                    <ol>
                        <li>
                            json seadistusfailis <a href='api/relations.json'>relations.json</a> on defineeritud
                            vaid tabelite nimed ning iga tabeli many-to-one või many-to-many seosed. Väljade nimedest on
                            ära toodud vaid primaar-
                            ja võõrvõtmed. many-to-one põhjal genereeritakse omakorda one-to-many seosed.
                            <strong>TEOKSIL: andmebaasipõhine haldus json faili asemel.</strong>
                        </li>
                        <li>
                            päringu väljade loetelud genereeritakse üldise seadistuse põhjal dünaamiliselt,
                            kasutades
                            päringut
                            SHOW
                            COLUMNS FROM tabeli_nimi. Peamise kirje alamtabelite väljanimed on aliasega `tabel:väli`
                        </li>
                        <li>
                            Üks universaalne vahendustabel kõigi many-to-many seoste tarvis (orignaalis on kummalgi
                            suunal oma tabel, nt beers_events
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
                            tulemuste tagastamise ja töödeldud andmete laadimise kiiruse vahel minu koduarvutis:
                            MySQL:
                            0.007775068283081055,
                            php: 5.6625449657440186 sekundit (orchestras, 4(!) andmerida koos kõigi alamate ja
                            alamate
                            alamatega). Veebimajutaja juures olid näitajad siiski paremad - 0.0010159015655517578 vs
                            1.9478819370269775
                        </p>
                </div>

            </div>
        </div>
    </div>
    </div>
    <script>
    jQuery(function() {
        jQuery('.repeat').each(function() {
            jQuery(this).repeatable_fields({
                wrapper: 'table',
                container: 'tbody',
                row: '.trow',
            });
        });
    });
    </script>
    <!-- script src="https://ajax.cloudflare.com/cdn-cgi/scripts/a2bd7673/cloudflare-static/rocket-loader.min.js"
        data-cf-settings="86dc64a4e19322c20a32dff2-|49" defer="">
    </!-->
</body>

</html>