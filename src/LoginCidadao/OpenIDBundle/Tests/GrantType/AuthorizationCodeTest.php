<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\GrantType;

use LoginCidadao\OpenIDBundle\Storage\SessionState;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\AuthorizationCodeInterface;

class AuthorizationCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testGrantType()
    {
        /** @var SessionState|\PHPUnit_Framework_MockObject_MockObject $sessionStateStorage */
        $sessionStateStorage = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Storage\SessionState')
            ->disableOriginalConstructor()->getMock();
        $sessionStateStorage->expects($this->once())->method('getSessionState')->willReturn('sessionState');

        /** @var AuthorizationCodeInterface|\PHPUnit_Framework_MockObject_MockObject $authorizationCodeStorage */
        $authorizationCodeStorage = $this->getMock('OAuth2\Storage\AuthorizationCodeInterface');

        $authorizationCode = new AuthorizationCode($authorizationCodeStorage);
        $authorizationCode->setSessionStateStorage($sessionStateStorage);

        /** @var AccessTokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMock('OAuth2\ResponseType\AccessTokenInterface');
        $token->expects($this->once())->method('createAccessToken')->willReturn([]);

        $clientId = '123_my_id';
        $userId = 'theUser';
        $scope = 'scope1';

        $accessToken = $authorizationCode->createAccessToken($token, $clientId, $userId, $scope);

        $this->assertArrayHasKey('session_state', $accessToken);
        $this->assertSame('sessionState', $accessToken['session_state']);
    }
}
