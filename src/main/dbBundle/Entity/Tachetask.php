<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tachetask
 */
class Tachetask
{
    /**
     * @var integer
     */
    private $userId;

    /**
     * @var integer
     */
    private $ssphaseId;

    /**
     * @var integer
     */
    private $produitComposantId;

    /**
     * @var integer
     */
    private $produitPlateformeId;

    /**
     * @var integer
     */
    private $produitVersionId;

    /**
     * @var integer
     */
    private $natureClientId;

    /**
     * @var string
     */
    private $nom;

    /**
     * @var boolean
     */
    private $actif;

    /**
     * @var boolean
     */
    private $suivi;

    /**
     * @var integer
     */
    private $chargeInit;

    /**
     * @var integer
     */
    private $chargeAttr;

    /**
     * @var integer
     */
    private $etat;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set userId
     *
     * @param integer $userId
     * @return Tachetask
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set ssphaseId
     *
     * @param integer $ssphaseId
     * @return Tachetask
     */
    public function setSsphaseId($ssphaseId)
    {
        $this->ssphaseId = $ssphaseId;

        return $this;
    }

    /**
     * Get ssphaseId
     *
     * @return integer 
     */
    public function getSsphaseId()
    {
        return $this->ssphaseId;
    }

    /**
     * Set produitComposantId
     *
     * @param integer $produitComposantId
     * @return Tachetask
     */
    public function setProduitComposantId($produitComposantId)
    {
        $this->produitComposantId = $produitComposantId;

        return $this;
    }

    /**
     * Get produitComposantId
     *
     * @return integer 
     */
    public function getProduitComposantId()
    {
        return $this->produitComposantId;
    }

    /**
     * Set produitPlateformeId
     *
     * @param integer $produitPlateformeId
     * @return Tachetask
     */
    public function setProduitPlateformeId($produitPlateformeId)
    {
        $this->produitPlateformeId = $produitPlateformeId;

        return $this;
    }

    /**
     * Get produitPlateformeId
     *
     * @return integer 
     */
    public function getProduitPlateformeId()
    {
        return $this->produitPlateformeId;
    }

    /**
     * Set produitVersionId
     *
     * @param integer $produitVersionId
     * @return Tachetask
     */
    public function setProduitVersionId($produitVersionId)
    {
        $this->produitVersionId = $produitVersionId;

        return $this;
    }

    /**
     * Get produitVersionId
     *
     * @return integer 
     */
    public function getProduitVersionId()
    {
        return $this->produitVersionId;
    }

    /**
     * Set natureClientId
     *
     * @param integer $natureClientId
     * @return Tachetask
     */
    public function setNatureClientId($natureClientId)
    {
        $this->natureClientId = $natureClientId;

        return $this;
    }

    /**
     * Get natureClientId
     *
     * @return integer 
     */
    public function getNatureClientId()
    {
        return $this->natureClientId;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Tachetask
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
     * @return Tachetask
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
     * Set suivi
     *
     * @param boolean $suivi
     * @return Tachetask
     */
    public function setSuivi($suivi)
    {
        $this->suivi = $suivi;

        return $this;
    }

    /**
     * Get suivi
     *
     * @return boolean 
     */
    public function getSuivi()
    {
        return $this->suivi;
    }

    /**
     * Set chargeInit
     *
     * @param integer $chargeInit
     * @return Tachetask
     */
    public function setChargeInit($chargeInit)
    {
        $this->chargeInit = $chargeInit;

        return $this;
    }

    /**
     * Get chargeInit
     *
     * @return integer 
     */
    public function getChargeInit()
    {
        return $this->chargeInit;
    }

    /**
     * Set chargeAttr
     *
     * @param integer $chargeAttr
     * @return Tachetask
     */
    public function setChargeAttr($chargeAttr)
    {
        $this->chargeAttr = $chargeAttr;

        return $this;
    }

    /**
     * Get chargeAttr
     *
     * @return integer 
     */
    public function getChargeAttr()
    {
        return $this->chargeAttr;
    }

    /**
     * Set etat
     *
     * @param integer $etat
     * @return Tachetask
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return integer 
     */
    public function getEtat()
    {
        return $this->etat;
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
