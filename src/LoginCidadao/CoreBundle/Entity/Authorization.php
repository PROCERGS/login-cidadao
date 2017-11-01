<?php

namespace LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\Client;

/**
 * @ORM\Entity(repositoryClass="LoginCidadao\CoreBundle\Entity\AuthorizationRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="auth",uniqueConstraints={@ORM\UniqueConstraint(name="unique_person_client", columns={"person_id", "client_id"})})
 */
class Authorization
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    protected $scope;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="authorizations")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     */
    protected $person;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client", inversedBy="authorizations")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    protected $client;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param PersonInterface|null $person
     */
    public function setPerson(PersonInterface $person = null)
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
     * @param \LoginCidadao\OAuthBundle\Entity\Client $client
     */
    public function setClient(Client $client = null)
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getScope()
    {
        $scope = $this->enforcePublicProfileScope(array_filter($this->scope));

        return array_unique($scope);
    }

    /**
     * @param array|string $scope
     */
    public function setScope($scope)
    {
        $scope = $this->enforcePublicProfileScope(Authorization::enforceArray($scope));
        $this->scope = $scope;
    }

    /**
     * @param mixed $needed
     * @return boolean
     */
    public function hasScopes($needed)
    {
        foreach (Authorization::enforceArray($needed) as $n) {
            if (array_search($n, $this->getScope()) === false) {
                return false;
            }
        }

        return true;
    }

    protected function enforcePublicProfileScope($scope)
    {
        if (array_search('public_profile', $scope) === false) {
            $scope[] = 'public_profile';
        }

        return $scope;
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

    /**
     * Enforces that a scope is an array
     *
     * @param $scope
     * @return array
     */
    public static function enforceArray($scope)
    {
        if (is_array($scope)) {
            return $scope;
        }

        if (is_bool($scope) || is_null($scope) || $scope === '') {
            return [];
        }

        return explode(' ', $scope);
    }
}
