<?php

function SendVerificationEmail($vemail)
{	
	$name = "";
	$code = "";
	$d = new DateTime();
	$date = $d->format("g:i A T M j, Y");
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT firstname,code,timestamp,timezone FROM users WHERE email = ?"))
	{
		$stmt->bind_param("s", $vemail);
		$stmt->execute();
		$stmt->bind_result($fname,$rcode,$time,$tz);
		if($stmt->fetch())
		{
			$code = $rcode;
			$name = $fname;
			$d->setTimestamp($time);
			$d->setTimezone(new DateTimeZone($tz));
			$date = $d->format("g:i A T M j, Y");
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$to_data = array( array("email"=>$vemail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$vemail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$name),array("name"=>"USERVERIFICATIONLINK","content"=>"/verify/".$code),array("name"=>"REGISTRATIONDATE","content"=>$date))) );
	SendMandrillEmail('new-account-verification-email',null,$to_data,$to_vars,null,true,true,true,array("new-user-email-verification"));
	
	
	/*$to_data = array( array("email"=>$vemail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$vemail) );
	SendMandrillEmail('new-user-email-list',null,$to_data,$to_vars,null,true,true,true,array("new-user-email-list"));*/
}

function SendAutoRegisterEmail($email,$firstname,$password)
{		
	$to_data = array( array("email"=>$email,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$email,"vars"=>array(array("name"=>"USEREMAIL","content"=>$email),array("name"=>"USERFIRSTNAME","content"=>$firstname),array("name"=>"PASSWORD","content"=>$password))) );
	SendMandrillEmail('autoregister-email',null,$to_data,$to_vars,null,true,true,true,array("autoregister-email"));
}

function SendPasswordResetMessage($email,$pwd)
{	
	$name = "";
	$d = new DateTime();
	$date = $d->format("g:i A T M j, Y");
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT firstname, timezone FROM users WHERE email = ?"))
	{
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->bind_result($fname,$tz);
		if($stmt->fetch())
		{
			$d->setTimezone(new DateTimeZone($tz));
			$date = $d->format("g:i A T M j, Y");
			$name = $fname;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$to_data = array( array("email"=>$email,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$email,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$name),array("name"=>"NEWPASSWORD","content"=>$pwd),array("name"=>"RESETDATE","content"=>$date))) );
	SendMandrillEmail('password-reset',null,$to_data,$to_vars,null,true,true,true,array("password-reset"));
}

function SendContactMessage($email, $subject, $message)
{
	$to_data = array( array("email"=>"support@invitebig.com","type"=>"to") );
	$to_vars = array( array("rcpt"=>"support@invitebig.com","vars"=>array(array("name"=>"USERMESSAGE","content"=>$message))) );
	$from_data = array("subject"=>$subject,"email"=>$email,"name"=>$email);
	SendMandrillEmail('invitebig-contact-form-message',$from_data,$to_data,$to_vars,null,true,true,true,array("invitebig-contact-form-message"));
	return "success";
}

function SendPaymentReceivedMessage($bid, $amount)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingstatus = "";
	$bookingtotal = "";
	$amountpaid = "";
	$contactname = "";
	$contactemail = "";
	$useremail = "";
	$userfirstname = "";
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, i.contact_name, i.contact_email, b.status, v.name, v.venueid, v.timezone, u.email, u.firstname, u.lastname, total_cost, v.currency from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$cname,$cemail,$stat,$vname,$vi,$tz,$uemail,$ufname,$ulname,$cost,$cur);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			
			$username = $ufname." ".$ulname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$bookingdate = $d->format("g:i A T M j, Y");
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingstatus = $stat;
			$bookingtotal = $cost;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
			$userfirstname = $ufname;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking AS b LEFT JOIN booking_payments AS bp ON bp.bookingid = b.bookingid LEFT JOIN payments AS p ON p.paymentid = bp.paymentid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($paid);
		if($stmt->fetch())
		{
			$amountpaid = $paid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
	array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
	array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
	array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),
	array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency)),
	array("name"=>"PAYMENTAMOUNT","content"=>FormatCurrency($amount,$currency)),
	array("name"=>"BOOKINGDETAILSLINK","content"=>"/booking/".$bid))));
	SendMandrillEmail('payment-received',null,$to_data,$to_vars,null,true,true,true,array("payment-received"));
	
	} catch (Exception $e) { }
}

function SendNewBookingDepositRequiredMessage($bid)
{
	try {
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingstatus = "";
	$bookingtotal = "";
	$depositdue = "";
	$useremail = "";
	$userfirstname = "";
	$venue_email_list = array();
	$fee = 0.39;
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, i.contact_name, i.contact_email, b.status, v.name, v.venueid, v.timezone, u.email, u.firstname, u.lastname, total_deposit, total_cost, v.bookingfee, v.currency from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$cname,$cemail,$stat,$vname,$vi,$tz,$uemail,$ufname,$ulname,$deposit,$cost,$bf,$cur);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			
			$username = $ufname." ".$ulname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$bookingdate = $d->format("g:i A T M j, Y");
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingstatus = $stat;
			$bookingtotal = $cost;
			$depositdue = $deposit;
			$useremail = $uemail;
			$userfirstname = $ufname;
			$fee = $bf;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$venue_email_list = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT email FROM venues_rights WHERE ((role & 4 OR role > 15) AND receiveEmails > 0) AND venueid = ?"))
	{
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$stmt->bind_result($email);
		while($stmt->fetch())
		{
			if ($email != null)
				$venue_email_list[] = $email;
		}
		$GLOBALS['db']->CloseConn();
	}
		
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
	array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
	array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
	array("name"=>"DEPOSITDUE","content"=>FormatCurrency($depositdue,$currency)),array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)) )));
	SendMandrillEmail('new-booking-deposit-required',null,$to_data,$to_vars,null,true,true,true,array("new-booking-deposit-required"));
	
	} catch (Exception $e) { }
}

