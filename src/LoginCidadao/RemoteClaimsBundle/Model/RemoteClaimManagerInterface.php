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
}
