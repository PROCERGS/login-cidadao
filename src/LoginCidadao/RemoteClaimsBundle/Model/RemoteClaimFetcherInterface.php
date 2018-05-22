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

use LoginCidadao\RemoteClaimsBundle\Exception\ClaimUriUnavailableException;

interface RemoteClaimFetcherInterface
{
    /**
     * @param TagUri|string $claimUri
     * @return RemoteClaimInterface
     */
    public function fetchRemoteClaim($claimUri);

    /**
     * Tries to discover the current Claim URI.
     *
     * If discovery succeeds the value is persisted into the RemoteClaimInterface.
     *
     * If the discovery fails then the persisted value will be returned, if present.
     * @param TagUri|string $claimName
     * @return string Claim URI if the discovery process succeeds. Persisted (cached) version otherwise.
     * @throws ClaimUriUnavailableException when the Claim URI can't be determined.
     */
    public function discoverClaimUri($claimName);

    /**
     * Fetches a RemoteClaimInterface via <code>fetchRemoteClaim</code>, persisting and returning the result.
     * @param TagUri|string $claimUri
     * @return RemoteClaimInterface
     */
    public function getRemoteClaim($claimUri);
}
