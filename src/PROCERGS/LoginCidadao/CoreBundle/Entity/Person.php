<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use PROCERGS\OAuthBundle\Entity\Client;

/**
 * @ORM\Entity
 */
class Person extends BaseUser
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
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
    protected $name;

    /**
     * @ORM\Column(type="date")
     */
    protected $birthdate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $cep;

    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;

    /**
     * @ORM\OneToMany(targetEntity="Authorization", mappedBy="person")
     */
    protected $authorizations;

    public function __construct()
    {
        parent::__construct();
        $this->authorizations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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
        $this->authorizations[] = $authorization;
    }

    public function getAuthorizations()
    {
        return $this->authorizations;
    }

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

}
