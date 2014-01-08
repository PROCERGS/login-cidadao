<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PROCERGS\OAuthBundle\Entity\Client;

/**
 * @ORM\Entity
 */
class Authorization
{

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    protected $scope;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="authorizations")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     */
    protected $person;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="PROCERGS\OAuthBundle\Entity\Client", inversedBy="people")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    protected $client;

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param \PROCERGS\LoginCidadao\CoreBundle\Entity\Person $person
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param \PROCERGS\OAuthBundle\Entity\Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param array $scope
     */
    public function setScope(array $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param mixed $needed
     * @return boolean
     */
    public function hasScopes($needed)
    {
        if (!is_array($needed)) {
            $needed = array($needed);
        }

        foreach ($needed as $n) {
            if (array_search($n, $this->getScope()) === false) {
                return false;
            }
        }
        return true;
    }

}
