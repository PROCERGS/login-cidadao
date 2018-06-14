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

use LoginCidadao\OAuthBundle\Entity\RefreshToken;
use PHPUnit\Framework\TestCase;

class RefreshTokenTest extends TestCase
{
    /**
     * @group time-sensitive
     */
    public function testEntity()
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setExpired();
        sleep(1);

        $this->assertTrue($refreshToken->hasExpired());
    }
}
