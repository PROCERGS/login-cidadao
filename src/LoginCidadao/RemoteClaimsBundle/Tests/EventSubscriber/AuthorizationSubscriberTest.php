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

use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use LoginCidadao\RemoteClaimsBundle\EventSubscriber\AuthorizationSubscriber;

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
        $fetcher = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface');
        $fetcher->expects($this->atLeastOnce())->method('getRemoteClaim')
            ->willReturnCallback(function ($scope) use (&$remoteClaims) {
                $remoteClaims[] = $scope;

                return $this->getRemoteClaim();
            });

        $event = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->atLeastOnce())->method('getScope')->willReturn($scope);

        $subscriber = new AuthorizationSubscriber($fetcher);
        $subscriber->onNewAuthorizationRequest($event);

        $this->assertCount(2, $remoteClaims);
    }

    private function getRemoteClaim()
    {
        $remoteClaim = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface');

        return $remoteClaim;
    }
}
