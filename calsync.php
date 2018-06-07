<?php
require_once('vendor/google/apiclient/src/Google/autoload.php');
require_once 'vendor/google/apiclient/src/Google/Client.php';
require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';
require_once("config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
session_start();

// Get All the Bookings from current date
if($_POST['todaysdate'])
{
  $sql = "SELECT booking.bookingid AS bookingid, booking.iseventcreated AS iseventcreated, venues.timezone AS timezone, booking.start AS start, booking.stop AS stop, booking_info.name AS name, booking_info.description AS description, booking_info.contact_email AS contact_email, venues.venueid AS venueid, venues.address AS address
  FROM booking
  INNER JOIN booking_info ON booking.bookingid = booking_info.bookingid
  INNER JOIN booking_resources ON booking.bookingid = booking_resources.bookingid
  INNER JOIN venues ON booking_resources.venueid = venues.venueid
  AND booking.start >= '". $_POST['todaysdate']."'";
  $result = $conn->query($sql);
  $response=array();
  $data=array();
  $client = new Google_Client();
  $calendarId = $_POST['calendarid'];
  $resourceid = $_POST['rsid'];
  $venueid = $_POST['venueid'];
  if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
  }

  if ($client->isAccessTokenExpired()) {
    $sqlSelect = "SELECT * FROM googleCallist where userid = ".$_SESSION['userid'];
    $resultSelect = $conn->query($sqlSelect);
    if ($resultSelect->num_rows > 0) {
      while($row = $resultSelect->fetch_assoc()) {
        $rToken = $row['refresh_token'];
        break;
      }
    }

    $client->setAuthConfig('{"web":{"client_id":"41840486781-6fit272os86kor3s4d2iebail0hmk1fj.apps.googleusercontent.com","project_id":"invitebigproject","auth_uri":"https://accounts.google.com/o/oauth2/auth","token_uri":"https://accounts.google.com/o/oauth2/token","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","client_secret":"vzWMOiSvQEZ6Cqn3zpceMdiE","redirect_uris":["http://myinvitebig.com","http://myinvitebig.com/cal5.php","http://myinvitebig.com/dashboard/","https://d.invitebig.com/","https://d.invitebig.com/cal5.php","https://d.invitebig.com/dashboard/","http://myinvitebig.com/cal4.php","http://myinvitebig.com/oauth1.php"],"javascript_origins":["http://localhost","http://myinvitebig.com","https://d.invitebig.com"]}}');

    $client->refreshToken($rToken);
    $refreshToken = $client->getAccessToken();
  }

  // $sqlGooglCalListUpdate="";
  //$sqlGooglCalListUpdate = "UPDATE googleCallist SET venueid = ".$venueid.", resourceid = ".$resourceid." WHERE userid = ".$_SESSION['userid']." and calid = '".$calendarId."'";

  //echo "\r\n=>".$sqlGooglCalListUpdate;
  //mysqli_query($conn, $sqlGooglCalListUpdate);

  //printf("%d=>",mysqli_affected_rows());
   $sqlGrSelect = "SELECT * FROM googleResourcelist where resourceid = ".$resourceid;
  // $sqlGrSelect = "SELECT * FROM googleResourcelist where calid = '".$calendarId."'";
    $resultGrSelect = $conn->query($sqlGrSelect);
   if ($resultGrSelect->num_rows > 0) {
       $sqlgoogleResourcelistUpdate = "UPDATE googleResourcelist SET calid = '".$calendarId."' , venueid = ".$venueid." where resourceid = ".$resourceid;
      //echo $sqlgoogleResourcelistUpdate;
      mysqli_query($conn, $sqlgoogleResourcelistUpdate);
      //echo "\r\n";
      }
      else
      {
      $sqlgoogleResourcelistInsert = "INSERT INTO googleResourcelist (calid, resourceid, venueid,userid) VALUES ("."'".$calendarId."'".",".$resourceid.",".$venueid.",".$_SESSION['userid'].")";
      //echo $sqlgoogleResourcelistInsert;
      //echo "\r\n";
      $conn->query($sqlgoogleResourcelistInsert);        
      }



  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      // To sync new booking with calendar
      if($row["iseventcreated"]!=1)
      {
        $address=$row['address'];
        $description=$row['description'];
        $eventname=$row['name'];
        $contactemail=$row['contact_email'];
        $st = gmdate("Y-m-d\TH:i:s\Z", $row['start']);
        $stp = gmdate("Y-m-d\TH:i:s\Z", $row['stop']);
        if ($client->getAccessToken()) {
          $service = new Google_Service_Calendar($client);
          $event = new Google_Service_Calendar_Event(array(
            'summary' => $eventname,
            'location' => $address,
            'description' => $description,
            'start' => array(
              'dateTime' => $st,
              'timeZone' => $row['timezone'],
            ),
            'end' => array(
              'dateTime' => $stp,
              'timeZone' => $row['timezone'],
            ),
            'recurrence' => array(
              'RRULE:FREQ=DAILY;COUNT=2'
            ),
            'attendees' => array(
              array('email' => $row['contact_email']),
            ),
            'reminders' => array(
              'useDefault' => FALSE,
              'overrides' => array(
                array('method' => 'email', 'minutes' => 24 * 60),
                array('method' => 'popup', 'minutes' => 10),
              ),
            ),
          ));
          $event = $service->events->insert($calendarId, $event);
          $token = json_decode($client->getAccessToken());
          $aToken = $token->access_token;
          $newcalid=$event->id;
          $sqlBookingUpdate = "UPDATE booking SET cal_id = '".$newcalid."', orgToken = '".$aToken."' , iseventcreated = 1 WHERE bookingid = ".$row['bookingid'];
          mysqli_query($conn, $sqlBookingUpdate);
        }
      }
    }
  }

  $service = new Google_Service_Calendar($client);
  $events = $service->events->listEvents($calendarId);
  while(true) {
    foreach ($events->getItems() as $event) {
      $sqlSelect = "SELECT * FROM booking where cal_id = '".$event->getId()."'";
      $resultSelect = $conn->query($sqlSelect);
      $vid = $_POST['venueid'];
      $todaysdate = $_POST['todaysdate'];
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

      if ($resultSelect->num_rows > 0) {
        // If event is already sync with Invitebig then if event is updated the just update it.
        while($row = $resultSelect->fetch_assoc()) {
          $sqlBookingUpdate="";
          //echo "Update to db\r\n";
          $sqlBookingUpdate = "UPDATE booking SET userid = ".$_SESSION['userid'].", start = '".$startDate."', stop = '".$endDate."', cal_updatedate = '".$updatedDate."' WHERE bookingid = ".$row['bookingid'];
          mysqli_query($conn, $sqlBookingUpdate);

          $sqlBookingResourcesUpdate = "";
          $sqlBookingResourcesUpdate = "UPDATE booking_resources SET starttime='".$startDate."', stoptime = '".$endDate."' WHERE bookingid = ".$row['bookingid'];

          mysqli_query($conn, $sqlBookingResourcesUpdate);

          $evname=utf8_encode($eventname);
          $desc=utf8_encode($description);
          $sqlBookingInfoUnpdate = "";
          $sqlBookingInfoUnpdate = "UPDATE booking_info SET name="."'".$evname."'".", description = "."'".$desc."'".", contact_email = "."'".$contactemail."'"." WHERE bookingid = ".$row['bookingid'];

          mysqli_query($conn, $sqlBookingInfoUnpdate);
        }
      }
      else
      {
        // To sync new events with Invitebig.
        //echo "Inesrt to db\r\n";
        $status = "Imported";
        $flag=1;
        $sqlBooking = "INSERT INTO booking (venueid,userid,timestamp,start,stop,status,cal_updatedate,cal_id,iseventcreated) VALUES (".$vid.",".$_SESSION['userid'].","."'".$todaysdate."'".","."'".$startDate."'".","."'".$endDate."'".","."'".$status."'".","."'".$updatedDate."'".","."'".$eventid."'".",".$flag.")";
        //echo $sqlBooking;
        //echo "\r\n";
        $conn->query($sqlBooking);

        $query="";
        $query = "SELECT MAX(bookingid) FROM booking";
        $result = mysqli_query($conn,  $query);
        $row = mysqli_fetch_row($result);
        //$bid = $row[0] + 1;
        $bid = $row[0];

        $sqlBooking_resources = "INSERT INTO booking_resources (bookingid,venueid,starttime,stoptime,resourceid) VALUES (".$bid .",".$vid.","."'".$startDate."'".","."'".$endDate."'".",".$resourceid.")";
        //echo $sqlBooking_resources;
        //echo "\r\n";

        $conn->query($sqlBooking_resources);
        $evname=utf8_encode($eventname);
        $desc=utf8_encode($description);

        $sqlBooking_infoInsert = "INSERT INTO booking_info (bookingid,name,description,contact_email) VALUES (".$bid.","."'".$evname."'".","."'".$desc."'".","."'".$contactemail."'".")";

       // echo $sqlBooking_infoInsert;
       // echo "\r\n";
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
?>

