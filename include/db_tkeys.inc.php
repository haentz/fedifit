<?php

use Opis\Database\Connection;

use Opis\ORM\{
    Entity, 
    EntityManager,
    IEntityMapper,
    IMappableEntity
};


class Keys extends Entity  implements IMappableEntity
{

 /**
  *  id
  */
    public function getId(): int
    {
        return $this->orm()->getColumn('id');
    }
        
    public function setId(int $id): self
    {
        $this->orm()->setColumn('id', $id);
        return $this;
    }

 /**
  *  id
  */
  public function getFkiduser(): int
  {
      return $this->orm()->getColumn('id');
  }
      
  public function setFkiduser(int $id): self
  {
      $this->orm()->setColumn('fkiduser', $id);
      return $this;
  }

 /** 
  *  privatekey
  */
  public function getPrivatekey(): string
  {
      return $this->orm()->getColumn('privatekey');
  }
      
  public function setPrivatekey(string $privatekey): self
  {
      $this->orm()->setColumn('privatekey', $privatekey);
      return $this;
  }


   /** 
  *  privatekey
  */
  public function getPublickey(): string
  {
      return $this->orm()->getColumn('publickey');
  }
      
  public function setPublickey(string $publickey): self
  {
      $this->orm()->setColumn('publickey', $publickey);
      return $this;
  }



  public static function mapEntity(IEntityMapper $mapper)
  {
   
    $mapper->table('tkeys');
   
     
     $mapper->cast([
          'id' => 'integer',
          'fkiduser' => 'integer',
          'privatekey' => 'string',
          'publickey' => 'string'
      ]);

    



  }




}


?>