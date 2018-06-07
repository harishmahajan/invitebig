<?php

session_start();        
//require_once 'Google/Client.php';
//require_once 'Google/Service/Calendar.php';   
require_once('vendor/google/apiclient/src/Google/autoload.php');
require_once 'vendor/google/apiclient/src/Google/Client.php';
require_once 'vendor/google/apiclient/src/Google/Service/Calendar.php';

//$client_id = '1046123799103-nk421gjc2v8mlr2qnmmqaak04ntb1dbp.apps.googleusercontent.com';
//$Email_address = '1046123799103-nk421gjc2v8mlr2qnmmqaak04ntb1dbp@developer.gserviceaccount.com';     
//$key_file_location = '629751513db09cd21a941399389f33e5abd633c9-privatekey.p12';     

$client_id = '41840486781-6fit272os86kor3s4d2iebail0hmk1fj.apps.googleusercontent.com';
//$Email_address = 'harishp@invitebigproject.iam.gserviceaccount.com';     
$key_file_location = 'InvitebigProject-86ed792594a3.p12'; 
$client_secret = "vzWMOiSvQEZ6Cqn3zpceMdiE";    
$redirect_uri = "http://myinvitebig.com/oauth1.php";
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

    //$authUrl="";
    //For loging out.
    if (isset($_GET['logout'])) {
    unset($_SESSION['token']);
    }


    // Step 2: The user accepted your access now you need to exchange it.
    if (isset($_GET['code'])) {
    
    $client->authenticate($_GET['code']);  
    $_SESSION['token'] = $client->getAccessToken();
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }

    // Step 1:  The user has not authenticated we give them a link to login    
    if (!isset($_SESSION['token'])) {

    $authUrl = $client->createAuthUrl();

    print "<a class='login' href='$authUrl'>Connect Me!</a>";
    }    


    // Step 3: We have access we can now create our service
    if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
    $authUrl = $client->createAuthUrl();
    print "<a class='logout' href='href='$authUrl?logout=1'>LogOut</a><br>";    
    
    $service = new Google_Service_Calendar($client);    
    
    $calendarList  = $service->calendarList->listCalendarList();;

    while(true) {
        foreach ($calendarList->getItems() as $calendarListEntry) {

            echo $calendarListEntry->getSummary()."<br>\n";


            // get events 
            $events = $service->events->listEvents($calendarListEntry->id);


            foreach ($events->getItems() as $event) {
                echo "-----".$event->getSummary()."<br>";
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
    }
?>