function SendBookingApprovalNeededMessage($bid)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingstatus = "";
	$bookingtotal = "";
	$amountpaid = "";
	$autoapprove = "no";
	$contactname = "";
	$contactemail = "";
	$useremail = "";
	$userfirstname = "";
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, i.contact_name, i.contact_email, b.status, v.name, v.venueid, v.timezone, u.email, u.firstname, u.lastname, total_cost, v.currency from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$cname,$cemail,$stat,$vname,$vi,$tz,$uemail,$ufname,$ulname,$cost,$cur);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			
			$username = $ufname." ".$ulname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$bookingdate = $d->format("g:i A T M j, Y");
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingstatus = $stat;
			$bookingtotal = $cost;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
			$userfirstname = $ufname;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking AS b LEFT JOIN booking_payments AS bp ON bp.bookingid = b.bookingid LEFT JOIN payments AS p ON p.paymentid = bp.paymentid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($paid);
		if($stmt->fetch())
		{
			$amountpaid = $paid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT COUNT(b.resourceid) FROM booking_resources AS b LEFT JOIN resources AS r ON r.resourceid = b.resourceid WHERE b.bookingid = ? AND b.cost IS NOT NULL AND r.autoapprove != 1"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($cnt);
		if($stmt->fetch())
		{
			if ($cnt == 0)
				$autoapprove = "yes";
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$venue_email_list = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT email FROM venues_rights WHERE ((role & 4 OR role > 15) AND receiveEmails > 0) AND venueid = ?"))
	{
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$stmt->bind_result($email);
		while($stmt->fetch())
		{
			if ($email != null)
				$venue_email_list[] = $email;
		}
		$GLOBALS['db']->CloseConn();
	}
	$to_data = array();
	$to_vars = array();
	foreach ($venue_email_list as $e)
	{
		$to_data[] = array("email"=>$e,"type"=>"to");
		$to_vars[] = array("rcpt"=>$e,"vars"=>array(array("name"=>"USERNAME","content"=>$username),
		array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
		array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
		array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"AUTOAPPROVE","content"=>$autoapprove),
		array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency))));
	}
	SendMandrillEmail('booking-approval-needed',null,$to_data,$to_vars,null,true,true,true,array("booking-approval-needed"));
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
	array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
	array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
	array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"AUTOAPPROVE","content"=>$autoapprove),
	array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency)),
	array("name"=>"BOOKINGDETAILSLINK","content"=>"/booking/".$bid))));
	if (strlen($contactemail) > 0 && $contactemail != $useremail)
	{
		$to_data[] = array("email"=>$contactemail,"type"=>"to");
		$to_vars[] = array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$contactname),
		array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
		array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
		array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"AUTOAPPROVE","content"=>$autoapprove),
		array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency)),
		array("name"=>"BOOKINGDETAILSLINK","content"=>"/booking/".$bid)));
	}
	SendMandrillEmail('booking-approval-needed-to-user',null,$to_data,$to_vars,null,true,true,true,array("booking-approval-needed-to-user"));
	
	} catch (Exception $e) { }
}

function SendBookingApprovedMessage($bid)
{
	try {
	$firstname = "";
	$venuename = "";
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingstatus = "";
	$contactname = "";
	$contactemail = "";
	$useremail = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, b.status, v.name, v.timezone, u.firstname, u.email, i.contact_name, i.contact_email from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$stat,$vname,$tz,$uname,$uemail,$cname,$cemail);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			
			$firstname = $uname;
			$venuename = Sanitize($vname);
			$bookingdate = $d->format("g:i A T M j, Y");
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingstatus = $stat;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$bookingstatus = "Pending Payment";
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$firstname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"DASHBOARDLINK","content"=>"/dashboard#bookings"))));
	if (strlen($contactemail) > 0 && $contactemail != $useremail)
	{
		$to_data[] = array("email"=>$contactemail,"type"=>"to");
		$to_vars[] = array("rcpt"=>$contactemail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$contactname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"DASHBOARDLINK","content"=>"/dashboard#bookings")));
	}
	SendMandrillEmail('booking-approved',null,$to_data,$to_vars,null,true,true,true,array("booking-approved"));
	
	} catch (Exception $e) { }
}

function SendBookingDeniedMessage($bid,$reason)
{
	try {
	$firstname = "";
	$venuename = "";
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$contactname = "";
	$contactemail = "";
	$useremail = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, b.status, v.name, v.timezone, u.firstname, u.email, i.contact_name, i.contact_email from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$stat,$vname,$tz,$uname,$uemail,$cname,$cemail);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			
			$firstname = $uname;
			$venuename = Sanitize($vname);
			$bookingdate = $d->format("g:i A T M j, Y");
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$firstname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"DASHBOARDLINK","content"=>"/dashboard#bookings"),array("name"=>"REASON","content"=>$reason))));
	if (strlen($contactemail) > 0 && $contactemail != $useremail)
	{
		$to_data[] = array("email"=>$contactemail,"type"=>"to");
		$to_vars[] = array("rcpt"=>$contactemail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$contactname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"DASHBOARDLINK","content"=>"/dashboard#bookings"),array("name"=>"REASON","content"=>$reason)));
	}
	SendMandrillEmail('booking-denied',null,$to_data,$to_vars,null,true,true,true,array("booking-denied"));
	
	} catch (Exception $e) { }
}

