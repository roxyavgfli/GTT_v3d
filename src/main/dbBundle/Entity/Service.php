<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Service
 */
class Service {

    /**
     * @var integer
     */
    private $societeId;

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
     * Set societeId
     *
     * @param integer $societeId
     * @return Service
     */
    public function setSocieteId($societeId) {
        $this->societeId = $societeId;

        return $this;
    }

    /**
     * set id and updating dependencies
     * @param Integer $id
     * @param EntityManager $em
     */
    public function setIdWithDependency($id, $em) {
        $oldId = $this->getId();
        $this->setId($id);
        $raws = $em->getRepository('maindbBundle:Equipe')->findBy(Array('serviceId' => $oldId));
        foreach ($raws as $raw) {
            $raw->setServiceId($id);
            $em->persist($raw);
            $em->flush();
        }
        $em->persist($this);
        $metadata = $em->getClassMetaData(get_class($this));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
    }

    /**
     * Get societeId
     *
     * @return integer 
     */
    public function getSocieteId() {
        return $this->societeId;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Service
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
     * @return Service
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
