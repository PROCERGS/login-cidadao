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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * @ORM\Entity(repositoryClass="LoginCidadao\OAuthBundle\Entity\OrganizationRepository")
 * @ORM\Table(name="organization")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("domain")
 * @UniqueEntity("name")
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
     * @Assert\NotBlank
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
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $verifiedAt;

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

    /**
     * @Assert\Url
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @var string
     */
    protected $validationUrl;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $validationSecret;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->initializeValidationCode();
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
     * @return \DateTime
     */
    public function getVerifiedAt()
    {
        return $this->verifiedAt;
    }

    /**
     * @param \DateTime $verifiedAt
     * @return \Organization
     */
    public function setVerifiedAt($verifiedAt)
    {
        $this->verifiedAt = $verifiedAt;

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

    public function getValidationUrl()
    {
        return $this->validationUrl;
    }

    public function setValidationUrl($validationUrl)
    {
        $this->validationUrl = $validationUrl;

        return $this;
    }

    public function getValidationSecret()
    {
        $this->initializeValidationCode();
        return $this->validationSecret;
    }

    public function setValidationSecret($validationSecret)
    {
        $this->validationSecret = $validationSecret;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    private function initializeValidationCode()
    {
        if ($this->validationSecret) {
            return;
        }
        $generator = new SecureRandom();
        $random    = bin2hex($generator->nextBytes(35));
        $this->setValidationSecret($random);
    }
}
