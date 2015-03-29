<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Imputation
 */
class Imputation
{
    /**
     * @var integer
     */
    private $ttaskId;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var integer
     */
    private $tempsPasse;

    /**
     * @var integer
     */
    private $raf;

    /**
     * @var string
     */
    private $commentaire;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set ttaskId
     *
     * @param integer $ttaskId
     * @return Imputation
     */
    public function setTtaskId($ttaskId)
    {
        $this->ttaskId = $ttaskId;

        return $this;
    }

    /**
     * Get ttaskId
     *
     * @return integer 
     */
    public function getTtaskId()
    {
        return $this->ttaskId;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Imputation
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set tempsPasse
     *
     * @param integer $tempsPasse
     * @return Imputation
     */
    public function setTempsPasse($tempsPasse)
    {
        $this->tempsPasse = $tempsPasse;

        return $this;
    }

    /**
     * Get tempsPasse
     *
     * @return integer 
     */
    public function getTempsPasse()
    {
        return $this->tempsPasse;
    }

    /**
     * Set raf
     *
     * @param integer $raf
     * @return Imputation
     */
    public function setRaf($raf)
    {
        $this->raf = $raf;

        return $this;
    }

    /**
     * Get raf
     *
     * @return integer 
     */
    public function getRaf()
    {
        return $this->raf;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return Imputation
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
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
