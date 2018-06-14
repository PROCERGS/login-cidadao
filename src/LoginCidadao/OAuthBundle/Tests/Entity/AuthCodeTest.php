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

use LoginCidadao\OAuthBundle\Entity\AuthCode;
use PHPUnit\Framework\TestCase;

class AuthCodeTest extends TestCase
{
    public function testAuthCode()
    {
        $idToken = 'id_token';
        $sessionId = 'session_id';

        $authCode = new AuthCode();
        $authCode
            ->setIdToken($idToken)
            ->setSessionId($sessionId);

        $this->assertEquals($idToken, $authCode->getIdToken());
        $this->assertEquals($sessionId, $authCode->getSessionId());
    }
}
