<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 */
class Client {

    /**
     * @var integer
     */
    private $partenaireId;

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
        $tasksWithThatClientId = $em->getRepository('maindbBundle:Tachesimple')->findBy(Array('clientId' => $oldId));
        foreach ($tasksWithThatClientId as $task) {
            $task->setClientId($id);
            $em->persist($task);
            $this->em->detach($task);
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
     * Set partenaireId
     *
     * @param integer $partenaireId
     * @return Client
     */
    public function setPartenaireId($partenaireId) {
        $this->partenaireId = $partenaireId;

        return $this;
    }

    /**
     * Get partenaireId
     *
     * @return integer 
     */
    public function getPartenaireId() {
        return $this->partenaireId;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Client
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
     * @return Client
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
