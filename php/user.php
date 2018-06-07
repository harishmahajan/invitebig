<?php

function Login($vemail, $vpassword, $isRefresh)
{
	$result = "Login failed, invalid credentials";
	
	$query = "SELECT userid, password, isverified, role, timezone, firstname, lastname, birthdate, phone, receive_Promotions
						FROM users WHERE email = ? AND LENGTH(password) > 0";
	
	if ($isRefresh)
		$query = "SELECT userid, password, isverified, role, timezone, firstname, lastname, birthdate, phone, receive_Promotions
						FROM users WHERE email = ?";
		
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare($query))
	{
		if ($isRefresh)
			$vemail = $_SESSION['email'];
		
		$stmt->bind_param("s", $vemail);
		$stmt->execute();
		$stmt->bind_result($userid,$password,$verified,$role,$timezone,$firstname,$lastname,$birthdate,$phone,$Promotions);
		if($stmt->fetch())
		{
			if ((!$isRefresh && password_verify($vpassword,$password)) || $isRefresh)
			{
				if ($verified == false)
				{
					$result = "Your email address has not been verified";
				} 
				else
				{
					if (!$isRefresh)
					{
						$_SESSION['userid'] = $userid;
						$_SESSION['email'] = $vemail;
						$_SESSION['ssoUser'] = 0;
					}
					$_SESSION['siteRole'] = $role;
					$_SESSION['timezone'] = $timezone;
					$_SESSION['firstname'] = $firstname;
					$_SESSION['lastname'] = $lastname;
					$_SESSION['birthdate'] = $birthdate;
					$_SESSION['phone'] = $phone;
					$_SESSION['promotions'] = $Promotions;
					$_SESSION['profilePic'] = "/assets/img/profilepic-01.png";
					session_regenerate_id(true);
					$result = "success";
				}
			}
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($result == "success")
	{
		//print_r($_SESSION['timezone']);
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE users SET lastlogin = UNIX_TIMESTAMP() WHERE userid = ?"))
		{
			$stmt->bind_param("i",$_SESSION['userid']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}		
		
		$_SESSION['venueRights'] = array();
		GetVenueRights();
		
		$signed = false;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(*) FROM signatures WHERE userid = ? AND contract = ?"))
		{
			$stmt->bind_param("is",$_SESSION['userid'],$GLOBALS['LATEST_ToS']);
			$stmt->execute();
			$stmt->bind_result($c);
			if ($stmt->fetch())
			{
				if ($c > 0)
					$signed = true;
			}			
			$GLOBALS['db']->CloseConn();
		}
		
		if (!$signed)
		{
			$_SESSION['MustSignToS'] = true;
			return "tos";
		}
	}

	return $result;
}

function FBLogin($token)
{
	$result = "There was an error processing your request";
	$graph_url = "https://graph.facebook.com/me?access_token=" . $token;
	$response = file_get_contents($graph_url);
	$decoded_response = json_decode($response,true);
	if (isset($decoded_response['error']))
		return "There was an error processing your request";
    else if (isset($decoded_response['id']))
	{
		$result = "This Facebook account has not been registered with InviteBIG";
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT userid, email, isverified, role, timezone, firstname, lastname, birthdate, phone, receive_Promotions FROM users WHERE fbid = ? AND password = ''"))
		{			
			$stmt->bind_param("s", $decoded_response['id']);
			$stmt->execute();
			$stmt->bind_result($userid,$vemail,$verified,$role,$timezone,$firstname,$lastname,$birthdate,$phone,$Promotions);
			if($stmt->fetch())
			{
				if ($verified == false)
				{
					$result = "Your email address has not been verified";
				} else
				{
					$_SESSION['userid'] = $userid;
					$_SESSION['ssoUser'] = 1;
					$_SESSION['email'] = $vemail;
					$_SESSION['siteRole'] = $role;
					
					$_SESSION['timezone'] = $timezone;
					$_SESSION['firstname'] = $firstname;
					$_SESSION['lastname'] = $lastname;
					$_SESSION['birthdate'] = $birthdate;
					$_SESSION['phone'] = $phone;
					$_SESSION['promotions'] = $Promotions;
					$_SESSION['profilePic'] = "/assets/img/profilepic-01.png";
					session_regenerate_id(true);
					$result = "success";
				}
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($result == "success")
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE users SET lastlogin = UNIX_TIMESTAMP() WHERE userid = ?"))
			{
				$stmt->bind_param("i",$_SESSION['userid']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}		
			
			$_SESSION['venueRights'] = array();
			GetVenueRights();
		
			$signed = false;
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT COUNT(*) FROM signatures WHERE userid = ? AND contract = ?"))
			{
				$stmt->bind_param("is",$_SESSION['userid'],$GLOBALS['LATEST_ToS']);
				$stmt->execute();
				$stmt->bind_result($c);
				if ($stmt->fetch())
				{
					if ($c > 0)
						$signed = true;
				}			
				$GLOBALS['db']->CloseConn();
			}
			
			if (!$signed)
			{
				$_SESSION['MustSignToS'] = true;
				return "tos";
			}
		}
    }
	
	return $result;
}

function GLogin($token)
{
	$result = "There was an error processing your request";
	$graph_url = "https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $token;
	$response = file_get_contents($graph_url);
	$decoded_response = json_decode($response,true);
	if (isset($decoded_response['error']))
		return "There was an error processing your request";
    else if (isset($decoded_response['id']))
	{
		$result = "This Google account has not been registered with InviteBIG";
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT userid, email, isverified, role, timezone, firstname, lastname, birthdate, phone, receive_Promotions FROM users WHERE gid = ? AND password = ''"))
		{			
			$stmt->bind_param("s", $decoded_response['id']);
			$stmt->execute();
			$stmt->bind_result($userid,$vemail,$verified,$role,$timezone,$firstname,$lastname,$birthdate,$phone,$Promotions);
			if($stmt->fetch())
			{
				if ($verified == false)
				{
					$result = "Your email address has not been verified";
				} else
				{
					$_SESSION['userid'] = $userid;
					$_SESSION['ssoUser'] = 1;
					$_SESSION['email'] = $vemail;
					$_SESSION['siteRole'] = $role;
					
					$_SESSION['timezone'] = $timezone;
					$_SESSION['firstname'] = $firstname;
					$_SESSION['lastname'] = $lastname;
					$_SESSION['birthdate'] = $birthdate;
					$_SESSION['phone'] = $phone;
					$_SESSION['promotions'] = $Promotions;
					$_SESSION['profilePic'] = "/assets/img/profilepic-01.png";
					session_regenerate_id(true);
					$result = "success";
				}
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($result == "success")
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE users SET lastlogin = UNIX_TIMESTAMP() WHERE userid = ?"))
			{
				$stmt->bind_param("i",$_SESSION['userid']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}		
			
			$_SESSION['venueRights'] = array();
			GetVenueRights();
			
			$signed = false;
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT COUNT(*) FROM signatures WHERE userid = ? AND contract = ?"))
			{
				$stmt->bind_param("is",$_SESSION['userid'],$GLOBALS['LATEST_ToS']);
				$stmt->execute();
				$stmt->bind_result($c);
				if ($stmt->fetch())
				{
					if ($c > 0)
						$signed = true;
				}			
				$GLOBALS['db']->CloseConn();
			}
			
			if (!$signed)
			{
				$_SESSION['MustSignToS'] = true;
				return "tos";
			}
		}
    }
	
	return $result;
}

function TWLogin($token)
{
	$result = "There was an error processing your request";
	$graph_url = "https://api.twitter.com/1.1/account/verify_credentials.json?oauth_access_token=" . $token;
	$response = file_get_contents($graph_url);
	$decoded_response = json_decode($response,true);
	if (isset($decoded_response['error']))
		return "There was an error processing your request";
    else if (isset($decoded_response['id']))
	{
		$result = "This Twitter account has not been registered with InviteBIG";
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT userid, email, isverified, role, timezone, firstname, lastname, birthdate, phone, receive_Promotions FROM users WHERE twid = ? AND password = ''"))
		{			
			$stmt->bind_param("s", $decoded_response['id']);
			$stmt->execute();
			$stmt->bind_result($userid,$vemail,$verified,$role,$timezone,$firstname,$lastname,$birthdate,$phone,$Promotions);
			if($stmt->fetch())
			{
				if ($verified == false)
				{
					$result = "Your email address has not been verified";
				} else
				{
					$_SESSION['userid'] = $userid;
					$_SESSION['ssoUser'] = 1;
					$_SESSION['email'] = $vemail;
					$_SESSION['siteRole'] = $role;
					
					$_SESSION['timezone'] = $timezone;
					$_SESSION['firstname'] = $firstname;
					$_SESSION['lastname'] = $lastname;
					$_SESSION['birthdate'] = $birthdate;
					$_SESSION['phone'] = $phone;
					$_SESSION['promotions'] = $Promotions;
					$_SESSION['profilePic'] = "/assets/img/profilepic-01.png";
					session_regenerate_id(true);
					$result = "success";
				}
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($result == "success")
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE users SET lastlogin = UNIX_TIMESTAMP() WHERE userid = ?"))
			{
				$stmt->bind_param("i",$_SESSION['userid']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}		
			
			$_SESSION['venueRights'] = array();
			GetVenueRights();
			
			$signed = false;
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT COUNT(*) FROM signatures WHERE userid = ? AND contract = ?"))
			{
				$stmt->bind_param("is",$_SESSION['userid'],$GLOBALS['LATEST_ToS']);
				$stmt->execute();
				$stmt->bind_result($c);
				if ($stmt->fetch())
				{
					if ($c > 0)
						$signed = true;
				}			
				$GLOBALS['db']->CloseConn();
			}
			
			if (!$signed)
			{
				$_SESSION['MustSignToS'] = true;
				return "tos";
			}
		}
    }
	
	return $result;
}

function Register($vfbid, $vgid, $vtwid, $vemail, $vpassword, $vfirstname, $vlastname, $vbirthdate, $vphone, $vrole, $vcode, $vtimezone, $vPromotions)
{	
	if (IsValidUser($vemail))
		return "The email address specified is already in use";
	if ($vemail != Sanitize($vemail))
		return "Invalid email address provided";
	if ($vfirstname != Sanitize($vfirstname))
		return "Invalid first name provided";
	if ($vlastname != Sanitize($vlastname))
		return "Invalid last name provided";
	if ($vphone != Sanitize($vphone))
		return "Invalid phone number provided";
	
	if (strlen($vfbid) > 0)
	{
		$err = false;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(*) FROM users WHERE fbid = ?"))
		{
			$stmt->bind_param("s",$vfbid);
			$stmt->execute();	
			$stmt->bind_result($c);
			if ($stmt->fetch())
			{
				if ($c > 0)
					$err = true;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($err)
			return "An account has already been registered with this Facebook account";
	}
	
	if (strlen($vgid) > 0)
	{
		$err = false;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(*) FROM users WHERE gid = ?"))
		{
			$stmt->bind_param("s",$vgid);
			$stmt->execute();	
			$stmt->bind_result($c);
			if ($stmt->fetch())
			{
				if ($c > 0)
					$err = true;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($err)
			return "An account has already been registered with this Google+ account";
	}
	
	if (strlen($vtwid) > 0)
	{
		$err = false;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(*) FROM users WHERE twid = ?"))
		{
			$stmt->bind_param("s",$vtwid);
			$stmt->execute();	
			$stmt->bind_result($c);
			if ($stmt->fetch())
			{
				if ($c > 0)
					$err = true;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($err)
			return "An account has already been registered with this Twitter account";
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("INSERT INTO users (timestamp, fbid, gid, twid, email, password, firstname, lastname, birthdate, phone, code, role, timezone, receive_promotions, isverified) VALUES (UNIX_TIMESTAMP(),?,?,?,LCASE(?),?,?,?,?,?,?,?,?,?,1)"))
	{
		$hashed = password_hash($vpassword,PASSWORD_DEFAULT,array("cost"=>10));
		if (strlen($vpassword) == 0 && (strlen($vfbid) > 0 || strlen($vgid) > 0 || strlen($vtwid) > 0))
			$hashed = "";
		$stmt->bind_param("ssssssssssisi",$vfbid,$vgid,$vtwid,$vemail,$hashed, $vfirstname, $vlastname, $vbirthdate, $vphone, $vcode, $vrole, $vtimezone, $vPromotions);
		$stmt->execute();	
		$GLOBALS['db']->CloseConn();
	}
	
	if (IsValidUser($vemail))
	{	
		if ($vrole == 2)
			SendWelcomeVenueMessage($vemail,$vfirstname);
		else if ($vrole == 0)
			SendWelcomeUserMessage($vemail,$vfirstname);
		
		return "success";
	}
	
	return "There was an error processing your registration request";
}

function AutoRegister($email,$name,$phone,$tz)
{
	$uid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT userid FROM users WHERE email = ?"))
	{
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->bind_result($u);
		if($stmt->fetch())
		{
			$uid = $u;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (!$uid)
	{
		$password = strval(bin2hex(openssl_random_pseudo_bytes(8)));
		$name = explode(' ',Sanitize($name));
		$firstname = $name[0];
		$lastname = $name[count($name)-1];
		$phone = Sanitize($phone);
		$email = Sanitize($email);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO users (timestamp, email, password, firstname, lastname, phone, role, timezone, receive_promotions, isverified) VALUES (UNIX_TIMESTAMP(),LCASE(?),?,?,?,?,0,?,1,1)"))
		{
			$hashed = password_hash($password,PASSWORD_DEFAULT,array("cost"=>10));
			$stmt->bind_param("ssssss",$email,$hashed,$firstname,$lastname,$phone,$timezone);
			$stmt->execute();
			
			if ($stmt->insert_id)
				$uid = $stmt->insert_id;
			
			$GLOBALS['db']->CloseConn();
		}
		
		if ($uid)
		{
			require_once('php/email.php');
			SendAutoRegisterEmail($email,$firstname,$password);
		}
	}
	
	return $uid;
}

function IsValidUser($vemail)
{
	$result = false;
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT COUNT(userid) FROM users WHERE email = ?"))
	{
		$stmt->bind_param("s", $vemail);
		$stmt->execute();
		$stmt->bind_result($c);
		if($stmt->fetch())
		{
			if ($c > 0)
				$result = true;
		}
		$GLOBALS['db']->CloseConn();
	}
	return $result;
}

function GetVenueRights()
{
	$_SESSION['venueRights'] = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT DISTINCT v.name,v.timezone,v.shorturl,r.venueid,r.role,s.status FROM venues_rights AS r LEFT JOIN venues AS v ON v.venueid = r.venueid LEFT JOIN venues_subscriptions AS s ON s.venueid = v.venueid WHERE r.role > 0 AND r.email = ? AND v.status != 'deleted' AND v.venueid != 2 ORDER BY v.name ASC"))
	{
		$stmt->bind_param("s", $_SESSION['email']);
		$stmt->execute();
		$stmt->bind_result($vname,$tz,$vurl,$vid,$role,$stat);
		while($stmt->fetch())
		{			
			$_SESSION['venueRights'][] = array('venueName'=>Sanitize($vname),'venueid'=>$vid,'venueTimezone'=>$tz,'role'=>$role,'profilePic'=>"","url"=>$vurl,"subscription_status"=>$stat);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($_SESSION['siteRole'] >= 2)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT DISTINCT v.name,v.timezone,v.shorturl,v.venueid,16,s.status FROM venues AS v LEFT JOIN venues_subscriptions AS s ON s.venueid = v.venueid WHERE v.venueid = 2"))
		{
			$stmt->execute();
			$stmt->bind_result($vname,$tz,$vurl,$vid,$role,$stat);
			while($stmt->fetch())
			{			
				$_SESSION['venueRights'][] = array('venueName'=>Sanitize($vname),'venueid'=>$vid,'venueTimezone'=>$tz,'role'=>$role,'profilePic'=>"","url"=>$vurl,"subscription_status"=>$stat);
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	for ($i = 0; $i < count($_SESSION['venueRights']); $i++)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT photo FROM venues_photos WHERE venueid = ? ORDER BY placement ASC LIMIT 0,1"))
		{
			$stmt->bind_param("i", $_SESSION['venueRights'][$i]['venueid']);
			$stmt->execute();
			$stmt->bind_result($p);
			while($stmt->fetch())
			{
				$_SESSION['venueRights'][$i]['profilePic'] = "/assets/content/".$p;
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	return;
}

function VerifyAccount($code)
{
	$res = "error";
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("UPDATE users SET isverified = 1 WHERE code = ?"))
	{
		$stmt->bind_param("s", $code);
		$stmt->execute();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT isverified FROM users WHERE code = ?"))
	{
		$stmt->bind_param("s", $code);
		$stmt->execute();
		$stmt->bind_result($is);
		if ($stmt->fetch())
		{
			if ($is == 1)
				$res = "success";
		}
	}
	$GLOBALS['db']->CloseConn();
	
	return $res;
}

function ResetPassword($email,$name)
{
	$uid = null;
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT userid,fbid,gid,twid FROM users WHERE email = ? AND LOWER(firstname) = LOWER(?)"))
	{
		$stmt->bind_param("ss", $email,$name);
		$stmt->execute();
		$stmt->bind_result($i,$f,$g,$t);
		if ($stmt->fetch())
		{
			$uid = $i;
			if (strlen($f) > 0)
				$uid = "fbid";
			if (strlen($g) > 0)
				$uid = "gid";
			if (strlen($t) > 0)
				$uid = "twid";
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (!$uid)
		return "We could not find an account that is registered with the email address and name combination you provided";
	else if ($uid == "fbid")
		return "The account you specified uses a Facebook login, please login using Facebook";
	else if ($uid == "gid")
		return "The account you specified uses a Google+ login, please login using Google+";
	else if ($uid == "twid")
		return "The account you specified uses a Twitter login, please login using Twitter";
	else
	{
		$pwd = substr(md5(rand()), 0, 16);
		$cpwd = password_hash($pwd,PASSWORD_DEFAULT,array("cost"=>10));
		
		$aff = 0;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE users SET password = ? WHERE userid = ?"))
		{
			$stmt->bind_param("si", $cpwd,$uid);
			$stmt->execute();
			$aff = $stmt->affected_rows;
			$GLOBALS['db']->CloseConn();
		}
		
		if ($aff > 0)
		{
			require_once('php/email.php');
			SendPasswordResetMessage($email,$pwd);
			return "success";
		}
	}
	
	return "There was an error resetting your password";
}

function UpdateUserProfile($arr)
{
	$result = "success";
	$queries = array();
	
	if (isset($arr['email']) && $arr['email'] != "")
	{
		$emailOk = false;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(*) FROM users WHERE email = ?"))
		{
			$stmt->bind_param("s",$arr['email']);
			$stmt->execute();
			$stmt->bind_result($c);
			if ($stmt->fetch())
			{
				if ($c == 0)
					$emailOk = true;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if (!$emailOk)
			return "The email address you provided is already in use";
		
		$queries[] = array('query'=>"UPDATE users SET email = ? WHERE userid = ?",
							'first'=>Sanitize($arr['email']),'second'=>'sis','third'=>$_SESSION['userid']);
	}
	if (isset($arr['firstname']) && $arr['firstname'] != "")
		$queries[] = array('query'=>"UPDATE users SET firstname = ? WHERE userid = ?",
							'first'=>Sanitize($arr['firstname']),'second'=>'si','third'=>$_SESSION['userid']);
	if (isset($arr['lastname']) && $arr['lastname'] != "")
		$queries[] = array('query'=>"UPDATE users SET lastname = ? WHERE userid = ?",
							'first'=>Sanitize($arr['lastname']),'second'=>'si','third'=>$_SESSION['userid']);
	if (isset($arr['phone']) && $arr['phone'] != "")
		$queries[] = array('query'=>"UPDATE users SET phone = ? WHERE userid = ?",
							'first'=>Sanitize($arr['phone']),'second'=>'si','third'=>$_SESSION['userid']);
	if (isset($arr['password']) && $arr['password'] != "")
	{
		$hashed = password_hash($arr['password'],PASSWORD_DEFAULT,array("cost"=>10));
		$queries[] = array('query'=>"UPDATE users SET password = ? WHERE fbid = '' AND gid = '' AND twid = '' AND userid = ?",
							'first'=>$hashed,'second'=>'si','third'=>$_SESSION['userid']);
	}
	if (isset($arr['birthdate']) && $arr['birthdate'] != "")
		$queries[] = array('query'=>"UPDATE users SET birthdate = ? WHERE userid = ?",
							'first'=>date('Y-m-d H:i:s', strtotime($arr['birthdate'])),'second'=>'si','third'=>$_SESSION['userid']);
	if (isset($arr['timezone']) && $arr['timezone'] != "")
		$queries[] = array('query'=>"UPDATE users SET timezone = ? WHERE userid = ?",
							'first'=>$arr['timezone'],'second'=>'si','third'=>$_SESSION['userid']);
	if (isset($arr['promotions']))
		$queries[] = array('query'=>"UPDATE users SET receive_Promotions = ? WHERE userid = ?",
						'first'=>$arr['promotions'],'second'=>'ii','third'=>$_SESSION['userid']);
	
	foreach ($queries as $q)
	{
		try
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare($q['query']))
			{
				$stmt->bind_param($q['second'], $q['first'], $q['third']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			else $result = "There was an error processing your request";
		} catch (Exception $e) { $result = "There was an error processing your request"; }		
	}
	
	if ($result == "success")
	{
		if (isset($arr['email']) && $arr['email'] != "")
			$_SESSION['email'] = $arr['email'];
		Login("","",true);
	}
		
	return $result;
}

function GetUserBookings($onlypending)
{
	$arr = array();
	$dnow = new DateTime();
	$now = $dnow->getTimestamp();
		
	$query = "SELECT b.bookingid,b.start,b.stop,b.headcount,b.total_cost,v.currency,b.status,v.name,v.timezone FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE b.userid = ?";
	if ($onlypending == 2)
		$query .= " AND (b.status = 'Pending Deposit' OR b.status = 'Pending Approval' OR b.status = 'Pending Payment')";
	$query .= " ORDER BY b.start DESC";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare($query))
	{		
		$stmt->bind_param("i",$_SESSION['userid']);
		$stmt->execute();
		$stmt->bind_result($bid,$start,$stop,$hc,$tc,$cur,$st,$venue,$tz);
		while ($stmt->fetch())
		{
			$arr[] = array("id"=>$bid,"start"=>$start,"stop"=>$stop,"headcount"=>$hc,"total"=>$tc,"currency"=>$cur,"status"=>$st,"venue"=>Sanitize($venue),"isnew"=>false,"timezone"=>$tz);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	for ($i = 0; $i < count($arr); $i++)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT viewed_by_user FROM booking_messages WHERE bookingid = ?"))
		{		
			$stmt->bind_param("i",$arr[$i]['id']);
			$stmt->execute();
			$stmt->bind_result($v);
			while ($stmt->fetch())
			{
				if (!$v)
					$arr[$i]['isnew'] = true;
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	return $arr;
}

function GetMyMessages()
{
	$arr = array();
	
	/*$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT m.bookingid, MAX(isvenue), v.name, MAX(m.timestamp), i.name, b.start, v.timezone FROM booking_messages AS m LEFT JOIN booking AS b ON b.bookingid = m.bookingid LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE m.viewed_by_user = 0 AND b.userid = ? GROUP BY m.bookingid, v.name, i.name, b.start, v.timezone"))
	{	
		$stmt->bind_param("i",$_SESSION['userid']);
		$stmt->execute();
		$stmt->bind_result($b,$is,$vname,$time,$name,$start,$tz);
		while ($stmt->fetch())
		{
			$sender = "Me";
			switch ($is)
			{
				case 1:
					$sender = $vname;
					break;
				case 2:
					$sender = "InviteBIG";
					break;
			}
			
			if (strlen($name) < 5)
				$name = "Booking";
			
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			$t = $d->format("M d, Y");
				
			$arr[] = array("bookingid"=>$b,"from"=>$sender,"time"=>$time,"subject"=>$name." @ ".$vname." - ".$t,"isnew"=>1);
		}
		$GLOBALS['db']->CloseConn();
	}*/
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT m.convoid, v.name, title, MAX(newforu), MAX(d.timestamp) FROM messages AS m LEFT JOIN messages_detail AS d ON d.convoid = m.convoid LEFT JOIN venues AS v ON v.venueid = m.venueid WHERE m.userid = ? AND m.deletedu != 1 GROUP BY m.convoid,v.name,title"))
	{	
		$stmt->bind_param("i",$_SESSION['userid']);
		$stmt->execute();
		$stmt->bind_result($c,$v,$t,$n,$time);
		while ($stmt->fetch())
		{			
			$arr[] = array("convoid"=>$c,"from"=>$v,"time"=>$time,"subject"=>Sanitize($t),"isnew"=>$n);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$tmp = array();
	foreach ($arr as &$a)
		$tmp[] = &$a['time'];
	array_multisort($tmp,SORT_DESC,$arr);
	
	return $arr;
}

function GetUserUpcomingEvents($date)
{
	$arr = array();
	$tz = "America/Los_Angeles";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT timezone FROM users WHERE userid = ?"))
	{	
		$stmt->bind_param("i",$_SESSION['userid']);
		$stmt->execute();
		$stmt->bind_result($t);
		if ($stmt->fetch())
		{
			$tz = $t;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$d = new DateTime($date,new DateTimeZone($tz));
	$d->setTime(0,0,0);
	$d2 = new DateTime($date,new DateTimeZone($tz));
	$d2->setTime(23,59,59);
	if (!$date)
		$d2 = new DateTime("2037-01-01");
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid, i.name, b.start, b.stop, b.total_cost, v.currency FROM booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE b.userid = ? AND b.start <= ? AND stop >= ? AND (b.status = 'Paid' OR b.status = 'Past Due' OR b.status = 'Pending Payment' OR b.status = 'Imported') ORDER BY start ASC LIMIT 0,20"))
	{	
		$s1 = $d2->getTimestamp();
		$s2 = $d->getTimestamp();
		
		$stmt->bind_param("iii",$_SESSION['userid'],$s1,$s2);
		$stmt->execute();
		$stmt->bind_result($i,$n,$start,$stop,$cost,$cur);
		while ($stmt->fetch())
		{
			$st = new DateTime(null,new DateTimeZone($tz));
			$st->setTimestamp($start);
			
			if (strlen($n) < 1)
				$n = "Booking @ ".$st->format("M d, Y");
				
			$arr[] = array("id"=>$i,"name"=>Sanitize($n),"start"=>$start,"stop"=>$stop,"cost"=>$cost,"currency"=>$cur);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function SignContract($contract)
{
	$result = false;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("INSERT INTO signatures (userid,contract,timestamp) VALUES (?,?,UNIX_TIMESTAMP())"))
	{	
		$stmt->bind_param("is",$_SESSION['userid'],$contract);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
		$result = true;
	}
	
	return $result;
}

?>