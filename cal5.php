<?php
require_once('vendor/google/apiclient/src/Google/autoload.php');
require_once 'vendor/google/apiclient/src/Google/Client.php';
require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';
session_start();
$client = new Google_Client();
$client->setApplicationName("InviteBig");
$client->setClientId("41840486781-6fit272os86kor3s4d2iebail0hmk1fj.apps.googleusercontent.com");
$client->setClientSecret('vzWMOiSvQEZ6Cqn3zpceMdiE');
//$client->setDeveloperKey('DEVELOPER KEY');
$client->setRedirectUri('http://myinvitebig.com/cal5.php');
//$client->setRedirectUri('https://d.invitebig.com/cal5.php');
$client->setScopes(array(
    'https://www.googleapis.com/auth/calendar',
    'https://www.googleapis.com/auth/calendar.readonly',
));
$client->setAccessType("offline");
$client->setApprovalPrompt("auto");

// Below function is used for getting calender's list and save to table
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
  $sqlSelect = "SELECT * FROM googleCallist WHERE calid = '".$cld."' and userid = ".$_SESSION['userid'];
  $resultSelect = $conn->query($sqlSelect);
  if($resultSelect->num_rows > 0)
  {
    // Means Already inserted into table 
  }
  else
  {
     $token = json_decode($_SESSION['token']);    
     $access_token = $token->access_token;
     $refresh_token = $token->refresh_token;
      //$sql = "INSERT INTO googleCallist (calName, userid, calid) VALUES ("."'".$calname."',". $_SESSION['userid'].",'".$cld."')";
     $sql = "INSERT INTO googleCallist (calName, userid, calid, access_token, refresh_token) VALUES ("."'".$calname."',". $_SESSION['userid'].",'".$cld."','". $access_token ."','".$refresh_token."')";
     mysqli_query($conn, $sql);
   }
 }

 function getCalenderEvents()
 {
 }

//  function clearData()
//  {
//   // $servername = "localhost";
//   // $username = "root";
//   // $password = "W2GC@zF!6c7%";
//   // $dbname = "invitebignew";
//   $servername = "localhost";
//   $username = "root";
//   $password = "jack123";
//   $dbname = "invitebignew";

//   $conn = mysqli_connect($servername, $username, $password, $dbname);
//   $sqlDelete = "DELETE FROM googleCallist WHERE userid=".$_SESSION['userid'];
// }

$response=array();
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['token'] = $client->getAccessToken();

  if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
  }
}
if ($client->getAccessToken()) {
  $service = new Google_Service_Calendar($client);
  $calendarList = $service->calendarList->listCalendarList();

  while(true) {
    foreach ($calendarList->getItems() as $calendarListEntry) {
      setData($calendarListEntry->getSummary(),$calendarListEntry->getId());
    }

    $pageToken = $calendarList->getNextPageToken();
    if ($pageToken) {
      $optParams = array('pageToken' => $pageToken);
      $calendarList = $service->calendarList->listCalendarList($optParams);
    } else {
      break;
    }
  }
} else {
  $authUrl = $client->createAuthUrl();
  echo $authUrl;
  // print "<a class='login' href='$authUrl' id='authuser'>Connect Me!</a>";
}
?>

