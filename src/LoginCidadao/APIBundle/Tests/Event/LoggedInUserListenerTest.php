<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Tests\Event;

use LoginCidadao\APIBundle\Event\LoggedInUserListener;
use LoginCidadao\OAuthBundle\Entity\AccessToken;
use LoginCidadao\OAuthBundle\Entity\AccessTokenRepository;
use LoginCidadao\OAuthBundle\Entity\Client;
use SimpleThings\EntityAudit\AuditConfiguration;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoggedInUserListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelRequest()
    {
        $client = new Client();
        $accessTokenToken = 'access_token';
        $accessToken = new AccessToken();
        $accessToken->setToken($accessTokenToken);
        $accessToken->setClient($client);

        /** @var AccessTokenRepository|\PHPUnit_Framework_MockObject_MockObject $accessTokenRepo */
        $accessTokenRepo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\AccessTokenRepository')
            ->disableOriginalConstructor()->getMock();
        $accessTokenRepo->expects($this->once())
            ->method('findOneBy')->with(['token' => $accessTokenToken])
            ->willReturn($accessToken);

        $token = $this->getMock('FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken');
        $token->expects($this->exactly(2))
            ->method('getToken')->willReturn($accessTokenToken);

        /** @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject $tokenStorage */
        $tokenStorage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $auditConfig = new AuditConfiguration();

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $listener = new LoggedInUserListener($accessTokenRepo, $tokenStorage, $auditConfig);
        $listener->onKernelRequest($event);
    }

    public function testOnKernelRequestNotMaster()
    {
        /** @var AccessTokenRepository|\PHPUnit_Framework_MockObject_MockObject $accessTokenRepo */
        $accessTokenRepo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\AccessTokenRepository')
            ->disableOriginalConstructor()->getMock();

        /** @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject $tokenStorage */
        $tokenStorage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $tokenStorage->expects($this->never())->method('getToken');

        $auditConfig = new AuditConfiguration();

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getRequestType')->willReturn(HttpKernelInterface::SUB_REQUEST);

        $listener = new LoggedInUserListener($accessTokenRepo, $tokenStorage, $auditConfig);
        $listener->onKernelRequest($event);
    }

    public function testOnKernelRequestNotOAuthToken()
    {
        /** @var AccessTokenRepository|\PHPUnit_Framework_MockObject_MockObject $accessTokenRepo */
        $accessTokenRepo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\AccessTokenRepository')
            ->disableOriginalConstructor()->getMock();

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        /** @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject $tokenStorage */
        $tokenStorage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $auditConfig = new AuditConfiguration();

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $listener = new LoggedInUserListener($accessTokenRepo, $tokenStorage, $auditConfig);
        $listener->onKernelRequest($event);
    }
}
