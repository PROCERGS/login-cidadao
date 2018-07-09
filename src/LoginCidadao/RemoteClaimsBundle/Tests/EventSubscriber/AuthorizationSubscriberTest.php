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

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OpenIDBundle\Event\AuthorizationEvent;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use LoginCidadao\RemoteClaimsBundle\EventSubscriber\AuthorizationSubscriber;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthorizationSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION_REQUEST => 'onNewAuthorizationRequest',
            LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION => ['onNewAuthorization', 100],
            LoginCidadaoOpenIDEvents::UPDATE_AUTHORIZATION => ['onUpdateAuthorization', 100],
            LoginCidadaoOpenIDEvents::REVOKE_AUTHORIZATION => 'onRevokeAuthorization',
        ], AuthorizationSubscriber::getSubscribedEvents()
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

        $fetcher = $this->getRemoteClaimFetcher();
        $fetcher->expects($this->atLeastOnce())->method('getRemoteClaim')
            ->willReturnCallback(function ($scope) use (&$remoteClaims) {
                $remoteClaims[] = $scope;

                return $this->getRemoteClaim();
            });

        $event = $this->getEvent();
        $event->expects($this->atLeastOnce())->method('getScope')->willReturn($scope);

        $subscriber = new AuthorizationSubscriber($this->getRemoteClaimManager(), $fetcher, $this->getAuthChecker());
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

        $fetcher = $this->getRemoteClaimFetcher();
        $fetcher->expects($this->atLeastOnce())->method('getRemoteClaim')
            ->willThrowException(new \RuntimeException('Random error'));

        /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface $logger */
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('log')->with(LogLevel::ERROR);

        $event = $this->getEvent();
        $event->expects($this->atLeastOnce())->method('getScope')->willReturn($scope);

        $subscriber = new AuthorizationSubscriber($this->getRemoteClaimManager(), $fetcher, $this->getAuthChecker());
        $subscriber->setLogger($logger);
        $subscriber->onNewAuthorizationRequest($event);

        $this->assertEmpty($remoteClaims);
    }

    public function testOnNewAuthorization()
    {
        $test = $this->prepareEnforceRemoteClaimsTest();

        /** @var AuthorizationSubscriber $subscriber */
        $subscriber = $test['subscriber'];

        /** @var AuthorizationEvent $event */
        $event = $test['event'];

        $subscriber->onNewAuthorization($event);
    }

    public function testOnUpdateAuthorization()
    {
        $test = $this->prepareEnforceRemoteClaimsTest();

        /** @var AuthorizationSubscriber $subscriber */
        $subscriber = $test['subscriber'];

        /** @var AuthorizationEvent $event */
        $event = $test['event'];

        $subscriber->onUpdateAuthorization($event);
    }

    public function testOnRevokeAuthorizationWithRemoteClaims()
    {
        $remoteClaims = [$this->getCompleteRemoteClaim(), $this->getCompleteRemoteClaim()];

        $authorization = $this->createMock('LoginCidadao\CoreBundle\Entity\Authorization');

        $event = $this->getEvent();
        $event->expects($this->once())->method('getRemoteClaims')->willReturn($remoteClaims);
        $event->expects($this->once())->method('getAuthorization')->willReturn($authorization);

        $manager = $this->getRemoteClaimManager();
        $manager->expects($this->once())
            ->method('revokeAllAuthorizations')
            ->with($this->isInstanceOf('LoginCidadao\CoreBundle\Entity\Authorization'));

        $subscriber = new AuthorizationSubscriber($manager, $this->getRemoteClaimFetcher(), $this->getAuthChecker());
        $subscriber->onRevokeAuthorization($event);
    }

    public function testOnRevokeAuthorizationWithoutRemoteClaims()
    {
        $event = $this->getEvent();
        $event->expects($this->once())->method('getRemoteClaims')->willReturn(null);

        $manager = $this->getRemoteClaimManager();
        $manager->expects($this->never())->method('revokeAllAuthorizations');

        $subscriber = new AuthorizationSubscriber($manager, $this->getRemoteClaimFetcher(), $this->getAuthChecker());
        $subscriber->onRevokeAuthorization($event);
    }

    public function testFeatureFlagOff()
    {
        $event = $this->getEvent();
        $event->expects($this->never())->method('addRemoteClaim');

        $claimManager = $this->getRemoteClaimManager();
        $claimFetcher = $this->getRemoteClaimFetcher();
        $subscriber = new AuthorizationSubscriber($claimManager, $claimFetcher, $this->getAuthChecker(false));

        $this->assertNull($subscriber->onNewAuthorizationRequest($event));
        $this->assertNull($subscriber->onNewAuthorization($event));
        $this->assertNull($subscriber->onUpdateAuthorization($event));
    }

    private function prepareEnforceRemoteClaimsTest()
    {
        $remoteClaims = [$this->getCompleteRemoteClaim(), $this->getCompleteRemoteClaim()];

        $event = $this->getEvent();
        $event->expects($this->once())->method('getRemoteClaims')->willReturn($remoteClaims);
        $event->expects($this->exactly(2))->method('getClient')->willReturn($this->getClient());
        $event->expects($this->exactly(2))->method('getPerson')->willReturn($this->getPerson());

        $manager = $this->getRemoteClaimManager();
        $manager->expects($this->exactly(2))
            ->method('enforceAuthorization')
            ->with($this->isInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface'));

        $subscriber = new AuthorizationSubscriber($manager, $this->getRemoteClaimFetcher(), $this->getAuthChecker());

        return [
            'subscriber' => $subscriber,
            'event' => $event,
        ];
    }

    private function getRemoteClaim()
    {
        $remoteClaim = $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface');

        return $remoteClaim;
    }

    private function getCompleteRemoteClaim($name = null)
    {
        $provider = $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');

        $remoteClaim = $this->getRemoteClaim();
        $remoteClaim->expects($this->any())->method('getProvider')
            ->willReturn($provider);
        $remoteClaim->expects($this->any())->method('getName')
            ->willReturn($name ?: TagUri::createFromString('tag:example.com,2017:my_claim'));

        return $remoteClaim;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteClaimManagerInterface
     */
    private function getRemoteClaimManager()
    {
        return $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteClaimFetcherInterface
     */
    private function getRemoteClaimFetcher()
    {
        return $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    private function getClient()
    {
        return $this->createMock('LoginCidadao\OAuthBundle\Model\ClientInterface');
    }

    /**
     * @return AuthorizationEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEvent()
    {
        return $this->getMockBuilder('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PersonInterface
     */
    private function getPerson()
    {
        return $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthorizationCheckerInterface
     */
    private function getAuthChecker($isGranted = true)
    {
        $checker = $this->createMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $checker->expects($this->any())->method('isGranted')->willReturn($isGranted);

        return $checker;
    }
}
