<?php
session_start();
$message = "";
// get user from token
require_once('include/db.inc.php');
require_once('include/db_tuser.inc.php');
require_once("include/DO_NOT_DEPLOY.php");
$user = $orm->create(User::class);

$token = $_GET["t"];

// get iduser by token. only valid for 1 hour!
$user =  $orm(User::class)->where('logintoken')->is($token)
->andWhere("logintokencreationdate")->greaterThan((new \DateTime())->modify('-1 hour'))
->get();

if($user) {
    $iduser = $user->getId();
    //login user
    $_SESSION['iduser'] = $iduser;
    
    $user->setLogintoken(0);
    $user->setLogintokencreationdate(new DateTime(null));
    $orm->save($user);
    
    //redirect user to /members/
    header('Location: /members/');
    die();
} else { 

$message = "Sorry, can't log you in. Please notice that logintokens sent to your Email are only valid for 60 Minutes!";

}
?><?= $message ?>