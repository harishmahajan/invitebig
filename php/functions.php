<?php

require_once("core.php");

$dbserver = "127.0.0.1";
$dbport = 3306;
$dbsocket = ":/var/run/mysqld/mysqld.sock";
// $dbuser = "ibuser";
// //$dbpass = "ibpassw!";
// $dbpass = "EChFX2!c^yc4";
// $dbname = "invitebignew";

$dbuser = "root";
$dbpass = "jack123";
$dbname = "invitebignew";


class DB
{
	public $mysqli = null;
	public $mysqliIsOpen = false;
	public $stmt = null;
	public $stmtIsOpen = false;
	
	function DB()
	{
		$this->mysqli = new mysqli($GLOBALS['dbserver'], $GLOBALS['dbuser'], $GLOBALS['dbpass'], $GLOBALS['dbname'], $GLOBALS['dbport']);
		if (mysqli_connect_errno()) die("Connection Failure to Database");
		// Didžioji salė
		$this->mysqli->set_charset("utf8");
		$this->mysqliIsOpen = true;
	}
	
	function stmt_init()
	{
		$this->stmt = $this->mysqli->stmt_init();
		$this->stmtIsOpen = true;
		return $this->stmt;
	}
	
	function CloseConn()
	{		
		if (isset($this->stmt) && $this->stmtIsOpen)
			$this->stmt->close();
		$this->stmtIsOpen = false;
	}
	
	function __destruct()
	{	
		if ($this->stmtIsOpen && isset($this->stmt->error) && strlen($this->stmt->error) > 0)
			error_log("MYSQL ERROR: ".$this->stmt->error);
		
		if (isset($this->stmt) && $this->stmtIsOpen)
			$this->stmt->close();
		
		if (isset($this->mysqli) && $this->mysqliIsOpen)
			$this->mysqli->close();
		
		unset($this->stmt);
		unset($this->mysqli);
	}
}
$db = new DB();

function arrayUnique($rArray)
{ 
    $rReturn = array(); 
    while (list($key, $val) = each($rArray))
	{ 
        if (!in_array($val,$rReturn)) 
			array_push($rReturn,$val); 
    } 
    return $rReturn; 
} 

function CheckNotifications()
{
	$_SESSION['notifications'] = array("count"=>0,"mymessages"=>0,"bookings"=>array(),"venues"=>array());
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT COUNT(*) FROM messages WHERE userid = ? AND newforu = 1"))
	{	
		$stmt->bind_param("i",$_SESSION['userid']);
		$stmt->execute();
		$stmt->bind_result($c);
		if ($stmt->fetch())
		{			
			if ($c > 0)
			{
				$_SESSION['notifications']['mymessages'] = $c;
				$_SESSION['notifications']['count']++;
			}
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT DISTINCT b.bookingid FROM booking_messages AS m LEFT JOIN booking AS b ON b.bookingid = m.bookingid WHERE b.userid = ? AND m.viewed_by_user = 0"))
	{	
		$stmt->bind_param("i",$_SESSION['userid']);
		$stmt->execute();
		$stmt->bind_result($b);
		while ($stmt->fetch())
		{			
			$_SESSION['notifications']['bookings'][] = $b;
			$_SESSION['notifications']['count']++;
		}
		$GLOBALS['db']->CloseConn();
	}
	
	foreach ($_SESSION['venueRights'] as $v)
	{
		if ($v['role'] & 4 || $v['role'] & 16)
		{
			$msgs = 0;
			$bms = array();
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT COUNT(*) FROM messages WHERE venueid = ? AND newforv = 1"))
			{	
				$stmt->bind_param("i",$v['venueid']);
				$stmt->execute();
				$stmt->bind_result($c);
				if ($stmt->fetch())
				{			
					if ($c > 0)
					{
						$msgs = $c;
						$_SESSION['notifications']['count']++;
					}
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT DISTINCT b.bookingid FROM booking_messages AS m LEFT JOIN booking AS b ON b.bookingid = m.bookingid WHERE b.venueid = ? AND m.viewed_by_venue = 0"))
			{	
				$stmt->bind_param("i",$v['venueid']);
				$stmt->execute();
				$stmt->bind_result($b);
				while ($stmt->fetch())
				{			
					$bms[] = $b;
					$_SESSION['notifications']['count']++;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			$stmt = $GLOBALS['db']->stmt_init();
			if ($stmt->prepare("SELECT DISTINCT bookingid FROM booking WHERE venueid = ? AND isnew = 1 AND status = 'Pending Approval'"))
			{	
				$stmt->bind_param("i",$v['venueid']);
				$stmt->execute();
				$stmt->bind_result($b);
				while ($stmt->fetch())
				{			
					$bms[] = $b;
					$_SESSION['notifications']['count']++;
				}
				$GLOBALS['db']->CloseConn();
			}
			
			for ($i = 0; $i < count($bms); $i++)
			{
				for ($i2 = $i+1; $i2 < count($bms); $i2++)
				{
					if ($bms[$i2] == $bms[$i])
					{
						unset($bms[$i2]);
						$bms = array_values($bms);
						$_SESSION['notifications']['count']--;
						$i2--;
					}
				}
			}
			
			$_SESSION['notifications']['venues'][] = array("id"=>$v['venueid'],"count"=>$msgs+count($bms),"messages"=>$msgs,"bookings"=>$bms);
		}
	}
}

function Sanitize($string)
{
	$s = $string;
	//$s = str_replace("'","&apos;",$s);
	//$s = str_replace("\"","&quote;",$s);
	//$s = str_replace("<","&lt;",$s);
	//$s = str_replace(">","&gt;",$s);
	//$s = htmlentities($s);
	
	return $s;
}

function FormatCurrency($amt,$cur)
{
	$cur = strtoupper($cur);
	$neg = false;
	if ($amt < 0)
		$neg = true;
	$amt = abs($amt);
	
	switch ($cur)
	{
		case "AUD":
			$amt = ($neg?"-":"")."A$".$amt;
			break;
		case "CAD":
			$amt = ($neg?"-":"")."C$".$amt;
			break;
		case "CHF":
			$amt = "CHF ".($neg?"-":"").$amt;
			break;
		case "EUR":
			$amt = ($neg?"-":"")."&#8364;".$amt;
			break;
		case "GBP":
			$amt = ($neg?"-":"")."&#163;".$amt;
			break;
		case "HKD":
			$amt = ($neg?"-":"")."HK$".$amt;
			break;
		case "INR":
			$amt = ($neg?"-":"")."&#8377;".$amt;
			break;
		case "JPY":
			$amt = ($neg?"-":"")."&#165;".$amt;
			break;
		case "MXN":
			$amt = ($neg?"-":"")."Mex$".$amt;
			break;
		case "NOK":
			$amt = ($neg?"-":"").$amt." kr";
			break;
		case "NZD":
			$amt = ($neg?"-":"")."$".$amt." NZD";
			break;
		case "RUB":
			$amt = ($neg?"-":"")."&#8381;".$amt;
			break;
		case "USD":
		default:
			$amt = ($neg?"-":"")."$".$amt;
			break;
	}
	
	return $amt;
}

function arrayCopy( array $array )
{
    $result = array();
    foreach( $array as $key => $val ) {
        if( is_array( $val ) ) {
            $result[$key] = arrayCopy( $val );
        } elseif ( is_object( $val ) ) {
            $result[$key] = clone $val;
        } else {
            $result[$key] = $val;
        }
    }
    return $result;
}

?>
