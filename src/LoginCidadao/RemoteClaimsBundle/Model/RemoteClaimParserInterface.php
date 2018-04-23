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

use Emarref\Jwt\Token;

interface RemoteClaimParserInterface
{
    /**
     * @param string|array|object $claimMetadata Claim Metadata encoded as JSON in a string, as an array or as an object
     * @param RemoteClaimInterface $claim
     * @param ClaimProviderInterface $provider
     * @return RemoteClaimInterface
     */
    public static function parseClaim(
        $claimMetadata,
        RemoteClaimInterface $claim,
        ClaimProviderInterface $provider = null
    );

    /**
     * @param string|array|object $claimProviderMetadata Claim Provider Metadata encoded as JSON in a string, as an array or as an object
     * @param ClaimProviderInterface $provider
     * @return ClaimProviderInterface
     */
    public static function parseClaimProvider($claimProviderMetadata, ClaimProviderInterface $provider);

    /**
     * @param Token|string $jwt
     * @param RemoteClaimInterface $claim
     * @param ClaimProviderInterface|null $provider
     * @return RemoteClaimInterface
     */
    public static function parseJwt($jwt, RemoteClaimInterface $claim, ClaimProviderInterface $provider = null);
}
