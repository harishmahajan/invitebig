<?php

// ...
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//set_include_path('google-api-php-client1/src');
require_once('vendor/google/apiclient/src/Google/autoload.php');
require_once 'vendor/google/apiclient/src/Google/Client.php';
require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';
//

session_start();
$client = new Google_Client();

//$client->setUseObjects(true);
$client->setApplicationName("InviteBig");
$client->setClientId("41840486781-6fit272os86kor3s4d2iebail0hmk1fj.apps.googleusercontent.com");
$client->setClientSecret('vzWMOiSvQEZ6Cqn3zpceMdiE');
//$client->setDeveloperKey('DEVELOPER KEY');
$client->setRedirectUri('http://myinvitebig.com/cal4.php');
//$client->setRedirectUri('https://d.invitebig.com/cal5.php');
$client->setScopes(array(
    'https://www.googleapis.com/auth/calendar',
    'https://www.googleapis.com/auth/calendar.readonly',
));

function setData($calname,$cld)
{
  $servername = "localhost";
  $username = "root";
  $password = "jack123";
  $dbname = "invitebignew";

  // $servername = "localhost";
  // $username = "root";
  // $password = "W2GC@zF!6c7%";
  // $dbname = "invitebignew";
  //require 'config.php';
  $conn = mysqli_connect($servername, $username, $password, $dbname);

  $sqlSelect = "SELECT * FROM googleCallist WHERE userid = ".$_SESSION['userid'];

  $resultSelect = $conn->query($sqlSelect);
  if($resultSelect->num_rows > 0)
  {
      while ($row = $resultSelect->fetch_assoc()) {
        echo "d-cal-id : ".$row['calid'];
        echo "<br>";
        echo "g-cal-id : ".$cld;
        echo "<br>";
      }
  }


  $sql = "INSERT INTO googleCallist (calName, userid, calid) VALUES ("."'".$calname."',". $_SESSION['userid'].",'".$cld."')";

  
  //mysqli_query($conn, $sql);
  
}
function getData()
{

}

function clearData()
{
  // $servername = "localhost";
  // $username = "root";
  // $password = "W2GC@zF!6c7%";
  // $dbname = "invitebignew";
  $servername = "localhost";
  $username = "root";
  $password = "jack123";
  $dbname = "invitebignew";
//require 'config.php';
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  $sqlDelete = "DELETE FROM googleCallist WHERE userid=".$_SESSION['userid'];
  //echo '<br>'.$sqlDelete;
  //$conn->query($sqlDelete);


}



// Create connection
//$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
$response=array();
if (isset($_GET['code'])) {

    $client->authenticate($_GET['code']);
    $_SESSION['token'] = $client->getAccessToken();
        //print_r($_SESSION['token']);exit;
    //header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
    echo "TOoo = ".$client->getAccessToken();

if (isset($_SESSION['token'])) {
    //echo 'innn';exit;
    $client->setAccessToken($_SESSION['token']);
    //echo 'innn';exit;

}
}
if ($client->getAccessToken()) {
     $service = new Google_Service_Calendar($client);
     //$events = $service->events->listEvents('primary');

     $calendarList = $service->calendarList->listCalendarList();

while(true) {
  //clearData();
  foreach ($calendarList->getItems() as $calendarListEntry) {
    //echo $calendarListEntry->getId();
  setData($calendarListEntry->getSummary(),$calendarListEntry->getId());
    //array_push($response,$calendarListEntry->getSummary(),$calendarListEntry->getId());
  }
  $pageToken = $calendarList->getNextPageToken();
  if ($pageToken) {
    $optParams = array('pageToken' => $pageToken);
    $calendarList = $service->calendarList->listCalendarList($optParams);
  } else {
    break;
  }
}
//$_SESSION['token']=$response;
//print_r($response);
// $response;
//return $response;


     //echo "get token".$_SERVER['QUERY_STRING'];
     //exit();
    //     $i=1;
    //         while(true) {
    //             foreach ($events->getItems() as $event) {
    //                 if($event->getSummary()!='')
    //                 echo "<br>".$i." )".$event->getSummary()."=>".$event->getId();
    //                 $i++;
    //                         }
    //                 $pageToken = $events->getNextPageToken();
    //                     if ($pageToken) {
    //                     $optParams = array('pageToken' => $pageToken);
    //                         $events = $service->events->listEvents('primary', $optParams);
    //                                     } else {
    //                                                 break;
    //                                             }
    //                     }

//==================
//$service->events->delete('primary', '5a17v4eg3gajh0ba5255079uf0');

// For Update Event
//     $_SESSION['token'] = $client->getAccessToken();

// // First retrieve the event from the API.
// $event = $service->events->get('primary', '5a17v4eg3gajh0ba5255079uf0');

// $event->setSummary('I want to ......');

// $updatedEvent = $service->events->update('primary', $event->getId(), $event);

// // Print the updated date.
// echo $updatedEvent->getUpdated();



// For Add Event
// $event = new Google_Service_Calendar_Event(array(
//   'summary' => 'GGGGG',
//   'location' => '800 Howard St., San Francisco, CA 94103',
//   'description' => 'A chance to hear more about Google\'s developer products.',
//   'start' => array(
//     'dateTime' => '2016-07-15T09:00:00-07:00',
//     'timeZone' => 'America/Los_Angeles',
//   ),
//   'end' => array(
//     'dateTime' => '2016-07-15T17:00:00-07:30',
//     'timeZone' => 'America/Los_Angeles',
//   ),
//   'recurrence' => array(
//     'RRULE:FREQ=DAILY;COUNT=2'
//   ),
//   'attendees' => array(
//     array('email' => 'harishmahajan@jacksolutions.biz'),
//     array('email' => 'harishmahajan@jacksolutions.biz'),
//   ),
//   'reminders' => array(
//     'useDefault' => FALSE,
//     'overrides' => array(
//       array('method' => 'email', 'minutes' => 24 * 60),
//       array('method' => 'popup', 'minutes' => 10),
//     ),
//   ),
// ));

// $calendarId = 'harishmahajan@jacksolutions.biz';
// $event = $service->events->insert($calendarId, $event);
// printf('Event created: %s\n', $event->htmlLink);


} else {
    $authUrl = $client->createAuthUrl();
    //echo $authUrl;
     print "<a class='login' href='$authUrl' id='authuser'>Connect Me!</a>";

}



?>

