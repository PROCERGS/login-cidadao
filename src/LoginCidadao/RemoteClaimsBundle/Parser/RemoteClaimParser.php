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

use Emarref\Jwt\Jwt;
use Emarref\Jwt\Token;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimParserInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;

class RemoteClaimParser implements RemoteClaimParserInterface
{
    /**
     * @param array|object|string $claimMetadata
     * @param RemoteClaimInterface $claim
     * @param ClaimProviderInterface|null $provider
     * @return RemoteClaimInterface
     */
    public static function parseClaim(
        $claimMetadata,
        RemoteClaimInterface $claim,
        ClaimProviderInterface $provider = null
    ) {
        $claimMetadata = self::normalizeData($claimMetadata);
        $claimName = TagUri::createFromString($claimMetadata->claim_name);

        $claim
            ->setName($claimName)
            ->setDisplayName($claimMetadata->claim_display_name)
            ->setDescription($claimMetadata->claim_description)
            ->setRecommendedScope($claimMetadata->claim_provider_recommended_scope ?? [])
            ->setEssentialScope($claimMetadata->claim_provider_essential_scope ?? []);

        if ($provider) {
            $claim->setProvider(self::parseClaimProvider($claimMetadata->claim_provider, $provider));
        }

        return $claim;
    }

    public static function parseClaimProvider($claimProviderMetadata, ClaimProviderInterface $provider)
    {
        $claimProviderMetadata = self::normalizeData($claimProviderMetadata);

        $provider
            ->setClientId($claimProviderMetadata->client_id)
            ->setName($claimProviderMetadata->client_name)
            ->setRedirectUris($claimProviderMetadata->redirect_uris ?? []);

        return $provider;
    }

    public static function parseJwt($jwt, RemoteClaimInterface $claim, ClaimProviderInterface $provider = null)
    {
        if (!$jwt instanceof Token) {
            $jwt = (new Jwt())->deserialize($jwt);
        }

        return self::parseClaim($jwt->getPayload()->jsonSerialize(), $claim, $provider);
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
            throw new \InvalidArgumentException("The metadata should be a string, array or object");
        }

        return $data;
    }
}
