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

$resource = $_GET["resource"];
$parts = explode('@', $resource);

$name = $parts[0];
$domain = "@" . $parts[1];
$server = $_SERVER;
if($domain!="@bikelog.de") {
    // todo: handel graceful!!!!
    die;
}


$user = $orm->create(User::class);
// get iduser by token. only valid for 1 hour!
$user =  $orm(User::class)->where('name')->is($name)
->get();

?>{
	"subject": "acct:<?= $user->getName() ?>@bikelog.de",

	"links": [
		{
			"rel": "self",
			"type": "application/activity+json",
			"href": "<?= $server  ?>/user/<?= $user->getName() ?>"
		}
	]
}