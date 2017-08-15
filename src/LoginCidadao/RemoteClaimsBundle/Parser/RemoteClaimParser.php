<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Parser;

use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimParserInterface;

class RemoteClaimParser implements RemoteClaimParserInterface
{
    public static function parseClaim(
        $claimMetadata,
        RemoteClaimInterface $claim,
        ClaimProviderInterface $provider = null
    ) {
        $claimMetadata = self::normalizeData($claimMetadata);

        $claim
            ->setName($claimMetadata->claim_name)
            ->setDisplayName($claimMetadata->claim_display_name)
            ->setDescription($claimMetadata->claim_description)
            ->setRecommendedScope($claimMetadata->claim_provider_recommended_scope)
            ->setEssentialScope($claimMetadata->claim_provider_essential_scope);

        if ($provider) {
            $claim->setProvider(self::parseClaimProvider($claimMetadata->claim_provider, $provider));
        }

        return $claim;
    }

    public static function parseClaimProvider($claimProviderMetadata, ClaimProviderInterface $provider)
    {
        $claimProviderMetadata = self::normalizeData($claimProviderMetadata);

        $provider
            ->setName($claimProviderMetadata->client_name)
            ->setRedirectUris($claimProviderMetadata->redirect_uris);

        return $provider;
    }

    /**
     * @param string|array|object $data
     * @return object
     */
    private static function normalizeData($data)
    {
        if (is_string($data)) {
            return json_decode($data, false);
        } elseif (is_array($data)) {
            return (object)$data;
        } elseif (!is_object($data)) {
            throw new \InvalidArgumentException('$data should be a string, array or object');
        }

        return $data;
    }
}
