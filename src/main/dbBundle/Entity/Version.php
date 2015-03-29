<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Version
 */
class Version
{
    /**
     * @var string
     */
    private $numero;

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
        $raws = $em->getRepository('maindbBundle:ProduitVersion')->findBy(Array('versionId' => $oldId));
        foreach ($raws as $raw) {
            $raw->setVersionId($id);
            $em->persist($raw);
            $em->flush();
        }
        $tasks = $em->getRepository('maindbBundle:Tachesimple')->findBy(Array('versionId' => $oldId));
        foreach ($tasks as $task){
            $task->setVersionId($id);
            $em->persist($task);
            $em->flush();
        }
        $em->persist($this);
        $metadata = $em->getClassMetaData(get_class($this));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
    }

    /**
     * Set numero
     *
     * @param string $numero
     * @return Version
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return Version
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
