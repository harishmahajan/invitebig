<?php

/*

	General Usage Pattern:
	
	$addon = new Addon($addonid);
	echo $addon->name;
	
	Class automatically load DB data upon instantiation.
	
	Venue class loads them all:
	
	$venue = new Venue(2);
	echo "Addons: " . count($venue->addons);
	echo "Addon 0: " . $venue->addons[0]->name;
	echo "Addon 0 Picture 0: " . $venue->addons[0]->pictures[0]->filename;
	echo "Resource 0: " .$venue->resources[0]->name;
	
*/

class Addon
{
	public $addonid = null;
	public $venueid = null;
	public $typeid = null;
	public $type = null;
	public $name = null;
	public $desc = null;
	public $min = null;
	public $max = null;
	public $deliverable = null;
	public $deposit_policyid = null;
	public $refund_policyid = null;
	public $price = null;
	public $status = null;
	public $hours = array();
	public $pictures = array();
	
	public function __construct($id)
	{
		$this->addonid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT a.*, t.name AS 'type' FROM addons AS a JOIN addons_types AS t ON t.typeid = a.typeid WHERE addonid = ?"))
		{
			$stmt->bind_param("i", $this->addonid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->typeid = $row['typeid'];
				$this->type = $row['type'];
				$this->name = $row['name'];
				$this->desc = $row['description'];
				$this->min = $row['min'];
				$this->max = $row['max'];
				$this->deliverable = $row['deliverable'];
				$this->deposit_policyid = $row['deposit_policyid'];
				$this->refund_policyid = $row['refund_policyid'];
				$this->price = $row['price'];
				$this->status = $row['status'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT startminute, stopminute FROM addons_hours WHERE addonid = ? ORDER BY startminute ASC"))
		{
			$stmt->bind_param("i", $this->addonid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->hours[] = array("start"=>$row['startminute'],"stop"=>$row['stopminute']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT photoid, placement, photo, caption FROM addons_photos WHERE addonid = ? ORDER BY placement ASC"))
		{
			$stmt->bind_param("i", $this->addonid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->pictures[] = new Picture($row['photoid'], $row['placement'], $row['photo'], $row['caption']);
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class DepositPolicy
{
	public $policyid = null;
	public $venueid = null;
	public $name = null;
	public $treshold = null;
	public $percent = null;
	public $fixedAmount = null;
	public $fullDue = null;
	
	public function __construct($id)
	{
		$this->policyid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM deposit_policies WHERE policyid = ?"))
		{
			$stmt->bind_param("i", $this->policyid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->name = $row['name'];
				$this->treshold = $row['deposit_dollar_thresh'];
				$this->percent = $row['deposit_perc'];
				$this->fixedAmount = $row['deposit_amount'];
				$this->fullDue = $row['full_due'];
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class Feature
{
	public $featureid = null;
	public $name = null;
	
	public function __construct($id)
	{
		$this->featureid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM features WHERE featureid = ?"))
		{
			$stmt->bind_param("i", $this->featureid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->name = $row['name'];
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class Menu
{
	public $menuid = null;
	public $venueid = null;
	public $name = null;
	public $desc = null;
	public $depositPolicyId = null;
	public $refundPolicyId = null;
	public $status = null;
	public $hours = array();
	public $items = array();
	
	public function __construct($id)
	{
		$this->menuid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM menus WHERE menuid = ?"))
		{
			$stmt->bind_param("i", $this->menuid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->name = $row['name'];
				$this->desc = $row['description'];
				$this->depositPolicyId = $row['deposit_policyid'];
				$this->refundPolicyId = $row['refund_policyid'];
				$this->status = $row['status'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT startminute, stopminute FROM menus_hours WHERE menuid = ? ORDER BY startminute ASC"))
		{
			$stmt->bind_param("i", $this->menuid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->hours[] = array("start"=>$row['startminute'],"stop"=>$row['stopminute']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$items = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT itemid FROM menus_items WHERE status != 'deleted' AND menuid = ?"))
		{
			$stmt->bind_param("i", $this->menuid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$items[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($items as $i)
			$this->items[] = new MenuItem($i);
	}
}

class MenuItem
{
	public $itemid = null;
	public $menuid = null;
	public $venueid = null;
	public $name = null;
	public $typeid = null;
	public $type = null;
	public $price = null;
	public $min = null;
	public $max = null;
	public $desc = null;
	public $status = null;
	public $pictures = array();
	
	public function __construct($id)
	{
		$this->itemid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT i.*, t.name AS 'typename' FROM menus_items AS i JOIN menus_types AS t ON t.typeid = i.type WHERE itemid = ?"))
		{
			$stmt->bind_param("i", $this->itemid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->menuid = $row['menuid'];
				$this->venueid = $row['venueid'];
				$this->name = $row['name'];
				$this->typeid = $row['type'];
				$this->type = $row['typename'];
				$this->price = $row['price'];
				$this->min = $row['min'];
				$this->max = $row['max'];
				$this->desc = $row['description'];
				$this->status = $row['status'];
				$this->pictures[] = new Picture(null, 0, $row['photo'], $row['caption']);
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class Personnel
{
	public $personnelid = null;
	public $venueid = null;
	public $name = null;
	public $desc = null;
	public $price = null;
	public $min = null;
	public $max = null;
	public $required = null;
	public $depositPolicyId = null;
	public $refundPolicyId = null;
	public $status = null;
	public $resourceList = array();
	
	public function __construct($id)
	{
		$this->personnelid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM personnel WHERE personnelid = ?"))
		{
			$stmt->bind_param("i", $this->personnelid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->name = $row['name'];
				$this->desc = $row['description'];
				$this->price = $row['price'];
				$this->min = $row['min'];
				$this->max = $row['max'];
				$this->required = $row['req'];
				$this->depositPolicyId = $row['deposit_policyid'];
				$this->refundPolicyId = $row['refund_policyid'];
				$this->status = $row['status'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT startminute, stopminute FROM personnel_hours WHERE personnelid = ? ORDER BY startminute ASC"))
		{
			$stmt->bind_param("i", $this->personnelid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->hours[] = array("start"=>$row['startminute'],"stop"=>$row['stopminute']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT resourceid FROM personnel_resources WHERE personnelid = ?"))
		{
			$stmt->bind_param("i", $this->personnelid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->resourceList[] = $row['resourceid'];
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class Picture
{
	public $photoid = null;
	public $placement = null;
	public $filename = null;
	public $caption = null;
	
	public function __construct($id, $placement, $filename, $caption)
	{
		$this->photoid = $id;
		$this->placement = $placement;
		$this->filename = $filename;
		$this->caption = $caption;
	}
}

class Promo
{
	public $promoid = null;
	public $venueid = null;
	public $lastmod = null;
	public $name = null;
	public $desc = null;
	public $threshold = null;
	public $dollarDiscount = null;
	public $percentDiscount = null;
	public $reuses = null;
	public $quantity = null;
	public $applic = null;
	public $entireInvoice = null;
	public $combinable = null;
	public $start = null;
	public $stop = null;
	public $expires = null;
	public $status = null;
	public $auto = null;
	public $hours = array();
	public $resourceList = array();
	
	public function __construct($id)
	{
		$this->promoid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM promos WHERE promoid = ?"))
		{
			$stmt->bind_param("i", $this->promoid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->lastmod = $row['lastmodified'];
				$this->name = $row['name'];
				$this->desc = $row['description'];
				$this->threshold = $row['dollar_threshold'];
				$this->dollarDiscount = $row['dollar_discount'];
				$this->percentDiscount = $row['percentage_discount'];
				$this->reuses = $row['reuses'];
				$this->quantity = $row['quantity'];
				$this->applic = $row['applic'];
				$this->entireInvoice = $row['entireinvoice'];
				$this->combinable = $row['combinable'];
				$this->start = $row['starttime'];
				$this->stop = $row['stoptime'];
				$this->expires = $row['expires'];
				$this->status = $row['status'];
				$this->auto = $row['auto'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT start, stop FROM promos_hours WHERE promoid = ? ORDER BY start ASC"))
		{
			$stmt->bind_param("i", $this->promoid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->hours[] = array("start"=>$row['start'],"stop"=>$row['stop']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT resourceid FROM promos_resources WHERE promoid = ?"))
		{
			$stmt->bind_param("i", $this->promoid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->resourceList[] = $row['resourceid'];
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class Question
{
	public $questionid = null;
	public $venueid = null;
	public $deleted = null;
	public $question = null;
	public $type = null;
	public $required = null;
	public $addonList = array();
	public $choices = array();
	public $menuList = array();
	public $personnelList = array();
	public $resourceList = array();
	
	public function __construct($id)
	{
		$this->questionid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM questions WHERE questionid = ?"))
		{
			$stmt->bind_param("i", $this->questionid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->deleted = $row['deleted'];
				$this->question = $row['question'];
				$this->type = $row['type'];
				$this->required = $row['req'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT addonid FROM questions_addons WHERE questionid = ?"))
		{
			$stmt->bind_param("i", $this->questionid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->addonList[] = $row['addonid'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT choiceid, choice FROM questions_choices WHERE questionid = ?"))
		{
			$stmt->bind_param("i", $this->questionid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->choices[] = array("id"=>$row['choiceid'],"choice"=>$row['choice']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT menuid FROM questions_menus WHERE questionid = ?"))
		{
			$stmt->bind_param("i", $this->questionid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->menuList[] = $row['menuid'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT personnelid FROM questions_personnel WHERE questionid = ?"))
		{
			$stmt->bind_param("i", $this->questionid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->personnelList[] = $row['personnelid'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT resourceid FROM questions_resources WHERE questionid = ?"))
		{
			$stmt->bind_param("i", $this->questionid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->resourceList[] = $row['resourceid'];
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class RefundPolicy
{
	public $policyid = null;
	public $venueid = null;
	public $name = null;
	public $policy = array();

	public function __construct($id)
	{
		$this->policyid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM refund_policies WHERE policyid = ?"))
		{
			$stmt->bind_param("i", $this->policyid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->name = $row['name'];
				$this->policy = json_decode($row['policy']);
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class Resourc
{
	public $resourceid = null;
	public $venueid = null;
	public $typeid = null;
	public $type = null;
	public $name = null;
	public $autoApprove = null;
	public $depositPolicyId = null;
	public $refundPolicyId = null;
	public $over21 = null;
	public $capacity = null;
	public $seats = null;
	public $timeslots = null;
	public $minDuration = null;
	public $increment = null;
	public $leadTime = null;
	public $cleanupCost = null;
	public $cleanupTime = null;
	public $desc = null;
	public $defaultRate = null;
	public $status = null;

	public $addonList = array();
	public $hours = array();
	public $pictures = array();
	public $rates = array();
	public $ratesRaw = array();
	public $rateExceptions = array();
	public $slots = array();
	
	public function __construct($id)
	{
		$this->resourceid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT r.*, t.name AS 'type' FROM resources AS r JOIN resources_types AS t ON t.typeid = r.typeid WHERE resourceid = ?"))
		{
			$stmt->bind_param("i", $this->resourceid);
			$stmt->execute();
			$rows = $stmt->get_result();
			if ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->typeid = $row['typeid'];
				$this->type = $row['type'];
				$this->name = $row['name'];
				$this->autoApprove = $row['autoapprove'];
				$this->depositPolicyId = $row['deposit_policyid'];
				$this->refundPolicyId = $row['refund_policyid'];
				$this->over21 = $row['over21_req'];
				$this->capacity = $row['capacity'];
				$this->seats = $row['seats'];
				$this->timeslots = $row['timeslots'];
				$this->minDuration = $row['minduration'];
				$this->increment = $row['increment'];
				$this->leadTime = $row['min_lead_time'];
				$this->cleanupCost = $row['cleanupcost'];
				$this->cleanupTime = $row['cleanuptime'];
				$this->desc = $row['description'];
				$this->defaultRate = $row['default_rate'];
				$this->status = $row['status'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM resources_addons WHERE resourceid = ?"))
		{
			$stmt->bind_param("i", $this->resourceid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->addonList[] = $row['addonid'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM resources_hours WHERE resourceid = ?"))
		{
			$stmt->bind_param("i", $this->resourceid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->hours[] = array("start"=>$row['startminute'],"stop"=>$row['stopminute']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM resources_photos WHERE resourceid = ? ORDER BY placement ASC"))
		{
			$stmt->bind_param("i", $this->resourceid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->pictures[] = new Picture($row['photoid'], $row['placement'], $row['photo'], $row['caption']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM resources_rates WHERE resourceid = ?"))
		{
			$stmt->bind_param("i", $this->resourceid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->rates[] = array("start"=>$row['startminute'],"stop"=>$row['stopminute'],"rate"=>$row['rate']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM resources_rates_raw WHERE resourceid = ?"))
		{
			$stmt->bind_param("i", $this->resourceid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->ratesRaw[] = array("start"=>$row['startminute'],"stop"=>$row['stopminute'],"rate"=>$row['rate']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM resources_rate_exceptions WHERE resourceid = ?"))
		{
			$stmt->bind_param("i", $this->resourceid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->ratesExceptions[] = array("start"=>$row['starttime'],"stop"=>$row['stoptime'],"rate"=>$row['rate']);
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM resources_slots WHERE resourceid = ?"))
		{
			$stmt->bind_param("i", $this->resourceid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->slots[] = array("start"=>$row['startminute'],"stop"=>$row['stopminute'],"rate"=>$row['rate'],"combinable"=>$row['combinable']);
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class VenueRight
{
	public $rightid = null;
	public $venueid = null;
	public $email = null;
	public $grantedBy = null;
	public $timestamp = null;
	public $role = null;
	public $receiveEmails = null;

	public function __construct($id)
	{
		$this->rightid = $id;
		$this->Load();
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM venues_rights WHERE rightid = ?"))
		{
			$stmt->bind_param("i", $this->rightid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->email = $row['email'];
				$this->grantedBy = $row['grantedby'];
				$this->timestamp = $row['timestamp'];
				$this->role = $row['role'];
				$this->receiveEmails = $row['receiveEmails'];
			}
			$GLOBALS['db']->CloseConn();
		}
	}
}

class Venue
{
	public $venueid = null;
	public $timestamp = null;
	public $lastmod = null;
	public $timezone = null;
	public $typeid = null;
	public $type = null;
	public $shorturl = null;
	public $searchstring = null;
	public $name = null;
	public $description = null;
	public $banner = null;
	public $visibility = null;
	public $website = null;
	public $facebook = null;
	public $businessName = null;
	public $phone = null;
	public $salesTax = null;
	public $currency = null;
	public $ein = null;
	public $bookingFee = null;
	public $processingFee = null;
	public $contract = null;
	public $status = null;
	
	public $address = array(
		"address"=>null,
		"city"=>null,
		"state"=>null,
		"zip"=>null,
		"country"=>null,
		"latitude"=>null,
		"longitude"=>null
	);
	
	public $addons = array();
	public $depositPolicies = array();
	public $menus = array();
	public $personnel = array();
	public $pictures = array();
	public $promos = array();
	public $questions = array();
	public $refundPolicies = array();
	public $resources = array();
	public $venueRights = array();
	public $features = array();
	
	public $functionality = array(
		"personnel"=>null,
		"questions"=>null,
		"promos"=>null,
		"publicFileUploads"=>null,
		"gratuity"=>null,
		"entireVenue"=>null,
	);
	
	public function __construct($id)
	{
		$this->venueid = $id;
		$this->Load();
		$this->LoadAddons();
		$this->LoadDepositPolicies();
		$this->LoadMenus();
		$this->LoadPersonnel();
		$this->LoadPictures();
		$this->LoadPromos();
		$this->LoadQuestions();
		$this->LoadRefundPolicies();
		$this->LoadResources();
		$this->LoadVenueRights();
		$this->LoadFunctionality();
		$this->LoadFeatures();
	}
	
	public function CountBookings()
	{		
		$c = 0;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT COUNT(*) FROM booking WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($c);
			if ($stmt->fetch())
			{
				$count = $c;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		return $c;
	}
	
	public function Load()
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT v.*, t.name AS 'type' FROM venues AS v JOIN venues_types AS t ON t.typeid = v.venue_typeid WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->venueid = $row['venueid'];
				$this->timestamp = $row['timestamp'];
				$this->lastmod = $row['mod_timestamp'];
				$this->timezone = $row['timezone'];
				$this->typeid = $row['venue_typeid'];
				$this->type = $row['type'];
				$this->shorturl = $row['shorturl'];
				$this->searchstring = $row['searchstring'];
				$this->name = $row['name'];
				$this->description = $row['description'];
				$this->banner = $row['banner'];
				$this->visibility = $row['visibility'];
				$this->website = $row['website'];
				$this->facebook = $row['facebook'];
				$this->businessName = $row['business_name'];
				$this->phone = $row['phone'];
				$this->salesTax = $row['salestax'];
				$this->currency = $row['currency'];
				$this->ein = $row['ein'];
				$this->bookingFee = $row['bookingfee'];
				$this->processingFee = $row['processingfee'];
				$this->contract = $row['contract'];
				$this->status = $row['status'];
				
				$this->address['address'] = $row['address'];
				$this->address['city'] = $row['city'];
				$this->address['state'] = $row['state'];
				$this->address['zip'] = $row['zip'];
				$this->address['country'] = $row['country'];
				$this->address['latitude'] = $row['latitude'];
				$this->address['longitude'] = $row['longitude'];
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	public function LoadAddons()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT addonid FROM addons WHERE status != 'deleted' AND venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->addons[] = new Addon($i);
	}
	
	public function LoadDepositPolicies()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT policyid FROM deposit_policies WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->depositPolicies[] = new DepositPolicy($i);
	}
	
	public function LoadMenus()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT menuid FROM menus WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->menus[] = new Menu($i);
	}
	
	public function LoadPersonnel()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT personnelid FROM personnel WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->personnel[] = new Personnel($i);
	}
	
	public function LoadPictures()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT photoid, placement, photo, caption FROM venues_photos WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i,$p,$f,$c);
			while ($stmt->fetch())
			{
				$this->pictures[] = new Picture($i,$p,$f,$c);
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	public function LoadPromos()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT promoid FROM promos WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->promos[] = new Promo($i);
	}
	
	public function LoadQuestions()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT questionid FROM questions WHERE deleted = 0 AND venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->questions[] = new Question($i);
	}
	
	public function LoadRefundPolicies()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT policyid FROM refund_policies WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->refundPolicies[] = new RefundPolicy($i);
	}
	
	public function LoadResources()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT resourceid FROM resources WHERE status != 'deleted' AND venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->resources[] = new Resourc($i);
	}
	
	public function LoadVenueRights()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT rightid FROM venues_rights WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->venueRights[] = new VenueRight($i);
	}
	
	public function LoadFunctionality()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT * FROM venues_functionality WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$rows = $stmt->get_result();
			while ($row = $rows->fetch_array(MYSQLI_ASSOC))
			{
				$this->functionality['menus'] = $row['showMenus'];
				$this->functionality['personnel'] = $row['showPersonnel'];
				$this->functionality['questions'] = $row['showQuestions'];
				$this->functionality['promos'] = $row['showPromos'];
				$this->functionality['publicFileUploads'] = $row['publicFileUploads'];
				$this->functionality['gratuity'] = $row['gratuity'];
				$this->functionality['entireVenue'] = $row['entireVenue'];
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->venueRights[] = new VenueRight($i);
	}
	
	public function LoadFeatures()
	{
		$arr = array();
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT featureid FROM venues_features WHERE venueid = ?"))
		{
			$stmt->bind_param("i", $this->venueid);
			$stmt->execute();
			$stmt->bind_result($i);
			while ($stmt->fetch())
			{
				$arr[] = $i;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		foreach ($arr as $i)
			$this->features[] = new Feature($i);
	}
}

?>