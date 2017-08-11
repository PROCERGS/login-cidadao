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
     * Get the name of the Claim Provider
     *
     * @return string
     */
    public function getName();

    /**
     * Get the redirect_uris allowed for this Claim Provider
     *
     * @return string[]
     */
    public function getRedirectUris();

    /**
     * Get the recommended scope needed by this Claim Provider
     *
     * @return string|string[]
     */
    public function getScope();

    /**
     * Get the essential scope needed by this Claim Provider.
     *
     * @return string|string[]
     */
    public function getEssentialScope();
}
