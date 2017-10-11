<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Storage;

use Doctrine\ORM\EntityManager;
use OAuth2\OpenID\Storage\UserClaimsInterface;

class UserClaims implements UserClaimsInterface
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getUserClaims($user_id, $scope)
    {
        return null;
    }
}
