<?php

require_once('vendor/autoload.php');


use Opis\Database\Connection;

use Opis\ORM\{
    Entity, 
    EntityManager,
    IEntityMapper,
    IMappableEntity
};

//  // Define a database connection
$connection = new Connection("mysql:host=localhost;dbname=activities", "activities_app", "password");
  
  
//  // Create an entity manager
$orm = new EntityManager($connection);








?>