function SendBookingCancelledByVenueMessage($bid,$reason)
{
	try {
	$firstname = "";
	$venuename = "";
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$contactname = "";
	$contactemail = "";
	$useremail = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, b.status, v.name, v.timezone, u.firstname, u.email, i.contact_name, i.contact_email from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$stat,$vname,$tz,$uname,$uemail,$cname,$cemail);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			
			$firstname = $uname;
			$venuename = Sanitize($vname);
			$bookingdate = $d->format("g:i A T M j, Y");
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$firstname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"DASHBOARDLINK","content"=>"/dashboard#bookings"),array("name"=>"REASON","content"=>$reason))));
	if (strlen($contactemail) > 0 && $contactemail != $useremail)
	{
		$to_data[] = array("email"=>$contactemail,"type"=>"to");
		$to_vars[] = array("rcpt"=>$contactemail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$contactname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"DASHBOARDLINK","content"=>"/dashboard#bookings"),array("name"=>"REASON","content"=>$reason)));
	}
	SendMandrillEmail('booking-cancelled-by-venue',null,$to_data,$to_vars,null,true,true,true,array("booking-cancelled-by-venue"));
	
	} catch (Exception $e) { }
}

function SendBookingCancelledByUserMessage($bid,$reason)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingtotal = "";
	$depositretained = "";
	$useremail = "";
	$contactname = "";
	$contactemail = "";
	$userfirstname = "";
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, v.name, v.venueid, v.timezone, u.firstname, u.lastname, u.email, i.contact_name, i.contact_email, b.total_cost,v.currency from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$vname,$vi,$tz,$ufname,$ulname,$uemail,$cname,$cemail,$cost,$cur);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			
			$userfirstname = $ufname;
			$username = $ufname." ".$ulname;
			$bookingdate = $d->format("g:i A T M j, Y");
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingtotal = $cost;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking AS b LEFT JOIN booking_payments AS bp ON bp.bookingid = b.bookingid LEFT JOIN payments AS p ON p.paymentid = bp.paymentid WHERE (p.status = 'processed' OR p.status = 'refunded') AND b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($sum);
		if($stmt->fetch())
		{
			$depositretained = $sum;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$venue_email_list = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT email FROM venues_rights WHERE ((role & 4 OR role > 15) AND receiveEmails > 0) AND venueid = ?"))
	{
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$stmt->bind_result($email);
		while($stmt->fetch())
		{
			if ($email != null)
				$venue_email_list[] = $email;
		}
		$GLOBALS['db']->CloseConn();
	}
	$from_data = array("subject"=>"Booking for ".$bookingdate." cancelled by user","email"=>"support@invitebig.com","name"=>"InviteBIG");
	$to_data = array();
	$to_vars = array();
	foreach ($venue_email_list as $e)
	{
		$to_data[] = array("email"=>$e,"type"=>"to");
		$to_vars[] = array("rcpt"=>$e,"vars"=>array(array("name"=>"USERNAME","content"=>$username),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"REASON","content"=>$reason),array("name"=>"DEPOSITRETAINED","content"=>FormatCurrency($depositretained,$currency))));
	}
	SendMandrillEmail('booking-cancelled-by-user-to-venue',$from_data,$to_data,$to_vars,null,true,true,true,array("booking-cancelled-by-user-to-venue"));
	
	$from_data = array("subject"=>"Booking for ".$bookingdate." cancelled","email"=>"support@invitebig.com","name"=>"InviteBIG");
	$to_data = array(array("email"=>$useremail,"type"=>"to"));
	$to_vars = array(array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"REASON","content"=>$reason),array("name"=>"DEPOSITRETAINED","content"=>FormatCurrency($depositretained,$currency)))));
	if (strlen($contactemail) > 0 && $contactemail != $useremail)
	{
		$to_data[] = array("email"=>$contactemail,"type"=>"to");
		$to_vars[] = array("rcpt"=>$contactemail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$contactname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"REASON","content"=>$reason),array("name"=>"DEPOSITRETAINED","content"=>FormatCurrency($depositretained,$currency))));
	}
	
	SendMandrillEmail('booking-cancelled-by-user-to-user',$from_data,$to_data,$to_vars,null,true,true,true,array("booking-cancelled-by-user-to-user"));
	
	} catch (Exception $e) { }
}

