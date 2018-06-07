<?php

function BuildSearchStringLike($str,$types)
{
	$ret = "";
	
	foreach ($types as $t)
	{
		if (strpos($t,"Any Type") === 0)
		{
			return " TRUE = TRUE ";
		}
		else
		{
			$ret .= "OR ".$str." '%".$t."%' ";
		}
	}
	
	$ret = preg_replace('/OR /','',$ret,1);
	$ret = "(".$ret.")";
	return $ret;
}

function BuildSearchStringIn($str,$types)
{
	$ret = "";
	
	foreach ($types as $t)
	{
		if (strpos($t,"Any Type") === 0)
		{
			return " TRUE = TRUE ";
		}
		else
		{
			$ret .= ",'".$t."'";
		}
	}
	
	$ret = preg_replace('/,/','',$ret,1);
	$ret = $str." (".$ret.")";
	return $ret;
}

function GetFeatureTypes()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT featureid,name FROM features ORDER BY featureid ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($id,$name);
		while($stmt->fetch())
		{
			$arr[] = array("id"=>$id,"name"=>Sanitize($name));
		}
		$GLOBALS['db']->CloseConn();
	}
	return $arr;
}

function GetResourceTypes()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT typeid,name FROM resources_types ORDER BY typeid ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($id,$name);
		while($stmt->fetch())
		{
			$arr[] = array("id"=>$id,"name"=>Sanitize($name));
		}
		$GLOBALS['db']->CloseConn();
	}
	return $arr;
}

function SearchVenues($filter)
{
	$result = array();
	$size = 0;
	$search = "";
	
	if (isset($filter['city']) && $filter['city'] != "all")
		$search .= $filter['city']."%";
	if (isset($filter['venueType']) && $filter['venueType'] != "all")
		$search .= "%" . $filter['venueType']."%";
	str_replace("%%","%",$search);
	
	if (isset($filter['size']))
		$size = preg_replace("/[^0-9]/","",$filter['size']);
	
	if ($size = "" || !$size)
		$size = 0;
	
	if (!isset($filter['types']))
		$filter['types'] = array("Any Type");
	// doesn't include timeslot billing
	$query = "SELECT v.shorturl, v.name, v.description, v.city, v.state, v.currency, t.name, v.latitude, v.longitude, 
				p.photo, COUNT(r.resourceid), r.default_rate, MIN(r.capacity), MAX(r.capacity), 
				MIN((SELECT IFNULL(MIN(rr.rate),r.default_rate) FROM resources_rates AS rr WHERE rr.resourceid = r.resourceid)) AS min, 
				MAX((SELECT IFNULL(MAX(rr.rate),r.default_rate) FROM resources_rates AS rr WHERE rr.resourceid = r.resourceid)) AS max 
			FROM resources AS r 
			JOIN resources_types AS rt ON rt.typeid = r.typeid
			JOIN venues AS v ON v.venueid = r.venueid
			JOIN venues_types AS t ON t.typeid = v.venue_typeid
			JOIN venues_photos AS p ON p.venueid = v.venueid AND (p.placement = 0 OR p.placement IS NULL)
			WHERE r.status = 'new' AND v.status = 'active' AND v.visibility = 'public'
			" . (strlen($search) > 0 ? "AND searchstring LIKE '".$search."'" : "") . "
			AND (((r.capacity >= ? AND (".BuildSearchStringIn("rt.name IN",$filter['types'])."))) )
			GROUP BY r.venueid ORDER BY v.currency";
			//AND ((".BuildSearchStringIn("f.name IN",$filter['types']).(isset($filter['typeFilter']) ? " OR REPLACE(LCASE(f.name),' ','-') = '".$filter['typeFilter']."'" : "")." OR (r.capacity >= ? AND (".BuildSearchStringIn("rt.name IN",$filter['types']).(isset($filter['typeFilter']) ? " OR REPLACE(LCASE(rt.name),' ','-') = '".$filter['typeFilter']."'" : "")."))) )
	//print_r($query);
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare($query))
	{
		$stmt->bind_param("i", $size);
		$stmt->execute();
		$stmt->bind_result($url,$vname,$vdesc,$city,$state,$currency,$type,$lat,$lng,$vpic,$rcnt,$rate,$cmin,$cmax,$min,$max);
		while($stmt->fetch())
		{
			$res = array("count"=>$rcnt,"rate"=>$rate,"cmin"=>$cmin,"cmax"=>$cmax,"min"=>$min,"max"=>$max);
			$result[] = array("url"=>$url,"name"=>$vname,"description"=>$vdesc,"city"=>$city,"state"=>$state,"currency"=>$currency,"type"=>$type,"latitude"=>Sanitize($lat),"longitude"=>Sanitize($lng),"photo"=>"/assets/content/thumbnail/".$vpic,"resources"=>array($res));
		}
		$GLOBALS['db']->CloseConn();
	}
		
	return $result;
}

