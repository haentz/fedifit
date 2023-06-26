<?php

//refresh accesstoken
function refreshAccessToken(int $stravaIdUser, String $refreshToken) {

    $options = [
        'clientId'     => $STRAVA_CLIENT_ID,
        'clientSecret' => $STRAVA_CLIENT_SECRET,
        'redirectUri'  => $stravaRedirectURI
    ];

    $oauth = new OAuth($options);
    // request tokens
    $token = $oauth->getAccessToken('efresh_token', [
        'code' => $refreshToken
    ]);

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




?>