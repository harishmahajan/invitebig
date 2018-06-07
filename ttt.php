<?php
session_start();
// require_once('/var/www/myinvitebig.com/vendor/google/apiclient/src/Google/autoload.php');
// require_once '/var/www/myinvitebig.com/vendor/google/apiclient/src/Google/Client.php';
// require_once '/var/www/myinvitebig.com/vendor/google/apiclient/src/Google/Service/Calendar.php';
// require_once("/var/www/myinvitebig.com/config.php");

// For Test Server
// require_once('/var/www/invitebig/vendor/google/apiclient/src/Google/autoload.php');
// require_once '/var/www/invitebig/vendor/google/apiclient/src/Google/Client.php';
// require_once '/var/www/invitebig/vendor/google/apiclient/src/Google/Service/Calendar.php';
// require_once("/var/www/invitebig/config.php");

require_once('vendor/google/apiclient/src/Google/autoload.php');
require_once 'vendor/google/apiclient/src/Google/Client.php';
require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';
require_once("config.php");

$conn = new mysqli($servername, $username, $password, $dbname);
$calid="";
$venueid="";
$resourceid="";
$client = new Google_Client();
$client->setAuthConfig('{"web":{"client_id":"41840486781-6fit272os86kor3s4d2iebail0hmk1fj.apps.googleusercontent.com","project_id":"invitebigproject","auth_uri":"https://accounts.google.com/o/oauth2/auth","token_uri":"https://accounts.google.com/o/oauth2/token","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","client_secret":"vzWMOiSvQEZ6Cqn3zpceMdiE","redirect_uris":["http://myinvitebig.com","http://myinvitebig.com/cal5.php","http://myinvitebig.com/dashboard/","https://d.invitebig.com/","https://d.invitebig.com/cal5.php","https://d.invitebig.com/dashboard/","http://myinvitebig.com/cal4.php","http://myinvitebig.com/oauth1.php"],"javascript_origins":["http://localhost","http://myinvitebig.com","https://d.invitebig.com"]}}');
$cnt = 1;
if ($client->isAccessTokenExpired()) {
	$sqlSelect = "SELECT * FROM googleCallist where status = 1 and refresh_token!='' GROUP BY userid";
	$resultSelect = $conn->query($sqlSelect);
	if ($resultSelect->num_rows > 0) {
		while($row = $resultSelect->fetch_assoc()) {
			$rToken = $row['refresh_token'];
			$calid = $row['calid'];
			$venueid = $row['venueid'];
			$resourceid = $row['resourceid'];
			$userid = $row['userid'];
			//echo "userid=".$userid."<br>";
			if($rToken!='')
			{
				//echo $cnt;
				//$cnt = $cnt + 1;
				//echo "<br> ============================================ <br>";
				
				$client->refreshToken($rToken);
				$refreshToken = $client->getAccessToken();
				$_SESSION['token'] = $client->getAccessToken();
				if (isset($_SESSION['token'])) {
					$client->setAccessToken($_SESSION['token']);
				}

				if (isset($_SESSION['token'])) {
					
					
					$service = new Google_Service_Calendar($client);
					$events = $service->events->listEvents('primary');

					echo "<br>".$cnt.")".$events->getTimeZone()." Userid = ".$userid;
					$cnt = $cnt + 1;
					while(true) {
						foreach ($events->getItems() as $event) {
							$sqlSelectBooking = "SELECT * FROM booking where iseventcreated = 1 and cal_id = '".trim($event->getId())."'";
							//if($event->timeZone)
							//{
								
							//echo "<br>".$event->start->dateTime;
						//}

						
						}
						$pageToken = $events->getNextPageToken();
						if ($pageToken) {
							$optParams = array('pageToken' => $pageToken);
							$events = $service->events->listEvents('primary', $optParams);
						} else {
							break;
						}
					}
				}
			}
		}
	}
}
?>