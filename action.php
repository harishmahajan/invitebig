<?php
header('Cache-Control: private, no-store, no-cache, max-age=0');

require_once("php/functions.php");
require_once("php/core.php");

if (isset($_GET['fSyncStripePlans']) && $_SERVER['REMOTE_ADDR'] == "127.0.0.1")
{
	require_once('php/admin.php');
	SyncStripePlans();
	exit;
}

if (isset($_GET['fCleanupPictures']) && $_SERVER['REMOTE_ADDR'] == "127.0.0.1")
{
	require_once('php/admin.php');
	CleanupPictures();
	exit;
}

if (isset($_GET['fRunOneMinEvents']))// && $_SERVER['REMOTE_ADDR'] == "127.0.0.1")
{
	require_once('php/admin.php');
	RunOneMinEvents();
	exit;
}

if (!isset($_POST['request']))
{
	echo '{"result":"Invalid JSON submitted"}';
	exit;
}

$post = json_decode($_POST['request'],true);

if (!isset($post['method']))
{
	echo '{"result":"Invalid JSON submitted"}';
	exit;
}

if (is_callable($post['method']))
{
	call_user_func($post['method'],$post);
	exit;
}
else
{
	echo '{"result":"Method \''.$post['method'].'\' not found"}';
	exit;
}

function CheckAuth($auth,$method)
{
	// allow auth tag to persist for 20 seconds after it has been used
	// this allows for multithreaded ajax queries to work in parallel
	
	if (!isset($_SESSION['auth']) || !$auth)
	{		
		$_SESSION['auth'] = bin2hex(openssl_random_pseudo_bytes(32));
		echo '{"result":"Invalid authorization token. Refresh the page or login again."}';
		exit;
	}
	else if ($_SESSION['auth'] == $auth)
	{
		$_SESSION['auth'] = bin2hex(openssl_random_pseudo_bytes(32));
		if (!isset($_SESSION['auth_old']))
			$_SESSION['auth_old'] = array();
		
		$found = false;
		$now = time();
		
		for ($i=0; $i<count($_SESSION['auth_old']); )
		{
			if ($_SESSION['auth_old'][$i][1] < $now - 20)
			{
				unset($_SESSION['auth_old'][$i]);
				$_SESSION['auth_old'] = array_values($_SESSION['auth_old']);
				continue;
			}
			
			if ($_SESSION['auth_old'][$i][0] == $auth)
				$found = true;
			
			$i++;
		}
		
		if (!$found)
			$_SESSION['auth_old'][] = array($auth,$now);
		
		while (count($_SESSION['auth_old']) > 10)
			$_SESSION['auth_old'] = array_shift($_SESSION['auth_old']);

		
		
		return;
	}
	else 
	{
		$found = false;
		
		if (isset($_SESSION['auth_old']))
		{
			$now = time();
			
			for ($i=0; $i<count($_SESSION['auth_old']); )
			{
				if ($_SESSION['auth_old'][$i][1] < $now - 20)
				{
					unset($_SESSION['auth_old'][$i]);
					$_SESSION['auth_old'] = array_values($_SESSION['auth_old']);
					continue;
				}
				
				if ($_SESSION['auth_old'][$i][0] == $auth)
					$found = true;
				
				$i++;
			}	
		}
		
		if (!$found)
		{
			echo '{"result":"Invalid authorization token. Refresh the page or login again."}';
			exit;
		}
	}

}

function IsExempt($method)
{
	switch($method)
	{
		case "fSearchVenues":
		case "fGetSearchTypes":
		case "fGetVenueProfile":
		case "fLoadVenueAvailability":
		return true;
		default:
		return false;
	}
}

//////////////////////////////////////////////////////////////////////////////////////////////

function fLogin($post)
{
	require_once('php/user.php');
	
	if (isset($post['email']) && isset($post['password']))
	{
		$res = Login(strtolower($post['email']), $post['password'],false);
		if ($res == "tos")
			echo '{"result":"Must sign ToS","auth":"' . $_SESSION['auth'] . '"}';
		else if ($res == "success")
			echo '{"result":' . json_encode($res) . ',"email":"' . $_SESSION['email'] . 
		'","firstname":"' . $_SESSION['firstname'] . '","lastname":"' . $_SESSION['lastname'] . 
		'","phone":"' . $_SESSION['phone'] . '","ssoUser":"' . $_SESSION['ssoUser'] . '","birthdate":"' . $_SESSION['birthdate'] . 
		'","timezone":"' . $_SESSION['timezone'] . '","promotions":"' . $_SESSION['promotions'] .
		'","venueRights":' . json_encode($_SESSION['venueRights']) .
		',"auth":"' . $_SESSION['auth'] . '"}';
		else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}
	else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
}

function fFBLogin($post)
{
	require_once('php/user.php');
	
	if (isset($post['token']))
	{
		$res = FBLogin($post['token']);
		if ($res == "tos")
			echo '{"result":"Must sign ToS","auth":"' . $_SESSION['auth'] . '"}';
		else if ($res == "success")
			echo '{"result":' . json_encode($res) . ',"email":"' . $_SESSION['email'] . 
		'","firstname":"' . $_SESSION['firstname'] . '","lastname":"' . $_SESSION['lastname'] . 
		'","phone":"' . $_SESSION['phone'] . '","ssoUser":"' . $_SESSION['ssoUser'] . '","birthdate":"' . $_SESSION['birthdate'] . 
		'","timezone":"' . $_SESSION['timezone'] . '","promotions":"' . $_SESSION['promotions'] .
		'","venueRights":' . json_encode($_SESSION['venueRights']) .
		',"auth":"' . $_SESSION['auth'] . '"}';
		else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}
	else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
}