function SendBookingCancelledPastDueMessage($bid,$reason)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingtotal = "";
	$depositretained = "";
	$useremail = "";
	$contactname = "";
	$contactemail = "";
	$userfirstname = "";
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, v.name, v.venueid, v.timezone, u.firstname, u.lastname, u.email, i.contact_name, i.contact_email, b.total_cost,v.currency from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$vname,$vi,$tz,$ufname,$ulname,$uemail,$cname,$cemail,$cost,$cur);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			
			$userfirstname = $ufname;
			$username = $ufname." ".$ulname;
			$bookingdate = $d->format("g:i A T M j, Y");
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingtotal = $cost;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking AS b LEFT JOIN booking_payments AS bp ON bp.bookingid = b.bookingid LEFT JOIN payments AS p ON p.paymentid = bp.paymentid WHERE (p.status = 'processed' OR p.status = 'refunded') AND b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($sum);
		if($stmt->fetch())
		{
			$depositretained = $sum;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$venue_email_list = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT email FROM venues_rights WHERE ((role & 4 OR role > 15) AND receiveEmails > 0) AND venueid = ?"))
	{
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$stmt->bind_result($email);
		while($stmt->fetch())
		{
			if ($email != null)
				$venue_email_list[] = $email;
		}
		$GLOBALS['db']->CloseConn();
	}
	$from_data = array("subject"=>"Booking for ".$bookingdate." cancelled by venue","email"=>"support@invitebig.com","name"=>"InviteBIG");
	$to_data = array();
	$to_vars = array();
	foreach ($venue_email_list as $e)
	{
		$to_data[] = array("email"=>$e,"type"=>"to");
		$to_vars[] = array("rcpt"=>$e,"vars"=>array(array("name"=>"USERNAME","content"=>$username),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"REASON","content"=>$reason),array("name"=>"DEPOSITRETAINED","content"=>FormatCurrency($depositretained,$currency))));
	}
	SendMandrillEmail('booking-cancelled-past-due-to-user',$from_data,$to_data,$to_vars,null,true,true,true,array("booking-cancelled-past-due-to-user"));
	
	$from_data = array("subject"=>"Booking for ".$bookingdate." cancelled","email"=>"support@invitebig.com","name"=>"InviteBIG");
	$to_data = array(array("email"=>$useremail,"type"=>"to"));
	$to_vars = array(array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"REASON","content"=>$reason),array("name"=>"DEPOSITRETAINED","content"=>FormatCurrency($depositretained,$currency)))));
	if (strlen($contactemail) > 0 && $contactemail != $useremail)
	{
		$to_data[] = array("email"=>$contactemail,"type"=>"to");
		$to_vars[] = array("rcpt"=>$contactemail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$contactname),array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"REASON","content"=>$reason),array("name"=>"DEPOSITRETAINED","content"=>FormatCurrency($depositretained,$currency))));
	}
	
	SendMandrillEmail('booking-cancelled-past-due-to-user',$from_data,$to_data,$to_vars,null,true,true,true,array("booking-cancelled-past-due-to-user"));
	
	} catch (Exception $e) { }
}

function SendUserNewPMMessage($mid)
{
	try {
	$useremail = "";
	$userfirstname = "";
	$venuename = "";
	$title = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT u.firstname, u.email, v.name, m.title FROM messages AS m LEFT JOIN users AS u ON u.userid = m.userid LEFT JOIN venues AS v ON v.venueid = m.venueid WHERE m.convoid = ?"))
	{
		$stmt->bind_param("i", $mid);
		$stmt->execute();
		$stmt->bind_result($fname,$uemail,$vname,$t);
		if($stmt->fetch())
		{
			$useremail = $uemail;
			$userfirstname = $fname;
			$venuename = Sanitize($vname);
			$title = $t;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"FIRSTNAME","content"=>$userfirstname),array("name"=>"PMSENDER","content"=>$venuename),array("name"=>"MESSAGETITLE","content"=>$title))) );
	SendMandrillEmail('new-pm-notification',null,$to_data,$to_vars,null,true,true,true,array("new-pm-notification"));
	
	} catch (Exception $e) { }
}

function SendVenueNewPMMessage($mid)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$title = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT CONCAT(u.firstname,' ',u.lastname), v.name, v.venueid, m.title FROM messages AS m LEFT JOIN users AS u ON u.userid = m.userid LEFT JOIN venues AS v ON v.venueid = m.venueid WHERE m.convoid = ?"))
	{
		$stmt->bind_param("i", $mid);
		$stmt->execute();
		$stmt->bind_result($uname,$vname,$vi,$t);
		if($stmt->fetch())
		{
			$username = $uname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$title = $t;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$venue_email_list = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT email FROM venues_rights WHERE ((role & 4 OR role > 15) AND receiveEmails > 0) AND venueid = ?"))
	{
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$stmt->bind_result($email);
		while($stmt->fetch())
		{
			if ($email != null)
				$venue_email_list[] = $email;
		}
		$GLOBALS['db']->CloseConn();
	}
	$to_data = array();
	$to_vars = array();
	foreach ($venue_email_list as $e)
	{
		$to_data[] = array("email"=>$e,"type"=>"to");
		$to_vars[] = array("rcpt"=>$e,"vars"=>array(array("name"=>"FIRSTNAME","content"=>$venuename),array("name"=>"PMSENDER","content"=>$username),array("name"=>"MESSAGETITLE","content"=>$title)));
	}
	SendMandrillEmail('new-pm-notification',null,$to_data,$to_vars,null,true,true,true,array("new-pm-notification"));
	
	} catch (Exception $e) { }
}

function SendUserNewMessageMessage($bid,$message)
{
	try {
	$useremail = "";
	$userfirstname = "";
	$venuename = "";
	$start = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT u.firstname, u.email, v.name, v.timezone, b.start FROM booking AS b LEFT JOIN users AS u ON u.userid = b.userid LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($fname,$uemail,$vname,$tz,$s);
		if($stmt->fetch())
		{
			$useremail = $uemail;
			$userfirstname = $fname;
			$venuename = Sanitize($vname);
			
			$d = new DateTime();
			$d->setTimestamp($s);
			$d->setTimezone(new DateTimeZone($tz));
			$start = $d->format("g:i A T M j, Y");
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
	array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$start),
	array("name"=>"MESSAGE","content"=>$message))) );
	SendMandrillEmail('new-booking-message-to-user',null,$to_data,$to_vars,null,true,true,true,array("new-booking-message-to-user"));
	
	} catch (Exception $e) { }
}

