<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Plateforme
 */
class Plateforme {

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
        $raws = $em->getRepository('maindbBundle:ProduitPlateforme')->findBy(Array('plateformeId' => $oldId));
        foreach ($raws as $raw) {
            $raw->setPlateformeId($id);
            $em->persist($raw);
            $this->em->detach($raw);
            $em->flush();
            $this->em->clear();
        }
        $tasks = $em->getRepository('maindbBundle:Tachesimple')->findBy(Array('plateformeId' => $oldId));
        foreach ($tasks as $task) {
            $task->setPlateformeId($id);
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
     * Set nom
     *
     * @param string $nom
     * @return Plateforme
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
     * @return Plateforme
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
