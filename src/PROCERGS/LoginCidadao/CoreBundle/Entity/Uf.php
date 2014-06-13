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
    const REVIEWED_OK = 0;
    const REVIEWED_IGNORE = 1;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")       
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
     * @ORM\Column(name="acronym", type="string", length=2, nullable=true)
     */
    private $acronym;
    
    /**
     *
     * @ORM\Column(name="iso", type="string", length=6, nullable=true)
     */
    private $iso;
    
    /**
     *
     * @ORM\Column(name="fips", type="string", length=4, nullable=true)
     */
    private $fips;
    
    /**
     *
     * @ORM\Column(name="stat", type="string", length=7, nullable=true)
     */
    private $stat;
    
    /**
     *
     * @ORM\Column(name="class", type="string", length=255, nullable=true)
     */
    private $class;
    
    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $reviewed;
    
    public function __construct($id = null)
    {
        $this->setId($id);
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
    
    public function setStat($var)
    {
        $this->stat = $var;
    
        return $this;
    }
    
    public function getStat()
    {
        return $this->stat;
    }
    
    public function setFips($var)
    {
        $this->fips = $var;
    
        return $this;
    }
    
    public function getFips()
    {
        return $this->fips;
    }
    
    public function setIso($var)
    {
        $this->iso = $var;
    
        return $this;
    }
    
    public function getIso()
    {
        return $this->iso;
    }
    
    public function setReviewed($var)
    {
        $this->reviewed = $var;
    
        return $this;
    }
    
    public function getReviewed()
    {
        return $this->reviewed;
    }
    
}
