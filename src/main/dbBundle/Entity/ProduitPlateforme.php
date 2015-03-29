<?php

namespace main\dbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProduitPlateforme
 */
class ProduitPlateforme
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
    private $plateformeId;


    /**
     * Set id
     *
     * @param integer $id
     * @return ProduitPlateforme
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
     * @return ProduitPlateforme
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
     * Set plateformeId
     *
     * @param integer $plateformeId
     * @return ProduitPlateforme
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
}
