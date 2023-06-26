<?php
include $basedir.'/vendor/autoload.php';
require_once('../include/db.inc.php');
use Strava\API\OAuth;
use Strava\API\Exception;
use Strava\API\Service\REST;
use Strava\API\Client;

//refresh accesstoken
function refreshAccessToken(int $stravaIdUser, String $refreshToken) {
    global $orm;

    global $STRAVA_CLIENT_ID;
    global $STRAVA_CLIENT_SECRET;
    global $stravaRedirectURI;

    $options = [
        'clientId'     => $STRAVA_CLIENT_ID,
        'clientSecret' => $STRAVA_CLIENT_SECRET,
        'redirectUri'  => $stravaRedirectURI
    ];
 

    $oauth = new OAuth($options);
    // request tokens
    $token = $oauth->getAccessToken('refresh_token', [
        'refresh_token' => $refreshToken
    ]);



//https://oauth2-client.thephpleague.com/usage/

   $user =  $orm(User::class)->where('strava_athlete_id')->is($stravaIdUser)
        ->get();

    $user->setStravaAccessToken($token->getToken());
    $expiretime=new DateTime();
    $expiretime->setTimestamp($token->getExpires());
    $user->setStravaExpirationdate($expiretime);
    $user->setStravaRefreshToken($token->getRefreshToken());
    $user->setStravaId($stravaIdUser);

    $orm->save($user); 

    error_log("new access token: ".print_r($user,true));
   
}



function getNewActivity($activityId, $iduser) {
    //test if $iduser accesstioken still valid
    global $orm;

    $user =  $orm(User::class)->where('id')->is($iduser)
    ->get();

    $accesstokenValidtill = $user->getStravaExpirationdate();
    error_log("getting new activity, accesstokenvalid?? :".print_r($accesstokenValidtill,true).":".
    ($accesstokenValidtill<new DateTime()?"expired":"not expired"));
    if($accesstokenValidtill<new DateTime()) {
        //expired
        refreshAccessToken($user->getStravaId(),$user->getStravaRefreshToken());
        $user =  $orm(User::class)->where('id')->is($iduser)
        ->get();
    
    }


    //refresh access token
    //get activity
    // save activity to db

}

function updateActivity($activityId, $accessToken) {


}


?>