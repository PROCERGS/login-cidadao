<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\Fetcher;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository;
use LoginCidadao\RemoteClaimsBundle\Fetcher\RemoteClaimFetcher;
use LoginCidadao\RemoteClaimsBundle\Model\HttpUri;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use LoginCidadao\RemoteClaimsBundle\Tests\Http\HttpMocker;
use LoginCidadao\RemoteClaimsBundle\Tests\Parser\RemoteClaimParserTest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RemoteClaimFetcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the Claim fetch using HTTP URI.
     */
    public function testFetchByHttpUri()
    {
        $uri = 'https://dummy.com';

        $data = RemoteClaimParserTest::$claimMetadata;

        $fetcher = $this->getFetcher(new HttpMocker($data));
        $remoteClaim = $fetcher->fetchRemoteClaim($uri);

        $this->assertInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface', $remoteClaim);
        $this->assertEquals($data['claim_display_name'], $remoteClaim->getDisplayName());
    }

    /**
     * Test the Claim fetch using Tag URI.
     */
    public function testFetchByTagUri()
    {
        $uri = 'https://dummy.com';

        $data = RemoteClaimParserTest::$claimMetadata;
        $tagUri = $data['claim_name'];

        $httpMocker = new HttpMocker($data, $uri);
        $fetcher = $this->getFetcher($httpMocker);
        $remoteClaim = $fetcher->fetchRemoteClaim($tagUri);

        $requests = $httpMocker->getHistory()->getRequests();
        $firstRequest = reset($requests);

        $this->assertInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface', $remoteClaim);
        $this->assertEquals($data['claim_display_name'], $remoteClaim->getDisplayName());
        $this->assertCount(2, $requests);

        $webFingerUrl = HttpUri::createFromString($firstRequest->getUrl());
        $this->assertEquals('https', $webFingerUrl->getScheme());
        $this->assertEquals('example.com', $webFingerUrl->getHost());
        $this->assertEquals('/.well-known/webfinger', $webFingerUrl->getPath());

        $webFingerParams = explode('&', $webFingerUrl->getQuery());
        $this->assertContains('rel=http%3A%2F%2Fopenid.net%2Fspecs%2Fconnect%2F1.0%2Fclaim', $webFingerParams);
        $encodedTag = urlencode($tagUri);
        $this->assertContains("resource={$encodedTag}", $webFingerParams);
    }

    public function testFetchTagNotFound()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $tagUri = 'tag:example.com,2018:my_claim';
        $fetcher = $this->getFetcher();
        $fetcher->fetchRemoteClaim($tagUri);
    }

    public function testFetchUriNotFound()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $httpClient = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()->getMock();
        $httpClient->expects($this->once())->method('get')->willThrowException(new TransferException());

        $tagUri = 'https://claim.uri/dummy';
        $fetcher = $this->getFetcher($httpClient);
        $fetcher->fetchRemoteClaim($tagUri);
    }

    /**
     * This test assumes the Remote Claim is already known by the IdP.
     * The existing Remote Claim is expected to be returned.
     */
    public function testExistingClaim()
    {
        $claimUri = 'https://dummy.com';

        $provider = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');
        $provider->expects($this->once())->method('getClientId')->willReturn(['https://redirect.uri']);

        $existingClaim = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface');
        $existingClaim->expects($this->once())->method('getProvider')->willReturn($provider);

        $claimRepository = $this->getClaimRepository();
        $claimRepository->expects($this->once())->method('findOneBy')->willReturn($existingClaim);

        $clientManager = $this->getClientManager();
        $clientManager->expects($this->once())->method('getClientById')->willReturn($provider);

        $em = $this->getEntityManager();
        $em->expects($this->never())->method('persist');

        $fetcher = $this->getFetcher(null, $em, $claimRepository, $clientManager);
        $actual = $fetcher->getRemoteClaim($claimUri);

        $this->assertEquals($existingClaim, $actual);
    }

    /**
     * This method tests a new Remote Claim, unknown by the IdP.
     * The Claim MUST be fetched and persisted.
     */
    public function testNewClaim()
    {
        $claimUri = 'https://dummy.com';

        $provider = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');

        $existingClaim = null;

        $claimRepository = $this->getClaimRepository();
        $claimRepository->expects($this->once())->method('findOneBy')->willReturn($existingClaim);

        $clientManager = $this->getClientManager();
        $clientManager->expects($this->once())->method('getClientById')->willReturn($provider);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface'));

        $fetcher = $this->getFetcher(null, $em, $claimRepository, $clientManager);
        $actual = $fetcher->getRemoteClaim($claimUri);

        $this->assertInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface', $actual);
    }

    /**
     * Test that the method fails when the Claim Provider is not already persisted.
     */
    public function testNonExistentProvider()
    {
        $this->setExpectedException('LoginCidadao\RemoteClaimsBundle\Exception\ClaimProviderNotFoundException');
        $claimUri = 'https://dummy.com';

        $clientManager = $this->getClientManager();
        $clientManager->expects($this->once())->method('getClientById')->willReturn(null);

        $em = $this->getEntityManager();
        $em->expects($this->never())->method('persist');

        $fetcher = $this->getFetcher(null, $em, null, $clientManager);
        $fetcher->getRemoteClaim($claimUri);
    }

    public function testHttpErrorOnFetch()
    {
        $tagUri = 'tag:example.com,2018:my_claim';

        $httpClient = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()->getMock();
        $httpClient->expects($this->once())->method('get')
            ->willThrowException(new TransferException('Some error'));

        $fetcher = $this->getFetcher($httpClient);
        $this->setExpectedException('LoginCidadao\RemoteClaimsBundle\Exception\ClaimUriUnavailableException');
        $fetcher->discoverClaimUri($tagUri);
    }

    public function testDiscoveryFallback()
    {
        $tagUri = TagUri::createFromString('tag:example.com,2018:my_claim');

        $httpClient = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()->getMock();
        $httpClient->expects($this->once())->method('get')
            ->willThrowException(new TransferException('Some error'));

        $uri = 'https://my.claim.uri/';
        $remoteClaim = (new RemoteClaim())->setUri($uri);

        $claimRepo = $this->getClaimRepository();
        $claimRepo->expects($this->once())->method('findOneBy')->with(['name' => $tagUri])
            ->willReturn($remoteClaim);

        $fetcher = $this->getFetcher($httpClient, null, $claimRepo);
        $this->assertSame($uri, $fetcher->discoverClaimUri($tagUri));
    }

    /**
     * @param HttpMocker|Client|null $httpMocker
     * @param EntityManagerInterface|null $em
     * @param RemoteClaimRepository|null $claimRepository
     * @param ClientManager|null $clientManager
     * @param EventDispatcherInterface|null $dispatcher
     * @return RemoteClaimFetcher
     */
    private function getFetcher(
        $httpMocker = null,
        EntityManagerInterface $em = null,
        RemoteClaimRepository $claimRepository = null,
        ClientManager $clientManager = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        if ($httpMocker instanceof Client) {
            $httpClient = $httpMocker;
        } else {
            if ($httpMocker === null) {
                $httpMocker = new HttpMocker();
            }
            $httpClient = $httpMocker->getClient();
        }

        if ($em === null) {
            $em = $this->getEntityManager();
        }
        if ($claimRepository === null) {
            $claimRepository = $this->getClaimRepository();
        }
        if ($clientManager === null) {
            $clientManager = $this->getClientManager();
        }
        if ($dispatcher === null) {
            $dispatcher = $this->getDispatcher();
        }

        $fetcher = new RemoteClaimFetcher($httpClient, $em, $claimRepository, $clientManager, $dispatcher);

        return $fetcher;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        return $em;
    }

    /**
     * @return RemoteClaimRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClaimRepository()
    {
        return $this->getMockBuilder('LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return ClientManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientManager()
    {
        $manager = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Manager\ClientManager')
            ->disableOriginalConstructor()->getMock();

        return $manager;
    }

    /**
     * @return EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDispatcher()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }
}
