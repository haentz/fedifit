<?php
require_once('../include/db.inc.php');
include $basedir.'/vendor/autoload.php';


require_once('../include/images.inc.php');

require_once($basedir.'/include/loggedin.inc.php');

require_once($basedir.'/include/db_tuser.inc.php');
require_once($basedir.'/include/db_tactivity.inc.php');



use Strava\API\OAuth;
use Strava\API\Exception;
use Strava\API\Service\REST;
use Strava\API\Client;

use \DantSu\OpenStreetMapStaticAPI\OpenStreetMap;
use \DantSu\OpenStreetMapStaticAPI\LatLng;
use \DantSu\OpenStreetMapStaticAPI\Line;
use \DantSu\OpenStreetMapStaticAPI\Markers;





//print_r($_GET);
//Array ( [state] => b2912cd7a79f2525a8dc3c1d1be34038 [code] => 7771f377e08337d1224bc9d75394279d149fd5ab [scope] => read,activity:read ) 86f19527f724c7f14ab6628b1da17dbd10e03251

// configuration for the oauth calls
$options = [
    'clientId'     => $STRAVA_CLIENT_ID,
    'clientSecret' => $STRAVA_CLIENT_SECRET,
    'redirectUri'  => $stravaRedirectURI
];


// test if user actually did give permissions
// if parameters scope or error passed, its a redirect to the initial permission request:
if(($_GET['scope']!="" && $_GET['scope']!="read,activity:read") || $_GET['error']!="") {
    header('Location: /members/?p=1');
    die();
} 
// now if user gave correct permissions and strava sent $code for generation of actual tokens:
else  if (isset($_GET['code'])) {
    
    $oauth = new OAuth($options);
    // request tokens
    $token = $oauth->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);
    //TODO: check if $token valid. bad documentation $token==null probably?


    //insert first tokens in DB
    // TODO: this needs to be outsiode for the refresh case:

           // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        // echo 'Access Token: ' . $token->getToken() . "<br>";
        // echo 'Refresh Token: ' . $token->getRefreshToken() . "<br>";
        // echo 'Expired in: ' . $token->getExpires() . "<br>";
        // echo 'Already expired? ' . ($token->hasExpired() ? 'expired' : 'not expired') . "<br>";

            // check if user connected to strava
        $user = $orm->create(User::class);

        //print_r($token);
        /* example response of oauth call
League\OAuth2\Client\Token\AccessToken Object ( [accessToken:protected] => 1ee46e4f9ed32ad7a57e39e6e8ec282297e28e60 [expires:protected] => 1687609398 [refreshToken:protected] => 4b8f8fa7c0b43ceeb2b28bcdf475829ac395e79f [resourceOwnerId:protected] => [values:protected] => Array ( [token_type] => Bearer [expires_at] => 1687609398 [athlete] => Array ( [id] => 2321457 [username] => hansschneider [resource_state] => 2 [firstname] => Hans [lastname] => Fritz [bio] => [city] => Munich [state] => Bavaria [country] => Germany [sex] => M [premium] => 1 [summit] => 1 [created_at] => 2013-06-10T11:00:20Z [updated_at] => 2023-06-23T11:28:12Z [badge_type_id] => 1 [weight] => 97.6 [profile_medium] => https://dgalywyr863hv.cloudfront.net/pictures/athletes/2321457/1629111/3/medium.jpg [profile] => https://dgalywyr863hv.cloudfront.net/pictures/athletes/2321457/1629111/3/large.jpg [friend] => [follower] => ) ) ) 

        */
        // get iduser by token. only valid for 1 hour!
        $user =  $orm(User::class)->where('id')->is($iduser)
        ->get();

        $user->setStravaAccessToken($token->getToken());
        $expiretime=new DateTime();
        $expiretime->setTimestamp($token->getExpires());
        $user->setStravaExpirationdate($expiretime);
        $user->setStravaRefreshToken($token->getRefreshToken());
        $user->setStravaId($token->getValues()['athlete']['id']);

        $orm->save($user); 

        

        // TODO: import last ride from strava
        // Only last ride importet, not the complete history, since for this use case (post new rides to social feed) it doesn't make sense to have all the old rides appear. 
        // Importing the last ride might be a nice way to kickstart your feed and have SOEMTHING in.
        try {
            $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
            $service = new REST($token->getToken(), $adapter);  // Define your user token here.
            $client = new Client($service); 
        

          

            $activities = $client->getAthleteActivities();


            if(count($activities)>0) {
                // todo check if strava_activity_id already in db!

                foreach($activities as $activity) {
                    $activity = $orm->create(DBActivity::class);
                    $activity->setFkiduser($user->getId());
                    $activity->setCreationdate(new DateTime());
                    $activity->setStrava_activity_id($stravaActivity["id"]);
                    $activity->setReleased(0);
                    $activity->setDownloaded(0);
                    $orm->save($activity);
                }

                // $stravaActivity = $activities[0];
                // $activity = $orm->create(Activity::class);
                
                //  //TODO: clean up code in libraries
                //  if($stravaActivity["map"]["summary_polyline"]!="") {
                //     error_log("strava activity has map data",0);
                    
                //     $heroimageFilename = hash('ripemd128', "heroimagesalt".$user->getId().time()).".jpg"; 
                    
                //     if(saveRouteToImage($stravaActivity["map"]["summary_polyline"],$heroimageFilename)) {
                //         $activity->setHeroImage($heroimageFilename);
                //     }
                    
                // } else {
                //     error_log("strava activity does not have map data",0);

                // }
                  
                // $activity->setFkiduser($user->getId());
                // $activity->setCreationdate(new DateTime());
                // $activity->setStrava_activity_id($stravaActivity["id"]);
                // $activity->setText($stravaActivity["name"]);
                
                // $orm->save($activity);


               

 

            }
          
          
        } catch(Exception $e) {
            print $e->getMessage();
        }




} else {

    //handle refresh of tokens, this call is probably initiated from another php page (request activity in webhook)

}



