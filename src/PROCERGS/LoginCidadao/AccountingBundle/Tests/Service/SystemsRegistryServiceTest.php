<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Tests\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService;

/**
 * @codeCoverageIgnore
 */
class SystemsRegistryServiceTest extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'apiUri' => 'https://api.uri/{host}',
        'organization' => 'MyOrganization',
        'registration_number' => 1234,
        'password' => 'ultra_top_secret_password',
    ];

    public function testGetSystemInitials()
    {
        $client = new Client();
        $client->setSiteUrl('http://host1/path');
        $client->setRedirectUris([
            'http://host1/path',
            'http://host2/path',
            'http://host2/path2',
        ]);

        $queries = [];
        $httpClient = $this->getHttpClient();
        $this->httpClientExpectGet($httpClient, $queries, 3);

        $registry = $this->getRegistry($httpClient);

        $initials = $registry->getSystemInitials($client);

        $this->assertContains('http://host1/path', $queries);
        $this->assertContains('http://host2/path', $queries);
        $this->assertContains('http://host2/path2', $queries);
        $this->assertCount(3, $queries);
        $this->assertContains('XPTO', $initials);
    }

    public function testGetSystemInitialsFromCache()
    {
        $client = new Client();
        $client->setSiteUrl('http://host1/path');
        $client->setRedirectUris([
            'http://host1/path',
            'http://host2/path',
            'http://host2/path2',
        ]);

        $queries = [];
        $httpClient = $this->getHttpClient();
        $this->httpClientExpectGet($httpClient, $queries, 3);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        // Logger should be called 11 times:
        //      2 for 'Fetching PROCERGS's system initials for client_id'
        //      6 for 'Searching for ...'
        //      3 for 'Returning cached result for ...'
        $logger->expects($this->exactly(11))->method('log')->with('info');

        $registry = $this->getRegistry($httpClient);
        $registry->setLogger($logger);

        $initials = $registry->getSystemInitials($client);
        $registry->getSystemInitials($client);

        $this->assertContains('http://host1/path', $queries);
        $this->assertContains('http://host2/path', $queries);
        $this->assertContains('http://host2/path2', $queries);
        $this->assertCount(3, $queries);
        $this->assertContains('XPTO', $initials);
    }

    public function testGetSystemInitialsNotFound()
    {
        $client = new Client();
        $client->setSiteUrl('http://host1/path');

        $queries = [];
        $httpClient = $this->getHttpClient();
        $httpClient->expects($this->once())->method('get')
            ->willReturnCallback(function ($url, $options) use (&$queries) {
                $queries[] = str_replace('https://api.uri/', '', $url);

                $e = $this->getMockBuilder('GuzzleHttp\Exception\ClientException')
                    ->disableOriginalConstructor()->getMock();
                $e->expects($this->exactly(2))->method('getResponse')
                    ->willReturn($this->getResponse(['error' => 'Not found!'], 404));

                throw $e;
            });

        $registry = $this->getRegistry($httpClient);
        $registry->getSystemInitials($client);

        $this->assertContains('http://host1/path', $queries);
        $this->assertCount(1, $queries);
    }

    public function testGetSystemInitialsServerError()
    {
        // Since this is a batch job, we do not handle errors so that the job can fail and alert us.
        $this->setExpectedException('GuzzleHttp\Exception\ClientException');

        $client = new Client();
        $client->setRedirectUris(['http://host1/path']);

        $queries = [];
        $httpClient = $this->getHttpClient();
        $httpClient->expects($this->once())->method('get')
            ->willReturnCallback(function ($url) use (&$queries) {
                $queries[] = str_replace('https://api.uri/', '', $url);

                $e = $this->getMockBuilder('GuzzleHttp\Exception\ClientException')
                    ->disableOriginalConstructor()->getMock();
                $e->expects($this->once())->method('getResponse')
                    ->willReturn($this->getResponse(null, 500));

                throw $e;
            });

        $registry = $this->getRegistry($httpClient);
        $registry->getSystemInitials($client);
    }

    public function testGetSystemInitialsIgnoreInactiveSystemsWithoutDecommissionDate()
    {
        $queries = [];
        $httpClient = $this->getHttpClient();
        $this->httpClientExpectGet($httpClient, $queries, 1, [
            ['sistema' => 'XPTO1', 'situacao' => 'Implantado'],
            ['sistema' => 'XPTO2', 'situacao' => 'Not Implantado'],
        ]);

        $client = new Client();
        $client->setSiteUrl('http://host1/path');

        $registry = $this->getRegistry($httpClient);
        $initials = $registry->getSystemInitials($client, new \DateTime());

        $this->assertNotEmpty($initials);
        $this->assertContains('XPTO1', $initials);
        $this->assertNotContains('XPTO2', $initials);
    }

    public function testGetSystemInitialsIgnoreInactiveSystemsWithDecommissionDate()
    {
        $queries = [];
        $httpClient = $this->getHttpClient();
        $this->httpClientExpectGet($httpClient, $queries, 1, [
            ['sistema' => 'XPTO1', 'decommissionedOn' => '2018-02-03'],
            ['sistema' => 'XPTO2', 'decommissionedOn' => '2018-01-31'],
        ]);

        $client = new Client();
        $client->setSiteUrl('http://host1/path');

        $registry = $this->getRegistry($httpClient);
        $initials = $registry->getSystemInitials($client, \DateTime::createFromFormat('Y-m-d', '2018-02-01'));

        $this->assertNotEmpty($initials);
        $this->assertContains('XPTO1', $initials);
        $this->assertNotContains('XPTO2', $initials);
    }

    public function testGetSystemOwners()
    {
        $client = new Client();
        $client->setRedirectUris(['https://host1/path', 'https://host2/path']);

        $registry = $this->getRegistry();
        $owners = $registry->getSystemOwners($client);

        $this->assertNotEmpty($owners);
    }

    public function testGetSystemOwnersNotFound()
    {
        $client = new Client();
        $client->setRedirectUris(['https://host1/path', 'https://host2/path']);

        $httpClient = $this->getHttpClient();
        $httpClient->expects($this->atLeastOnce())->method('get')
            ->willReturnCallback(function () {
                $e = $this->getMockBuilder('GuzzleHttp\Exception\ClientException')
                    ->disableOriginalConstructor()->getMock();
                $e->expects($this->exactly(2))->method('getResponse')
                    ->willReturn($this->getResponse(['error' => 'Not found!'], 404));

                throw $e;
            });

        $registry = $this->getRegistry($httpClient);
        $owners = $registry->getSystemOwners($client);

        $this->assertEmpty($owners);
    }

    public function testFetchLinked()
    {
        $client = new Client();
        $client->setId(123);

        $link = new ProcergsLink();
        $link->setClient($client)
            ->setSystemType(ProcergsLink::TYPE_INTERNAL);

        $repoClass = 'PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLinkRepository';
        $repo = $this->getMockBuilder($repoClass)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('findBy')->willReturn([$link]);

        $emptyRepo = $this->getMockBuilder($repoClass)->disableOriginalConstructor()->getMock();
        $emptyRepo->expects($this->once())->method('findBy')->willReturn([]);

        $registry = $this->getRegistry($this->getHttpClient());
        $empty = $registry->fetchLinked([$client], $emptyRepo);
        $this->assertEmpty($empty);

        $links = $registry->fetchLinked([$client], $repo);
        $this->assertNotEmpty($links);
    }

    private function getHttpClient()
    {
        return $this->getMock('GuzzleHttp\ClientInterface');
    }

    private function getRegistry($client = null, $options = null)
    {
        if (!$client) {
            $client = $this->getHttpClient();
            $client->expects($this->any())->method('get')->willReturn($this->getResponse([
                [
                    'sistema' => 'XPTO',
                    'clienteDono' => 'CLIENT',
                    'urls' => [
                        'url' => 'https://url.tld/',
                        'ambiente' => 'Produção',
                    ],
                ],
            ]));
        }

        if (!$options) {
            $options = $this->config;
        }

        return new SystemsRegistryService($client, $options);
    }

    /**
     * @param $json
     * @param null $statusCode
     * @return RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResponse($json = null, $statusCode = null)
    {
        $response = $this->getMock('GuzzleHttp\Message\ResponseInterface');
        if ($json) {
            $response->expects($this->atLeastOnce())->method('json')->willReturn($json);
        }

        if ($statusCode) {
            $response->expects($this->once())->method('getStatusCode')
                ->willReturn($statusCode);
        }

        return $response;
    }

    /**
     * @param ClientInterface|\PHPUnit_Framework_MockObject_MockObject $httpClient
     * @param $queries
     * @param $count
     * @param array|null $payload
     */
    private function httpClientExpectGet(&$httpClient, &$queries, $count, $payload = null)
    {
        $httpClient->expects($this->exactly($count))->method('get')
            ->willReturnCallback(function ($url, $options) use (&$queries, $payload) {
                $queries[] = str_replace('https://api.uri/', '', $url);

                $headers = $options['headers'];
                $this->assertEquals($this->config['organization'], $headers['organizacao']);

                $response = $this->getResponse($payload ?: [['sistema' => 'XPTO']]);

                return $response;
            });
    }
}
