<?php

function LoadGuide($guideid)
{
	$name = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT name FROM guides WHERE guideid = ?"))
	{
		$stmt->bind_param("i", $guideid);
		$stmt->execute();
		$stmt->bind_result($n);
		if ($stmt->fetch())
		{
			$name = $n;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$guide = null;
	
	switch ($name)
	{
		case "venueOnboarding":
			$guide = new VenueOnboardingGuide($guideid);
			break;
		default:
			$guide = new Guide($guideid);
	}
	
	return $guide;
}

class Guide
{	
	/* 
	
	Process Flow:
	
		1. On venue creation insert a row into 'guides':
		
			INSERT INTO guides (venueid,name,timestamp,status) 
				VALUES (1,'venueOnboarding',UNIX_TIMESTAMP(),'[]');
		
		2. GetVenueNotifications will find this guide and tell the user to load it

		3. User sends AJAX to load the guide, we instantiate a new Guide with it and return data:
		
				$guide = new VenueOnboardingGuide($guideid);
				return $guide->JSON();
		
		4. As client side tasks are done and need to be marked as complete, call MarkComplete() to
			save that status.  taskDone will be set to 2 for manually completed tasks, or 1 for 
			CheckCompletion().  taskDone==2 will be preserved.
	*/
	
	public $guideid = null;
	public $venueid = null;
	public $userid = null;
	public $guideName = null;
	public $tasks = array();
	public $isDone = false;
	public $auth = false;
	public $oneLiner = "";
	
	public function __construct($guideid)
	{
		$this->guideid = $guideid;
		$this->LoadStatus();
	}
	
	public function LoadStatus()
	{
		if ($this->guideid)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT venueid, userid, name, status FROM guides WHERE guideid = ?"))
			{
				$stmt->bind_param("i", $this->guideid);
				$stmt->execute();
				$stmt->bind_result($v, $u, $n, $s);
				if ($stmt->fetch())
				{
					if ($v && isset($_SESSION['venueRights']))
					{
						foreach ($_SESSION['venueRights'] as $venue)
						{
							if ($venue['venueid'] == $v) 
							{
								if ($venue['role'] > 0)
									$this->auth = true;
							}
						}
					}
					
					if ($u && isset($_SESSION['userid']) && $_SESSION['userid'] == $u)
						$this->auth = true;
					
					$this->venueid = $v;
					$this->userid = $u;
					$this->guideName = $n;
					
					if ($s == "done")
					{
						$this->isDone = true;
						for ($i2=0; $i2<count($this->tasks); $i2++)
							$this->tasks[$i2]['taskDone'] = 2;
					}
					else
					{
						$arr = json_decode($s,true);
						for ($i=0; $i<count($arr); $i++)
						{
							for ($i2=0; $i2<count($this->tasks); $i2++)
							{
								if ($this->tasks[$i2]['taskID'] == $arr[$i]['taskID'])
								{
									if ($arr[$i]['taskDone'] > $this->tasks[$i2]['taskDone'])
										$this->tasks[$i2]['taskDone'] = $arr[$i]['taskDone'];
								}
							}
						}
					}
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$this->CheckCompletion();
			$this->SaveStatus();
		}
		
		return true;
	}
	
	public function MarkComplete($x)
	{
		for ($i=0; $i<count($this->tasks); $i++)
			if ($this->tasks[$i]['taskID'] == $x)
				$this->tasks[$i]['taskDone'] = 2;
			
		$this->SaveStatus();
		
		return true;
	}
	
	public function SaveStatus()
	{
		if ($this->auth && $this->guideid && !$this->isDone)
		{
			$this->isDone = true;
			$arr = array();
			for ($i=0; $i<count($this->tasks); $i++)
			{
				if ($this->tasks[$i]['taskDone'] == 0)
					$this->isDone = false;
				
				$arr[] = array("taskID"=>$this->tasks[$i]['taskID'],"taskDone"=>$this->tasks[$i]['taskDone'],"taskWeight"=>$this->tasks[$i]['taskWeight']);
			}
			$j = json_encode($arr);
			
			if ($this->isDone)
				$j = "done";
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO guides (guideid,status,timestamp) VALUES (?,?,UNIX_TIMESTAMP()) ON DUPLICATE KEY UPDATE status = ?, timestamp = UNIX_TIMESTAMP()"))
			{
				$stmt->bind_param("iss", $this->guideid, $j, $j);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
		}
		
		return true;
	}
	
	public function JSON()
	{
		if ($this->auth && is_array($this->tasks))
			return json_encode($this->tasks);
		else return "[]";
	}
	
	function CheckCompletion(){ return true; }
}

class VenueOnboardingGuide extends Guide
{
	// guideName = "venueOnboarding"
	
	public $tasks = array(
		array(
			"taskID" => 0,
			"taskTitle" => "---Venue has been created---",
			"taskDesc" => "Give some credit for signing up and creating the basic profile at /create-venue",
			"taskWeight" => 6,
			"taskDone" => 2
		), 
		array(
			"taskID" => 1,
			"taskTitle" => "What To Do Next",
			"taskDesc" => "Use this step-by-step guide to get your venue configured for your staff and your customers to book events online. If you would like more assistance please feel free to email us at <a href='mailto:support@invitebig.com'>support@invitebig.com</a>.",
			"taskWeight" => 0,
			"taskDone" => -1
		), 
		array(
			"taskID" => 2,
			"taskTitle" => "Upload Images of Your Venue",
			"taskDesc" => "Improve your visibility on InviteBIG by <a href='#' data-page='/dashboard#profile' data-target='#venuePics'>adding your logo and profile images, and at least one additional image</a>.  For the best experience your images should be at least 720x480 pixels, and your profile header image should be at least 1920x540 pixels.",
			"taskWeight" => 5,
			"taskDone" => 0
		), 
		array(
			"taskID" => 3,
			"taskTitle" => "Enable or Disable Functionality",
			"taskDesc" => "Let us know how you would like to use InviteBIG for your venue. Use <a href='#' data-page='/dashboard#profile' data-target='#venueCreatorDetailsPane'>the settings tab</a> to turn on or off things like Food & Drink Menus, Personnel, Questionnaires, and Promo Codes. If you want to leave all of this functionality disabled, <a class='markComplete' href='#'>mark this step complete</a>.",
			"taskWeight" => 1,
			"taskDone" => 0
		), 
		array(
			"taskID" => 4,
			"taskTitle" => "Grant Your Team Access",
			"taskDesc" => "<a href='#' data-page='/dashboard#profile' data-target='#rowPerms'>Add email addresses for your team's accounts</a> so that you can grant them permissions to view or manage your venue. You can also configure each email address to receive email notifications. If you do not want to grant anyone rights, <a class='markComplete' href='#'>mark this step complete</a>.",
			"taskWeight" => 1,
			"taskDone" => 0
		), 
		array(
			"taskID" => 5,
			"taskTitle" => "Edit Deposit and Refund Policies",
			"taskDesc" => "Review and edit our default <a href='#' data-page='/dashboard#profile' data-target='div.deposits,div.refunds'>deposit and refund policies</a>, or add your own. Deposit and Refund policies define the rules for how much deposit is required, in which situations, and how much money the customer gets back if they cancel their booking. Every price on InviteBIG has an associated Deposit and Refund policy which you can specify. If you want to use the default policies, <a class='markComplete' href='#'>mark this step complete</a>.",
			"taskWeight" => 2,
			"taskDone" => 0
		), 
		array(
			"taskID" => 6,
			"taskTitle" => "Create Your Venue's Addons",
			"taskDesc" => "<a href='#' data-page='/dashboard#profile' data-target='div.addons'>Add your venue's first Addon</a> to allow your customers to request items, such as furniture, sound & lighting, decor, and more for their bookings. After your addons are created you can make them available to any room or resource.",
			"taskWeight" => 5,
			"taskDone" => 0
		), 
		array(
			"taskID" => 7,
			"taskTitle" => "Add Your Spaces and Resources",
			"taskDesc" => "Review and edit your <a href='#' data-page='/dashboard#profile' data-target='div.resources'>Spaces and Resources</a> to allow your customers to book meeting rooms, desks, classrooms, vip booths, bowling lanes, pool tables and any other item within your venue that is bookable by the hour or by timeslot. Spaces can have spaces and resources within them, and when the parent space is booked then the children may be included in the cost of the parent.",
			"taskWeight" => 5,
			"taskDone" => 0
		), 
		array(
			"taskID" => 8,
			"taskTitle" => "Create Food & Drink Menus and Items",
			"taskDesc" => "Use the <a href='#' data-page='/dashboard#profile' data-target='#venueCreatorFoodPane'>Food & Drink tab</a> to create menus, and then add items to your menus. For each menu you will define Deposit and Refund policies in addition to day and time availability, and these will apply to all items on that menu. Menu Item pictures should be at least 300x200 pixels. Menus can be <a href='#' data-page='/dashboard#profile' data-target='#venueCreatorDetailsPane'>turned off</a>",
			"taskWeight" => 1,
			"taskDone" => 0
		), 
		array(
			"taskID" => 9,
			"taskTitle" => "Add Personnel and Staff For Hire",
			"taskDesc" => "If you have any personnel that can be hired for an event then <a href='#' data-page='/dashboard#profile' data-target='#venueCreatorPersonnelPane'>add them to your profile</a>. Personnel are billed by the hour, with minimums and maximums, and you can make personnel required based on the number of guests attending the event. If you want to require 1 security guard per 50 guests, you can do that. Personnel can be <a href='#' data-page='/dashboard#profile' data-target='#venueCreatorDetailsPane'>turned off</a>.",
			"taskWeight" => 1,
			"taskDone" => 0
		), 
		array(
			"taskID" => 10,
			"taskTitle" => "Build Your Questionnaire",
			"taskDesc" => "<a href='#' data-page='/dashboard#profile' data-target='#venueCreatorQuestionnairePane'>The questionnaire builder</a> will let you create questions to ask about each booking. You can configure each question to be required, or to only be asked for certain resources, addons, menus, and personnel. The order of the questions can be specified as well, and questionnaires can be <a href='#' data-page='/dashboard#profile' data-target='#venueCreatorDetailsPane'>turned off</a>",
			"taskWeight" => 1,
			"taskDone" => 0
		), 
		array(
			"taskID" => 11,
			"taskTitle" => "Create and Configure Promo Codes",
			"taskDesc" => "Promo codes are a powerful tool for increasing sales and creating special discounts. You can <a href='#' data-page='/dashboard#profile' data-target='#venueCreatorPromosPane'>create promo codes</a> which are only valid if the specified conditions are met. Promo codes can be made to auto-apply to invoices or require the code, they can be scheduled, with inventory, for select resources, exceeding a certain price, combined, expired, and more. They can also be <a href='#' data-page='/dashboard#profile' data-target='#venueCreatorDetailsPane'>turned off</a>.",
			"taskWeight" => 1,
			"taskDone" => 0
		), 
		array(
			"taskID" => 12,
			"taskTitle" => "Test Your Venue's Booking Process",
			"taskDesc" => "<a href='#' data-page='/reserve' data-target='#'>Complete your first booking</a> to see what your team and/or your customers will see. Tune your venue's configuration until it looks right and you are happy with your venue's new booking process. If you have any questions, feel free to <a href='mailto:support@invitebig.com'>send us an email</a>.",
			"taskWeight" => 2,
			"taskDone" => 0
		), 
		array(
			"taskID" => 13,
			"taskTitle" => "Ready to Publish Your Venue?",
			"taskDesc" => "Once your venue is configured and you are ready to go live, go to <a href='#' data-page='/dashboard#profile' data-target='#venueCreatorApprovePane'>submit for approval</a> and ask our team to review your venue's profile. We will make sure that your profile looks sensible and then we will activate your venue.",
			"taskWeight" => 2,
			"taskDone" => 0
		), 
		array(
			"taskID" => 14,
			"taskTitle" => "Integrating Booking Process into Your Website",
			"taskDesc" => "Drive traffic and conversions by sharing your new booking process with your customers on your website and on your social media accounts. See our <a href='#' data-page='/dashboard#integrations' data-target='#'>integrations documentation</a> to learn how to create a link to your new booking process, how to integrate with an embedded iframe, and how to use our popup iframe. If you have already done this, <a class='markComplete' href='#'>click here to mark this step complete</a>.",
			"taskWeight" => 2,
			"taskDone" => 0
		)
	);
	
	public function CheckCompletion()
	{
		if ($this->isDone || !$this->auth)
			return false;
		
		require_once("php/class/venue.php");
		$venue = new Venue($this->venueid);
		
		// taskDone = 0   <- auto-detected as incomplete
		// taskDone = 1   <- auto-detected as complete
		// taskDone = 2   <- manually marked as complete with MarkComplete()
		
		for ($i=0; $i<count($this->tasks); $i++)
		{
			$task = &$this->tasks[$i];
			if ($task['taskDone'] == 2) continue;
			
			switch ($task['taskID'])
			{
				case "2":
					if (count($venue->pictures) > 2 && strpos($venue->pictures[0]->filename,"placeholder-") !== 0
						&& strpos($venue->pictures[1]->filename,"placeholder-") !== 0 && strpos($venue->pictures[2]->filename,"placeholder-") !== 0)
						$task['taskDone'] = 1;
					else $task['taskDone'] = 0;
					break;
					
				case "3":
					if ($venue->functionality['menus'] == true || $venue->functionality['personnel'] == true ||
						$venue->functionality['questions'] == true || $venue->functionality['promos'] == true)
						$task['taskDone'] = 1;
					break;
				
				case "4":
					if (count($venue->venueRights) > 1)
						$task['taskDone'] = 1;
					break;
				
				case "5":
					if (count($venue->refundPolicies) > 1 || count($venue->depositPolicies) > 1)
						$task['taskDone'] = 1;
					break;
					
				case "6":
					if (count($venue->addons) > 0)
						$task['taskDone'] = 1;
					else $task['taskDone'] = 0;
					break;
					
				case "7":
					if (count($venue->resources) > 1)
						$task['taskDone'] = 1;
					else $task['taskDone'] = 0;
					break;
					
				case "8":
					if ($venue->functionality['menus'] == 0 || count($venue->menus) > 0)
						$task['taskDone'] = 1;
					else $task['taskDone'] = 0;
					break;
					
				case "9":
					if ($venue->functionality['personnel'] == 0 || count($venue->personnel) > 0)
						$task['taskDone'] = 1;
					else $task['taskDone'] = 0;
					break;
					
				case "10":
					if ($venue->functionality['questions'] == 0 || count($venue->questions) > 0)
						$task['taskDone'] = 1;
					else $task['taskDone'] = 0;
					break;
					
				case "11":
					if ($venue->functionality['promos'] == 0 || count($venue->promos) > 0)
						$task['taskDone'] = 1;
					else $task['taskDone'] = 0;
					break;
					
				case "12":
					if ($venue->CountBookings() > 0)
						$task['taskDone'] = 1;
					else $task['taskDone'] = 0;
					break;
				
				case "13":
					if ($venue->status == "active")
						$task['taskDone'] = 1;
					else $task['taskDone'] = 0;
					break;
			}
		}
		
		$done = 0;
		$total = 0;
		for ($i=0; $i<count($this->tasks); $i++)
		{
			$total += $this->tasks[$i]['taskWeight'];
			if ($this->tasks[$i]['taskDone'] != 0)
				$done += $this->tasks[$i]['taskWeight'];
		}
		
		$this->oneLiner = "<b>Your account is only " . floor(($done / $total) * 100) . "% complete.</b> The more you set up, the better experience you and your customers will have. <a href='#' class='guide-widget-close-small'>Learn more now.</a>";
	}
}

?>