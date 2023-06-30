<?php
/* 

rewrite /.well-known/webfinger to /.well-known/webfinger.php
nginx:
       location /.well-known/webfinger {
                try_files $uri $uri/ /.well-known/webfinger.php?$args;
        }

*/

require_once('../include/db.inc.php');
require_once($basedir.'/vendor/autoload.php');
require_once($basedir.'/include/db_tuser.inc.php');

error_log('webfinger: '.print_r($_GET,true));


$resource = $_GET["resource"];
$parts = explode('@', $resource);

$name = $parts[0];
$domain = "@" . $parts[1];

$parts = explode(':', $name);
$name = $parts[1];


if($domain!="@567c-95-89-45-59.ngrok-free.app") {
    // todo: handel graceful!!!!
    die;
}


error_log($name);
$user = $orm->create(User::class);
// get iduser by token. only valid for 1 hour!
$user =  $orm(User::class)->where('name')->is($name)
->get();


if($user==null) {
    // todo: handel graceful!!!!
    die;
}


?>{
	"subject": "acct:<?= $user->getName() ?>@567c-95-89-45-59.ngrok-free.app",

	"links": [
		{
			"rel": "self",
			"type": "application/activity+json",
			"href": "<?= $serverName  ?>/user/<?= $user->getName() ?>"
		}
	]
}