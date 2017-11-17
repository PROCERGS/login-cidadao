<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\EventSubscriber;

use LoginCidadao\OpenIDBundle\Event\AuthorizationEvent;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use LoginCidadao\RemoteClaimsBundle\EventSubscriber\AuthorizationSubscriber;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AuthorizationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION_REQUEST => 'onNewAuthorizationRequest'],
            AuthorizationSubscriber::getSubscribedEvents()
        );
    }

    public function testOnNewAuthorizationRequest()
    {
        $scope = [
            'openid',
            'simple_claim',
            'https://claim.provider.example.com/my-claim',
            'tag:example.com,2017:my_claim',
        ];

        $remoteClaims = [];

        /** @var \PHPUnit_Framework_MockObject_MockObject|RemoteClaimFetcherInterface $fetcher */
        $fetcher = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface');
        $fetcher->expects($this->atLeastOnce())->method('getRemoteClaim')
            ->willReturnCallback(function ($scope) use (&$remoteClaims) {
                $remoteClaims[] = $scope;

                return $this->getRemoteClaim();
            });

        /** @var \PHPUnit_Framework_MockObject_MockObject|AuthorizationEvent $event */
        $event = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->atLeastOnce())->method('getScope')->willReturn($scope);

        $subscriber = new AuthorizationSubscriber($fetcher);
        $subscriber->onNewAuthorizationRequest($event);

        $this->assertCount(2, $remoteClaims);
    }

    public function testOnRemoteClaimNotFound()
    {
        $scope = [
            'openid',
            'simple_claim',
            'https://not.actually.a.remote.claim/fake',
        ];

        $remoteClaims = [];

        /** @var \PHPUnit_Framework_MockObject_MockObject|RemoteClaimFetcherInterface $fetcher */
        $fetcher = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface');
        $fetcher->expects($this->atLeastOnce())->method('getRemoteClaim')
            ->willThrowException(new \RuntimeException('Random error'));

        /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('log')->with(LogLevel::ERROR);

        /** @var \PHPUnit_Framework_MockObject_MockObject|AuthorizationEvent $event */
        $event = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->atLeastOnce())->method('getScope')->willReturn($scope);

        $subscriber = new AuthorizationSubscriber($fetcher);
        $subscriber->setLogger($logger);
        $subscriber->onNewAuthorizationRequest($event);

        $this->assertEmpty($remoteClaims);
    }

    private function getRemoteClaim()
    {
        $remoteClaim = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface');

        return $remoteClaim;
    }
}
