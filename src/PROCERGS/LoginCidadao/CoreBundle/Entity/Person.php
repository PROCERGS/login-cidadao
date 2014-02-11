<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use PROCERGS\OAuthBundle\Entity\Client;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity
 * @ExclusionPolicy("all")
 */
class Person extends BaseUser
{

    /**
     * @Expose
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Expose
     * @Groups({"name"})
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Please enter your name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $firstName;

    /**
     * @Expose
     * @Groups({"name"})
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Please enter your surname.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="The surname is too short.",
     *     maxMessage="The surname is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $surname;

    /**
     * @Expose
     * @Groups({"name"})
     * @var string
     */
    protected $fullName;

    /**
     * @Expose
     * @Groups({"username"})
     */
    protected $username;

    /**
     * @Expose
     * @Groups({"cpf"})
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(
     *     min=11,
     *     max="11",
     *     minMessage="The CPF is too short.",
     *     maxMessage="The CPF is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $cpf;

    /**
     * @Expose
     * @Groups({"email"})
     */
    protected $email;

    /**
     * @Expose
     * @Groups({"birthdate"})
     * @ORM\Column(type="date", nullable=true)
     */
    protected $birthdate;

    /**
     * @Expose
     * @Groups({"cep"})
     * @ORM\Column(type="string", nullable=true)

     */
    protected $cep;

    /**
     * @Expose
     * @Groups({"city"})
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookId", type="string", length=255, nullable=true)
     */
    protected $facebookId;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterId", type="string", length=255, nullable=true)
     */
    protected $twitterId;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterUsername", type="string", length=255, nullable=true)
     */
    protected $twitterUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterAccessToken", type="string", length=255, nullable=true)
     */
    protected $twitterAccessToken;

    /**
     * @ORM\OneToMany(targetEntity="Authorization", mappedBy="person", cascade={"remove"}, orphanRemoval=true)
     */
    protected $authorizations;

    public function __construct()
    {
        parent::__construct();
        $this->authorizations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setSurname($suname)
    {
        $this->surname = $suname;

        return $this;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    public function getCep()
    {
        return $this->cep;
    }

    public function setCep($cep)
    {
        $this->cep = $cep;
    }

    public function addAuthorization(Authorization $authorization)
    {
        $this->authorizations->add($authorization);
        $authorization->setPerson($this);
        return $this;
    }

    public function removeAuthorization(Authorization $authorization)
    {
        if ($this->authorizations->contains($authorization)) {
            $this->authorizations->removeElement($authorization);
        }
        return $this;
    }

    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    /**
     * Checks if a given Client can access this Person's specified scope.
     * @param \PROCERGS\OAuthBundle\Entity\Client $client
     * @param mixed $scope can be a single scope or an array with several.
     * @return boolean
     */
    public function isAuthorizedClient(Client $client, $scope)
    {
        $authorizations = $this->getAuthorizations();
        foreach ($authorizations as $auth) {
            $c = $auth->getClient();
            if ($c->getId() == $client->getId()) {
                return $auth->hasScopes($scope);
            }
        }
        return false;
    }

    /**
     * Checks if this Person has any authorization for a given Client.
     * WARNING: Note that it does NOT validate scope!
     * @param \PROCERGS\OAuthBundle\Entity\Client $client
     */
    public function hasAuthorization(Client $client)
    {
        $authorizations = $this->getAuthorizations();
        foreach ($authorizations as $auth) {
            $c = $auth->getClient();
            if ($c->getId() == $client->getId()) {
                return true;
            }
        }
        return false;
    }

    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    public function getFacebookId()
    {
        return $this->facebookId;
    }

    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;

        return $this;
    }

    public function getTwitterId()
    {
        return $this->twitterId;
    }

    public function setTwitterUsername($twitterUsername)
    {
        $this->twitterUsername = $twitterUsername;

        return $this;
    }

    public function getTwitterUsername()
    {
        return $this->twitterUsername;
    }

    public function setTwitterAccessToken($twitterAccessToken)
    {
        $this->twitterAccessToken = $twitterAccessToken;

        return $this;
    }

    public function getTwitterAccessToken()
    {
        return $this->twitterAccessToken;
    }

    public function serialize()
    {
        $this->fullName = $this->getFullName();
        return serialize(array($this->facebookId, parent::serialize()));
    }

    public function unserialize($data)
    {
        list($this->facebookId, $parentData) = unserialize($data);
        parent::unserialize($parentData);
    }

    /**
     * Get the full name of the user (first + last name)
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstname() . ' ' . $this->getSurname();
    }

    /**
     * @param array
     */
    public function setFBData($fbdata)
    {
        if (isset($fbdata['id'])) {
            $this->setFacebookId($fbdata['id']);
            $this->addRole('ROLE_FACEBOOK');
        }
        if (isset($fbdata['first_name'])) {
            $this->setFirstName($fbdata['first_name']);
        }
        if (isset($fbdata['last_name'])) {
            $this->setSurname($fbdata['last_name']);
        }
        if (isset($fbdata['email'])) {
            $this->setEmail($fbdata['email']);
        }
        if (isset($fbdata['birthday'])) {
            $date = \DateTime::createFromFormat('m/d/Y', $fbdata['birthday']);
            $this->setBirthdate($date);
        }
    }

    public function setCpf($var)
    {
        $this->cpf = $var;

        return $this;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

}
