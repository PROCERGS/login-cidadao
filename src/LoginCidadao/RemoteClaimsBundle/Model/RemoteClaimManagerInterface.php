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

use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;

interface RemoteClaimManagerInterface
{
    /**
     * @param RemoteClaimAuthorizationInterface $authorization
     * @return RemoteClaimAuthorizationInterface
     */
    public function enforceAuthorization(RemoteClaimAuthorizationInterface $authorization);

    /**
     * @param TagUri|string $claimName
     * @param PersonInterface $person
     * @param ClientInterface $client
     * @return bool
     */
    public function isAuthorized($claimName, PersonInterface $person, ClientInterface $client);

    /**
     * @param Authorization $authorization
     * @return bool
     */
    public function revokeAllAuthorizations(Authorization $authorization);

    /**
     * Removes TagURI scopes from the given input.
     *
     * @param string|array $scope
     * @return string|array Returns the input with TagURI scopes removed. The type will remain the same as the input.
     */
    public function filterRemoteClaims($scope);

    /**
     * Get an already persisted RemoteClaimInterface without trying to fetch it again.
     *
     * @param TagUri $claimName
     * @return RemoteClaimInterface
     */
    public function getExistingRemoteClaim(TagUri $claimName);

    /**
     * @param Authorization $authorization
     * @return RemoteClaimInterface[]
     */
    public function getRemoteClaimsFromAuthorization(Authorization $authorization);

    /**
     * @param Authorization $authorization
     * @return RemoteClaimAuthorizationInterface[]
     */
    public function getRemoteClaimsAuthorizationsFromAuthorization(Authorization $authorization);

    /**
     * The response will be in the format:
     * [
     *   [
     *     'authorization' => RemoteClaimAuthorizationInterface,
     *     'remoteClaim' => RemoteClaimInterface,
     *   ],
     * ]
     * @param ClientInterface $client
     * @param PersonInterface $person
     * @return RemoteClaimInterface[]
     */
    public function getRemoteClaimsWithTokens(ClientInterface $client, PersonInterface $person);

    /**
     * @param ClaimProviderInterface $claimProvider
     * @param string $accessToken
     * @return RemoteClaimAuthorizationInterface|null
     */
    public function getRemoteClaimAuthorizationByAccessToken(ClaimProviderInterface $claimProvider, $accessToken);

    /**
     * @param TagUri $claimName
     * @param string $uri the new URI
     * @return RemoteClaimInterface the updated Remote Claim
     */
    public function updateRemoteClaimUri(TagUri $claimName, $uri);

    /**
     * @param RemoteClaimAuthorizationInterface $claimAuthorization
     * @param RemoteClaimInterface $remoteClaim
     * @return Authorization
     */
    public function enforceImplicitAuthorization(
        RemoteClaimAuthorizationInterface $claimAuthorization,
        RemoteClaimInterface $remoteClaim
    ): Authorization;
}