// refreshing token:
// $existingAccessToken = getAccessTokenFromYourDataStore();

// if ($existingAccessToken->hasExpired()) {
//     $newAccessToken = $provider->getAccessToken('refresh_token', [
//         'refresh_token' => $existingAccessToken->getRefreshToken()
//     ]);

//     // Purge old access token and store new access token to your data store.
// }



//print_r($token);
//League\OAuth2\Client\Token\AccessToken Object ( [accessToken:protected] => 86f19527f724c7f14ab6628b1da17dbd10e03251 [expires:protected] => 1687542063 [refreshToken:protected] => 4b8f8fa7c0b43ceeb2b28bcdf475829ac395e79f [resourceOwnerId:protected] => [values:protected] => Array ( [token_type] => Bearer [expires_at] => 1687542063 [athlete] => Array ( [id] => 2321457 [username] => hansschneider [resource_state] => 2 [firstname] => Hans [lastname] => Fritz [bio] => [city] => Munich [state] => Bavaria [country] => Germany [sex] => M [premium] => 1 [summit] => 1 [created_at] => 2013-06-10T11:00:20Z [updated_at] => 2023-06-23T11:28:12Z [badge_type_id] => 1 [weight] => 97.6 [profile_medium] => https://dgalywyr863hv.cloudfront.net/pictures/athletes/2321457/1629111/3/medium.jpg [profile] => https://dgalywyr863hv.cloudfront.net/pictures/athletes/2321457/1629111/3/large.jpg [friend] => [follower] => ) ) ) 

        
    //     try {
    //         $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
    //         $service = new REST($token->getToken(), $adapter);  // Define your user token here.
    //         $client = new Client($service);
        

          

    //         $athlete = $client->getAthlete();
    //         print_r($athlete);
        
          
          
    //     } catch(Exception $e) {
    //         print $e->getMessage();
    //     }


    // }

?>