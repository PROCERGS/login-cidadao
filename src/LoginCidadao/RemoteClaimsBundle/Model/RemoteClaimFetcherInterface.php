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

interface RemoteClaimFetcherInterface
{
    /**
     * @param TagUri|string $claimUri
     * @return RemoteClaimInterface
     */
    public function fetchRemoteClaim($claimUri);

    /**
     * @param TagUri $claimName
     * @return string
     */
    public function discoverClaimUri(TagUri $claimName);
}
