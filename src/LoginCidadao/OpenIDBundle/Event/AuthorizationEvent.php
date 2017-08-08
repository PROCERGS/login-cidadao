<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Event;

use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Symfony\Component\EventDispatcher\Event;

class AuthorizationEvent extends Event
{
    /** @var Authorization */
    private $authorization;

    /** @var PersonInterface */
    private $person;

    /** @var ClientInterface */
    private $client;

    /** @var string[]|string */
    private $scope;

    /**
     * AuthorizationEvent constructor
     * @param PersonInterface $person
     * @param ClientInterface $client
     * @param string|string[] $scope scope authorized by the End-User
     */
    public function __construct(PersonInterface $person, ClientInterface $client, $scope)
    {
        $this->person = $person;
        $this->client = $client;
        $this->setScope($scope);
    }

    /**
     * @return string|string[]
     */
    public function getScope()
    {
        return $this->scope;
    }

    private function setScope($scope)
    {
        if (!is_array($scope)) {
            $scope = explode(' ', $scope);
        }
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return Authorization
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * @param Authorization $authorization
     * @return AuthorizationEvent
     */
    public function setAuthorization($authorization)
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
}
