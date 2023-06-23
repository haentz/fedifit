<?php
require_once('../include/db.inc.php');
require_once($basedir.'/include/loggedin.inc.php');
include $basedir.'/vendor/autoload.php';

require_once($basedir.'/include/db_tuser.inc.php');

use Strava\API\OAuth;
use Strava\API\Exception;
use Strava\API\Service\REST;
use Strava\API\Client;

//print_r($_GET);
//Array ( [state] => b2912cd7a79f2525a8dc3c1d1be34038 [code] => 7771f377e08337d1224bc9d75394279d149fd5ab [scope] => read,activity:read ) 86f19527f724c7f14ab6628b1da17dbd10e03251

// test if user actually did give permissions
if($_GET['scope']!="read,activity:read") {
    header('Location: /members/?p=1');
    die();
} else {


    $options = [
        'clientId'     => $stravaCLientID,
        'clientSecret' => $stravaAppToken,
        'redirectUri'  => $stravaRedirectURI
    ];
    $oauth = new OAuth($options);

    if (isset($_GET['code'])) {
        $token = $oauth->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        
        
        try {
            $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
            $service = new REST($token->getToken(), $adapter);  // Define your user token here.
            $client = new Client($service);
        
            $athlete = $client->getAthlete();
            print_r($athlete);
        
            $activities = $client->getAthleteActivities();
            print_r($activities);
        
            $club = $client->getClub(9729);
            print_r($club);
        } catch(Exception $e) {
            print $e->getMessage();
        }


    }

}

?>