function SendVenueNewMessageMessage($bid,$message)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$start = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT CONCAT(u.firstname,' ',u.lastname), v.name, v.venueid, v.timezone, b.start FROM booking AS b LEFT JOIN users AS u ON u.userid = b.userid LEFT JOIN venues AS v ON v.venueid = b.venueid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($fname,$vname,$vi,$tz,$s);
		if($stmt->fetch())
		{
			$username = $fname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			
			$d = new DateTime();
			$d->setTimestamp($s);
			$d->setTimezone(new DateTimeZone($tz));
			$start = $d->format("g:i A T M j, Y");
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$venue_email_list = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT email FROM venues_rights WHERE ((role & 4 OR role > 15) AND receiveEmails > 0) AND venueid = ?"))
	{
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$stmt->bind_result($email);
		while($stmt->fetch())
		{
			if ($email != null)
				$venue_email_list[] = $email;
		}
		$GLOBALS['db']->CloseConn();
	}
	$to_data = array();
	$to_vars = array();
	foreach ($venue_email_list as $e)
	{
		$to_data[] = array("email"=>$e,"type"=>"to");
		$to_vars[] = array("rcpt"=>$e,"vars"=>array(array("name"=>"USERNAME","content"=>$username),
			array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$start),
			array("name"=>"MESSAGE","content"=>$message)));
	}
	SendMandrillEmail('new-booking-message-to-venue',null,$to_data,$to_vars,null,true,true,true,array("new-booking-message-to-venue"));
	
	} catch (Exception $e) { }
}

function SendApprovalWarningMessage($bid)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingstatus = "";
	$bookingtotal = "";
	$amountpaid = "";
	$autoapprove = "no";
	$contactname = "";
	$contactemail = "";
	$useremail = "";
	$userfirstname = "";
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, i.contact_name, i.contact_email, b.status, v.name, v.venueid, v.timezone, u.email, u.firstname, u.lastname, total_cost, v.currency from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$cname,$cemail,$stat,$vname,$vi,$tz,$uemail,$ufname,$ulname,$cost,$cur);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			
			$username = $ufname." ".$ulname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$bookingdate = $d->format("g:i A T M j, Y");
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingstatus = $stat;
			$bookingtotal = $cost;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
			$userfirstname = $ufname;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking AS b LEFT JOIN booking_payments AS bp ON bp.bookingid = b.bookingid LEFT JOIN payments AS p ON p.paymentid = bp.paymentid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($paid);
		if($stmt->fetch())
		{
			$amountpaid = $paid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT COUNT(b.resourceid) FROM booking_resources AS b LEFT JOIN resources AS r ON r.resourceid = b.resourceid WHERE b.bookingid = ? AND b.cost IS NOT NULL AND r.autoapprove != 1"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($cnt);
		if($stmt->fetch())
		{
			if ($cnt == 0)
				$autoapprove = "yes";
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$venue_email_list = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT email FROM venues_rights WHERE ((role & 4 OR role > 15) AND receiveEmails > 0) AND venueid = ?"))
	{
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$stmt->bind_result($email);
		while($stmt->fetch())
		{
			if ($email != null)
				$venue_email_list[] = $email;
		}
		$GLOBALS['db']->CloseConn();
	}
	$to_data = array();
	$to_vars = array();
	foreach ($venue_email_list as $e)
	{
		$to_data[] = array("email"=>$e,"type"=>"to");
		$to_vars[] = array("rcpt"=>$e,"vars"=>array(
		array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"USERNAME","content"=>$username),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
		array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
		array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"AUTOAPPROVE","content"=>$autoapprove),
		array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency))));
	}
	SendMandrillEmail('booking-approval-warning',null,$to_data,$to_vars,null,true,true,true,array("booking-approval-warning"));
	
	} catch (Exception $e) { }
}

function SendFullPaymentWarningMessage($bid)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingstatus = "";
	$bookingtotal = "";
	$amountpaid = "";
	$autoapprove = "no";
	$contactname = "";
	$contactemail = "";
	$useremail = "";
	$userfirstname = "";
	$fulldue = "";
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, i.contact_name, i.contact_email, b.status, v.name, v.venueid, v.timezone, u.email, u.firstname, u.lastname, total_cost, full_due, v.currency from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$cname,$cemail,$stat,$vname,$vi,$tz,$uemail,$ufname,$ulname,$cost,$due,$cur);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			$bookingdate = $d->format("g:i A T M j, Y");
			
			$d = new DateTime();
			$d->setTimestamp($due);
			$d->setTimezone(new DateTimeZone($tz));
			$fulldue = $d->format("g:i A T M j, Y");
			
			$username = $ufname." ".$ulname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingstatus = $stat;
			$bookingtotal = $cost;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
			$userfirstname = $ufname;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking AS b LEFT JOIN booking_payments AS bp ON bp.bookingid = b.bookingid LEFT JOIN payments AS p ON p.paymentid = bp.paymentid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($paid);
		if($stmt->fetch())
		{
			$amountpaid = $paid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
	array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
	array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
	array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"PAYMENTDUE","content"=>$fulldue),
	array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency)),
	array("name"=>"PAYMENTLINK","content"=>"/booking/".$bid."/pay"))));
	if (strlen($contactemail) > 0 && $contactemail != $useremail)
	{
		$to_data[] = array("email"=>$contactemail,"type"=>"to");
		$to_vars[] = array("rcpt"=>$contactemail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$contactname),
			array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
			array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
			array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"PAYMENTDUE","content"=>$fulldue),
			array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency)),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency)),
			array("name"=>"PAYMENTLINK","content"=>"/booking/".$bid."/pay")));
	}
	SendMandrillEmail('booking-full-payment-due-warning',null,$to_data,$to_vars,null,true,true,true,array("booking-full-payment-due-warning"));
	
	} catch (Exception $e) { }
}

