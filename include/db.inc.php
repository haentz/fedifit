<?php
$basedir = $_SERVER['DOCUMENT_ROOT'];
require_once($basedir.'../../DO_NOT_DEPLOY.php');

error_reporting(E_ERROR  | E_PARSE);

// todo: move to own file

$serverHost = "99cf-95-89-45-59.ngrok-free.app";
$serverName = "https://".$serverHost;
$stravaRedirectURI = $serverName.'/members/auth_strava.php';




require_once($basedir.'/vendor/autoload.php');


use Opis\Database\Connection;

use Opis\ORM\{
    Entity, 
    EntityManager,
    IEntityMapper,
    IMappableEntity
};

//  // Define a database connection
$connection = new Connection("mysql:host=10.0.1.94;dbname=fedifit", "fedifit", "fit");
  
  
//  // Create an entity manager
$orm = new EntityManager($connection);








?>