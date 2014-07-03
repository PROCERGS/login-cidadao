<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * City
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class City
{
    const REVIEWED_OK = 0;
    const REVIEWED_IGNORE = 1;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @Groups({"city"})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @Groups({"city"})
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;
    
    /**
     * @var string
     *
     * @Groups({"city"})
     * @ORM\Column(name="stat", type="string", length=7, nullable=true)
     */
    protected $stat;
    
    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Uf")
     * @ORM\JoinColumn(name="uf_id", referencedColumnName="id")
     */
    protected $uf;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $reviewed;

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

    public function setName($var)
    {
        $this->name = $var;
    
        return $this;
    }

    public function getName()
    {
        return $this->name;
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
    
    public function setReviewed($var)
    {
        $this->reviewed = $var;
    
        return $this;
    }
    
    public function getReviewed()
    {
        return $this->reviewed;
    }
    
    public function setUf($var)
    {
        $this->uf = $var;
        return $this;
    }

    public function getUf()
    {
        return $this->uf;
    }
    
    
}
