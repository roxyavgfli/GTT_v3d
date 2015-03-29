<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActiviteNature
 */
class ActiviteNature
{
    /**
     * @var integer
     */
    private $activiteId;

    /**
     * @var string
     */
    private $nature;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set activiteId
     *
     * @param integer $activiteId
     * @return ActiviteNature
     */
    public function setActiviteId($activiteId)
    {
        $this->activiteId = $activiteId;

        return $this;
    }

    /**
     * Get activiteId
     *
     * @return integer 
     */
    public function getActiviteId()
    {
        return $this->activiteId;
    }

    /**
     * Set nature
     *
     * @param string $nature
     * @return ActiviteNature
     */
    public function setNature($nature)
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * Get nature
     *
     * @return string 
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
