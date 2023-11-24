<?php
if (!isset($_SESSION)) session_start();
$from = '';
if (isset($_REQUEST['cb']) && !empty($_REQUEST['cb'])) {
	$from = '/'.urldecode($_REQUEST["cb"]);
} else {
    if ($_SESSION['loginstart']) {
        $from = $_SESSION['loginstart'];
    }
    if (isset($_SESSION['fromQueryString'])) {
        $from .= '?' . $_SESSION['fromQueryString'];
    }
}
if (!empty(trim($_POST['connection_token'])))
{
	$token = $_POST['connection_token'];
	$ini = parse_ini_file(__DIR__.'/../../../config/social.ini', true);
	$cnf = $ini['OneAll'];
	$site_subdomain = $cnf['subDomain'];
	$site_public_key = $cnf['public'];
	$site_private_key = $cnf['private'];
	$site_domain = $site_subdomain.'.api.oneall.com';
	$resource_uri = 'https://'.$site_domain.'/connections/'.$token .'.json';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $resource_uri);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_USERPWD, $site_public_key . ":" . $site_private_key);
	curl_setopt($curl, CURLOPT_TIMEOUT, 15);
	curl_setopt($curl, CURLOPT_VERBOSE, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl, CURLOPT_FAILONERROR, 0);
	$result_json = curl_exec($curl);
	if ($result_json === false)
	{
		echo 'Curl error: ' . curl_error($curl). '<br />';
		echo 'Curl info: ' . curl_getinfo($curl). '<br />';
		curl_close($curl);
	}
	else
	{
		curl_close($curl);
		$json = json_decode($result_json);
		$data = $json->response->result->data;
		switch ($data->plugin->key)
		{
			case 'social_login':
			if ($data->plugin->data->status == 'success')
				{
					$userToken = $data->user->user_token;
					$identityToken = $data->user->identity->identity_token;
					$userName = $data->user->identity->displayName;
					$email = isset($data->user->identity->emails) ? $data->user->identity->emails[0]->value : '';
					$provider = $data->user->identity->provider;
					$_SESSION['currentPerson'] = "$userName ($provider)";		
                    $userData['userToken'] = $userToken;
                    $userData['identityToken'] = $identityToken;
                    $userData['username'] = $userName;
                    $userData['email'] = $email;
                    $userData['social'] = $provider;
                    $_SESSION['userData'] = $userData;
			}
			break;
		}
	}
	
	$_SESSION['newfrom'] = $from;

}
else
{
	echo "No posted data";
}
	header("Location: $from");

?>