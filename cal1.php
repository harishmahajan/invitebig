<?php         
require_once 'vendor/google/apiclient/src/Google/autoload.php';     
require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';       
 //require_once 'Google/autoload.php';
 //  session_start();     
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
// //$client = new Google_Client();
 
//setApplicationName("Client_Library_Examples");
//     $key = file_get_contents("InvitebigProject-SA.p12");    

// // separate additional scopes with a comma 
//   $Email_address="harishmahajan@jacksolutions.biz";


//     $client = new Google_Client();      
//     $client->setApplicationName("Client_Library_Examples");
  

// // separate additional scopes with a comma   
// $scopes ="https://www.googleapis.com/auth/calendar";    
// $cred = new Google_Auth_AssertionCredentials(    
//     $Email_address,      
//     array($scopes),     
//     $key         
//     );      
// $client->setAssertionCredentials($cred);
// if($client->getAuth()->isAccessTokenExpired()) {        
//     $client->getAuth()->refreshTokenWithAssertion($cred);       
// }       
// $service = new Google_Service_Calendar($client);    


session_start();      
$client = new Google_Client();
 
//setApplicationName("Client_Library_Examples");
    $key = file_get_contents("InvitebigProjectSA.json");    

// separate additional scopes with a comma 
  $Email_address="harishmahajan@jacksolutions.biz";
$scopes ="https://www.googleapis.com/auth/calendar.readonly";   
$cred = new Google_Auth_AssertionCredentials(    
    $Email_address,      
    array($scopes),     
    $key         
    );      
$client->setAssertionCredentials($cred);
if($client->getAuth()->isAccessTokenExpired()) {        
    $client->getAuth()->refreshTokenWithAssertion($cred);       
}       
$service = new Google_Service_Calendar($client);    



$calendarList->listCalendarList();

while(true) {
    foreach ($calendarList->getItems() as $calendarListEntry) {
           echo $calendarListEntry->getSummary()."\n";


            // get events 
            $events = $service->events->listEvents($calendarListEntry->id);


            foreach ($events->getItems() as $event) {
                echo "-----".$event->getSummary()."";
            }
        }
        $pageToken = $calendarList->getNextPageToken();
        if ($pageToken) {
            $optParams = array('pageToken' => $pageToken);
            $calendarList = $service->calendarList->listCalendarList($optParams);
        } else {
            break;
        }
    }
    
?>
