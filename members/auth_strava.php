<?php
require_once('../include/db.inc.php');
require_once($basedir.'/include/loggedin.inc.php');
include $basedir.'/vendor/autoload.php';

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
    'clientId'     => $stravaCLientID,
    'clientSecret' => $stravaAppToken,
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
        $user->setStravaRefreshToken($token->getRefreshToken());
        $user->setStravaExpirationdate($expiretime);
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



                $stravaActivity = $activities[0];
                
                $activity = $orm->create(Activity::class);
                $activity->setFkiduser($user->getId());
                $activity->setCreationdate(new DateTime());
                $activity->setStrava_activity_id($stravaActivity["id"]);
                $activity->setText($stravaActivity["type"]." ".$stravaActivity["name"]);
                $heroimageFilename = hash('ripemd128', "heroimagesalt".$user->getId().time()).".jpg"; 
                $activity->setHeroImage($heroimageFilename);

                $orm->save($activity);

                //do we have a map?
                if($stravaActivity["map"]["summary_polyline"]!="") {
                    error_log("strava activity has map data",0);
                    $points = Polyline::decode($stravaActivity["map"]["summary_polyline"]);
                    $points = Polyline::pair($points);
            
                    // get bounding box
                    $polygon = new \League\Geotools\Polygon\Polygon(
                        $points
                    );
                    $boundingBox = $polygon->getBoundingBox();

                   
                    // calcualte middle point of map
                    $geotools   = new \League\Geotools\Geotools();
                    $southWest = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getSouth(), $boundingBox->getwest()]);
                    $northEast = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getNorth(), $boundingBox->getEast()]);
                    $coordA   = $southWest;
                    $coordB   = $northEast;
                    $vertex    =  $geotools->vertex()->setFrom($coordA)->setTo($coordB);
                    $middlePoint = $vertex->middle(); // \League\Geotools\Coordinate\Coordinate

                    // calculate zoom

                 // TODO: is this right??


                    $zoomLevel = 12;
                    $latDiff = $northEast->getLatitude() - $southWest->getLatitude();
                    $lngDiff = $northEast->getLongitude() - $southWest->getLongitude();
                    
                    $maxDiff=$lngDiff>$latDiff?$lngDiff:$latDiff;
                     if ($maxDiff < 360 / pow(2, 20)) {
                        $zoomLevel = 21;
                    } else {
                        $zoomLevel = (int) (-1*( (log($maxDiff)/log(2)) - (log(360)/log(2))));
                        if ($zoomLevel < 1)
                            $zoomLevel = 1;
                    }
                    $zoomLevel++;
                   // print_r($boundingBox);
//League\Geotools\BoundingBox\BoundingBox Object ( [north:League\Geotools\BoundingBox\BoundingBox:private] => 48.08094 [east:League\Geotools\BoundingBox\BoundingBox:private] => 11.52298 [south:League\Geotools\BoundingBox\BoundingBox:private] => 48.08094 [west:League\Geotools\BoundingBox\BoundingBox:private] => 11.52298 [hasCoordinate:League\Geotools\BoundingBox\BoundingBox:private] => 1 [ellipsoid:League\Geotools\BoundingBox\BoundingBox:private] => League\Geotools\Coordinate\Ellipsoid Object ( [name:protected] => WGS 84 [a:protected] => 6378137 [invF:protected] => 298.257223563 ) [precision:League\Geotools\BoundingBox\BoundingBox:private] => 8 )
        
//



$lineToDraw = new Line('FF0000', 2);

foreach($points as $point) {
    
    $lineToDraw->addPoint(new LatLng($point[0], $point[1]));
}



    $image = (new OpenStreetMap(new LatLng($middlePoint->getLatitude(), $middlePoint->getLongitude()), $zoomLevel, 800, 800))
   
    ->addDraw($lineToDraw)
    ->getImage();

    $image->drawRectangle(0, 600, 800, 800, '#FF0000DD');


    $image->saveJPG('../images/'.$heroimageFilename,82);

    

    
  
 
                } else {
                    error_log("strava activity does not have map data",0);

                }
             

                //                 Array
                // (
                //     [resource_state] => 2
                //     [athlete] => Array
                //         (
                //             [id] => 2321457
                //             [resource_state] => 1
                //         )

                //     [name] => Pullach im Isartal -  Evening Gravel Ride
                //     [distance] => 5000.5
                //     [moving_time] => 1273
                //     [elapsed_time] => 1297
                //     [total_elevation_gain] => 17
                //     [type] => Ride
                //     [sport_type] => GravelRide
                //     [workout_type] => 
                //     [id] => 9308924239
                //     [start_date] => 2023-06-21T17:18:45Z
                //     [start_date_local] => 2023-06-21T19:18:45Z
                //     [timezone] => (GMT+01:00) Europe/Berlin
                //     [utc_offset] => 7200
                //     [location_city] => 
                //     [location_state] => 
                //     [location_country] => Germany
                //     [achievement_count] => 0
                //     [kudos_count] => 2
                //     [comment_count] => 0
                //     [athlete_count] => 1
                //     [photo_count] => 0
                //     [map] => Array
                //         (
                //             [id] => a9308924239
                //             [summary_polyline] => {xmdHsqieA_ArBYd@_@v@o@hAe@pAwB|Ee@|AWj@Mb@e@hB]`Ag@~AQd@sAnF_AjDQf@Oz@y@vC{@nDkAbEoAdFUt@Qv@Eb@Sv@AJ@JNTd@Tj@b@l@j@fApAV^Xv@Zh@X\j@f@T`@`@d@XTJLTx@P`AJXb@jCr@hHJrB@n@DTHFD?^G`@C\KbAIF@D^JTJj@PlAL\RZpCfC\Ft@`An@p@FTGd@@l@Yn@ANTj@XtA@z@HfANx@BDH@XGN@j@Vx@J`@Bb@HPN^Np@J^CJ@JNHPDj@Jp@?lDCTCp@AnBG`@Ux@GPS\wApE}@`CS^K^[|@]dAu@fAQ?_@OG?GBMNw@rBs@`B_@hAeCnG[j@Cd@R\DXAPMPCXElCBl@Ed@?~@Y~BAPBz@Cl@KFMBeCHkACMJGP
                //             [resource_state] => 2
                //         )

                //     [trainer] => 
                //     [commute] => 
                //     [manual] => 
                //     [private] => 
                //     [visibility] => everyone
                //     [flagged] => 
                //     [gear_id] => b6717517
                //     [start_latlng] => Array
                //         (
                //             [0] => 48.08
                //             [1] => 11.53
                //         )

                //     [end_latlng] => Array
                //         (
                //             [0] => 48.09
                //             [1] => 11.48
                //         )

                //     [average_speed] => 3.928
                //     [max_speed] => 6.492
                //     [average_cadence] => 53.6
                //     [average_temp] => 28
                //     [average_watts] => 53.3
                //     [max_watts] => 330
                //     [weighted_average_watts] => 74
                //     [kilojoules] => 67.8
                //     [device_watts] => 1
                //     [has_heartrate] => 
                //     [heartrate_opt_out] => 
                //     [display_hide_heartrate_option] => 
                //     [elev_high] => 574.8
                //     [elev_low] => 564.6
                //     [upload_id] => 9985732414
                //     [upload_id_str] => 9985732414
                //     [external_id] => garmin_ping_280705960980
                //     [from_accepted_tag] => 
                //     [pr_count] => 0
                //     [total_photo_count] => 0
                //     [has_kudoed] => 

 

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