function fGLogin($post)
{
	require_once('php/user.php');
	
	if (isset($post['token']))
	{
		$res = GLogin($post['token']);
		if ($res == "tos")
			echo '{"result":"Must sign ToS","auth":"' . $_SESSION['auth'] . '"}';
		else if ($res == "success")
			echo '{"result":' . json_encode($res) . ',"email":"' . $_SESSION['email'] . 
		'","firstname":"' . $_SESSION['firstname'] . '","lastname":"' . $_SESSION['lastname'] . 
		'","phone":"' . $_SESSION['phone'] . '","ssoUser":"' . $_SESSION['ssoUser'] . '","birthdate":"' . $_SESSION['birthdate'] . 
		'","timezone":"' . $_SESSION['timezone'] . '","promotions":"' . $_SESSION['promotions'] .
		'","venueRights":' . json_encode($_SESSION['venueRights']) .
		',"auth":"' . $_SESSION['auth'] . '"}';
		else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}
	else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
}

function fTWLogin($post)
{
	require_once('php/user.php');
	
	if (isset($post['token']))
	{
		$res = GLogin($post['token']);
		if ($res == "tos")
			echo '{"result":"Must sign ToS","auth":"' . $_SESSION['auth'] . '"}';
		else if ($res == "success")
			echo '{"result":' . json_encode($res) . ',"email":"' . $_SESSION['email'] . 
		'","firstname":"' . $_SESSION['firstname'] . '","lastname":"' . $_SESSION['lastname'] . 
		'","phone":"' . $_SESSION['phone'] . '","ssoUser":"' . $_SESSION['ssoUser'] . '","birthdate":"' . $_SESSION['birthdate'] . 
		'","timezone":"' . $_SESSION['timezone'] . '","promotions":"' . $_SESSION['promotions'] .
		'","venueRights":' . json_encode($_SESSION['venueRights']) .
		',"auth":"' . $_SESSION['auth'] . '"}';
		else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}
	else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
}

function fLogout($post)
{
	session_unset();
	session_destroy();
	session_start();
	session_regenerate_id(true);
	session_set_cookie_params(86400);
	$_SESSION['auth'] = bin2hex(openssl_random_pseudo_bytes(32));
	$_SESSION['CREATED'] = time();
	$_SESSION['LAST_ACTIVITY'] = time();
	echo '{"result":"success","auth":"' . $_SESSION['auth'] . '"}';
}

function fForgot($post)
{
	require_once('php/user.php');
	
	if (isset($post['email']) && isset($post['name']))
	{
		$res = ResetPassword($post['email'],$post['name']);
		echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}
	else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
}

