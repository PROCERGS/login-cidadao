<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Model;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;

interface RemoteClaimAuthorizationInterface
{
    /**
     * Defines the entity responsible for providing extra information about the User
     *
     * @param ClaimProviderInterface $claimProvider
     * @return RemoteClaimAuthorizationInterface
     */
    public function setClaimProvider(ClaimProviderInterface $claimProvider);

    /**
     * @return ClaimProviderInterface
     */
    public function getClaimProvider();

    /**
     * Defines the entity that will be given access to the information provided by the Claim Provider
     *
     * @param ClientInterface $client
     * @return RemoteClaimAuthorizationInterface
     */
    public function setClient(ClientInterface $client);

    /**
     * @return ClientInterface
     */
    public function getClient();

    /**
     * @param PersonInterface $user
     * @return RemoteClaimAuthorizationInterface
     */
    public function setPerson(PersonInterface $user);

    /**
     * @return PersonInterface
     */
    public function getPerson();

    /**
     * Set the tag this authorization refers to.
     *
     * @param TagUri $claimName
     * @return RemoteClaimAuthorizationInterface
     */
    public function setClaimName(TagUri $claimName);

    /**
     * @return TagUri
     */
    public function getClaimName();

    /**
     * @param string $accessToken
     * @return RemoteClaimAuthorizationInterface
     */
    public function setAccessToken($accessToken);

    /**
     * @return string
     */
    public function getAccessToken();
}