// No longer needed as of 9/29/2015
function SendDepositExpiredMessage($bid)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingstatus = "";
	$bookingtotal = "";
	$useremail = "";
	$userfirstname = "";
	$fulldue = "";
			
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, i.contact_name, i.contact_email, b.status, v.name, v.venueid, v.timezone, u.email, u.firstname, u.lastname, total_cost, full_due from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$cname,$cemail,$stat,$vname,$vi,$tz,$uemail,$ufname,$ulname,$cost,$due);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			$bookingdate = $d->format("g:i A T M j, Y");
			
			$d = new DateTime();
			$d->setTimestamp($due);
			$d->setTimezone(new DateTimeZone($tz));
			$fulldue = $d->format("g:i A T M j, Y");
			
			$username = $ufname." ".$ulname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingstatus = $stat;
			$bookingtotal = $cost;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
			$userfirstname = $ufname;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$bookingstatus = "Cancelled By User";
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
	array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
	array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc)
	)));
	
	SendMandrillEmail('deposit-expired',null,$to_data,$to_vars,null,true,true,true,array("deposit-expired"));
	} catch (Exception $e) { }
}

function SendApprovalExpiredMessage($bid)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingstatus = "";
	$bookingtotal = "";
	$autoapprove = "no";
	$contactname = "";
	$contactemail = "";
	$useremail = "";
	$userfirstname = "";
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, i.contact_name, i.contact_email, b.status, v.name, v.venueid, v.timezone, u.email, u.firstname, u.lastname, total_cost, full_due, v.currency from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$cname,$cemail,$stat,$vname,$vi,$tz,$uemail,$ufname,$ulname,$cost,$due,$cur);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			$bookingdate = $d->format("g:i A T M j, Y");
			
			$d = new DateTime();
			$d->setTimestamp($due);
			$d->setTimezone(new DateTimeZone($tz));
			$fulldue = $d->format("g:i A T M j, Y");
			
			$username = $ufname." ".$ulname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingstatus = $stat;
			$bookingtotal = $cost;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
			$userfirstname = $ufname;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$venue_email_list = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT email FROM venues_rights WHERE ((role & 4 OR role > 15) AND receiveEmails > 0) AND venueid = ?"))
	{
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$stmt->bind_result($email);
		while($stmt->fetch())
		{
			if ($email != null)
				$venue_email_list[] = $email;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$bookingstatus = "Cancelled By Venue";
	
	$to_data = array();
	$to_vars = array();
	foreach ($venue_email_list as $e)
	{
		$to_data[] = array("email"=>$e,"type"=>"to");
		$to_vars[] = array("rcpt"=>$e,"vars"=>array(array("name"=>"USERNAME","content"=>$username),
			array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
			array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
			array("name"=>"BOOKINGTOTAL","content"=>FormatCurrency($bookingtotal,$currency))));
	}
	SendMandrillEmail('approval-expired-to-venue',null,$to_data,$to_vars,null,true,true,true,array("approval-expired-to-venue"));
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
	array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
	array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc)
	)));
	if (strlen($contactemail) > 0 && $contactemail != $useremail)
	{
		$to_data[] = array("email"=>$contactemail,"type"=>"to");
		$to_vars[] = array("rcpt"=>$contactemail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$contactname),
			array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
			array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc)
			));
	}
	SendMandrillEmail('approval-expired-to-user',null,$to_data,$to_vars,null,true,true,true,array("approval-expired-to-user"));
	
	} catch (Exception $e) { }
}

