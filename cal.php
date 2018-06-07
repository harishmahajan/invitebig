
<?php
require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
define('CREDENTIALS_PATH', '/.credentials/calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/cs.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR_READONLY)
));
$service = new Google_CalendarService($client);
$events = $service->events->listEvents('primary');

while(true) {
  foreach ($events->getItems() as $event) {
    echo $event->getSummary();
  }
  $pageToken = $events->getNextPageToken();
  if ($pageToken) {
    $optParams = array('pageToken' => $pageToken);
    $events = $service->events->listEvents('primary', $optParams);
  } else {
    break;
  }
}
// if (php_sapi_name() != 'cli') {
//   throw new Exception('This application must be run on the command line.');
// }

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
// function getClient() {
//   $client = new Google_Client();
//   $client->setApplicationName(APPLICATION_NAME);
//   $client->setScopes(SCOPES);
//   $client->setAuthConfigFile(CLIENT_SECRET_PATH);
//   $client->setAccessType('online');

//   // Load previously authorized credentials from a file.
//   $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
//   if (file_exists($credentialsPath)) {
//     $accessToken = file_get_contents($credentialsPath);
//   } else {
//     // Request authorization from the user.
//     $authUrl = $client->createAuthUrl();
//     printf("Open the following link in your browser:\n%s\n", $authUrl);
//     print 'Enter verification code: ';
//     $authCode = trim(fgets(STDIN));

//     // Exchange authorization code for an access token.
//     $accessToken = $client->authenticate($authCode);

//     // Store the credentials to disk.
//     if(!file_exists(dirname($credentialsPath))) {
//       mkdir(dirname($credentialsPath), 0700, true);
//     }
//     file_put_contents($credentialsPath, $accessToken);
//     printf("Credentials saved to %s\n", $credentialsPath);
//   }
//   $client->setAccessToken($accessToken);

//   // Refresh the token if it's expired.
//   if ($client->isAccessTokenExpired()) {
//     $client->refreshToken($client->getRefreshToken());
//     file_put_contents($credentialsPath, $client->getAccessToken());
//   }
//   return $client;
// }

// /**
//  * Expands the home directory alias '~' to the full path.
//  * @param string $path the path to expand.
//  * @return string the expanded path.
//  */
// function expandHomeDirectory($path) {
//   $homeDirectory = getenv('HOME');
//   if (empty($homeDirectory)) {
//     $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
//   }
//   return str_replace('~', realpath($homeDirectory), $path);
// }

// // Get the API client and construct the service object.
// $client = getClient();
// $service = new Google_Service_Calendar($client);

// // Print the next 10 events on the user's calendar.
// $calendarId = 'primary';
// $optParams = array(
//   'maxResults' => 10,
//   'orderBy' => 'startTime',
//   'singleEvents' => TRUE,
//   'timeMin' => date('c'),
// );
// $results = $service->events->listEvents($calendarId, $optParams);

// if (count($results->getItems()) == 0) {
//   print "No upcoming events found.\n";
// } else {
//   print "Upcoming events:\n";
//   foreach ($results->getItems() as $event) {
//     $start = $event->start->dateTime;
//     if (empty($start)) {
//       $start = $event->start->date;
//     }
//     printf("%s (%s)\n", $event->getSummary(), $start);
//     //printf("%s (%s)\n", $event->getId());
//   }

  // First retrieve the event from the API.
// $event = $service->events->get('primary', 'araj0roetan4soh5bckk2svq18_20160924T063000Z');

// $event->setSummary('Appointment at Somewhere');

// $updatedEvent = $service->events->update('primary', $event->getId(), $event);

// // Print the updated date.
// echo $updatedEvent->getUpdated();
//}



//$event = $service->events->get('primary', "araj0roetan4soh5bckk2svq18_20160924T063000Z");

//printf("%s \n",$event->getSummary());

// First retrieve the event from the API.
// $event = $service->events->get('primary', 'araj0roetan4soh5bckk2svq18_20160924T063000Z');

// $event->setSummary('Appointment at Somewhere');

// $updatedEvent = $service->events->update('primary', $event->getId(), $event);

// $calendarListEntry = $service->calendarList->get('primary');
// $calendarListEntry->setSummary('Appointment at Somewhere');

// $updatedCalendarListEntry = $service->calendarList->update($calendarListEntry->getId(), $calendarListEntry);

// Print the updated date.


//$event = $service->events->get('primary', 'araj0roetan4soh5bckk2svq18_20160924T063000Z');

//printf("%s \n",$event->getSummary());

?>