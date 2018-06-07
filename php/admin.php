<?php

function GetVenuesPendingApproval()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT venueid, name, shorturl, timestamp FROM venues WHERE status = 'pending_review'"))
	{
		$stmt->execute();
		$stmt->bind_result($id, $name, $url, $time);
		while($stmt->fetch())
		{
			$arr[] = array("id"=>$id, "name"=>Sanitize($name), "url"=>$url, "created"=>$time);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function ApproveVenue($vid)
{
	require_once('php/email.php');
	
	$result = "There was an error approving this venue";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("UPDATE venues SET status = 'active' WHERE venueid = ?"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		if ($stmt->affected_rows)
			$result = "success";
		$GLOBALS['db']->CloseConn();
	}
	
	if ($result == "success")
		SendVenueApprovedMessage($vid);
		
	return $result;
}

function DenyVenue($vid)
{
	$result = "There was an error denying this venue";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("UPDATE venues SET status = 'new' WHERE venueid = ?"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$GLOBALS['db']->CloseConn();
		if ($stmt->affected_rows)
			$result = "success";
	}
	
	return $result;
}

function GetAdminConfigList()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT name, venueid, timezone FROM venues ORDER BY name ASC"))
	{	
		$stmt->execute();
		$stmt->bind_result($n,$i,$t);
		while ($stmt->fetch())
		{
			$arr[] = array("id"=>$i,"name"=>$n,"tz"=>$t);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetAdminOverviewStats()
{
	$arr = array();

	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT COUNT(*), SUM(CASE WHEN visibility = 'public' THEN 1 ELSE 0 END) AS pub FROM venues"))
	{	
		$stmt->execute();
		$stmt->bind_result($c,$p);
		if ($stmt->fetch())
		{
			$arr['totalVenues'] = $c;
			$arr['publicVenues'] = $p;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT COUNT(*), SUM(CASE WHEN LENGTH(fbid) > 4 THEN 1 ELSE 0 END) AS fb, SUM(CASE WHEN LENGTH(gid) > 4 THEN 1 ELSE 0 END) AS g, SUM(CASE WHEN LENGTH(twid) > 4 THEN 1 ELSE 0 END) AS t FROM users"))
	{	
		$stmt->execute();
		$stmt->bind_result($c,$f,$g,$t);
		while ($stmt->fetch())
		{
			$arr['totalUsers'] = $c;
			$arr['fbUsers'] = $f;
			$arr['gUsers'] = $g;
			$arr['tUsers'] = $t;
		}
		$GLOBALS['db']->CloseConn();
	}
		
	return $arr;
}

function AdminGetUsers()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT userid, email, IFNULL(CONCAT(firstname, ' ', lastname),''), phone, role, timestamp, lastlogin FROM users ORDER BY timestamp DESC"))
	{	
		$stmt->execute();
		$stmt->bind_result($u,$e,$n,$p,$r,$t,$l);
		while ($stmt->fetch())
		{
			$arr[] = array("userid"=>$u,"email"=>$e,"name"=>$n,"phone"=>$p,"role"=>$r,"time"=>$t,"login"=>$l);
		}
		$GLOBALS['db']->CloseConn();
	}
		
	return $arr;
}

function AdminGetVenues()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT v.venueid, v.name, v.phone, v.visibility, v.timestamp, v.status, CONCAT(u.firstname,' ',u.lastname), u.userid, s.plan, s.status, (SELECT COUNT(*) FROM booking b WHERE b.venueid = v.venueid), g.status
						FROM venues v LEFT JOIN users u ON u.userid = v.userid LEFT JOIN venues_subscriptions s ON s.venueid = v.venueid LEFT JOIN guides g ON g.venueid = v.venueid AND g.name = 'venueOnboarding'
						ORDER BY v.venueid DESC"))
	{
		$stmt->execute();
		$stmt->bind_result($vid,$name,$phone,$vis,$time,$status,$user,$uid,$plan,$pstat,$cnt,$guide);
		while ($stmt->fetch())
		{
			$gstat = "0%";
			if ($guide == "done")
				$gstat = "100%";
			else
			{
				$j = json_decode($guide,true);
				$total = 0;
				$done = 0;
				for ($i=0; $i<count($j); $i++)
				{
					if (!isset($j[$i]['taskWeight'])) $j[$i]['taskWeight'] = 1;
					if ($j[$i]['taskDone'] > 0)
						$done += $j[$i]['taskWeight'];
					$total += $j[$i]['taskWeight'];
				}
				$gstat = @floor(100 * $done / $total) . "%";
			}
			$arr[] = array(
				"venueid"=>$vid,
				"owner"=>$user,
				"uid"=>$uid,
				"name"=>$name,
				"phone"=>$phone,
				"visibility"=>$vis,
				"bookings"=>$cnt,
				"created"=>$time,
				"status"=>$status,
				"plan"=>$plan,
				"plan-status"=>$pstat,
				"guide"=>$gstat);
		}
		$GLOBALS['db']->CloseConn();
	}
		
	return $arr;
}

function RunOneMinEvents()
{
	// send warnings about payments/approval being needed
	
	require_once('php/book.php');
	
	
	/*
	// No longer needed as of 9/29/2015
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid, b.venueid FROM booking AS b WHERE b.status = 'Pending Deposit' AND ((UNIX_TIMESTAMP() - b.timestamp) > 7200 OR UNIX_TIMESTAMP() > (b.start + 7200))"))
	{	
		$stmt->execute();
		$stmt->bind_result($id,$vid);
		while ($stmt->fetch())
		{	
			$arr[] = array("bid"=>$id,"vid"=>$vid);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($arr as $a)
		UpdateBookingStatus($a['bid'],$a['vid'],"Deposit Time Expired",null,2);
	*/
	
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid, b.venueid FROM booking AS b LEFT JOIN booking_status AS s ON s.bookingid = b.bookingid AND s.status = b.status WHERE b.status = 'Pending Approval' AND ((UNIX_TIMESTAMP() - s.timestamp) > 259200 OR UNIX_TIMESTAMP() > b.stop + 7200)"))
	{	
		// cancel if not approved within 72 hours
		$stmt->execute();
		$stmt->bind_result($id,$vid);
		while ($stmt->fetch())
		{			
			$arr[] = array("bid"=>$id,"vid"=>$vid);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($arr as $a)
		UpdateBookingStatus($a['bid'],$a['vid'],"Approval Expired",null,2);
	
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid, b.venueid, (UNIX_TIMESTAMP() - s.timestamp) FROM booking AS b LEFT JOIN booking_status AS s ON s.bookingid = b.bookingid AND s.status = b.status WHERE b.status = 'Pending Approval' AND s.notified IS NULL AND (UNIX_TIMESTAMP() - s.timestamp) > 172800"))
	{	
		$stmt->execute();
		$stmt->bind_result($id,$vid,$l);
		while ($stmt->fetch())
		{
			$left = 259200 - $l; // approval required within 72 hours
			$left = floor($left / 3600);
			if ($left < 0) $left = 0;
			$arr[] = array("bid"=>$id,"vid"=>$vid,"left"=>$left);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($arr as $a)
		UpdateBookingStatus($a['bid'],$a['vid'],"approval_warning",$a['left'],2);
		
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid, b.venueid, (b.full_due - UNIX_TIMESTAMP()) FROM booking AS b LEFT JOIN booking_status AS s ON s.bookingid = b.bookingid AND s.status = b.status WHERE (b.status = 'Pending Payment') AND s.notified IS NULL AND (UNIX_TIMESTAMP() - b.full_due) < 86400 AND NOT ((UNIX_TIMESTAMP() - b.timestamp) < 7200 AND (UNIX_TIMESTAMP() - b.start) < 3600)"))
	{	
		$stmt->execute();
		$stmt->bind_result($id,$vid,$left);
		while ($stmt->fetch())
		{			
			$arr[] = array("bid"=>$id,"vid"=>$vid,"left"=>($left > 0 ? floor($left / 3600) : 0));
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($arr as $a)
		UpdateBookingStatus($a['bid'],$a['vid'],"payment_warning",$a['left'],2);
	
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid, b.venueid, (UNIX_TIMESTAMP() - b.full_due) FROM booking AS b LEFT JOIN booking_status AS s ON s.bookingid = b.bookingid AND s.status = 'Payment Pending' WHERE s.notified IS NULL AND (b.status = 'Pending Payment') AND (UNIX_TIMESTAMP() - b.full_due) > 7200 AND NOT ((UNIX_TIMESTAMP() - b.timestamp) < 7200 AND (UNIX_TIMESTAMP() - b.start) < 3600)"))
	{	
		$stmt->execute();
		$stmt->bind_result($id,$vid,$timeleft);
		while ($stmt->fetch())
		{	
			$arr[] = array("bid"=>$id,"vid"=>$vid,"timeleft"=>$timeleft);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($arr as $a)
		UpdateBookingStatus($a['bid'],$a['vid'],"Past Due",null,2);
	
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT bookingid FROM booking b WHERE (b.status = 'Pending Payment' OR b.status = 'Pending Deposit')"))
	{	
		$stmt->execute();
		$stmt->bind_result($id);
		while ($stmt->fetch())
		{			
			$arr[] = $id;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($arr as $a)
		CheckPromoExpiration($a);
}

function CleanupPictures()
{
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT photo FROM venues_photos AS p LEFT JOIN venues AS v ON v.venueid = p.venueid WHERE v.status != 'deleted'"))
	{	
		$stmt->execute();
		$stmt->bind_result($p);
		while ($stmt->fetch())
		{			
			$arr[] = "assets/content/".$p;
		}
		$GLOBALS['db']->CloseConn();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT photo FROM addons_photos AS p LEFT JOIN addons AS a ON a.addonid = p.addonid WHERE a.status != 'deleted'"))
	{	
		$stmt->execute();
		$stmt->bind_result($p);
		while ($stmt->fetch())
		{			
			$arr[] = "assets/content/".$p;
		}
		$GLOBALS['db']->CloseConn();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT photo FROM resources_photos AS p LEFT JOIN resources AS r ON r.resourceid = p.resourceid WHERE r.status = 'new'"))
	{	
		$stmt->execute();
		$stmt->bind_result($p);
		while ($stmt->fetch())
		{			
			$arr[] = "assets/content/".$p;
		}
		$GLOBALS['db']->CloseConn();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT photo FROM events_photos AS p LEFT JOIN events AS e ON e.eventid = p.eventid WHERE e.status != 'deleted'"))
	{	
		$stmt->execute();
		$stmt->bind_result($p);
		while ($stmt->fetch())
		{			
			$arr[] = "assets/content/".$p;
		}
		$GLOBALS['db']->CloseConn();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT photo FROM tickets_photos"))
	{	
		$stmt->execute();
		$stmt->bind_result($p);
		while ($stmt->fetch())
		{			
			$arr[] = "assets/content/".$p;
		}
		$GLOBALS['db']->CloseConn();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT photo FROM menus_items WHERE status != 'deleted'"))
	{	
		$stmt->execute();
		$stmt->bind_result($p);
		while ($stmt->fetch())
		{			
			$arr[] = "assets/content/".$p;
		}
		$GLOBALS['db']->CloseConn();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT name FROM booking_files"))
	{	
		$stmt->execute();
		$stmt->bind_result($p);
		while ($stmt->fetch())
		{			
			$arr[] = "assets/content/".$p;
		}
		$GLOBALS['db']->CloseConn();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT contract FROM venues"))
	{	
		$stmt->execute();
		$stmt->bind_result($p);
		while ($stmt->fetch())
		{			
			$arr[] = "assets/content/".$p;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$arr2 = glob("assets/content/*.*",GLOB_MARK);
	
	for ($i=0; $i<count($arr); $i++)
		$arr[$i] = urldecode($arr[$i]);
		
	if (count($arr2) > 0)
	{
		for ($i=0; $i<count($arr2); $i++)
		{
			if (!in_array($arr2[$i], $arr) && strpos($arr2[$i],"placeholder-") === false)
			{
				unlink($arr2[$i]);
				unlink(str_replace("/content/","/content/thumbnail/",$arr2[$i]));
			}
		}
	}
	
	$arr2 = glob("assets/content/*.*",GLOB_MARK);
	$arr3 = glob("assets/content/thumbnail/*.*",GLOB_MARK);
	
	if (count($arr3) > 0)
	{
		for ($i=0; $i<count($arr3); $i++)
		{
			if (!in_array(str_replace("/content/thumbnail/","/content/",$arr3[$i]), $arr2))
			{
				unlink($arr3[$i]);
			}
		}
	}
}

function SyncStripePlans()
{
	require_once('php/email.php');
	
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT s.venueid, s.customerid, s.subid, s.status, (UNIX_TIMESTAMP() - v.timestamp) FROM venues_subscriptions AS s LEFT JOIN venues AS v ON v.venueid = s.venueid"))
	{	
		$stmt->execute();
		$stmt->bind_result($v,$c,$s,$st,$a);
		while ($stmt->fetch())
		{			
			$arr[] = array("venueid"=>$v,"cid"=>$c,"subid"=>$s,"status"=>$st,"age"=>$a);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	for ($i=0; $i<count($arr); $i++)
	{
		try {
			require_once('php/stripe-php-2.3.0/init.php');
			\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
			
			$arr[$i]['plan'] = "Not subscribed";
			$arr[$i]['newstatus'] = "canceled";
			$arr[$i]['renews'] = 0;
			
			$customer = \Stripe\Customer::retrieve($arr[$i]['cid']);		
			for ($i2=0; $i2<count($customer['subscriptions']['data']); $i2++)
			{
				if ($customer['subscriptions']['data'][$i2]['id'] == $arr[$i]['subid'])
				{
					$arr[$i]['plan'] = $customer['subscriptions']['data'][$i2]['plan']['id'];
					$arr[$i]['newstatus'] = $customer['subscriptions']['data'][$i2]['status'];
					$arr[$i]['renews'] = $customer['subscriptions']['data'][$i2]['current_period_end'];
					break;
				}
			}
			
			if (isset($arr[$i]['newstatus']))
			{
				// trialing, active, past_due, canceled, unpaid
				
				if ($arr[$i]['newstatus'] == "active" && $arr[$i]['status'] == "trialing")
					;; // send email saying trial has ended?
				
				if (($arr[$i]['newstatus'] == "past_due" && $arr[$i]['status'] != "past_due") ||
					($arr[$i]['newstatus'] == "unpaid" && $arr[$i]['status'] != "unpaid"))
					SendSubscriptionPaymentDueMessage($arr[$i]['venueid']);
				
				if ($arr[$i]['newstatus'] == "canceled" && $arr[$i]['status'] != "canceled")
					SendSubscriptionCancelledMessage($arr[$i]['venueid']);
				
			}
		} catch (Exception $e) {
			if ($e instanceof \Stripe\Error\InvalidRequest)
			{
				$body = $e->getJsonBody();
				$err  = $body['error']['message'];
				error_log($err);
			}
		}
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("UPDATE venues_subscriptions SET plan = ?, status = ?, renews = ? WHERE venueid = ?"))
	{	
		for ($i=0; $i<count($arr); $i++)
		{
			if (isset($arr[$i]['plan']) && isset($arr[$i]['newstatus']) && isset($arr[$i]['venueid']))
			{
				$stmt->bind_param("ssii",$arr[$i]['plan'],$arr[$i]['newstatus'],$arr[$i]['renews'],$arr[$i]['venueid']);
				$stmt->execute();
			}
		}		
		$GLOBALS['db']->CloseConn();
	}
}

?>