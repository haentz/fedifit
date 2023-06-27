<?php
// https://developers.strava.com/docs/webhooks/

//https://www.curtiscode.dev/post/project/displaying-strava-stats-using-webhooks/
require_once('../include/db.inc.php');
include $basedir.'/vendor/autoload.php';
require_once($basedir.'/include/db_tuser.inc.php');
require_once($basedir.'/include/db_tactivity.inc.php');
require_once($basedir.'/include/lib_strava.php');
require_once('../include/images.inc.php');


use Strava\API\OAuth;
use Strava\API\Exception;
use Strava\API\Service\REST;
use Strava\API\Client;

use \DantSu\OpenStreetMapStaticAPI\OpenStreetMap;
use \DantSu\OpenStreetMapStaticAPI\LatLng;
use \DantSu\OpenStreetMapStaticAPI\Line;
use \DantSu\OpenStreetMapStaticAPI\Markers;





if (isset($_GET['hub_challenge'])) {
    $data = ['hub.challenge' => $_GET['hub_challenge']];
    error_log("webhook challenge: ");
    header("HTTP/1.1 200 OK");
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    
    exit;
} else {
    $jsonData= json_decode(file_get_contents("php://input"));
   
    error_log("New webhook call: " . print_r($jsonData,true));

    if($jsonData->aspect_type=="create") {
        
        $user =  $orm(User::class)->where('strava_athlete_id')->is($jsonData->owner_id)
        ->get();



        // get activity for $jsonData->object_id
        $stravaActivity = getNewActivity($jsonData->object_id, $jsonData->owner_id);
     
        $text = $stravaActivity->name;
        $heroimageFilename = "";
        if($stravaActivity->summary_polyline!="") {
                error_log("strava activity has map data",0);
                
                $heroimageFilename = hash('ripemd128', "heroimagesalt".$user->getId().time()).".jpg"; 
                
                if(saveRouteToImage($stravaActivity->summary_polyline,$heroimageFilename)) {
                    $activity->setHeroImage($heroimageFilename);
                }
                
        } else {
                error_log("strava activity does not have map data",0);
                $text.="<br>soundsoviele km";
        }



        $activity = $orm->create(ActivityTable::class);
        $activity->setFkiduser($user->getId());
        $activity->setCreationdate(new DateTime());
        $activity->setStrava_activity_id($stravaActivity->stravaId);
        $activity->setText($text);
        $activity->setHeroImage($heroimageFilename);
        $activity->setReleased(1);
        $activity->setDownloaded(1);
        error_log(print_r($activity,true));
     
        $orm->save($activity);
/*
 stdClass Object
(
    [aspect_type] => create
    [event_time] => 1687785982
    [object_id] => 9337700014
    [object_type] => activity
    [owner_id] => 2321457
    [subscription_id] => 243913
    [updates] => stdClass Object
        (
        )

)*/


    } else if($jsonData->aspect_type=="update") {

        $stravaActivity = getNewActivity($jsonData->object_id, $jsonData->owner_id);
       
        

/*

(
    [aspect_type] => update
    [event_time] => 1687785953
    [object_id] => 9337696475
    [object_type] => activity
    [owner_id] => 2321457
    [subscription_id] => 243913
    [updates] => stdClass Object
        (
            [title] => Afternoon Ride1 test
        )

)"
*/



    } else if($jsonData->aspect_type=="delete") {

        /*
(
    [aspect_type] => delete
    [event_time] => 1687780789
    [object_id] => 9336885235
    [object_type] => activity
    [owner_id] => 2321457
    [subscription_id] => 243913
    [updates] => stdClass Object
        (
        )

)
        */

    }
    //     $activities = $client->getAthleteActivities();

}
// if(count($activities)>0) {
//     // todo check if strava_activity_id already in db!

//     foreach($activities as $activity) {
//         $activity = $orm->create(DBActivity::class);
//         $activity->setFkiduser($user->getId());
//         $activity->setCreationdate(new DateTime());
//         $activity->setStrava_activity_id($stravaActivity["id"]);
//         $activity->setReleased(0);
//         $activity->setDownloaded(0);
//         $orm->save($activity);
//     }

// do this with curl in command line:
// $client_id = '5';
// $client_secret = $STRAVA_CLIENT_SECRET;
// $callback_url = 'http://a-valid.com/url/webhook.php'; /* this should be the same URL as this page */
// $verify_token = 'STRAVA';

// curl -X POST https://www.strava.com/api/v3/push_subscriptions \
// -F client_id=$STRAVA_CLIENT_ID \
// -F client_secret=$STRAVA_CLIENT_SECRET  \
// -F callback_url=https://567c-95-89-45-59.ngrok-free.app/admin/create_strava_webhook_subscription.php \
// -F verify_token=$STRAVA_VERIFY_TOKEN


// $curl = curl_init();

// curl_setopt_array($curl, array(
//   CURLOPT_URL => "https://www.strava.com/api/v3/push_subscriptions?client_id={$client_id}&client_secret={$client_secret}&callback_url={$callback_url}&verify_token={$verify_token}",
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'POST',
// ));

// $response = curl_exec($curl);

// curl_close($curl);

// echo $response; /* will return the id or an error message */

// store and/or process id


// further messages rom strava appear at this url with POST reqeusts
// write all content of POSTS to textfiles





// test create webhook
//curl -X POST https://567c-95-89-45-59.ngrok-free.app/admin/create_strava_webhook_subscription.php -H 'Content-Type: application/json' -d '{ "aspect_type": "create", "event_time": 1687787304,"object_id": 9332359898,"object_type": "activity","owner_id": 2321457,"subscription_id": 243913}'

?> 