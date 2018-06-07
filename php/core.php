<?php
session_start();
session_set_cookie_params(86400);

$LATEST_ToS = "InviteBIG ToS 01-27-2016";

if (isset($_SESSION['MustSignToS']) && 
	(!isset($_POST['request']) || !isset(json_decode($_POST['request'],true)['method']) ||
	json_decode($_POST['request'],true)['method'] != "fSignContract"))
{
	session_regenerate_id(true);
	session_unset();
	session_destroy();
}

$URL_ARGS = array();
if (isset($_SERVER) && isset($_SERVER['REQUEST_URI']))
{
	$URL_ARGS = preg_split('/[\\/\\?]+/', trim($_SERVER['REQUEST_URI'], '/'));
	$URL_ARGS = array_pad($URL_ARGS, 10, "");
}

if (!isset($_SESSION['auth']))
{
	/*session_unset();
    session_destroy();
	session_start();
	session_set_cookie_params(86400);*/
	$_SESSION['auth'] = bin2hex(openssl_random_pseudo_bytes(32));
}

if (!isset($_SESSION['CREATED']) || (time() - $_SESSION['CREATED'] > 86400) || $URL_ARGS[0] == "logout") 
{
	// if new session, or if session started more than 1d ago, regen ID
	session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

/*
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 86400) && !isset($_SESSION['REMEMBER'])) 
{
    // if last request was more than 1d ago, and not "remember me", expire session
	// THIS IS DISABLED BY SETTING time() ABOVE
    session_unset();
    session_destroy();
	header("Location: ".$_SERVER['HTTP_HOST']."/expired");
}
$_SESSION['LAST_ACTIVITY'] = time();
*/

/*
// No longer needed ?
if ($URL_ARGS[0] == "help" || $URL_ARGS[0] == "venue-management-software" || $URL_ARGS[0] == "online-event-booking"
	|| $URL_ARGS[0] == "request-a-demo" || $URL_ARGS[0] == "register" || $URL_ARGS[0] == "forgot" 
	|| $URL_ARGS[0] == "terms" || $URL_ARGS[0] == "privacy" || $URL_ARGS[0] == "")
{
	header( 'Cache-Control: public, max-age=86400' ); 
}
*/

?>
