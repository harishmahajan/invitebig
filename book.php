<?php

function CheckBookingAuth($id, $lvl)
{
	// 16 = Manage Venue = can do everything
	// 8 = View Financials = can only view the bookings and sales reporting
	// 4 = Manage Books = can only view and edit booking
	// 2 = View Books = can only bookings
	
	$uid = -1;
	$vid = -1;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT venueid, userid FROM booking WHERE bookingid = ?"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($v,$u);
		if ($stmt->fetch())
		{
			$uid = $u;
			$vid = $v;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (!isset($_SESSION['userid']) && (!isset($_SESSION['lastInsertedBooking']) || $_SESSION['lastInsertedBooking'] != $id))
		return false;
	
	$auth = false;
	
	if ($uid == $_SESSION['userid'])
		$auth = 1;
	
	if (isset($_SESSION['venueRights']))
	{
		foreach ($_SESSION['venueRights'] as $venue)
		{
			if ($venue['venueid'] == $vid) 
			{
				if ($venue['role'] & 16)
					$auth = 16;
				else if ($lvl == 2 && $venue['role'] & 4)
					$auth = 4;
				else if ($venue['role'] & $lvl)
					$auth = $lvl;
			}
		}
	}
	
	if (isset($_SESSION['siteRole']) && $_SESSION['siteRole'] == 999)
		$auth = 999;
	
	if (!$auth && isset($_SESSION['lastInsertedBooking']) && $_SESSION['lastInsertedBooking'] == $id)
		return true;
	
	return $auth;
}

function ConsolidateStatus($arr,$isTimeslot=false)
{
	// This function takes in a schedule of {start,stop,status}, combines blocks with the same
	//  status, and resolves 'busy' overlap
	for ($i=0; $i<count($arr); $i++)
	{
		if ($arr[$i]['status'] != "busy")
			continue;
		
		for ($i2=0; $i2<count($arr); $i2++)
		{
			if ($i2 == $i || $arr[$i2]['status'] == "busy")
				continue;
			
			if ($arr[$i2]['start'] < $arr[$i]['stop'] && $arr[$i2]['stop'] > $arr[$i]['start'])
			{
				if ($isTimeslot)
				{
					if ($i2 < $i)
						$i--;
					
					unset($arr[$i2]);
					$arr = array_values($arr);
					$i2--;
					continue;
				}
				else
				{
					if ($arr[$i2]['start'] < $arr[$i]['start'])
					{
						if ($arr[$i2]['stop'] > $arr[$i]['stop'])
							$arr[] = array("start"=>$arr[$i]['stop'],"stop"=>$arr[$i2]['stop'],"status"=>$arr[$i2]['status']);
						
						$arr[$i2]['stop'] = $arr[$i]['start'];
					}
					else if ($arr[$i2]['stop'] > $arr[$i]['stop'])
					{

						$arr[$i2]['start'] = $arr[$i]['stop'];
					}
					else
					{
						if ($i2 < $i)
							$i--;
					
						unset($arr[$i2]);
						$arr = array_values($arr);
						$i2--;
						continue;
					}
				}
			}
			
		}
	}
	
	// Next, combine similar blocks
	$modified = true;
	while ($modified)
	{
		$modified = false;
		for ($i=0; $i<count($arr); $i++)
		{
			for ($i2=0; $i2<count($arr); $i2++)
			{
				if ($i2 == $i || $arr[$i2]['status'] != $arr[$i]['status'] || ($isTimeslot && $arr[$i2]['status'] != "busy"))
					continue;
				
				if ($arr[$i2]['start'] < $arr[$i]['stop'] && $arr[$i2]['stop'] > $arr[$i]['start'])
				{
					if ($arr[$i2]['start'] < $arr[$i]['start'])
						$arr[$i]['start'] = $arr[$i2]['start'];
					
					if ($arr[$i2]['stop'] > $arr[$i]['stop'])
						$arr[$i]['stop'] = $arr[$i2]['stop'];
					
					if ($i2 < $i)
						$i--;
					
					unset($arr[$i2]);
					$arr = array_values($arr);
					$i2--;
					$modified = true;
					continue;
				}
			}
		}
		
	}
	return $arr;
}

/*
This function is used to display available slots on calendar
*/
function LoadVenueAvailability($url, $filter_in)
{	
	$filter = null;
	if ($filter_in)
		$filter = json_decode($filter_in,true);
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();

	// Get Venue details
	if ($stmt->prepare("SELECT venueid, name, shorturl, visibility, address, city, state, zip, country, currency, phone, website, description, timezone, contract FROM venues WHERE shorturl = ?"))
	{
		$stmt->bind_param("s",$url);
		$stmt->execute();
		$stmt->bind_result($id, $name, $shorturl, $vis, $address, $city, $state, $zip, $country, $cur, $phone, $website, $desc, $tz, $c);
		while($stmt->fetch())
		{
			$arr['venueid'] = $id;
			$arr['name'] = Sanitize($name);
			$arr['url'] = $shorturl;
			$arr['address'] = Sanitize($address.", ".$city.", ".$state." ".$zip);
			$arr['country'] = Sanitize($country);
			$arr['currency'] = $cur;
			$arr['phone'] = Sanitize($phone);
			$arr['website'] = $website;
			$arr['description'] = Sanitize($desc);
			$arr['timezone'] = $tz;
			$arr['visibility'] = $vis;
			$arr['types'] = array();
			$arr['books'] = array();
			$arr['relationships'] = array();
			$arr['functionality'] = array();
			$arr['contract'] = (strlen($c) > 0 ? "/assets/content/".$c : "");
			
			// Remove this one after the booking process redesign
			$_SESSION["venueids"]=$id;;
			$arr['showGroupSizePopup'] = false;
		}
		$GLOBALS['db']->CloseConn();
	}	
	if (isset($arr['venueid']))
	{
		$approved = false;
		if (isset($_SESSION['venueRights']))
		{
			foreach ($_SESSION['venueRights'] as $venue)
				if ($venue['venueid'] == $arr['venueid'] && $venue['role'] > 0)
					$approved = $venue['role'];
		}
		if (isset($_SESSION['siteRole']) && $_SESSION['siteRole'] == 999)
			$approved = 16;
		
		if ($arr['visibility'] != 'public' && !$approved)
			return "This venue does not allow public users to make reservations";
		
		date_default_timezone_set($arr['timezone']);
		$datetime = new DateTime();
		//Below line is commented by Harish because of timezone error and added a new line above date_default_timezone_set($arr['timezone']);
		//$datetime->setTimezone(new DateTimeZone($arr['timezone']));
		if (isset($filter['date']))
		{
			$datetime->setTimestamp($filter['date']);
		}
		if (!isset($filter['starting']) || $filter['starting'] < 0)
			$filter['starting'] = 14400;
		if ($filter['starting'] < 0)
			$filter['starting'] = 0;
		$datetime->setTime(0,0,0);

		$datetime->setTimestamp($datetime->getTimestamp() + $filter['starting']);
		$arr['date'] = $datetime->getTimestamp();

		$startdatetime = new DateTime("@".$datetime->getTimestamp());
		$startdatetime->setTimezone(new DateTimeZone($arr['timezone']));
		$stopdatetime = new DateTime("@".$startdatetime->getTimestamp());
		$stopdatetime->setTimestamp($stopdatetime->getTimestamp() + 86400);
		$firstdatetime = new DateTime(date("c", strtotime("this week",strtotime("@".$datetime->getTimestamp()))));
		$firstdatetime->setTimezone(new DateTimeZone($arr['timezone']));
		$firstdatetime->setTime(0,0,0);
		if ($firstdatetime->format("l") == "Monday")
			$firstdatetime->sub(new DateInterval('P1D'));
		if ($firstdatetime->getTimestamp() > $startdatetime->getTimestamp())
			$firstdatetime->sub(new DateInterval('P7D'));
		if ($startdatetime->getOffset() != $firstdatetime->getOffset())
			$firstdatetime->setTimestamp($firstdatetime->getTimestamp() + ($firstdatetime->getOffset() - $startdatetime->getOffset()));
		
		$arr['start'] = $startdatetime->getTimestamp();
		$arr['stop'] = $stopdatetime->getTimestamp();
		$arr['buffer'] = 43200;
		$startmin = ($startdatetime->getTimestamp() - $arr['buffer'] - $firstdatetime->getTimestamp()) / 60;
		$stopmin = ($stopdatetime->getTimestamp() + $arr['buffer'] - $firstdatetime->getTimestamp()) / 60;
		
		$dnow = new DateTime();
		$now = $dnow->getTimestamp();
		
		if (!isset($filter['types']) || strlen($filter['types']) == 0)
			$filter['types'] = "";
		if (!isset($filter['size']))
			$filter['size'] = 0;
		if (!isset($filter['id']))
			$filter['id'] = "%";
		
		$filter['types'] = preg_replace("/[^0-9],/","",$filter['types']);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT photo FROM venues_photos WHERE venueid = ? AND placement < 2 ORDER BY placement DESC LIMIT 0,1"))
		{
			$stmt->bind_param("i",$arr['venueid']);
			$stmt->execute();
			$stmt->bind_result($photo);
			while($stmt->fetch())
			{
				$arr['logo'] = "/assets/content/".$photo;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT showMenus, showPersonnel, showQuestions, showPromos, publicFileUploads,gratuity,entireVenue, (SELECT COUNT(*) FROM personnel p WHERE p.req > 0 AND p.venueid = f.venueid) FROM venues_functionality f WHERE f.venueid = ?"))
		{
			$stmt->bind_param("i",$arr['venueid']);
			$stmt->execute();
			$stmt->bind_result($m,$p,$q,$pc,$fu,$gra,$ev,$show);
			while($stmt->fetch())
			{
				$arr['functionality']['menus'] = $m;
				$arr['functionality']['personnel'] = $p;
				$arr['functionality']['questions'] = $q;
				$arr['functionality']['promos'] = $pc;
				$arr['functionality']['publicFileUploads'] = $fu;
				$arr['functionality']['gratuity'] = $gra;
				$arr['functionality']['entireVenue'] = $ev;
				
				// Remove this after booking process redesign
				if ($show > 0)
					$arr['showGroupSizePopup'] = true;
			}
			$GLOBALS['db']->CloseConn();
		}
		if (isset($arr['venueid']))
		{		
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT DISTINCT r.typeid, t.name FROM resources AS r LEFT JOIN resources_types AS t ON t.typeid = r.typeid WHERE r.venueid = ? AND status != 'deleted'"))
			{
				$stmt->bind_param("i",$arr['venueid']);
				$stmt->execute();
				$stmt->bind_result($id, $name);
				while($stmt->fetch())
				{
					$arr['types'][] = array("id"=>$id,"name"=>Sanitize($name));
				}
				$GLOBALS['db']->CloseConn();
			}
			$evid = -1;
			if ($arr['functionality']['entireVenue'] == 0)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT MIN(resourceid) FROM resources WHERE venueid = ? AND status = 'new'"))
				{
					$stmt->bind_param("i",$arr['venueid']);
					$stmt->execute();
					$stmt->bind_result($id);
					while($stmt->fetch())
					{
						$evid = $id;
					}
					$GLOBALS['db']->CloseConn();
				}
			}
			$stmt = $GLOBALS['db']->stmt_init();
			$resourceid=0;
			$bookingid_ary=array();
			$resourceid_ary=array();
			$resources_name=array();
			$catcnt=0;
			if ($stmt->prepare("SELECT r.resourceid, r.name, r.description, r.capacity, r.typeid, r.timeslots, r.default_rate, r.minduration, r.increment, r.cleanuptime, r.cleanupcost, r.min_lead_time FROM resources AS r WHERE r.venueid = ? AND status != 'deleted' ORDER BY r.resourceid ASC"))
			{
				$stmt->bind_param("i",$arr['venueid']);
				$stmt->execute();
				$stmt->bind_result($id, $name, $desc, $cap, $type, $timeslots, $rate, $min, $inc, $cleanup, $cc, $lead);
				while($stmt->fetch())
				{
					$resourceid=$id;
					$arr['books'][] = array("id"=>$id,"name"=>Sanitize($name),"description"=>Sanitize($desc),"pictures"=>array(),"capacity"=>$cap,"type"=>$type,"timeslots"=>$timeslots,"rate"=>$rate,"minduration"=>$min,"increment"=>$inc,"cleanup"=>$cleanup,"cleanupcost"=>$cc,"lead"=>$lead,"rates"=>array(),"rate_exceptions"=>array(),"schedule"=>array(),"slots"=>array(),"children"=>array());
					$resources_name[$catcnt]=Sanitize($name);
					$resourceid_ary[$catcnt]=$id;
					$catcnt++;
				}
				$GLOBALS['db']->CloseConn();
			}

				// Simple Connection creates(Server)
			// $servername = "localhost";
			// $username = "root";
			// $password = "W2GC@zF!6c7%";
			// $dbname = "invitebignew";

			$servername = "localhost";
			$username = "root";
			$password = "jack123";
			$dbname = "invitebignew";

			// Create connection
			$conn = new mysqli($servername, $username, $password, $dbname);
			$parentsary=array();
			$childary=array();
			$j=0;
			$i=0;
			foreach ($arr['books'] as &$resource)
			{
				$resource['parents'] = FindParents($resource['id']);
				$resource['checkparents'] = checkparents($resource['id']);
				$resource['children'] = FindChildren($resource['id'],true);
				$l = $now + ($resource['lead'] * 60);
				$l = $l + (900 - ($l % 900));
				$resource['schedule'][] = array("status"=>"busy","start"=>0,"stop"=>$l);
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT photo,caption FROM resources_photos WHERE resourceid = ? ORDER BY placement ASC"))
				{
					$stmt->bind_param("i",$resource['id']);
					$stmt->execute();
					$stmt->bind_result($photo,$caption);
					while($stmt->fetch())
					{
						$resource['pictures'][] = array("url"=>"/assets/content/".$photo,"caption"=>Sanitize($caption));
					}
					$GLOBALS['db']->CloseConn();
				}
 
				$stmt = $GLOBALS['db']->stmt_init();
				// foreach ($resource['children'] as $key1) {
				//  	if(!in_array($key1, $childary))
				//  	{
				//  		$childary[$j]=$key1;
				//  		$j++;
				//  	}
				//  }
				//  foreach ($resource['parents'] as $key) {
				//  	if(!in_array($key, $parentsary))
				//  	{
				//  		$parentsary[$i]=$key;
				//  		$i++;
				//  	}
				if ($stmt->prepare("SELECT br.starttime, br.stoptime, s.status, r.cleanuptime, br.cleanuptime FROM booking_resources AS br LEFT JOIN booking AS s ON s.bookingid = br.bookingid LEFT JOIN resources AS r ON r.resourceid = br.resourceid WHERE br.resourceid = ? AND br.starttime < ? AND br.stoptime >= ? AND (s.status = 'Paid' OR s.status = 'Pending Payment' OR s.status = 'Imported' OR s.status = 'Past Due')"))
				{
					$stop = $stopdatetime->getTimestamp() + $arr['buffer'];
					$start = $startdatetime->getTimestamp() - $arr['buffer'];
					$stmt->bind_param("iii",$resource['id'],$stop,$start);
					$stmt->execute();
					$stmt->bind_result($starttime, $stoptime, $status, $ct, $ct2);
					$cmb=0;
					while($stmt->fetch())
					{
						if ($resource['timeslots'] == 0)
						{
							$adjusted_start = $starttime - ($ct * 60);
							$adjusted_stop = $stoptime + ($ct2 * 60);
						}
						else
						{
							$adjusted_start = $starttime;
							$adjusted_stop = $stoptime;
						}

						// block parent if this child is booked
						foreach ($arr['books'] as &$r)
						{
							 if (in_array($r['id'],$resource['checkparents']))
							 {
								$r['schedule'][] = array("status"=>"busy","start"=>$adjusted_start,"stop"=>$adjusted_stop);		 									//echo $row["start1"]."\r\n";
	 						 }
						}

						// block children if this parent is booked
						foreach ($arr['books'] as &$r)
						{
							if (in_array($r['id'],$resource['children']))
							{
								$r['schedule'][] = array("status"=>"busy","start"=>$adjusted_start,"stop"=>$adjusted_stop);
							}
						}
					}
					$GLOBALS['db']->CloseConn();
				}
			 
			}

			for ($i = 0; $i < count($arr['books']); $i++)
			{	
				if ($arr['books'][$i]['timeslots'] == 0)  // For Hourly Method
				{
				
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("SELECT resourceid, startminute, stopminute FROM resources_hours WHERE resourceid = ?"))
					{
						$stmt->bind_param("i",$arr['books'][$i]['id']);
						$stmt->execute();
						$stmt->bind_result($resourceid, $startminute, $stopminute);
						$tempresource = 0;
						while($stmt->fetch())
						{
							$startcheck1=trim($firstdatetime->getTimestamp() + ($startminute*60) - 604800);
							// $startcheck2=$firstdatetime->getTimestamp() + ($startminute*60);
							// $startcheck3=$firstdatetime->getTimestamp() + ($startminute*60) + 604800;
							// $stopcheck1=$firstdatetime->getTimestamp() + ($stopminute*60) - 604800;
							// $stopcheck2=$firstdatetime->getTimestamp() + ($stopminute*60);
							$stopcheck3=trim($firstdatetime->getTimestamp() + ($stopminute*60) + 604800);

								// For booked time slots only
								if($tempresource == 0){
									$sql = "SELECT br.starttime as start1, br.stoptime as stop3, s.status, br.cleanuptime FROM booking_resources AS br LEFT JOIN booking AS s ON s.bookingid = br.bookingid WHERE br.resourceid =".$resourceid."  AND br.starttime >= ".$startcheck1." AND br.stoptime <= ".$stopcheck3."  AND s.status = 'Paid'";
									$result = $conn->query($sql);
									if ($result->num_rows > 0) {
										while($row = $result->fetch_assoc()) {

											// Hide booked slots, that is already booked. 
											$arr['books'][$i]['schedule'][] = array("status"=>"busy","start"=>$row['start1'],"stop"=>$row['stop3']);
										}
										$tempresource = 1;
									}
								}

							// Display all the available time slots
							$arr['books'][$i]['schedule'][] = array("status"=>"open","start"=>$firstdatetime->getTimestamp() + ($startminute*60) - 604800,"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60) - 604800);
							$arr['books'][$i]['schedule'][] = array("status"=>"open","start"=>$firstdatetime->getTimestamp() + ($startminute*60),"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60));
							$arr['books'][$i]['schedule'][] = array("status"=>"open","start"=>$firstdatetime->getTimestamp() + ($startminute*60) + 604800,"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60) + 604800);
						}
						$GLOBALS['db']->CloseConn();
					}
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("SELECT rate, startminute, stopminute FROM resources_rates WHERE resourceid = ?"))
					{
						$stmt->bind_param("i",$arr['books'][$i]['id']);
						$stmt->execute();
						$stmt->bind_result($rate, $startminute, $stopminute);
						while($stmt->fetch())
						{
							$arr['books'][$i]['rates'][] = array("rate"=>$rate,"start"=>$firstdatetime->getTimestamp() + ($startminute*60) - 604800,"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60) - 604800);
							$arr['books'][$i]['rates'][] = array("rate"=>$rate,"start"=>$firstdatetime->getTimestamp() + ($startminute*60),"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60));
							$arr['books'][$i]['rates'][] = array("rate"=>$rate,"start"=>$firstdatetime->getTimestamp() + ($startminute*60) + 604800,"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60) + 604800);
						}
						$GLOBALS['db']->CloseConn();
					}
					ConsolidateStatus($arr['books'][$i]['schedule'],false);
				}
				else // For Flat-rates method
				{
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("SELECT resourceid, startminute, stopminute, rate, combinable FROM resources_slots WHERE resourceid = ?"))
					{
						$stmt->bind_param("i",$arr['books'][$i]['id']);
						$stmt->execute();
						$stmt->bind_result($resourceid, $startminute, $stopminute, $rate, $combinable);
						while($stmt->fetch())
						{

							$startcheck1=$firstdatetime->getTimestamp() + ($startminute*60) - 604800;
							// $startcheck2=$firstdatetime->getTimestamp() + ($startminute*60);
							// $startcheck3=$firstdatetime->getTimestamp() + ($startminute*60) + 604800;
							// $stopcheck1=$firstdatetime->getTimestamp() + ($stopminute*60) - 604800;
							// $stopcheck2=$firstdatetime->getTimestamp() + ($stopminute*60);
							$stopcheck3=$firstdatetime->getTimestamp() + ($stopminute*60) + 604800;

								// For booked time slots only
								$tempresource = 0;
								if($tempresource == 0){
									$sql = "SELECT br.starttime as start1, br.stoptime as stop3, s.status, br.cleanuptime FROM booking_resources AS br LEFT JOIN booking AS s ON s.bookingid = br.bookingid WHERE br.resourceid =".$resourceid."  AND br.starttime >= ".$startcheck1." AND br.stoptime <= ".$stopcheck3."  AND s.status = 'Paid'";
									$result = $conn->query($sql);

									if ($result->num_rows > 0) {
										while($row = $result->fetch_assoc()) {
											// Hide booked slots, that is already booked. 
											$arr['books'][$i]['schedule'][] = array("status"=>"busy","start"=>$row['start1'],"stop"=>$row['stop3']);
										}
										$tempresource = 1;
									}
									//echo "\r\n".$sql.";";
								}

							$arr['books'][$i]['schedule'][] = array("status"=>"open","start"=>$firstdatetime->getTimestamp() + ($startminute*60) - 604800,"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60) - 604800);
							$arr['books'][$i]['schedule'][] = array("status"=>"open","start"=>$firstdatetime->getTimestamp() + ($startminute*60),"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60));
							$arr['books'][$i]['schedule'][] = array("status"=>"open","start"=>$firstdatetime->getTimestamp() + ($startminute*60) + 604800,"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60) + 604800);
							
							$arr['books'][$i]['rates'][] = array("rate"=>$rate,"combinable"=>$combinable,"start"=>$firstdatetime->getTimestamp() + ($startminute*60) - 604800,"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60) - 604800);
							$arr['books'][$i]['rates'][] = array("rate"=>$rate,"combinable"=>$combinable,"start"=>$firstdatetime->getTimestamp() + ($startminute*60),"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60));
							$arr['books'][$i]['rates'][] = array("rate"=>$rate,"combinable"=>$combinable,"start"=>$firstdatetime->getTimestamp() + ($startminute*60) + 604800,"stop"=>$firstdatetime->getTimestamp() + ($stopminute*60) + 604800);
						}
						$GLOBALS['db']->CloseConn();
					}
					
					ConsolidateStatus($arr['books'][$i]['schedule'],true);
				}
			}
			$conn->close();
			/*
			//      No longer doing this as of 11/16/2015
			//
			// This gets all child hours of operation, consolidates them, 
			//  adds a 'busy' block for these to the parent's availability.
			// This blocks the parent when the children are not all available.
			for ($b = 0; $b < count($arr['books']); $b++)
			{
				// If the parent resource is using timeslot billing then ignore children's hours of operation
				if ($arr['books'][$b]['timeslots'] == 1)
					continue;
				
				for ($s = 0; $s < count($arr['books'][$b]['schedule']); $s++)
				{
					if ($arr['books'][$b]['schedule'][$s]['status'] != "open")
						continue;
					
					for ($c = 0; $c < count($arr['books']); $c++)
					{
						if (!in_array($arr['books'][$c]['id'],$arr['books'][$b]['children']))
							continue;
							
						$matched = array();
						if ($arr['books'][$c]['timeslots'] == 1)
							$matched[] = array("status"=>"open","start"=>$arr['books'][$b]['schedule'][$s]['start'],"stop"=>$arr['books'][$b]['schedule'][$s]['stop']);
						else
						{
							for ($c2 = 0; $c2 < count($arr['books'][$c]['schedule']); $c2++)
							{
								if ($arr['books'][$c]['schedule'][$c2]['status'] == "open" && 
									$arr['books'][$c]['schedule'][$c2]['start'] < $arr['books'][$b]['schedule'][$s]['stop'] && 
									$arr['books'][$c]['schedule'][$c2]['stop'] > $arr['books'][$b]['schedule'][$s]['start'])
								{
									$arr2 = array("status"=>"open","start"=>$arr['books'][$c]['schedule'][$c2]['start'],"stop"=>$arr['books'][$c]['schedule'][$c2]['stop']);
									$matched[] = $arr2;
								}
							}
						}
						
						$matched = ConsolidateStatus($matched,false);
						$i = $arr['books'][$b]['schedule'][$s]['start'];
						while ($i < $arr['books'][$b]['schedule'][$s]['stop'])
						{
							$stop = $i;
							
							foreach ($matched as $m)
							{
								if ($i >= $m['start'] && $i < $m['stop'])
									$stop = $m['stop'];
							}
							
							if ($stop <= $i)
							{
								$start = 2147483647;
								foreach ($matched as $m)
								{
									if ($m['start'] > $i && $m['start'] < $start)
										$start = $m['start'];
								}
								
								if ($start > $arr['books'][$b]['schedule'][$s]['stop'])
									$start = $arr['books'][$b]['schedule'][$s]['stop'];
								
								// This is where the 'busy' is added to the parent
								$arr['books'][$b]['schedule'][] = array("status"=>"busy","start"=>$stop,"stop"=>$start);
								//$arr['debug'] .= "res: ".$arr['books'][$b]['id']." child: ".$arr['books'][$c]['id']." arr: ".json_encode(array("status"=>"busy","start"=>$stop,"stop"=>$start));
								$i = $start;
							}
							else $i = $stop;						
						}
					}
				}
			}
			*/
			
			// This removes the 'busy' time from the schedule
			for ($b = 0; $b < count($arr['books']); $b++)
			{
				if ($arr['books'][$b]['timeslots'] == 0)
					$arr['books'][$b]['schedule'] = ConsolidateStatus($arr['books'][$b]['schedule'],false);
				else $arr['books'][$b]['schedule'] = ConsolidateStatus($arr['books'][$b]['schedule'],true);
				
				for ($i = 0; $i < count($arr['books'][$b]['schedule']); $i++)
				{
					if ($arr['books'][$b]['schedule'][$i]['stop'] < $arr['start'] - $arr['buffer'])
					{
						unset($arr['books'][$b]['schedule'][$i]);
						continue;
					}
					if ($arr['books'][$b]['schedule'][$i]['start'] > $arr['stop'] + $arr['buffer'])
					{
						unset($arr['books'][$b]['schedule'][$i]);
						continue;
					}
					
					if ($arr['books'][$b]['schedule'][$i]['start'] < $arr['start'] - $arr['buffer'])
						$arr['books'][$b]['schedule'][$i]['start'] = $arr['start'] - $arr['buffer'];
					if ($arr['books'][$b]['schedule'][$i]['stop'] > $arr['stop'] + $arr['buffer'])
						$arr['books'][$b]['schedule'][$i]['stop'] = $arr['stop'] + $arr['buffer'];
				}
				
				$arr['books'][$b]['schedule'] = array_values($arr['books'][$b]['schedule']);
			}
			
			for ($b = 0; $b < count($arr['books']); $b++)
			{
				if ($arr['books'][$b]['timeslots'] == 1)
					continue;
				
				for ($s = 0; $s < count($arr['books'][$b]['schedule']); $s++)
				{
					if ($arr['books'][$b]['schedule'][$s]['status'] == "open" && 
						($arr['books'][$b]['schedule'][$s]['stop'] - $arr['books'][$b]['schedule'][$s]['start'] < $arr['books'][$b]['minduration'] * 60 ||
						$arr['books'][$b]['schedule'][$s]['stop'] - $arr['books'][$b]['schedule'][$s]['start'] < $arr['books'][$b]['increment'] * 60))
					{
						unset($arr['books'][$b]['schedule'][$s]);
						$arr['books'][$b]['schedule'] = array_values($arr['books'][$b]['schedule']);
						$s--;
					}
				}
			}
			
			/*
			// Rate Exceptions are not yet implemented
			foreach ($arr['books'] as &$resource)
			{
				/////// fix this, convert the unix timestamp to an offset from the first day in this array
				/// no, this should be an INT, find out why I thought it shouldn't be
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT rate, starttime, stoptime FROM resources_rate_exceptions WHERE resourceid = ? AND starttime < ? AND stoptime >= ?"))
				{
					$stop = $stopdatetime->getTimestamp();
					$start = $startdatetime->getTimestamp();
					$stmt->bind_param("iss",$resource['id'],$stop,$start);
					$stmt->execute();
					$stmt->bind_result($rate, $startdate, $stopdate);
					while($stmt->fetch())
					{
						$adjusted_start = intval(($starttime - $start) / 60);
						$adjusted_stop = intval(($stoptime - $start) / 60);
						
						$resource['rates'][] = array("rate"=>$rate,"start"=>$start,"stop"=>$stop);
					}
					$GLOBALS['db']->CloseConn();
				}
			}*/
			
			// If there is no availability returned for any one resource, find the next availability
			for ($b = 0; $b < count($arr['books']); $b++)
			{
				$matched = false;
				for ($b2=0; $b2<count($arr['books'][$b]['schedule']); $b2++)
				{
					if ($arr['books'][$b]['schedule'][$b2]['status'] == "open" && $arr['books'][$b]['schedule'][$b2]['start'] < $arr['stop'] && $arr['books'][$b]['schedule'][$b2]['stop'] > $arr['start'])
						$matched = true;
				}
				
				if ($matched == false)
				{
					$n = (new DateTime())->getTimestamp();
					if ($n < $startdatetime->getTimestamp())
						$n = $startdatetime->getTimestamp();
					
					// iterate through this resource's next available timeslots
					//  until one is found for which all children are available
					
					$modified = true;
					$start = $n;
					
					while ($modified)
					{						
						$modified = false;
						$slot = GetNextAvailability($arr['books'][$b],$firstdatetime->getTimestamp(),$start,-1,0);
						$start = $slot['start'];
						
						if ($start - $n > 31536000 || $start == -1)
						{
							$start = -1;
							break;
						}
						
						for ($c = 0; $c < count($arr['books']); $c++)
						{
							if (!in_array($arr['books'][$c]['id'],$arr['books'][$b]['children']))
								continue;
							
							if ($arr['books'][$c]['timeslots'] == 0)
							{
								$cslot = GetNextAvailability($arr['books'][$c],$firstdatetime->getTimestamp(),$start,$slot['min'],0);
								if ($cslot['stop'] - $start < $slot['min'] || $cslot['start'] == -1)
								{
									$start = $cslot['stop'];
									$modified = true;
									break;
								}
							}
							else
							{
								$cslot = GetNextBooking($arr['books'][$c],$start);
								if ($cslot['start'] - $start < $slot['min'])
								{
									$start = $cslot['stop'];
									$modified = true;
									break;
								}
							}
						}
					}
					
					$arr['books'][$b]['nextAvailability'] = $start;
				}
			}
		}
		
		// Trim uneccesary information, apply filter
		for ($i=0; $i<count($arr['books']); $i++)
		{
			if ($arr['books'][$i]['id'] == $evid)
			{
				$arr['books'][$i]['hide'] = true;
				//unset($arr['books'][$i]);
				//$arr['books'] = array_values($arr['books']);
				//$i--;
				continue;
			}
			
			for ($i2=0; $i2<count($arr['books'][$i]['schedule']); $i2++)
			{
				if ($arr['books'][$i]['schedule'][$i2]['status'] == "busy")
				{
					unset($arr['books'][$i]['schedule'][$i2]);
					$arr['books'][$i]['schedule'] = array_values($arr['books'][$i]['schedule']);
					$i2--;
					continue;
				}
				
				if ($arr['books'][$i]['schedule'][$i2]['stop'] <= $arr['start'] - $arr['buffer']
					|| $arr['books'][$i]['schedule'][$i2]['start'] >= $arr['stop'] + $arr['buffer'])
				{
					unset($arr['books'][$i]['schedule'][$i2]);
					$arr['books'][$i]['schedule'] = array_values($arr['books'][$i]['schedule']);
					$i2--;
					continue;
				}
				
				if ($arr['books'][$i]['schedule'][$i2]['stop'] > $arr['stop'] + $arr['buffer'])
					$arr['books'][$i]['schedule'][$i2]['stop'] = $arr['stop'] + $arr['buffer'];
				
				if ($arr['books'][$i]['schedule'][$i2]['start'] < $arr['start'] - $arr['buffer'])
					$arr['books'][$i]['schedule'][$i2]['start'] = $arr['start'] - $arr['buffer'];
			}
		}
		
		for ($i=0; $i<count($arr['books']); $i++)
		{
			// replace the children (which was all children) with only linked children
			$arr['books'][$i]['children'] = FindChildren($arr['books'][$i]['id'],false);
		}
		//echo $arr['venueid'];
		return $arr;
	}
	else return "The venue you specified does not exist";
}

function GetNextBooking(&$resource,$start)
{
	$ret = array("start"=>2147483647,"stop"=>2147483647);
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT br.starttime, br.stoptime FROM booking_resources AS br LEFT JOIN booking AS s ON s.bookingid = br.bookingid LEFT JOIN resources AS r ON r.resourceid = br.resourceid WHERE br.resourceid = ? AND br.stoptime > ? AND (s.status = 'Paid' OR s.status = 'Pending Payment' OR s.status = 'Imported' OR b.status = 'Past Due') ORDER BY br.starttime ASC LIMIT 1"))
	{
		$stmt->bind_param("ii",$resource['id'],$start);
		$stmt->execute();
		$stmt->bind_result($startime,$stoptime);
		while($stmt->fetch())
		{
			$ret = array("start"=>$startime,"stop"=>$stoptime);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $ret;
}

function GetNextAvailability(&$resource,$weekstart,$start,$min,$pagination)
{
	if ($pagination > 26)
		return array("start"=>-1,"stop"=>$start,"min"=>$min);
	$pagination++;
	
	$stop = $start + 1296000; // two weeks + one day
	$schedule = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT br.starttime, br.stoptime, s.status, r.cleanuptime, br.cleanuptime FROM booking_resources AS br LEFT JOIN booking AS s ON s.bookingid = br.bookingid LEFT JOIN resources AS r ON r.resourceid = br.resourceid WHERE br.resourceid = ? AND br.starttime < ? AND br.stoptime > ? AND (s.status = 'Paid' OR s.status = 'Pending Payment' OR s.status = 'Imported' OR b.status = 'Past Due')"))
	{
		$stmt->bind_param("iii",$resource['id'],$stop,$start);
		$stmt->execute();
		$stmt->bind_result($starttime, $stoptime, $status, $ct, $ct2);
		while($stmt->fetch())
		{
			$adjusted_start = $starttime - ($ct * 60);
			$adjusted_stop = $stoptime + ($ct2 * 60);
			
			$schedule[] = array("status"=>"busy","start"=>$adjusted_start,"stop"=>$adjusted_stop);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$dnow = new DateTime();
	$now = $dnow->getTimestamp();
	$l = $now + ($resource['lead'] * 60);
	$l = $l + (900 - ($l % 900));
	if ($start < $l)
		$start = $l;
	
	$schedule[] = array("status"=>"busy","start"=>0,"stop"=>$start);

	if ($resource['timeslots'] == 0)
	{
		// only factor in hours of operation for hourly-rate billed resources, not for timeslot billing
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT startminute, stopminute FROM resources_hours WHERE resourceid = ?"))
		{
			$stmt->bind_param("i",$resource['id']);
			$stmt->execute();
			$stmt->bind_result($startminute, $stopminute);
			while($stmt->fetch())
			{
				for ($i=$weekstart; $i<$stop; $i+=604800)
				{
					$nstart = $i + ($startminute*60);
					$nstop = $i + ($stopminute*60);
					
					if ($nstart < $start)
						$nstart = $start;
					
					if ($nstart < $nstop && $nstop > $start)
						$schedule[] = array("status"=>"open","start"=>$nstart,"stop"=>$nstop);
				}
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($min == -1)
			$min = $resource['minduration'] * 60;
		
		$schedule = ConsolidateStatus($schedule,false);
	}
	else 
	{	
		if ($min == -1)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT startminute, stopminute FROM resources_slots WHERE resourceid = ?"))
			{
				$stmt->bind_param("i",$resource['id']);
				$stmt->execute();
				$stmt->bind_result($startminute, $stopminute);
				while($stmt->fetch())
				{
					for ($i=$weekstart; $i<$stop; $i+=604800)
					{
						$nstart = $i + ($startminute*60);
						$nstop = $i + ($stopminute*60);
						
						if ($nstart < $nstop && $nstop > $start)
							$schedule[] = array("status"=>"open","start"=>$nstart,"stop"=>$nstop);
					}
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		// 'else' shouldn't ever happen, GetNextBooking should be called instead from precious function
		else $schedule[] = array("status"=>"open","start"=>$start,"stop"=>$stop);
		
		$schedule = ConsolidateStatus($schedule,true);
	}
	
	$slot = array("start"=>4000000000,"stop"=>4000000000,"min"=>$min);
	
	for ($i=0; $i < count($schedule); $i++)
	{
		if ($schedule[$i]['status'] == "open" && 
			($resource['timeslots'] == 1 || ($schedule[$i]['stop'] - $schedule[$i]['start'] >= $min && $schedule[$i]['stop'] - $schedule[$i]['start'] >= $resource['increment'] * 60)))
		{
			if ($schedule[$i]['start'] < $slot['start'])
			{
				$slot['start'] = $schedule[$i]['start'];
				$slot['stop'] = $schedule[$i]['stop'];
				
				if ($min == -1)
					$slot['min'] = $slot['stop'] - $slot['start'];
			}
		}
	}
	
	if ($slot['start'] == 4000000000)
		return GetNextAvailability($resource,$weekstart+1209600,$start+1209600,$min,$pagination);
	
	return $slot;
}

function GetReservationResourceData($rid, $starttime, $stoptime)
{
	$arr = array();
	$timezone = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT timezone FROM resources AS r LEFT JOIN venues AS v ON v.venueid = r.venueid WHERE resourceid = ?"))
	{
		$stmt->bind_param("i",$rid);
		$stmt->execute();
		$stmt->bind_result($tz);
		if($stmt->fetch())
			$timezone = $tz;
		$GLOBALS['db']->CloseConn();
	}	
	
	$startmin=0;$stopmin=0;$firstdatetime=0;$startdatetime=0;$stopdatetime=0;
	CalcStartStopMinute($starttime,$stoptime,$timezone,$startmin,$stopmin,$firstdatetime,$startdatetime,$stopdatetime);
	
	while ($startmin < 0)
		$startmin += 10080;
	while ($stopmin < 0)
		$stopmin += 10080;
	while ($stopmin < $startmin)
		$stopmin += 10080;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT r.resourceid, r.name, r.description, r.capacity, r.seats, p.photo FROM resources AS r LEFT JOIN resources_photos AS p ON p.resourceid = r.resourceid WHERE r.status = 'new' AND r.resourceid = ? ORDER BY p.placement ASC LIMIT 0,1"))
	{
		$stmt->bind_param("i",$rid);
		$stmt->execute();
		$stmt->bind_result($id, $name, $desc, $cap, $seats, $url);
		if ($stmt->fetch())
		{
			$arr = array("id"=>$id,"name"=>Sanitize($name),"description"=>Sanitize($desc),"capacity"=>$cap,"seats"=>$seats,"picture"=>($url==null?"":"/assets/content/".$url),"addons"=>array(),"children"=>array());
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (isset($arr['addons']))
	{	
		$children = FindChildren($arr['id']);
		$childstr = "";
		foreach ($children as $child)
		{
			$childstr .= $child . ",";
		}
		$childstr = preg_replace("/,+$/","",$childstr);
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT name FROM resources WHERE resourceid IN (" . $childstr . ") ORDER BY name ASC"))
		{
			$stmt->execute();
			$stmt->bind_result($name);
			while ($stmt->fetch())
			{
				$arr['children'][] = ($name);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$childstr .= "," . $arr['id'];
		$childstr = ltrim(rtrim($childstr,','),',');
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT DISTINCT a.addonid, at.name, a.name, a.description, a.price, p.photo, p.caption, a.min, a.max, a.deliverable FROM resources_addons AS r LEFT JOIN addons AS a ON a.addonid = r.addonid LEFT JOIN addons_types AS at ON at.typeid = a.typeid LEFT JOIN addons_photos AS p ON p.addonid = a.addonid WHERE a.status = 'new' AND r.resourceid IN (" . $childstr . ") AND (p.placement = 0 OR p.placement IS NULL) ORDER BY at.name,a.name"))
		{
			$stmt->execute();
			$stmt->bind_result($id, $type, $name, $desc, $price, $url, $cap, $min, $max, $deliverable);
			while ($stmt->fetch())
			{
				$arr['addons'][] = array("id"=>$id,"type"=>$type,"name"=>Sanitize($name),"description"=>Sanitize($desc),"price"=>$price,"picture"=>($url==null?"":"/assets/content/".$url),"caption"=>Sanitize($cap),"minimum"=>$min,"maximum"=>$max,"deliverable"=>$deliverable);
			}
			$GLOBALS['db']->CloseConn();
		}
	
		for ($i = 0; $i < count($arr['addons']); $i++)
		{
			$hours_start = null;
			$hours_stop = null;
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT startminute, stopminute FROM addons_hours WHERE addonid = ?"))
			{
				$matched = false;
				
				$stmt->bind_param("i",$arr['addons'][$i]['id']);
				$stmt->execute();
				$stmt->bind_result($startminute, $stopminute);
				while ($stmt->fetch())
				{
					if ($stopminute < $startminute)
						$stopminute += 10080;
						
					if ($startminute < $stopmin && $stopminute > $startmin)
					{
						$matched = true;
						
						$s = new DateTime($startdatetime->format("c"));
						$s->setTimezone(new DateTimeZone($timezone));
						if ($startmin < $startminute)
							$s->setTimestamp($s->getTimestamp() + (($startminute-$startmin) * 60));
						$hours_start = $s->format("c");
						
						$s = new DateTime($stopdatetime->format("c"));
						$s->setTimezone(new DateTimeZone($timezone));
						if ($stopmin > $stopminute)
							$s->setTimestamp($s->getTimestamp() - (($stopmin-$stopminute) * 60));
						$hours_stop = $s->format("c");
					}
				}
				$GLOBALS['db']->CloseConn();
				
				$arr['addons'][$i]['hours_limits'] = array("start"=>$hours_start,"stop"=>$hours_stop,"list"=>array());
				$s1 = new DateTime($hours_start, new DateTimeZone($timezone));
				$s2 = new DateTime($hours_stop, new DateTimeZone($timezone));
				
				while ($s1->getTimestamp() < $s2->getTimestamp())
				{
					$arr['addons'][$i]['hours_limits']['list'][] = array("value"=>$s1->format("c"), "text"=>$s1->format("g:i A \\o\\n M jS"));
					$s1->setTimestamp($s1->getTimestamp() + 900);
				}
				
				if (!$matched)
				{
					unset($arr['addons'][$i]);
					$arr['addons'] = array_values($arr['addons']);
					$i--;
				}
			}
		}
	}
	
	return $arr;
}

function GetReservationMenus($vid, $starttime, $stoptime)
{
	$menus = array();
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
	
	$startmin=0;$stopmin=0;$firstdatetime=0;$startdatetime=0;$stopdatetime=0;
	CalcStartStopMinute($starttime,$stoptime,$timezone,$startmin,$stopmin,$firstdatetime,$startdatetime,$stopdatetime);
	
	while ($startmin < 0)
		$startmin += 10080;
	while ($stopmin < 0)
		$stopmin += 10080;
	while ($stopmin < $startmin)
		$stopmin += 10080;
		
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT m.menuid, m.name, m.description FROM menus AS m LEFT JOIN venues_functionality AS v ON v.venueid = m.venueid WHERE m.venueid = ? AND m.status != 'deleted' AND v.showMenus = 1"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($id, $name, $desc);
		while ($stmt->fetch())
		{
			$menus[] = array("id"=>$id,"name"=>Sanitize($name),"description"=>Sanitize($desc),"items"=>array());
		}
		$GLOBALS['db']->CloseConn();
	}
	
	for ($i = 0; $i < count($menus); $i++)
	{
		$hours_start = null;
		$hours_stop = null;
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT startminute, stopminute FROM menus_hours WHERE menuid = ?"))
		{
			$matched = false;
			
			$stmt->bind_param("i",$menus[$i]['id']);
			$stmt->execute();
			$stmt->bind_result($startminute, $stopminute);
			while ($stmt->fetch())
			{
				if ($stopminute < $startminute)
					$stopminute += 10080;
					
				if (($startminute < $stopmin && $stopminute > $startmin) || ($startminute > $startmin && $stopminute < $stopmin))
				{
					$matched = true;
					
					$s = new DateTime($startdatetime->format("c"));
					$s->setTimezone(new DateTimeZone($timezone));
					if ($startmin < $startminute)
						$s->setTimestamp($s->getTimestamp() + (($startminute-$startmin) * 60));
					$hours_start = $s->format("c");
					
					$s = new DateTime($stopdatetime->format("c"));
					$s->setTimezone(new DateTimeZone($timezone));
					if ($stopmin > $stopminute)
						$s->setTimestamp($s->getTimestamp() - (($stopmin-$stopminute) * 60));
					$hours_stop = $s->format("c");
				}
			}
			$GLOBALS['db']->CloseConn();
			
			$menus[$i]['hours_limits'] = array("start"=>$hours_start,"stop"=>$hours_stop,"list"=>array());
			$s1 = new DateTime($hours_start, new DateTimeZone($timezone));
			$s2 = new DateTime($hours_stop, new DateTimeZone($timezone));
			
			while ($s1->getTimestamp() < $s2->getTimestamp())
			{
				$menus[$i]['hours_limits']['list'][] = array("value"=>$s1->format("c"), "text"=>$s1->format("g:i A \\o\\n M jS"));
				$s1->setTimestamp($s1->getTimestamp() + 900);
			}
			
			if (!$matched)
			{
				unset($menus[$i]);
				$menus = array_values($menus);
				$i--;
			}
		}
	}
	
	for ($i=0; $i<count($menus); $i++)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT itemid, name, price, min, max, description, photo, caption FROM menus_items WHERE menuid = ? AND status != 'deleted'"))
		{			
			$stmt->bind_param("i",$menus[$i]['id']);
			$stmt->execute();
			$stmt->bind_result($id, $name, $price, $min, $max, $desc, $url, $cap);
			while ($stmt->fetch())
			{
				$menus[$i]['items'][] = array("id"=>$id, "name"=>Sanitize($name), "description"=>Sanitize($desc), "min"=>$min, "max"=>$max, "price"=>$price, "picture"=>($url==null?"":"/assets/content/".$url), "caption"=>Sanitize($cap),);
			}
		}
		
		if (count($menus[$i]['items']) < 1)
		{
			unset($menus[$i]);
			$menus = array_values($menus);
			$i--;
		}
	}
	
	return $menus;
}

function GetReservationPersonnel($vid, $starttime, $stoptime)
{
	$personnel = array();
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
	
	$startmin=0;$stopmin=0;$firstdatetime=0;$startdatetime=0;$stopdatetime=0;
	CalcStartStopMinute($starttime,$stoptime,$timezone,$startmin,$stopmin,$firstdatetime,$startdatetime,$stopdatetime);
	
	while ($startmin < 0)
		$startmin += 10080;
	while ($stopmin < 0)
		$stopmin += 10080;
	while ($stopmin < $startmin)
		$stopmin += 10080;
		
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT p.personnelid, p.name, p.price, p.min, p.max, p.req, p.description FROM personnel AS p LEFT JOIN venues_functionality AS v ON v.venueid = p.venueid WHERE p.venueid = ? AND p.status != 'deleted' AND v.showPersonnel = 1"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($id, $name, $price, $min, $max, $req, $desc);
		while ($stmt->fetch())
		{
			$personnel[] = array("id"=>$id, "name"=>Sanitize($name), "description"=>Sanitize($desc), "min"=>$min, "max"=>$max, "req"=>$req, "price"=>$price);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	for ($i = 0; $i < count($personnel); $i++)
	{
		$hours_start = null;
		$hours_stop = null;
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT startminute, stopminute FROM personnel_hours WHERE personnelid = ?"))
		{
			$matched = false;
			
			$stmt->bind_param("i",$personnel[$i]['id']);
			$stmt->execute();
			$stmt->bind_result($startminute, $stopminute);
			while ($stmt->fetch())
			{
				if ($stopminute < $startminute)
					$stopminute += 10080;
					
				if (($startminute < $stopmin && $stopminute > $startmin) || ($startminute > $startmin && $stopminute < $stopmin))
				{
					$matched = true;
					
					$s = new DateTime($startdatetime->format("c"));
					$s->setTimezone(new DateTimeZone($timezone));
					if ($startmin < $startminute)
						$s->setTimestamp($s->getTimestamp() + (($startminute-$startmin) * 60));
					$hours_start = $s->format("c");
					
					$s = new DateTime($stopdatetime->format("c"));
					$s->setTimezone(new DateTimeZone($timezone));
					if ($stopmin > $stopminute)
						$s->setTimestamp($s->getTimestamp() - (($stopmin-$stopminute) * 60));
					$hours_stop = $s->format("c");
				}
			}
			$GLOBALS['db']->CloseConn();
			
			$personnel[$i]['hours_limits'] = array("start"=>$hours_start,"stop"=>$hours_stop,"list"=>array());
			$s1 = new DateTime($hours_start, new DateTimeZone($timezone));
			$s2 = new DateTime($hours_stop, new DateTimeZone($timezone));
			
			while ($s1->getTimestamp() < $s2->getTimestamp())
			{
				$personnel[$i]['hours_limits']['list'][] = array("value"=>$s1->format("c"), "text"=>$s1->format("g:i A \\o\\n M jS"));
				$s1->setTimestamp($s1->getTimestamp() + 900);
			}
			
			if (!$matched)
			{
				unset($personnel[$i]);
				$personnel = array_values($personnel);
				$i--;
			}
		}
	}
	
	return $personnel;
}

function GetReservationQuestions($vid,$res,$add,$menus,$pers)
{
	$questions = array();
	$show = false;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT showQuestions FROM venues_functionality WHERE venueid = ?"))
	{
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($q);
		if ($stmt->fetch())
		{
			$show = $q;
		}
		$GLOBALS['db']->CloseConn();
	}
		
	if ($q)
	{		
		for ($i = 0; $i < count($res); $i++)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT q.questionid, q.question, q.type, q.req FROM questions_resources AS qa LEFT JOIN questions AS q ON q.questionid = qa.questionid WHERE q.venueid = ? AND (qa.resourceid = ? OR qa.resourceid = 0) AND q.deleted != 1"))
			{
				$stmt->bind_param("ii",$vid,$res[$i]);
				$stmt->execute();
				$stmt->bind_result($qid,$q,$t,$r);
				while ($stmt->fetch())
				{
					$questions[] = array("id"=>$qid,"question"=>$q,"type"=>$t,"req"=>$r,"choices"=>array());
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		for ($i = 0; $i < count($add); $i++)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT q.questionid, q.question, q.type, q.req FROM questions_addons AS qa LEFT JOIN questions AS q ON q.questionid = qa.questionid WHERE q.venueid = ? AND (qa.addonid = ? OR qa.addonid = 0) AND q.deleted != 1"))
			{
				$stmt->bind_param("ii",$vid,$add[$i]);
				$stmt->execute();
				$stmt->bind_result($qid,$q,$t,$r);
				while ($stmt->fetch())
				{
					$questions[] = array("id"=>$qid,"question"=>$q,"type"=>$t,"req"=>$r,"choices"=>array());
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		for ($i = 0; $i < count($menus); $i++)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT q.questionid, q.question, q.type, q.req FROM questions_menus AS qa LEFT JOIN questions AS q ON q.questionid = qa.questionid WHERE q.venueid = ? AND (qa.menuid = ? OR qa.menuid = 0) AND q.deleted != 1"))
			{
				$stmt->bind_param("ii",$vid,$menus[$i]);
				$stmt->execute();
				$stmt->bind_result($qid,$q,$t,$r);
				while ($stmt->fetch())
				{
					$questions[] = array("id"=>$qid,"question"=>$q,"type"=>$t,"req"=>$r,"choices"=>array());
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		for ($i = 0; $i < count($pers); $i++)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT q.questionid, q.question, q.type, q.req FROM questions_personnel AS qa LEFT JOIN questions AS q ON q.questionid = qa.questionid WHERE q.venueid = ? AND (qa.personnelid = ? OR qa.personnelid = 0) AND q.deleted != 1"))
			{
				$stmt->bind_param("ii",$vid,$pers[$i]);
				$stmt->execute();
				$stmt->bind_result($qid,$q,$t,$r);
				while ($stmt->fetch())
				{
					$questions[] = array("id"=>$qid,"question"=>$q,"type"=>$t,"req"=>$r,"choices"=>array());
				}
				$GLOBALS['db']->CloseConn();
			}
		}
	}
	
	for ($i = 0; $i < count($questions)-1; $i++)
	{
		for ($i2 = $i+1; $i2 < count($questions); $i2++)
		{
			if ($questions[$i]['id'] == $questions[$i2]['id'])
			{
				unset($questions[$i2]);
				$questions = array_values($questions);
				$i2--;
			}
		}
	}
	
	$questions = array_values($questions);
	
	for ($i = 0; $i < count($questions); $i++)
	{
		if ($questions[$i]['type'] == "radio" || $questions[$i]['type'] == "select")
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT choiceid, choice FROM questions_choices WHERE questionid = ? ORDER BY choiceid ASC"))
			{
				$stmt->bind_param("i",$questions[$i]['id']);
				$stmt->execute();
				$stmt->bind_result($cid,$c);
				while ($stmt->fetch())
				{
					$questions[$i]['choices'][] = array("id"=>$cid,"choice"=>$c);
				}
				$GLOBALS['db']->CloseConn();
			}
		}
	}
	
	return $questions;
}

function IsNoLongerAvailable($booking,$id)
{
	$resources = array();
	$timezone = "";
	
	if ($booking)
	{
		$resources = &$booking['resources'];
		$timezone = $booking['timezone'];
	}
	else
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT r.resourceid, r.starttime, r.stoptime + r.cleanuptime, r2.timeslots, v.timezone FROM booking_resources AS r LEFT JOIN venues AS v ON v.venueid = r.venueid LEFT JOIN resources AS r2 ON r2.resourceid = br.resourceid WHERE r.bookingid = ?"))
		{			
			$stmt->bind_param("i",$id);
			$stmt->execute();
			$stmt->bind_result($r,$start,$stop,$ts,$tz);
			while ($stmt->fetch())
			{
				$resources[] = array("id"=>$r,"timeslots"=>$ts,"start"=>$start,"stop"=>$stop);
				$timezone = $tz;
			}
		}
	}
	
	foreach ($resources as &$resource)
	{
		$is = IsBooked($resource, $timezone, $resource['timeslots']);
		if ($is)
			return $is;
		
		$children = array();
		$tchildren = FindChildren($resource['id'],true);
		for ($i=0; $i<count($tchildren); $i++)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT timeslots FROM resources WHERE resourceid = ?"))
			{			
				$stmt->bind_param("i",$tchildren[$i]);
				$stmt->execute();
				$stmt->bind_result($ts);
				if ($stmt->fetch())
				{
					$children[] = array("id"=>$tchildren[$i],"start"=>$resource['start'],"stop"=>$resource['stop'],"timeslots"=>$ts);
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		foreach ($children as &$child)
		{
			$is = IsBooked($child, $timezone, $resource['timeslots']);
			if ($is)
				return $is;
		}
	}
	
	return false;
}

function IsBooked(&$resource, $timezone, $timeslots)
{	
	$is = false;
	$closed = false;
	
	$startmin=0;$stopmin=0;$firstdatetime=0;$startdatetime=0;$stopdatetime=0;
	CalcStartStopMinute($resource['start'],$resource['stop'],$timezone,$startmin,$stopmin,$firstdatetime,$startdatetime,$stopdatetime);
	
	/*
	//  Disabling this so that parent resource's availabilty is not restricted by the children's
	//   hours of operation
	//
	// Make sure that the resource is open (hours of operation) for the entire selection
	if ($timeslots == 0 && $resource['timeslots'] == 0)
	{	
		$sel = array();
		if ($stopmin <= 10080)
			$sel[] = array("start"=>$startmin,"stop"=>$stopmin);
		else while ($stopmin > 10080)
		{
			$sel[] = array("start"=>$startmin,"stop"=>10080);
			$startmin = 0;
			$stopmin -= 10080;
		}
	
		$hours = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT startminute,stopminute FROM resources_hours WHERE resourceid = ?"))
		{
			$stmt->bind_param("i",$resource['id']);
			$stmt->execute();
			$stmt->bind_result($start, $stop);
			while ($stmt->fetch())
			{
				$hours[] = array("start"=>$start,"stop"=>$stop);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		for ($i=0; $i<count($sel); $i++)
		{
			for ($i2=0; $i2<count($hours); $i2++)
			{
				if ($hours[$i2]['start'] < $sel[$i]['stop'] && $hours[$i2]['stop'] > $sel[$i]['start'])
				{
					//cut from sel
					if ($hours[$i2]['start'] <= $sel[$i]['start'] && $hours[$i2]['stop'] >= $sel[$i]['stop'])
					{
						$sel[$i]['start'] = -1;
						$sel[$i]['stop'] = -1;
						break;
					}
					
					if ($hours[$i2]['stop'] < $sel[$i]['stop'])
					{
						if ($hours[$i2]['start'] <= $sel[$i]['start'])
							$sel[$i]['start'] = $hours[$i2]['stop'];
						else
						{
							$sel[] = array("start"=>$hours[$i2]['stop'],"stop"=>$sel[$i]['stop']);
							$sel[$i]['stop'] = $hours[$i2]['start'];
						}
						
						$i2 = -1; // restart the loop
						continue;
					}
					
					if ($hours[$i2]['start'] > $sel[$i]['start'])
					{
						if ($hours[$i2]['stop'] >= $sel[$i]['stop'])
							$sel[$i]['stop'] = $hours[$i2]['start'];
						else
						{
							$sel[] = array("start"=>$hours[$i2]['stop'],"stop"=>$sel[$i]['stop']);
							$sel[$i]['stop'] = $hours[$i2]['start'];
						}
						
						$i2 = -1; // restart the loop
						continue;
					}
				}
			}
			
			// if any $sel left then it is closed during at least some of the selected time
			if ($sel[$i]['start'] != -1 || $sel[$i]['stop'] != -1)
			{
				$closed = true;
				break;
			}
		}
		
		if ($closed)
			return "closed";
	}
	*/
	
	// check to see if there are any bookings for this resource that overlap the selection
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT MAX(r.bookingid) FROM booking_resources AS r LEFT JOIN booking AS b ON b.bookingid = r.bookingid WHERE (b.status = 'Pending Payment' OR b.status = 'Paid' OR b.status = 'Imported' OR b.status = 'Past Due') AND r.resourceid = ? AND ? < r.stoptime + r.cleanuptime AND ? > r.starttime - r.cleanuptime"))
	{
		$s1 = $startdatetime->getTimestamp();
		$s2 = $stopdatetime->getTimestamp();
		
		$stmt->bind_param("iii",$resource['id'],$s1,$s2);
		$stmt->execute();
		$stmt->bind_result($i);
		if ($stmt->fetch())
		{
			if ($i > 0)
			{
				$is = $i;
			}
		}
		$GLOBALS['db']->CloseConn();
	}
		
	return $is;
}

function SnapToTimeslot(&$startmin, &$stopmin, &$resrates)
{
	// 'snap' the start and stop to the nearest defined start/stop for timeslots
	$modified = true;
	while ($modified)
	{
		$modified = false;
		$goodstart = false;
		$goodstop = false;
		
		for ($i=0; $i<count($resrates); $i++)
		{
			
			if ($startmin > $resrates[$i]['start'] && $startmin < $resrates[$i]['stop'])
				$startmin = $resrates[$i]['start'];
			if ($stopmin < $resrates[$i]['stop'] && $stopmin > $resrates[$i]['start'])
				$stopmin = $resrates[$i]['stop'];
			
			
			if ($startmin == $resrates[$i]['start'])
				$goodstart = true;
			if ($stopmin == $resrates[$i]['stop'])
				$goodstop = true;
		}
		
		if (!$goodstart)
		{
			$best = 9999999;
			for ($i=0; $i<count($resrates); $i++)
			{
				if ($resrates[$i]['start'] >= $startmin && $resrates[$i]['start'] - $startmin < $best - $startmin)
					$best = $resrates[$i]['start'];
			}
			
			if ($best == 9999999)
			{ // need to wrap the weekend, start from 0
				$startmin = 0;
				for ($i=0; $i<count($resrates); $i++)
				{
					if ($resrates[$i]['start'] >= $startmin && $resrates[$i]['start'] - $startmin < $best - $startmin)
						$best = $resrates[$i]['start'];
				}
				$best += 10080;
			}
			
			$startmin = $best;
			$modified = true;
		}
		
		if (!$goodstop)
		{
			$best = 9999999;
			for ($i=0; $i<count($resrates); $i++)
			{
				if ($resrates[$i]['stop'] >= $stopmin && $resrates[$i]['stop'] - $stopmin < $best - $stopmin)
					$best = $resrates[$i]['stop'];
			}
			
			if ($best == 9999999)
			{ // need to wrap the weekend, start from 0
				$stopmin = 0;
				for ($i=0; $i<count($resrates); $i++)
				{
					if ($resrates[$i]['stop'] >= $stopmin && $resrates[$i]['stop'] - $stopmin < $best - $stopmin)
						$best = $resrates[$i]['stop'];
				}
				$best += 10080;
			}
			
			$stopmin = $best;
			$modified = true;
		}
	}
}

function UpdateBookingPrices()
{	
	$_SESSION['booking']['start'] = 2147483647;
	$_SESSION['booking']['stop'] = 0;
	foreach ($_SESSION['booking']['resources'] as $r)
	{
		if ($r['start'] < $_SESSION['booking']['start'])
			$_SESSION['booking']['start'] = $r['start'];
		if ($r['stop'] > $_SESSION['booking']['stop'])
			$_SESSION['booking']['stop'] = $r['stop'];
	}
	
	$_SESSION['booking']['cost'] = 0;
	$_SESSION['booking']['promos_total'] = 0;
	$_SESSION['booking']['full_tax'] = 0;
	$_SESSION['booking']['bookingfee'] = 0;
	$_SESSION['booking']['bookingfee_tax'] = 0;
	$_SESSION['booking']['cleanupfee'] = 0;
	$_SESSION['booking']['deposit'] = 0;
	$_SESSION['booking']['full'] = $_SESSION['booking']['start'];
	$_SESSION['booking']['autoapprove'] = 1;
	
	if (!isset($_SESSION['booking']['personnel']))
		$_SESSION['booking']['personnel'] = array();
	
	$timezone = "";
	$salestax = 0.095;
	$bookingfee = 0.0;
	$approved = 0;
	$doPromos = false;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT v.timezone, v.salestax, v.bookingfee, v.currency, f.showPromos FROM venues AS v LEFT JOIN venues_functionality AS f ON f.venueid = v.venueid WHERE v.venueid = ?"))
	{
		$stmt->bind_param("i",$_SESSION['booking']['venueid']);
		$stmt->execute();
		$stmt->bind_result($tz,$tr,$bf,$cur,$p);
		if($stmt->fetch())
		{
			$timezone = $tz;
			$salestax = $tr;
			$bookingfee = $bf;
			$_SESSION['booking']['currency'] = $cur;
			$doPromos = $p;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	// Load resource information into the array
	for ($r1=0; $r1<count($_SESSION['booking']['resources']); $r1++)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT timeslots, default_rate, name, minduration, increment, min_lead_time, cleanuptime, cleanupcost, autoapprove FROM resources WHERE resourceid = ?"))
		{
			$stmt->bind_param("i",$_SESSION['booking']['resources'][$r1]['id']);
			$stmt->execute();
			$stmt->bind_result($timeslots, $rate, $name, $min, $inc, $lead, $ct, $cc, $auto);
			if ($stmt->fetch())
			{
				if ($timeslots == 1)
					$ct = 0;
				
				$_SESSION['booking']['resources'][$r1]['timeslots'] = $timeslots;
				$_SESSION['booking']['resources'][$r1]['default_rate'] = $rate;
				$_SESSION['booking']['resources'][$r1]['name'] = Sanitize($name);
				$_SESSION['booking']['resources'][$r1]['minduration'] = $min;
				$_SESSION['booking']['resources'][$r1]['increment'] = $inc;
				$_SESSION['booking']['resources'][$r1]['lead'] = $lead;
				$_SESSION['booking']['resources'][$r1]['cleanup'] = $ct;
				$_SESSION['booking']['resources'][$r1]['cleanupcost'] = $cc;
				$_SESSION['booking']['resources'][$r1]['rates'] = array();
					
				if ($auto < 1)
					$_SESSION['booking']['autoapprove'] = 0;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($_SESSION['booking']['resources'][$r1]['timeslots'] == 0)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT startminute, stopminute, rate FROM resources_rates WHERE resourceid = ? ORDER BY startminute ASC"))
			{
				$stmt->bind_param("i",$_SESSION['booking']['resources'][$r1]['id']);
				$stmt->execute();
				$stmt->bind_result($start,$stop,$rate);
				while ($stmt->fetch())
					$_SESSION['booking']['resources'][$r1]['rates'][] = array("start"=>$start,"stop"=>$stop,"rate"=>$rate);
				$GLOBALS['db']->CloseConn();
			}
		}
		else
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT startminute, stopminute, rate, combinable FROM resources_slots WHERE resourceid = ? ORDER BY startminute ASC"))
			{
				$stmt->bind_param("i",$_SESSION['booking']['resources'][$r1]['id']);
				$stmt->execute();
				$stmt->bind_result($start,$stop,$rate,$combinable);
				while ($stmt->fetch())
				{
					$_SESSION['booking']['resources'][$r1]['rates'][] = array("start"=>$start,"stop"=>$stop,"rate"=>$rate,"combinable"=>$combinable);
					
				}
				$GLOBALS['db']->CloseConn();
			}
		}
	}

	// For timeslot billing, this will set the resource start time to the start of the timeslot
	//  it lies within, it will set the resource stop time to the stop of the timeslot it lies
	//  within, and it will break up multi-timeslot bookings if those timeslots cannot be combined.
	for ($r1=0; $r1<count($_SESSION['booking']['resources']); $r1++)
	{
		$resrates = $_SESSION['booking']['resources'][$r1]['rates'];
		$startmin=0;$stopmin=0;$firstdatetime=0;$startdatetime=0;$stopdatetime=0;
		CalcStartStopMinute($_SESSION['booking']['resources'][$r1]['start'],$_SESSION['booking']['resources'][$r1]['stop'],$timezone,$startmin,$stopmin,$firstdatetime,$startdatetime,$stopdatetime);
		
		while ($startmin < 0)
			$startmin += 10080;
		while ($stopmin < 0)
			$stopmin += 10080;
		while ($stopmin < $startmin)
			$stopmin += 10080;
		
		if ($_SESSION['booking']['resources'][$r1]['timeslots'] == 1)
		{			
			// split into multiple reservations if timeslots are not combinable, and snap all to valid timeslots
			$modified = true;
			while ($modified)
			{
				SnapToTimeslot($startmin,$stopmin,$resrates);
				
				$modified = false;
				for ($i=0; $i<count($resrates); $i++)
				{
					if ($startmin == $resrates[$i]['start'] && $stopmin != $resrates[$i]['stop'] && $resrates[$i]['combinable'] == 0)
					{
						$newres = arrayCopy($_SESSION['booking']['resources'][$r1]);
						$newres['start'] = $firstdatetime->getTimestamp() + ($resrates[$i]['stop'] * 60);
						$_SESSION['booking']['resources'][] = $newres;
						//echo " adding ";
						$stopmin = $resrates[$i]['stop'];
					}
					
					if ($stopmin == $resrates[$i]['stop'] && $startmin != $resrates[$i]['start'] && $resrates[$i]['combinable'] == 0)
					{
						$newres = arrayCopy($_SESSION['booking']['resources'][$r1]);
						$newres['stop'] = $firstdatetime->getTimestamp() + ($resrates[$i]['start'] * 60);
						$_SESSION['booking']['resources'][] = $newres;
						//echo " adding ";
						$startmin = $resrates[$i]['start'];
					}
					
					if ($resrates[$i]['start'] > $startmin && $resrates[$i]['stop'] < $stopmin && $resrates[$i]['combinable'] == 0)
					{
						$newres = arrayCopy($_SESSION['booking']['resources'][$r1]);
						$newres['start'] = $firstdatetime->getTimestamp() + ($resrates[$i]['start'] * 60);
						$newres['stop'] = $firstdatetime->getTimestamp() + ($resrates[$i]['stop'] * 60);
						$_SESSION['booking']['resources'][] = $newres;
						
						$newres2 = arrayCopy($_SESSION['booking']['resources'][$r1]);
						$newres2['start'] = $firstdatetime->getTimestamp() + ($resrates[$i]['stop'] * 60);
						$newres2['stop'] = $firstdatetime->getTimestamp() + ($stopmin * 60);
						$_SESSION['booking']['resources'][] = $newres2;
						
						//echo " adding ";
						$stopmin = $resrates[$i]['start'];
						SnapToTimeslot($startmin,$stopmin,$resrates);
						$modified = true;
					}
				}
			}
			
			$_SESSION['booking']['resources'][$r1]['start'] = $firstdatetime->getTimestamp() + ($startmin * 60);
			$_SESSION['booking']['resources'][$r1]['stop'] = $firstdatetime->getTimestamp() + ($stopmin * 60);
		}
	}
	
	// For timeslot billing, this will combine all combinable timeslots
	$modified = true;
	while ($modified)
	{
		$modified = false;
		for ($r1=0; $r1<count($_SESSION['booking']['resources']); $r1++)
		{
			$resrates = $_SESSION['booking']['resources'][$r1]['rates'];
			$startmin=0;$stopmin=0;$firstdatetime=0;$startdatetime=0;$stopdatetime=0;
			CalcStartStopMinute($_SESSION['booking']['resources'][$r1]['start'],$_SESSION['booking']['resources'][$r1]['stop'],$timezone,$startmin,$stopmin,$firstdatetime,$startdatetime,$stopdatetime);
			
			while ($startmin < 0)
				$startmin += 10080;
			while ($stopmin < 0)
				$stopmin += 10080;
			while ($stopmin < $startmin)
				$stopmin += 10080;
			
			if ($_SESSION['booking']['resources'][$r1]['timeslots'] == 1)
			{	
				//  This will also combine timeslots if they can be combined.
				if (count($_SESSION['booking']['resources']) > 1)
				{
					$combinable = 1;
					
					for ($i=0; $i<count($resrates); $i++)
					{
						if ($startmin <= $resrates[$i]['start'] && $stopmin >= $resrates[$i]['stop'] && $resrates[$i]['combinable'] == 0)
							$combinable = 0;
					}
					
					if ($combinable == 1)
					{
						$nextstart = 999999;
						$nextcomb = 0;
						
						for ($i=0; $i<count($resrates); $i++)
						{
							if ($resrates[$i]['start'] > $stopmin && $resrates[$i]['start'] - $stopmin < $nextstart - $stopmin)
							{
								$nextstart = $resrates[$i]['start'];
								$nextcomb = $resrates[$i]['combinable'];
							}
						}
						if ($nextstart == 999999)
						{
							for ($i=0; $i<count($resrates); $i++)
							{
								if ($resrates[$i]['start'] > $stopmin && $resrates[$i]['start'] - $stopmin < $nextstart - $stopmin)
								{
									$nextstart = $resrates[$i]['start'];
									$nextcomb = $resrates[$i]['combinable'];
								}
							}
						}
						
						if ($nextcomb == 1 && $nextstart != 999999)
						{
							for ($r2=0; $r2<count($_SESSION['booking']['resources']); $r2++)
							{
								if ($r2 != $r1 && $_SESSION['booking']['resources'][$r2]['id'] == $_SESSION['booking']['resources'][$r1]['id'] 
									&& $_SESSION['booking']['resources'][$r2]['timeslots'] == 1)
								{
									$startmin2=0;$stopmin2=0;$firstdatetime2=0;$startdatetime2=0;$stopdatetime2=0;
									CalcStartStopMinute($_SESSION['booking']['resources'][$r2]['start'],$_SESSION['booking']['resources'][$r2]['stop'],$timezone,$startmin2,$stopmin2,$firstdatetime2,$startdatetime2,$stopdatetime2);
									
									if ($startmin2 == $nextstart)
									{
										$stopmin = $stopmin2;
										$_SESSION['booking']['resources'][$r1]['stop'] = $firstdatetime->getTimestamp() + ($stopmin * 60);
										unset($_SESSION['booking']['resources'][$r2]);
										$_SESSION['booking']['resources'] = array_values($_SESSION['booking']['resources']);
										$r2--;
										if ($r2 < $r1)
											$r1--;
										
										$modified = true;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	
	for ($r1=0; $r1<count($_SESSION['booking']['resources']); $r1++)
	{
		$_SESSION['booking']['resources'][$r1]['cost'] = 0;
		if (!isset($_SESSION['booking']['resources'][$r1]['addons']))
			$_SESSION['booking']['resources'][$r1]['addons'] = array();
	
		$resrates = $_SESSION['booking']['resources'][$r1]['rates'];
		$startmin=0;$stopmin=0;$firstdatetime=0;$startdatetime=0;$stopdatetime=0;
		CalcStartStopMinute($_SESSION['booking']['resources'][$r1]['start'],$_SESSION['booking']['resources'][$r1]['stop'],$timezone,$startmin,$stopmin,$firstdatetime,$startdatetime,$stopdatetime);
		
		while ($startmin < 0)
			$startmin += 10080;
		while ($stopmin < 0)
			$stopmin += 10080;
		while ($stopmin < $startmin)
			$stopmin += 10080;
		
		if ($_SESSION['booking']['resources'][$r1]['timeslots'] == 0)
		{
			$arr = array();
			$arr[] = array("start"=>$startmin,"stop"=>$stopmin);
			
			while (count($arr) > 0)
			{
				$modified = false;
				
				foreach ($resrates as $r)
				{
					if ($r['start'] < $arr[0]['stop'] && $r['stop'] > $arr[0]['start'])
					{	
						$start = 0;
						$stop = 0;
						
						if ($r['start'] <= $arr[0]['start'])
							$start = $arr[0]['start'];
						else 
						{
							$start = $r['start'];
							$arr[] = array("start"=>$arr[0]['start'],"stop"=>$r['start']);
						}
						
						if ($r['stop'] >= $arr[0]['stop'])
							$stop = $arr[0]['stop'];
						else 
						{
							$stop = $r['stop'];
							$arr[] = array("start"=>$r['stop'],"stop"=>$arr[0]['stop']);
						}
						
						$_SESSION['booking']['resources'][$r1]['cost'] += ($stop - $start) * ($r['rate'] / 60);
						array_splice($arr,0,1);
						$modified = true;
						break;
					}
				}
				
				if (!$modified)
				{
					$_SESSION['booking']['resources'][$r1]['cost'] += ($arr[0]['stop'] - $arr[0]['start']) * ($_SESSION['booking']['resources'][$r1]['default_rate'] / 60);
					array_splice($arr,0,1);
				}
			}
		}
		else
		{
			foreach ($resrates as $r)
			{
				if ($r['start'] < $stopmin && $r['stop'] > $startmin)
					$_SESSION['booking']['resources'][$r1]['cost'] += $r['rate'];
			}
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT deposit_dollar_thresh, deposit_perc, deposit_amount, full_due, refund_policyid FROM resources AS r LEFT JOIN deposit_policies AS d ON d.policyid = r.deposit_policyid WHERE r.resourceid = ?"))
		{
			$stmt->bind_param("i",$_SESSION['booking']['resources'][$r1]['id']);
			$stmt->execute();
			$stmt->bind_result($dt,$dp,$da,$fd,$rp);
			if ($stmt->fetch())
			{
				$_SESSION['booking']['cleanupfee'] += $_SESSION['booking']['resources'][$r1]['cleanupcost'];
				
				if ($_SESSION['booking']['resources'][$r1]['cost'] > $dt)
				{
					$_SESSION['booking']['deposit'] += ceil($dp * ($_SESSION['booking']['resources'][$r1]['cost'] + $_SESSION['booking']['resources'][$r1]['cleanupcost']) * (1 + $salestax)) / 100;
					$_SESSION['booking']['deposit'] += $da;
					
					if ($_SESSION['booking']['resources'][$r1]['start'] - ($fd * 86400) < $_SESSION['booking']['full'])
						$_SESSION['booking']['full'] = $_SESSION['booking']['resources'][$r1]['start'] - ($fd * 86400);
				}
				
				$_SESSION['booking']['resources'][$r1]['refund_policyid'] = $rp;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT a.addonid, a.min FROM addons AS a LEFT JOIN resources_addons AS r ON r.addonid = a.addonid WHERE r.resourceid = ? AND r.min > 0"))
		{
			$stmt->bind_param("i",$_SESSION['booking']['resources'][$r1]['id']);
			$stmt->execute();
			$stmt->bind_result($id,$min);
			while ($stmt->fetch())
			{
				$matched = false;
				for ($i=0; $i<count($_SESSION['booking']['addons']); $i++)
				{
					if ($_SESSION['booking']['addons'][$i]['id'] == $id)
					{
						$matched = true;
						break;
					}
				}
				
				if (!$matched)
					$_SESSION['booking']['addons'][] = array('id'=>$id,'quantity'=>$min);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($_SESSION['booking']['resources'][$r1]['addons'] as &$addon)
		{			
			$addon['cost'] = 0;
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT a.name, a.min, a.max, deposit_dollar_thresh, deposit_perc, deposit_amount, full_due, refund_policyid, price FROM addons AS a LEFT JOIN deposit_policies AS d ON d.policyid = a.deposit_policyid WHERE a.addonid = ?"))
			{
				$stmt->bind_param("i",$addon['id']);
				$stmt->execute();
				$stmt->bind_result($name,$min,$max,$dt,$dp,$da,$fd,$rp,$p);
				if ($stmt->fetch())
				{
					$addon['name'] = Sanitize($name);
					$addon['price'] = $p;
					if ($addon['quantity'] < $min)
						$addon['quantity'] = $min;
					if ($addon['quantity'] > $max)
						$addon['quantity'] = $max;
					
					$addon['cost'] = $addon['price'] * $addon['quantity'];
					
					if ($addon['cost'] > $dt)
					{
						$_SESSION['booking']['deposit'] += ceil($dp * $addon['cost'] * (1 + $salestax)) / 100;
						$_SESSION['booking']['deposit'] += $da;
						
						if ($_SESSION['booking']['resources'][$r1]['start'] - ($fd * 86400) < $_SESSION['booking']['full'])
							$_SESSION['booking']['full'] = $_SESSION['booking']['resources'][$r1]['start'] - ($fd * 86400);
					}
					$addon['refund_policyid'] = $rp;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$_SESSION['booking']['cost'] += $addon['cost'];
		}
		
		$_SESSION['booking']['cost'] += $_SESSION['booking']['resources'][$r1]['cost'];
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT p.personnelid, p.req, p.min FROM personnel AS p LEFT JOIN personnel_resources AS r ON r.personnelid = p.personnelid WHERE p.req > 0  AND (r.resourceid = ? OR r.resourceid = 0) AND p.venueid = ?"))
		{
			$stmt->bind_param("ii",$_SESSION['booking']['resources'][$r1]['id'],$_SESSION['booking']['venueid']);
			$stmt->execute();
			$stmt->bind_result($id,$req,$min);
			while ($stmt->fetch())
			{
				$matched = false;
				for ($i=0; $i<count($_SESSION['booking']['personnel']); $i++)
				{
					if ($_SESSION['booking']['personnel'][$i]['id'] == $id)
					{
						$matched = true;
						if (isset($_SESSION['booking']['personnel'][$i]['reqFor']) && $_SESSION['booking']['personnel'][$i]['reqFor'] >= ceil(($_SESSION['booking']['resources'][$r1]['stop'] - $_SESSION['booking']['resources'][$r1]['start']) / 3600))
							true;
						else $_SESSION['booking']['personnel'][$i]['reqFor'] = ceil(($_SESSION['booking']['resources'][$r1]['stop'] - $_SESSION['booking']['resources'][$r1]['start']) / 3600);
					}
				}
				
				if (!$matched)
					$_SESSION['booking']['personnel'][] = array('id'=>$id,'quantity'=>0,'reqFor'=>ceil(($_SESSION['booking']['resources'][$r1]['stop'] - $_SESSION['booking']['resources'][$r1]['start']) / 3600));
			}
			$GLOBALS['db']->CloseConn();
		}
	}	
		
	foreach ($_SESSION['booking']['menus'] as &$menu)
	{
		$menu['cost'] = 0;
		
		foreach ($menu['items'] as &$item)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT name, price FROM menus_items WHERE menuid = ? AND itemid = ?"))
			{
				$stmt->bind_param("ii",$menu['id'],$item['id']);
				$stmt->execute();
				$stmt->bind_result($n,$p);
				if ($stmt->fetch())
				{
					$item['name'] = Sanitize($n);
					$item['price'] = $p;
					$item['cost'] = $item['price'] * $item['quantity'];
					$menu['cost'] += $item['cost'];
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT deposit_dollar_thresh, deposit_perc, deposit_amount, full_due, refund_policyid FROM menus AS m LEFT JOIN deposit_policies AS d ON d.policyid = m.deposit_policyid WHERE m.menuid = ?"))
		{
			$stmt->bind_param("i",$menu['id']);
			$stmt->execute();
			$stmt->bind_result($dt,$dp,$da,$fd,$rp);
			if ($stmt->fetch())
			{
				if ($menu['cost'] > $dt)
				{
					$_SESSION['booking']['deposit'] += ceil($dp * $menu['cost'] * (1 + $salestax)) / 100;
					$_SESSION['booking']['deposit'] += $da;
					
					if ($menu['deliverat'] - ($fd * 86400) < $_SESSION['booking']['full'])
						$_SESSION['booking']['full'] = $menu['deliverat'] - ($fd * 86400);
				}
				
				$menu['refund_policyid'] = $rp;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$_SESSION['booking']['cost'] += $menu['cost'];
	}
		
	foreach ($_SESSION['booking']['personnel'] as &$personnel)
	{
		$personnel['cost'] = 0; 
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT p.name, p.price, p.min, p.max, p.req, deposit_dollar_thresh, deposit_perc, deposit_amount, full_due, p.refund_policyid FROM personnel AS p LEFT JOIN deposit_policies AS d ON d.policyid = deposit_policyid WHERE p.personnelid = ?"))
		{
			$stmt->bind_param("i",$personnel['id']);
			$stmt->execute();
			$stmt->bind_result($n,$p,$min,$max,$req,$dt,$dp,$da,$fd,$rp);
			if ($stmt->fetch())
			{				
				$personnel['name'] = Sanitize($n);
				$personnel['price'] = $p;
				$personnel['refund_policyid'] = $rp;
				
				if ($personnel['quantity'] < $min)
					$personnel['quantity'] = $min;
				if (!$req && $personnel['quantity'] > $max)
					$personnel['quantity'] = $max;
				
				/// set the quantity based on the # of staff required for the number of attendees
				$num = 0;
				$group = 0;
				if (isset($_SESSION['booking']['info']['headcount']))
					$group = $_SESSION['booking']['info']['headcount'];
				// round down if <10% over the threshold
				if ($req && $group)
				{
					$num = floor($group / $req);
					if (($group % $req) / $req > 0.1)
						$num++;
				}
				
				$reqf = (isset($personnel['reqFor']) ? $personnel['reqFor'] : 0);
				$minq = $num * $reqf;
				if ($req && $personnel['quantity'] < $minq)
					$personnel['quantity'] = $minq;
				
				/// double-check the min/max bounds
				if ($personnel['quantity'] != 0 || $req)
				{
					if ($personnel['quantity'] < $min)
						$personnel['quantity'] = $min;
					if (!$req && $personnel['quantity'] > $max)
						$personnel['quantity'] = $max;
					if ($req && $personnel['quantity'] < $minq)
						$personnel['quantity'] = $minq;
				}
				
				$personnel['cost'] = $personnel['price'] * $personnel['quantity'];
				
				if ($personnel['cost'] > $dt)
				{
					$_SESSION['booking']['deposit'] += ceil($dp * $personnel['cost'] * (1 + $salestax)) / 100;
					$_SESSION['booking']['deposit'] += $da;
				}
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$_SESSION['booking']['cost'] += $personnel['cost'];
	}
	
	if (!$doPromos)
	{
		unset($_SESSION['booking']['promos']);
		$_SESSION['booking']['promos'] = array();
	}
	else
	{
		if (!isset($_SESSION['booking']['promos']))
			$_SESSION['booking']['promos'] = array();
		
		$p = array();
		foreach ($_SESSION['booking']['promos'] as $promo)
			$p[] = $promo['name'];
		
		// find any active auto-applicable promos and add to booking
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT name FROM promos WHERE venueid = ? AND auto = 1 AND status = 'active'"))
		{
			$stmt->bind_param("i",$_SESSION['booking']['venueid']);
			$stmt->execute();
			$stmt->bind_result($n);
			while ($stmt->fetch())
			{
				$p[] = $n;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		// remove duplicate codes
		for ($i=0; $i<count($p); $i++)
		{
			for ($i2=0; $i2<count($p); $i2++)
			{
				if ($i2 == $i)
					continue;
				
				if ($p[$i2] == $p[$i])
				{
					unset($p[$i2]);
					$p = array_values($p);
					
					if ($i2 < $i)
						$i--;
					$i2--;
				}
			}
		}		
		
		// Load all codes in list, leave out invalid auto-applied codes
		unset($_SESSION['booking']['promos']);
		$_SESSION['booking']['promos'] = array();
		foreach ($p as $promo)
		{
			$temp = LoadPromoCode($promo);
			if ($temp['id'] && !($temp['auto'] == 1 && ($temp['verified'] != 1 || $temp['discountAmount'] <= 0)))
				$_SESSION['booking']['promos'][] = $temp;
		}
		
		// find the best combination of codes
		$maxSingleID = null;
		$maxSingle = 0;
		$maxComb = 0;
		foreach ($_SESSION['booking']['promos'] as &$promo)
		{
			if ($promo['combinable'] == 0 && $promo['verified'] == 1 && $promo['discountAmount'] > $maxSingle)
			{
				$maxSingleID = $promo['id'];
				$maxSingle = $promo['discountAmount'];
			}
			
			if ($promo['combinable'] == 1 && $promo['verified'] == 1)
				$maxComb += $promo['discountAmount'];
		}
		
		// un-verify all but the best code combination
		if ($maxSingle > $maxComb && $maxSingleID)
		{
			for ($i=0; $i<count($_SESSION['booking']['promos']); $i++)
				if ($_SESSION['booking']['promos'][$i]['id'] != $maxSingleID)
					$_SESSION['booking']['promos'][$i]['verified'] = "This code cannot be combined with the best code available";
		}
		else
		{
			for ($i=0; $i<count($_SESSION['booking']['promos']); $i++)
				if ($_SESSION['booking']['promos'][$i]['combinable'] != 1)
					$_SESSION['booking']['promos'][$i]['verified'] = "This code cannot be combined with the best code combination available";
		}
		
		// strip out all of the backend info from promo codes in the array
		$arr = array();
		foreach ($_SESSION['booking']['promos'] as $p)
		{
			if ($p['verified'] == 1)
			{
				$_SESSION['booking']['promos_total'] += $p['discountAmount'];
				$arr[] = array("id"=>$p['id'],"name"=>$p['name'],"desc"=>$p['desc'],"discountAmount"=>$p['discountAmount'],"verified"=>$p['verified']);
			}
		}
		
		unset($_SESSION['booking']['promos']);
		$_SESSION['booking']['promos'] = &$arr;
	}
	
	$d1 = new DateTime();
	$d1->setTimestamp($_SESSION['booking']['full']);
	$d2 = new DateTime();
	if ($d1 < $d2)
		$_SESSION['booking']['full'] = $d2->getTimestamp();
	
	if (isset($_SESSION['booking']['adjustment']))
		$_SESSION['booking']['cost'] += $_SESSION['booking']['adjustment'];
	
	$_SESSION['booking']['cost'] -= $_SESSION['booking']['promos_total'];	
	$_SESSION['booking']['cost'] += $_SESSION['booking']['cleanupfee'];
	if ($_SESSION['booking']['cost'] < 0) $_SESSION['booking']['cost'] = 0;
	
	$_SESSION['booking']['full_tax'] = ceil($_SESSION['booking']['cost'] * $salestax * 100) / 100;
	$_SESSION['booking']['cost'] += $_SESSION['booking']['full_tax'];
	
	if ($_SESSION['booking']['deposit'] > $_SESSION['booking']['cost'])
		$_SESSION['booking']['deposit'] = $_SESSION['booking']['cost'];

	//$_SESSION['booking']['bookingfee'] = ceil(($bookingfee * $_SESSION['booking']['cost'] * 100) + ($bookingfee * $_SESSION['booking']['gratuity'] * 100)) / 100;
	$_SESSION['booking']['bookingfee'] = ceil(($bookingfee * $_SESSION['booking']['cost'] * 100) ) / 100;
	$_SESSION['booking']['bookingfee_tax'] = ceil($_SESSION['booking']['bookingfee'] * $salestax * 100) / 100;
	$_SESSION['booking']['cost'] += $_SESSION['booking']['bookingfee'] + $_SESSION['booking']['bookingfee_tax'];
	$_SESSION['booking']['deposit'] += $_SESSION['booking']['bookingfee'] + $_SESSION['booking']['bookingfee_tax'];
	
	if ($_SESSION['booking']['deposit'] > $_SESSION['booking']['cost'])
	{
		$_SESSION['booking']['deposit'] = $_SESSION['booking']['cost'];
	}
}

function CheckBookingErrors()
{
	if (IsNoLongerAvailable($_SESSION['booking'],null))
	{
		unset($_SESSION['booking']);
		return "One or more of the timeslots you selected are no longer available.";
	}
	
	foreach ($_SESSION['booking']['resources'] as &$resource)
	{
		$now = new DateTime();
		
		if ($resource['start'] - $resource['lead'] * 60 < $now->getTimestamp())
		{
			unset($_SESSION['booking']);
			return "The minimum lead-time requirement is not met for at least one room or resource.";
		}
			
		if ($resource['timeslots']  == 0)
		{
			if ($resource['stop'] - $resource['start'] < $resource['minduration'] * 60)
			{
				unset($_SESSION['booking']);
				return "The minimum reservation duration requirement is not met for at least one room or resource.";
			}
			
			if (($resource['stop'] - $resource['start']) % ($resource['increment'] * 60))
			{
				unset($_SESSION['booking']);
				return "The reservation duration was not of the allowed increment for at least one room or resource.";
			}
		}
	}
	
	return "success";
}

function InsertRateExceeded()
{
	$result = false;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT count(bookingid) FROM booking WHERE userid = ? AND timestamp > UNIX_TIMESTAMP()-60"))
	{
		$stmt->bind_param("i",$_SESSION['userid']);
		$stmt->execute();
		$stmt->bind_result($cnt);
		if ($stmt->fetch())
		{
			if ($cnt > 2)
				$result = true;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $result;
}

function InsertBooking()
{
	if (InsertRateExceeded())
		return "You have created too many bookings in too little time, please do not create them so quickly.";
		
	UpdateBookingPrices();
	$total = $_SESSION['booking']['cost'];
	if (isset($_SESSION['booking']['gratuity']))
		$total += $_SESSION['booking']['gratuity'];
	
	$result = CheckBookingErrors();
	if ($result != "success")
		return $result;
	
	if (!isset($_SESSION['userid']) || $_SESSION['userid'] < 0 || isset($_SESSION['booking']['info']['onbehalf']))
	{
		// automatically create an account for the contact_email if it doesn't already 
		//  have an account, otherwise use that existing account. Don't auto-login though, 
		//  that would make for a serious exploit.
		
		require_once('php/user.php');
		$uid = AutoRegister($_SESSION['booking']['info']['contact_email'],
							$_SESSION['booking']['info']['contact_name'],
							$_SESSION['booking']['info']['contact_phone'],
							$_SESSION['booking']['timezone']);
	}
	else $uid = $_SESSION['userid'];
	
	//echo "===>".$_SESSION['booking']['timezone'];

	//echo "Start=".$_SESSION['booking']['start'];
	//echo "Stop=".$_SESSION['booking']['stop'];

	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("INSERT INTO booking (venueid,userid,timestamp,start,stop,headcount,gratuity,total_cost,total_deposit,full_due,isnew,status) VALUES (?,?,UNIX_TIMESTAMP(),?,?,?,?,?,?,?,1,'Pending Deposit')"))
	{
		$stmt->bind_param("iiiiidddi",$_SESSION['booking']['venueid'],$uid,$_SESSION['booking']['start'],$_SESSION['booking']['stop'],
		$_SESSION['booking']['info']['headcount'],$_SESSION['booking']['gratuity'],$total,$_SESSION['booking']['deposit'],$_SESSION['booking']['full']);
		$stmt->execute();
		if ($stmt->affected_rows)
			$_SESSION['booking']['id'] = $stmt->insert_id;
		$GLOBALS['db']->CloseConn();
	}
	
	if (isset($_SESSION['booking']['id']))
	{
		foreach ($_SESSION['booking']['resources'] as $resource)
		{
			$children = FindChildren($resource['id'],false);
			foreach ($children as $child)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO booking_resources (bookingid,venueid,resourceid,starttime,stoptime,cleanuptime) VALUES (?,?,?,?,?,?)"))
				{
					$stmt->bind_param("iiiiii",$_SESSION['booking']['id'],$_SESSION['booking']['venueid'],$child,$resource['start'],$resource['stop'],$resource['cleanup']);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
			
			$refund = "";
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT policy FROM refund_policies WHERE policyid = ?"))
			{				
				$stmt->bind_param("i",$resource['refund_policyid']);
				$stmt->execute();
				$stmt->bind_result($p);
				if ($stmt->fetch())
				{
					$refund = $p;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_resources (bookingid,venueid,resourceid,starttime,stoptime,cost,cleanuptime,cleanupcost,refund_policyid,refund_policy) VALUES (?,?,?,?,?,?,?,?,?,?)"))
			{				
				$stmt->bind_param("iiiiididis",$_SESSION['booking']['id'],$_SESSION['booking']['venueid'],$resource['id'],$resource['start'],$resource['stop'],$resource['cost'],$resource['cleanup'],$resource['cleanupcost'],$resource['refund_policyid'],$refund);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			foreach ($resource['addons'] as $addon)
			{
				$arefund = "";
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT policy FROM refund_policies WHERE policyid = ?"))
				{				
					$stmt->bind_param("i",$addon['refund_policyid']);
					$stmt->execute();
					$stmt->bind_result($p);
					if ($stmt->fetch())
					{
						$arefund = $p;
					}
					$GLOBALS['db']->CloseConn();
				}
			
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO booking_addons (bookingid,resourceid,addonid,quantity,cost,deliverat,refund_policyid,refund_policy) VALUES (?,?,?,?,?,?,?,?)"))
				{
					$d = new DateTime($addon['deliverat']);
					$deliverat = $d->getTimestamp();
					
					$stmt->bind_param("iiiidiis",$_SESSION['booking']['id'],$resource['id'],$addon['id'],$addon['quantity'],$addon['cost'],$deliverat,$addon['refund_policyid'],$arefund);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
		}
		
		foreach ($_SESSION['booking']['personnel'] as $personnel)
		{
			$refund = "";
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT policy FROM refund_policies WHERE policyid = ?"))
			{				
				$stmt->bind_param("i",$personnel['refund_policyid']);
				$stmt->execute();
				$stmt->bind_result($p);
				if ($stmt->fetch())
				{
					$refund = $p;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_personnel (bookingid,personnelid,quantity,cost,refund_policyid,refund_policy) VALUES (?,?,?,?,?,?)"))
			{					
				$stmt->bind_param("iiddis",$_SESSION['booking']['id'],$personnel['id'],$personnel['quantity'],$personnel['cost'],$personnel['refund_policyid'],$refund);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		
		foreach ($_SESSION['booking']['questions'] as $question)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_questions (bookingid,questionid,answer) VALUES (?,?,?)"))
			{					
				$stmt->bind_param("iis",$_SESSION['booking']['id'],$question['id'],$question['answer']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		
		foreach ($_SESSION['booking']['menus'] as $menu)
		{
			$menuindex = -1;
			$d = new DateTime($menu['deliverat']);
			$deliverat = $d->getTimestamp();
			
			$refund = "";
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT policy FROM refund_policies WHERE policyid = ?"))
			{				
				$stmt->bind_param("i",$menu['refund_policyid']);
				$stmt->execute();
				$stmt->bind_result($p);
				if ($stmt->fetch())
				{
					$refund = $p;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_menus (bookingid,menuid,cost,deliverat,refund_policyid,refund_policy) VALUES (?,?,?,?,?,?)"))
			{
				$stmt->bind_param("iidiis",$_SESSION['booking']['id'],$menu['id'],$menu['cost'],$deliverat,$menu['refund_policyid'],$refund);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT menuindex FROM booking_menus WHERE bookingid = ? AND menuid = ? AND cost = ? AND deliverat = ?"))
			{
				$stmt->bind_param("iiii",$_SESSION['booking']['id'],$menu['id'],$menu['cost'],$deliverat);
				$stmt->execute();
				$stmt->bind_result($mi);
				if ($stmt->fetch())
				{
					$menuindex = $mi;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			if ($menuindex >= 0)
			{
				foreach ($menu['items'] as $item)
				{
						$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("INSERT INTO booking_menuitems (bookingid,menuindex,itemid,quantity,cost) VALUES (?,?,?,?,?)"))
					{
						$stmt->bind_param("iiiid",$_SESSION['booking']['id'],$menuindex,$item['id'],$item['quantity'],$item['cost']);
						$stmt->execute();
						$GLOBALS['db']->CloseConn();
					}
				}
			}
		}
		
		if (isset($_SESSION['booking']['promos']))
		{
			foreach ($_SESSION['booking']['promos'] as $p)
			{
				if ($p['verified'] != 1)
					continue;
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO booking_promos (bookingid, promoid, amount) VALUES (?,?,?)"))
				{
					$stmt->bind_param("iid",$_SESSION['booking']['id'],$p['id'],$p['discountAmount']);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_info (bookingid, name, description, comments, contact_name, contact_company, contact_title, contact_phone, contact_email, contact_website) VALUES (?,?,?,?,?,?,?,?,?,?)"))
		{
			$stmt->bind_param("isssssssss",$_SESSION['booking']['id'],$_SESSION['booking']['info']['name'],
				$_SESSION['booking']['info']['description'],$_SESSION['booking']['info']['comments'],$_SESSION['booking']['info']['contact_name'],
				$_SESSION['booking']['info']['contact_company'],$_SESSION['booking']['info']['contact_title'],$_SESSION['booking']['info']['contact_phone'],
				$_SESSION['booking']['info']['contact_email'],$_SESSION['booking']['info']['contact_website']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_adjustments (bookingid,name,fee) VALUES (?,'full_tax',?)"))
		{
			$stmt->bind_param("id",$_SESSION['booking']['id'],$_SESSION['booking']['full_tax']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_adjustments (bookingid,name,fee) VALUES (?,'booking_fee',?)"))
		{
			$stmt->bind_param("id",$_SESSION['booking']['id'],$_SESSION['booking']['bookingfee']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_adjustments (bookingid,name,fee) VALUES (?,'booking_fee_tax',?)"))
		{
			$stmt->bind_param("id",$_SESSION['booking']['id'],$_SESSION['booking']['bookingfee_tax']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		if (isset($_SESSION['booking']['adjustment']))
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_adjustments (bookingid,name,fee) VALUES (?,'invoice_adjustment',?)"))
			{
				$stmt->bind_param("id",$_SESSION['booking']['id'],$_SESSION['booking']['adjustment']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_status (bookingid,timestamp,status,changedby) VALUES (?,UNIX_TIMESTAMP(),'Pending Deposit',?)"))
		{
			$stmt->bind_param("ii",$_SESSION['booking']['id'],$uid);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		BookingStateChanged($_SESSION['booking']['id']);
		$result = $_SESSION['booking']['id'];
		$_SESSION['lastInsertedBooking'] = $result;
		unset($_SESSION['booking']);
	}
	else $result = "There was a problem saving your reservation!  We are sorry for the inconvenience, please contact support with the details of your reservation request.";
	
	return $result;
}

function GetRefundPolicies($ids)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT policyid, policy FROM refund_policies WHERE policyid IN (".$ids.") ORDER BY policyid ASC"))
	{		
		$stmt->execute();
		$stmt->bind_result($id, $policy);
		while ($stmt->fetch())
		{
			$arr[] = array("id"=>$id,"policy"=>$policy);
		}
		$GLOBALS['db']->CloseConn();
	}
	return $arr;
}

function GetBookingRefundPolicies($id)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT DISTINCT refund_policyid, refund_policy FROM booking_resources WHERE bookingid = ? AND refund_policy IS NOT NULL
					UNION SELECT DISTINCT refund_policyid, refund_policy FROM booking_personnel WHERE bookingid = ? AND refund_policy IS NOT NULL
					UNION SELECT DISTINCT refund_policyid, refund_policy FROM booking_menus WHERE bookingid = ? AND refund_policy IS NOT NULL
					UNION SELECT DISTINCT refund_policyid, refund_policy FROM booking_addons WHERE bookingid = ? AND refund_policy IS NOT NULL"))
	{	
		$stmt->bind_param("iiii",$id,$id,$id,$id);
		$stmt->execute();
		$stmt->bind_result($id, $policy);
		while ($stmt->fetch())
		{
			$arr[] = array("id"=>$id,"policy"=>$policy);
		}
		$GLOBALS['db']->CloseConn();
	}
	return $arr;
}
function checkparents($id)
{
	$arr = array();
	$arr2 = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT parentid, relation FROM resources_relationships WHERE childid = ? AND (relation = 'inside_linked') ORDER BY childid"))
	{		
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($pid,$relation);
		while ($stmt->fetch())
		{
			$arr[] = $pid;
		}
		$GLOBALS['db']->CloseConn();
	}
	foreach ($arr as $a)
	{
		$arr2 = array_merge($arr2,checkparents($a));
	}
	$arr = array_merge($arr,$arr2);
	
	return $arr;
}

function FindParents($id)
{
	$arr = array();
	$arr2 = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT parentid, relation FROM resources_relationships WHERE childid = ? AND (relation = 'inside_linked' or relation = 'inside_unlinked') ORDER BY childid"))
	{		
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($pid,$relation);
		while ($stmt->fetch())
		{
			$arr[] = $pid;
		}
		$GLOBALS['db']->CloseConn();
	}
	foreach ($arr as $a)
	{
		$arr2 = array_merge($arr2,FindParents($a));
	}
	$arr = array_merge($arr,$arr2);
	
	return $arr;
}

function FindChildren($id,$unlinked = false)
{
	$arr = array();
	$arr2 = array();
	
	$stmt = $GLOBALS['db']->stmt_init();

	if ($stmt->prepare("SELECT r.childid FROM resources_relationships AS r LEFT JOIN resources AS r2 ON r2.resourceid = r.childid WHERE r2.status = 'new' AND r.parentid = ? AND (relation = 'inside_linked' ".($unlinked?"OR relation = 'inside_unlinked'":"").") ORDER BY parentid"))
	{		
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($cid);
		while ($stmt->fetch())
		{
			if($relation == 'inside_linked')
			$arr[] = $cid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	// if unlinked was false before, then we found all of the linked children, now we need to find all linked and unlinked children of those children
	$unlinked = true;
	
	foreach ($arr as $a)
	{
		$arr2 = array_merge($arr2,FindChildren($a,$unlinked));
	}
	$arr = array_merge($arr,$arr2);
	
	return $arr;
}

function UpdateBookingStatus($bid, $vid, $action, $message, $isVenue)
{	
	//echo "sdfsdfsdfsdfsdf";

	$message = Sanitize($message);
	$origStatus = "";
	
	require_once('php/email.php');
	
	if ($isVenue == 0)
	{
		$auth = false;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT venueid,status FROM booking WHERE bookingid = ? AND userid = ?"))
		{		
			$stmt->bind_param("ii",$bid,$_SESSION['userid']);
			$stmt->execute();
			$stmt->bind_result($v,$s);
			if ($stmt->fetch())
			{
				$auth = true;
				$vid = $v;
				$origStatus = $s;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if (!$auth)
			return "You are not authorized to make this request";
	}
	
	if ($isVenue == 1)
	{
		$auth = false;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT venueid,status FROM booking WHERE bookingid = ?"))
		{		
			$stmt->bind_param("i",$bid);
			$stmt->execute();
			$stmt->bind_result($v,$s);
			if ($stmt->fetch())
			{
				if ($vid == $v)
					$auth = true;
				$origStatus = $s;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if (!$auth)
			return "You are not authorized to make this request";
	}
	
	if ($isVenue == 2)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT status FROM booking WHERE bookingid = ?"))
		{		
			$stmt->bind_param("i",$bid);
			$stmt->execute();
			$stmt->bind_result($s);
			if ($stmt->fetch())
			{
				$origStatus = $s;
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	$status = "error";
	$msg = $message;
	$v1 = 0;
	$v2 = 0;
	
	switch ($isVenue)
	{
		case 0:
			$v1 = 1;
			break;
		default:
			$v2 = 1;
	}
	
	switch ($action)
	{
		case "approve":

	

			$locked = false;
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT status FROM venues_subscriptions WHERE venueid = ?"))
			{	
				$stmt->bind_param("i",$vid);
				$stmt->execute();
				$stmt->bind_result($s);
				if ($stmt->fetch())
				{
					if ($s == "canceled")
						$locked = true;
				}
				$GLOBALS['db']->CloseConn();
			}
			if ($locked)
				return "This venue account has been restricted because the subscription plan is not in good standing.";
			
			if (IsNoLongerAvailable(null,$bid))
				return "not available";
			//$status = "Pending Payment"; // This line is commented by Harish (12-May-2016). 
			                               // The issue was when we made payment and clicked on approved button, 
			                               // still it shows Pending Payement, 
			                               // and the slot still availbale for booking, So i comment this line and add below line
			

			$status = "Paid";
			$msg = "Booking approved by venue";
			SendBookingApprovedMessage($bid);
			break;
		
		case "deny":
			$status = "Denied";
			$msg = "Booking denied by venue.  Reason given: ".$message;
			SendBookingDeniedMessage($bid,$message);
			break;
			
		case "cancel":
			if ($isVenue == 1 || $isVenue == 2)
			{
				$status = "Cancelled by Venue";
				$msg = "Booking cancelled by venue, all online payments will be refunded.  Reason given: ".$message;
				SendBookingCancelledByVenueMessage($bid,$message);
			}
			else if ($isVenue == 0)
			{
				$status = "Cancelled by User";
				$msg = "Booking cancelled by user.  Reason given: ".$message;
				SendBookingCancelledByUserMessage($bid,$message);
			}
			break;
			
		case "cancel_keep":
			if ($isVenue == 1 || $isVenue == 2)
			{
				$status = "Cancelled by Venue (past due)";
				$msg = "Booking cancelled by venue, payment is past due.  Cancellation explanation: ".$message;
				SendBookingCancelledPastDueMessage($bid,$message);
			}
			break;
			
		case "Paid":
			$status = "Paid";
			$v2 = 1;
			$msg = "This booking has been paid in full";
			break;
			
		case "Deposit Paid":
			$status = "Pending Approval";
			SendBookingApprovalNeededMessage($bid);
			break;
			
		case "refunded":
			MarkAsRefunded($bid,$vid);
			$status = null;
			break;
			
		case "closed":
			$status = "closed";
			$msg = "All online payments/refunds have been settled, this booking is now closed";
			break;
			
		case "message":
			$status = null;
			if ($v1 == 0)
				SendUserNewMessageMessage($bid,$message);
			if ($v2 == 0)
				SendVenueNewMessageMessage($bid,$message);
			break;
			
		// No longer needed as of 9/29/2015
		case "Deposit Time Expired":
			$status = "Deposit Time Expired";
			$msg = "The deposit for this booking was not paid within the allowed time, this booking has been automatically cancelled and any online payments made will be refunded.";
			SendDepositExpiredMessage($bid);
			break;
			
		case "Approval Expired":
			$status = "Approval Expired";
			$msg = "This booking was not approved within the allowed time, this booking has been automatically cancelled and any online payments made will be refunded.";
			SendApprovalExpiredMessage($bid);
			break;
		
		case "approval_warning":
			$status = null;
			$msg = "Notice for venue management: This booking must be approved within the next ".$message." hours or it will be automatically denied.";
			SendApprovalWarningMessage($bid);
			break;
		
		case "payment_warning":
			$status = null;
			$msg = "Notice: This booking must be paid in full within the next ".$message." hours or it may be cancelled and any deposit paid may be forfeited.";
			SendFullPaymentWarningMessage($bid);
			break;
			
		case "Past Due":
			$status = "Past Due";
			$msg = "This booking has not paid in full within the allowed time, the venue may now cancel the booking and any deposit paid may be forfeited.";
			SendFullPaymentExpiredMessage($bid);
			break;
	}
	
	$aff = 0;
	if ($status)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE booking SET status = ? WHERE bookingid = ? AND venueid = ?"))
		{		
			$stmt->bind_param("sii",$status,$bid,$vid);
			$stmt->execute();
			$aff = $stmt->affected_rows;
			$GLOBALS['db']->CloseConn();
		}
	}
	if (!$status || $aff > 0)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_messages (bookingid,userid,timestamp,isvenue,message,viewed_by_user,viewed_by_venue) VALUES (?,?,UNIX_TIMESTAMP(),?,?,?,?)"))
		{		
			$stmt->bind_param("iiisii",$bid,$_SESSION['userid'],$isVenue,$msg,$v1,$v2);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		if ($status)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_status (bookingid,timestamp,status,changedby) VALUES (?,UNIX_TIMESTAMP(),?,?)"))
			{		
				$stmt->bind_param("isi",$bid,$status,$_SESSION['userid']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			BookingStateChanged($bid);
		}
		
		if ($action == "approval_warning" || $action == "payment_warning" || $action == "Past Due")
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE booking_status SET notified = 1 WHERE bookingid = ? AND status = ?"))
			{					
				$stmt->bind_param("is",$bid,$origStatus);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		
		return "success";
	}
}

function MarkAsRefunded($bid,$vid)
{
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT p.paymentid, p.amount, p.currency FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.bookingid = ? AND p.status = 'pending_refund'"))
	{		
		$stmt->bind_param("i",$bid);
		$stmt->execute();
		$stmt->bind_result($pid,$a,$cur);
		while ($stmt->fetch())
		{
			$arr[] = array("id"=>$pid,"amount"=>$a,"currency"=>$cur);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	for ($i = 0; $i < count($arr); $i++)
	{
		$str = "Payment of ".FormatCurrency(abs($arr[$i]['amount']),$arr[$i]['currency'])." marked as refunded by venue";
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE payments SET status = 'processed' WHERE paymentid = ?"))
		{		
			$stmt->bind_param("i",$arr[$i]['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO payments_status (paymentid,userid,timestamp,status) VALUES (?,?,UNIX_TIMESTAMP(),?)"))
		{		
			$stmt->bind_param("iis",$arr[$i]['id'],$_SESSION['userid'],$str);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		UpdateBookingStatus($bid,$vid,"message",$str,1);	
	}
}

function LoadBooking($bid)
{
	$approved = CheckBookingAuth($bid,2);
	if (!$approved)
		return "You are not authorized to make this request";
		
	CheckPromoExpiration($bid);
	
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT bookingid,b.start,b.timestamp,venueid,eventid,b.userid,u.email,headcount,gratuity,total_cost,total_deposit,full_due,isnew,status FROM booking AS b LEFT JOIN users AS u ON u.userid = b.userid WHERE bookingid = ?"))
	{	
		$stmt->bind_param("i",$bid);
		$stmt->execute();
		$stmt->bind_result($bid,$start,$ts,$vid,$eid,$uid,$uemail,$hc,$gra,$tc,$td,$fd,$in,$st);
		if ($stmt->fetch())
		{
			$arr = array("id"=>$bid,"venueid"=>$vid,"start"=>$start,"timestamp"=>$ts,"imvenue"=>0,"eventid"=>$eid,"userid"=>$uid,"useremail"=>$uemail,"headcount"=>$hc,"gratuity"=>$gra,"cost"=>$tc,"deposit"=>$td,"promos_total"=>0,"tax"=>0,"bookingfee"=>0,"bookingfee_tax"=>0,"processingfee"=>0,"full"=>$fd,"isnew"=>$in,"status"=>$st,"resources"=>array(),"menus"=>array(),"personnel"=>array(),"promos"=>array(),"info"=>array(),"messages"=>array());
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($arr) > 0 && isset($arr['id']))
	{
		if ($approved > 1)
			$arr['imvenue'] = 1;
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT v.name, v.address, v.city, v.state, v.zip, v.country, v.currency, v.phone, v.website, v.timezone, v.shorturl, p.photo, vf.publicFileUploads, v.contract FROM venues AS v LEFT JOIN venues_functionality AS vf ON vf.venueid = v.venueid LEFT JOIN venues_photos AS p ON p.venueid = v.venueid WHERE v.venueid = ? AND (p.placement < 2 OR p.placement IS NULL) ORDER BY p.placement DESC LIMIT 0,1"))
		{
			$stmt->bind_param("i",$arr['venueid']);
			$stmt->execute();
			$stmt->bind_result($name, $address, $city, $state, $zip, $country, $cur, $phone, $website, $tz, $url, $photo, $userCanUpload, $con);
			while($stmt->fetch())
			{
				$arr['name'] = Sanitize($name);
				$arr['address'] = Sanitize($address.", ".$city.", ".$state." ".$zip);
				$arr['country'] = Sanitize($country);
				$arr['currency'] = $cur;
				$arr['phone'] = Sanitize($phone);
				$arr['website'] = $website;
				$arr['timezone'] = $tz;
				$arr['contract'] = $con;
				$arr['url'] = $url;
				$arr['logo'] = "/assets/content/".$photo;
				$arr['info'] = array();
				$arr['resources'] = array();
				$arr['addons'] = array();
				$arr['menus'] = array();
				$arr['personnel'] = array();
				$arr['questions'] = array();
				$arr['messages'] = array();
				$arr['files'] = array();
				$arr['userCanUpload'] = $userCanUpload;
				
				$arr['full_tax'] = 0;
				$arr['bookingfee'] = 0;
				$arr['bookingfee_tax'] = 0;
				$arr['processingfee'] = 0;
				$arr['cleanupfee'] = 0;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($arr['imvenue'])
			$arr['userCanUpload'] = 1;
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT name, description, comments, contact_name, contact_company, contact_title, contact_phone, contact_email, contact_website FROM booking_info WHERE bookingid = ?"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($name, $desc, $comments, $cn, $cc, $ct, $cp, $ce, $cw);
			while($stmt->fetch())
			{
				$arr['info']['name'] = Sanitize($name);
				$arr['info']['description'] = Sanitize($desc);
				$arr['info']['comments'] = Sanitize($comments);
				$arr['info']['contact_name'] = Sanitize($cn);
				$arr['info']['contact_company'] = Sanitize($cc);
				$arr['info']['contact_title'] = Sanitize($ct);
				$arr['info']['contact_phone'] = Sanitize($cp);
				$arr['info']['contact_email'] = Sanitize($ce);
				$arr['info']['contact_website'] = $cw;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT r.resourceid, r.name, starttime, stoptime, cost, b.cleanupcost, b.refund_policyid FROM booking_resources AS b LEFT JOIN resources AS r ON r.resourceid = b.resourceid WHERE bookingid = ? AND cost IS NOT NULL"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($id, $name, $start, $stop, $cost, $cc, $rp);
			while($stmt->fetch())
			{					
				$arr['resources'][] = array("id"=>$id,"name"=>Sanitize($name),"cost"=>$cost,"cleanupcost"=>$cc,"refund_policyid"=>$rp,"start"=>$start,"stop"=>$stop,"addons"=>array());
			}
			$GLOBALS['db']->CloseConn();
		}	
		
		foreach ($arr['resources'] as &$r)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT a.name, b.quantity, b.cost, b.deliverat, b.refund_policyid FROM booking_addons AS b LEFT JOIN addons AS a ON a.addonid = b.addonid WHERE b.bookingid = ? AND b.resourceid = ?"))
			{
				$stmt->bind_param("ii",$arr['id'],$r['id']);
				$stmt->execute();
				$stmt->bind_result($name, $q, $cost, $deliver, $rp);
				while($stmt->fetch())
				{
					$r['addons'][] = array("name"=>Sanitize($name),"price"=>($cost/$q),"cost"=>$cost,"quantity"=>$q,"refund_policyid"=>$rp,"deliverat"=>$deliver);
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT menuid, menuindex, deliverat, cost, refund_policyid FROM booking_menus WHERE bookingid = ? ORDER BY menuindex ASC"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($id, $mi, $deliver, $cost, $rp);
			while($stmt->fetch())
			{
				$arr['menus'][] = array("id"=>$id,"mi"=>$mi,"cost"=>$cost,"deliverat"=>$deliver,"refund_policyid"=>$rp,"items"=>array());
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr['menus'] as &$menu)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT i.name, b.quantity, b.cost FROM booking_menuitems AS b LEFT JOIN menus_items AS i ON i.itemid = b.itemid WHERE b.bookingid = ? AND b.menuindex = ?"))
			{
				$stmt->bind_param("ii",$arr['id'],$menu['mi']);
				$stmt->execute();
				$stmt->bind_result($name, $q, $cost);
				while($stmt->fetch())
				{
					$menu['items'][] = array("name"=>Sanitize($name),"price"=>($cost/$q),"cost"=>$cost,"quantity"=>$q);
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT pp.name, p.quantity, p.cost, p.refund_policyid FROM booking_personnel AS p LEFT JOIN personnel AS pp ON pp.personnelid = p.personnelid WHERE p.bookingid = ?"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($name, $q, $cost, $rp);
			while($stmt->fetch())
			{
				$arr['personnel'][] = array("name"=>Sanitize($name),"price"=>($cost/$q),"cost"=>$cost,"quantity"=>$q,"refund_policyid"=>$rp);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT b.questionid, b.answer, q.question, q.type FROM booking_questions AS b LEFT JOIN questions AS q ON q.questionid = b.questionid WHERE b.bookingid = ?"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($id, $a, $q, $t);
			while($stmt->fetch())
			{
				$arr['questions'][] = array("id"=>$id,"question"=>$q,"answer"=>$a,"type"=>$t);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		for ($i=0; $i<count($arr['questions']); $i++)
		{
			if ($arr['questions'][$i]['type'] == "radio" || $arr['questions'][$i]['type'] == "select")
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT choice FROM questions_choices WHERE questionid = ? AND choiceid = ?"))
				{
					$stmt->bind_param("ii",$arr['questions'][$i]['id'],$arr['questions'][$i]['answer']);
					$stmt->execute();
					$stmt->bind_result($c);
					while($stmt->fetch())
					{
						$arr['questions'][$i]['answer'] = $c;
					}
					$GLOBALS['db']->CloseConn();
				}
			}
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT IFNULL(SUM(cleanupcost),0) FROM booking_resources WHERE bookingid = ?"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($cc);
			if($stmt->fetch())
			{
				$arr['cleanupfee'] = $cc;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT name, IFNULL(SUM(fee),0) FROM booking_adjustments WHERE bookingid = ? GROUP BY name"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($name,$fee);
			while($stmt->fetch())
			{
				if ($name == "full_tax")
					$arr['full_tax'] = $fee;
				if ($name == "booking_fee")
					$arr['bookingfee'] = $fee;
				if ($name == "booking_fee_tax")
					$arr['bookingfee_tax'] = $fee;
				if ($name == "invoice_adjustment")
					$arr['adjustment'] = $fee;
				if ($name == "processing_fee")
					$arr['processingfee'] = $fee;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT p.name, p.description, p.expires, b.amount FROM booking_promos AS b JOIN promos AS p ON p.promoid = b.promoid WHERE b.bookingid = ?"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($name, $description, $exp, $amount);
			while($stmt->fetch())
			{
				$e = "";
				if ($exp > 0)
				{
					$d = new DateTime("@" . ($arr['timestamp'] + $exp*60));
					$d->setTimezone(new DateTimeZone($tz));
					$e = " | Expires if not paid in full by " . $d->format("g:i A T M j, Y");
				}
				else if ($exp < 0)
				{
					$d = new DateTime("@" . $arr['start'] + $exp*60);
					$d->setTimezone(new DateTimeZone($tz));
					$e = " | Expires at " . $d->format("g:i A T M j, Y");
				}
		
				$arr['promos'][] = array("name"=>Sanitize($name),"desc"=>Sanitize($description).$e,"discountAmount"=>$amount);
				$arr['promos_total'] += $amount;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT CONCAT(u.firstname,' ',u.lastname,' (',u.email,')'), v.name, m.isvenue, m.message, m.timestamp, m.viewed_by_user, m.viewed_by_venue FROM booking_messages AS m LEFT JOIN users AS u ON u.userid = m.userid LEFT JOIN booking AS b ON b.bookingid = m.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE m.bookingid = ? ORDER BY m.timestamp DESC"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($name,$v,$is,$m,$time,$v1,$v2);
			while($stmt->fetch())
			{
				$n = $name;
				switch ($is)
				{
					case 1:
						$n = $v;
						break;
					case 2:
						$n = "InviteBIG";
						break;
				}
				
				$isnew = false;
				if ($arr['imvenue'] && !$v2)
					$isnew = true;
				if (!$arr['imvenue'] && !$v1)
					$isnew = true;
					
				$arr['messages'][] = array("from"=>$n,"isvenue"=>$is,"time"=>$time,"isnew"=>$isnew,"message"=>Sanitize($m));
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT f.fileid, CONCAT(u.firstname,' ',u.lastname), f.userid, f.name, f.description, f.timestamp, f.private FROM booking_files AS f LEFT JOIN users AS u ON u.userid = f.userid WHERE f.bookingid = ? ORDER BY f.timestamp DESC"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$stmt->bind_result($fid,$name,$u,$f,$d,$t,$p);
			while($stmt->fetch())
			{
				if (!$arr['imvenue'] && $p)
					continue;
				
				$canDel = false;
				if ($u == $_SESSION['userid'])
					$canDel = true;
				if ($arr['imvenue'])
					$canDel = true;
					
				$arr['files'][] = array("fileid"=>$fid,"name"=>$f,"desc"=>$d,"date"=>$time,"private"=>$p,"user"=>$name, "canDel"=>$canDel);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if ($arr['imvenue'] && $arr['isnew'])
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE booking SET isnew = 0 WHERE bookingid = ?"))
			{
				$stmt->bind_param("i",$arr['id']);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		
		/*
		$q = "";
		if ($arr['imvenue'] )
		{
			$q = "viewed_by_venue = 1";
		}
		if ($arr['userid'] == $_SESSION['userid'])
		{
			if (strlen($q) > 0)
				$q .= ", ";
			$q .= "viewed_by_user = 1";
		}
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE booking_messages SET ".$q." WHERE bookingid = ?"))
		{
			$stmt->bind_param("i",$arr['id']);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}*/
		
		return $arr;
	}
	
	return "There was an error loading this booking";
}

function MarkBookingMessagesRead($bid)
{
	$arr = array();
	$marked = false;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT bookingid,venueid,eventid,b.userid,u.email,headcount,total_cost,total_deposit,full_due,status FROM booking AS b LEFT JOIN users AS u ON u.userid = b.userid WHERE bookingid = ?"))
	{	
		$stmt->bind_param("i",$bid);
		$stmt->execute();
		$stmt->bind_result($bid,$vid,$eid,$uid,$uemail,$hc,$tc,$td,$fd,$st);
		if ($stmt->fetch())
		{
			$arr = array("id"=>$bid,"venueid"=>$vid,"eventid"=>$eid,"userid"=>$uid,"useremail"=>$uemail,"headcount"=>$hc,"cost"=>$tc,"deposit"=>$td,"full"=>$fd,"status"=>$st,"resources"=>array(),"menus"=>array(),"personnel"=>array(),"info"=>array(),"messages"=>array());
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($arr) > 0 && isset($arr['id']))
	{
		$approved = CheckBookingAuth($arr['id'],2);
		
		if ($approved)
		{
			$q = "";
			
			if ($approved > 1)
				$q = "viewed_by_venue = 1";
			
			if ($arr['userid'] == $_SESSION['userid'])
			{
				if (strlen($q) > 0)
					$q .= ", ";
				$q .= "viewed_by_user = 1";
			}
			
			if (strlen($q) > 0)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("UPDATE booking_messages SET ".$q." WHERE bookingid = ?"))
				{
					$stmt->bind_param("i",$arr['id']);
					$stmt->execute();
					if ($stmt->affected_rows)
						$marked = true;
					$GLOBALS['db']->CloseConn();
				}
			}
		}
	}
	
	return $marked;
}

function LoadPaymentInfo($id)
{
	CheckPromoExpiration($id);
	
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid,b.venueid,b.eventid,b.userid,u.email,b.total_cost,b.total_deposit,b.full_due,b.timestamp,b.status,v.bookingfee,v.processingfee,v.currency,v.phone,v.timezone FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE bookingid = ?"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($bid,$vid,$eid,$uid,$uemail,$tc,$td,$fd,$time,$st,$fee,$pfee,$cur,$phone,$tz);
		if ($stmt->fetch())
		{			
			$arr = array("id"=>$bid,"venueid"=>$vid,"eventid"=>$eid,"userid"=>$uid,"useremail"=>$uemail,"phone"=>$phone,"cost"=>$tc,"deposit"=>$td,"full"=>$fd,"bookingfee"=>$fee,"processingfee"=>$pfee,"currency"=>$cur,"status"=>$st,"payments"=>array(),"paid"=>0,"showDeposit"=>1,"imvenue"=>0,"timezone"=>$tz);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($arr) > 0 && isset($arr['id']))
	{
		$approved = CheckBookingAuth($arr['id'],2);
		
		if ($approved)
		{
			if ($approved > 1)
				$arr['imvenue'] = 1;
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT p.timestamp, p.name, p.last4, p.payment_method, p.amount, p.status FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.bookingid = ? ORDER BY p.timestamp ASC"))
			{
				$stmt->bind_param("i",$arr['id']);
				$stmt->execute();
				$stmt->bind_result($time,$name,$last4,$method,$amount,$status);
				while($stmt->fetch())
				{
					$arr['payments'][] = array("time"=>$time,"name"=>$name,"last4"=>$last4,"method"=>$method,"amount"=>$amount,"status"=>$status);
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT IFNULL(SUM(fee),0) FROM booking_adjustments WHERE bookingid = ? AND name = 'booking_fee'"))
			{
				$stmt->bind_param("i",$arr['id']);
				$stmt->execute();
				$stmt->bind_result($f);
				if($stmt->fetch())
				{
					$arr['hasbookingfee'] = $f;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			foreach ($arr['payments'] as $p)
			{
				if ($p['status'] == "holding" || $p['status'] == "processed" || $p['status'] == "refunded")
					$arr['paid'] += $p['amount'];
			}
				
			if ($arr['deposit'] == 0)
				$arr['showDeposit'] = 0;
		}
	}
	
	return $arr;
}

function SubmitPayment($payment)
{	
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid,b.venueid,b.userid,v.currency,v.processingfee FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE b.bookingid = ? AND (b.status = 'Pending Deposit' OR b.status = 'Pending Payment' OR b.status = 'Pending Approval')"))
	{	
		$stmt->bind_param("i",$payment['bookingid']);
		$stmt->execute();
		$stmt->bind_result($bid,$vid,$uid,$cur,$pfee);
		if ($stmt->fetch())
		{
			$arr = array("id"=>$bid,"venueid"=>$vid,"userid"=>$uid,"currency"=>$cur,"pfee"=>$pfee);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (count($arr) > 0 && isset($arr['id']))
	{
		$approved = CheckBookingAuth($payment['bookingid'],2);
		
		if ($approved)
		{
			$payment['name'] = null;
			$payment['paymentid'] = null;
			$payment['last4'] = null;
			$payment['chargeid'] = null;
			$payment['currency'] = $arr['currency'];
			$charge = null;
			$payment['fee'] = ceil($arr['pfee'] * $payment['amount'] * 100) / 100;
			$payment['amount'] = $payment['amount'] + $payment['fee'];
			
			try {
				require_once('php/stripe-php-2.3.0/init.php');
				\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
			
				$charge = \Stripe\Charge::create(array(
					'card' => $payment['token'],
					'amount' => $payment['amount'] * 100,
					'currency' => $payment['currency'],
					'description' => "Payment to InviteBIG",
					'capture' => false
				));
				
				$payment['name'] = $charge->source->name;
				if (strlen($payment['name']) < 1)
					$payment['name'] = "";
				$payment['last4'] = $charge->source->last4;
				$payment['chargeid'] = $charge->id;
				$payment['paymentid'] = null;
				
			} catch (Exception $e) {
				$errmsg = "There was an error processing this payment, please contact customer support";
				
				if ($e instanceof \Stripe\Error\Card) 
				{
					$body = $e->getJsonBody();
					$err  = $body['error']['message'];
					error_log($err);
					return $errmsg;
				}
				else if ($e instanceof \Stripe\Error\InvalidRequest)
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
			
			if ($payment['chargeid'])
			{
				$uid = null;
				if (!isset($_SESSION['userid']) || !$_SESSION['userid'])
				{
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("SELECT userid FROM booking WHERE bookingid = ?"))
					{
						$stmt->bind_param("i",$payment['bookingid']);
						$stmt->execute();
						$stmt->bind_result($u);
						if($stmt->fetch())
						{
							$uid = $u;
						}
						$GLOBALS['db']->CloseConn();
					}
				}
				else $uid = $_SESSION['userid'];
			
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO payments (payment_method,userid,chargeid,timestamp,name,last4,amount,currency,status) VALUES ('stripe',?,?,UNIX_TIMESTAMP(),?,?,?,?,'holding')"))
				{	
					$stmt->bind_param("isssds",$uid,$payment['chargeid'],$payment['name'],$payment['last4'],$payment['amount'],$payment['currency']);
					$stmt->execute();
					if ($stmt->affected_rows)
						$payment['paymentid'] = $stmt->insert_id;
					$GLOBALS['db']->CloseConn();
				}
				
				if ($payment['paymentid'])
				{					
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("INSERT INTO booking_payments (bookingid,paymentid) VALUES (?,?)"))
					{	
						$stmt->bind_param("ii",$payment['bookingid'],$payment['paymentid']);
						$stmt->execute();
						$GLOBALS['db']->CloseConn();
					}
					
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("INSERT INTO payments_status (paymentid,userid,timestamp,status) VALUES (?,?,UNIX_TIMESTAMP(),'holding')"))
					{	
						$stmt->bind_param("ii",$payment['paymentid'],$uid);
						$stmt->execute();
						$GLOBALS['db']->CloseConn();
					}
					
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("INSERT INTO booking_messages (bookingid,userid,timestamp,message,isvenue,viewed_by_user,viewed_by_venue) VALUES (?,?,UNIX_TIMESTAMP(),?,2,1,1)"))
					{	
						$msg = "Payment of ".FormatCurrency($payment['amount'],$payment['currency'])." received";
						$stmt->bind_param("iis",$arr['id'],$uid,$msg);
						$stmt->execute();
						$GLOBALS['db']->CloseConn();
					}
					
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("INSERT INTO booking_adjustments (bookingid,name,fee) VALUES (?,'processing_fee',?)"))
					{	
						$stmt->bind_param("id",$arr['id'],$payment['fee']);
						$stmt->execute();
						$GLOBALS['db']->CloseConn();
					}
					
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("UPDATE booking SET total_cost = total_cost + ?, total_deposit = total_deposit + ? WHERE bookingid = ?"))
					{	
						$stmt->bind_param("ddi",$payment['fee'],$payment['fee'],$arr['id']);
						$stmt->execute();
						$GLOBALS['db']->CloseConn();
					}
					
					BookingStateChanged($payment['bookingid']);
					return "success";
				}
			}
		}
	}
	
	return "No payments can be made for this booking at this time";
}

function CapturePayment($id)
{
	require_once('php/email.php');
	
	$chargeid = null;
	$bookingid = null;
	$amount = 0;
	$last4 = "";
	$error = "";
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid,p.chargeid,p.amount,p.last4 FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.paymentid = ? AND p.payment_method = 'stripe'"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($bid,$cid,$amt,$l4);
		if ($stmt->fetch())
		{
			$chargeid = $cid;
			$bookingid = $bid;
			$amount = $amt;
			$last4 = $l4;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($chargeid && $bookingid && $amount)
	{
		try {
			require_once('php/stripe-php-2.3.0/init.php');
			\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
		
			$charge = \Stripe\Charge::retrieve($chargeid);
			$charge->capture(array("amount"=>($amount * 100)));
			$currency = $charge->currency;
			
			if ($charge->captured)
			{
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("UPDATE payments SET status = 'processed' WHERE paymentid = ?"))
				{	
					$stmt->bind_param("i",$id);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("INSERT INTO payments_status (paymentid,timestamp,status) VALUES (?,UNIX_TIMESTAMP(),'processed')"))
				{	
					$stmt->bind_param("i",$id);
					$stmt->execute();
					$GLOBALS['db']->CloseConn();
				}
			}			
		} catch (Exception $e) {
			$errmsg = "There was an error processing this payment, please contact customer support";
			
			if ($e instanceof \Stripe\Error\Card) 
			{
				$body = $e->getJsonBody();
				$err  = $body['error']['message'];
				error_log($err);
				return $errmsg;
			}
			else if ($e instanceof \Stripe\Error\InvalidRequest)
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
		
		if (strlen($error) > 0)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE payments SET status = 'failed' WHERE paymentid = ?"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO payments_status (paymentid,timestamp,status) VALUES (?,UNIX_TIMESTAMP(),'failed')"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_messages (bookingid,userid,timestamp,message,isvenue,viewed_by_user,viewed_by_venue) VALUES (?,0,UNIX_TIMESTAMP(),?,2,0,1)"))
			{	
				$msg = "Payment of ".FormatCurrency($amount,$currency)." from card ending in " . $last4 ." was declined.  Reason: " . $error;
				$stmt->bind_param("is",$bookingid,$msg);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		else SendPaymentReceivedMessage($bookingid,$amount);
	}
}

function RefundPayment($id, $amount = 0)
{
	$chargeid = null;
	$bookingid = null;
	$last4 = "";
	$error = "";
	$status = "";
	$pamount = 0;
	$method = "";
	$venueid = null;
	$name = null;
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.bookingid,b2.venueid,p.chargeid,p.amount,p.currency,p.last4,p.status,p.payment_method,p.name FROM booking_payments AS b LEFT JOIN booking AS b2 ON b2.bookingid = b.bookingid LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.paymentid = ?"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($bid,$v,$cid,$amt,$cur,$l4,$st,$m,$n);
		if ($stmt->fetch())
		{
			$chargeid = $cid;
			$bookingid = $bid;
			if ($amount == 0)
				$amount = $amt;
			$pamount = $amt;
			$last4 = $l4;
			$status = $st;
			$method = $m;
			$venueid = $v;
			$name = $n;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($method != "stripe")
	{		
		$pid = null;
		$amt = -$amount;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO payments (payment_method,userid,name,timestamp,last4,amount,currency,status) VALUES (?,?,?,UNIX_TIMESTAMP(),?,?,?,'pending_refund')"))
		{	
			$stmt->bind_param("sissds",$method,$_SESSION['userid'],$name,$last4,$amt,$currency);
			$stmt->execute();
			if ($stmt->affected_rows)
				$pid = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
		
		if ($pid)
		{			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_payments (bookingid,paymentid) VALUES (?,?)"))
			{	
				$stmt->bind_param("ii",$bookingid,$pid);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO payments_status (paymentid,userid,timestamp,status) VALUES (?,UNIX_TIMESTAMP(),'pending_refund')"))
			{	
				$stmt->bind_param("i",$pid);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_messages (bookingid,timestamp,message,isvenue,viewed_by_user,viewed_by_venue) VALUES (?,UNIX_TIMESTAMP(),?,2,0,0)"))
			{	
				$msg = $amount." must be refunded by the venue because online payment processing was not used.";
				$stmt->bind_param("is",$bookingid,$msg);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
	}
	
	if ($status == "holding")
	{
		if ($amount >= $pamount)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE payments SET status = 'cancelled' WHERE paymentid = ?"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO payments_status (paymentid,timestamp,status) VALUES (?,UNIX_TIMESTAMP(),'cancelled')"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO booking_messages (bookingid,userid,timestamp,message,isvenue,viewed_by_user,viewed_by_venue) VALUES (?,0,UNIX_TIMESTAMP(),?,2,0,1)"))
			{	
				$msg = "The payment of ".FormatCurrency($pamount,$currency)." from card ending in " . $last4 ." was cancelled";
				$stmt->bind_param("is",$bookingid,$msg);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			if ($method == "stripe")
			{
				try {
					require_once('php/stripe-php-2.3.0/init.php');
					\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
				
					$charge = \Stripe\Charge::retrieve($chargeid);
					$charge->refund();
					
				} catch (Exception $e) {
					$errmsg = "There was an error processing this payment, please contact customer support";
					
					if ($e instanceof \Stripe\Error\Card) 
					{
						$body = $e->getJsonBody();
						$err  = $body['error']['message'];
						error_log($err);
						return $errmsg;
					}
					else if ($e instanceof \Stripe\Error\InvalidRequest)
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
		}
		else
		{
			if ($method == "stripe")
			{
				try {
					require_once('php/stripe-php-2.3.0/init.php');
					\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
				
					$charge = \Stripe\Charge::retrieve($chargeid);
					$origamount = $charge->amount;
					$newamount = $origamount - ($amount * 100);
					
					$charge->capture(array("amount"=>$newamount));
					
					if ($charge->captured)
					{
						$stmt = $GLOBALS['db']->stmt_init();
						if ($stmt->prepare("UPDATE payments SET amount = ?, status = 'processed' WHERE paymentid = ?"))
						{	
							$n = $newamount/100;
							$stmt->bind_param("di",$n,$id);
							$stmt->execute();
							$GLOBALS['db']->CloseConn();
						}
						$stmt = $GLOBALS['db']->stmt_init();
						if ($stmt->prepare("INSERT INTO payments_status (paymentid,timestamp,status) VALUES (?,UNIX_TIMESTAMP(),?)"))
						{	
							$s = "adjusted -" . $amount;
							$stmt->bind_param("is",$id,$s);
							$stmt->execute();
							$GLOBALS['db']->CloseConn();
						}
						
						$stmt = $GLOBALS['db']->stmt_init();
						if ($stmt->prepare("INSERT INTO booking_messages (bookingid,userid,timestamp,message,viewed_by_user,viewed_by_venue) VALUES (?,0,UNIX_TIMESTAMP(),?,0,1)"))
						{	
							$msg = $amount." was refunded to card ending in " . $last4;
							$stmt->bind_param("is",$bookingid,$msg);
							$stmt->execute();
							$GLOBALS['db']->CloseConn();
						}
					}
					
				} catch (Exception $e) {
					$errmsg = "There was an error processing this payment, please contact customer support";
					
					if ($e instanceof \Stripe\Error\Card) 
					{
						$body = $e->getJsonBody();
						$err  = $body['error']['message'];
						error_log($err);
						return $errmsg;
					}
					else if ($e instanceof \Stripe\Error\InvalidRequest)
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
		}
	}
	else if ($status == "processed" && $chargeid && $bookingid && $amount)
	{
		if ($method == "stripe")
		{
			try {
				require_once('php/stripe-php-2.3.0/init.php');
				\Stripe\Stripe::setApiKey("sk_live_dA4CHpr3GoKDKKpPH3g9pzAL");
			
				$charge = \Stripe\Charge::retrieve($chargeid);
				$count = $charge->refunds->total_count;
				$charge->refund(array("amount"=>($amount*100)));
				
				if ($charge->refunded || $charge->refunds->total_count > $count)
				{
					$nid = null;
					$namount = -1 * $amount;
					
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("INSERT INTO payments (userid,payment_method,chargeid,timestamp,name,last4,amount,currency,status) SELECT userid,payment_method,chargeid,UNIX_TIMESTAMP(),name,last4,?,currency,'refunded' FROM payments WHERE paymentid=?"))
					{	
						$stmt->bind_param("di",$namount,$id);
						$stmt->execute();
						$nid = $stmt->insert_id;
						$GLOBALS['db']->CloseConn();
					}
					if ($nid)
					{
						$stmt = $GLOBALS['db']->stmt_init();
						if ($stmt->prepare("INSERT INTO booking_payments (bookingid,paymentid) VALUES (?,?)"))
						{
							$stmt->bind_param("ii",$bookingid,$nid);
							$stmt->execute();
							$GLOBALS['db']->CloseConn();
						}
						$stmt = $GLOBALS['db']->stmt_init();
						if ($stmt->prepare("INSERT INTO payments_status (paymentid,timestamp,status) VALUES (?,UNIX_TIMESTAMP(),?)"))
						{	
							$s = "refunded " . FormatCurrency(-1*$amount,$charge->currency);
							$stmt->bind_param("is",$nid,$s);
							$stmt->execute();
							$GLOBALS['db']->CloseConn();
						}
					}
					
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("INSERT INTO booking_messages (bookingid,userid,timestamp,message,viewed_by_user,viewed_by_venue) VALUES (?,0,UNIX_TIMESTAMP(),?,0,1)"))
					{	
						if ($charge->refunded)
							$msg = "Payment of ".FormatCurrency($amount,$charge->currency)." from card ending in " . $last4 ." was refunded";
						else $msg = "The charge of ".FormatCurrency(($charge->amount/100),$charge->currency)." from card ending in " . $last4 ." was refunded ".$amount;
						$stmt->bind_param("is",$bookingid,$msg);
						$stmt->execute();
						$GLOBALS['db']->CloseConn();
					}
				}
			} catch (Exception $e) {
				$errmsg = "There was an error processing this payment, please contact customer support";
				
				if ($e instanceof \Stripe\Error\Card) 
				{
					$body = $e->getJsonBody();
					$err  = $body['error']['message'];
					error_log($err);
					return $errmsg;
				}
				else if ($e instanceof \Stripe\Error\InvalidRequest)
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
	}
	
	if (strlen($error) > 0)
	{
		/*$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE payments SET status = 'failed' WHERE paymentid = ?"))
		{	
			$stmt->bind_param("i",$id);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}*/
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO payments_status (paymentid,timestamp,status) VALUES (?,UNIX_TIMESTAMP(),?)"))
		{	
			$s = "failed adjust -".$amount;
			$stmt->bind_param("is",$id,$s);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_messages (bookingid,userid,timestamp,message,viewed_by_user,viewed_by_venue) VALUES (?,0,UNIX_TIMESTAMP(),?,0,1)"))
		{	
			$msg = "Refund of ".$amount." to card ending in " . $last4 ." failed.  Reason: " . $error;
			$stmt->bind_param("is",$bookingid,$msg);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		return false;
	}
	
	return true;
}

function BookingStateChanged($id)
{	
	/*
		Pending Deposit
		Pending Approval
		Pending Payment
		Paid
		Denied
		Cancelled by Venue
		Cancelled by User
		Past Due
		Approval Expired
	*/
	
	require_once('php/email.php');
	$arr = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT venueid, userid, total_cost, total_deposit, (full_due - UNIX_TIMESTAMP()), status FROM booking WHERE bookingid = ?"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($vid,$uid,$tc,$td,$f,$s);
		if ($stmt->fetch())
		{
			$arr = array("id"=>$id,"venueid"=>$vid,"userid"=>$uid,"cost"=>$tc,"deposit"=>$td,"status"=>$s,"paid"=>0,"full"=>$f);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($arr)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.bookingid = ? AND (p.status = 'holding' OR p.status = 'processed' OR p.status = 'refunded')"))
		{	
			$stmt->bind_param("i",$id);
			$stmt->execute();
			$stmt->bind_result($paid);
			if ($stmt->fetch())
			{
				$arr['paid'] = $paid;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		switch ($arr['status'])
		{
			case "Pending Deposit":
				if ($arr['deposit'] == 0)
				{
					if ($arr['full'] <= 0 && $arr['paid'] < $arr['cost'])
					{
						// if full payment is due immediately then do not move forward
						break;
					}
					else
					{
						$msg = "No deposit is required for this booking. Awaiting venue's approval";
						UpdateBookingStatus($id,$arr['venueid'],"Deposit Paid",$msg,2);
					}
				}
				else if ($arr['paid'] >= $arr['deposit'])
				{
					$msg = "The deposit for this booking has been paid. Awaiting venue's approval";						
					UpdateBookingStatus($id,$arr['venueid'],"Deposit Paid",$msg,2);
				}
				else SendNewBookingDepositRequiredMessage($id);
				break;
				
			case "Pending Approval":
				$auto = false;
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT COUNT(b.resourceid) FROM booking_resources AS b LEFT JOIN resources AS r ON r.resourceid = b.resourceid WHERE b.bookingid = ? AND b.cost IS NOT NULL AND r.autoapprove != 1"))
				{	
					$stmt->bind_param("i",$id);
					$stmt->execute();
					$stmt->bind_result($c);
					if ($stmt->fetch())
					{
						if ($c == 0)
							$auto = true;
					}
					$GLOBALS['db']->CloseConn();
				}
				
				$locked = false;
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT status FROM venues_subscriptions WHERE venueid = ?"))
				{	
					$stmt->bind_param("i",$arr['venueid']);
					$stmt->execute();
					$stmt->bind_result($s);
					if ($stmt->fetch())
					{
						if ($s != "active" && $s != "trialing")
							$locked = true;
					}
					$GLOBALS['db']->CloseConn();
				}
				
				if ($auto)
				{
					if (!IsNoLongerAvailable(null,$id) && !$locked)
						UpdateBookingStatus($id,$arr['venueid'],"approve",null,2);
					else UpdateBookingStatus($id,$arr['venueid'],"cancel","Some of the resources requested in this booking are no longer available during the specified time, please choose different times or resources.",2);
				}
				break;
				
			case "Pending Payment":
				$payments = array();
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT b.paymentid FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.bookingid = ? AND p.status = 'holding' AND p.payment_method = 'stripe'"))
				{	
					$stmt->bind_param("i",$id);
					$stmt->execute();
					$stmt->bind_result($pid);
					while ($stmt->fetch())
					{
						$payments[] = $pid;
					}
					$GLOBALS['db']->CloseConn();
				}
				
				foreach ($payments AS $p)
					CapturePayment($p);
					
				if ($arr['paid'] >= $arr['cost'])
				{					
					$paid = 0;
					$stmt = $GLOBALS['db']->stmt_init();
					if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.bookingid = ? AND p.status = 'processed' OR p.status = 'refunded'"))
					{	
						$stmt->bind_param("i",$id);
						$stmt->execute();
						$stmt->bind_result($p);
						if ($stmt->fetch())
						{
							$paid = $p;
						}
						$GLOBALS['db']->CloseConn();
					}
					
					if ($paid >= $arr['cost'])
						UpdateBookingStatus($id,$arr['venueid'],"Paid",null,2);
				}
				break;
			
			case "Denied":
				$payments = array();
			
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT b.paymentid FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.bookingid = ? AND (p.status = 'holding' OR p.status = 'processed' OR p.status = 'refunded')"))
				{	
					$stmt->bind_param("i",$id);
					$stmt->execute();
					$stmt->bind_result($pid);
					while ($stmt->fetch())
					{
						$payments[] = $pid;
					}
					$GLOBALS['db']->CloseConn();
				}
				
				foreach ($payments AS $p)
					RefundPayment($p);
					
				//UpdateBookingStatus($id,$arr['venueid'],"closed",null,2);
				break;
			
			// "Deposit Time Expired" no longer needed as of 9.29.2015
			case "Deposit Time Expired":
			case "Cancelled by Venue":
				$payments = array();
			
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT b.paymentid FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.bookingid = ? AND (p.status = 'holding' OR p.status = 'processed')"))
				{	
					$stmt->bind_param("i",$id);
					$stmt->execute();
					$stmt->bind_result($pid);
					while ($stmt->fetch())
					{
						$payments[] = $pid;
					}
					$GLOBALS['db']->CloseConn();
				}
				
				foreach ($payments AS $p)
					RefundPayment($p);
					
				//UpdateBookingStatus($id,$arr['venueid'],"closed",null,2);
				break;
				
			case "Cancelled by User":
				$noforce = true;
			case "Cancelled by Venue (past due)":
				// pass 'false' for the 'force' parameter if user is cancelling, or 'true' if venue is cancelling
				$refund = CalculateRefund($id,false,(isset($noforce)?false:true));
				
				$payments = array();
				$epay = 0;
				$bf = 0;
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT IFNULL(SUM(fee),0) FROM booking_adjustments WHERE bookingid = ? AND (name = 'booking_fee' OR name = 'booking_fee_tax' OR name = 'processing_fee')"))
				{	
					$stmt->bind_param("i",$id);
					$stmt->execute();
					$stmt->bind_result($amt);
					while ($stmt->fetch())
					{
						$bf = $amt;
					}
					$GLOBALS['db']->CloseConn();
				}
				
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT b.paymentid,p.amount,p.payment_method FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.bookingid = ? AND (p.status = 'holding' OR p.status = 'processed' OR p.status = 'refunded') ORDER BY status ASC,payment_method DESC,amount DESC"))
				{	
					$stmt->bind_param("i",$id);
					$stmt->execute();
					$stmt->bind_result($pid,$amt,$method);
					while ($stmt->fetch())
					{
						$payments[] = array("id"=>$pid,"amount"=>$amt,"method"=>$method);
						if ($method == "stripe")
							$epay += $amt;
					}
					$GLOBALS['db']->CloseConn();
				}
				
				if ($bf > $epay)
					$bf = $epay;
				
				while ($refund > 0 && count($payments) > 0)
				{
					// first make sure we keep enough stripe payment to cover our fees
					if ($bf > 0 && $payments[0]['method'] == "stripe")
					{
						if ($payments[0]['amount'] <= $bf)
						{
							$bf -= $payments[0]['amount'];
							unset($payments[0]);
							$payments = array_values($payments);
							continue;
						}
						else
						{
							$amt = $payments[0]['amount'] - $bf;
							if ($amt > $refund)
								$amt = $refund;

							if (RefundPayment($payments[0]['id'], $amt))
							{
								$bf = 0;
								$refund -= $amt;
								unset($payments[0]);
								$payments = array_values($payments);
								continue;
							}
						}
					}
					// now keep the venue's share but refund from 
					if ($payments[0]['amount'] <= $refund)
					{
						if (RefundPayment($payments[0]['id']))
							$refund -= $payments[0]['amount'];						
					}
					else
					{
						if (RefundPayment($payments[0]['id'], $refund))
							$refund -= $payments[0]['amount'];
					}
					
					unset($payments[0]);
					$payments = array_values($payments);
				}
				
				/*if ($refund <= 0)
					//UpdateBookingStatus($id,$arr['venueid'],"closed",null,2);
				else UpdateBookingStatus($id,$arr['venueid'],"message","Further payment is due before this booking can be closed",2);*/
				
				break;
							
			case "Approval Expired":
				$payments = array();
			
				$stmt = $GLOBALS['db']->stmt_init();
				if ($stmt->prepare("SELECT b.paymentid FROM booking_payments AS b LEFT JOIN payments AS p ON p.paymentid = b.paymentid WHERE b.bookingid = ? AND (p.status = 'holding' OR p.status = 'processed' OR p.status = 'refunded')"))
				{	
					$stmt->bind_param("i",$id);
					$stmt->execute();
					$stmt->bind_result($pid);
					while ($stmt->fetch())
					{
						$payments[] = $pid;
					}
					$GLOBALS['db']->CloseConn();
				}
				
				foreach ($payments AS $p)
					RefundPayment($p);
					
				//UpdateBookingStatus($id,$arr['venueid'],"closed",null,2);
				break;
		}
	}
}

function CalculateRefund($id, $return_array = false, $force = false)
{	
	$arr = array();
	$refund = 0;
	$due = 0;
	$daysleft = 0;
	$paid = 0;
	$full_tax = 0;
	$bookingfee_tax = 0;
	$bookingfee = 0;
	$processingfee = 0;
	$adj = 0;
	$promos = 0;
	
	$stmt = $GLOBALS['db']->stmt_init();
	//if ($stmt->prepare("SELECT b.bookingid,b.venueid,b.total_cost,b.total_deposit,b.start,b.timestamp,v.salestax,b.status FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE bookingid = ? AND b.start > UNIX_TIMESTAMP() AND (b.status = 'Cancelled by User' OR b.status = 'Paid' OR b.status LIKE 'Pending %')"))
	if ($stmt->prepare("SELECT b.bookingid,b.venueid,b.userid,b.total_cost,b.total_deposit,v.currency,b.start,b.timestamp,v.salestax,b.status FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE bookingid = ? AND (b.status = 'Cancelled by User' OR b.status = 'Cancelled by Venue (past due)' OR b.status = 'Past Due' OR b.status = 'Paid' OR b.status = 'Past Due' OR b.status LIKE 'Pending %')"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($bid,$vid,$uid,$tc,$td,$cur,$start,$time,$salestax,$st);
		if ($stmt->fetch())
		{			
			$d2 = new DateTime();
			$daysleft = ($start - $d2->getTimestamp()) / 86400;
			
			$approved = false;
			if ($st == "Paid" || $st == "Pending Payment")
				$approved = true;
			
			$arr = array("id"=>$bid,"userid"=>$uid,"venueid"=>$vid,"currency"=>$cur,"salestax"=>$salestax,"cost"=>$tc,"deposit"=>$td,"paid"=>0,"due"=>0,"bookingfee"=>0,"taxes"=>0,"start"=>$start,"status"=>$st,"approved"=>$approved,"already_started"=>($daysleft<=0?true:false),"resources"=>array(),"addons"=>array(),"menus"=>array(),"personnel"=>array());
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (isset($arr['id']))
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(*) FROM booking_status WHERE bookingid = ? AND status = 'Pending Payment'"))
		{	
			$stmt->bind_param("i",$id);
			$stmt->execute();
			$stmt->bind_result($c);
			if ($stmt->fetch())
			{
				if ($c > 0)
					$arr['approved'] = true;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if (!$arr['approved'])
		{
			$refund = $arr['cost'];
		}
		else 
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT b.resourceid, b.cost, b.cleanupcost, b.refund_policyid, b.refund_policy FROM booking_resources AS b WHERE b.bookingid = ? AND b.cost > 0"))
			{
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$stmt->bind_result($rid,$cost,$cc,$rp,$r);
				while ($stmt->fetch())
				{
					$fee = 0;
					$policy = json_decode($r,true);
					if ($cost > 0 && $policy)
					{
						foreach ($policy as $p)
						{
							if ($daysleft <= (int)$p['days'] && (int)$p['fee'] > $fee)
								$fee = (int)$p['fee'];
						}
						
						$d = ($fee / 100) * ($cost + $cc);
						$d = floor($d * 100) / 100;
						$arr['resources'][] = array("id"=>$rid,"refund_policyid"=>$rp,"fee"=>$d);
						$due += $d;
					}
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT a.addonid, a.cost, a.refund_policyid, a.refund_policy FROM booking_addons AS a WHERE bookingid = ?"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$stmt->bind_result($aid,$cost,$rp,$r);
				while ($stmt->fetch())
				{
					$fee = 0;
					$policy = json_decode($r,true);
					if ($cost > 0 && $policy)
					{
						foreach ($policy as $p)
						{
							if ($daysleft <= (int)$p['days'] && (int)$p['fee'] > $fee)
								$fee = (int)$p['fee'];
						}
						
						$d = ($fee / 100) * ($cost);
						$d = floor($d * 100) / 100;
						$arr['addons'][] = array("id"=>$aid,"refund_policyid"=>$rp,"fee"=>$d);
						$due += $d;
					}
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT m.menuid, m.cost, m.refund_policyid, m.refund_policy FROM booking_menus AS m WHERE bookingid = ?"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$stmt->bind_result($mid,$cost,$rp,$r);
				while ($stmt->fetch())
				{
					$fee = 0;
					$policy = json_decode($r,true);
					if ($cost > 0 && $policy)
					{
						foreach ($policy as $p)
						{
							if ($daysleft <= (int)$p['days'] && (int)$p['fee'] > $fee)
								$fee = (int)$p['fee'];
						}
						
						$d = ($fee / 100) * ($cost);
						$d = floor($d * 100) / 100;
						$arr['menus'][] = array("id"=>$mid,"refund_policyid"=>$rp,"fee"=>$d);
						$due += $d;
					}
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT p.personnelid, p.cost, p.refund_policyid, p.refund_policy FROM booking_personnel AS p WHERE bookingid = ?"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$stmt->bind_result($pid,$cost,$rp,$r);
				while ($stmt->fetch())
				{
					$fee = 0;
					$policy = json_decode($r,true);
					if ($cost > 0 && $policy)
					{
						foreach ($policy as $p)
						{
							if ($daysleft <= (int)$p['days'] && (int)$p['fee'] > $fee)
								$fee = (int)$p['fee'];
						}
						
						$d = ($fee / 100) * ($cost);
						$d = floor($d * 100) / 100;
						$arr['personnel'][] = array("id"=>$pid,"refund_policyid"=>$rp,"fee"=>$d);
						$due += $d;
					}
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT name,IFNULL(SUM(fee),0) FROM booking_adjustments WHERE bookingid = ? GROUP BY name"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$stmt->bind_result($name,$fee);
				while ($stmt->fetch())
				{
					switch ($name)
					{
						case "full_tax":
							$full_tax = $fee;
							break;
						case "booking_fee_tax":
							$bookingfee_tax = $fee;
							break;
						case "booking_fee":
							$bookingfee = $fee;
							break;
						case "processing_fee":
							$processingfee = $fee;
							break;
						case "invoice_adjustment":
							$adjustment = $fee;
							break;
					}
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT amount FROM booking_promos WHERE bookingid = ?"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$stmt->bind_result($p);
				while ($stmt->fetch())
				{
					$promos += $p;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT IFNULL(SUM(amount),0) FROM payments AS p LEFT JOIN booking_payments AS b ON b.paymentid = p.paymentid WHERE b.bookingid = ? AND (p.status = 'processed' OR p.status = 'holding' OR p.status = 'refunded')"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$stmt->bind_result($p);
				if ($stmt->fetch())
				{
					$arr['paid'] = $p;
				}
				$GLOBALS['db']->CloseConn();
			}
			//echo "due: ".$due."<br>paid: ".$arr['paid']."<br>deposit: ".$arr['deposit']."<br>cost: ".$arr['cost']."<br>";
			
			$newtax = ceil($due * $arr['salestax'] * 100) / 100;
			$due += $bookingfee + $bookingfee_tax + $newtax + $processingfee;
			
			if ($due < $arr['deposit'])
				$due = $arr['deposit'];
						
			$refund = $arr['paid'] - $due;
			if ($refund <= 0)
				$refund = 0;			
			
			$refund = floor($refund * 100) / 100;
			
			// $promos and $adj
			
			$arr['due'] = $due;
			$arr['bookingfee'] = $bookingfee;
			$arr['taxes'] = $newtax + $bookingfee_tax;
		}
	
		$arr['refund_amount'] = $refund;
	}
		
	if ($return_array)
		return $arr;
	
	if ($arr['already_started'] == true && !$force)
		return 0;
		
	return $refund;
}

function UploadBookingFile($bid, $file, $desc, $private)
{
	$desc = Sanitize($desc);
	
	$result = "There was an error processing your request";
	
	$auth = 0;
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT vf.publicFileUploads, b.venueid FROM booking AS b LEFT JOIN venues_functionality AS vf ON vf.venueid = b.venueid WHERE b.bookingid = ?"))
	{	
		$stmt->bind_param("i",$bid);
		$stmt->execute();
		$stmt->bind_result($d,$vid);
		if ($stmt->fetch())
		{			
			if ($d > 0)
				$auth = 1;
			
			foreach ($_SESSION['venueRights'] as $v)
				if ($v['venueid'] == $vid) $auth = $v['role'];
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($auth)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_files (bookingid,userid,name,description,private,timestamp) VALUES (?,?,?,?,?,UNIX_TIMESTAMP())"))
		{	
			$stmt->bind_param("iissi",$bid,$_SESSION['userid'],$file,$desc,$private);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
			$result = "success";
		}
	}
	else $result = "You are not authorized to make this request";
	
	return $result;
}

function DeleteBookingFile($fileid)
{
	$auth = false;
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.venueid, f.userid FROM booking_files AS f LEFT JOIN booking AS b ON b.bookingid = f.bookingid WHERE f.fileid = ?"))
	{	
		$stmt->bind_param("i",$fileid);
		$stmt->execute();
		$stmt->bind_result($v, $u);
		while ($stmt->fetch())
		{			
			if ($u == $_SESSION['userid'])
				$auth = true;
			
			foreach ($_SESSION['venueRights'] as $venue)
				if ($venue['venueid'] == $v && $venue['role'] > 0)
					$auth = true;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($auth)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("DELETE FROM booking_files WHERE fileid = ?"))
		{	
			$stmt->bind_param("i",$fileid);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		return "success";
	}
	else return "You are not authorized to delete this file";
}

function SetAcceptOnlinePayments($id)
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.venueid, b.total_cost, b.total_deposit, v.salestax, v.bookingfee FROM booking AS b LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE b.bookingid = ?"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($v,$c,$d,$t,$f);
		if ($stmt->fetch())
		{			
			$arr = array("venueid"=>$v,"cost"=>$c,"deposit"=>$d,"salestax"=>$t,"tax"=>0,"bookingfee"=>$f);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$full_tax = 0;
	$deposit_tax = 0;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT name,IFNULL(SUM(fee),0) FROM booking_adjustments WHERE bookingid = ? GROUP BY name"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($name,$fee);
		while ($stmt->fetch())
		{
			switch ($name)
			{
				case "full_tax":
					$full_tax = $fee;
					break;
				case "deposit_tax":
					$deposit_tax = $fee;
					break;
			}
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$bf = ceil(($arr['cost'] - $full_tax) * $arr['bookingfee'] * 100) / 100;
	if ($bf < 1) $bf = 1;
	$bft = ceil($bf * $arr['salestax'] * 100) / 100;
	$cost = $arr['cost'] + $bf + $bft;
	$deposit = $arr['deposit'] + $bf + $bft;
	$modified = 0;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("UPDATE booking SET total_cost = ?, total_deposit = ? WHERE bookingid = ?"))
	{	
		$stmt->bind_param("ddi",$cost,$deposit,$id);
		$stmt->execute();
		if ($stmt->affected_rows)
			$modified++;
		$GLOBALS['db']->CloseConn();
	}
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("INSERT INTO booking_adjustments (bookingid,name,fee) VALUES (?,'booking_fee',?),(?,'booking_fee_tax',?)"))
	{	
		$stmt->bind_param("idid",$id,$bf,$id,$bft);
		$stmt->execute();
		if ($stmt->affected_rows)
			$modified++;
		$GLOBALS['db']->CloseConn();
	}
	
	if ($modified == 2)
		return "success";
	else return "There was an error changing this booking";
}

function RecordPayment($id, $name, $method, $amount, $currency)
{
	$name = Sanitize($name);
	$method = Sanitize($method);
	
	$pid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("INSERT INTO payments (payment_method,last4,userid,timestamp,name,amount,currency,status) VALUES (?,'',?,UNIX_TIMESTAMP(),?,?,?,'processed')"))
	{	
		$m = "m_".$method;
		$stmt->bind_param("sisds",$m,$_SESSION['userid'],$name,$amount,$currency);
		$stmt->execute();
		if ($stmt->affected_rows)
			$pid = $stmt->insert_id;
		$GLOBALS['db']->CloseConn();
	}
	
	if ($pid)
	{			
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_payments (bookingid,paymentid) VALUES (?,?)"))
		{	
			$stmt->bind_param("ii",$id,$pid);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO payments_status (paymentid,userid,timestamp,status) VALUES (?,?,UNIX_TIMESTAMP(),?)"))
		{	
			$s = "Payment of ".FormatCurrency($amount,$currency)." manually entered by venue";
			$stmt->bind_param("iis",$pid,$_SESSION['userid'],$s);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_messages (bookingid,userid,timestamp,message,isvenue,viewed_by_user,viewed_by_venue) VALUES (?,?,UNIX_TIMESTAMP(),?,2,1,1)"))
		{	
			$msg = "Payment of ".FormatCurrency($amount,$currency)." recorded by venue";
			$stmt->bind_param("iis",$id,$_SESSION['userid'],$msg);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		BookingStateChanged($id);
		return "success";
	}
	
	return "There was an error recording this payment";
}

function CheckPromoExpiration($id)
{
	// find expired promo codes
	$arr = array();
	$amount = 0;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.promoid, b.amount, p.expires * 60, bo.start, bo.timestamp FROM booking_promos AS b JOIN promos AS p ON p.promoid = b.promoid JOIN booking AS bo ON bo.bookingid = b.bookingid WHERE (bo.status = 'Pending Deposit' OR bo.status = 'Pending Payment') AND b.amount > 0 AND (p.expires > 0 OR p.expires < 0) AND b.bookingid = ?"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($pid,$amt,$exp,$start,$ts);
		while($stmt->fetch())
		{
			$d = (new DateTime())->getTimestamp();
			if ($exp > 0 && $d > $ts + $exp)
			{
				$arr[] = $pid;
				$amount += $amt;
			}
			else if ($exp < 0 && $d > $start + $exp)
			{
				$arr[] = $pid;
				$amount += $amt;
			}
		}
		$GLOBALS['db']->CloseConn();
	}
	
	// set promo code amount to $0 and increase the taxes and total
	if (count($arr) > 0)
	{
		$taxrate = 0;
		$vid = null;
		$cur = "";
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT v.venueid, v.salestax, v.currency FROM booking AS b JOIN venues AS v ON v.venueid = b.venueid WHERE b.bookingid = ?"))
		{	
			$stmt->bind_param("i",$id);
			$stmt->execute();
			$stmt->bind_result($v,$st,$c);
			while($stmt->fetch())
			{
				$taxrate = $st;
				$vid = $v;
				$cur = $c;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$newtax = ceil($taxrate * $amount * 100) / 100;
		$newamt = $amount + $newtax;
		
		foreach ($arr as $p)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE booking_promos SET amount = 0 WHERE bookingid = ? AND promoid = ?"))
			{	
				$stmt->bind_param("ii",$id,$p);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE booking SET total_cost = total_cost + ? WHERE bookingid = ?"))
		{	
			$stmt->bind_param("di",$newamt,$id);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("UPDATE booking_adjustments SET fee = fee + ? WHERE name = 'full_tax' AND bookingid = ?"))
		{	
			$stmt->bind_param("di",$newtax,$id);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		UpdateBookingStatus($id,$vid,"message",FormatCurrency($amount,$cur)." of promo codes just expired",2);
	}
}

function VerifyPromoCode(&$promo)
{
	if (!isset($_SESSION['booking']))
		return "You no longer have a saved reservation, please start a new one";
	if ($promo['status'] != "active")
		return "This promo code is no longer valid";
	if ($promo['reuses'] > 0 && $promo['user_uses'] >= $promo['reuses'])
		return "You have already used this code ".$promo['user_uses']." times, you are not allowed to use it anymore";
	if ($promo['quant'] > 0 && $promo['uses'] >= $promo['quant'])
		return "The limited supply of this code has been used, this code is no longer available";
	if ($promo['entireinvoice'] == 1 && $_SESSION['booking']['cost'] < $promo['thresh'])
		return "This code is only valid for invoices with price exceeding ".FormatCurrency($promo['thresh'],$_SESSION['booking']['currency']).". ";
	
	$err = "";
	$applies = false;
	
	foreach ($_SESSION['booking']['resources'] as $b)
	{
		foreach ($promo['resources'] as $r)
		{
			$goodDate = false;
			$goodTime = false;
			$expGood = false;
			$threshGood = false;
			
			if ($r == $b['id'])
			{
				// make sure this matching resource is booked within the allowed start/stop dates
				
				$d1 = new DateTime();
				$d1->setTimestamp($promo['start']);
				$d1->setTimezone(new DateTimeZone($promo['tz']));
				$d2 = new DateTime();
				$d2->setTimestamp($promo['stop']);
				$d2->setTimezone(new DateTimeZone($promo['tz']));
				
				$start = $d1->getTimestamp();
				$stop = $d2->getTimestamp();
				
				if ($b['start'] >= $start && $b['start'] < $stop && $b['stop'] > $start && $b['stop'] <= $stop)
					$goodDate = true;
				else $err .= "This promo code is only valid for reservations between ".$d1->format("g:i A T M j, Y")." and ".$d2->format("g:i A T M j, Y").". ";

				
				// ensure this matching resource is booked during an acceptable timeslot
				
				$startmin=0;$stopmin=0;$firstdatetime=0;$startdatetime=0;$stopdatetime=0;
				CalcStartStopMinute($b['start'],$b['stop'],$promo['tz'],$startmin,$stopmin,$firstdatetime,$startdatetime,$stopdatetime);
				
				while ($startmin < 0)
					$startmin += 10080;
				while ($stopmin < 0)
					$stopmin += 10080;
				while ($stopmin < $startmin)
					$stopmin += 10080;
				
				$modified = true;
				while ($modified && $startmin <= $stopmin)
				{
					$modified = false;
					foreach ($promo['hours'] as $h)
					{
						if ($startmin >= $h['start'] && $startmin < $h['stop'])
						{
							$startmin = $h['stop'];
							
							// wrap it
							if ($h['stop'] < $h['start'])
								$startmin += 10080;
							
							if ($startmin >= $stopmin)
								break;
							
							$modified = true;
						}
					}
				}
				
				if ($startmin >= $stopmin)
					$goodTime = true;
				else $err .= "Your reservation includes time for which this code is not valid. ";
				
				// check if code has expired
				$d1 = new DateTime();
				if ($promo['expires'] < 0 && $d1->getTimestamp() > $_SESSION['booking']['start'] + ($promo['expires']*60))
					$err .= "This code is only valid if payment is made " . abs($promo['expires']) . " minutes before the booking start date. ";
				else $expGood = true;
				
				// check if discount only applies to this resource,
				if ($promo['entireinvoice'] == 0 && $b['cost'] < $promo['thresh'])
					$err .= "This code is only valid for reservations with price exceeding ".FormatCurrency($promo['thresh'],$_SESSION['booking']['currency']).". ";
				else $threshGood = true;
				
				// if all checks passed then apply it
				if ($goodDate && $goodTime && $expGood && $threshGood)
				{
					if ($promo['entireinvoice'] == 0)
					{
						if ($promo['doldisc'] > 0)
							$promo['discountAmount'] += $promo['doldisc'];
						if ($promo['perdisc'] > 0)
							$promo['discountAmount'] += floor($b['cost'] * ($promo['perdisc'] / 100) * 100) / 100;
					}
					
					$applies = true;
					$matched = true;
				}
			}
		}
	}
	
	// if promo requires all resources to be purchased then do that check
	if ($promo['applic'] == 3)
	{
		$matchedAll = true;
		foreach ($promo['resources'] as $r)
		{
			$matched = false;
			foreach ($_SESSION['booking']['resources'] as $b)
			{
				if ($r == $b['id'])
				{
					$matched = true;
				}
			}
			
			if (!$matched)
				$matchedAll = false;
		}
		
		if (!$matchedAll && $promo['applic'] == 3)
			return "Your reservation does not include all of the resources required for this code";
	}
	
	// if promo applies to the entire invoice then apply it now
	if ($promo['entireinvoice'] == 1 && $applies)
	{
		if ($promo['doldisc'] > 0)
			$promo['discountAmount'] += $promo['doldisc'];
		if ($promo['perdisc'] > 0)
			$promo['discountAmount'] += floor($_SESSION['booking']['cost'] * ($promo['perdisc'] / 100) * 100) / 100;
	}
	
	if ($applies)
		return 1;
	
	if (strlen($err) > 0)
		return $err;
	
	return "Your reservation is not eligible for this code";
}

function LoadPromoCode($code_name)
{
	$promo = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT promoid,p.name,p.description,dollar_threshold,dollar_discount,percentage_discount,reuses,quantity,applic,entireinvoice,combinable,auto,starttime,stoptime,expires,p.status,v.timezone FROM promos AS p LEFT JOIN venues AS v ON v.venueid = p.venueid LEFT JOIN venues_functionality AS f ON f.venueid = p.venueid WHERE p.status = 'active' AND p.venueid = ? AND p.name = ? AND f.showPromos = 1"))
	{	
		$stmt->bind_param("is",$_SESSION['booking']['venueid'],$code_name);
		$stmt->execute();
		$stmt->bind_result($pid,$name,$desc,$thresh,$doldisc,$perdisc,$reuses,$quant,$applic,$entire,$comb,$auto,$start,$stop,$exp,$status,$tz);
		if ($stmt->fetch())
		{
			$promo = array("id"=>$pid,
				"name"=>Sanitize($name),
				"desc"=>Sanitize($desc),
				"thresh"=>$thresh,
				"doldisc"=>$doldisc,
				"perdisc"=>$perdisc,
				"reuses"=>$reuses,
				"quant"=>$quant,
				"applic"=>$applic,
				"entireinvoice"=>$entire,
				"combinable"=>$comb,
				"auto"=>$auto,
				"start"=>$start,
				"stop"=>$stop,
				"expires"=>$exp,
				"status"=>$status,
				"tz"=>$tz,
				"uses"=>0,
				"user_uses"=>0,
				"discountAmount"=>0,
				"resources"=>array(),
				"hours"=>array()
			);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (is_array($promo))
	{
		if ($promo['expires'] > 0)
		{
			$now = (new DateTime())->getTimestamp();
			$d = new DateTime("@" . ($now + $promo['expires']*60));
			$d->setTimezone(new DateTimeZone($tz));
			$promo['desc'] .= " | Expires at " . $d->format("g:i A T M j, Y");
		}
		else if ($promo['expires'] < 0)
		{
			$d = new DateTime("@" . $_SESSION['booking']['start'] + $promo['expires']*60);
			$d->setTimezone(new DateTimeZone($tz));
			$promo['desc'] .= " | Expires at " . $d->format("g:i A T M j, Y");
		}
		
		// load promo hours
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT start,stop FROM promos_hours WHERE promoid = ? ORDER BY start ASC"))
		{
			$stmt->bind_param("i",$pid);
			$stmt->execute();
			$stmt->bind_result($start,$stop);
			while($stmt->fetch())
			{
				$promo['hours'][] = array("start"=>$start,"stop"=>$stop);
				$promo['hours'][] = array("start"=>$start+10080,"stop"=>$stop+10080);
				$promo['hours'][] = array("start"=>$start+20160,"stop"=>$stop+20160);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		// load promo resources
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT resourceid FROM promos_resources WHERE promoid = ? ORDER BY resourceid ASC"))
		{
			$stmt->bind_param("i",$pid);
			$stmt->execute();
			$stmt->bind_result($res);
			while($stmt->fetch())
			{
				$promo['resources'][] = $res;
			}
			$GLOBALS['db']->CloseConn();
		}
	
		$any = false;
		foreach ($promo['resources'] as $r)
			if ($r == 0)
				$any = true;
			
		if ($any)
		{
			unset($promo['resources']);
			$promo['resources'] = array();
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT resourceid FROM resources WHERE venueid = ? AND status = 'new'"))
			{
				$stmt->bind_param("i",$_SESSION['booking']['venueid']);
				$stmt->execute();
				$stmt->bind_result($res);
				while($stmt->fetch())
				{
					$promo['resources'][] = $res;
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		if ($promo['applic'] == 2 || $promo['applic'] == 4)
		{
			$children = array();
			foreach ($promo['resources'] as $r)
				$children = array_merge($children,FindChildren($r,true));
			$promo['resources'] = array_merge($promo['resources'],$children);
			
			for ($i=0; $i<count($promo['resources']); $i++)
			{
				for ($i2=0; $i2<count($promo['resources']); $i2++)
				{
					if ($i2 == $i)
						continue;
					
					if ($promo['resources'][$i2] == $promo['resources'][$i])
					{
						unset($promo['resources'][$i2]);
						$promo['resources'] = array_values($promo['resources']);
						
						if ($i2 < $i)
							$i--;
						$i2--;
					}
				}
			}
		}
		
		if (isset($promo['quantity']))
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT COUNT(DISTINCT p.bookingid) FROM booking_promos AS p LEFT JOIN booking AS b ON b.bookingid = p.bookingid WHERE p.promoid = ? AND (b.status = 'Pending Approval' OR b.status = 'Pending Payment' OR b.status = 'Paid' OR b.status = 'Past Due')"))
			{	
				$stmt->bind_param("i",$promo['id']);
				$stmt->execute();
				$stmt->bind_result($cnt);
				if ($stmt->fetch())
				{
					$promo['uses'] = $cnt;
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		if (isset($promo['reuses']) && isset($_SESSION['userid']))
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT COUNT(DISTINCT p.bookingid) FROM booking_promos AS p LEFT JOIN booking AS b ON b.bookingid = p.bookingid WHERE p.promoid = ? AND b.userid = ? AND (b.status = 'Pending Deposit' OR b.status = 'Pending Approval' OR b.status = 'Pending Payment' OR b.status = 'Paid' OR b.status = 'Past Due')"))
			{	
				$stmt->bind_param("ii",$promo['id'],$_SESSION['userid']);
				$stmt->execute();
				$stmt->bind_result($cnt);
				if ($stmt->fetch())
				{
					$promo['user_uses'] = $cnt;
				}
				$GLOBALS['db']->CloseConn();
			}
		}
		
		$promo['verified'] = VerifyPromoCode($promo);
	}
	
	return $promo;
}

function ApplyPromoCode($code_name)
{
	$promo = LoadPromoCode($code_name);
	
	if (is_array($promo))
	{
		if ($promo['auto'])
			return "This promo code was already considered";
	
		if (strlen($promo['verified']) > 1)
			return $promo['verified'];
		
		$_SESSION['booking']['promos'][] = array("id"=>$promo['id'],"name"=>$code_name);
		// UpdateBookingPrices() will be called after the invoice page is refreshed
		return "success";
	}
	else return "Invalid promo code!";
}

function CalcStartStopMinute($start, $stop, $tz, &$startmin, &$stopmin, &$first, &$starttime, &$stoptime)
{
	$startmin = 0;
	$stopmin = 0;
	
	$startdatetime = new DateTime("@".$start);
	$startdatetime->setTimezone(new DateTimeZone($tz));
	$stopdatetime = new DateTime("@".$stop);
	$stopdatetime->setTimezone(new DateTimeZone($tz));
	$firstdatetime = new DateTime(date("c", strtotime("this week",$startdatetime->getTimestamp())));
	$firstdatetime->setTimezone(new DateTimeZone($tz));
	$firstdatetime->setTime(0,0,0);
	if ($firstdatetime->format("l") == "Monday")
		$firstdatetime->sub(new DateInterval('P1D'));
	if ($firstdatetime->getTimestamp() > $startdatetime->getTimestamp())
		$firstdatetime->sub(new DateInterval('P7D'));
	if ($startdatetime->getOffset() != $firstdatetime->getOffset())
		$firstdatetime->setTimestamp($firstdatetime->getTimestamp() + ($firstdatetime->getOffset() - $startdatetime->getOffset()));
	
	$startmin = floor(($startdatetime->getTimestamp() - $firstdatetime->getTimestamp())/60);
	$stopmin = floor(($stopdatetime->getTimestamp() - $firstdatetime->getTimestamp())/60);
	
	$first = $firstdatetime;
	$starttime = $startdatetime;
	$stoptime = $stopdatetime;
}

?>
