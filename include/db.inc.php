<?php

require_once('composer/vendor/autoload.php');

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







class Activity extends Entity  implements IMappableEntity
{


/**
     * Get user's ID
     * @return int
     */
    public function id(): int
    {
        return $this->orm()->getColumn('id');
    }
        
    /**
     * Get user's id
     * @return integer
     */
    public function getFkiduser(): string
    {
        return $this->orm()->getColumn('fkiduser');
    }

    /**
     * Set user's id
     * @param integer $fkiduser
     * @return User
     */
    public function setFkiduser(string $fkiduser): self
    {
        $this->orm()->setColumn('fkiduser', $fkiduser);
        return $this;
    }



/**
     * Get user's name
     * @return date
     */
    public function getCreationdate(): DateTime
    {
        return $this->orm()->getColumn('creationdate');
    }

    /**
     * Set user's name
     * @param string $fkiduser
     * @return User
     */
    public function setCreationdate(DateTime $creationdate): self
    {
        $this->orm()->setColumn('creationdate', $creationdate);
        return $this;
    }


/**
     * Get user's name
     * @return date
     */
    public function getActivitydate(): DateTime
    {
        return $this->orm()->getColumn('activitydate');
    }

    /**
     * Set user's name
     * @param string $fkiduser
     * @return User
     */
    public function setActivitydate(DateTime     $activitydate): self
    {
        $this->orm()->setColumn('activitydate', $activitydate);
        return $this;
    }




  public function getActivityfile(): string
  {
      return $this->orm()->getColumn('activityfile');
  }      // User entity
  

     /**
   * @inheritdoc
   */


  



   public static function mapEntity(IEntityMapper $mapper)
   {
    
      $mapper->table('tactivity');
    
      
      $mapper->cast([
           'id' => 'integer',
           'fkiduser' => 'integer',
           'creationdate' => 'date',
           'activitydate' => 'date',
           'activityfile' => 'string',
           'distance' => 'integer',
           'duration' => 'integer',
           'ascend' => 'integer',
           'mapimage' => 'string'
       ]);

   
   }
}
?>