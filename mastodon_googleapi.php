<?php
require_once(__DIR__ . '/googleapi.config.php');
require_once(__DIR__ . '/google-api-php-client/src/Google/autoload.php');

define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR_READONLY)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = file_get_contents($credentialsPath);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->authenticate($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, $accessToken);
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->refreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, $client->getAccessToken());
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);

// Print the next 10 events on the user's calendar.
$optParams = array(
  'maxResults' => 10,
  'orderBy' => 'startTime',
  'singleEvents' => TRUE,
  'timeMin' => date('c'),
);
$results = $service->events->listEvents(CALENDAR_ID_ZEMI, $optParams);

// 直近の定期ゼミ
$upcoming_teizemi = array();
// 今日が定期ゼミの日か
$is_teizemi_today = false;
// 明日は定期ゼミの日か
$is_teizemi_tomorrow = false;

$todaystr = date('Y-m-d');
$tomorrowstr = date('Y-m-d', time() + 24 * 60 * 60);
if (count($results->getItems()) == 0) {
    //print "No upcoming events found.\n";
} else {
    //print "Upcoming events:\n";
    foreach ($results->getItems() as $event) {
        if ($event->getSummary() === '定期ゼミ') {
            $datestr = substr($event->start->dateTime, 0, 10);
            //$timestamp = strtotime($datestr);
            //print date('Y/m/d H:i:s', $timestamp);
            if ($datestr === $todaystr) {
                $is_teizemi_today = true;
            }
            if ($datestr === $tomorrowstr) {
                $is_teizemi_tomorrow = true;
            }
            $upcoming_teizemi[] = array(
                'summary' => $event->getSummary(),
                'date' => $datestr
            );
        }
    }
}
//var_dump($upcoming_teizemi);
//var_dump($is_teizemi_today);
//var_dump($is_teizemi_tomorrow);