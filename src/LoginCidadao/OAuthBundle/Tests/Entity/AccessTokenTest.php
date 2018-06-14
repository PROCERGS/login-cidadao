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
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    /**
     * @group time-sensitive
     */
    public function testEntity()
    {
        $idToken = 'id_token';

        $accessToken = new AccessToken();
        $accessToken
            ->setIdToken($idToken)
            ->setCreatedAtValue();

        $this->assertFalse($accessToken->hasExpired());

        $accessToken->setExpired();
        sleep(3);

        $this->assertEquals($idToken, $accessToken->getIdToken());
        $this->assertTrue($accessToken->hasExpired());
        $this->assertInstanceOf('\DateTime', $accessToken->getCreatedAt());
    }
}
