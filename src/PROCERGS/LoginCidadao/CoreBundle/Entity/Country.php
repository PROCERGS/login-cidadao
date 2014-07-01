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
class Country
{
    const REVIEWED_OK = 0;
    const REVIEWED_IGNORE = 1;

    /**
     * @Groups({"country"}) 
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")     
     */
    private $id;

    /**
     * @Groups({"country"}) 
     * @var string @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;
    
    /**
     * @Groups({"country"}) 
     * @var string @ORM\Column(name="iso", type="string", length=2, nullable=true)
     */
    protected $iso;

    /**
     * @Groups({"country"}) 
     * @var string @ORM\Column(name="postal_format", type="string", length=30, nullable=true)
     */
    protected $postalFormat;

    /**
     * @Groups({"country"}) 
     * @var string @ORM\Column(name="postal_name", type="string", length=30, nullable=true)
     */
    protected $postalName;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $reviewed;

    public function __construct($id = null)
    {
        $this->setId($id);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($var)
    {
        $this->id = $var;
        
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPostalFormat($var)
    {
        $this->postalFormat = $var;
        
        return $this;
    }

    public function getPostalFormat()
    {
        return $this->postalFormat;
    }

    public function setPostalName($var)
    {
        $this->postalName = $var;
        
        return $this;
    }

    public function getPostalName()
    {
        return $this->postalName;
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
