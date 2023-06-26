<?php

use Opis\Database\Connection;

use Opis\ORM\{
    Entity, 
    EntityManager,
    IEntityMapper,
    IMappableEntity
};

class DBActivity extends Entity  implements IMappableEntity
{


/**
     * Get activity's ID
     * @return int
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
     * Get user's id
     * @return integer
     */
    public function getFkiduser(): int
    {
        return $this->orm()->getColumn('fkiduser');
    }

    /**
     * Set user's id
     * @param integer $fkiduser
     * @return User
     */
    public function setFkiduser(int $fkiduser): self
    {
        $this->orm()->setColumn('fkiduser', $fkiduser);
        return $this;
    }


    /**
    * creationdate
     */
    public function getCreationdate(): DateTime
    {
        return $this->orm()->getColumn('creationdate');
    }

    /**
    *
     */
    public function setCreationdate(DateTime $creationdate): self
    {
        $this->orm()->setColumn('creationdate', $creationdate);
        return $this;
    }

    /**
    * strava_activity_id
     */
    public function getStrava_activity_id(): int
    {
        return $this->orm()->getColumn('strava_activity_id');
    }

    /**
    *
     */
    public function setStrava_activity_id(int $strava_activity_id): self
    {
        $this->orm()->setColumn('strava_activity_id', $strava_activity_id);
        return $this;
    }


    /**
    *heroImage
     */
    public function getHeroImage(): string
    {
        return $this->orm()->getColumn('heroImage');
    }

    /**
    *heroImage
     */
    public function setHeroImage(string $heroImage): self
    {
        $this->orm()->setColumn('heroImage', $heroImage);
        return $this;
    }
        /**
    *
     */
    public function getText(): string
    {
        return $this->orm()->getColumn('text');
    }

    /**
    *
     */
    public function setText(string $text): self
    {
        $this->orm()->setColumn('text', $text);
        return $this;
    }
        

    /**
    *released
     */
    public function getReleased(): int
    {
        return $this->orm()->getColumn('released');
    }

  
    public function setReleased(int $released): self
    {
        $this->orm()->setColumn('released', $released);
        return $this;
    }

    /**
    *released
     */
    public function getDownloaded(): int
    {
        return $this->orm()->getColumn('downloaded');
    }

  
    public function setDownloaded(int $downloaded): self
    {
        $this->orm()->setColumn('downloaded', $downloaded);
        return $this;
    }

   public static function mapEntity(IEntityMapper $mapper)
   {
    
      $mapper->table('tactivity');
    
      
      $mapper->cast([
           'id' => 'integer',
           'fkiduser' => 'integer',
           'creationdate' => 'date',
           'released' => 'int',
           'downloaded' => 'int',
           'strava_activity_id' => 'integer',
           'heroImage' => 'string',
           'text' => 'string'
       ]);

   
   }
}


?>