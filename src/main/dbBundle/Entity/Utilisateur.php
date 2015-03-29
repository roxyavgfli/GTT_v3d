<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Utilisateur
 */
class Utilisateur
{
    /**
     * @var integer
     */
    private $equipeId;

    /**
     * @var boolean
     */
    private $actif;

    /**
     * @var string
     */
    private $nom;

    /**
     * @var string
     */
    private $prenom;

    /**
     * @var string
     */
    private $trigramme;

    /**
     * @var string
     */
    private $mail;

    /**
     * @var string
     */
    private $mdp;

    /**
     * @var string
     */
    private $dateInscription;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set equipeId
     *
     * @param integer $equipeId
     * @return Utilisateur
     */
    public function setEquipeId($equipeId)
    {
        $this->equipeId = $equipeId;

        return $this;
    }

    /**
     * Get equipeId
     *
     * @return integer 
     */
    public function getEquipeId()
    {
        return $this->equipeId;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return Utilisateur
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
     * Set nom
     *
     * @param string $nom
     * @return Utilisateur
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
     * Set prenom
     *
     * @param string $prenom
     * @return Utilisateur
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set trigramme
     *
     * @param string $trigramme
     * @return Utilisateur
     */
    public function setTrigramme($trigramme)
    {
        $this->trigramme = $trigramme;

        return $this;
    }

    /**
     * Get trigramme
     *
     * @return string 
     */
    public function getTrigramme()
    {
        return $this->trigramme;
    }

    /**
     * Set mail
     *
     * @param string $mail
     * @return Utilisateur
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set mdp
     *
     * @param string $mdp
     * @return Utilisateur
     */
    public function setMdp($mdp)
    {
        $this->mdp = $mdp;

        return $this;
    }

    /**
     * Get mdp
     *
     * @return string 
     */
    public function getMdp()
    {
        return $this->mdp;
    }

    /**
     * Set dateInscription
     *
     * @param string $dateInscription
     * @return Utilisateur
     */
    public function setDateInscription($dateInscription)
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    /**
     * Get dateInscription
     *
     * @return string 
     */
    public function getDateInscription()
    {
        return $this->dateInscription;
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
