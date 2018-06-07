<?php

require_once('php/email.php');

function LoadMessage($id)
{
	$arr = array();
	$imvenue = false;
	
	$auth = false;
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT m.convoid, m.title, m.userid, m.venueid, CONCAT(u.firstname,' ',u.lastname,' (',u.email,')'), v.name, m.deletedu, m.deletedv FROM messages AS m LEFT JOIN users AS u ON u.userid = m.userid LEFT JOIN venues AS v ON v.venueid = m.venueid WHERE m.convoid = ?"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($c,$t,$u,$v,$un,$vn,$du,$dv);
		while ($stmt->fetch())
		{			
			$arr = array("convoid"=>$c,"title"=>Sanitize($t),"user"=>$un,"userid"=>$u,"du"=>$du,"dv"=>$dv,"venue"=>$vn,"venueid"=>$v,"messages"=>array());
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (isset($arr['convoid']))
	{
		$auth = false;
		if ($arr['userid'] == $_SESSION['userid'])
			$auth = true;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT role FROM venues_rights WHERE venueid = ? AND email = ?"))
		{	
			$stmt->bind_param("is",$arr['venueid'],$_SESSION['email']);
			$stmt->execute();
			$stmt->bind_result($r);
			while ($stmt->fetch())
			{			
				if ($r & 4 || $r & 16)
				{
					$auth = true;
					$imvenue = true;
				}
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if (!$auth)
			return "You are not authorized to participate in this conversation";
		else
		{
			if ((!$imvenue && $arr['du'] == 1) || ($imvenue && $arr['dv'] == 1))
				return array();
				
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT isvenue,timestamp,message FROM messages_detail WHERE convoid = ? ORDER BY timestamp DESC"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$stmt->bind_result($is,$time,$m);
				while ($stmt->fetch())
				{			
					$from = $arr['user'];
					if ($is)
						$from = $arr['venue'];
						
					$arr['messages'][] = array("isvenue"=>$is,"from"=>$from,"time"=>$time,"message"=>Sanitize($m));
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$q = "";
			if ($imvenue)
			{
				$q = "newforv = 0";
				if ($arr['userid'] == $_SESSION['userid'])
					$q .= " AND newforu = 0";
			}
			else $q = "newforu = 0";
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE messages SET ".$q." WHERE convoid = ?"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			return $arr;
		}
	}
	
	return "There was an error loading this conversation";
}

function SendMessage($convoid,$msg)
{
	$msg = Sanitize($msg);
	
	$arr = array();
	$imvenue = false;
	
	$auth = false;
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT convoid, title, userid, venueid FROM messages WHERE convoid = ?"))
	{	
		$stmt->bind_param("i",$convoid);
		$stmt->execute();
		$stmt->bind_result($c,$t,$u,$v);
		while ($stmt->fetch())
		{			
			$arr = array("convoid"=>$c,"title"=>Sanitize($t),"userid"=>$u,"venueid"=>$v);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (isset($arr['convoid']))
	{
		$auth = false;
		if ($arr['userid'] == $_SESSION['userid'])
			$auth = true;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT role FROM venues_rights WHERE venueid = ? AND email = ?"))
		{	
			$stmt->bind_param("is",$arr['venueid'],$_SESSION['email']);
			$stmt->execute();
			$stmt->bind_result($r);
			while ($stmt->fetch())
			{			
				if ($r & 4 || $r & 16)
				{
					$auth = true;
					$imvenue = true;
				}
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if (!$auth)
			return "You are not authorized to participate in this conversation";
		else
		{
			$isvenue = 0;
			if ($imvenue && $arr['userid'] != $_SESSION['userid'])
				$isvenue = 1;
				
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO messages_detail (convoid,isvenue,timestamp,message) VALUES (?,?,UNIX_TIMESTAMP(),?)"))
			{	
				$stmt->bind_param("iis",$convoid,$isvenue,$msg);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			$q = "";
			if ($arr['userid'] != $_SESSION['userid'])
				$q .= ", newforu = 1";
			if (!$imvenue)
			{
				$q .= ", newforv = 1";
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE messages SET deletedu = 0, deletedv = 0 ".$q." WHERE convoid = ?"))
			{	
				$stmt->bind_param("i",$convoid);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			if ($isvenue)
				SendUserNewPMMessage($convoid);
			else SendVenueNewPMMessage($convoid);
				
			return "success";
		}
	}
	
	return "There was an error sending this message";
}

function SendBookingMessage($bookingid,$msg)
{
	$msg = Sanitize($msg);
	
	$imvenue = CheckBookingAuth($bookingid,4);
	$userid = null;
	$venueid = null;
	
	if (!$imvenue)
		return "You are not authorized to make this request.";
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT userid, venueid FROM booking WHERE bookingid = ?"))
	{	
		$stmt->bind_param("i",$bookingid);
		$stmt->execute();
		$stmt->bind_result($u,$v);
		while ($stmt->fetch())
		{		
			$userid = $u;
			$venueid = $v;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if ($userid && $venueid)
	{
		$isvenue = 0;
		if ($imvenue && $userid != $_SESSION['userid'])
			$isvenue = 1;
		
		$v1 = 0; $v2 = 0;
		if ($userid == $_SESSION['userid'])
			$v1 = 1;
		if ($imvenue > 1)
			$v2 = 1;
			
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO booking_messages (bookingid,userid,isvenue,timestamp,message,viewed_by_user,viewed_by_venue) VALUES (?,?,?,UNIX_TIMESTAMP(),?,?,?)"))
		{	
			$stmt->bind_param("iiisii",$bookingid,$_SESSION['userid'],$isvenue,$msg,$v1,$v2);
			$stmt->execute();
			$GLOBALS['db']->CloseConn();
		}
		
		if (!$v1)
			SendUserNewPMMessage($bookingid);
		if (!$v2)
			SendVenueNewPMMessage($bookingid);
		
		return "success";
	}
	
	return "Failed to send message";
}

function DeleteMessage($id)
{
	$arr = array();
	$imvenue = false;
	
	$auth = false;
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT convoid, userid, venueid FROM messages WHERE convoid = ?"))
	{	
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($c,$u,$v);
		while ($stmt->fetch())
		{			
			$arr = array("convoid"=>$c,"userid"=>$u,"venueid"=>$v);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	if (isset($arr['convoid']))
	{
		$auth = false;
		if ($arr['userid'] == $_SESSION['userid'])
			$auth = true;
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT role FROM venues_rights WHERE venueid = ? AND email = ?"))
		{	
			$stmt->bind_param("is",$arr['venueid'],$_SESSION['email']);
			$stmt->execute();
			$stmt->bind_result($r);
			while ($stmt->fetch())
			{			
				if ($r & 4 || $r & 16)
				{
					$auth = true;
					$imvenue = true;
				}
			}
			$GLOBALS['db']->CloseConn();
		}
		
		if (!$auth)
			return "You are not authorized to delete this message";
		else
		{
			$q = "";
			if ($arr['userid'] == $_SESSION['userid'])
				$q = "deletedu = 1";
			if ($imvenue)
			{
				if (strlen($q) > 0)
					$q .= ", ";
				$q .= "deletedv = 1";
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("UPDATE messages SET ".$q." WHERE convoid = ?"))
			{	
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			return "success";
		}
	}
	
	return "There was an error deleting this message";
}

function GetVenueList()
{
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT venueid, name FROM venues WHERE status = 'active' ORDER BY name ASC"))
	{	
		$stmt->execute();
		$stmt->bind_result($i,$n);
		while ($stmt->fetch())
		{			
			$arr[] = array("id"=>$i,"name"=>Sanitize($n));
		}
		$GLOBALS['db']->CloseConn();
	}
	
	return $arr;
}

function GetUserList($vid)
{
	$arr = array();
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT DISTINCT u.userid, CONCAT(u.firstname,' ',u.lastname,' (',u.email,')') FROM booking AS b LEFT JOIN users AS u ON u.userid = b.userid WHERE b.venueid = ? ORDER BY CONCAT(u.firstname,' ',u.lastname,' (',u.email,')') ASC"))
	{	
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($i,$n);
		while ($stmt->fetch())
		{			
			$arr[] = array("id"=>$i,"name"=>$n);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT DISTINCT u.userid, CONCAT(u.firstname,' ',u.lastname,' (',u.email,')') FROM messages AS m LEFT JOIN users AS u ON u.userid = m.userid WHERE m.venueid = ? ORDER BY CONCAT(u.firstname,' ',u.lastname,' (',u.email,')') ASC"))
	{	
		$stmt->bind_param("i",$vid);
		$stmt->execute();
		$stmt->bind_result($i,$n);
		while ($stmt->fetch())
		{			
			$arr[] = array("id"=>$i,"name"=>$n);
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$arr = arrayUnique($arr);
	return $arr;
}

function NewMessage($userid, $venueid, $title, $message)
{
	$title = Sanitize($title);
	$message = Sanitize($message);
	
	$n1 = 1;
	$n2 = 1;
	if ($userid == $_SESSION['userid'])
		$n1 = 0;
	else $n2 = 0;
	
	$auth = false;
	if ($userid == $_SESSION['userid'])
		$auth = true;
	else
	{
		$approved = 0;
		foreach ($_SESSION['venueRights'] as $venue)
			if ($venue['venueid'] == $venueid && $venue['role'] > 0)
				$approved = $venue['role'];
			
		if ($_SESSION['siteRole'] == 999)
			$approved = 16;
			
		if ($approved & 4 || $approved & 16)
			$auth = true;
	}
	
	if ($auth)
	{
		$convoid = null;
		
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("INSERT INTO messages (userid,venueid,deletedu,deletedv,newforu,newforv,title) VALUES (?,?,0,0,?,?,?)"))
		{	
			$stmt->bind_param("iiiis",$userid,$venueid,$n1,$n2,$title);
			$stmt->execute();
			if ($stmt->affected_rows)
				$convoid = $stmt->insert_id;
			$GLOBALS['db']->CloseConn();
		}
		
		if ($convoid)
		{
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("INSERT INTO messages_detail (convoid,isvenue,timestamp,message) VALUES (?,?,UNIX_TIMESTAMP(),?)"))
			{	
				$stmt->bind_param("iis",$convoid,$n1,$message);
				$stmt->execute();
				$GLOBALS['db']->CloseConn();
			}
			
			if ($n1)
				SendUserNewPMMessage($convoid);
			if ($n2)
				SendVenueNewPMMessage($convoid);
			return array("id"=>$convoid);
		}
	}
	
	return null;
}

?>