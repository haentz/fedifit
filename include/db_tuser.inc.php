<?php

use Opis\Database\Connection;

use Opis\ORM\{
    Entity, 
    EntityManager,
    IEntityMapper,
    IMappableEntity
};


class User extends Entity  implements IMappableEntity
{

 /**
  *  id
  */
    public function getId(): int
    {
        return $this->orm()->getColumn('id');
    }
        
    public function setId(string $id): self
    {
        $this->orm()->setColumn('id', $id);
        return $this;
    }

 /**
  *  email
  */
  public function getEmail(): string
  {
      return $this->orm()->getColumn('email');
  }
      
  public function setEmail(string $email): self
  {
      $this->orm()->setColumn('email', $email);
      return $this;
  }


   /**
  *  name
  */
  public function getName(): string
  {
      return $this->orm()->getColumn('name');
  }
      
  public function setName(string $name): self
  {
      $this->orm()->setColumn('name', $name);
      return $this;
  }


   /**
  *  logintoken
  */
  public function getLogintoken(): string
  {
      return $this->orm()->getColumn('logintoken');
  }
      
  public function setLogintoken(string $logintoken): self
  {
      $this->orm()->setColumn('logintoken', $logintoken);
      return $this;
  }

   /**
  *  loogintokencreatiodnate
  */
  public function getLogintokencreationdate(): DateTime
  {
      return $this->orm()->getColumn('logintokencreationdate');
  }
      
  public function setLogintokencreationdate(DateTime $logintokencreationdate): self
  {
      $this->orm()->setColumn('logintokencreationdate', $logintokencreationdate);
      return $this;
  }

     /**
  *  creationdate
  */
  public function getCreationdate(): DateTime
  {
      return $this->orm()->getColumn('creationdate');
  }
      
  public function setCreationdate(DateTime $creationdate): self
  {
      $this->orm()->setColumn('creationdate', $creationdate);
      return $this;
  }



/**
  *  strava_athlete_id 
  */
  public function getStravaId(): ?int
  {
      return $this->orm()->getColumn('strava_athlete_id');
  }
      
  public function setStravaId(string $id): self
  {
      $this->orm()->setColumn('strava_athlete_id', $id);
      return $this;
  }


   /**
  *  strava_access_token
  */
  public function getStravaAccessToken(): ?string
  {
      return $this->orm()->getColumn('strava_access_token');
  }
      
  public function setStravaAccessToken(string $strava_access_token): self
  {
      $this->orm()->setColumn('strava_access_token', $strava_access_token);
      return $this;
    }
  

   /**
  *  strava_refresh_token
  */
  public function getStravaRefreshToken(): ?string
  {
      return $this->orm()->getColumn('strava_refresh_token');
  }
      
  public function setStravaRefreshToken(string $strava_refresh_token): self
  {
      $this->orm()->setColumn('strava_refresh_token', $strava_refresh_token);
      return $this;
  }

     /**
  *  strava_access_token_expirationdate
  */
  public function getStravaExpirationdate(): ?DateTime
  {
      return $this->orm()->getColumn('strava_access_token_expirationdate');
  }
      
  public function setStravaExpirationdate(DateTime $strava_access_token_expirationdate): self
  {
      $this->orm()->setColumn('strava_access_token_expirationdate', $strava_access_token_expirationdate);
      return $this;
  }





  public static function mapEntity(IEntityMapper $mapper)
  {
   
     $mapper->table('tuser');
   
     
     $mapper->cast([
          'id' => 'integer',
          'email' => 'string',
          'name' => 'string',
          'creationdate' => 'date',
          'logintoken' => 'string',
          'logintokencreationdate' => 'date',
          `strava_athlete_id` => 'integer',
          `strava_refresh_token` => '?string',
          `strava_access_token` => '?string',
          `strava_access_token_expirationdate`  => '?date'
      ]);

  



  }




}


?>