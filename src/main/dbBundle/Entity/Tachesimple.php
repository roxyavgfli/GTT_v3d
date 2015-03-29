<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tachesimple
 */
class Tachesimple
{
    /**
     * @var integer
     */
    private $userId;

    /**
     * @var string
     */
    private $nom;

    /**
     * @var boolean
     */
    private $actif;

    /**
     * @var float
     */
    private $tempsPasse;

    /**
     * @var string
     */
    private $commentaire;

    /**
     * @var string
     */
    private $date;

    /**
     * @var string
     */
    private $nature;

    /**
     * @var integer
     */
    private $activiteId;

    /**
     * @var integer
     */
    private $clientId;

    /**
     * @var integer
     */
    private $partenaireId;

    /**
     * @var integer
     */
    private $plateformeId;

    /**
     * @var integer
     */
    private $produitId;

    /**
     * @var integer
     */
    private $versionId;

    /**
     * @var integer
     */
    private $ssphaseId;

    /**
     * @var integer
     */
    private $composantId;

    /**
     * @var boolean
     */
    private $editable;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set userId
     *
     * @param integer $userId
     * @return Tachesimple
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
     * Set nom
     *
     * @param string $nom
     * @return Tachesimple
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
     * @return Tachesimple
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
     * Set tempsPasse
     *
     * @param float $tempsPasse
     * @return Tachesimple
     */
    public function setTempsPasse($tempsPasse)
    {
        $this->tempsPasse = $tempsPasse;

        return $this;
    }

    /**
     * Get tempsPasse
     *
     * @return float 
     */
    public function getTempsPasse()
    {
        return $this->tempsPasse;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return Tachesimple
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
     * Set date
     *
     * @param string $date
     * @return Tachesimple
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set nature
     *
     * @param string $nature
     * @return Tachesimple
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
     * Set activiteId
     *
     * @param integer $activiteId
     * @return Tachesimple
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
     * Set clientId
     *
     * @param integer $clientId
     * @return Tachesimple
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get clientId
     *
     * @return integer 
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set partenaireId
     *
     * @param integer $partenaireId
     * @return Tachesimple
     */
    public function setPartenaireId($partenaireId)
    {
        $this->partenaireId = $partenaireId;

        return $this;
    }

    /**
     * Get partenaireId
     *
     * @return integer 
     */
    public function getPartenaireId()
    {
        return $this->partenaireId;
    }

    /**
     * Set plateformeId
     *
     * @param integer $plateformeId
     * @return Tachesimple
     */
    public function setPlateformeId($plateformeId)
    {
        $this->plateformeId = $plateformeId;

        return $this;
    }

    /**
     * Get plateformeId
     *
     * @return integer 
     */
    public function getPlateformeId()
    {
        return $this->plateformeId;
    }

    /**
     * Set produitId
     *
     * @param integer $produitId
     * @return Tachesimple
     */
    public function setProduitId($produitId)
    {
        $this->produitId = $produitId;

        return $this;
    }

    /**
     * Get produitId
     *
     * @return integer 
     */
    public function getProduitId()
    {
        return $this->produitId;
    }

    /**
     * Set versionId
     *
     * @param integer $versionId
     * @return Tachesimple
     */
    public function setVersionId($versionId)
    {
        $this->versionId = $versionId;

        return $this;
    }

    /**
     * Get versionId
     *
     * @return integer 
     */
    public function getVersionId()
    {
        return $this->versionId;
    }

    /**
     * Set ssphaseId
     *
     * @param integer $ssphaseId
     * @return Tachesimple
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
     * Set composantId
     *
     * @param integer $composantId
     * @return Tachesimple
     */
    public function setComposantId($composantId)
    {
        $this->composantId = $composantId;

        return $this;
    }

    /**
     * Get composantId
     *
     * @return integer 
     */
    public function getComposantId()
    {
        return $this->composantId;
    }

    /**
     * Set editable
     *
     * @param boolean $editable
     * @return Tachesimple
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;

        return $this;
    }

    /**
     * Get editable
     *
     * @return boolean 
     */
    public function getEditable()
    {
        return $this->editable;
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
