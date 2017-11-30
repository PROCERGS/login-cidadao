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
 * City
 *
 * @ORM\Table(name="country")
 * @ORM\Entity(repositoryClass="LoginCidadao\CoreBundle\Entity\CountryRepository")
 */
class Country
{
    const REVIEWED_OK     = 0;
    const REVIEWED_IGNORE = 1;

    /**
     * @Groups({"country", "typeahead", "public_profile"})
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Groups({"country", "typeahead", "public_profile"})
     * @var string @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;

    /**
     * @Groups({"country", "typeahead", "public_profile"})
     * @var string @ORM\Column(name="iso2", type="string", length=2, nullable=true)
     */
    protected $iso2;

    /**
     * @Groups({"country", "public_profile"})
     * @var string @ORM\Column(name="iso3", type="string", length=3, nullable=true)
     */
    protected $iso3;

    /**
     * @Groups({"country"})
     * @var string @ORM\Column(name="iso_num", type="integer", nullable=true)
     */
    protected $isoNum;

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

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" = 0})
     * @var int
     */
    protected $preference;

    /**
     * @ORM\OneToMany(targetEntity="State", mappedBy="country", cascade={"remove"}, orphanRemoval=true)
     * @var State[]
     */
    protected $states;

    public function __construct($id = null)
    {
        $this->setId($id);
        $this->states = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function setIso2($var)
    {
        $this->iso2 = $var;

        return $this;
    }

    public function getIso2()
    {
        return $this->iso2;
    }

    public function setIso3($var)
    {
        $this->iso3 = $var;

        return $this;
    }

    public function getIso3()
    {
        return $this->iso3;
    }

    public function setIsoNum($var)
    {
        $this->isoNum = $var;

        return $this;
    }

    public function getIsoNum()
    {
        return $this->isoNum;
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

    public function getStates()
    {
        return $this->states;
    }

    public function getPreference()
    {
        return $this->preference;
    }
}
