<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\Table(name="person_address")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class PersonAddress
{

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Since("1.1.0")
     */
    protected $id;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Person", inversedBy="addresses")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     * @var \PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface
     */
    protected $person;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $name;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    protected $line1;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $line2;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * @var City
     */
    protected $city;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @ORM\Column(name="postal_code", type="string", nullable=true)
     * @var string
     */
    protected $postalCode;

    public function getLine1()
    {
        return $this->line1;
    }

    public function getLine2()
    {
        return $this->line2;
    }

    /**
     *
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    public function setLine1($line1)
    {
        $this->line1 = $line1;
        return $this;
    }

    public function setLine2($line2)
    {
        $this->line2 = $line2;
        return $this;
    }

    public function setCity(City $city)
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setPerson($person)
    {
        $this->person = $person;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

}
