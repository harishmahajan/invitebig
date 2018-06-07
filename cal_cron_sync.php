<?php
session_start();
require_once('/var/www/myinvitebig.com/vendor/google/apiclient/src/Google/autoload.php');
require_once '/var/www/myinvitebig.com/vendor/google/apiclient/src/Google/Client.php';
require_once '/var/www/myinvitebig.com/vendor/google/apiclient/src/Google/Service/Calendar.php';
require_once("/var/www/myinvitebig.com/config.php");

// For Test Server
// require_once('/var/www/invitebig/vendor/google/apiclient/src/Google/autoload.php');
// require_once '/var/www/invitebig/vendor/google/apiclient/src/Google/Client.php';
// require_once '/var/www/invitebig/vendor/google/apiclient/src/Google/Service/Calendar.php';
// require_once("/var/www/invitebig/config.php");

// require_once('vendor/google/apiclient/src/Google/autoload.php');
// require_once 'vendor/google/apiclient/src/Google/Client.php';
// require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';
// require_once("config.php");

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
			echo "userid=".$userid."<br>";
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
					//echo "<br> userid : ";
					//echo $userid;
					// echo "<br> userid : ";
					// echo $userid;
					// echo "<br>";

					$sqlBooking = "SELECT booking.bookingid AS bookingid, booking.iseventcreated AS iseventcreated, venues.timezone AS timezone, booking.start AS start, booking.stop AS stop, booking_info.name AS name, booking_info.description AS description, booking_info.contact_email AS contact_email, venues.venueid AS venueid, venues.address AS address
  					FROM booking
  					INNER JOIN booking_info ON booking.bookingid = booking_info.bookingid
  					INNER JOIN booking_resources ON booking.bookingid = booking_resources.bookingid
  					INNER JOIN venues ON booking_resources.venueid = venues.venueid
  					AND booking.start >= '". strtotime(date("Y/m/d")) ."'"." and booking.userid = ".$userid;
					$resultBooking = $conn->query($sqlBooking);
					//$cnt = 1;
					
					$service = new Google_Service_Calendar($client);
					$events = $service->events->listEvents('primary');

					if ($resultBooking->num_rows > 0) {
						while($row = $resultBooking->fetch_assoc()) {
							//echo "iseventcreated=".$row['bookingid'];
							//echo "<br>";
							if($row['iseventcreated']!=1)
							{
								if($row['name']!='')
								{
									$evname=utf8_encode($row['name']);
									//echo $row['bookingid']."=".$row['name']."=".$row['iseventcreated']."=".$row['timezone']."=".$row['start']."=".$row["stop"]."=".$row["description"]."=".$row["contact_email"]."=".$row['venueid']."=".$row["address"];
									//echo "<br>";
								}
								else
								{
									$evname=" ";
								}
								if($row['timezone']!='')
								{
									$timezone=$row['timezone'];
								}
								else
								{
									$timezone="";
								}
								if($row['start']!='')
								{
									$start = gmdate("Y-m-d\TH:i:s\Z", $row['start']);
								}
								else
								{
									$start="";
								}
								if($row['stop']!='')
								{
									$stop = gmdate("Y-m-d\TH:i:s\Z", $row['stop']);
								}
								else
								{
									$stop='';
								}
								if($row['description']!='')
								{
									$description=utf8_encode($row['description']);
								}
								else
								{
									$description=" ";
								}
								if($row['contact_email']!='')
								{
									$contact_email=$row['contact_email'];
								}
								else
								{
									$contact_email=" ";
								}
								if($row['address']!='')
								{
									$address=utf8_encode($row['address']);
								}
								else
								{
									$address=" ";
								}
								$service = new Google_Service_Calendar($client);
								$event = new Google_Service_Calendar_Event(array(
									'summary' => $evname,
									'location' => $address,
									'description' => $description,
									'start' => array(
										'dateTime' => $start,
										'timeZone' => $timezone,
										),
									'end' => array(
										'dateTime' => $stop,
										'timeZone' => $timezone,
										),
									'recurrence' => array(
										'RRULE:FREQ=DAILY;COUNT=2'
										),
									'attendees' => array(
										array('email' => $contact_email),
										),
									'reminders' => array(
										'useDefault' => FALSE,
										'overrides' => array(
											array('method' => 'email', 'minutes' => 24 * 
												60),
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

								$sqlBookingUpdate = "UPDATE booking SET cal_updatedate = '".$updatedDate."', cal_id = '".$newcalid."', orgToken = '".$aToken."' , iseventcreated = 1 WHERE bookingid = ".$row['bookingid'];

								//echo $sqlBookingUpdate."<br>";
								mysqli_query($conn, $sqlBookingUpdate);
							}
						}
					}
					
					while(true) {
						foreach ($events->getItems() as $event) {
							$sqlSelectBooking = "SELECT * FROM booking where iseventcreated = 1 and cal_id = '".trim($event->getId())."'";
							//echo "<br>".$resultSelectBooking->num_rows."=>".$sqlSelectBooking;
							$resultSelectBooking = $conn->query($sqlSelectBooking);
							if ($resultSelectBooking->num_rows > 0) {
								while($row=$resultSelectBooking->fetch_assoc())
								{
									//echo "<br>Update:- ".$resultSelectBooking->num_rows."=>".$sqlSelectBooking;
									$startDate = $event->start->dateTime;
									//echo "<br>st:- ".$startDate;
									if($startDate!='')
										$startDate = strtotime($event->start->dateTime);
									else
										$startDate = " ";


									$endDate = $event->end->dateTime;
									if($endDate!='')
										$endDate = strtotime($event->end->dateTime);
									else
										$endDate = " ";

									$updatedDate = $event->getUpdated();
									if($updatedDate!='')
										$updatedDate = strtotime($event->getUpdated());
									else
										$updatedDate = " ";

									$eventname = $event->getSummary();
									if($eventname!='')
										$eventname = $event->getSummary();
									else
										$eventname = " ";

									$description = $event->getDescription();
									if($description!='')
										$description = $event->getDescription();
									else
										$description = " ";

									$contactemail = $event->creator->email;
									if($contactemail!='')
										$contactemail = $event->creator->email;
									else
										$contactemail = " ";

									$eventid = $event->getId();
									$sqlBookingUpdate="";
									//$sqlBookingUpdate = "UPDATE booking SET userid = ".$row['userid'].", start = '".$startDate."', stop = '".$endDate."', cal_updatedate = '".$updatedDate."' WHERE bookingid = ".$row['bookingid'];

									//echo $sqlBookingUpdate;
									//echo "<br>";
									//mysqli_query($conn, $sqlBookingUpdate);

									$sqlBookingResourcesUpdate = "";
									//$sqlBookingResourcesUpdate = "UPDATE booking_resources SET starttime='".$startDate."', stoptime = '".$endDate."' WHERE bookingid = ".$row['bookingid'];
									//echo $sqlBookingResourcesUpdate;
									//echo "<br>";
									//mysqli_query($conn, $sqlBookingResourcesUpdate);

									$evname=utf8_encode($eventname);
									$desc=utf8_encode($description);
									$sqlBookingInfoUnpdate = "";
									$sqlBookingInfoUnpdate = "UPDATE booking_info SET name="."'".$evname."'".", description = "."'".$desc."'".", contact_email = "."'".$contactemail."'"." WHERE bookingid = ".$row['bookingid'];
							//echo $sqlBookingInfoUnpdate."<br>";
									mysqli_query($conn, $sqlBookingInfoUnpdate);
								}
							}
							else
							{
								//echo "<br> Insert : ";
								//echo trim($event->getId());|
								//echo "=>".$userid;
								//echo "<br>Insert:- ".$resultSelectBooking->num_rows."=>".$sqlSelectBooking;
								if($startDate!='')
									$startDate = strtotime($event->start->dateTime);
								else
									$startDate = " ";
								$endDate = $event->end->dateTime;
								if($endDate!==null)
									$endDate = strtotime($event->end->dateTime);
								else
									$endDate = " ";
								$updatedDate = $event->getUpdated();
								if($updatedDate!==null)
									$updatedDate = strtotime($event->getUpdated());
								else
									$updatedDate = " ";
								$createdDate = $event->getCreated();
								if($createdDate!==null)
									$createdDate = strtotime($event->getCreated());
								else
									$createdDate = " ";
								$eventname = $event->getSummary();
								if($eventname!==null)
									$eventname = $event->getSummary();
								else
									$eventname = " ";
								$description = $event->getDescription();
								if($description!==null)
									$description = $event->getDescription();
								else
									$description = " ";
								$contactemail = $event->creator->email;
								if($contactemail!==null)
									$contactemail = $event->creator->email;
								else
									$contactemail = " ";
								$eventid = $event->getId();
								$status = "Imported";
								$flag=1;
								$sqlBooking = "INSERT INTO booking (venueid,userid,timestamp,start,stop,status,cal_updatedate,cal_id,iseventcreated) VALUES (".$venueid.",".$userid.","."'".$createdDate."'".","."'".$startDate."'".","."'".$endDate."'".","."'".$status."'".","."'".$updatedDate."'".","."'".$eventid."'".",".$flag.")";
								//echo $sqlBooking;
								//echo "\r\n\r\n<br>";
								$conn->query($sqlBooking);

								$query="";
								$query = "SELECT MAX(bookingid) FROM booking";
								$result = mysqli_query($conn,  $query);
								$row = mysqli_fetch_row($result);
								$bid = $row[0];
								$sqlBooking_resources = "INSERT INTO booking_resources (bookingid,venueid,starttime,stoptime,resourceid) VALUES (".$bid .",".$venueid.","."'".$startDate."'".","."'".$endDate."'".",".$resourceid.")";
								//echo $sqlBooking_resources;
								//echo "\r\n\r\n<br>";
								$conn->query($sqlBooking_resources);
								$evname=utf8_encode($eventname);
								$desc=utf8_encode($description);
								$sqlBooking_infoInsert = "INSERT INTO booking_info (bookingid,name,description,contact_email) VALUES (".$bid.","."'".$evname."'".","."'".$desc."'".","."'".$contactemail."'".")";
								//echo $sqlBooking_infoInsert;
								//echo "\r\n\r\n<br>";
								$conn->query($sqlBooking_infoInsert);
							}
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