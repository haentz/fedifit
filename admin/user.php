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

$name = $_GET["user"];

$name = $parts[0];

$server = $_SERVER;

$user = $orm->create(User::class);
// get iduser by token. only valid for 1 hour!
$user =  $orm(User::class)->where('name')->is($name)
->get();

$userKeys = $orm(Keys::class)->where('fkiduser')->is($user->getId())
->get();

?>{
	"@context": [
		"https://www.w3.org/ns/activitystreams",
		"https://w3id.org/security/v1"
	],

	"id": "https://<?= $server ?>/user/<?= $name ?>",
	"type": "Person",
	"preferredUsername": "<?= $name ?>",
	"inbox": "https://<?= $server ?>/inbox/<?= $name ?>",

	"publicKey": {
		"id": "https://<?= $server ?>/user/<?= $name ?>#main-key",
		"owner": "https://<?= $server ?>/user/<?= $name ?>",
		"publicKeyPem": "<?= $userKeys->getPublickey() ?>"
	}
}