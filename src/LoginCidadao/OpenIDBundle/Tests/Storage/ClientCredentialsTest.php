<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Storage;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OpenIDBundle\Storage\ClientCredentials;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;

class ClientCredentialsTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckClientCredentials()
    {
        $clientId = '123_randomId';
        $clientSecret = 'client_secret';

        $client = $this->getClient(123, 'randomId', $clientSecret);

        $em = $this->getEntityManagerFind($client, 'findOneBy');

        $clientCredentials = new ClientCredentials($em);
        $result = $clientCredentials->checkClientCredentials($clientId, $clientSecret);

        $this->assertTrue($result);
    }

    public function testCheckInvalidClientCredentials()
    {
        $clientId = '123';
        $clientSecret = 'client_secret';

        $client = $this->getClient(123, 'randomId', $clientSecret);

        $em = $this->getEntityManagerFind($client, 'find');

        $clientCredentials = new ClientCredentials($em);
        $result = $clientCredentials->checkClientCredentials($clientId, 'wrong');

        $this->assertFalse($result);
    }

    public function testCheckNonExistentClient()
    {
        $em = $this->getEntityManagerFind(null, 'find');

        $clientCredentials = new ClientCredentials($em);
        $result = $clientCredentials->checkClientCredentials(123, 'wrong');

        $this->assertFalse($result);
    }

    public function testGetClientDetails()
    {
        $id = 123;
        $randomId = 'randomId';

        $client = $this->getClient($id, $randomId, 'client_secret');

        $em = $this->getEntityManagerFind($client, 'findOneBy');
        $clientCredentials = new ClientCredentials($em);

        $details = $clientCredentials->getClientDetails("{$id}_{$randomId}");

        $this->assertNotEmpty($details);
        $this->assertCount(3, $details);
        $this->assertArrayHasKey('redirect_uri', $details);
        $this->assertArrayHasKey('client_id', $details);
        $this->assertArrayHasKey('grant_types', $details);
        $this->assertEquals($client->getPublicId(), $details['client_id']);
        $this->assertEquals($client->getAllowedGrantTypes(), $details['grant_types']);
        $this->assertContains($client->getRedirectUris()[0], $details['redirect_uri']);
    }

    public function testGetClientNotFoundDetails()
    {
        $id = 123;
        $randomId = 'randomId';

        $em = $this->getEntityManagerFind(null, 'findOneBy');
        $clientCredentials = new ClientCredentials($em);

        $details = $clientCredentials->getClientDetails("{$id}_{$randomId}");

        $this->assertFalse($details);
    }

    public function testIsPublicClient()
    {
        $id = 123;
        $randomId = 'randomId';

        $client = $this->getClient($id, $randomId, 'client_secret');

        $em = $this->getEntityManagerFind($client, 'findOneBy');
        $clientCredentials = new ClientCredentials($em);

        $result = $clientCredentials->isPublicClient("{$id}_{$randomId}");

        $this->assertFalse($result);
    }

    public function testIsPublicClientNotFound()
    {
        $id = 123;
        $randomId = 'randomId';

        $em = $this->getEntityManagerFind(null, 'findOneBy');
        $clientCredentials = new ClientCredentials($em);

        $result = $clientCredentials->isPublicClient("{$id}_{$randomId}");

        $this->assertFalse($result);
    }

    public function testGetClientScopeWithRemoteClaim()
    {
        $id = 123;
        $randomId = 'randomId';
        $expectedRemoteClaim = 'tag:example.com,2017:my_claim';

        $client = $this->getClient($id, $randomId, 'client_secret');

        $em = $this->getEntityManagerFind($client, 'findOneBy', null, $expectedRemoteClaim);
        $clientCredentials = new ClientCredentials($em);

        $scopes = explode(' ', $clientCredentials->getClientScope("{$id}_{$randomId}"));

        $this->assertContains('name', $scopes);
        $this->assertContains('openid', $scopes);
        $this->assertContains($expectedRemoteClaim, $scopes);
    }

    public function testGetClientScopeWithoutRemoteClaim()
    {
        $id = 123;
        $randomId = 'randomId';

        $client = $this->getClient($id, $randomId, 'client_secret');

        $em = $this->getEntityManagerFind($client, 'findOneBy');
        $clientCredentials = new ClientCredentials($em);

        $scopes = explode(' ', $clientCredentials->getClientScope("{$id}_{$randomId}"));

        $this->assertContains('name', $scopes);
        $this->assertContains('openid', $scopes);
    }

    public function testGetClientNotFoundScope()
    {
        $id = 123;
        $randomId = 'randomId';

        $em = $this->getEntityManagerFind(null, 'findOneBy');
        $clientCredentials = new ClientCredentials($em);

        $scopes = $clientCredentials->getClientScope("{$id}_{$randomId}");

        $this->assertFalse($scopes);
    }

    /**
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        return $em;
    }

    private function getEntityManagerFind($client, $findMethod, $em = null, $expectedRemoteClaim = null)
    {
        $clientRepo = $this->getClientRepository();
        $clientRepo->expects($this->once())->method($findMethod)->willReturn($client);

        $remoteClaimRepo = $this->getRemoteClaimRepository();

        $em = $em ?: $this->getEntityManager();
        $em->expects($this->atMost(2))->method('getRepository')
            ->willReturnCallback(function ($entity) use ($clientRepo, $remoteClaimRepo, $expectedRemoteClaim) {
                switch ($entity) {
                    case 'LoginCidadaoOAuthBundle:Client':
                        return $clientRepo;
                    case 'LoginCidadaoRemoteClaimsBundle:RemoteClaim':
                        $remoteClaims = [];
                        if ($expectedRemoteClaim !== null) {
                            $expectedRemoteClaim = TagUri::createFromString($expectedRemoteClaim);
                            $remoteClaim = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface');
                            $remoteClaim->expects($this->any())->method('getName')->willReturn($expectedRemoteClaim);
                            $remoteClaims[] = $remoteClaim;
                        }
                        $remoteClaimRepo->expects($this->once())->method('findAll')->willReturn($remoteClaims);

                        return $remoteClaimRepo;
                    default:
                        return null;
                }
            });

        return $em;
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

    /**
     * @return RemoteClaimRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRemoteClaimRepository()
    {
        $repo = $this->getMockBuilder('LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository')
            ->disableOriginalConstructor()->getMock();

        return $repo;
    }

    private function getClient($id, $randomId, $secret)
    {
        $client = new Client();
        $client->setId($id);
        $client->setRandomId($randomId);
        $client->setSecret($secret);
        $client->setRedirectUris(['https://redirect.uri']);
        $client->setAllowedGrantTypes(['authorization_code']);
        $client->setAllowedScopes(['name', 'openid']);

        return $client;
    }
}
