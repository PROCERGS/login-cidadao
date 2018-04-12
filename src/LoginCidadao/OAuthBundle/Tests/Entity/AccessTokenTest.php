<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Tests\Entity;

use LoginCidadao\OAuthBundle\Entity\AccessToken;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessToken()
    {
        $idToken = 'jwt.id.token';
        $accessToken = new AccessToken();
        $accessToken->setExpired();
        $accessToken->setIdToken($idToken);
        $accessToken->setCreatedAtValue();

        $this->assertSame($idToken, $accessToken->getIdToken());
        $this->assertInstanceOf('\DateTime', $accessToken->getCreatedAt());
        $this->assertInternalType('int', $accessToken->getExpiresAt());
    }
}
