<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProduitVersion
 */
class ProduitVersion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $produitId;

    /**
     * @var integer
     */
    private $versionId;


    /**
     * Set id
     *
     * @param integer $id
     * @return ProduitVersion
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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

    /**
     * Set produitId
     *
     * @param integer $produitId
     * @return ProduitVersion
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
     * @return ProduitVersion
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
}
