<?php
require_once("functions.php");
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
			require_once('stripe-php-2.3.0/init.php');
			\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
			
			$arr[$i]['plan'] = "Not subscribed";
			$arr[$i]['newstatus'] = "canceled";
			$arr[$i]['renews'] = 0;
			
			$customer = \Stripe\Customer::retrieve($arr[$i]['cid']);		
			for ($i2=0; $i2<count($customer['subscriptions']['data']); $i2++)
			{
				//echo "==>".$customer['subscriptions']['data'][$i2]['id']."<br>";
				//echo "-->". $arr[$i]['subid']."<br>";
				if ($customer['subscriptions']['data'][$i2]['id'] == $arr[$i]['subid'])
				{
				echo "==>".$customer['subscriptions']['data'][$i2]['id']."<br>";
				//echo "-->". $arr[$i]['subid']."<br>";					
				echo "Plan:- ". $customer['subscriptions']['data'][$i2]['plan']['id']."<br>";					
				echo "New status:- ". $customer['subscriptions']['data'][$i2]['status']."<br>";				
				echo "Renews:- ". $customer['subscriptions']['data'][$i2]['current_period_end']."<br>";	
				echo "venueid:- ".$arr[$i]['venueid']."<br>";				
					$arr[$i]['plan'] = $customer['subscriptions']['data'][$i2]['plan']['id'];
					$arr[$i]['newstatus'] = $customer['subscriptions']['data'][$i2]['status'];
					$arr[$i]['renews'] = $customer['subscriptions']['data'][$i2]['current_period_end'];
					break;
				}

				$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("UPDATE venues_subscriptions SET plan = ?, status = ?, renews = ? WHERE venueid = ?"))
					{	
						//$stmt->bind_param("ssii",$pid,$result['status'],$result['renews'],$vid);
						//$stmt->execute();
						echo "Update"."<br>";
						$GLOBALS['db']->CloseConn();
					}
			}
			
			// if (isset($arr[$i]['newstatus']))
			// {
			// 	// trialing, active, past_due, canceled, unpaid
				
			// 	if ($arr[$i]['newstatus'] == "active" && $arr[$i]['status'] == "trialing")
			// 		;; // send email saying trial has ended?
				
			// 	if (($arr[$i]['newstatus'] == "past_due" && $arr[$i]['status'] != "past_due") ||
			// 		($arr[$i]['newstatus'] == "unpaid" && $arr[$i]['status'] != "unpaid"))
			// 		SendSubscriptionPaymentDueMessage($arr[$i]['venueid']);
				
			// 	if ($arr[$i]['newstatus'] == "canceled" && $arr[$i]['status'] != "canceled")
			// 		SendSubscriptionCancelledMessage($arr[$i]['venueid']);
				
			// }
		} catch (Exception $e) {
			if ($e instanceof \Stripe\Error\InvalidRequest)
			{
				$body = $e->getJsonBody();
				$err  = $body['error']['message'];
				error_log($err);
			}
		}
	}
	
// $arr = array();
// 	$stmt = $GLOBALS['db']->stmt_init();
// 	if ($stmt->prepare("SELECT s.venueid, s.customerid, s.subid, s.status, (UNIX_TIMESTAMP() - v.timestamp) FROM venues_subscriptions AS s LEFT JOIN venues AS v ON v.venueid = s.venueid"))
// 	{	
// 		$stmt->execute();
// 		$stmt->bind_result($v,$c,$s,$st,$a);
// 		while ($stmt->fetch())
// 		{			
// 			$arr[] = array("venueid"=>$v,"cid"=>$c,"subid"=>$s,"status"=>$st,"age"=>$a);
// 		}
// 		$GLOBALS['db']->CloseConn();
// 	}
	
// 	for ($i=0; $i<count($arr); $i++)
// 	{
// 		try {
// 			require_once('stripe-php-2.3.0/init.php');
// 			\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
			
// 			$arr[$i]['plan'] = "Not subscribed";
// 			$arr[$i]['newstatus'] = "canceled";
// 			$arr[$i]['renews'] = 0;
			
// 			$customer = \Stripe\Customer::retrieve($arr[$i]['cid']);		
// 			for ($i2=0; $i2<count($customer['subscriptions']['data']); $i2++)
// 			{
// 				if ($customer['subscriptions']['data'][$i2]['id'] == $arr[$i]['subid'])
// 				{
// 					$arr[$i]['plan'] = $customer['subscriptions']['data'][$i2]['plan']['id'];
// 					$arr[$i]['newstatus'] = $customer['subscriptions']['data'][$i2]['status'];
// 					$arr[$i]['renews'] = $customer['subscriptions']['data'][$i2]['current_period_end'];
// 					break;
// 				}
// 			}
			
// 			if (isset($arr[$i]['newstatus']))
// 			{
// 				// trialing, active, past_due, canceled, unpaid
				
// 				if ($arr[$i]['newstatus'] == "active" && $arr[$i]['status'] == "trialing")
// 					;; // send email saying trial has ended?
				
// 				if (($arr[$i]['newstatus'] == "past_due" && $arr[$i]['status'] != "past_due") ||
// 					($arr[$i]['newstatus'] == "unpaid" && $arr[$i]['status'] != "unpaid"))
// 					SendSubscriptionPaymentDueMessage($arr[$i]['venueid']);
				
// 				if ($arr[$i]['newstatus'] == "canceled" && $arr[$i]['status'] != "canceled")
// 					SendSubscriptionCancelledMessage($arr[$i]['venueid']);
				
// 			}
// 		} catch (Exception $e) {
// 			if ($e instanceof \Stripe\Error\InvalidRequest)
// 			{
// 				$body = $e->getJsonBody();
// 				$err  = $body['error']['message'];
// 				error_log($err);
// 			}
// 		}
// 	}
	
// 	$stmt = $GLOBALS['db']->stmt_init();
// 	if ($stmt->prepare("UPDATE venues_subscriptions SET plan = ?, status = ?, renews = ? WHERE venueid = ?"))
// 	{	
// 		for ($i=0; $i<count($arr); $i++)
// 		{
// 			if (isset($arr[$i]['plan']) && isset($arr[$i]['newstatus']) && isset($arr[$i]['venueid']))
// 			{
// 				$stmt->bind_param("ssii",$arr[$i]['plan'],$arr[$i]['newstatus'],$arr[$i]['renews'],$arr[$i]['venueid']);
// 				$stmt->execute();
// 			}
// 		}		
// 		$GLOBALS['db']->CloseConn();
// 	}

?>