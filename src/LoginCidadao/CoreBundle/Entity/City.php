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
 * @ORM\Table(name="city",indexes={@ORM\Index(name="city_name_index", columns={"name"})})
 * @ORM\Entity(repositoryClass="LoginCidadao\CoreBundle\Entity\CityRepository")
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
     * @Groups({"city","typeahead","public_profile"})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @Groups({"city","typeahead","public_profile"})
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
     * @Groups({"city","typeahead","public_profile"})
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\State", inversedBy="cities")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     */
    protected $state;

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

    public function setState($var)
    {
        $this->state = $var;
        return $this;
    }

    /**
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }


}
