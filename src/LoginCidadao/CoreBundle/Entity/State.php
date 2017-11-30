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
use JMS\Serializer\Annotation\Groups;

/**
 * State
 *
 * @ORM\Table(name="state",indexes={@ORM\Index(name="state_preference_index", columns={"preference"})})
 * @ORM\Entity(repositoryClass="LoginCidadao\CoreBundle\Entity\StateRepository")
 */
class State
{

    const REVIEWED_OK = 0;
    const REVIEWED_IGNORE = 1;

    /**
     * @Groups({"state", "typeahead", "public_profile"})
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Groups({"state", "typeahead", "public_profile"})
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"typeahead", "public_profile"})
     * @ORM\Column(name="acronym", type="string", length=2, nullable=true)
     */
    private $acronym;

    /**
     * @Groups({"state", "public_profile"})
     * @ORM\Column(name="iso6", type="string", length=6, nullable=true)
     */
    private $iso6;

    /**
     * @Groups({"state"})
     * @ORM\Column(name="fips", type="string", length=4, nullable=true)
     */
    private $fips;

    /**
     * @Groups({"state"})
     * @ORM\Column(name="stat", type="string", length=7, nullable=true)
     */
    private $stat;

    /**
     * @Groups({"state"})
     * @ORM\Column(name="class", type="string", length=255, nullable=true)
     */
    private $class;

    /**
     * @Groups({"typeahead", "public_profile"})
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Country", inversedBy="states")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $reviewed;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" = 0})
     * @var int
     */
    protected $preference;

    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="state", cascade={"remove"}, orphanRemoval=true)
     * @var City
     */
    protected $cities;

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
     * @return State
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
     * @return State
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

    public function setIso6($var)
    {
        $this->iso6 = $var;

        return $this;
    }

    public function getIso6()
    {
        return $this->iso6;
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

    public function setCountry($var)
    {
        $this->country = $var;

        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    public function getPreference()
    {
        return $this->preference;
    }

    public function getCities()
    {
        return $this->cities;
    }

    public function setPreference($preference)
    {
        $this->preference = $preference;
        return $this;
    }

    public function setCities($cities)
    {
        $this->cities = $cities;
        return $this;
    }

}
