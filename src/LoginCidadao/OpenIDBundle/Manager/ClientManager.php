<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Manager;

use Doctrine\ORM\EntityManager;
use PROCERGS\OAuthBundle\Model\AccessTokenManager;

class ClientManager
{
    /** @var EntityManager */
    private $em;

    /** @var AccessTokenManager */
    private $accessTokenManager;

    public function __construct(EntityManager $em,
                                AccessTokenManager $accessTokenManager)
    {
        $this->em = $em;

        $this->accessTokenManager = $accessTokenManager;
    }

    public function getClient()
    {
        
    }
}
