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

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;
use LoginCidadao\APIBundle\Service\VersionService;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\AccessTokenManager;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorization;
use LoginCidadao\RemoteClaimsBundle\EventSubscriber\PersonSerializeEventSubscriber;
use LoginCidadao\RemoteClaimsBundle\Exception\ClaimUriUnavailableException;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use PHPUnit\Framework\TestCase;

class PersonSerializeEventSubscriberTest extends TestCase
{
    // PersonSerializeEventSubscriber

    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ], PersonSerializeEventSubscriber::getSubscribedEvents());
    }

    public function testRunsOnlyForPersonObjects()
    {
        $event = $this->getEvent();
        $event->expects($this->once())->method('getObject')->willReturn(new \stdClass());

        $subscriber = $this->getSubscriber();
        $subscriber->onPostSerialize($event);
    }

    public function testWontRunForVersion1_2_3()
    {
        $this->checkVersion([1, 2, 3], false);
    }

    public function testWillRunForVersion2_3_4()
    {
        $this->checkVersion([2, 3, 4], true);
    }

    public function testWillRunForVersion3_4_5()
    {
        $this->checkVersion([3, 4, 5], true);
    }

    public function testAddRemoteClaim()
    {
        $dummyJson = [];
        $endpoint = 'https://dummy/endpoint';
        $fetcher = $this->getFetcher();
        $fetcher->expects($this->once())->method('discoverClaimUri')->willReturn($endpoint);

        $this->runAddDistributedAndAggregatedClaimsTest($fetcher, $dummyJson);

        $tag = 'tag:example.com,2018-01:example';
        $accessToken = 'my_secret_access_token';
        $this->assertEquals([
            $tag => [
                'endpoint' => $endpoint,
                'access_token' => $accessToken,
            ],
        ], $dummyJson['_claim_sources']);

        $this->assertEquals([$tag => $tag], $dummyJson['_claim_names']);
    }

    public function testUnknownEndpoint()
    {
        $dummyJson = [];
        $fetcher = $this->getFetcher();
        $fetcher->expects($this->once())->method('discoverClaimUri')
            ->willThrowException(new ClaimUriUnavailableException());

        $this->runAddDistributedAndAggregatedClaimsTest($fetcher, $dummyJson);

        $this->assertEmpty($dummyJson['_claim_names']);
        $this->assertEmpty($dummyJson['_claim_sources']);
    }

    public function runAddDistributedAndAggregatedClaimsTest($fetcher, &$dummyJson)
    {
        $person = $this->getPerson();
        $client = $this->getClient();
        $tag = 'tag:example.com,2018-01:example';
        $claimName = TagUri::createFromString($tag);
        $accessToken = 'my_secret_access_token';

        $remoteClaims = [
            [
                'remoteClaim' => (new RemoteClaim())
                    ->setProvider($client)
                    ->setName($claimName),
                'authorization' => (new RemoteClaimAuthorization())
                    ->setAccessToken($accessToken),
            ],
        ];

        $visitor = $this->getVisitor();
        $visitor->expects($this->exactly(2))
            ->method('setData')->willReturnCallback(function ($key, $value) use (&$dummyJson) {
                $this->assertContains($key, ['_claim_names', '_claim_sources']);
                $dummyJson[$key] = $value;
            });

        $event = $this->getEvent();
        $event->expects($this->once())->method('getVisitor')->willReturn($visitor);
        $event->expects($this->exactly(2))->method('getObject')->willReturn($person);

        $accessTokenManager = $this->getAccessTokenManager();
        $accessTokenManager->expects($this->once())->method('getTokenClient')->willReturn($client);

        $remoteClaimManager = $this->getRemoteClaimManager();
        $remoteClaimManager->expects($this->once())
            ->method('getRemoteClaimsWithTokens')->with($client, $person)
            ->willReturn($remoteClaims);

        $versionService = $this->getVersionService([2, 0, 0]);

        $subscriber = $this->getSubscriber($accessTokenManager, $remoteClaimManager, $fetcher, $versionService);
        $subscriber->onPostSerialize($event);
    }

    /**
     * @param array $version
     * @param bool $shouldRun
     */
    private function checkVersion($version, $shouldRun)
    {
        $event = $this->getEvent();
        $event->expects($this->once())->method('getObject')->willReturn($this->getPerson());
        if ($shouldRun) {
            $event->expects($this->once())->method('getVisitor')->willReturn(new \stdClass());
        } else {
            $event->expects($this->never())->method('getVisitor');
        }

        $versionService = $this->getVersionService($version);

        $subscriber = $this->getSubscriber(null, null, null, $versionService);
        $subscriber->onPostSerialize($event);
    }

    /**
     * @return ObjectEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEvent()
    {
        return $this->getMockBuilder('JMS\Serializer\EventDispatcher\ObjectEvent')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return PersonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPerson()
    {
        return $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
    }

    /**
     * @return ClaimProviderInterface|ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClient()
    {
        return $this->createMock('LoginCidadao\OAuthBundle\Entity\Client');
    }

    /**
     * @return AccessTokenManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAccessTokenManager()
    {
        return $this->getMockBuilder('LoginCidadao\OAuthBundle\Model\AccessTokenManager')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return RemoteClaimManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRemoteClaimManager()
    {
        return $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface');
    }

    /**
     * @return RemoteClaimFetcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFetcher()
    {
        return $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface');
    }

    /**
     * @return GenericSerializationVisitor|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getVisitor()
    {
        return $this->getMockBuilder('JMS\Serializer\GenericSerializationVisitor')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @param array|null $version
     * @return VersionService|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getVersionService($version = null)
    {
        $versionService = $this->getMockBuilder('LoginCidadao\APIBundle\Service\VersionService')
            ->disableOriginalConstructor()->getMock();

        if ($version !== null) {
            $versionString = implode('.', $version);
            $version = [
                'major' => $version[0],
                'minor' => $version[1],
                'patch' => $version[2],
            ];

            $versionService->expects($this->once())->method('getVersionFromRequest')->willReturn($version);
            $versionService->expects($this->once())->method('getString')->with($version)->willReturn($versionString);
        }

        return $versionService;
    }

    private function getSubscriber(
        $accessTokenManager = null,
        $remoteClaimManager = null,
        $fetcher = null,
        $versionService = null
    ) {
        if ($accessTokenManager === null) {
            $accessTokenManager = $this->getAccessTokenManager();
        }
        if ($remoteClaimManager === null) {
            $remoteClaimManager = $this->getRemoteClaimManager();
        }
        if ($fetcher === null) {
            $fetcher = $this->getFetcher();
        }
        if ($versionService === null) {
            $versionService = $this->getVersionService();
        }

        return new PersonSerializeEventSubscriber($accessTokenManager, $remoteClaimManager, $fetcher, $versionService);
    }
}