function SendFullPaymentExpiredMessage($bid)
{
	try {
	$username = "";
	$venuename = "";
	$vid = null;
	$bookingdate = "";
	$bookingname = "";
	$bookingdesc = "";
	$bookingstatus = "";
	$bookingtotal = "";
	$amountpaid = "";
	$autoapprove = "no";
	$contactname = "";
	$contactemail = "";
	$useremail = "";
	$userfirstname = "";
	$fulldue = "";
	$depositretained = "";
	$currency = "";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT b.start, i.name, i.description, i.contact_name, i.contact_email, b.status, v.name, v.venueid, v.timezone, u.email, u.firstname, u.lastname, total_cost, full_due, v.currency from booking AS b LEFT JOIN booking_info AS i ON i.bookingid = b.bookingid LEFT JOIN venues AS v ON v.venueid = b.venueid LEFT JOIN users AS u ON u.userid = b.userid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($start,$bname,$bdesc,$cname,$cemail,$stat,$vname,$vi,$tz,$uemail,$ufname,$ulname,$cost,$due,$cur);
		if($stmt->fetch())
		{
			$d = new DateTime();
			$d->setTimestamp($start);
			$d->setTimezone(new DateTimeZone($tz));
			$bookingdate = $d->format("g:i A T M j, Y");
			
			$d = new DateTime();
			$d->setTimestamp($due);
			$d->setTimezone(new DateTimeZone($tz));
			$fulldue = $d->format("g:i A T M j, Y");
			
			$username = $ufname." ".$ulname;
			$venuename = Sanitize($vname);
			$vid = $vi;
			$bookingname = Sanitize($bname);
			$bookingdesc = Sanitize($bdesc);
			$bookingstatus = $stat;
			$bookingtotal = $cost;
			$contactname = Sanitize($cname);
			$contactemail = $cemail;
			$useremail = $uemail;
			$userfirstname = $ufname;
			$currency = $cur;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking AS b LEFT JOIN booking_payments AS bp ON bp.bookingid = b.bookingid LEFT JOIN payments AS p ON p.paymentid = bp.paymentid WHERE b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($paid);
		if($stmt->fetch())
		{
			$amountpaid = $paid;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT IFNULL(SUM(p.amount),0) FROM booking AS b LEFT JOIN booking_payments AS bp ON bp.bookingid = b.bookingid LEFT JOIN payments AS p ON p.paymentid = bp.paymentid WHERE (p.status = 'processed' OR p.status = 'refunded') AND b.bookingid = ?"))
	{
		$stmt->bind_param("i", $bid);
		$stmt->execute();
		$stmt->bind_result($sum);
		if($stmt->fetch())
		{
			$depositretained = $sum;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$venue_email_list = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT email FROM venues_rights WHERE ((role & 4 OR role > 15) AND receiveEmails > 0) AND venueid = ?"))
	{
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$stmt->bind_result($email);
		while($stmt->fetch())
		{
			if ($email != null)
				$venue_email_list[] = $email;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$bookingstatus = "Past Due";
	
	$to_data = array();
	$to_vars = array();
	foreach ($venue_email_list as $e)
	{
		$to_data[] = array("email"=>$e,"type"=>"to");
		$to_vars[] = array("rcpt"=>$e,"vars"=>array(array("name"=>"USERNAME","content"=>$username),
			array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
			array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
			array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"PAYMENTDUE","content"=>$fulldue),
			array("name"=>"BOOKINGTOTAL","content"=>$bookingtotal),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency)),
			array("name"=>"AMOUNTRETAINED","content"=>FormatCurrency($depositretained,$currency))));
	}
	SendMandrillEmail('full-payment-expired-to-venue',null,$to_data,$to_vars,null,true,true,true,array("full-payment-expired-to-venue"));
	
	$to_data = array( array("email"=>$useremail,"type"=>"to") );
	$to_vars = array( array("rcpt"=>$useremail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
	array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
	array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
	array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"PAYMENTDUE","content"=>$fulldue),
	array("name"=>"BOOKINGTOTAL","content"=>$bookingtotal),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency)),
	array("name"=>"AMOUNTRETAINED","content"=>FormatCurrency($depositretained,$currency)))));
	if (strlen($contactemail) > 0 && $contactemail != $useremail)
	{
		$to_data[] = array("email"=>$contactemail,"type"=>"to");
		$to_vars[] = array("rcpt"=>$contactemail,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$contactname),
			array("name"=>"VENUENAME","content"=>$venuename),array("name"=>"BOOKINGDATE","content"=>$bookingdate),
			array("name"=>"BOOKINGNAME","content"=>$bookingname),array("name"=>"BOOKINGDESCRIPTION","content"=>$bookingdesc),
			array("name"=>"BOOKINGSTATUS","content"=>$bookingstatus),array("name"=>"PAYMENTDUE","content"=>$fulldue),
			array("name"=>"BOOKINGTOTAL","content"=>$bookingtotal),array("name"=>"AMOUNTPAID","content"=>FormatCurrency($amountpaid,$currency)),
			array("name"=>"AMOUNTRETAINED","content"=>FormatCurrency($depositretained,$currency))));
	}
	SendMandrillEmail('full-payment-expired',null,$to_data,$to_vars,null,true,true,true,array("full-payment-expired"));
	
	} catch (Exception $e) { }
}

function SendVenueApprovalRequestedMessage($vid)
{
	try {
		$name = "";
		$email = "";
		$userfirstname = "";
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT v.name, u.firstname, u.email FROM venues AS v LEFT JOIN users AS u ON u.userid = v.userid WHERE v.venueid = ?"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($n, $fname, $e);
			while($stmt->fetch())
			{
				$name = Sanitize($n);
				$userfirstname = $fname;
				$email = $e;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$to_data = array( array("email"=>"support@invitebig.com","type"=>"to") );
		$to_vars = array( array("rcpt"=>"support@invitebig.com","vars"=>array(array("name"=>"VENUENAME","content"=>$name))) );
		$from_data = array("subject"=>"Venue approval requested - ".$name,"email"=>$email,"name"=>null);
		SendMandrillEmail('venue-approval-request',$from_data,$to_data,$to_vars,null,true,true,true,array("venue-approval-request"));
	} catch (Exception $e) { }
}

function SendVenueApprovedMessage($vid)
{
	try {
		$name = "";
		$email = "";
		$userfirstname = "";
		$url = "";
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT v.name, u.firstname, u.email, v.url FROM venues AS v LEFT JOIN users AS u ON u.userid = v.userid WHERE v.venueid = ?"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($n, $fname, $e, $u);
			while($stmt->fetch())
			{
				$name = Sanitize($n);
				$userfirstname = $fname;
				$email = $e;
				$url = $u;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$to_data = array( array("email"=>$email,"type"=>"to") );
		$to_vars = array( array("rcpt"=>$email,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
		array("name"=>"VENUENAME","content"=>$name),array("name"=>"PROFILELINK","content"=>"/venue/".$url))) );
		SendMandrillEmail('venue-approved',null,$to_data,$to_vars,null,true,true,true,array("venue-approved"));
	} catch (Exception $e) { }
}

function SendSubscriptionPaymentDueMessage($vid)
{
	try {
		$name = "";
		$email = "";
		$userfirstname = "";
		$url = "";
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT v.name, u.firstname, u.email, v.url FROM venues AS v LEFT JOIN users AS u ON u.userid = v.userid WHERE v.venueid = ?"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($n, $fname, $e, $u);
			while($stmt->fetch())
			{
				$name = Sanitize($n);
				$userfirstname = $fname;
				$email = $e;
				$url = $u;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$to_data = array( array("email"=>$email,"type"=>"to") );
		$to_vars = array( array("rcpt"=>$email,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
		array("name"=>"VENUENAME","content"=>$name),array("name"=>"PROFILELINK","content"=>"/venue/".$url))) );
		SendMandrillEmail('subscription-payment-due',null,$to_data,$to_vars,null,true,true,true,array("subscription-payment-due"));
	} catch (Exception $e) { }
}

function SendSubscriptionCancelledMessage($vid)
{
	try {
		$name = "";
		$email = "";
		$userfirstname = "";
		$url = "";
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT v.name, u.firstname, u.email, v.url FROM venues AS v LEFT JOIN users AS u ON u.userid = v.userid WHERE v.venueid = ?"))
		{
			$stmt->bind_param("i",$vid);
			$stmt->execute();
			$stmt->bind_result($n, $fname, $e, $u);
			while($stmt->fetch())
			{
				$name = Sanitize($n);
				$userfirstname = $fname;
				$email = $e;
				$url = $u;
			}
			$GLOBALS['db']->CloseConn();
		}
		
		$to_data = array( array("email"=>$email,"type"=>"to") );
		$to_vars = array( array("rcpt"=>$email,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$userfirstname),
		array("name"=>"VENUENAME","content"=>$name),array("name"=>"PROFILELINK","content"=>"/venue/".$url))) );
		SendMandrillEmail('subscription-plan-cancelled',null,$to_data,$to_vars,null,true,true,true,array("subscription-plan-cancelled"));
	} catch (Exception $e) { }
}

