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
       
           // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        echo 'Access Token: ' . $token->getToken() . "<br>";
        echo 'Refresh Token: ' . $token->getRefreshToken() . "<br>";
        echo 'Expired in: ' . $token->getExpires() . "<br>";
        echo 'Already expired? ' . ($token->hasExpired() ? 'expired' : 'not expired') . "<br>";

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

}

?>