function GetVenueProfile($venueShortURL)
{
	$result = array();
	$vid = null;
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT v.venueid,v.timezone,v.name,v.description,v.banner,v.shorturl,v.visibility,v.address,v.city,v.state,v.zip,v.country,t.name,s.name,v.currency
						FROM venues AS v 
							LEFT JOIN venues_types AS t ON t.typeid = v.venue_typeid 
							LEFT JOIN venues_styles AS s ON s.styleid = v.venue_styleid 
						WHERE shorturl=?"))
	{
		$stmt->bind_param("s", $venueShortURL);
		$stmt->execute();
		$stmt->bind_result($venueid,$tz,$venueName,$venueDescription,$banner,$url,$vis,$address,$city,$state,$zip,$country,$venueType,$venueStyle,$currency);
		while($stmt->fetch())
		{
			$result['tz'] = $tz;
			$result['url'] = $url;
			$result['name'] = Sanitize($venueName);
			$result['description'] = Sanitize($venueDescription);
			$result['banner'] = Sanitize($banner);
			$result['visibility'] = $vis;
			$result['address'] = array("address"=>$address,"city"=>$city,"state"=>$state,"zip"=>$zip,"country"=>$country);
			$result['type'] = $venueType;
			$result['style'] = $venueStyle;
			$result['currency'] = $currency;
			$result['openingHours'] = "";
			$result['types'] = array();
			$result['pictures'] = array();
			$result['resources'] = array();
			$result['addons'] = array();
			$result['menus'] = array();
			$result['personnel'] = array();
			$result['features'] = array();
			$result['functionality'] = array();
			$vid = $venueid;
		}
		$GLOBALS['db']->CloseConn();
	}
	if ($vid)
	{
		$approved = false;
		if (isset($_SESSION['venueRights']))
		{
			foreach ($_SESSION['venueRights'] as $venue)
				if ($venue['venueid'] == $vid && $venue['role'] > 0)
					$approved = $venue['role'];
				
			if ($_SESSION['siteRole'] == 999)
				$approved = 16;
		}
		
		if ($result['visibility'] != 'public' && !$approved)
			return "This venue only allows venue management to view the venue profile.  If you have been granted access, please sign in now to access this page.";
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT showMenus, showPersonnel, showQuestions, showPromos, publicFileUploads,gratuity,entireVenue FROM venues_functionality WHERE venueid = ?"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($m,$p,$q,$pc,$fu,$gra,$ev);
			while($stmt->fetch())
			{
				$result['functionality']['menus'] = $m;
				$result['functionality']['personnel'] = $p;
				$result['functionality']['questions'] = $q;
				$result['functionality']['promos'] = $pc;
				$result['functionality']['publicFileUploads'] = $fu;
				$result['functionality']['gratuity'] = $gra;
				$result['functionality']['entireVenue'] = $ev;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT photo,caption FROM venues_photos WHERE venueid=? ORDER BY placement ASC"))
		{
			$stmt->bind_param("i", $vid);
			$stmt->execute();
			$stmt->bind_result($url,$caption);
			while($stmt->fetch())
			{
				$result['pictures'][] = array("url"=>"/assets/content/".$url,"caption"=>$caption);
			}
			$GLOBALS['db']->CloseConn();
			if (count($result['pictures']) == 1)
				$result['pictures'][] = $result['pictures'][0];
			if (count($result['pictures']) == 2)
				$result['pictures'][] = array("url"=>"/assets/img/placeholder-720-480.png","caption"=>"");
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT f.name FROM venues_features AS v LEFT JOIN features AS f ON f.featureid = v.featureid WHERE v.venueid=? ORDER BY f.name ASC"))
		{
			$stmt->bind_param("i", $vid);
			$stmt->execute();
			$stmt->bind_result($feature);
			while($stmt->fetch())
			{
				$result['features'][] = Sanitize($feature);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT DISTINCT t.typeid, t.name FROM resources_types AS t LEFT JOIN resources AS r ON r.typeid = t.typeid WHERE r.venueid=? AND r.status = 'new' ORDER BY t.name ASC"))
		{
			$stmt->bind_param("i", $vid);
			$stmt->execute();
			$stmt->bind_result($id,$name);
			while($stmt->fetch())
			{
				$result['types'][] = array("id"=>$id,'name'=>Sanitize($name));
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$evid = -1;
		if ($result['functionality']['entireVenue'] == 0)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT MIN(resourceid) FROM resources WHERE venueid = ?"))
			{
				$stmt->bind_param("i",$vid);
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
		if ($stmt->prepare("SELECT r.name,r.description,r.timeslots," .
							"(SELECT IFNULL(MIN(rate),0) FROM resources_slots AS s JOIN resources AS r2 ON r2.resourceid = s.resourceid WHERE s.resourceid = r.resourceid)," .
							"(SELECT COUNT(DISTINCT s.rate) - 1 FROM resources_slots AS s JOIN resources AS r2 ON r2.resourceid = s.resourceid WHERE s.resourceid = r.resourceid)," .
							"(SELECT COUNT(*) FROM resources_rates AS s JOIN resources AS r2 ON r2.resourceid = s.resourceid WHERE s.resourceid = r.resourceid)," .
							"r.minduration,r.capacity,r.default_rate,p.photo,p.caption FROM resources AS r LEFT JOIN resources_photos AS p ON p.resourceid = r.resourceid WHERE r.venueid=? AND r.status = 'new' AND (p.placement IS NULL OR p.placement = 0) AND r.resourceid != ? ORDER BY r.default_rate DESC, r.name ASC"))
		{
			$stmt->bind_param("ii", $vid, $evid);
			$stmt->execute();
			$stmt->bind_result($name,$desc,$timeslots,$trate,$tcnt,$rcnt,$min,$cap,$rate,$pic,$caption);
			while($stmt->fetch())
			{
				
				$result['resources'][] = array(
					"name"=>Sanitize($name),
					"description"=>Sanitize($desc),
					"timeslots"=>$timeslots,
					"min"=>$min,
					"capacity"=>$cap,
					"price"=>($timeslots?$trate:$rate),
					"pricePlus"=>((($timeslots && $tcnt) || (!$timeslots && $rcnt)) ? true : false),
					"photo"=>($pic==null?"":"/assets/content/thumbnail/".$pic),
					"caption"=>($caption==null?"":Sanitize($caption))
				);
				//$result['resources'][] = array('name'=>Sanitize($name),'description'=>Sanitize($desc),"timeslots"=>$timeslots,"min"=>$min,"capacity"=>$cap,"price"=>$rate,"photo"=>($pic==null?"":"/assets/content/thumbnail/".$pic),"caption"=>($caption==null?"":Sanitize($caption)));
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT a.name,a.description,a.price,p.photo,p.caption FROM addons AS a LEFT JOIN addons_photos AS p ON p.addonid = a.addonid WHERE venueid=? AND status = 'new' AND (p.placement IS NULL OR p.placement = 0) ORDER BY a.name ASC"))
		{
			$stmt->bind_param("i", $vid);
			$stmt->execute();
			$stmt->bind_result($name,$desc,$rate,$photo,$caption);
			while($stmt->fetch())
			{
				$price = $rate;
				$result['addons'][] = array('name'=>Sanitize($name),'description'=>Sanitize($desc),'price'=>$price,'photo'=>($photo==null?"":"/assets/content/thumbnail/".$photo),'caption'=>($caption==null?"":Sanitize($caption)));
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT m.menuid,m.name,m.description FROM menus AS m LEFT JOIN venues_functionality AS v ON v.venueid = m.venueid WHERE m.venueid=? AND m.status = 'new' AND v.showMenus = 1 ORDER BY m.menuid ASC"))
		{
			$stmt->bind_param("i", $vid);
			$stmt->execute();
			$stmt->bind_result($id,$name,$desc);
			while($stmt->fetch())
			{
				$result['menus'][] = array('id'=>$id,'name'=>Sanitize($name),'description'=>Sanitize($desc),'items'=>array());
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT i.menuid,i.name,i.description,i.photo,i.caption,i.price,i.min,i.max FROM menus_items AS i LEFT JOIN venues_functionality AS v ON v.venueid = i.venueid WHERE i.venueid=? AND i.status = 'new' AND v.showMenus = 1 ORDER BY i.itemid ASC"))
		{
			$stmt->bind_param("i", $vid);
			$stmt->execute();
			$stmt->bind_result($mid,$name,$desc,$photo,$cap,$price,$min,$max);
			while($stmt->fetch())
			{
				for ($i = 0; $i < count($result['menus']); $i++)
				{
					if ($result['menus'][$i]['id'] == $mid)
					{
						$result['menus'][$i]['items'][] = array('name'=>Sanitize($name),'description'=>Sanitize($desc),
							'photo'=>($photo==null?"":"/assets/content/thumbnail/".$photo),'caption'=>($cap==null?"":Sanitize($cap)),'price'=>$price,'min'=>$min,'max'=>$max);
						break;
					}
				}
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT p.name,p.description,p.price FROM personnel AS p LEFT JOIN venues_functionality AS v ON v.venueid = p.venueid WHERE p.venueid=? AND p.status = 'new' AND v.showPersonnel = 1 ORDER BY personnelid ASC"))
		{
			$stmt->bind_param("i", $vid);
			$stmt->execute();
			$stmt->bind_result($name,$desc,$price);
			while($stmt->fetch())
			{
				$result['personnel'][] = array('name'=>Sanitize($name),'description'=>Sanitize($desc),'rate'=>$price);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		/*
		$hours = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT h.startminute, h.stopminute FROM resources_hours AS h JOIN resources AS r ON r.resourceid = h.resourceid JOIN venues AS v ON v.venueid = r.venueid WHERE v.venueid = ? AND r.status = 'new' UNION SELECT s.startminute, s.stopminute FROM resources_slots AS s JOIN resources AS r ON r.resourceid = s.resourceid JOIN venues AS v ON v.venueid = r.venueid WHERE v.venueid = ? AND r.status = 'new'"))
		{
			$stmt->bind_param("i", $vid);
			$stmt->execute();
			$stmt->bind_result($start,$stop);
			while($stmt->fetch())
			{
				$hours[] = array("start"=>$start,"stop"=>$stop,"status"=>"open");
			}
			$GLOBALS['db']->CloseConn();
		}
		require_once("php/book.php");
		$hours = ConsolidateStatus($hours);
		foreach ($hours as $h)
			if ($h['start'] == 0 && $h['stop'] == 0)
				$result['openingHours'] = "Mo-Su";
		if ($result['openingHours'] == "")
		{
			$days = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,+\6=>0);
			foreach ($hours as $h)
			{
				$days[floor($h['start'] / 1440)] = 1;
				$days[floor($h['stop'] / 1440)] = 1;
				// this logic will not work, this problem is actually quite hard.
				//  using the schema.org format is not ideal
			}
		}
		*/
	}
	else return "Invalid venue specified";
	
	return $result;
}

function GetAddonTypes()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT typeid,name FROM addons_types ORDER BY typeid ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($id,$name);
		while($stmt->fetch())
		{
			$arr[] = array("id"=>$id,"name"=>Sanitize($name));
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetMenuItemTypes()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT typeid,name FROM menus_types ORDER BY typeid ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($id,$name);
		while($stmt->fetch())
		{
			$arr[] = array("id"=>$id,"name"=>Sanitize($name));
		}
		$GLOBALS['db']->CloseConn();
	}
	return $arr;
}

function GetVenueStyles()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT styleid,name FROM venues_styles ORDER BY styleid ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($id,$name);
		while($stmt->fetch())
		{
			$arr[] = array("id"=>$id,"name"=>Sanitize($name));
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetVenueTypes()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT typeid,name FROM venues_types ORDER BY typeid ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($id,$name);
		while($stmt->fetch())
		{
			$arr[] = array("id"=>$id,"name"=>Sanitize($name));
		}
		$GLOBALS['db']->CloseConn();
	}
	return $arr;
}

function GetVenueFeatures()
{
	$arr = array();
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT featureid,name FROM features ORDER BY name ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($id,$name);
		while($stmt->fetch())
		{
			$arr[] = array("id"=>$id,"name"=>Sanitize($name));
		}
		$GLOBALS['db']->CloseConn();
	}
	return $arr;
}

?>