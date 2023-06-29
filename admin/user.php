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
require_once($basedir.'/include/db_tkeys.inc.php');

$name = $_GET["name"];

error_log('user: '.print_r($_GET,true));


$user = $orm->create(User::class);
// get iduser by token. only valid for 1 hour!
$user =  $orm(User::class)->where('name')->is($name)
->get();

if($user==null) {
    // todo: fail gracefully
    die;
}

$userKeys = $orm(Keys::class)->where('fkiduser')->is($user->getId())
->get();



?>{
	"@context": [
		"https://www.w3.org/ns/activitystreams",
		"https://w3id.org/security/v1"
	],

	"id": "<?= $serverName ?>/user/<?= $name ?>",
	"type": "Person",
	"preferredUsername": "<?= $name ?>",
	"inbox": "<?= $serverName ?>/inbox/<?= $name ?>",
    "outbox": "<?= $serverName ?>/outbox/<?= $name ?>",
	"publicKey": {
		"id": "<?= $serverName ?>/user/<?= $name ?>#main-key",
		"owner": "<?= $serverName ?>/user/<?= $name ?>",
		"publicKeyPem": "<?= $userKeys->getPublickey() ?>"
	}
}