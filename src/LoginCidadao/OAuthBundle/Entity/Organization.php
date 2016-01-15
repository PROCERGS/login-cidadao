<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OAuthBundle\Model\OrganizationInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="organization")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("domain")
 */
class Organization implements OrganizationInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @var string
     */
    protected $name;

    /**
     * @var PersonInterface[]
     * @ORM\ManyToMany(targetEntity="LoginCidadao\CoreBundle\Model\PersonInterface")
     * @ORM\JoinTable(name="person_organizations",
     *      joinColumns={@ORM\JoinColumn(name="organization_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="person_id", referencedColumnName="id")}
     * )
     */
    protected $members;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    protected $verified;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @var string
     */
    protected $domain;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\OAuthBundle\Model\ClientInterface", mappedBy="organization")
     * @var ClientInterface
     */
    protected $clients;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return PersonInterface[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param PersonInterface[] $members
     * @return \Organization
     */
    public function setMembers(array $members)
    {
        $this->members = $members;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isVerified()
    {
        return $this->verified;
    }

    /**
     * @param boolean $verified
     * @return \Organization
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function setClients(array $clients)
    {
        $this->clients = $clients;

        return $this;
    }
}
