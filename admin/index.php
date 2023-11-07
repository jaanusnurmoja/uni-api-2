<?php namespace Admin;

//require_once 'Autoload.php';

use \Controller\Table as TableController;

session_start();
$thisDir = dirname($_SERVER['SCRIPT_NAME']);

$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
$request = !empty($path) ? explode('/', $path) : [];

$uri = trim($_SERVER['REQUEST_URI'], '/');


$http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$current = str_replace(['/index.php'], '', $_SERVER['PHP_SELF']);

$currentFullUrl = $http.$_SERVER['HTTP_HOST'].$current.$path;

$_SESSION['urlComingFrom'] = $uri;

$siteBase = str_replace(['/admin', $path], '', $current);

$siteBaseUrl = $http.$_SERVER['HTTP_HOST'].$siteBase;

$_SESSION['loginstart'] = str_replace('/index.php', '', $_SERVER['PHP_SELF'] . $path);

if (!empty($_SERVER['QUERY_STRING']))
{
	$_SESSION['fromQueryString'] = $_SERVER['QUERY_STRING'];
}



    function loggedIn()
	{
		if (isset($_SESSION['currentPerson']) && !empty($_SESSION['currentPerson']))
		{
			return $_SESSION['currentPerson'];
		}
	}
if (isset($_SESSION['newfrom'])) print_r($_SESSION['newfrom']);
print_r($_SESSION['loginstart']);

$api = isset($_GET['api']) ? true : false;
include_once __DIR__ .'/Controller/Table.php';

$tc = new \Controller\Table();


if (!$api) {
    ?>
<!DOCTYPE html>
<html lang="et">

<head>
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script type="text/javascript">
    var oa = document.createElement('script');
    oa.type = 'text/javascript';
    oa.async = true;
    oa.src = '//nurmoja.api.oneall.com/socialize/library.js'
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(oa, s);
    </script>
</head>

<body>
    <nav class="navbar sticky-top navbar-dark bg-dark">
        <div class="container-fluid">
            <ul class="navbar-nav nav-pills list-group-horizontal">
                <li class="nav-item">
                    <a class="navbar-brand" href="/uni-api/admin">Admin</a>
                </li>
                <li class="nav-item">
                    <a class="navbar-brand" href="/uni-api/admin/tables">Tabelid</a>
                </li>
                <?php
					if (loggedIn())
					{?>
                <li><button class="btn btn-warning" style="margin-top:-2px;"
                        onclick="window.location.href='<?php echo $siteBaseUrl?>/user/logout'"><?=loggedIn()?> |
                        LOGOUT</button></li>
                <?php }
					else
					{?>
                <li class="nav-item navbar-brand">Sisene: </li>
                <li><button class="btn btn-warning" style="margin-top:-2px;"
                        onclick="window.location.href='https://id.nurmoja.net.ee?cb=<?php echo urlencode($currentFullUrl)?>'">Estonian
                        ID CARD</button></li>
                <li class="nav-item"><button id="oa_social_login_link" class="btn btn-warning"
                        style="margin-top:-2px;"><img src="https://secure.oneallcdn.com/img/favicon.png"
                            style="max-height:16px"><span
                            style="vertical-align:top; padding-left:2px">OTHER</span></button>
                    <script type="text/javascript">
                    var _oneall = _oneall || [];
                    _oneall.push(['social_login', 'set_callback_uri',
                        '<?php echo $siteBaseUrl?>/user/social/oneall/callback.php'
                    ]);
                    _oneall.push(['social_login', 'set_providers', ['github', 'google', 'windowslive', 'openid',
                        'twitter'
                    ]]);
                    _oneall.push(['social_login', 'set_custom_css_uri',
                        'https://secure.oneallcdn.com/css/api/themes/beveled_connect_w208_h30_wc_v1.css'
                    ]);
                    _oneall.push(['social_login', 'set_grid_sizes', [3, ]]);
                    _oneall.push(['social_login', 'attach_onclick_popup_ui', 'oa_social_login_link']);
                    </script>
                </li>
                <?php }?>
            </ul>
            <a class="navbar-brand" href="/uni-api">Sait</a>

        </div>
    </nav>

    <div class="container">
        <?php
}
if (loggedIn()) {
    $r = $tc->pathParams();

    if (!empty($r['type']) && $r['type'] == 'tables') {
        if (!empty($r['item'])) {
            if (!empty($r['subtype'])) {
                if ($r['subtype'] == 'fields' && !empty($r['subitem'])) {
                    echo json_encode($tc->getField(), JSON_PRETTY_PRINT);
                } else {
                    if ($r['subtype'] == 'edit') {
                        $tc->getTableByIdOrName();
                    }
                }
            } else {
                if ($r['item'] == 'new') {
                    $tc->newTable();
                } else {
                    if ($api) {
                        echo json_encode($tc->getTableByIdOrName($api), JSON_PRETTY_PRINT);
                    } else {
                        $tc->getTableByIdOrName();
                    }
                }
            }
        } else {
            if ($api) {
                echo json_encode($tc->getTables($api), JSON_PRETTY_PRINT);
            } else {
                $tc->getTables($api);
            }

        }
    } else {
        if (empty($r['type'])) {
            $tc->getTables($api);
        }

    }
} else {
    ?>
        <div class="card w-75">
            <div class="card-body">
                <h5 class="card-title">Tuleb sisse logida</h5>
                <div class="card-text">
                    Hea huviline! Selleks, et vaadata ringi halduskeskkonnas, peaksid olema sisse logitud.
                    Soovitan seda teha id-kaardiga. Sotsiaalmeedia kontoga sisselogimine ei tundu hetkel töökindel
                    olevat.
                    Praegu ei saa sisseloginust kasutajat - sisseloginu on üksnes oma sessiooniga sees.
                </div>
            </div>
        </div>
        <div class=" card w-75">
            <div class="card-body">
                <h5 class="card-title">Need to log in</h5>
                <div class="card-text">
                    Dear visitor! You should be logged in to look around the administration environment.
                    I recommend doing this with an ID card. Logging in with a social media account does not seem to be
                    reliable
                    at the moment.
                    Currently, a login does not become a registered user - a login is only logged in with its own
                    session.
                </div>
            </div>
        </div>

        <?php
}
if (!$api) {
    ?>
    </div>
</body>

</html>
<?php }?>