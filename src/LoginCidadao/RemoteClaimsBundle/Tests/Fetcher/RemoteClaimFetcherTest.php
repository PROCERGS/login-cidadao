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
use GuzzleHttp\Message\Request;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Subscriber\Mock;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository;
use LoginCidadao\RemoteClaimsBundle\Fetcher\RemoteClaimFetcher;
use LoginCidadao\RemoteClaimsBundle\Model\HttpUri;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use LoginCidadao\RemoteClaimsBundle\Tests\Parser\RemoteClaimParserTest;

class RemoteClaimFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function testFetchByUri()
    {
        $this->runTestFetch('https://dummy.com');
    }

    public function testFetchByUriObject()
    {
        $this->runTestFetch(HttpUri::createFromString('https://dummy.com'));
    }

    public function testFetchByTag()
    {
        $data = RemoteClaimParserTest::$claimMetadata;

        $this->runTestFetch($data['claim_name'], 'https://dummy.com');
    }

    public function testFetchByTagObject()
    {
        $data = RemoteClaimParserTest::$claimMetadata;

        $this->runTestFetch(TagUri::createFromString($data['claim_name']), 'https://dummy.com');
    }

    public function testDiscoveryFailure()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $data = RemoteClaimParserTest::$claimMetadata;
        $data['claim_name'] = 'tag:example.com,2017:my_claim';

        $this->runTestFetch(TagUri::createFromString('tag:example.com,2018:my_claim'), 'https://dummy.com');
    }

    public function testGetNewRemoteClaim()
    {
        $uri = 'https://dummy.com';
        $data = RemoteClaimParserTest::$claimMetadata;
        $history = new History();

        $httpClient = $this->getHttpClient($data, $history);
        $em = $this->getEntityManager();
        $em->expects($this->exactly(2))->method('persist')->willReturnCallback(function ($entity) {
            if (!$entity instanceof ClientInterface
                && !$entity instanceof RemoteClaimInterface) {
                $this->fail('Expected ClientInterface or RemoteClaimInterface to be persisted.');
            }
        });
        $em->expects($this->once())->method('flush');

        $remoteClaimRepo = $this->getRemoteClaimRepository();
        $clientRepo = $this->getClientRepository();

        $fetcher = new RemoteClaimFetcher($httpClient, $em, $remoteClaimRepo, $clientRepo);
        $fetcher->getRemoteClaim($uri);
    }

    public function testGetExistingRemoteClaim()
    {
        $uri = 'https://dummy.com';
        $data = RemoteClaimParserTest::$claimMetadata;
        $history = new History();

        $remoteClaim = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface');

        $httpClient = $this->getHttpClient($data, $history);
        $em = $this->getEntityManager();
        $remoteClaimRepo = $this->getRemoteClaimRepository();
        $remoteClaimRepo->expects($this->once())->method('findOneBy')->willReturn($remoteClaim);
        $clientRepo = $this->getClientRepository();

        $fetcher = new RemoteClaimFetcher($httpClient, $em, $remoteClaimRepo, $clientRepo);
        $this->assertEquals($remoteClaim, $fetcher->getRemoteClaim($uri));
    }

    private function runTestFetch($uri, $expectedUri = null)
    {
        $data = RemoteClaimParserTest::$claimMetadata;

        /** @var History $history */
        $history = new History();

        $httpClient = $this->getHttpClient($data, $history, $expectedUri);
        $em = $this->getEntityManager();
        $remoteClaimRepo = $this->getRemoteClaimRepository();
        $clientRepo = $this->getClientRepository();

        $fetcher = new RemoteClaimFetcher($httpClient, $em, $remoteClaimRepo, $clientRepo);
        $remoteClaim = $fetcher->fetchRemoteClaim($uri);

        $this->assertEquals($data['claim_name'], $remoteClaim->getName());

        /** @var Request $request */
        $request = $history->getRequests()[$expectedUri ? 1 : 0];
        $this->assertEquals($expectedUri ?: $uri, $request->getUrl());
    }

    private function getHttpClient($data, &$history, $expectedUri = null)
    {
        $client = new Client();

        $metadata = json_encode($data);
        $length = strlen($metadata);

        $responses = ["HTTP/1.1 200 OK\r\n\Content-Length: {$length}\r\n\r\n{$metadata}"];

        if ($expectedUri) {
            $discoveryResponse = json_encode([
                'subject' => $data['claim_name'],
                'links' => [
                    ['rel' => 'http://openid.net/specs/connect/1.0/claim', 'href' => $expectedUri],
                ],
            ]);
            $discoveryLen = strlen($discoveryResponse);
            array_unshift($responses,
                "HTTP/1.1 200 OK\r\n\Content-Length: {$discoveryLen}\r\n\r\n{$discoveryResponse}");
        }

        $mock = new Mock($responses);
        $client->getEmitter()->attach($history);
        $client->getEmitter()->attach($mock);

        return $client;
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
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteClaimRepository
     */
    private function getRemoteClaimRepository()
    {
        $repo = $this->getMockBuilder('LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository')
            ->disableOriginalConstructor()->getMock();

        return $repo;
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