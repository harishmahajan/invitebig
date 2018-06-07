<?php

function SaveVenue(&$venue)
{
	$result = null;
	
	$venue['city'] = ucwords(strtolower($venue['city']));
	$venue['state'] = strtoupper($venue['state']);
	$type = "";
	
	if (!isset($venue['id']) || $venue['id'] == '')
	{
		if ($_SESSION['siteRole'] < 2)
			return "You are not authorized to create a new venue";
		
		$toomany = false;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(*) FROM venues WHERE userid = ?"))
		{
			$stmt->bind_param("i",$_SESSION['userid']);
			$stmt->execute();
			$stmt->bind_result($c);
			if ($stmt->fetch())
			{
				if ($c >= 10 && $_SESSION['siteRole'] != 999)
					$toomany = true;
			}
			$GLOBALS['db']->CloseConn();
		}
		if ($toomany)
			return "You have already created as many venues as you are allowed to create, please contact support if you need assistance or if you would like to create more";
		
		$venue['id'] = null;
		
		$cus = "";
		$sub = "";
		$renews = 0;
		$sta = "trialing";
		$plan = "standard-venue-monthly";
		$email = $_SESSION['email'];
		$token = null;
		$customer = null;
		
		if (isset($venue['subscription']))
		{
			if (isset($venue['subscription']['token']))
				$token = $venue['subscription']['token'];
			if (isset($venue['subscription']['plan']))
				$plan = $venue['subscription']['plan'];
			if (isset($venue['subscription']['email']))
				$email = $venue['subscription']['email'];
		}
		
		try {
			require_once('php/stripe-php-2.3.0/init.php');
			\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
			
			$cust = array(
				"description"=>"venue: ".$venue['name'] . " (pending update)",
				"email"=>$email,
				"plan"=>$plan,
				"tax_percent"=>($venue['state'] == "WA" ? 9.5 : null));
			if ($token)
				$cust['source'] = $token;
				
			$customer = \Stripe\Customer::create($cust);
			
			$cus = $customer->id;
			
			for ($i=0; $i<count($customer['subscriptions']['data']); $i++)
			{
				if ($customer['subscriptions']['data'][$i]['plan']['id'] == $plan)
				{
					$sta = $customer['subscriptions']['data'][$i]['status'];
					$sub = $customer['subscriptions']['data'][$i]['id'];
					$renews = $customer['subscriptions']['data'][$i]['current_period_end'];
					break;
				}
			}
			
		} catch (Exception $e) {
			if ($e instanceof \Stripe\Error\Card) 
			{
				$body = $e->getJsonBody();
				$err  = $body['error']['message'];
				return $err;
			}
			else return "There was an error processing your credit card";
		}
			
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO venues (timestamp,userid,shorturl,salestax,currency,bookingfee,processingfee,status) VALUES (UNIX_TIMESTAMP(),?,?,0,'USD',0,0.049,'new')"))
		{
			$short = CreateShortURL($venue['name'].'-'.$venue['city']);
			$stmt->bind_param("is",$_SESSION['userid'], $short);
			$stmt->execute();
			if ($stmt->affected_rows)
				$venue['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
		
		if ($venue['id'] >= 0)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO guides (venueid,name,status,timestamp) VALUES (?,'venueOnboarding','[]',UNIX_TIMESTAMP())"))
			{
				$stmt->bind_param("i", $venue['id']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO venues_rights (venueid,email,grantedby,timestamp,role,receiveEmails) VALUES (?,?,?,UNIX_TIMESTAMP(),16,1) ON DUPLICATE KEY UPDATE role = 16"))
			{
				$stmt->bind_param("isi", $venue['id'], $_SESSION['email'], $_SESSION['userid']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO venues_subscriptions (venueid,customerid,subid,plan,renews,status) VALUES (?,?,?,?,?,?)"))
			{
				$stmt->bind_param("isssis", $venue['id'],$cus,$sub,$plan,$renews,$sta);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			if ($customer)
			{
				$customer->description = "venueid: ".$venue['id'];
				$customer->save();
			}
		}
		else if ($customer) $customer->delete();

		if ($venue['id'] >= 0)
		{
			require_once("php/user.php");
			Login("","",true);
		}
	}
	
	if ($venue['id'] == 2 && $_SESSION['siteRole'] != 999)
		return "This demo venue does not allow configuration changes to be made. You may view and alter the configuration for your own reference, but may not save any changes you make.";
	
	if ($venue['id'] >= 0)
	{
		$auth = false;
		foreach ($_SESSION['venueRights'] as $v)
			if ($v['venueid'] == $venue['id']) $auth = $v['role'];
		if ($_SESSION['siteRole'] == 999)
			$auth = 16;
			
		if ($auth & 16)
		{			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT CONCAT(t.name,'/',v.shorturl) FROM venues_types AS t JOIN venues AS v WHERE t.typeid = ? AND v.venueid = ?"))
			{
				$stmt->bind_param("ii",$venue['type'],$venue['id']);
				$stmt->execute();
				$stmt->bind_result($t);
				if ($stmt->fetch())
				{
					$type = $t;
				}
				
				$GLOBALS['db']->CloseConn();
			}
			
			$venue['name'] = Sanitize($venue['name']);
			$venue['description'] = Sanitize($venue['description']);
			$venue['banner'] = Sanitize($venue['banner']);
			$venue['website'] = Sanitize($venue['website']);
			$venue['facebook'] = Sanitize($venue['facebook']);
			$venue['twitter'] = Sanitize($venue['twitter']);
			$venue['business'] = Sanitize($venue['business']);
			$venue['ein'] = Sanitize($venue['ein']);
			$venue['address'] = Sanitize($venue['address']);
			$venue['zip'] = Sanitize($venue['zip']);
			$venue['country'] = Sanitize($venue['country']);
			$venue['city'] = Sanitize($venue['city']);
			$venue['state'] = Sanitize($venue['state']);
			$venue['phone'] = Sanitize($venue['phone']);
			
			$origCur = null;
			$bookingCount = null;
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT currency, (SELECT COUNT(*) FROM booking WHERE venueid=?) FROM venues WHERE venueid=?"))
			{
				$stmt->bind_param("ii",$venue['id'],$venue['id']);
				$stmt->execute();
				$stmt->bind_result($cur,$c);
				if ($stmt->fetch())
				{
					$origCur = $cur;
					$bookingCount = $c;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			if ($venue['currency'] != $origCur && $bookingCount > 0)
				return "You cannot change the venue's currency after more than one booking has been made. Contact support if you need assistance.";
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE venues SET currency=? WHERE venueid = ? AND NOT EXISTS (SELECT venueid FROM booking WHERE venueid = ?)"))
			{
				$stmt->bind_param("sii",$venue['currency'],$venue['id'],$venue['id']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE venues SET mod_timestamp = UNIX_TIMESTAMP(), venue_typeid = ?, venue_styleid = ?,
				name = ?, description = ?, banner = ?, salestax = ?, visibility = ?, website = ?, facebook = ?, twitter = ?, business_name = ?, ein = ?, address = ?, zip = ?, country = ?, city = ?,
				state = ?, latitude = ?, longitude = ?, phone = ?, timezone = ?, contract = ? WHERE venueid = ?"))
			{
				$stmt->bind_param("iisssdssssssssssssssssi",$venue['type'],$venue['style'],$venue['name'],$venue['description'],$venue['banner'],$venue['salesTax'],$venue['visibility'],
					$venue['website'],$venue['facebook'],$venue['twitter'],$venue['business'],$venue['ein'],$venue['address'],$venue['zip'],$venue['country'],
					$venue['city'],$venue['state'],$venue['latitude'],$venue['longitude'],$venue['phone'],$venue['timezone'],$venue['contract'],$venue['id']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("DELETE FROM venues_features WHERE venueid = ?"))
			{
				$stmt->bind_param("i",$venue['id']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			foreach ($venue['features'] as $f)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO venues_features (venueid,featureid) VALUES (?,?)"))
				{
					$stmt->bind_param("ii",$venue['id'],$f);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("DELETE FROM venues_functionality WHERE venueid = ?"))
			{
				$stmt->bind_param("i",$venue['id']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO venues_functionality (venueid,showMenus,showPersonnel,showQuestions,showPromos,publicFileUploads,gratuity,entireVenue) VALUES (?,?,?,?,?,?,?,?)"))
			{
				$stmt->bind_param("iiiiiiii",$venue['id'],$venue['functionality']['menus'],$venue['functionality']['personnel'],$venue['functionality']['questions'],$venue['functionality']['promos'],$venue['functionality']['publicFileUploads'],$venue['functionality']['gratuity'],$venue['functionality']['entireVenue']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("DELETE FROM venues_contacts WHERE venueid = ?"))
			{
				$stmt->bind_param("i",$venue['id']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			foreach ($venue['contacts'] as $c)
			{
				$c['name'] = Sanitize($c['name']);
				$c['title'] = Sanitize($c['title']);
				$c['phone'] = Sanitize($c['phone']);
				$c['email'] = Sanitize($c['email']);
				$c['comments'] = Sanitize($c['comments']);
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO venues_contacts (venueid,name,title,phone,email,comments) VALUES (?,?,?,?,?,?)"))
				{
					$stmt->bind_param("isssss",$venue['id'],$c['name'],$c['title'],$c['phone'],$c['email'],$c['comments']);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
			
			// remove rights for any users that are no longer in the list
			$list = "";
			foreach ($venue['rights'] as $r)
			{
				$list .= "'" . preg_replace("/[';]/","",$r['name']) . "',";
			}
			$list = rtrim($list,',');
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("DELETE FROM venues_rights WHERE venueid = ? AND email NOT IN (" . $list . ")"))
			{
				$stmt->bind_param("i",$venue['id']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$oemail = "_none_".substr(md5(rand()), 0, 16);
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT u.email,r.role FROM venues AS v LEFT JOIN users AS u ON u.userid = v.userid LEFT JOIN venues_rights AS r ON r.email = u.email AND r.venueid = v.venueid WHERE v.venueid = ?"))
			{
				$stmt->bind_param("i", $venue['id']);
				$stmt->execute();
				$stmt->bind_result($email,$role);
				if($stmt->fetch())
				{
					$oemail = $email;
				}
				$GLOBALS['db']->CloseConn();
			}
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO venues_rights (venueid,email,grantedby,timestamp,role,receiveEmails) VALUES (?,?,NULL,UNIX_TIMESTAMP(),16,1) ON DUPLICATE KEY UPDATE role = 16, receiveEmails = receiveEmails"))
			{
				$stmt->bind_param("is",$venue['id'],$oemail);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			foreach ($venue['rights'] as $r)
			{				
				$arr = explode(",",$r['roles']);
				$role = 0;
				foreach ($arr as $i)
					$role += pow(2,$i);
				
				$r['name'] = Sanitize($r['name']);
				
				if ($r['name'] == $oemail && !($role & 16))
					$role = $role + 16;
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO venues_rights (venueid,email,grantedby,timestamp,role,receiveEmails) VALUES (?,?,?,UNIX_TIMESTAMP(),?,?) ON DUPLICATE KEY UPDATE role = ?, receiveEmails = ?"))
				{
					$stmt->bind_param("isiiiii",$venue['id'],$r['name'],$_SESSION['userid'],$role,$r['receiveEmails'],$role,$r['receiveEmails']);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("DELETE FROM venues_photos WHERE venueid = ?"))
			{
				$stmt->bind_param("i",$venue['id']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			foreach ($venue['pictures'] as $p)
			{
				$p['caption'] = Sanitize($p['caption']);
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO venues_photos (venueid,placement,photo,caption) VALUES (?,?,?,?)"))
				{
					$stmt->bind_param("iiss",$venue['id'],$p['placement'],$p['url'],$p['caption']);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
			
			$result = "success";
		}
		else $result = "You are not authorized to modify this venue";
	}
	
	return $result;
}

function SaveDepositPolicy($vid, &$policy)
{
	if (!isset($policy['id']) || $policy['id'] == '')
	{
		$policy['id'] = null;
		$policy['name'] = Sanitize($policy['name']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO deposit_policies (venueid,name,deposit_dollar_thresh,deposit_perc,deposit_amount,full_due) values (?,?,?,?,?,?)"))
		{
			$stmt->bind_param("isdddi",$vid,$policy['name'],$policy['threshold'],$policy['perc'],$policy['amount'],$policy['full']);
			$stmt->execute();
			if ($stmt->affected_rows)
				$policy['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
	}
	
	if (isset($policy['id']) && $policy['id'] >= 0)
	{
		$policy['name'] = Sanitize($policy['name']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE deposit_policies SET name = ?, deposit_dollar_thresh = ?, deposit_perc = ?, deposit_amount = ?, full_due = ? WHERE policyid = ?"))
		{
			$stmt->bind_param("siddii",$policy['name'],$policy['threshold'],$policy['perc'],$policy['amount'],$policy['full'],$policy['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
	
	return $policy['id'];
}

function SaveRefundPolicy($vid, &$policy)
{
	if (!isset($policy['id']) || $policy['id'] == '')
	{
		$policy['id'] = null;
		$policy['name'] = Sanitize($policy['name']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO refund_policies (venueid,name,policy) values (?,?,?)"))
		{
			$stmt->bind_param("iss",$vid,$policy['name'],$policy['policy']);
			$stmt->execute();
			if ($stmt->affected_rows)
				$policy['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
	}
	
	if (isset($policy['id']) && $policy['id'] >= 0)
	{
		$policy['name'] = Sanitize($policy['name']);
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE refund_policies SET name = ?, policy = ? WHERE policyid = ?"))
		{
			$stmt->bind_param("ssi",$policy['name'],$policy['policy'],$policy['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
	
	return $policy['id'];
}

function SavePromo($vid, &$promo, $tz)
{
	if (!isset($promo['id']) || $promo['id'] == '')
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO promos (venueid) VALUES (?)"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			if ($stmt->affected_rows > 0)
				$promo['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
	}
	if (isset($promo['id']) && $promo['id'] >= 0 && $promo['id'] != '')
	{
		$promo['name'] = Sanitize($promo['name']);
		$promo['description'] = Sanitize($promo['description']);
		$promo['status'] = Sanitize($promo['status']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE promos SET lastmodified = CURRENT_TIMESTAMP(),name=?,description=?,dollar_threshold=?,dollar_discount=?,percentage_discount=?,reuses=?,quantity=?,applic=?,entireinvoice=?,combinable=?,auto=?,starttime=?,stoptime=?,expires=?,status=? WHERE promoid = ?"))
		{
			$thresh = 0;
			$doldisc = null;
			$perdisc = null;
			$start = null;
			$stop = null;
			
			if ($promo['discountthreshold'] > 0)
				$thresh = $promo['discountthreshold'];
			if ($promo['discounttype'] == 'percent')
				$perdisc = $promo['discountamount'];
			else $doldisc = $promo['discountamount'];
			
			if ($promo['start'])
			{
				$d = new DateTime("@".$promo['start'], new DateTimeZone($tz));
				$start = $d->getTimestamp();
			}
			if ($promo['stop'])
			{
				$d = new DateTime("@".$promo['stop'], new DateTimeZone($tz));
				$stop = $d->getTimestamp();
			}
			
			$stmt->bind_param("ssdddiiiiiiiiisi",$promo['name'],$promo['description'],$thresh,
				$doldisc,$perdisc,$promo['peruser'],$promo['quantity'],$promo['applic'],
				$promo['entireinvoice'],$promo['combinable'],$promo['auto'],$start,$stop,$promo['expires'],
				$promo['status'],$promo['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		SetPromoHours($promo);
		SetPromoResources($promo);
	}
	
	return $promo['id'];
}

function SetPromoResources($promo)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM promos_resources WHERE promoid = ?"))
	{
		$stmt->bind_param("i",$promo['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($promo['resources'] as $r)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO promos_resources (promoid,resourceid) VALUES (?,?)"))
		{
			$stmt->bind_param("ii",$promo['id'],$r);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SaveAddon($vid, &$addon, $tz)
{
	if (!isset($addon['id']) || $addon['id'] == '')
	{
		$addon['id'] = null;
		$addon['name'] = Sanitize($addon['name']);
		$addon['description'] = Sanitize($addon['description']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO addons (venueid,typeid,name,description,min,max,deliverable,price,status) values (?,?,?,?,?,?,?,?,'new')"))
		{
			$stmt->bind_param("iiissiid",$vid,$addon['type'],$addon['name'],$addon['description'],$addon['minimum'],$addon['maximum'],$addon['deliverable'],$addon['price']);
			$stmt->execute();
			if ($stmt->affected_rows)
				$addon['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
	}
	
	if (isset($addon['id']) && $addon['id'] >= 0)
	{
		$addon['name'] = Sanitize($addon['name']);
		$addon['description'] = Sanitize($addon['description']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE addons SET min = ?, max = ?, deliverable = ?, price = ?, name = ?, description = ?, typeid = ? WHERE addonid = ?"))
		{
			$stmt->bind_param("iiidssii",$addon['minimum'],$addon['maximum'],$addon['deliverable'],$addon['price'],$addon['name'],$addon['description'],$addon['type'],$addon['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("DELETE FROM addons_photos WHERE addonid = ?"))
		{
			$stmt->bind_param("i",$addon['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($addon['pictures'] as $p)
		{
			if (!$p['url'])
				continue;
				
			$p['caption'] = Sanitize($p['caption']);
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO addons_photos (addonid,placement,photo,caption) VALUES (?,?,?,?)"))
			{
				$stmt->bind_param("iiss",$addon['id'],$p['placement'],$p['url'],$p['caption']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		
		SetAddonPolicies($vid, $addon);
		SetAddonHours($addon, $tz);
	}
	
	return $addon['id'];
}

function SaveResource($vid, &$resource, $tz)
{
	if (isset($resource['over21']) && $resource['over21'] == "true")
		$resource['over21'] = 1;
	else $resource['over21'] = 0;
	
	if (isset($resource['autoapprove']) && $resource['autoapprove'] == "true")
		$resource['autoapprove'] = 1;
	else $resource['autoapprove'] = 0;
	
	if (isset($resource['linked']) && $resource['linked'] == "true")
		$resource['linked'] = 1;
	else $resource['linked'] = 0;
	
	if (!isset($resource['id']) || $resource['id'] == '')
	{				
		// insert only the things that can't be edited if resource is in use
		$resource['id'] = null;
		$resource['name'] = Sanitize($resource['name']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO resources (venueid,name,status) values (?,?,'new')"))
		{
			$stmt->bind_param("is",$vid,$resource['name']);
			$stmt->execute();
			if ($stmt->affected_rows)
				$resource['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
	}
	
	if (isset($resource['id']) && $resource['id'] > 0)
	{
		$resource['name'] = Sanitize($resource['name']);
		$resource['description'] = Sanitize($resource['description']);
		
		// update all info that is ok to be edited even if resource is in use
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE resources SET typeid = ?,name = ?,autoapprove = ?,over21_req = ?,capacity = ?,seats = ?,timeslots=?,minduration = ?,increment = ?,min_lead_time = ?,cleanupcost = ?,cleanuptime = ?, description = ?,default_rate = ? WHERE resourceid = ?"))
		{
			$stmt->bind_param("isiiiiiiiiiisdi",$resource['type'],$resource['name'],
				$resource['autoapprove'],$resource['over21'],
				$resource['capacity'],$resource['seats'],$resource['timeslots'],$resource['duration'],$resource['increment'],$resource['lead'],$resource['cleanupcost'],
				$resource['cleanup'],$resource['description'],$resource['rate'],$resource['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("DELETE FROM resources_photos WHERE resourceid = ?"))
		{
			$stmt->bind_param("i",$resource['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		if (count($resource['pictures']) > 0)
		{
			foreach ($resource['pictures'] as $p)
			{
				$p['caption'] = Sanitize($p['caption']);
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO resources_photos (resourceid,placement,photo,caption) VALUES (?,?,?,?)"))
				{
					$stmt->bind_param("iiss",$resource['id'],$p['placement'],$p['url'],$p['caption']);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
		}
		
		SetResourcePolicies($vid, $resource);
		SetResourceHours($resource);
		SetResourceRates($resource);
		SetResourceSlots($resource);
		SetResourceAddons($vid, $resource);
	}
	
	foreach ($resource['children'] as &$r)
		SaveResource($vid, $r, $tz);
		
	return $resource['id'];
}

function SaveMenu($vid, &$menu, $tz)
{
	if (!isset($menu['id']) || $menu['id'] == '')
	{
		$menu['id'] = null;
		$menu['name'] = Sanitize($menu['name']);
		$menu['description'] = Sanitize($menu['description']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO menus (venueid,name,description,status) values (?,?,?,'new')"))
		{
			$stmt->bind_param("iss",$vid,$menu['name'],$menu['description']);
			$stmt->execute();
			if ($stmt->affected_rows)
				$menu['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
	}
	
	if (isset($menu['id']) && $menu['id'] >= 0)
	{
		$menu['name'] = Sanitize($menu['name']);
		$menu['description'] = Sanitize($menu['description']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE menus SET name = ?, description = ? WHERE menuid = ?"))
		{
			$stmt->bind_param("ssi",$menu['name'],$menu['description'],$menu['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		SetMenuHours($menu,$tz);
		SetMenuPolicies($vid,$menu);
	}
	
	return $menu['id'];
}

function SaveItem($vid, &$item)
{
	if (!isset($item['id']) || $item['id'] == '')
	{
		$item['id'] = null;
		$item['name'] = Sanitize($item['name']);
		$item['description'] = Sanitize($item['description']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO menus_items (venueid,name,type,price,min,max,description,photo,caption,status) VALUES (?,?,?,?,?,?,?,?,?,'new')"))
		{
			$stmt->bind_param("isidiisss",$vid,$item['name'],$item['type'],$item['price'],$item['min'],$item['max'],$item['description'],$item['pictures'][0]['url'],$item['pictures'][0]['caption']);
			$stmt->execute();
			if ($stmt->affected_rows)
				$item['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
		
		if ($item['id'])
		{
			$mid = null;
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT menuid FROM menus WHERE venueid = ? AND name = ? ORDER BY menuid DESC"))
			{
				$stmt->bind_param("is", $vid,$item['menu']);
				$stmt->execute();
				$stmt->bind_result($mid);
				$stmt->fetch();
				$GLOBALS['db']->CloseConn();
			}
			if ($mid)
			{
	
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("UPDATE menus_items SET menuid = ? WHERE itemid = ?"))
				{
					$stmt->bind_param("ii", $mid,$item['id']);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
		}
	}
	
	if (isset($item['id']) && $item['id'] >= 0)
	{
		$item['name'] = Sanitize($item['name']);
		$item['description'] = Sanitize($item['description']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE menus_items SET name = ?, type = ?, price = ?, min = ?, max = ?, description = ?, photo = ?, caption = ? WHERE itemid = ?"))
		{
			$stmt->bind_param("sidiisssi",$item['name'],$item['type'],$item['price'],$item['min'],$item['max'],$item['description'],$item['pictures'][0]['url'],$item['pictures'][0]['caption'],$item['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
	
	return $item['id'];
}

function SavePersonnel($vid, &$personnel, $tz)
{
	if (!isset($personnel['id']) || $personnel['id'] == '')
	{
		$personnel['name'] = Sanitize($personnel['name']);
		$personnel['description'] = Sanitize($personnel['description']);
		
		$personnel['id'] = null;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO personnel (venueid,name,description,price,min,max,req,status) values (?,?,?,?,?,?,?,'new')"))
		{
			$stmt->bind_param("issdiii",$vid,$personnel['name'],$personnel['description'],$personnel['price'],$personnel['min'],$personnel['max'],$personnel['req']);
			$stmt->execute();
			if ($stmt->affected_rows)
				$personnel['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
	}
	
	if (isset($personnel['id']) && $personnel['id'] >= 0)
	{
		$personnel['name'] = Sanitize($personnel['name']);
		$personnel['description'] = Sanitize($personnel['description']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE personnel SET name = ?, description = ?, price = ?, min = ?, max = ?, req = ? WHERE personnelid = ?"))
		{
			$stmt->bind_param("ssdiiii",$personnel['name'],$personnel['description'],$personnel['price'],$personnel['min'],$personnel['max'],$personnel['req'],$personnel['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		SetPersonnelHours($personnel,$tz);
		SetPersonnelResources($personnel);
		SetPersonnelPolicies($vid,$personnel);
	}
	
	return $personnel['id'];
}

function SaveQuestion($vid, &$question)
{
	if (!isset($question['id']) || substr($question['id'],0,1) == '_')
	{
		$question['text'] = Sanitize($question['text']);
		
		$question['id'] = null;
		$stmt = $GLOBALS['db']->stmt_init();		
		if ($stmt->prepare("INSERT INTO questions (venueid,deleted,question,type,req) values (?,0,?,?,?)"))
		{	
			$req = ($question['req'] == "yes" ? 1 : 0);
			$stmt->bind_param("issi",$vid,$question['text'],$question['type'],$req);
			$stmt->execute();
			if ($stmt->affected_rows)
				$question['id'] = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
	
		if (isset($question['id']) && $question['id'] >= 0)
		{
			for ($i = 0; $i < count($question['choices']); $i++)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO questions_choices (questionid,choice) values (?,?)"))
				{	
					$stmt->bind_param("is",$question['id'],$question['choices'][$i]);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
			
			for ($i = 0; $i < count($question['appliesto']['addons']); $i++)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO questions_addons (questionid,addonid) values (?,?)"))
				{	
					$stmt->bind_param("ii",$question['id'],$question['appliesto']['addons'][$i]);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
			for ($i = 0; $i < count($question['appliesto']['menus']); $i++)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO questions_menus (questionid,menuid) values (?,?)"))
				{	
					$stmt->bind_param("ii",$question['id'],$question['appliesto']['menus'][$i]);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
			for ($i = 0; $i < count($question['appliesto']['personnel']); $i++)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO questions_personnel (questionid,personnelid) values (?,?)"))
				{	
					$stmt->bind_param("ii",$question['id'],$question['appliesto']['personnel'][$i]);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
			for ($i = 0; $i < count($question['appliesto']['resources']); $i++)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO questions_resources (questionid,resourceid) values (?,?)"))
				{	
					$stmt->bind_param("ii",$question['id'],$question['appliesto']['resources'][$i]);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
		}
	}
	else if (isset($question['id']) && $question['id'] >= 0)
	{
		$req = ($question['req'] == "yes" ? 1 : 0);
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE questions SET req = ? WHERE questionid = ?"))
		{	
			$stmt->bind_param("ii",$question['id'],$req);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("DELETE FROM questions_addons WHERE questionid = ?"))
		{	
			$stmt->bind_param("i",$question['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("DELETE FROM questions_menus WHERE questionid = ?"))
		{	
			$stmt->bind_param("i",$question['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("DELETE FROM questions_personnel WHERE questionid = ?"))
		{	
			$stmt->bind_param("i",$question['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("DELETE FROM questions_resources WHERE questionid = ?"))
		{	
			$stmt->bind_param("i",$question['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		for ($i = 0; $i < count($question['appliesto']['addons']); $i++)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO questions_addons (questionid,addonid) values (?,?)"))
			{	
				$stmt->bind_param("ii",$question['id'],$question['appliesto']['addons'][$i]);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		for ($i = 0; $i < count($question['appliesto']['menus']); $i++)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO questions_menus (questionid,menuid) values (?,?)"))
			{	
				$stmt->bind_param("ii",$question['id'],$question['appliesto']['menus'][$i]);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		for ($i = 0; $i < count($question['appliesto']['personnel']); $i++)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO questions_personnel (questionid,personnelid) values (?,?)"))
			{	
				$stmt->bind_param("ii",$question['id'],$question['appliesto']['personnel'][$i]);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		for ($i = 0; $i < count($question['appliesto']['resources']); $i++)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO questions_resources (questionid,resourceid) values (?,?)"))
			{	
				$stmt->bind_param("ii",$question['id'],$question['appliesto']['resources'][$i]);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
	}
	
	return $question['id'];
}

function SetAddonPolicies($vid, $addon)
{
	$pid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid FROM deposit_policies WHERE venueid = ? AND name = ?"))
	{
		$stmt->bind_param("is",$vid,$addon['deposit']);
		$stmt->execute();
		$stmt->bind_result($p);
		if ($stmt->fetch())
			$pid = $p;
		$GLOBALS['db']->CloseConn();
	}	
	if ($pid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE addons SET deposit_policyid = ? WHERE addonid = ?"))
		{
			$stmt->bind_param("ii", $pid,$addon['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
	
	$pid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid FROM refund_policies WHERE venueid = ? AND name = ?"))
	{
		$stmt->bind_param("is",$vid,$addon['refund']);
		$stmt->execute();
		$stmt->bind_result($p);
		if ($stmt->fetch())
			$pid = $p;
		$GLOBALS['db']->CloseConn();
	}
	if ($pid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE addons SET refund_policyid = ? WHERE addonid = ?"))
		{
			$stmt->bind_param("ii", $pid,$addon['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SetResourcePolicies($vid, $resource)
{
	$pid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid FROM deposit_policies WHERE venueid = ? AND name = ?"))
	{
		$stmt->bind_param("is",$vid,$resource['deposit']);
		$stmt->execute();
		$stmt->bind_result($p);
		if ($stmt->fetch())
			$pid = $p;
		$GLOBALS['db']->CloseConn();
	}	
	if ($pid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE resources SET deposit_policyid = ? WHERE resourceid = ?"))
		{
			$stmt->bind_param("ii", $pid,$resource['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
	
	$pid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid FROM refund_policies WHERE venueid = ? AND name = ?"))
	{
		$stmt->bind_param("is",$vid,$resource['refund']);
		$stmt->execute();
		$stmt->bind_result($p);
		if ($stmt->fetch())
			$pid = $p;
		$GLOBALS['db']->CloseConn();
	}	
	if ($pid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE resources SET refund_policyid = ? WHERE resourceid = ?"))
		{
			$stmt->bind_param("ii", $pid,$resource['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SetMenuPolicies($vid, $menu)
{
	$pid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid FROM deposit_policies WHERE venueid = ? AND name = ?"))
	{
		$stmt->bind_param("is",$vid,$menu['deposit']);
		$stmt->execute();
		$stmt->bind_result($p);
		if ($stmt->fetch())
			$pid = $p;
		$GLOBALS['db']->CloseConn();
	}
	if ($pid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE menus SET deposit_policyid = ? WHERE menuid = ?"))
		{
			$stmt->bind_param("ii", $pid,$menu['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
	
	$pid = null;
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid FROM refund_policies WHERE venueid = ? AND name = ?"))
	{
		$stmt->bind_param("is",$vid,$menu['refund']);
		$stmt->execute();
		$stmt->bind_result($p);
		if ($stmt->fetch())
			$pid = $p;
		$GLOBALS['db']->CloseConn();
	}
	if ($pid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE menus SET refund_policyid = ? WHERE menuid = ?"))
		{
			$stmt->bind_param("ii", $pid,$menu['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SetPersonnelPolicies($vid, $personnel)
{
	$pid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid FROM deposit_policies WHERE venueid = ? AND name = ?"))
	{
		$stmt->bind_param("is",$vid,$personnel['deposit']);
		$stmt->execute();
		$stmt->bind_result($p);
		if ($stmt->fetch())
			$pid = $p;
		$GLOBALS['db']->CloseConn();
	}
	if ($pid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE personnel SET deposit_policyid = ? WHERE personnelid = ?"))
		{
			$stmt->bind_param("ii", $pid,$personnel['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
	
	$pid = null;
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid FROM refund_policies WHERE venueid = ? AND name = ?"))
	{
		$stmt->bind_param("is",$vid,$personnel['refund']);
		$stmt->execute();
		$stmt->bind_result($p);
		if ($stmt->fetch())
			$pid = $p;
		$GLOBALS['db']->CloseConn();
	}
	if ($pid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE personnel SET refund_policyid = ? WHERE personnelid = ?"))
		{
			$stmt->bind_param("ii", $pid,$personnel['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SetPromoHours($promo)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM promos_hours WHERE promoid = ?"))
	{
		$stmt->bind_param("i",$promo['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($promo['hours']) > 0)
	{
		foreach ($promo['hours'] as $h)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO promos_hours (promoid,start,stop) VALUES (?,?,?)"))
			{
				$stmt->bind_param("iii",$promo['id'],$h['start'],$h['stop']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
	}
}

function SetAddonHours($addon)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM addons_hours WHERE addonid = ?"))
	{
		$stmt->bind_param("i",$addon['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($addon['hours']) < 1)
		return;
	
	foreach ($addon['hours'] as $h)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO addons_hours (addonid,startminute,stopminute) VALUES (?,?,?)"))
		{
			$stmt->bind_param("iii",$addon['id'],$h['start'],$h['stop']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function ConsolidateRates($hours)
{
	$arr = array();
	
	while (count($hours) > 0)
	{
		if (count($arr) == 0)
		{
			$arr[] = array("start"=>$hours[0]['start'],"stop"=>$hours[0]['stop'],"rate"=>$hours[0]['rate']);
			array_splice($hours,0,1);
			continue;
		}
		
		$modified = false;
		
		foreach ($arr as $key => &$a)
		{			
			if ($hours[0]['start'] >= $a['start'] && $hours[0]['start'] < $a['stop'])
			{
				$modified = true;
				
				if ($hours[0]['rate'] <= $a['rate'])
				{
					if ($hours[0]['stop'] > $a['stop'])
						$hours[0]['start'] = $a['stop'];
					else 
					{
						array_splice($hours,0,1);
						break;
					}
				}
				else
				{
					$a['stop'] = $hours[0]['start'];
				}
				
				if ($hours[0]['start'] >= $hours[0]['stop'])
					array_splice($hours,0,1);
				if ($a['start'] >= $a['stop'])
					array_splice($arr,$key,1);
				
				break;
			}
			
			if ($hours[0]['stop'] > $a['start'] && $hours[0]['stop'] <= $a['stop'])
			{
				$modified = true;
				
				if ($hours[0]['rate'] <= $a['rate'])
					$hours[0]['stop'] = $a['start'];
				else
				{
					$a['start'] = $hours[0]['stop'];
				}
				
				if ($hours[0]['start'] >= $hours[0]['stop'])
					array_splice($hours,0,1);
				if ($a['start'] >= $a['stop'])
					array_splice($arr,$key,1);
				break;
			}
			
			if ($a['start'] >= $hours[0]['start'] && $a['stop'] <= $hours[0]['stop'])
			{
				$modified = true;
				
				if ($hours[0]['rate'] <= $a['rate'])
				{
					$hours[] = array("start"=>$hours[0]['start'],"stop"=>$a['start'],"rate"=>$hours[0]['rate']);
					$hours[] = array("start"=>$a['stop'],"stop"=>$hours[0]['stop'],"rate"=>$hours[0]['rate']);
					array_splice($hours,0,1);
				}
				else
				{
					array_splice($arr,$key,1);
				}
				
				break;
			}
		}
		
		if (!$modified)
		{
			$arr[] = array("start"=>$hours[0]['start'],"stop"=>$hours[0]['stop'],"rate"=>$hours[0]['rate']);
			array_splice($hours,0,1);
		}
	}
	
	for ($i = 0; $i < count($arr); $i++)
	{
		for ($i2 = 0; $i2 < count($arr); $i2++)
		{
			if ($arr[$i]['rate'] == $arr[$i2]['rate'] && $arr[$i]['stop'] == $arr[$i2]['start'])
			{
				$arr[$i]['stop'] = $arr[$i2]['stop'];
				array_splice($arr,$i2,1);
				$i2--;
				
				if ($i2 <= $i)
					$i--;
			}
		}
	}
	
	return $arr;
}

function SetResourceHours($resource)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM resources_hours WHERE resourceid = ?"))
	{
		$stmt->bind_param("i",$resource['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($resource['hours']) < 1)
		return;
	
	foreach ($resource['hours'] as $h)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO resources_hours (resourceid,startminute,stopminute) VALUES (?,?,?)"))
		{
			$stmt->bind_param("iii",$resource['id'],$h['start'],$h['stop']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SetMenuHours($menu)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM menus_hours WHERE menuid = ?"))
	{
		$stmt->bind_param("i",$menu['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($menu['hours']) < 1)
		return;
	
	foreach ($menu['hours'] as $h)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO menus_hours (menuid,startminute,stopminute) VALUES (?,?,?)"))
		{
			$stmt->bind_param("iii",$menu['id'],$h['start'],$h['stop']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SetPersonnelHours($personnel)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM personnel_hours WHERE personnelid = ?"))
	{
		$stmt->bind_param("i",$personnel['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($personnel['hours']) < 1)
		return;
	
	foreach ($personnel['hours'] as $h)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO personnel_hours (personnelid,startminute,stopminute) VALUES (?,?,?)"))
		{
			$stmt->bind_param("iii",$personnel['id'],$h['start'],$h['stop']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SetPersonnelResources($personnel)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM personnel_resources WHERE personnelid = ?"))
	{
		$stmt->bind_param("i",$personnel['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($personnel['resources']) == 0)
		$personnel['resources'][] = 0;
	
	for ($i=0; $i<count($personnel['resources']); $i++)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO personnel_resources (personnelid,resourceid) VALUES (?,?)"))
		{
			$r = $personnel['resources'][$i];
			$stmt->bind_param("ii",$personnel['id'],$r);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SetResourceRates($resource)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM resources_rates WHERE resourceid = ?"))
	{
		$stmt->bind_param("i",$resource['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM resources_rates_raw WHERE resourceid = ?"))
	{
		$stmt->bind_param("i",$resource['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($resource['rates']) > 0)
	{
		foreach ($resource['rates'] as $rate)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO resources_rates_raw (resourceid,rate,startminute,stopminute) VALUES (?,?,?,?)"))
			{
				$stmt->bind_param("idii",$resource['id'],$rate['rate'],$rate['start'],$rate['stop']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
	}
		
	$arr = ConsolidateRates($resource['rates']);
	
	foreach ($arr as $a)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO resources_rates (resourceid,rate,startminute,stopminute) VALUES (?,?,?,?)"))
		{
			$stmt->bind_param("idii",$resource['id'],$a['rate'],$a['start'],$a['stop']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function SetResourceSlots($resource)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM resources_slots WHERE resourceid = ?"))
	{
		$stmt->bind_param("i",$resource['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	$arr = array();
	
	if (count($resource['slots']) > 0)
	{
		foreach($resource['slots'] as $slot)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO resources_slots (resourceid,rate,startminute,stopminute,combinable) VALUES (?,?,?,?,?)"))
			{
				$stmt->bind_param("idiii",$resource['id'],$slot['rate'],$slot['start'],$slot['stop'],$slot['combinable']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
	}
}

function GetResourceRates($rid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT startminute,stopminute,rate FROM resources_rates_raw WHERE resourceid = ? ORDER BY rate ASC,startminute ASC"))
	{
		$stmt->bind_param("i",$rid);
		$stmt->execute();
		$stmt->bind_result($start,$stop,$rate);
		while ($stmt->fetch())
		{
			$arr[] = array("start"=>$start,"stop"=>$stop,"rate"=>$rate);
		}			
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetResourceSlots($rid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT startminute,stopminute,rate,combinable FROM resources_slots WHERE resourceid = ? ORDER BY startminute ASC"))
	{
		$stmt->bind_param("i",$rid);
		$stmt->execute();
		$stmt->bind_result($start,$stop,$rate,$comb);
		while ($stmt->fetch())
		{
			$arr[] = array("start"=>$start,"stop"=>$stop,"rate"=>$rate,"combinable"=>$comb);
		}			
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetAddonHours($aid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT startminute,stopminute FROM addons_hours WHERE addonid = ? ORDER BY startminute ASC"))
	{
		$stmt->bind_param("i",$aid);
		$stmt->execute();
		$stmt->bind_result($start,$stop);
		while ($stmt->fetch())
		{
			$arr[] = array("start"=>$start,"stop"=>$stop);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetAddonPictures($aid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT placement,photo,caption FROM addons_photos WHERE addonid = ? ORDER BY placement ASC"))
	{
		$stmt->bind_param("i",$aid);
		$stmt->execute();
		$stmt->bind_result($placement,$photo,$caption);
		while ($stmt->fetch())
		{
			$arr[] = array("placement"=>$placement,"url"=>"/assets/content/".$photo,"caption"=>Sanitize($caption));
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetResourcePictures($rid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT placement,photo,caption FROM resources_photos WHERE resourceid = ? ORDER BY placement ASC"))
	{
		$stmt->bind_param("i",$rid);
		$stmt->execute();
		$stmt->bind_result($placement,$photo,$caption);
		while ($stmt->fetch())
		{
			$arr[] = array("placement"=>$placement,"url"=>"/assets/content/".$photo,"caption"=>Sanitize($caption));
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetResourceHours($rid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT startminute,stopminute FROM resources_hours WHERE resourceid = ? ORDER BY startminute ASC"))
	{
		$stmt->bind_param("i",$rid);
		$stmt->execute();
		$stmt->bind_result($start,$stop);
		while ($stmt->fetch())
		{
			$arr[] = array("start"=>$start,"stop"=>$stop);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetMenuHours($mid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT startminute,stopminute FROM menus_hours WHERE menuid = ? ORDER BY startminute ASC"))
	{
		$stmt->bind_param("i",$mid);
		$stmt->execute();
		$stmt->bind_result($start,$stop);
		while ($stmt->fetch())
		{
			$arr[] = array("start"=>$start,"stop"=>$stop);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetPersonnelHours($pid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT startminute,stopminute FROM personnel_hours WHERE personnelid = ? ORDER BY startminute ASC"))
	{
		$stmt->bind_param("i",$pid);
		$stmt->execute();
		$stmt->bind_result($start,$stop);
		while ($stmt->fetch())
		{
			$arr[] = array("start"=>$start,"stop"=>$stop);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetPersonnelResources($pid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT resourceid FROM personnel_resources WHERE personnelid = ? ORDER BY resourceid ASC"))
	{
		$stmt->bind_param("i",$pid);
		$stmt->execute();
		$stmt->bind_result($r);
		while ($stmt->fetch())
		{
			$arr[] = $r;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function SetResourceAddons($vid, $resource)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM resources_addons WHERE resourceid = ?"))
	{
		$stmt->bind_param("i",$resource['id']);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($resource['addons']) > 0)
	{
		foreach ($resource['addons'] as $addon)
		{
			$aid = null;
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT addonid FROM addons WHERE venueid = ? AND name = ? AND status = 'new'"))
			{
				$stmt->bind_param("is",$vid,$addon);
				$stmt->execute();
				$stmt->bind_result($a);
				if ($stmt->fetch())
					$aid = $a;
				$GLOBALS['db']->CloseConn();
			}
			if ($aid)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO resources_addons (resourceid,addonid) VALUES (?,?)"))
				{
					$stmt->bind_param("ii",$resource['id'],$aid);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
		}
	}
}

function GetResourceAddons($rid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT a.name FROM resources_addons AS r LEFT JOIN addons AS a ON a.addonid = r.addonid WHERE a.status = 'new' AND r.resourceid = ? ORDER BY r.addonid ASC"))
	{
		$stmt->bind_param("i",$rid);
		$stmt->execute();
		$stmt->bind_result($name);
		while ($stmt->fetch())
		{
			$arr[] = Sanitize($name);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetResourceIDs(&$resource)
{
	$result = "".(isset($resource['id'])?$resource['id']:"").",";
	
	foreach ($resource['children'] as &$child)
		$result .= GetResourceIDs($child);
	return $result;
}

function ClearResourceRelationships(&$resource)
{	
	if (CheckIfEditOK($resource['id']))
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("DELETE FROM resources_relationships WHERE childid = ? OR parentid = ?"))
		{
			$stmt->bind_param("ii",$resource['id'],$resource['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($resource['children'] as &$child)
			ClearResourceRelationships($child);
	}
}

function SaveResourceRelationships(&$resource)
{
	if (CheckIfEditOK($resource['id']))
	{
		foreach ($resource['children'] as &$child)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO resources_relationships (childid,parentid,relation) VALUES (?,?,?)"))
			{
				$relationship = "inside_".($child['linked'] == "1" ? "linked" : "unlinked");
				$stmt->bind_param("iis",$child['id'],$resource['id'],$relationship);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			SaveResourceRelationships($child);
		}	
	}
}

function PrunePromos($vid, $pid_string)
{
	if (strlen($pid_string) < 1)
		$pid_string = "9999999999999";
		
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM promos WHERE venueid = ? AND promoid NOT IN (".$pid_string.")"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
}

function PruneDeposits($vid, $pid_string)
{
	if (strlen($pid_string) < 1)
		$pid_string = "9999999999999";
		
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM deposit_policies WHERE venueid = ? AND policyid NOT IN (".$pid_string.")"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
}

function PruneRefunds($vid, $rid_string)
{
	if (strlen($rid_string) < 1)
		$rid_string = "9999999999999";
		
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("DELETE FROM refund_policies WHERE venueid = ? AND policyid NOT IN (".$rid_string.")"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
}

function PruneAddons($vid, $aid_string)
{
	if (strlen($aid_string) < 1)
		$aid_string = "9999999999999";
		
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("UPDATE addons SET status = 'deleted' WHERE venueid = ? AND addonid NOT IN (".$aid_string.")"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
}

function PruneResources($vid, $rid_string)
{
	if (strlen($rid_string) < 1)
		$rid_string = "9999999999999";
		
	$tobedel = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT resourceid FROM resources WHERE venueid = ? AND status != 'deleted' AND resourceid NOT IN (".$rid_string.")"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($rid);
		while ($stmt->fetch())
		{
			$tobedel[] = $rid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($tobedel as $rid)
	{
		if (CheckIfEditOK($rid))
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE resources SET status = 'deleted' WHERE venueid = ? AND resourceid NOT IN (".$rid_string.")"))
			{
				$stmt->bind_param("i",$vid);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
	}
}

function PruneMenus($vid, $mid_string)
{
	if (strlen($mid_string) < 1)
		$mid_string = "9999999999999";
		
	$tobedel = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT menuid FROM menus WHERE menuid = ? AND status != 'deleted' AND menuid NOT IN (".$mid_string.")"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($mid);
		while ($stmt->fetch())
		{
			$tobedel[] = $mid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($tobedel as $mid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE menus SET status = 'deleted' WHERE menuid = ? AND menuid NOT IN (".$mid_string.")"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE menus_items SET status = 'deleted' WHERE menuid = ? AND menuid NOT IN (".$mid_string.")"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function PruneItems($vid, $iid_string)
{
	if (strlen($iid_string) < 1)
		$iid_string = "9999999999999";
		
	$tobedel = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT itemid FROM menus_items WHERE itemid = ? AND status != 'deleted' AND itemid NOT IN (".$iid_string.")"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($iid);
		while ($stmt->fetch())
		{
			$tobedel[] = $iid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($tobedel as $iid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE menus_items SET status = 'deleted' WHERE itemid = ? AND itemid NOT IN (".$iid_string.")"))
		{
			$stmt->bind_param("i",$iid);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function PrunePersonnel($vid, $pid_string)
{
	if (strlen($pid_string) < 1)
		$pid_string = "9999999999999";
		
	$tobedel = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT personnelid FROM personnel WHERE venueid = ? AND status != 'deleted' AND personnelid NOT IN (".$pid_string.")"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($iid);
		while ($stmt->fetch())
		{
			$tobedel[] = $iid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($tobedel as $iid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE personnel SET status = 'deleted' WHERE personnelid = ? AND personnelid NOT IN (".$pid_string.")"))
		{
			$stmt->bind_param("i",$iid);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function PruneQuestions($vid, $id_string)
{
	if (strlen($id_string) < 1)
		$id_string = "9999999999999";
		
	$tobedel = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT questionid FROM questions WHERE venueid = ? AND deleted != 1 AND questionid NOT IN (".$id_string.")"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($iid);
		while ($stmt->fetch())
		{
			$tobedel[] = $iid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($tobedel as $iid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE questions SET deleted = 1 WHERE questionid = ? AND questionid NOT IN (".$id_string.")"))
		{
			$stmt->bind_param("i",$iid);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
	}
}

function CheckIfDeleteOK($idstr)
{
	$result = "success";
	
	$ids = json_decode($idstr);
	
	foreach ($ids as $id)
	{
		if (CheckIfEditOK($id) != "success")
			$result = "no";
	}
	
	return $result;
}

function CheckIfEditOK($id)
{
	$query = "";
	$cnt = 0;
	
	$query = "SELECT COUNT(*) FROM booking AS b LEFT JOIN booking_resources AS r ON r.bookingid = b.bookingid WHERE r.starttime > UNIX_TIMESTAMP() AND b.status != 'cancelled' AND r.resourceid = ?";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare($query))
	{
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();
		$cnt = $count;
		$GLOBALS['db']->CloseConn();
	}
	
	if ($cnt > 0)
		return "no";
	return "success";
}

function RequestVenueReview($id)
{
	require_once('php/email.php');
	$result = "There was an error submitting your venue for review";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("UPDATE venues SET status = 'pending_review' WHERE status != 'active' AND venueid = ?"))
	{
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
		$result = "success";
	}
	SendVenueApprovalRequestedMessage($id);
	return $result;
}

function CheckShortURL($url)
{
	$result = 0;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT COUNT(*) FROM venues WHERE shorturl = ?"))
	{
		$stmt->bind_param("s",$url);
		$stmt->execute();
		$stmt->bind_result($cnt);
		while($stmt->fetch())
		{
			$result = $cnt;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($result > 0)
		return true;
	else return false;
}

function CreateShortURL($name)
{
	$short = strtolower($name);
	$short = str_replace(" ","-",$short);
	$short = preg_replace("/[^\da-z_\-]/i", "", $short);
	$short = str_replace("--","-",$short);
	$short = str_replace("--","-",$short);
	$short = (strlen($short) > 30 ? substr($short,0,30) : $short);
	
	$rand = "";
	
	while (CheckShortURL($short.$rand))
		$rand = "." . rand(0,1000);
		
	return $short.$rand;
}

function LoadVenue($vid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT venue_typeid, venue_styleid, name, description, banner, shorturl, visibility, website, facebook, twitter,
		business_name, ein, address, city, state, zip, country, latitude, longitude, phone, timezone, salestax, currency, status, contract FROM venues WHERE venueid = ?"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($type,$style,$name,$desc,$banner,$shorturl,$vis,$web,$fb,$tw,$business,$ein,$address,$city,$state,$zip,$country,$lat,$lng,$phone,$timezone,$salestax,$currency,$status,$c);
		if ($stmt->fetch())
		{
			$arr['id'] = $vid;
			$arr['type'] = $type;
			$arr['style'] = $style;
			$arr['name'] = Sanitize($name);
			$arr['description'] = Sanitize($desc);
			$arr['banner'] = Sanitize($banner);
			$arr['shorturl'] = $shorturl;
			$arr['website'] = $web;
			$arr['facebook'] = $fb;
			$arr['twitter'] = $tw;
			$arr['business_name'] = Sanitize($business);
			$arr['ein'] = Sanitize($ein);
			$arr['address'] = Sanitize($address);
			$arr['city'] = Sanitize($city);
			$arr['state'] = Sanitize($state);
			$arr['zip'] = Sanitize($zip);
			$arr['country'] = Sanitize($country);
			$arr['latitude'] = Sanitize($lat);
			$arr['longitude'] = Sanitize($lng);
			$arr['phone'] = Sanitize($phone);
			$arr['features'] = array();
			$arr['functionality'] = array();
			$arr['contacts'] = array();
			$arr['rights'] = array();
			$arr['pictures'] = array();
			$arr['timezone'] = $timezone;
			$arr['salesTax'] = $salestax;
			$arr['currency'] = $currency;
			$arr['visibility'] = $vis;
			$arr['status'] = $status;
			$arr['contract'] = (strlen($c) > 0 ? "/assets/content/".$c : "");
		}
		$GLOBALS['db']->CloseConn();
	}
	if (isset($arr['id']))
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT featureid FROM venues_features WHERE venueid = ?"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($fid);
			while($stmt->fetch())
			{
				$arr['features'][] = $fid;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT showMenus,showPersonnel,showQuestions,showPromos,publicFileUploads,gratuity,entireVenue FROM venues_functionality WHERE venueid = ?"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($m,$p,$q,$pc,$fu,$gra,$ev);
			while($stmt->fetch())
			{
				$arr['functionality']['menus'] = $m;
				$arr['functionality']['personnel'] = $p;
				$arr['functionality']['questions'] = $q;
				$arr['functionality']['promos'] = $pc;
				$arr['functionality']['publicFileUploads'] = $fu;
				$arr['functionality']['gratuity'] = $gra;
				$arr['functionality']['entireVenue'] = $ev;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT name,title,phone,email,comments FROM venues_contacts WHERE venueid = ?"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($name,$title,$phone,$email,$comments);
			while($stmt->fetch())
			{				
				$arr['contacts'][] = array("name"=>Sanitize($name),"title"=>Sanitize($title),"phone"=>Sanitize($phone),"email"=>$email,"comments"=>Sanitize($comments));
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT email,role,receiveEmails FROM venues_rights WHERE venueid = ?"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($name,$role,$e);
			while($stmt->fetch())
			{	
				$roles = array();
				for ($i = 0; $i < 10; $i++)
					if (($role & pow(2,$i)) != 0)
						$roles[] = $i;
						
				$arr['rights'][] = array("name"=>Sanitize($name),"roles"=>$roles,"receiveEmails"=>$e);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT placement,photo,caption FROM venues_photos WHERE venueid = ? ORDER BY placement ASC"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($placement,$url,$caption);
			while($stmt->fetch())
			{
				$arr['pictures'][] = array("placement"=>$placement,"url"=>"/assets/content/".$url,"caption"=>Sanitize($caption));
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	return $arr;
}

function LoadDeposits($vid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid, name, deposit_dollar_thresh, deposit_perc, deposit_amount, full_due FROM deposit_policies WHERE venueid = ? ORDER BY policyid ASC"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($pid,$name,$dollar,$perc,$amount,$full);
		while($stmt->fetch())
		{
			$arr2 = array();
			$arr2['id'] = $pid;
			$arr2['name'] = Sanitize($name);
			$arr2['threshold'] = $dollar;
			$arr2['perc'] = $perc;
			$arr2['amount'] = $amount;
			$arr2['full'] = $full;
			
			$arr[] = $arr2;
		}
		$GLOBALS['db']->CloseConn();
	}
	return $arr;
}

function LoadRefunds($vid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid, name, policy FROM refund_policies WHERE venueid = ? ORDER BY policyid ASC"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($pid,$name,$policy);
		while($stmt->fetch())
		{
			$arr2 = array();
			$arr2['id'] = $pid;
			$arr2['name'] = Sanitize($name);
			$arr2['policy'] = $policy;
			
			$arr[] = $arr2;
		}
		$GLOBALS['db']->CloseConn();
	}
	return $arr;
}

function LoadPromos($vid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT promoid,name,description,dollar_threshold,dollar_discount,percentage_discount,reuses,quantity,applic,entireinvoice,combinable,auto,starttime,stoptime,expires,status FROM promos WHERE venueid = ? ORDER BY promoid ASC"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($pid,$name,$desc,$thresh,$doldisc,$perdisc,$reuses,$quant,$applic,$entire,$comb,$auto,$start,$stop,$exp,$status);
		while($stmt->fetch())
		{			
			$arr2 = array();
			$arr2['id'] = $pid;
			$arr2['name'] = Sanitize($name);
			$arr2['description'] = Sanitize($desc);
			$arr2['status'] = $status;
			$arr2['discount_threshold'] = $thresh;
			$arr2['hours'] = null;
			$arr2['start'] = $start;
			$arr2['stop'] = $stop;
			$arr2['expires'] = $exp;
			
			if ($doldisc > 0)
			{
				$arr2['discount_type'] = "dollar";
				$arr2['discount_amount'] = $doldisc;
			}
			else
			{
				$arr2['discount_type'] = "percent";
				$arr2['discount_amount'] = $perdisc;
			}
			
			if ($reuses == null)
				$arr2['peruser'] = "unlim";
			else $arr2['peruser'] = $reuses;
			
			if ($quant == null)
				$arr2['quantity'] = "unlim";
			else $arr2['quantity'] = $quant;
			
			$arr2['applic'] = $applic;			
			$arr2['entireinvoice'] = $entire;
			$arr2['combinable'] = $comb;
			$arr2['auto'] = $auto;
			
			$arr[] = $arr2;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($arr as &$a)
	{
		$a['hours'] = LoadPromoHours($a['id']);
		$a['resources'] = LoadPromoResources($a['id']);
	}
	
	return $arr;
}

function LoadPromoHours($pid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT start,stop FROM promos_hours WHERE promoid = ? ORDER BY start ASC"))
	{
		$stmt->bind_param("i",$pid);
		$stmt->execute();
		$stmt->bind_result($start,$stop);
		while($stmt->fetch())
		{
			$arr[] = array("start"=>$start,"stop"=>$stop);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function LoadPromoResources($pid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT resourceid FROM promos_resources WHERE promoid = ? ORDER BY resourceid ASC"))
	{
		$stmt->bind_param("i",$pid);
		$stmt->execute();
		$stmt->bind_result($res);
		while($stmt->fetch())
		{
			$arr[] = $res;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function LoadAddons($vid, $tz)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT addonid, name, typeid, description, min, max, deliverable, price, deposit_policyid, refund_policyid FROM addons WHERE venueid = ? AND status != 'deleted' ORDER BY name ASC"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($aid,$name,$type,$desc,$min,$max,$deliverable,$price,$deposit,$refund);
		while($stmt->fetch())
		{
			$arr2 = array();
			$arr2['id'] = $aid;
			$arr2['name'] = Sanitize($name);
			$arr2['type'] = $type;
			$arr2['description'] = Sanitize($desc);
			$arr2['minimum'] = $min;
			$arr2['maximum'] = $max;
			$arr2['deliverable'] = $deliverable;
			$arr2['price'] = $price;
			$arr2['deposit'] = $deposit;
			$arr2['refund'] = $refund;
			
			$arr[] = $arr2;
		}
		$GLOBALS['db']->CloseConn();
	}
	if (count($arr) > 0)
	{
		foreach ($arr as &$a)
		{
			$a['pictures'] = GetAddonPictures($a['id']);
			$a['hours'] = GetAddonHours($a['id'],$tz);
			GetPolicies($vid,$a,$a['deposit'],$a['refund']);
		}
	}
	
	return $arr;
}

function LoadResources($vid,$tz)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT resourceid, typeid, name, description, autoapprove, deposit_policyid, refund_policyid,
		over21_req, capacity, seats, minduration, increment, min_lead_time, cleanupcost, cleanuptime, default_rate, timeslots, status 
		FROM resources WHERE venueid = ? AND status = 'new' ORDER BY default_rate DESC, name ASC"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($rid,$type,$name,$desc,$auto,$deposit,$refund,$over21,$capacity,$seats,$duration,$inc,$lead,$cleanupcost,$cleanup,$rate,$timeslots,$status);
		while($stmt->fetch())
		{
			$arr2 = array();
			$arr2['id'] = $rid;
			$arr2['name'] = Sanitize($name);
			$arr2['type'] = $type;
			$arr2['description'] = Sanitize($desc);
			$arr2['autoapprove'] = $auto;
			$arr2['over21'] = $over21;
			$arr2['capacity'] = $capacity;
			$arr2['seats'] = $seats;
			$arr2['duration'] = $duration;
			$arr2['increment'] = $inc;
			$arr2['lead'] = $lead;
			$arr2['cleanupcost'] = $cleanupcost;
			$arr2['cleanup'] = $cleanup;
			$arr2['rate'] = $rate;
			$arr2['status'] = $status;
			$arr2['children'] = array();
			$arr2['deposit'] = $deposit;
			$arr2['refund'] = $refund;
			$arr2['timeslots'] = $timeslots;
			$arr[] = $arr2;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($arr) > 0)
	{
		foreach ($arr as &$a)
		{
			$a['pictures'] = GetResourcePictures($a['id']);
			$a['hours'] = GetResourceHours($a['id'],$tz);
			$a['rates'] = GetResourceRates($a['id'],$tz);
			$a['slots'] = GetResourceSlots($a['id'],$tz);
			$a['addons'] = json_encode(GetResourceAddons($a['id']));
			GetPolicies($vid,$a,$a['deposit'],$a['refund']);
		}
	}
	
	return $arr;
}

function LoadFood($vid,$tz)
{
	$arr = array();
	$arr['menus'] = array();
	$arr['items'] = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT menuid,name,description,deposit_policyid,refund_policyid FROM menus WHERE venueid = ? AND status = 'new' ORDER BY menuid ASC"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($mid,$name,$desc,$deposit,$refund);
		while($stmt->fetch())
		{
			$arr2 = array();
			$arr2['id'] = $mid;
			$arr2['name'] = Sanitize($name);
			$arr2['description'] = Sanitize($desc);
			$arr2['deposit'] = $deposit;
			$arr2['refund'] = $refund;
			$arr2['hours'] = array();
			
			$arr['menus'][] = $arr2;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($arr['menus']) > 0)
	{
		foreach ($arr['menus'] as &$a)
		{
			$a['hours'] = GetMenuHours($a['id'],$tz);
			GetPolicies($vid,$a,$a['deposit'],$a['refund']);
		}
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT i.itemid,m.name,i.menuid,i.name,i.type,i.description,i.price,i.min,i.max,i.photo,i.caption FROM menus_items AS i LEFT JOIN menus AS m ON m.menuid = i.menuid WHERE i.venueid = ? AND m.status = 'new' AND i.status = 'new' ORDER BY itemid ASC"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($iid,$menu,$mid,$name,$type,$desc,$price,$min,$max,$url,$caption);
		while($stmt->fetch())
		{
			$arr2 = array();
			$arr2['id'] = $iid;
			$arr2['type'] = $type;
			$arr2['menu'] = Sanitize($menu);
			$arr2['menuid'] = $mid;
			$arr2['name'] = Sanitize($name);
			$arr2['description'] = Sanitize($desc);
			$arr2['price'] = $price;
			$arr2['min'] = $min;
			$arr2['max'] = $max;
			$arr2['pictures'] = array();
			if ($url && $url != "null")
				$arr2['pictures'][] = array("placement"=>0,"url"=>"/assets/content/".$url,"caption"=>Sanitize($caption));
			
			$arr['items'][] = $arr2;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function LoadPersonnel($vid,$tz)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT personnelid,name,description,price,min,max,req,deposit_policyid,refund_policyid FROM personnel WHERE venueid = ? AND status = 'new' ORDER BY personnelid ASC"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($pid,$name,$desc,$price,$min,$max,$req,$deposit,$refund);
		while($stmt->fetch())
		{
			$arr2 = array();
			$arr2['id'] = $pid;
			$arr2['name'] = Sanitize($name);
			$arr2['price'] = $price;
			$arr2['min'] = $min;
			$arr2['max'] = $max;
			$arr2['req'] = $req;
			$arr2['description'] = Sanitize($desc);
			$arr2['deposit'] = $deposit;
			$arr2['refund'] = $refund;
			$arr2['resources'] = array();
			$arr2['hours'] = array();
			
			$arr[] = $arr2;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($arr) > 0)
	{
		foreach ($arr as &$a)
		{
			$a['hours'] = GetPersonnelHours($a['id'],$tz);
			$a['resources'] = json_encode(GetPersonnelResources($a['id'],$tz));
			GetPolicies($vid,$a,$a['deposit'],$a['refund']);
		}
	}
	
	return $arr;
}

function LoadQuestions($vid)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT questionid,question,type,req FROM questions WHERE venueid = ? AND deleted != 1 ORDER BY questionid ASC"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($qid,$text,$type,$req);
		while($stmt->fetch())
		{
			$arr2 = array();
			$arr2['id'] = "".$qid;
			$arr2['text'] = $text;
			$arr2['type'] = $type;
			$arr2['req'] = ($req == 1 ? "yes" : "no");
			$arr2['appliesto'] = array("addons"=>array(),"menus"=>array(),"personnel"=>array(),"resources"=>array());
			
			$arr[] = $arr2;
		}
		$GLOBALS['db']->CloseConn();
	}
	if (count($arr) > 0)
	{
		foreach ($arr as &$a)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT choice FROM questions_choices WHERE questionid=?"))
			{
				$stmt->bind_param("i",$a['id']);
				$stmt->execute();
				$stmt->bind_result($t);
				while($stmt->fetch())
				{
					$a['choices'][] = $t;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT addonid FROM questions_addons WHERE questionid=?"))
			{
				$stmt->bind_param("i",$a['id']);
				$stmt->execute();
				$stmt->bind_result($t);
				while($stmt->fetch())
				{
					$a['appliesto']['addons'][] = "".$t;
				}
				$GLOBALS['db']->CloseConn();
			}
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT menuid FROM questions_menus WHERE questionid=?"))
			{
				$stmt->bind_param("i",$a['id']);
				$stmt->execute();
				$stmt->bind_result($t);
				while($stmt->fetch())
				{
					$a['appliesto']['menus'][] = "".$t;
				}
				$GLOBALS['db']->CloseConn();
			}
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT personnelid FROM questions_personnel WHERE questionid=?"))
			{
				$stmt->bind_param("i",$a['id']);
				$stmt->execute();
				$stmt->bind_result($t);
				while($stmt->fetch())
				{
					$a['appliesto']['personnel'][] = "".$t;
				}
				$GLOBALS['db']->CloseConn();
			}
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT resourceid FROM questions_resources WHERE questionid=?"))
			{
				$stmt->bind_param("i",$a['id']);
				$stmt->execute();
				$stmt->bind_result($t);
				while($stmt->fetch())
				{
					$a['appliesto']['resources'][] = "".$t;
				}
				$GLOBALS['db']->CloseConn();
			}
		}
	}
	
	return $arr;
}

function GetPolicies($vid,&$arr,$deposit,$refund)
{
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT name FROM deposit_policies WHERE venueid = ? AND policyid = ?"))
	{
		$stmt->bind_param("ii",$vid,$deposit);
		$stmt->execute();
		$stmt->bind_result($name);
		if($stmt->fetch())
		{
			$arr['deposit'] = Sanitize($name);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT name FROM refund_policies WHERE venueid = ? AND policyid = ?"))
	{
		$stmt->bind_param("ii",$vid,$refund);
		$stmt->execute();
		$stmt->bind_result($name);
		if($stmt->fetch())
		{
			$arr['refund'] = Sanitize($name);
		}
		$GLOBALS['db']->CloseConn();
	}
}

function LoadRelationships($vid)
{
	$arr = array();
	$ids = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT resourceid FROM resources WHERE venueid = ? AND status = 'new'"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($rid);
		while($stmt->fetch())
		{
			$ids .= $rid.",";
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$ids = rtrim($ids,",");
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT childid, parentid, relation FROM resources_relationships AS r LEFT JOIN resources AS r2 ON r2.resourceid = r.childid WHERE childid IN (".$ids.") ORDER BY r2.name DESC"))
	{
		$stmt->execute();
		$stmt->bind_result($child,$parent,$relation);
		while($stmt->fetch())
		{
			$arr[] = array("child"=>$child,"parent"=>$parent,"relation"=>$relation);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function IsDoubleBooked($booking,$id)
{
	require_once("php/book.php");
	$resources = array();
	
	if ($booking)
		$resources = &$booking['resources'];
	else
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT r.resourceid, r.starttime, r.stoptime, v.timezone FROM booking_resources AS r LEFT JOIN venues AS v ON v.venueid = r.venueid WHERE r.bookingid = ?"))
		{			
			$stmt->bind_param("i",$id);
			$stmt->execute();
			$stmt->bind_result($r,$start,$stop,$tz);
			while ($stmt->fetch())
			{
				$resources[] = array("id"=>$r,"start"=>$start,"stop"=>$stop);
			}
		}
	}
	
	foreach ($resources as $resource)
	{
		$is = IsDBooked($id,$resource['id'], $resource['start'], $resource['stop']);
		if ($is)
			return $is;
		
		$children = FindChildren($resource['id'],true);
		foreach ($children as $child)
		{
			$is = IsDBooked($id,$child, $resource['start'], $resource['stop']);
			if ($is)
				return $is;
		}
	}
	
	return false;
}

function IsDBooked($bid,$rid, $starttime, $stoptime)
{
	$is = false;
		
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT r.bookingid FROM booking_resources AS r LEFT JOIN booking AS b ON b.bookingid = r.bookingid WHERE r.bookingid != ? AND (b.status = 'Pending Approval' OR b.status = 'Pending Payment' OR b.status = 'Paid' OR b.status = 'Imported' OR b.status = 'Past Due') AND r.resourceid = ? AND ? < r.stoptime + r.cleanuptime AND ? > r.starttime - r.cleanuptime"))
	{		
		$stmt->bind_param("iiii",$bid,$rid,$starttime,$stoptime);
		$stmt->execute();
		$stmt->bind_result($i);
		if ($stmt->fetch())
		{
			$is = $i;
		}
		$GLOBALS['db']->CloseConn();
	}
		
	return $is;
}

function GetVenueBookings($id,$onlypending)
{

	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $id)
			if ($v['role'] & 16 || $v['role'] & 4 || $v['role'] & 2)
				$auth = true;
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
		
	if (!$auth)
		return "You are not authorized to view this venue's bookings";
	
	$arr = array();
	$vid = preg_replace("/[^0-9]/","",$id);
	$dnow = new DateTime();
	$now = $dnow->getTimestamp();
	
	$timezone = "";	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT timezone FROM venues WHERE venueid = ?"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($tz);
		if($stmt->fetch())
			$timezone = $tz;
		$GLOBALS['db']->CloseConn();
	}	
	
	$now = time();
	
	$stmt = $GLOBALS['db']->stmt_init();
	//echo "SELECT b.bookingid,b.start,b.stop,b.headcount,b.total_cost,v.currency,b.status,u.firstname,u.lastname,b.isnew,b.full_due FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.venueid = ? AND b.status = 'Pending Approval' ORDER BY b.start ASC";
	if ($stmt->prepare("SELECT b.bookingid,b.start,b.stop,b.headcount,b.total_cost,v.currency,b.status,u.firstname,u.lastname,b.isnew,b.full_due FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.venueid = ? AND b.status = 'Pending Approval' ORDER BY b.start ASC"))
	{		
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($bid,$start,$stop,$hc,$tc,$cur,$st,$firstname,$lastname,$in,$fd);
		while ($stmt->fetch())
		{
			$user = $firstname." ".$lastname;
			if ($user == " ")
				$user = "Imported";
			
			$pastDue = false;
			if ($now > $fd && ($st == "Pending Payment" || $st == "Pending Deposit" || $st == "Past Due"))
				$pastDue = true;
			
			$arr[] = array("id"=>$bid,"start"=>$start,"stop"=>$stop,"multiple"=>false,"headcount"=>$hc,"total"=>$tc,"currency"=>$cur,"status"=>$st,"isnew"=>$in,"user"=>$user,"doublebooked"=>false,"pastDue"=>$pastDue,"retainable"=>false,"timezone"=>$timezone);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($onlypending != "true")
	{
		$stmt = $GLOBALS['db']->stmt_init();
		//echo "SELECT b.bookingid,b.start,b.stop,b.headcount,b.total_cost,v.currency,b.status,u.firstname,u.lastname,b.full_due FROM booking AS b LEFT JOIN venues AS v ON v.venueid=b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.venueid = ? AND b.status != 'Pending Approval' ORDER BY b.start DESC LIMIT 0,200 \r\n";
		if ($stmt->prepare("SELECT b.bookingid,b.start,b.stop,b.headcount,b.total_cost,v.currency,b.status,u.firstname,u.lastname,b.full_due FROM booking AS b LEFT JOIN venues AS v ON v.venueid=b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.venueid = ? AND b.status != 'Pending Approval' ORDER BY b.start DESC LIMIT 0,200"))
		{		
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($bid,$start,$stop,$hc,$tc,$cur,$st,$firstname,$lastname,$fd);
			while ($stmt->fetch())
			{
				$user = $firstname." ".$lastname;
				if ($user == " ")
					$user = "Imported";
				
				$pastDue = false;
				if ($now > $fd && ($st == "Pending Payment" || $st == "Pending Deposit" || $st == "Past Due"))
					$pastDue = true;
				
				$arr[] = array("id"=>$bid,"start"=>$start,"stop"=>$stop,"headcount"=>$hc,"total"=>$tc,"currency"=>$cur,"status"=>$st,"user"=>$user,"pastDue"=>$pastDue,"retainable"=>false,"timezone"=>$timezone);
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	for ($i = 0; $i < count($arr); $i++)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT viewed_by_venue FROM booking_messages WHERE bookingid = ?"))
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
		
		// set 'doublebooked' = true if there is a booking conflict
		if (IsDoubleBooked(null,$arr[$i]['id']))
			$arr[$i]['doublebooked'] = true;
		
		// set 'retainable' = true if the booking is past due and it was approved by the venue at some point
		if ($arr[$i]['pastDue'])
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT COUNT(*) FROM booking_status WHERE status = 'Pending Payment' AND bookingid = ?"))
			{
				$stmt->bind_param("i",$arr[$i]['id']);
				$stmt->execute();
				$stmt->bind_result($c);
				if($stmt->fetch())
					if ($c > 0) $arr[$i]['retainable'] = true;
				$GLOBALS['db']->CloseConn();
			}
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(DISTINCT starttime,stoptime) FROM booking_resources WHERE bookingid = ?"))
		{		
			$stmt->bind_param("i",$arr[$i]['id']);
			$stmt->execute();
			$stmt->bind_result($c);
			if ($stmt->fetch())
			{
				if ($c > 1)
					$arr[$i]['multiple'] = true;
			}
			$GLOBALS['db']->CloseConn();
		}
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(DISTINCT starttime,stoptime), resourceid FROM booking_resources WHERE bookingid = ?"))
		{		
			$stmt->bind_param("i",$arr[$i]['id']);
			$stmt->execute();
			$stmt->bind_result($c,$resourceid);
			if ($stmt->fetch())
			{
				$arr[$i]['resourceids'] =$resourceid;	
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	return $arr;
}

function GetVenueRefunds($vid)
{
	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $vid)
			if ($v['role'] & 16 || $v['role'] & 4 || $v['role'] & 2)
				$auth = true;
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
		
	if (!$auth)
		return "You are not authorized to view this venue's bookings";
	
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid, CONCAT(u.firstname,' ',u.lastname), b.start, b.stop, b.total_cost, p.amount, v.currency FROM payments AS p LEFT JOIN booking_payments AS bp ON bp.paymentid = p.paymentid LEFT JOIN booking AS b ON b.bookingid = bp.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE p.status = 'pending_refund' AND b.venueid = ?"))
	{	
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($id,$u,$start,$stop,$total,$refund,$cur);
		while ($stmt->fetch())
		{			
			$arr[] = array("id"=>$id,"user"=>$u,"start"=>$start,"stop"=>$stop,"total"=>$total,"currency"=>$cur,"refund"=>$refund);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetVenueMessages($vid)
{
	$arr = array();
	// sender type subject tmestamp
	
	/*$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT m.bookingid, MAX(isvenue), v.name, MAX(m.timestamp), i.name, b.start, v.timezone FROM booking_messages AS m LEFT JOIN booking AS b ON b.bookingid = m.bookingid LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE m.viewed_by_venue = 0 AND b.venueid = ? GROUP BY m.bookingid, v.name, i.name, b.start, v.timezone"))
	{	
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($b,$is,$vname,$time,$name,$start,$tz);
		while ($stmt->fetch())
		{
			$sender = $vname;
			switch ($is)
			{
				case 0:
					$sender = $name;
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
	if ($stmt->prepare("SELECT m.convoid, CONCAT(u.firstname,' ',u.lastname,' (',u.email,')'), title, MAX(newforv), MAX(d.timestamp) FROM messages AS m LEFT JOIN messages_detail AS d ON d.convoid = m.convoid LEFT JOIN users AS u ON u.userid = m.userid WHERE m.venueid = ? AND m.deletedv != 1 GROUP BY m.convoid,CONCAT(u.firstname,' ',u.lastname,' (',u.email,')'),title"))
	{	
		$stmt->bind_param("i",$vid);
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

function GetVenueUpcomingEvents($id, $date)
{
	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $id)
			if ($v['role'] & 16 || $v['role'] & 4 || $v['role'] & 2)
				$auth = true;
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
	
	if (!$auth)
		return "You are not authorized to view this venue's bookings";
	
	$arr = array();
	$tz = "America/Los_Angeles";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT timezone FROM venues WHERE venueid = ?"))
	{	
		$stmt->bind_param("i",$id);
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
	if ($stmt->prepare("SELECT b.bookingid, i.name, b.start, b.stop, b.total_cost, v.currency FROM booking AS b LEFT JOIN venues AS v ON v.venueid=b.venueid LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid WHERE b.venueid = ? AND b.start <= ? AND b.stop >= ? AND (b.status = 'Paid' OR b.status = 'Pending Payment' OR b.status = 'Pending Approval' OR b.status = 'Imported' OR b.status = 'Past Due') ORDER BY b.start ASC LIMIT 0,20"))
	{	
		$s1 = $d2->getTimestamp();
		$s2 = $d->getTimestamp();
		
		$stmt->bind_param("iii",$id,$s1,$s2);
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

function GenerateSalesReport($venueid,$start,$stop)
{
	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $venueid)
			if ($v['role'] & 16 || $v['role'] & 8)
				$auth = true;
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
	
	if (!$auth)
		return "You are not authorized to view this venue's bookings";
	
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid, CONCAT(u.firstname,' ',u.lastname), b.start, b.stop, b.total_cost, v.currency, b.status, v.timezone FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.venueid = ? AND b.start >= ? AND b.start < ? ORDER BY b.start DESC"))
	{	
		$stmt->bind_param("iii",$venueid,$start,$stop);
		$stmt->execute();
		$stmt->bind_result($bid,$name,$start,$stop,$total,$cur,$status,$timezone);
		while ($stmt->fetch())
		{
			$arr[] = array("date"=>$start,"bookingID"=>$bid,"customer"=>$name,"duration"=>$stop-$start,"total"=>$total,"currency"=>$cur,"income"=>0,"status"=>$status,"timezone"=>$timezone);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	for ($i=0; $i<count($arr); $i++)
	{		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT SUM(p.amount) FROM payments AS p LEFT JOIN booking_payments AS b ON b.paymentid = p.paymentid WHERE b.bookingid = ? AND (p.status = 'processed' || p.status = 'pending_refund' || p.status = 'refunded')"))
		{	
			$stmt->bind_param("i",$arr[$i]['bookingID']);
			$stmt->execute();
			$stmt->bind_result($income);
			while ($stmt->fetch())
			{
				$arr[$i]['income'] = $income;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT SUM(fee) FROM booking_adjustments WHERE bookingid = ? AND (name = 'booking_fee' OR name = 'booking_fee_tax')"))
		{	
			$stmt->bind_param("i",$arr[$i]['bookingID']);
			$stmt->execute();
			$stmt->bind_result($fee);
			while ($stmt->fetch())
			{
				$arr[$i]['total'] -= $fee;
				$arr[$i]['income'] = $income - $fee;
				if ($arr[$i]['income'] < 0)
					$arr[$i]['income'] = 0;
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	return $arr;
}

function ImportBookingForm($vid, $name, $desc, $start, $stop, $price, $size, $cname, $cemail)
{
	$name = Sanitize($name);
	$desc = Sanitize($name);
	$cname = Sanitize($cname);
	$cemail = Sanitize($cemail);
	
	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $vid)
			if ($v['role'] & 16 || $v['role'] & 4)
				$auth = true;
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
	
	if (!$auth)
		return "You are not authorized to view this venue's bookings";
	
	$result = "There was an error inserting this booking";
	$bid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("INSERT INTO booking (venueid,userid,timestamp,start,stop,headcount,total_cost,total_deposit,full_due,isnew,status) VALUES (?,0,UNIX_TIMESTAMP(),?,?,?,?,0,UNIX_TIMESTAMP(),0,'Imported')"))
	{	
		$stmt->bind_param("iiiid",$vid,$start,$stop,$size,$price);
		$stmt->execute();
		if ($stmt->affected_rows)
			$bid = $stmt->insert_id;
		
		$GLOBALS['db']->CloseConn();
	}
	
	if ($bid)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_info (bookingid,name,description,contact_name,contact_email) VALUES (?,?,?,?,?)"))
		{	
			$stmt->bind_param("issss",$bid,$name,$desc,$cname,$cemail);
			$stmt->execute();
			if ($stmt->affected_rows)
				$result = "success";
			
			$GLOBALS['db']->CloseConn();
		}
	}
	
	return $result;
}

function ContactDump($vid)
{
	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $vid)
			if ($v['role'] & 16 || $v['role'] & 4 || $v['role'] & 2)
				$auth = true;
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
	
	if (!$auth)
		return "You are not authorized to view this venue's contact information";
	
	$result = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("select u.email, u.firstname, u.lastname, v.currency, COUNT(*), MAX(b.start), SUM(total_cost) FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid=b.userid WHERE b.venueid = ? AND (b.status = 'Paid' OR b.status = 'Pending Payment' OR b.status = 'Past Due') GROUP BY u.email, u.firstname, u.lastname, v.currency"))
	{	
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($e,$f,$l,$cur,$c,$s,$t);
		while ($stmt->fetch())
		{
			if ($c > 0)
				$result[] = array("email"=>$e,"firstname"=>$f,"lastname"=>$l,"bookings"=>$c,"last"=>$s,"total"=>$t,"currency"=>$cur);
		}
		
		$GLOBALS['db']->CloseConn();
	}
	
	return $result;
}

function GetVenueSubscription($vid)
{
	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $vid)
			if ($v['role'] & 16)
				$auth = true;
	//echo "Auth = ".$auth." Vid = ".$vid;		
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
	//echo "Auth = ".$auth." Vid = ".$vid;		
	if (!$auth)
		return "You are not authorized to make this request";
	
	$errmsg = "There was an error loading this venue's subscription information, please contact customer support for assistance";
	$cus = null;
	$sub = null;
	$sta = null;
	$created = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT customerid, subid, s.status, v.timestamp FROM venues AS v LEFT JOIN venues_subscriptions AS s ON s.venueid=v.venueid WHERE v.venueid = ?"))
	{	
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($c,$s,$st,$t);
		if ($stmt->fetch())
		{
			$cus = $c;
			$sub = $s;
			$sta = $st;
			$created = $t;
		}

		//  echo "\r\ncus=".$cus;
		//  echo "\r\nsub=".$sub;
		//  echo "\r\nsta=".$sta;
		//  echo "\r\ncreated=".$created;
		// exit();
		$GLOBALS['db']->CloseConn();
	}
	
	$result = array();
	$result['plan'] = "Not subscribed";
	$result['cost'] = "";
	$result['status'] = "canceled";
	$result['renews'] = 0;
	
	if ($cus && $sub)
	{
		try {
			require_once('php/stripe-php-2.3.0/init.php');
			\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
			$customer = \Stripe\Customer::retrieve($cus);
			$result['email'] = $customer->email;
			//echo "Email = ".$result['email'] ;
			if (count($customer->sources->data) > 0)
			{
				$result['last4'] = $customer->sources->data[0]->last4;
				$result['exp'] = $customer->sources->data[0]->exp_month."/".$customer->sources->data[0]->exp_year;
				$result['brand'] = $customer->sources->data[0]->brand;
			}
			
			for ($i2=0; $i2<count($customer['subscriptions']['data']); $i2++)
			{
				if ($customer['subscriptions']['data'][$i2]['id'] == $sub)
				{
					$pid = $customer['subscriptions']['data'][$i2]['plan']['id'];
					$result['balance'] = $customer->account_balance;
					$result['plan'] = $customer['subscriptions']['data'][$i2]['plan']['name'];
					$result['cost'] = $customer['subscriptions']['data'][$i2]['plan']['amount'];
					$result['status'] = $customer['subscriptions']['data'][$i2]['status'];
					$result['renews'] = $customer['subscriptions']['data'][$i2]['current_period_end'];
					$result['deactivates'] = $customer['subscriptions']['data'][$i2]['current_period_start'] + 604800; // 7 days
					
					if ($customer['subscriptions']['data'][$i2]['cancel_at_period_end'])
						$result['cancelsatend'] = true;
					
					if ($customer['subscriptions']['data'][$i2]['discount'] && 
						$customer['subscriptions']['data'][$i2]['discount']['coupon'] &&
						$customer['subscriptions']['data'][$i2]['discount']['coupon']['valid'])
					{
						if ($customer['subscriptions']['data'][$i2]['discount']['coupon']['percent_off'])
							$result['discounted'] = $result['cost'] * ((100 - $customer['subscriptions']['data'][$i2]['discount']['coupon']['percent_off']) / 100);
						else if ($customer['subscriptions']['data'][$i2]['discount']['coupon']['amount_off'])
							$result['discounted'] = $result['cost'] - $customer['subscriptions']['data'][$i2]['discount']['coupon']['amount_off'];
					}
					
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("UPDATE venues_subscriptions SET plan = ?, status = ?, renews = ? WHERE venueid = ?"))
					{
					//echo "Testing...";	
					//exit();
						$stmt->bind_param("ssii",$pid,$result['status'],$result['renews'],$vid);
						$stmt->execute();
						$GLOBALS['db']->CloseConn();
					}
				}
			}
			
			$invoices = \Stripe\Invoice::all(array("customer"=>$cus,"limit" => 100));
			if (count($invoices['data']) > 0)
			{
				$result['history'] = array();
				for ($i=0; $i<count($invoices['data']); $i++)
				{
					$inv = array();
					if ($invoices['data'][$i]['charge'])
					{
						$charge = \Stripe\Charge::retrieve($invoices['data'][$i]['charge']);
						if ($charge->captured)
						{
							$inv['charge'] = array(
								"source"=>$charge['source']['funding'],
								"brand"=>$charge['source']['brand'],
								"last4"=>$charge['source']['last4'],
								"amount"=>$charge['amount']
							);
							
							if ($charge['amount'] < $invoices['data'][$i]['total'])
								$inv['charge'][] = array(
									"source"=>"balance",
									"brand"=>"",
									"last4"=>"",
									"amount"=>$invoices['data'][$i]['total'] - $charge['amount']
								);
						}
					}
					
					$inv['lines'] = array();
					$inv['date'] = $invoices['data'][$i]['date'];
					$inv['amount'] = $invoices['data'][$i]['total'];
					if ($invoices['data'][$i]['total'] < $invoices['data'][$i]['subtotal'])
						$inv['discount'] = $invoices['data'][$i]['total'] - $invoices['data'][$i]['subtotal'];
					$inv['status'] = "Unpaid";
					
					if ($invoices['data'][$i]['paid'])
						$inv['status'] = "Paid";
					if ($invoices['data'][$i]['total'] < 0)
						$inv['status'] = "Credit";
					if ($invoices['data'][$i]['forgiven'] == true)
						$inv['status'] = "Forgiven";
					
					for ($i2=0; $i2<count($invoices['data'][$i]['lines']['data']); $i2++)
					{
						$item = $invoices['data'][$i]['lines']['data'][$i2];
						
						$inv['lines'][] = array(
							"amount"=>$item['amount'],
							"description"=>($item['description'] ? $item['description'] : $item['plan']['name'])
						);
					}
					
					$result['history'][] = $inv;
				}
			}
			
		} catch (Exception $e) {
			//return $e->getJsonBody();
			if ($e instanceof \Stripe\Error\InvalidRequest)
			{
				$body = $e->getJsonBody();
				$err  = $body['error']['message'];
				error_log($err);
				return $errmsg;
			}
			else
			{
				return $errmsg;
			}
		}
	}
	
	return $result;
}

function SetVenueSubscription($vid, $pid, $email, $token)
{
	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $vid)
			if ($v['role'] & 16)
				$auth = true;
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
	
	if (!$auth)
		return "You are not authorized to make this request";
	
	$errmsg = "There was an error saving this venue's subscription information, please contact customer support for assistance";
	$cus = null;
	$sub = null;
	$plan = null;
	$sta = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT customerid, subid, plan, status FROM venues_subscriptions WHERE venueid = ?"))
	{	
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($c,$s,$p,$st);
		if ($stmt->fetch())
		{
			$cus = $c;
			$sub = $s;
			$plan = $p;
			$sta = $st;
		}
		
		$GLOBALS['db']->CloseConn();
	}
	
	try {
		require_once('php/stripe-php-2.3.0/init.php');
		\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
		$customer = null;
		
		if ($cus && $sub)
		{
			$customer = \Stripe\Customer::retrieve($cus);
			if ($email)
				$customer->email = $email;
			if ($token)
				$customer->source = $token;
			if ($email || $token)
				$customer = $customer->save();
			
			if ($pid != "dontset")
			{
				try {
					$subscription = $customer->subscriptions->retrieve($sub);
					if ($subscription)
					{
						$subscription->prorate = true;
						if ($subscription->plan != $pid && $subscription->discount)
							$subscription->deleteDiscount();
						$subscription->plan = $pid;
						$subscription->trial_end = "now";
						$subscription->save();	
					}
				}
				catch (Exception $e)
				{
					if ($e instanceof \Stripe\Error\InvalidRequest)
					{
						$body = $e->getJsonBody();
						if (strpos($body['error']['message'],'does not have a subscription') > 0)
						{
							$subscription = $customer->subscriptions->create(array("plan"=>$pid));
							$stmt = $GLOBALS['db']->stmt_init();
							if ($stmt->prepare("UPDATE venues_subscriptions SET plan = ?, subid = ?, status = ? WHERE venueid = ?"))
							{
								$stmt->bind_param("sssi",$pid,$subscription->id,$subscription->status,$vid);
								$stmt->execute();						
								$GLOBALS['db']->CloseConn();
							}
						}
						else throw $e;
					}
				}

				$customer = \Stripe\Customer::retrieve($cus);
			}
		}
		else
		{
			$customer = \Stripe\Customer::create(array(
				"description"=>"venueid: ".$vid,
				"source"=>$token,
				"email"=>$email,
				"plan"=>$pid
			));
		}
		
		$c = array("cid"=>$customer->id,"plan"=>$pid);
		for ($i=0; $i<count($customer['subscriptions']['data']); $i++)
		{
			if ($customer['subscriptions']['data'][$i]['plan']['id'] == $pid)
			{
				$c['status'] = $customer['subscriptions']['data'][$i]['status'];
				$c['subid'] = $customer['subscriptions']['data'][$i]['id'];
				$c['renews'] = $customer['subscriptions']['data'][$i]['current_period_end'];
				break;
			}
		}
		
		if (isset($c['status']))
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE venues_subscriptions SET customerid = ?, subid = ?, plan = ?, status = ?, renews = ? WHERE venueid = ?"))
			{	
				$stmt->bind_param("ssssii",$c['cid'],$c['subid'],$c['plan'],$c['status'],$c['renews'],$vid);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			return "success";
		}			
		
		if ($pid == "dontset")
			return "success";
		
	} catch (Exception $e) {
		if ($e instanceof \Stripe\Error\InvalidRequest)
		{
			$body = $e->getJsonBody();
			$err  = $body['error']['message'];
			error_log($err);
			return $err;
		}
		else
		{
			return $err;
		}
	}
	
	return $errmsg;
}

function CancelVenueSubscription($vid,$reason)
{
	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $vid)
			if ($v['role'] & 16)
				$auth = true;
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
	
	if (!$auth)
		return "You are not authorized to make this request";
	
	$errmsg = "There was an error cancelling this venue's subscription, please contact customer support for assistance";
	$cus = null;
	$sub = null;
	$sta = null;
	$created = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT customerid, subid, s.status, v.timestamp FROM venues AS v LEFT JOIN venues_subscriptions AS s ON s.venueid=v.venueid WHERE v.venueid = ?"))
	{	
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($c,$s,$st,$t);
		if ($stmt->fetch())
		{
			$cus = $c;
			$sub = $s;
			$sta = $st;
			$created = $t;
		}
		
		$GLOBALS['db']->CloseConn();
	}
	
	if ($cus && $sub)
	{
		try {
			require_once('php/stripe-php-2.3.0/init.php');
			\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
			
			$customer = \Stripe\Customer::retrieve($cus);
			$subscription = $customer->subscriptions->retrieve($sub)->cancel(array("at_period_end"=>true));
		} catch (Exception $e) {
			if ($e instanceof \Stripe\Error\InvalidRequest)
			{
				$body = $e->getJsonBody();
				$err  = $body['error']['message'];
				error_log($err);
				return $errmsg;
			}
			else
			{
				return $errmsg;
			}
		}
	}
	else $sta = "canceled";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("UPDATE venues_subscriptions SET status = ? WHERE venueid = ?"))
	{	
		$stmt->bind_param("si",$sta,$vid);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
		
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("INSERT INTO subscription_cancel (venueid, reason, timestamp) VALUES (?,?,UNIX_TIMESTAMP())"))
	{	
		$stmt->bind_param("is",$vid,$reason);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
	}
	
	return "success";
}

function GetVenueNotifications($vid)
{
	$auth = false;
	foreach ($_SESSION['venueRights'] as $v)
		if ($v['venueid'] == $vid)
			if ($v['role'] & 16 || $v['role'] & 4)
				$auth = true;
	if ($_SESSION['siteRole'] == 999)
		$auth = true;
	
	if (!$auth)
		return "You are not authorized to make this request";
	
	$arr = array();
	$canceled = false;
	
	// check for subscription plan notifications
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT status, renews - UNIX_TIMESTAMP() FROM venues_subscriptions WHERE venueid = ?"))
	{	
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($s,$days);
		if ($stmt->fetch())
		{
			if ($s == "past_due" || $s == "unpaid")
				$arr[] = array("class"=>"alert-info","message"=>"Your subscription payment is past due and your account will be restricted soon, <a data-partial=true href='/dashboard#subscription'>Add a Payment Method</a>");
			
			if ($s == "canceled")
			{
				$arr[] = array("class"=>"alert-danger","message"=>"Your subscription plan is not active, <a data-partial=true href='/dashboard#subscription'>Renew Your Subscription</a>");
				$canceled = true;
			}
			
			if ($s == "trialing")
			{
				$days = floor($days / 86400);
				if ($days < 0) $days = 0;
				
				$arr[] = array("class"=>"alert-grey","message"=>"You have ".$days." days remaining in your free trial, <a data-partial=true href='/dashboard#subscription'>Upgrade Now</a>");
			}
		}
		
		$GLOBALS['db']->CloseConn();
	}
	
	if (!$canceled)
	{
		// check for guides to be completed
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT guideid FROM guides WHERE venueid = ? AND status != 'done'"))
		{	
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($i);
			if ($stmt->fetch())
			{
				$arr[] = array("guide"=>$i);
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	// print_r($arr);
	// exit();
	return $arr;
}

?>