function fGetSearchTypes($post)
{
	require_once('php/venues_public.php');
	
	$r = GetResourceTypes();
	//$f = GetFeatureTypes();
	$types = array();
	
	for ($i=0; $i<count($r); $i++)
		if (!in_array($r[$i]['name'],$types))
			$types[] = $r[$i]['name'];

	//for ($i=0; $i<count($f); $i++)
	//	if (!in_array($f[$i]['name'],$types))
	//		$types[] = $f[$i]['name'];

		sort($types);

		echo '{"result":' . json_encode($types) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}

	function fContact($post)
	{
		require_once('php/email.php');

		if (isset($post['email']) && isset($post['subject']) && isset($post['message']) && filter_var($post['email'], FILTER_VALIDATE_EMAIL))
		{		
			$res = SendContactMessage($post['email'],$post['subject'],$post['message']);
			echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fRequestDemo($post)
	{
		require_once('php/email.php');

		if (isset($post['email']) && isset($post['name']) && isset($post['company']) && filter_var($post['email'], FILTER_VALIDATE_EMAIL))
		{
			$str = "InviteBIG demo requested via the 'Request a Demo' page!<br><br>Email: ".$post['email']."<br>Name: ".$post['name']."<br>Company: ".$post['company']."<br>Phone: ";

			if (isset($post['phone']))
				$str .= $post['phone'];

			$str .= "<br>Message: ";

			if (isset($post['message']))
				$str .= $post['message'];

			$str .= "<br><br>";

			$res = SendContactMessage($post['email'],"InviteBIG Demo Requested",$str);
			echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function CheckPassword($password)
	{
	// simple password policy check

		$ok = true;
		if (strlen($password) < 8)
			$ok = false;
		if (strtolower($password) == "password" || $password == "12345678")
			$ok = false;
		if (!preg_match("#[0-9]+#", $password))
			$ok = false;
		if (!preg_match("#[a-zA-Z]+#", $password))
			$ok = false;

		return $ok;
	}

	function fRegister($post)
	{
		require_once('php/email.php');
		require_once('php/user.php');

		if (isset($post['fbid']) && isset($post['gid']) && isset($post['twid']) && 
			isset($post['email']) && isset($post['password']) && 
			isset($post['timezone']) && isset($post['firstname']) && 
			isset($post['lastname']) && isset($post['isvenue']))
		{
			if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL))
			{
				echo '{"result":"Please specify a valid email address","auth":"' . $_SESSION['auth'] . '"}';
				exit;
			}

			if ($post['fbid'] == "" && $post['gid'] == "" && $post['twid'] == "" && !CheckPassword($post['password']))
			{
				echo '{"result":"Your password must be at least 8 characters long and contain at least 1 number and 1 letter","auth":"' . $_SESSION['auth'] . '"}';
				exit;
			}

			if (!isset($post['firstname']) || !isset($post['lastname']) || 
				strlen($post['firstname']) < 3 || strlen($post['lastname']) < 3 ||
				preg_match("/\\d/", $post['firstname']) > 0 || preg_match("/\\d/", $post['lastname']) > 0)
			{
				echo '{"result":"Your first and last name cannot contain numbers","auth":"' . $_SESSION['auth'] . '"}';
				exit;
			}

			if (!isset($post['birthdate']) || $post['birthdate'] == "0000-00-00") $post['birthdate'] = null;
			if (!isset($post['phone'])) $post['phone'] = null;
			if (!isset($post['terms'])) $post['terms'] = null;
		//if (!isset($post['promotions'])) $post['promotions'] = 1;

			if (IsValidUser($post['email']))
			{
				echo '{"result":"The email address you specified is already registered","auth":"' . $_SESSION['auth'] . '"}';
				exit;
			}

			$role = 0;
			if ($post['isvenue']) $role = 2;

			$code = md5($_SESSION['auth']);
			$res = Register($post['fbid'],$post['gid'],$post['twid'],strtolower($post['email']), $post['password'],ucwords(strtolower($post['firstname'])),
				ucwords(strtolower($post['lastname'])),$post['birthdate'],$post['phone'],$role,$code,$post['timezone'],
				true);

			if ($res == "success")
				echo '{"result":"success","auth":"' . $_SESSION['auth'] . '"}';
			else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Some required information is missing","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fVerifyAccount($post)
	{
		require_once('php/user.php');
		if (isset($post['code']))
		{
			$res = VerifyAccount($post['code']);
			echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fResendVerification($post)
	{
		require_once('php/email.php');
		if (isset($post['email']))
		{
			$res = SendVerificationEmail($post['email']);
			echo '{"result":"success","auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}


	function fGetVenueApprovalRequests($post)
	{
		require_once('php/admin.php');

		if (!isset($_SESSION['siteRole']) || $_SESSION['siteRole'] != 999)
		{
			echo '{"result":"You are not an administrator, you are not authorized to perform this action","auth":"' . $_SESSION['auth'] . '"}';
			exit;
		}
		else
		{
			$res = GetVenuesPendingApproval();
			echo '{"result":"success","venues":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
	}

	function fAdminApproveVenue($post)
	{
		require_once('php/admin.php');

		if (!isset($_SESSION['siteRole']) || $_SESSION['siteRole'] != 999)
		{
			echo '{"result":"You are not an administrator, you are not authorized to perform this action","auth":"' . $_SESSION['auth'] . '"}';
			exit;
		}
		else if (isset($post['venueid']))
		{
			$res = ApproveVenue($post['venueid']);
			echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fAdminDenyVenue($post)
	{
		require_once('php/admin.php');

		if (!isset($_SESSION['siteRole']) || $_SESSION['siteRole'] != 999)
		{
			echo '{"result":"You are not an administrator, you are not authorized to perform this action","auth":"' . $_SESSION['auth'] . '"}';
			exit;
		}
		else if (isset($post['venueid']))
		{
			$res = DenyVenue($post['venueid']);
			echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fAdminGetConfigList($post)
	{
		require_once('php/admin.php');

		if (!isset($_SESSION['siteRole']) || $_SESSION['siteRole'] != 999)
		{
			echo '{"result":"You are not an administrator, you are not authorized to perform this action","auth":"' . $_SESSION['auth'] . '"}';
			exit;
		}
		else
		{
			$res = GetAdminConfigList();
			echo '{"result":"success","venues":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
	}

	function fAdminGetOverview($post)
	{
		require_once('php/admin.php');

		if (!isset($_SESSION['siteRole']) || $_SESSION['siteRole'] != 999)
		{
			echo '{"result":"You are not an administrator, you are not authorized to perform this action","auth":"' . $_SESSION['auth'] . '"}';
			exit;
		}
		else
		{
			$res = GetAdminOverviewStats();
			if (count($res) > 0)
				echo '{"result":"success","stats":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
			else echo '{"result":"Failed to collect stats","auth":"' . $_SESSION['auth'] . '"}';
		}
	}

	function fAdminGetUsers($post)
	{
		require_once('php/admin.php');

		if (!isset($_SESSION['siteRole']) || $_SESSION['siteRole'] != 999)
		{
			echo '{"result":"You are not an administrator, you are not authorized to perform this action","auth":"' . $_SESSION['auth'] . '"}';
			exit;
		}
		else
		{
			$res = AdminGetUsers();
			if (count($res) > 0)
				echo '{"result":"success","users":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
			else echo '{"result":"Failed to collect user list","auth":"' . $_SESSION['auth'] . '"}';
		}
	}

	function fAdminGetVenues($post)
	{
		require_once('php/admin.php');

		if (!isset($_SESSION['siteRole']) || $_SESSION['siteRole'] != 999)
		{
			echo '{"result":"You are not an administrator, you are not authorized to perform this action","auth":"' . $_SESSION['auth'] . '"}';
			exit;
		}
		else
		{
			$res = AdminGetVenues();
			if (count($res) > 0)
				echo '{"result":"success","venues":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
			else echo '{"result":"Failed to collect user list","auth":"' . $_SESSION['auth'] . '"}';
		}
	}

	function fCheckIfEditOK($post)
	{
		require_once('php/venues_private.php');

		if (isset($post['resourceid']))
		{
			$res = CheckIfEditOK($post['resourceid']);
			echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fCheckIfDeleteOK($post)
	{
		require_once('php/venues_private.php');

		if (isset($post['ids']))
		{
			$result = "success";

			foreach ($post['ids'] as $id)
			{
				if (!CheckIfEditOK($id) != "success")
					$result = "no";
			}

			echo '{"result":' . json_encode($result) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fSaveVenue($post)
	{
		require_once('php/venues_private.php');

		if (isset($post['data']))
		{
			$res = SaveVenue($post['data']['config']);
			if ($res != "success")
			{
				echo '{"result":"' . $res . '","auth":"' . $_SESSION['auth'] . '"}';
				exit;
			}
			
			if ($post['data']['config']['id'] >= 0)
			{
				$de_ids = "";
				$re_ids = "";
				$ad_ids = "";
				$r_ids = "";
				$m_ids = "";
				$i_ids = "";
				$p_ids = "";
				$per_ids = "";
				$q_ids = "";

				foreach ($post['data']['deposits'] as $policy)
				{
					SaveDepositPolicy($post['data']['config']['id'], $policy);
					$de_ids .= $policy['id'].",";
				}
				foreach ($post['data']['refunds'] as $policy)
				{
					SaveRefundPolicy($post['data']['config']['id'], $policy);
					$re_ids .= $policy['id'].",";
				}
				foreach ($post['data']['promos'] as $promo)
				{
					SavePromo($post['data']['config']['id'], $promo, $post['data']['config']['timezone']);
					$p_ids .= $promo['id'].",";
				}
				foreach ($post['data']['addons'] as $addon)
				{
					SaveAddon($post['data']['config']['id'], $addon, $post['data']['config']['timezone']);
					$ad_ids .= $addon['id'].",";
				}
				foreach ($post['data']['resources'] as &$resource)
				{
					SaveResource($post['data']['config']['id'], $resource, $post['data']['config']['timezone']);
				}
				foreach ($post['data']['resources'] as &$resource)
				{
					$r_ids .= GetResourceIDs($resource);
				}
				foreach ($post['data']['food']['menus'] as &$menu)
				{
					$m_ids .= SaveMenu($post['data']['config']['id'], $menu, $post['data']['config']['timezone']).",";
				}
				foreach ($post['data']['food']['items'] as &$item)
				{
					$i_ids .= SaveItem($post['data']['config']['id'], $item).",";
				}
				foreach ($post['data']['personnel'] as &$personnel)
				{
					$per_ids .= SavePersonnel($post['data']['config']['id'], $personnel, $post['data']['config']['timezone']).",";
				}
				foreach ($post['data']['questions'] as &$question)
				{
					$q_ids .= SaveQuestion($post['data']['config']['id'], $question).",";
				}

				$de_ids = preg_replace("/[^0-9],/","",rtrim($de_ids, ","));
				$re_ids = preg_replace("/[^0-9],/","",rtrim($re_ids, ","));
				$ad_ids = preg_replace("/[^0-9],/","",rtrim($ad_ids, ","));
				$r_ids = preg_replace("/[^0-9],/","",rtrim($r_ids, ","));
				$m_ids = preg_replace("/[^0-9],/","",rtrim($m_ids, ","));
				$i_ids = preg_replace("/[^0-9],/","",rtrim($i_ids, ","));
				$p_ids = preg_replace("/[^0-9],/","",rtrim($p_ids, ","));
				$per_ids = preg_replace("/[^0-9],/","",rtrim($per_ids, ","));
				$q_ids = preg_replace("/[^0-9],/","",rtrim($q_ids, ","));

				PruneDeposits($post['data']['config']['id'], $de_ids);
				PruneRefunds($post['data']['config']['id'], $re_ids);
				PrunePromos($post['data']['config']['id'], $p_ids);
				PruneAddons($post['data']['config']['id'], $ad_ids);
				PruneResources($post['data']['config']['id'], $r_ids);
				PruneMenus($post['data']['config']['id'], $m_ids);
				PruneItems($post['data']['config']['id'], $i_ids);
				PrunePersonnel($post['data']['config']['id'], $per_ids);
				PruneQuestions($post['data']['config']['id'], $q_ids);

				foreach ($post['data']['resources'] as &$resource)
				{
					ClearResourceRelationships($resource);
					SaveResourceRelationships($resource);
				}

				require_once("php/user.php");
				Login("","",true);
				echo '{"result":"success","id":"'.$post['data']['config']['id'].'","auth":"' . $_SESSION['auth'] . '"}';
				exit;
			}

			echo '{"result":"There was an error saving your venue configuration","auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fLoadVenueConfig($post)
	{
		require_once('php/venues_private.php');

		if (isset($post['venueid']))
		{
			$auth = false;
			if ($_SESSION['siteRole'] == 999)
				$auth = true;
			else
			{
				foreach ($_SESSION['venueRights'] as $venue)
					if ($venue['venueid'] == $post['venueid']) $auth = true;
			}

			if ($auth)
			{
				$arr = array();
				$arr['config'] = LoadVenue($post['venueid']);
				$arr['deposits'] = LoadDeposits($post['venueid']);
				$arr['refunds'] = LoadRefunds($post['venueid']);
				$arr['promos'] = LoadPromos($post['venueid']);
				$arr['addons'] = LoadAddons($post['venueid'], $arr['config']['timezone']);
				$arr['resources'] = LoadResources($post['venueid'], $arr['config']['timezone']);
				$arr['relationships'] = LoadRelationships($post['venueid']);
				$arr['food'] = LoadFood($post['venueid'], $arr['config']['timezone']);
				$arr['personnel'] = LoadPersonnel($post['venueid'], $arr['config']['timezone']);
				$arr['questions'] = LoadQuestions($post['venueid']);

				$j = json_encode($arr);
				if (is_array($arr) && isset($arr['config']['id']) && strlen($j) > 0)
					echo '{"result":"success","data":' . $j . ',"auth":"' . $_SESSION['auth'] . '"}';
				else echo '{"result":"error","data":"There was an error loading this venue, please contact support","auth":"' . $_SESSION['auth'] . '"}';
			}
			else echo '{"result":"You are not authorized to load this venue\'s configuration","auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fRequestVenueReview($post)
	{
		require_once('php/venues_private.php');

		if (isset($post['venueid']))
		{
			$auth = false;
			if ($_SESSION['siteRole'] == 999)
				$auth = true;
			else
			{
				foreach ($_SESSION['venueRights'] as $venue)
					if ($venue['venueid'] == $post['venueid']) $auth = true;
			}
			if ($auth)
			{

				$res = RequestVenueReview($post['venueid']);
				echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				exit;
			}

			echo '{"result":"There was an error submitting your venue for review","auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fGetVenueTypes($post)
	{
		require_once('php/venues_public.php');
		$res = GetVenueTypes();
		echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}

	function fGetVenueStyles($post)
	{
		require_once('php/venues_public.php');
		$res = GetVenueStyles();
		echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}

	function fGetVenueFeatures($post)
	{
		require_once('php/venues_public.php');
		$res = GetVenueFeatures();
		echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}

	function fGetResourceTypes($post)
	{
		require_once('php/venues_public.php');
		$res = GetResourceTypes();
		echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}

	function fGetAddonTypes($post)
	{
		require_once('php/venues_public.php');
		$res = GetAddonTypes();
		echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}

	function fGetMenuItemTypes($post)
	{
		require_once('php/venues_public.php');
		$res = GetMenuItemTypes();
		echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
	}

	function fGetRefundPolicies($post)
	{
		require_once('php/book.php');
		if (isset($post['policies']))
		{
			$p_ids = "";

			foreach ($post['policies'] as $p)
				$p_ids .= $p.",";

			$p_ids = preg_replace("/[^0-9],/","",rtrim($p_ids, ","));

			if (isset($post['fromBooking']) && $post['fromBooking'] > 0)
				$res = GetBookingRefundPolicies($post['fromBooking']);
			else $res = GetRefundPolicies($p_ids);

			if (is_array($res) && count($res) > 0)
				echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
			else echo '{"result":"There was an error loading refund policies","auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fUpdateUserProfile($post)
	{
		require_once('php/user.php');

		if (isset($post['password']) && isset($post['birthdate']) && isset($post['phone']) &&
			isset($post['timezone']) && isset($post['firstname']) && isset($post['lastname']) &&
			isset($post['promotions']))
		{
			$res = UpdateUserProfile($post);
			if ($res == "success")
				echo '{"result":' . json_encode($res) . ',"email":"' . $_SESSION['email'] . 
			'","firstname":"' . $_SESSION['firstname'] . '","lastname":"' . $_SESSION['lastname'] . 
			'","phone":"' . $_SESSION['phone'] . '","ssoUser":"' . $_SESSION['ssoUser'] . '","birthdate":"' . $_SESSION['birthdate'] . 
			'","timezone":"' . $_SESSION['timezone'] . '","promotions":"' . $_SESSION['promotions'] .
			'","venueRights":' . json_encode($_SESSION['venueRights']) .
			',"auth":"' . $_SESSION['auth'] . '"}';
			else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fCalculateRefund($post)
	{
		require_once('php/book.php');
		if (isset($post['bookingid']))
		{
			if (!CheckBookingAuth($post['bookingid'],4))
				echo '{"result":"You are not authorized to make this request","auth":"' . $_SESSION['auth'] . '"}';
			$res = CalculateRefund($post['bookingid'],true);
			echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
		}
		else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
	}

	function fUpdateBookingStatus($post)
	{
		require_once('php/book.php');
		if (isset($post['bookingid']) && isset($post['venueid']) && isset($post['action']) &&
			isset($post['message']) && isset($post['isVenue']))
		{
			if ($post['isVenue'] > 1)
				$post['isVenue'] = 1;
			
			$auth = false;
			foreach ($_SESSION['venueRights'] as $v)
				if ($v['venueid'] == $post['venueid'])
					if ($v['role'] & 16 || $v['role'] & 4)
						$auth = true;

					if ($_SESSION['siteRole'] == 999)
						$auth = true;

					if ($auth || $post['isVenue'] == 0)
					{
						$res = UpdateBookingStatus($post['bookingid'], $post['venueid'], $post['action'], $post['message'], $post['isVenue']);

						$servername = "localhost";
						$username = "root";
						$password = "jack123";
						$dbname = "invitebignew";
			// $servername = "localhost";
			// $username = "root";
			// $password = "W2GC@zF!6c7%";
			// $dbname = "invitebignew";


			// Create connection
						$conn = new mysqli($servername, $username, $password, $dbname);

			// Removes Confllict booking.

						$date = new DateTime();
						$timestamp1 = $date->getTimestamp();

						$sql = "SELECT booking.bookingid, booking.start as 'start1', booking.stop as 'stop1', booking.venueid as 'venueid', booking.status, br.resourceid as 'resourceid', br.bookingid as 'brbookingid' FROM booking_resources as br, booking INNER JOIN( SELECT start, stop FROM booking GROUP BY start HAVING COUNT(bookingid) >1 )temp ON booking.start= temp.start and booking.stop = temp.stop and booking.venueid = ". $post['venueid'] . " and booking.bookingid = " . $post['bookingid'] ." where br.resourceid = " . $post['rsid'] ." and booking.bookingid = " . $post['bookingid']." and br.bookingid = " . $post['bookingid'];
						$result = $conn->query($sql);
						if ($result->num_rows > 0) {
							while($row = $result->fetch_assoc()) {
								if($row["bookingid"] == $post['bookingid'])
								{
									$sql1 = "SELECT bk.bookingid as 'bookinkdids', bk.venueid as 'venueid',  bk.status as 'sts', bkr.resourceid as 'rsid' FROM booking as bk INNER JOIN booking_resources as bkr ON (bk.bookingid  = bkr.bookingid and bkr.resourceid = ".$post['rsid']." and bk.start = ".$row['start1']." and bk.stop = ".$row['stop1'].")";
							$result1 = $conn->query($sql1);
							if ($result1->num_rows > 0) {
			    				while($row1 = $result1->fetch_assoc()) {
			    					if($row1["bookinkdids"] != $post['bookingid'])
									{
			    						$sql = "UPDATE booking SET status='Cancelled by Venue' WHERE bookingid = ". $row1['bookinkdids'] ." and venueid = ".$row1["venueid"]." and start = ".$row["start1"]." and stop = ".$row["stop1"];
									   if ($conn->query($sql) === TRUE) {
									   }
									}
			    				}
			    			}
			    		}
			    	}
			    }
						$conn->close();

						echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					}
					else echo '{"result":"You are not authorized to perform this action","auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fAutoConflictCancelled($post)
			{
						$servername = "localhost";
						$username = "root";
						$password = "jack123";
						$dbname = "invitebignew";
			// $servername = "localhost";
			// $username = "root";
			// $password = "W2GC@zF!6c7%";
			// $dbname = "invitebignew";

			// Create connection
						$conn = new mysqli($servername, $username, $password, $dbname);

			// Removes Confllict booking.

						$date = new DateTime();
						$timestamp1 = $date->getTimestamp();
						//echo "rsid=".$post['rsid'];
			//$sql = "SELECT bookingid, booking.start as 'start1', booking.stop as 'stop1', booking.venueid as 'venueid', booking.status FROM booking INNER JOIN( SELECT start,stop FROM booking GROUP BY start HAVING COUNT(bookingid) >1 )temp ON booking.start= temp.start and booking.stop = temp.stop and booking.status = 'Pending Approval' and booking.venueid = ". $post['venueid'];
						$sql = "SELECT booking.bookingid, booking.start as 'start1', booking.stop as 'stop1', booking.venueid as 'venueid', booking.status, br.resourceid as 'resourceid', br.bookingid as 'brbookingid' FROM booking_resources as br, booking INNER JOIN( SELECT start,stop FROM booking GROUP BY start HAVING COUNT(bookingid) >1 )temp ON booking.start= temp.start and booking.stop = temp.stop and booking.venueid = ". $post['venueid'] . " and booking.bookingid = " . $post['bookingid'] ." where br.resourceid = " . $post['rsid'] ." and booking.bookingid = " . $post['bookingid']." and br.bookingid = " . $post['bookingid'];
						$result = $conn->query($sql);
			//echo $sql."\r\n";
						if ($result->num_rows > 0) {
							//echo "\r\ncount = ".$result->num_rows;
							while($row = $result->fetch_assoc()) {
								if($row["bookingid"] == $post['bookingid'])
								{
									// echo "\r\nRow=".$row["bookingid"];
									// echo "\r\nBookingid=".$post['bookingid'];
									// echo "\r\nBrBookingid=".$row['brbookingid'];
									// echo "\r\nResourceid=".$row['resourceid'];
			    			//$sql1 = "SELECT bk.bookingid as 'bids', bk.status as 'sts', bkr.resourceid as 'rsid' from booking as bk, booking_resources as bkr where bk.bookingid <> ".$post['bookingid']." and bk.start = ".$row["start1"]." and bk.stop = ".$row["stop1"]." and bkr.bookingid = ".$row["brbookingid"]." and bkr.resourceid = ".$row["resourceid"];
									//$sql1 = "SELECT bk.bookingid as 'bids', bk.status as 'sts', bkr.resourceid as 'rsid' from booking as bk, booking_resources as bkr where bk.bookingid  = bkr.bookingid and bkr.resourceid = ".$post['rsid']." bk.start = ".$row['start1']." bk.stop = ".$row['stop1'];
									$sql1 = "SELECT bk.bookingid as 'bookinkdids', bk.venueid as 'venueid',  bk.status as 'sts', bkr.resourceid as 'rsid' FROM booking as bk INNER JOIN booking_resources as bkr ON (bk.bookingid  = bkr.bookingid and bkr.resourceid = ".$post['rsid']." and bk.start = ".$row['start1']." and bk.stop = ".$row['stop1'].")";
							//echo "\r\n".$sql1;
							$result1 = $conn->query($sql1);
							//echo "\r\ncnt".$result1->num_rows;
							if ($result1->num_rows > 0) {
			    				while($row1 = $result1->fetch_assoc()) {
			    					if($row1["bookinkdids"] != $post['bookingid'])
									{
			    					 //echo "\r\n bookingid = ".$row1['bids'];
			    					 ///echo "\r\n status = ".$row1['sts'];
			    					 //echo "\r\n rsid = ".$row1['rsid'];
			    					
						    		   $sql = "UPDATE booking SET status='Cancelled by Venue' WHERE bookingid = ". $row1['bookinkdids'] ." and venueid = ".$row1["venueid"]." and start = ".$row["start1"]." and stop = ".$row["stop1"];
									   if ($conn->query($sql) === TRUE) {
									   }
									}
			    				}
			    			}
			    		 //$sql = "UPDATE booking SET status='Cancelled by Venue' WHERE bookingid=".$row["bookingid"]." and venueid = ".$row["venueid"]." and start = ".$row["start1"]." and stop = ".$row["stop1"];
									// $sql = "UPDATE booking as b, booking_resources as br SET status='Cancelled by Venue' WHERE bookingid <> ". $post['bookingid'] ." and venueid = ".$row["venueid"]." and start = ".$row["start1"]." and stop = ".$row["stop1"]." and br.resourceid = ".$row["stop1"];
									// if ($conn->query($sql) === TRUE) {
									// }
								}
							}
						}
						$conn->close();

			}


			function fGetUserBookings($post)
			{
				require_once('php/user.php');
				if (isset($post['onlypending']))
				{
					$res = GetUserBookings($post['onlypending']);
					echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetMyMessages($post)
			{
				require_once('php/user.php');
				$res = GetMyMessages((isset($post['onlynew']) ? $post['onlynew'] : null));
				echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetUserUpcomingEvents($post)
			{
				require_once('php/user.php');
				if (true)
				{
					$res = GetUserUpcomingEvents($post['date']);
					echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetVenueBookings($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']) && isset($post['onlypending']))
				{
					$res = GetVenueBookings($post['venueid'],$post['onlypending']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetVenueMessages($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']))
				{
					$res = GetVenueMessages($post['venueid'], (isset($post['onlynew']) ? $post['onlynew'] : null));
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetVenueUpcomingEvents($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']))
				{
					$res = GetVenueUpcomingEvents($post['venueid'],$post['date']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetVenueRefunds($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']))
				{
					$res = GetVenueRefunds($post['venueid']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetVenueNotifications($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']))
				{
					$res = GetVenueNotifications($post['venueid']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fLoadPaymentInfo($post)
			{
				require_once('php/book.php');
				if (isset($post['bookingid']))
				{
					if (!CheckBookingAuth($post['bookingid'],2))
						echo '{"result":"You are not authorized to make this request","auth":"' . $_SESSION['auth'] . '"}';
					else
					{
						$res = LoadPaymentInfo($post['bookingid']);
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					}
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fSubmitPayment($post)
			{
				require_once('php/book.php');
				if (isset($post['bookingid']) && isset($post['token']) && isset($post['amount']))
				{
					if (!CheckBookingAuth($post['bookingid'],4))
						echo '{"result":"You are not authorized to make this request","auth":"' . $_SESSION['auth'] . '"}';
					else
					{
						$res = SubmitPayment($post);
						echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					}
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fSetAcceptOnlinePayments($post)
			{
				require_once('php/book.php');
				if (isset($post['bookingid']))
				{
					if (!CheckBookingAuth($post['bookingid'],4))
						echo '{"result":"You are not authorized to make this request","auth":"' . $_SESSION['auth'] . '"}';
					else
					{
						$res = SetAcceptOnlinePayments($post['bookingid']);
						echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					}
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fRecordPayment($post)
			{
				require_once('php/book.php');
				if (isset($post['bookingid']) && isset($post['name']) && isset($post['type']) && isset($post['amount']) && isset($post['currency']))
				{
					if (!CheckBookingAuth($post['bookingid'],4))
						echo '{"result":"You are not authorized to make this request","auth":"' . $_SESSION['auth'] . '"}';
					else
					{
						$res = RecordPayment($post['bookingid'],$post['name'],$post['type'],$post['amount'],$post['currency']);
						echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					}
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fMarkBookingMessagesRead($post)
			{
				require_once('php/book.php');
				if (isset($post['bookingid']))
				{
					if (!CheckBookingAuth($post['bookingid'],4))
						echo '{"result":"You are not authorized to make this request","auth":"' . $_SESSION['auth'] . '"}';
					else
					{
						$res = MarkBookingMessagesRead($post['bookingid']);
						echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					}
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fSendBookingMessage($post)
			{
				require_once('php/book.php');
				require_once('php/messages.php');
				if (isset($post['bookingid']) && isset($post['text']))
				{
					$res = SendBookingMessage($post['bookingid'],$post['text']);
					echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fUploadBookingFile($post)
			{
				require_once('php/book.php');

				if (!isset($_FILES) || count($_FILES) < 1)
					echo '{"result":"Invalid file count","auth":"' . $_SESSION['auth'] . '"}';
				else if (isset($post['bookingid']) && isset($post['desc']) && isset($post['priv'])) 
				{
					if (!CheckBookingAuth($post['bookingid'],4))
					{
						echo '{"result":"You are not authorized to make this request","auth":"' . $_SESSION['auth'] . '"}';
						exit;
					}

					$result = array();

					foreach ($_FILES as $file)
					{
						for ($i=0; $i<count($file['name']); $i++)
						{
							if (!preg_match('/.(jpg)|(gif)|(png)|(bmp)|(pdf)|(doc)|(docx)|(jpeg)|(rtf)|(txt)|(csv)|(xml)|(pps)|(ppt)|(pptx)$/',$file['name'][$i]))
							{	
								echo '{"result":"Invalid file type","auth":"' . $_SESSION['auth'] . '"}';
								exit;
							}

							$uploaddir = "./assets/content/";
							$dest = $uploaddir.basename($file['name'][$i]);

							$inc = 1;
							while (file_exists($dest))
							{
								$inc++;
								$dest = $uploaddir . preg_replace('/\.(\S*)$/', '-'.$inc.'.$1', basename($file['name'][$i]));
							}

							move_uploaded_file($file['tmp_name'][$i], $dest);

							$res = UploadBookingFile($post['bookingid'],basename($dest),$post['desc'],$post['priv']);
							if ($res != "success")
							{
								echo '{"result":"' . $res . '","auth":"' . $_SESSION['auth'] . '"}';
								exit;
							}
							else $result[] = str_replace($uploaddir,"",$dest);
						}
					}
					echo '{"result":"success","data":' . json_encode($result) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fDeleteBookingFile($post)
			{
				require_once('php/book.php');
				if (isset($post['file']))
				{
					$res = DeleteBookingFile($post['bookingid'],$post['text']);
					echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fLoadBooking($post)
			{
				require_once('php/book.php');
				if (isset($post['bookingid']))
				{
					$res = LoadBooking($post['bookingid']);
					if (is_array($res) && isset($res['id']))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":"' . $res . '","auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fNewMessage($post)
			{
				require_once('php/messages.php');
				if (isset($post['venueid']) && isset($post['title']) && isset($post['message']))
				{
					$res = NewMessage((isset($post['userid']) ? $post['userid'] : $_SESSION['userid']),$post['venueid'],$post['title'],$post['message']);
					echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetVenueList($post)
			{
				require_once('php/messages.php');
				$res = GetVenueList();
				echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetUserList($post)
			{
				require_once('php/messages.php');
				if (isset($post['venueid']))
				{
					$res = GetUserList($post['venueid']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fLoadMessage($post)
			{
				require_once('php/messages.php');
				if (isset($post['messageid']))
				{
					$res = LoadMessage($post['messageid']);
					if (isset($res['messages']))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fSendMessage($post)
			{
				require_once('php/messages.php');
				if (isset($post['convoid']) && isset($post['text']))
				{
					$res = SendMessage($post['convoid'],$post['text']);
					echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fDeleteMessage($post)
			{
				require_once('php/messages.php');
				if (isset($post['convoid']))
				{
					$res = DeleteMessage($post['convoid']);
					echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGenerateSalesReport($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']) && isset($post['start']) && isset($post['stop']))
				{
					$res = GenerateSalesReport($post['venueid'],$post['start'],$post['stop']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fSearchVenues($post)
			{
				require_once('php/venues_public.php');
				if (isset($post['filter']))
				{
					$res = SearchVenues($post['filter']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetVenueProfile($post)
			{
				require_once('php/venues_public.php');
				if (isset($post['venueurl']))
				{
					$res = GetVenueProfile($post['venueurl']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetSessionBooking($post)
			{
				$res = array();

				if (isset($_SESSION['booking']))
					$res = $_SESSION['booking'];

				if (count($res) > 0)
					echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				else echo '{"result":"You have no saved reservations, please start a new reservation","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fSetSessionBooking($post)
			{	
				if (isset($post['booking']))
				{
					if ($post['booking'] == 0)
						unset($_SESSION['booking']);
					else 
					{
						$_SESSION['booking'] = $post['booking'];
						require_once('php/book.php');
						UpdateBookingPrices();
					}

					echo '{"result":"success","auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}


			LoadBookVenue($_POST['functionLoadBookVenue'], (isset($_POST['filter'])?$_POST['filter']:null));

			function fLoadVenueAvailability($post)
			{
				require_once('php/book.php');
				if (isset($post['shorturl']))
				{
					$res = LoadVenueAvailability($post['shorturl'], $post['filter']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetReservationResourceData($post)
			{
				require_once('php/book.php');
				if (isset($post['venueid']) && isset($post['start']) && isset($post['stop']))
				{
					$res = GetReservationResourceData($post['venueid'], $post['start'], $post['stop']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetReservationMenus($post)
			{
				require_once('php/book.php');
				if (isset($post['venueid']) && isset($post['start']) && isset($post['stop']))
				{
					$res = GetReservationMenus($post['venueid'], $post['start'], $post['stop']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetReservationPersonnel($post)
			{
				require_once('php/book.php');
				if (isset($post['venueid']) && isset($post['start']) && isset($post['stop']))
				{
					$res = GetReservationPersonnel($post['venueid'], $post['start'], $post['stop']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetReservationQuestions($post)
			{
				require_once('php/book.php');
				if (isset($post['booking']))
				{
					$arr = $post['booking'];

					$res = array();
					$add = array();
					$menus = array();
					$pers = array();

					if (isset($arr['resources']))
					{
						for ($i = 0; $i < count($arr['resources']); $i++)
						{
							$res[] = $arr['resources'][$i]['id'];

							for ($i2 = 0; $i2 < count($arr['resources'][$i]['addons']); $i2++)
							{
								$add[] = $arr['resources'][$i]['addons'][$i2]['id'];
							}
						}
					}

					if (isset($arr['menus']))
					{
						for ($i = 0; $i < count($arr['menus']); $i++)
						{
							$menus[] = $arr['menus'][$i]['id'];
						}
					}

					if (isset($arr['personnel']))
					{
						for ($i = 0; $i < count($arr['personnel']); $i++)
						{
							$pers[] = $arr['personnel'][$i]['id'];
						}
					}

					$result = GetReservationQuestions($arr['venueid'], $res, $add, $menus, $pers);

					if (is_array($result))
						echo '{"result":"success","data":' . json_encode($result) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($result) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fUpdateBookingPrices($post)
			{
				require_once('php/book.php');
				if (isset($_SESSION['booking']))
				{
					UpdateBookingPrices();
					echo '{"result":"success","data":' . json_encode($_SESSION['booking']) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Your session expired, please refresh the page and start a new reservation","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fCheckBookingErrors($post)
			{
				require_once('php/book.php');
				if (isset($_SESSION['booking']))
				{
					$res = CheckBookingErrors();
					echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Your session expired, please refresh the page and start a new reservation","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fInsertBooking($post)
			{
				require_once('php/book.php');
				if (isset($post['booking']))
				{
					$_SESSION['booking'] = $post['booking'];
					$res = InsertBooking();
					if (is_numeric($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Your session expired, please refresh the page and start a new reservation","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fApplyPromoCode($post)
			{
				require_once('php/book.php');
				if (isset($post['code']) && isset($_SESSION['booking']))
				{
					$res = ApplyPromoCode($post['code']);
					echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Your session expired, please refresh the page and start a new reservation","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fImportBookingForm($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']) && isset($post['name']) && isset($post['desc']) && isset($post['start']) && isset($post['stop']) && 
					isset($post['price']) && isset($post['size']) && isset($post['contactName']) && isset($post['contactEmail']))
				{
					$res = ImportBookingForm($post['venueid'], $post['name'], $post['desc'], $post['start'], $post['stop'], $post['price'], $post['size'], $post['contactName'], $post['contactEmail']);
					echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fContactDump($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']))
				{
					$res = ContactDump($post['venueid']);
					if (is_array($res))
						echo '{"result":"success","users":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fAuthPing($post)
			{
				if (isset($_SESSION['email']) && strlen($_SESSION['email']) > 1 && isset($_SESSION['venueRights']))
				{
					require_once('php/user.php');
					GetVenueRights();
					echo '{"result":"success","cacheTimestamp":"?_=1455331660","rights":' . json_encode($_SESSION['venueRights']) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"success","cacheTimestamp":"?_=1455331660","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGetVenueSubscription($post)
			{

				require_once('php/venues_private.php');
				if (isset($post['venueid']))
				{
					$res = GetVenueSubscription($post['venueid']);
					if (is_array($res))
					{
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					}
					else 
					{
						echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					}
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fSetVenueSubscription($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']) && isset($post['planid']) && isset($post['email']) && isset($post['token']))
				{
					$res = SetVenueSubscription($post['venueid'],$post['planid'],$post['email'],$post['token']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fCancelVenueSubscription($post)
			{
				require_once('php/venues_private.php');
				if (isset($post['venueid']) && isset($post['reason']))
				{
					$res = CancelVenueSubscription($post['venueid'],$post['reason']);
					if (is_array($res))
						echo '{"result":"success","data":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
					else echo '{"result":' . json_encode($res) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fSignContract($post)
			{
				require_once('php/user.php');
				if (isset($_SESSION['userid']) && isset($post['contract']))
				{
					if ($post['contract'] == "tos")
					{
						unset($_SESSION['MustSignToS']);
						$post['contract'] = $GLOBALS['LATEST_ToS'];
					}

					$res = SignContract($post['contract']);
					echo '{"result":"success","auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGuideLoad($post)
			{
				require_once('php/class/guide.php');
				if (isset($post['guideid']))
				{
					$guide = LoadGuide($post['guideid']);

					echo '{"result":"success","guide":' . json_encode($guide->JSON()) . ',"oneLiner":' . json_encode($guide->oneLiner) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			function fGuideMarkComplete($post)
			{
				require_once('php/class/guide.php');
				if (isset($post['guideid']) && isset($post['taskid']))
				{
					$guide = LoadGuide($post['guideid']);
					$guide->MarkComplete($post['taskid']);
					$guide->LoadStatus();

					echo '{"result":"success","guide":' . json_encode($guide->JSON()) . ',"oneLiner":' . json_encode($guide->oneLiner) . ',"auth":"' . $_SESSION['auth'] . '"}';
				}
				else echo '{"result":"Invalid request parameters","auth":"' . $_SESSION['auth'] . '"}';
			}

			?>