function SendWelcomeUserMessage($email, $name)
{
	try {		
		$to_data = array( array("email"=>$email,"type"=>"to") );
		$to_vars = array( array("rcpt"=>$email,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$name) )));
		SendMandrillEmail('welcome-user',null,$to_data,$to_vars,null,true,true,true,array("welcome-user"));
	} catch (Exception $e) { }
}

function SendWelcomeVenueMessage($email, $name)
{
	try {		
		$to_data = array( array("email"=>$email,"type"=>"to") );
		$to_vars = array( array("rcpt"=>$email,"vars"=>array(array("name"=>"USERFIRSTNAME","content"=>$name) )));
		SendMandrillEmail('welcome-venue',null,$to_data,$to_vars,null,true,true,true,array("welcome-venue"));
	} catch (Exception $e) { }
}

function SendMandrillEmail($template, $from_data, $user_to_data, $user_var_data, $vars, $track_clicks, $track_opens, $log_content, $tags)
{
	require_once("php/mandrill-api-php/Mandrill.php");
	try {
		
		for ($i = 0; $i < count($user_var_data); $i++)
		{
			for ($i2 = 0; $i2 < count($user_var_data[$i]['vars']); $i2++)
			{
				if ($user_var_data[$i]['vars'][$i2]['content'] == null)
					$user_var_data[$i]['vars'][$i2]['content'] = "&lt;none&gt;";
			}
		}
		
		$mandrill = new Mandrill('4rG3BSsTVNaqpA8Rjhk7MQ');
		$template_name = $template;
		/*$template_content = array(
			array(
				'name' => 'example name',
				'content' => 'example content'
			)
		);*/
		$template_content = null;
		$message = array(
			'html' => null,
			'text' => null,
			'subject' => (isset($from_data)?$from_data['subject']:null),
			'from_email' => (isset($from_data)?$from_data['email']:null),
			'from_name' => (isset($from_data)?$from_data['name']:null),
			'to' => $user_to_data,
			'headers' => null,
			'important' => false,
			'track_opens' => $track_clicks,
			'track_clicks' => $track_opens,
			'auto_text' => null,
			'auto_html' => null,
			'inline_css' => true,
			'url_strip_qs' => null,
			'preserve_recipients' => null,
			'view_content_link' => $log_content,
			'bcc_address' => null,
			'tracking_domain' => null,
			'signing_domain' => null,
			'return_path_domain' => null,
			'merge' => true,
			'global_merge_vars' => $vars,
			'merge_vars' => $user_var_data,
			'tags' => $tags,
			'subaccount' => 'InviteBIG - Live',
			'google_analytics_domains' => array(''),
			'google_analytics_campaign' => null,
			'metadata' => null,
			'recipient_metadata' => null,
			'attachments' => null,
			'images' => array(array("type"=>"image/png","name"=>"navbarlogo","content"=>base64_encode_image('assets/img/Invite_BIG_Final_Logo_01_email.png','png')))
		);
		$async = false;
		$ip_pool = 'Main Pool';
		$send_at = null;
		$result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
		//print_r($result);
		/*
		Array
		(
			[0] => Array
				(
					[email] => recipient.email@example.com
					[status] => sent
					[reject_reason] => hard-bounce
					[_id] => abc123abc123abc123abc123abc123
				)
		
		)
		*/
	} catch(Mandrill_Error $e) {
		// Mandrill errors are thrown as exceptions
		//echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
		// A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
		//throw $e;
		//print_r( $e);
	}
}

function base64_encode_image ($filename=string,$filetype=string) {
    if ($filename) {
        $imgbinary = fread(fopen($filename, "r"), filesize($filename));
        //return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
		return base64_encode($imgbinary);
    }
}

?>