<?php
session_start();
require_once('vendor/google/apiclient/src/Google/autoload.php');
require_once 'vendor/google/apiclient/src/Google/Client.php';
require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';
require_once("config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
$calid="";
$venueid="";
$resourceid="";
$client = new Google_Client();
if ($client->isAccessTokenExpired()) {
	$sqlSelect = "SELECT * FROM googleCallist where status = 1 and userid = ".$_SESSION['userid'];
	$resultSelect = $conn->query($sqlSelect);
	if ($resultSelect->num_rows > 0) {
		while($row = $resultSelect->fetch_assoc()) {
			$rToken = $row['refresh_token'];
			$calid = $row['calName'];
			$venueid = $row['venueid'];
			$resourceid = $row['resourceid'];
			break;
		}
	}
	$client->setAuthConfig('{"web":{"client_id":"41840486781-6fit272os86kor3s4d2iebail0hmk1fj.apps.googleusercontent.com","project_id":"invitebigproject","auth_uri":"https://accounts.google.com/o/oauth2/auth","token_uri":"https://accounts.google.com/o/oauth2/token","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","client_secret":"vzWMOiSvQEZ6Cqn3zpceMdiE","redirect_uris":["http://myinvitebig.com","http://myinvitebig.com/cal5.php","http://myinvitebig.com/dashboard/","https://d.invitebig.com/","https://d.invitebig.com/cal5.php","https://d.invitebig.com/dashboard/","http://myinvitebig.com/cal4.php","http://myinvitebig.com/oauth1.php"],"javascript_origins":["http://localhost","http://myinvitebig.com","https://d.invitebig.com"]}}');
	if($rToken!='')
	{
		$client->refreshToken($rToken);
		$refreshToken = $client->getAccessToken();
		$_SESSION['token'] = $client->getAccessToken();

		if (isset($_SESSION['token'])) {
			$client->setAccessToken($_SESSION['token']);
		}

		if ($client->getAccessToken()) {
			$service = new Google_Service_Calendar($client);
			$events = $service->events->listEvents($calid);
			echo "<table border=1>";
			while(true) {

			foreach ($events->getItems() as $event) {
				$sqlSelectBooking = "SELECT * FROM booking where cal_id = '".$event->getId()."'";
			$resultSelectBooking = $conn->query($sqlSelectBooking);
			echo "<br>".$sqlSelectBooking ."</br>";
			if ($resultSelectBooking->num_rows > 0) {
			
				while($row = $resultSelectBooking->fetch_assoc()) {
				$uDate=strtotime($event->getUpdated());
					if($uDate!=$row['cal_updatedate'])
					 {
					  	$startDate = $event->start->dateTime;
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
				        $sqlBookingUpdate = "UPDATE booking SET userid = ".$_SESSION['userid'].", start = '".$startDate."', stop = '".$endDate."', cal_updatedate = '".$updatedDate."' WHERE bookingid = ".$row['bookingid'];
				        //mysqli_query($conn, $sqlBookingUpdate);
				        echo $sqlBookingUpdate."<br>";
				        $sqlBookingResourcesUpdate = "";
				        $sqlBookingResourcesUpdate = "UPDATE booking_resources SET starttime='".$startDate."', stoptime = '".$endDate."' WHERE bookingid = ".$row['bookingid'];

				        //mysqli_query($conn, $sqlBookingResourcesUpdate);
				        echo $sqlBookingResourcesUpdate."<br>";
				        $evname=utf8_encode($eventname);
				        $desc=utf8_encode($description);
				        $sqlBookingInfoUnpdate = "";
				        $sqlBookingInfoUnpdate = "UPDATE booking_info SET name="."'".$evname."'".", description = "."'".$desc."'".", contact_email = "."'".$contactemail."'"." WHERE bookingid = ".$row['bookingid'];
				        echo $sqlBookingInfoUnpdate."<br>";
				        //mysqli_query($conn, $sqlBookingInfoUnpdate);
					   }

				}
			  }
			  else
			  {
			  	//if($row['cal_id'] == $event->getId())
					//{	
					// }
					// else{
					    // echo "<tr>";
					    // echo "<td>".$event->getId()."</td>";
					    // echo "<td>".$event->getUpdated()."</td>";
					    // echo "<td>".$event->getSummary()."</td>";
					    // echo "<td>".$event->start->dateTime."</td>";
					    // echo "<td>".$event->end->dateTime."</td>";
					    // echo "<td>".$event->getDescription()."</td>";
					    // echo "<td>".$event->creator->email."</td>";
			    
					    // echo "</tr>";

						// $vid = $_POST['venueid'];
					 //    $todaysdate = $_POST['todaysdate'];
			  	if($event->getId()!=NULL)
			  	{
			  			if($event->start->dateTime!='')
					    $startDate = $event->start->dateTime;

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

					    $createdDate = $event->getCreated();
					    if($createdDate!='')
					      $createdDate = strtotime($event->getCreated());
					    else
					      $createdDate = " ";

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

					  echo "\r\nEventid = ".$eventid;  	
					  $status = "Imported";
				      $flag=1;
				      $sqlBooking = "INSERT INTO booking (venueid,userid,timestamp,start,stop,status,cal_updatedate,cal_id,iseventcreated) VALUES (".$venueid.",".$_SESSION['userid'].","."'".$createdDate."'".","."'".$startDate."'".","."'".$endDate."'".","."'".$status."'".","."'".$updatedDate."'".","."'".$eventid."'".",".$flag.")";
				      echo $sqlBooking;
				      echo "\r\n\r\n<br>";
				      //$conn->query($sqlBooking);

				      $query="";
				      $query = "SELECT MAX(bookingid) FROM booking";
				      $result = mysqli_query($conn,  $query);
				      $row = mysqli_fetch_row($result);
				      $bid = $row[0] + 1;

				      //echo "\r\nbid:-".$row[0]."=".$bid;

				      $sqlBooking_resources = "INSERT INTO booking_resources (bookingid,venueid,starttime,stoptime,resourceid) VALUES (".$bid .",".$venueid.","."'".$startDate."'".","."'".$endDate."'".",".$resourceid.")";
				      echo $sqlBooking_resources;
				      echo "\r\n\r\n<br>";

				      //$conn->query($sqlBooking_resources);
				      $evname=utf8_encode($eventname);
				      $desc=utf8_encode($description);

				      $sqlBooking_infoInsert = "INSERT INTO booking_info (bookingid,name,description,contact_email) VALUES (".$bid.","."'".$evname."'".","."'".$desc."'".","."'".$contactemail."'".")";

				      echo $sqlBooking_infoInsert;
				      echo "\r\n\r\n<br>";
				   //    $conn->query($sqlBooking_infoInsert);
			  }
			  }
			}
			  $pageToken = $events->getNextPageToken();
			  if ($pageToken) {
			    $optParams = array('pageToken' => $pageToken);
			    $events = $service->events->listEvents($calid, $optParams);
			  } else {
			    break;
			  }
			}

			echo "</table>";
			
		}
	}
}
?>