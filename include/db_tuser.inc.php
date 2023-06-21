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
  *  id
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
  *  logintoken
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





  public static function mapEntity(IEntityMapper $mapper)
  {
   
     $mapper->table('tuser');
   
     
     $mapper->cast([
          'id' => 'integer',
          'email' => 'integer',
          'name' => 'string',
          'creationdate' => 'date',
          'logintoken' => 'string',
          'logintokencreationdate' => 'date'
      ]);

  
  }




}


?>