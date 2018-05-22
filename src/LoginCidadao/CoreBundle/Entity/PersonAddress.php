<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use LoginCidadao\CoreBundle\Model\LocationSelectData;
use LoginCidadao\CoreBundle\Model\LocationAwareInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="person_address")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class PersonAddress implements LocationAwareInterface
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
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person", inversedBy="addresses")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     * @var \LoginCidadao\CoreBundle\Model\PersonInterface
     */
    protected $person;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @Assert\Length(max="30", groups={"Default", "LoginCidadaoRegistration", "Registration", "Profile", "LoginCidadaoProfile", "Dynamic", "Address"})
     * @ORM\Column(type="string", nullable=true, length=30)
     * @var string
     */
    protected $name;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @Assert\Length(max="255", groups={"Default", "LoginCidadaoRegistration", "Registration", "Profile", "LoginCidadaoProfile", "Dynamic", "Address"})
     * @ORM\Column(type="string", nullable=false, length=255)
     * @var string
     */
    protected $address;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Assert\Length(max="255", groups={"Default", "LoginCidadaoRegistration", "Registration", "Profile", "LoginCidadaoProfile", "Dynamic", "Address"})
     * @var string
     */
    protected $complement;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @Assert\Length(max="10", groups={"Default", "LoginCidadaoRegistration", "Registration", "Profile", "LoginCidadaoProfile", "Dynamic", "Address"})
     * @ORM\Column(name="address_number",type="string", nullable=true, length=10)
     * @var string
     */
    protected $addressNumber;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", nullable=true)
     * @var City
     */
    protected $city;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses"})
     * @JMS\Since("1.1.0")
     * @Assert\Length(max="20", groups={"Default", "LoginCidadaoRegistration", "Registration", "Profile", "LoginCidadaoProfile", "Dynamic", "Address"})
     * @ORM\Column(name="postal_code", type="string", nullable=true, length=20)
     * @var string
     */
    protected $postalCode;

    /** @var LocationSelectData */
    protected $location;

    public function getAddress()
    {
        return $this->address;
    }

    public function getComplement()
    {
        return $this->complement;
    }

    public function getAddressNumber()
    {
        return $this->addressNumber;
    }

    /**
     *
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    public function setAddress($var)
    {
        $this->address = $var;
        return $this;
    }

    public function setComplement($var)
    {
        $this->complement = $var;
        return $this;
    }

    public function setAddressNumber($var)
    {
        $this->addressNumber = $var;
        return $this;
    }

    public function setCity(City $city = NULL)
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

    public function getState()
    {
        return $this->state;
    }

    public function setState(State $state = NULL)
    {
        $this->state = $state;
        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(Country $country = NULL)
    {
        $this->country = $country;
        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation(LocationSelectData $location)
    {
        $this->location = $location;
        return $this;
    }

    public function __wakeup()
    {
        if ($this->location !== null) {
            $this->location->toObject($this);
        } else {
            $this->location = new LocationSelectData();
            $this->location->getFromObject($this);
        }
    }
}
