<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ssphase
 */
class Ssphase
{
    /**
     * @var integer
     */
    private $phaseId;

    /**
     * @var string
     */
    private $nom;

    /**
     * @var boolean
     */
    private $actif;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set phaseId
     *
     * @param integer $phaseId
     * @return Ssphase
     */
    public function setPhaseId($phaseId)
    {
        $this->phaseId = $phaseId;

        return $this;
    }

    /**
     * Get phaseId
     *
     * @return integer 
     */
    public function getPhaseId()
    {
        return $this->phaseId;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Ssphase
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return Ssphase
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return boolean 
     */
    public function getActif()
    {
        return $this->actif;
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
