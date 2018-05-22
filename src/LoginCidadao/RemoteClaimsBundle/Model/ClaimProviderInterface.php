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

interface ClaimProviderInterface
{
    /**
     * @return string
     */
    public function getClientId();

    /**
     * @param string $clientId
     * @return ClaimProviderInterface
     */
    public function setClientId($clientId);

    /**
     * Get the name of the Claim Provider
     *
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return ClaimProviderInterface
     */
    public function setName($name);

    /**
     * Get the redirect_uris allowed for this Claim Provider
     *
     * @return string[]
     */
    public function getRedirectUris();

    /**
     * @param string[] $redirectUris
     * @return ClaimProviderInterface
     */
    public function setRedirectUris(array $redirectUris);
}
