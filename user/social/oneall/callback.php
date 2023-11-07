<?php
session_start();
	if ($_SESSION['loginstart']) $from = $_SESSION['loginstart'];
	if ($_SESSION['fromQueryString']) $from .= '?' . $_SESSION['fromQueryString'];

//include_once(__DIR__.'/../../../db.php');
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
		$json = json_decode ($result_json);
		$data = $json->response->result->data;
	//$db = new db;
		switch ($data->plugin->key)
		{
			case 'social_login':
			if ($data->plugin->data->status == 'success')
				{
					// The user_token uniquely identifies the user 
					// that has connected with the social network account.
					$userToken = $data->user->user_token;
					// The identity_token uniquely identifies the social network account 
					// that the user has used to connect with,
					$identityToken = $data->user->identity->identity_token;			 
						// 1) Check if you have a user_id for this token in your database
					// 1a) If the user_id is empty then this is the first time that this user 
					// has connected with a social network account on your website
					$userName = $data->user->identity->displayName;
					$email = $data->user->identity->emails[0]->value;
					$provider = $data->user->identity->provider;
					/*
					if ($db->getUserIdForUserToken($userToken) === null)
					{
						// 1a1) Create a new user account and store it in your database
						// Optionally display a form to collect  more data about the user.
						$existingUser = $db->existingUser($userName, $email);
						if (!$existingUser)
						{
							$db->newUser($userName, $email);
							$existingUser = $db->existingUser($userName, $email);
						}
						
						$userId = $existingUser;
							// 1a2) Attach the user_token to the user_id of the created account.
						$db->linkUserTokenToUserId($userId, $userToken);
					}
					*/
					// 1b) If you DO have an user_id for the user_token then this user has
					// already connected before
					/*
					else
					{
					$userId = $db->getUserIdForUserToken($userToken);
						// 1b1) The account already exists
					}
					*/
					// 2) You have now either created a new user or read the details of an existing
					// user from your database. In both cases you should now have a $user_id 
					// Now need to login this user, exactly like you would login a user
					// after a traditional (username/password) login (i.e. set cookies, setup 
					// the session) and forward him to another page (i.e. his account dashboard)	  
					$_SESSION['currentPerson'] = "$userName ($provider)";				
			}
			break;
		}
	}
	if (empty($from)) $from = '/uni-api';
	
	header("Location: $from");
}
else
{
	echo "No posted data";
}
		
?>