<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Produit
 */
class Produit
{
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
    public function setId($id){
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
        $rawsC = $em->getRepository('maindbBundle:ProduitComposant')->findBy(Array('produitId' => $oldId));
        foreach ($rawsC as $raw) {
            $raw->setProduitId($id);
            $em->persist($raw);
            $em->flush();
        }
        $rawsP = $em->getRepository('maindbBundle:ProduitPlateforme')->findBy(Array('produitId' => $oldId));
        foreach ($rawsP as $raw) {
            $raw->setProduitId($id);
            $em->persist($raw);
            $em->flush();
        }
        $rawsV = $em->getRepository('maindbBundle:ProduitVersion')->findBy(Array('produitId' => $oldId));
        foreach ($rawsV as $raw) {
            $raw->setProduitId($id);
            $em->persist($raw);
            $em->flush();
        }
        $tasks = $em->getRepository('maindbBundle:Tachesimple')->findBy(Array('produitId' => $oldId));
        foreach ($tasks as $task){
            $task->setComposantId($id);
            $em->persist($task);
            $em->flush();
        }
        $em->persist($this);
        $metadata = $em->getClassMetaData(get_class($this));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Produit
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
     * @return Produit
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
