<?php
require_once('../include/db.inc.php');
require_once($basedir.'/include/loggedin.inc.php');
include $basedir.'/vendor/autoload.php';
require_once($basedir.'/include/db_tuser.inc.php');

use Strava\API\OAuth;
use Strava\API\Exception;

$error = $_GET['p'];
// get user object from db


// check if user connected to strava
$user = $orm->create(User::class);


// get iduser by token. only valid for 1 hour!
$user =  $orm(User::class)->where('id')->is($iduser)
->get();



?><?php include($basedir."/include/nav.inc.php") ?>

<h1>members area</h1>
<?php 
// user did not get permissions on strava and was redirected here
if($error==1) { ?>
<b>Please set sufficient permissions on Strava.</b><br>

<?php } ?>
Hello <?= $user->getName() ?>!
<?php

if ($user->getStravaAccessToken()==null) {
// if not connected to strava… {}

    try {
        $options = [
            'clientId'     => $stravaCLientID,
            'clientSecret' => $stravaAppToken,
            'redirectUri'  => $stravaRedirectURI
        ];
        $oauth = new OAuth($options);

    
            print '<div class=\"authorize_button\"><a href="'.$oauth->getAuthorizationUrl([
                // Uncomment required scopes.
                'scope' => [
                    //'read',
                    // 'read_all',
                    // 'profile:read_all',
                    // 'profile:write',
                    'activity:read',
                    // 'activity:read_all',
                    // 'activity:write',
                ]
            ]).'">Connect to Strava</a></div>';
    
    } catch(Exception $e) {
        print $e->getMessage();
    }
} else  { ?>

Recent rides synced from Strava: <br>
<ul class="ridelist">




</ul>


<?php
} // ende else


// show last x posts, pagination, delete individual posts



// link to "delete all posts"
// link to disconnect strava
// link to "delete account"
?>