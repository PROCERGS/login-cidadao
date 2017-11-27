<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Entity;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;

class RemoteClaimAuthorization implements RemoteClaimAuthorizationInterface
{
    /** @var ClaimProviderInterface */
    private $claimProvider;

    /** @var ClientInterface */
    private $client;

    /** @var PersonInterface */
    private $person;

    /** @var TagUri */
    private $claimName;

    /** @var string */
    private $accessToken;

    /**
     * @param ClaimProviderInterface $claimProvider
     * @return RemoteClaimAuthorizationInterface
     */
    public function setClaimProvider(ClaimProviderInterface $claimProvider)
    {
        $this->claimProvider = $claimProvider;

        return $this;
    }

    /**
     * @param string $accessToken
     * @return RemoteClaimAuthorizationInterface
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return ClaimProviderInterface
     */
    public function getClaimProvider()
    {
        return $this->claimProvider;
    }

    /**
     * Defines the entity that will be given access to the information provided by the Claim Provider
     *
     * @param ClientInterface $client
     * @return RemoteClaimAuthorizationInterface
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the tag this authorization refers to.
     *
     * @param TagUri $claimName
     * @return RemoteClaimAuthorizationInterface
     */
    public function setClaimName(TagUri $claimName)
    {
        $this->claimName = $claimName;

        return $this;
    }

    /**
     * @return TagUri
     */
    public function getClaimName()
    {
        return $this->claimName;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param PersonInterface $person
     * @return RemoteClaimAuthorization
     */
    public function setPerson(PersonInterface $person)
    {
        $this->person = $person;

        return $this;
    }
}
