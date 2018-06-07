<?php
session_start();        
//require_once 'Google/Client.php';
//require_once 'Google/Service/Calendar.php';   
require_once('vendor/google/apiclient/src/Google/autoload.php');
require_once 'vendor/google/apiclient/src/Google/Client.php';
require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';
//

/************************************************   
 The following 3 values an befound in the setting   
 for the application you created on Google      
 Developers console.         Developers console.
 The Key file should be placed in a location     
 that is not accessable from the web. outside of 
 web root.   

 In order to access your GA account you must    
 Add the Email address as a user at the     
 ACCOUNT Level in the GA admin.         
 ************************************************/
//$client_id = '111125041809995689578';
//$Email_address = 'harishp@invitebigproject.iam.gserviceaccount.com';     
//$key_file_location = 'InvitebigProject-86ed792594a3.p12';     

//$client->setApplicationName("Client_Library_Examples");
//$key = file_get_contents($key_file_location);    
// seproate additional scopes with a comma   
$scopes ="https://www.googleapis.com/auth/calendar.readonly";   

$client_id = '41840486781-6fit272os86kor3s4d2iebail0hmk1fj.apps.googleusercontent.com';
$Email_address = 'harishp@invitebigproject.iam.gserviceaccount.com';     
$key_file_location = 'InvitebigProject-86ed792594a3.p12'; 
$client_secret = "vzWMOiSvQEZ6Cqn3zpceMdiE";    
$redirect_uri = "http://myinvitebig.com/oauth1.php";
$key = file_get_contents($key_file_location);
 // $client_id = '[Your client Id]';
 //    $client_secret = '[Your Client Secret]';
 //    $redirect_uri = '[Your Redirect URI]';

    $client = new Google_Client();
    $client->setApplicationName("Client_Library_Examples");
    $client->setClientId($client_id);
    $client->setClientSecret($client_secret);
    $client->setRedirectUri('http://myinvitebig.com/oauth1.php');
    $client->setAccessType('offline');   // Gets us our refreshtoken

    $client->setScopes(array('https://www.googleapis.com/auth/calendar.readonly'));

$cred = new Google_Auth_AssertionCredentials(    
    $Email_address,      
    array($scopes),     
    $key         
    );      
$client->setAssertionCredentials($cred);
if($client->getAuth()->isAccessTokenExpired()) {        
    $client->getAuth()->refreshTokenWithAssertion($cred); 
    $_SESSION['token'] = $client->getAccessToken();
$client->setAccessToken($_SESSION['token']);
}
else
{
    $client->getAuth()->refreshTokenWithAssertion($cred);    
    $_SESSION['token'] = $client->getAccessToken();
$client->setAccessToken($_SESSION['token']); 
}
//echo "Token".$client->getRefreshToken();
if ($client->getAccessToken()) {
    //print_r($client);
     $service = new Google_Service_Calendar($client);
     //getCalenderEvents();
     $events = $service->events->listEvents('primary');
    //while(true) {

  foreach ($events->getItems() as $event) {
    echo $event->getSummary() . " ==> ";
    echo $event->end->dateTime;
    echo "<br>";
  }
  $pageToken = $events->getNextPageToken();
  if ($pageToken) {
    $optParams = array('pageToken' => $pageToken);
    $events = $service->events->listEvents('primary', $optParams);
   } 
   //else {
//     break;
//   }
// }
//      $calendarList = $service->calendarList->listCalendarList();

// while(true) {
//   //clearData();
//   foreach ($calendarList->getItems() as $calendarListEntry) {
//     echo $calendarListEntry->getSummary()."\r\n";
//   //setData($calendarListEntry->getSummary(),$calendarListEntry->getId());
//   }
//   $pageToken = $calendarList->getNextPageToken();
//   if ($pageToken) {
//     $optParams = array('pageToken' => $pageToken);
//     $calendarList = $service->calendarList->listCalendarList($optParams);
//   } else {
//     break;
//   }
// }

}  


?>
