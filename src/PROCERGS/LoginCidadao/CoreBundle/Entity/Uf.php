<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Uf
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id     
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
     * 
     * @ORM\Column(name="acronym", type="string", length=2)
     */
    private $acronym;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($var)
    {
        $this->id = $var;
    
        return $this;
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return City
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set Acronym
     *
     * @param string $name
     * @return City
     */
    public function setAcronym($var)
    {
        $this->acronym = $var;
    
        return $this;
    }
    
    /**
     * Get Acronym
     *
     * @return string
     */
    public function getAcronym()
    {
        return $this->acronym;
    }    
}
