<?php
error_reporting(E_ERROR  | E_PARSE);

require_once('vendor/autoload.php');


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