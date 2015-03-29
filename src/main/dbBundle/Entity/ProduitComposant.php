<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProduitComposant
 */
class ProduitComposant
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $composantId;

    /**
     * @var integer
     */
    private $produitId;


    /**
     * Set id
     *
     * @param integer $id
     * @return ProduitComposant
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
     * Set composantId
     *
     * @param integer $composantId
     * @return ProduitComposant
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
     * Set produitId
     *
     * @param integer $produitId
     * @return ProduitComposant
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
}
