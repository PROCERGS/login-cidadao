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

interface RemoteClaimInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return RemoteClaimInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDisplayName();

    /**
     * @param string $displayName
     * @return RemoteClaimInterface
     */
    public function setDisplayName($displayName);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return RemoteClaimInterface
     */
    public function setDescription($description);

    /**
     * @return string[]
     */
    public function getRecommendedScope();

    /**
     * @param string|string[] $recommendedScope
     * @return RemoteClaimInterface
     */
    public function setRecommendedScope($recommendedScope);

    /**
     * @return string[]
     */
    public function getEssentialScope();

    /**
     * @param string|string[] $essentialScope
     * @return RemoteClaimInterface
     */
    public function setEssentialScope($essentialScope);

    /**
     * @return ClaimProviderInterface
     */
    public function getProvider();

    /**
     * @param ClaimProviderInterface $provider
     * @return RemoteClaimInterface
     */
    public function setProvider(ClaimProviderInterface $provider);
}
