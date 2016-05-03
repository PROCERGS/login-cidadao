<?php

namespace LoginCidadao\APIBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Model\Client;

/**
 * @ORM\Entity(repositoryClass="LoginCidadao\APIBundle\Entity\LogoutKeyRepository")
 * @ORM\Table(name="logout_key")
 * @UniqueEntity("logoutKey")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class LogoutKey
{

    /**
     * @JMS\Expose
     * @JMS\Groups({"id"})
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Since("1.1.0")
     */
    protected $id;

    /**
     * @JMS\Expose
     * @JMS\Groups({"key","public","public_profile"})
     * @JMS\Since("1.1.0")
     * @ORM\Column(name="logout_key", type="string", nullable=false)
     * @var string
     */
    protected $logoutKey;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person", inversedBy="logoutKeys")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     */
    protected $person;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client", inversedBy="logoutKeys")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     * @var \LoginCidadao\OAuthBundle\Entity\Client
     */
    protected $client;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     * @JMS\Since("1.1.0")
     */
    protected $createdAt;

    public function getId()
    {
        return $this->id;
    }

    public function getKey()
    {
        return $this->logoutKey;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setKey($logoutKey)
    {
        $this->logoutKey = $logoutKey;

        return $this;
    }

    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    public function generateKey()
    {
        return sha1(random_bytes(250));
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if (!($this->getCreatedAt() instanceof \DateTime)) {
            $this->createdAt = new \DateTime();
        }
    }

}
