<?php
require_once($basedir.'/include/loggedin.inc.php');
include $basedir.'/vendor/autoload.php';
require_once($basedir.'/include/db.inc.php');
require_once($basedir.'/include/db_tuser.inc.php');

use Strava\API\OAuth;
use Strava\API\Exception;


// get user object from db


// check if user connected to strava
$user = $orm->create(User::class);


// get iduser by token. only valid for 1 hour!
$user =  $orm(User::class)->where('id')->is($iduser)
->get();



?><?php include($basedir."/include/nav.inc.php") ?>

<h1>members area</h1>
<?= $user->getName() ?>
<?php


// if not connected to strava… {}

?>

Connect Strava


<?php

// else 


// show last x posts, pagination, delete individual posts
// link to "delete all posts"
// link to disconnect strava
// link to "delete account"
?>