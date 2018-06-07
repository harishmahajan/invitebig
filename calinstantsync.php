<?php
session_start();
require_once('vendor/google/apiclient/src/Google/autoload.php');
require_once 'vendor/google/apiclient/src/Google/Client.php';
require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';
require_once("config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
$eventname = $_POST['reservationEventName'];
$googleDescUrl = $_POST['googleDescUrl'];
$description = $_POST['reservationDescription'] . "\r\n".$googleDescUrl;
$contactemail = $_POST['reservationContactEmail'];
//$start = gmdate("Y-m-d\TH:i:s\Z", $_POST['startdate']);
//$end = gmdate("Y-m-d\TH:i:s\Z", $_POST['enddate']);
$start = $_POST['startdate'];
$end = $_POST['enddate'];
$address = " ";
$timezone = $_POST['timezone'];
//$timezone = "Asia/Kolkata";

// echo "\r\nstartdate".$_POST['startdate'];
// echo "\r\nenddate".$_POST['enddate'];
// echo "\r\nstart:".$start;
// echo "\r\nend:".$end;
//echo "\r\nstart:".$sdate;
//echo "\r\nend:".$edate;
//echo "\r\ntimezone:".$timezone;

$startarray = explode(",", $_POST['startdate']);
$endarray = explode(",", $_POST['enddate']);

$count=count($startarray);

$calid="";

$query="";
$query = "SELECT MAX(bookingid) FROM booking";
$result = mysqli_query($conn,  $query);
$row = mysqli_fetch_row($result);
$bid = $row[0];
$rToken='';
$client = new Google_Client();
if ($client->isAccessTokenExpired()) {
	$sqlSelect = "SELECT * FROM googleCallist where status = 1 and userid = ".$_SESSION['userid'];
	$resultSelect = $conn->query($sqlSelect);
	if ($resultSelect->num_rows > 0) {
		while($row = $resultSelect->fetch_assoc()) {
			$rToken = $row['refresh_token'];
			$calid = $row['calid'];
			break;
		}
	}
	//echo $rToken;
	$client->setAuthConfig('{"web":{"client_id":"41840486781-6fit272os86kor3s4d2iebail0hmk1fj.apps.googleusercontent.com","project_id":"invitebigproject","auth_uri":"https://accounts.google.com/o/oauth2/auth","token_uri":"https://accounts.google.com/o/oauth2/token","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","client_secret":"vzWMOiSvQEZ6Cqn3zpceMdiE","redirect_uris":["http://myinvitebig.com","http://myinvitebig.com/cal5.php","http://myinvitebig.com/dashboard/","https://d.invitebig.com/","https://d.invitebig.com/cal5.php","https://d.invitebig.com/dashboard/","http://myinvitebig.com/cal4.php","http://myinvitebig.com/oauth1.php"],"javascript_origins":["http://localhost","http://myinvitebig.com","https://d.invitebig.com"]}}');
	
	if($rToken!='')
	{
		$client->refreshToken($rToken);
		$refreshToken = $client->getAccessToken();
		$_SESSION['token'] = $client->getAccessToken();

		if (isset($_SESSION['token'])) {
			$client->setAccessToken($_SESSION['token']);
		}


		if($bid!='' || $bid!=null)
		{
		if ($client->getAccessToken()) {
			
			for ($i=0; $i < $count; $i++) { 
				$s=$startarray[$i];
				$e=$endarray[$i];
				$service = new Google_Service_Calendar($client);
				$event = new Google_Service_Calendar_Event(array(
		        'summary' => $eventname,
		        'location' => $address,
		        'description' => $description,
		        'start' => array(
		          'dateTime' => $s,
		          'timeZone' => $timezone,
		        ),
		        'end' => array(
		          'dateTime' => $e,
		          'timeZone' => $timezone,
		        ),
		        'attendees' => array(
		          array('email' => $contactemail),
		        ),
		        'reminders' => array(
		          'useDefault' => FALSE,
		          'overrides' => array(
		            array('method' => 'email', 'minutes' => 24 * 60),
		            array('method' => 'popup', 'minutes' => 10),
		          ),
		        ),
		      ));

			$event = $service->events->insert($calid, $event);
			$token = json_decode($client->getAccessToken());
			$aToken = $token->access_token;
			$newcalid=$event->id;

		    $updatedDate = $event->getUpdated();
		    if($updatedDate!='')
		      $updatedDate = strtotime($event->getUpdated());
		    else
		      $updatedDate = " ";

			$sqlBookingUpdate = "UPDATE booking SET cal_updatedate = '".$updatedDate."', cal_id = '".$newcalid."', orgToken = '".$aToken."' , iseventcreated = 1 WHERE bookingid = ".$bid;
		    mysqli_query($conn, $sqlBookingUpdate);
		    //echo $startarray[$i];
			//	echo "\r\n";
			//break;
			}
		    //echo "\r\n".$sqlBookingUpdate;
		    //exit();
		}
		}
	}

}

?>