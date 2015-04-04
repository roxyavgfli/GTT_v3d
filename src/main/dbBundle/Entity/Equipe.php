<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Equipe
 */
class Equipe {

    /**
     * @var integer
     */
    private $serviceId;

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
     * Set id
     * 
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * set id and updating dependencies
     * @param Integer $id
     * @param EntityManager $em
     */
    public function setIdWithDependency($id, $em) {
        $oldId = $this->getId();
        $this->setId($id);
        $raws = $em->getRepository('maindbBundle:Utilisateur')->findBy(Array('equipeId' => $oldId));
        foreach ($raws as $raw) {
            $raw->setEquipeId($id);
            $em->persist($raw);
            $this->em->detach($raw);
            $em->flush();
            $this->em->clear();
        }
        $em->persist($this);
        $metadata = $em->getClassMetaData(get_class($this));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $this->em->detach($this);
        $em->flush();
        $this->em->clear();
    }

    /**
     * Set serviceId
     *
     * @param integer $serviceId
     * @return Equipe
     */
    public function setServiceId($serviceId) {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get serviceId
     *
     * @return integer 
     */
    public function getServiceId() {
        return $this->serviceId;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Equipe
     */
    public function setNom($nom) {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom() {
        return $this->nom;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return Equipe
     */
    public function setActif($actif) {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return boolean 
     */
    public function getActif() {
        return $this->actif;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

}
