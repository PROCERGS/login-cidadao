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
use LoginCidadao\OAuthBundle\Validator\Constraints\DomainOwnership;
use LoginCidadao\OAuthBundle\Validator\Constraints\SectorIdentifier;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="LoginCidadao\OAuthBundle\Entity\OrganizationRepository")
 * @ORM\Table(name="organization")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("domain")
 * @UniqueEntity("name")
 * @DomainOwnership
 * @SectorIdentifier
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
     * @ORM\Column(name="name", type="string", nullable=false, unique=true)
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
     * @ORM\Column(name="verified_at", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $verifiedAt;

    /**
     * @ORM\Column(name="domain", type="string", nullable=false, unique=true)
     * @var string
     */
    protected $domain;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\OpenIDBundle\Entity\ClientMetadata", mappedBy="organization")
     * @var ClientInterface
     */
    protected $clients;

    /**
     * @Assert\Url
     * @ORM\Column(name="validation_url", type="string", nullable=true, unique=true)
     * @var string
     */
    protected $validationUrl;

    /**
     * @ORM\Column(name="validation_secret", type="string", nullable=true)
     * @var string
     */
    protected $validationSecret;

    /**
     * @ORM\Column(name="validated_url", type="string", nullable=true)
     * @var string
     */
    protected $validatedUrl;

    /**
     * @ORM\Column(name="trusted", type="boolean", nullable=false)
     * @var boolean
     */
    protected $trusted;

    /**
     * @Assert\Url
     * @ORM\Column(name="sector_identifier_uri", type="string", nullable=true, unique=true)
     * @var string
     */
    protected $sectorIdentifierUri;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->initializeValidationCode();
        $this->trusted = false;
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

    public function checkValidation()
    {
        if ($this->validatedUrl !== $this->getValidationUrl()) {
            $this->setVerifiedAt(null);
            $this->validatedUrl = null;

            return false;
        }

        return true;
    }

    public function setValidatedUrl($validatedUrl)
    {
        $this->validatedUrl = $validatedUrl;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function isVerified()
    {
        return $this->getVerifiedAt() instanceof \DateTime;
    }

    public function isTrusted()
    {
        return $this->trusted;
    }

    /**
     *
     * @param boolean $trusted
     * @return \LoginCidadao\OAuthBundle\Entity\Organization
     */
    public function setTrusted($trusted)
    {
        $this->trusted = $trusted;

        return $this;
    }

    /**
     * @return string
     */
    public function getSectorIdentifierUri()
    {
        return $this->sectorIdentifierUri;
    }

    /**
     * @param string $sectorIdentifierUri
     */
    public function setSectorIdentifierUri($sectorIdentifierUri)
    {
        $this->sectorIdentifierUri = $sectorIdentifierUri;
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
        $random = base64_encode(random_bytes(35));
        $this->setValidationSecret($random);
    }


}
