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
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository;
use LoginCidadao\RemoteClaimsBundle\Fetcher\RemoteClaimFetcher;
use LoginCidadao\RemoteClaimsBundle\Tests\Http\HttpMocker;
use LoginCidadao\RemoteClaimsBundle\Tests\Parser\RemoteClaimParserTest;

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
        $this->assertEquals('https://example.com?rel=http%3A%2F%2Fopenid.net%2Fspecs%2Fconnect%2F1.0%2Fclaim',
            $firstRequest->getUrl());
    }

    /**
     * This test assumes the Remote Claim is already known by the IdP.
     * The existing Remote Claim is expected to be returned.
     */
    public function testExistingClaim()
    {
        $claimUri = 'https://dummy.com';

        $provider = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');
        $provider->expects($this->once())->method('getRedirectUris')->willReturn(['https://redirect.uri']);

        $existingClaim = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface');
        $existingClaim->expects($this->once())->method('getProvider')->willReturn($provider);

        $claimRepository = $this->getClaimRepository();
        $claimRepository->expects($this->once())->method('findOneBy')->willReturn($existingClaim);

        $clientRepository = $this->getClientRepository();
        $clientRepository->expects($this->once())->method('findByRedirectUris')->willReturn([$provider]);

        $em = $this->getEntityManager();
        $em->expects($this->never())->method('persist');

        $fetcher = $this->getFetcher(null, $em, $claimRepository, $clientRepository);
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

        $clientRepository = $this->getClientRepository();
        $clientRepository->expects($this->once())->method('findByRedirectUris')->willReturn([$provider]);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface'));

        $fetcher = $this->getFetcher(null, $em, $claimRepository, $clientRepository);
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

        $clientRepository = $this->getClientRepository();
        $clientRepository->expects($this->once())->method('findByRedirectUris')->willReturn(null);

        $em = $this->getEntityManager();
        $em->expects($this->never())->method('persist');

        $fetcher = $this->getFetcher(null, $em, null, $clientRepository);
        $fetcher->getRemoteClaim($claimUri);
    }

    /**
     * It should also fail when more than one Claim Provider is found.
     */
    public function testManyProviders()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $claimUri = 'https://dummy.com';

        $provider1 = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');
        $provider2 = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');

        $clientRepository = $this->getClientRepository();
        $clientRepository->expects($this->once())->method('findByRedirectUris')->willReturn([$provider1, $provider2]);

        $em = $this->getEntityManager();
        $em->expects($this->never())->method('persist');

        $fetcher = $this->getFetcher(null, $em, null, $clientRepository);
        $fetcher->getRemoteClaim($claimUri);
    }

    /**
     * @param HttpMocker|null $httpMocker
     * @param EntityManagerInterface|null $em
     * @param RemoteClaimRepository|null $claimRepository
     * @param ClientRepository|null $clientRepository
     * @return RemoteClaimFetcher
     */
    private function getFetcher(
        HttpMocker $httpMocker = null,
        EntityManagerInterface $em = null,
        RemoteClaimRepository $claimRepository = null,
        ClientRepository $clientRepository = null
    ) {
        if ($httpMocker === null) {
            $httpMocker = new HttpMocker();
        }
        if ($em === null) {
            $em = $this->getEntityManager();
        }
        if ($claimRepository === null) {
            $claimRepository = $this->getClaimRepository();
        }
        if ($clientRepository === null) {
            $clientRepository = $this->getClientRepository();
        }
        $httpClient = $httpMocker->getClient();

        $fetcher = new RemoteClaimFetcher($httpClient, $em, $claimRepository, $clientRepository);

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
     * @return ClientRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientRepository()
    {
        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\ClientRepository')
            ->disableOriginalConstructor()->getMock();

        return $repo;
    }
}
