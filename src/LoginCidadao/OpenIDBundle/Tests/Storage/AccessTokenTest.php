<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\Tests\OpenIDBundle\Storage;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use LoginCidadao\OpenIDBundle\Storage\AccessToken;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{

    public function testGetAccessToken()
    {
        $clientId = 'client_id';
        $token = 'my.access.token';
        $expires = time();
        $scope = 'scope1 scope2';
        $idToken = 'id-token-here';

        $clientMetadata = new ClientMetadata();

        $client = new Client();
        $client->setId('client');
        $client->setRandomId('id');
        $client->setMetadata($clientMetadata);

        $person = new Person();
        $accessToken = new \LoginCidadao\OAuthBundle\Entity\AccessToken();
        $accessToken->setClient($client);
        $accessToken->setUser($person);
        $accessToken->setExpiresAt($expires);
        $accessToken->setScope($scope);
        $accessToken->setIdToken($idToken);

        $repo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['token' => $token])
            ->willReturn($accessToken);

        $em = $this->getEntityManager();
        $em->expects($this->once())
            ->method('getRepository')->with('LoginCidadaoOAuthBundle:AccessToken')
            ->willReturn($repo);

        $subIdService = $this->getSubjectIdentifierService();
        $subIdService->expects($this->once())
            ->method('getSubjectIdentifier')->with($person, $clientMetadata)
            ->willReturn('subId');

        $accessTokenStorage = new AccessToken($em);
        $accessTokenStorage->setSubjectIdentifierService($subIdService);

        $this->assertSame([
            'client_id' => $clientId,
            'user_id' => 'subId',
            'expires' => $expires,
            'scope' => $scope,
            'id_token' => $idToken,
        ], $accessTokenStorage->getAccessToken($token));
    }

    public function testGetAccessTokenNotFound()
    {
        $token = 'my.access.token';
        $repo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['token' => $token])
            ->willReturn(null);

        $em = $this->getEntityManager();
        $em->expects($this->once())
            ->method('getRepository')->with('LoginCidadaoOAuthBundle:AccessToken')
            ->willReturn($repo);

        $accessTokenStorage = new AccessToken($em);
        $this->assertNull($accessTokenStorage->getAccessToken($token));
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testSetAccessToken()
    {
        $clientId = 'client_id';
        $userId = 'subId';
        $token = 'my.access.token';
        $expires = time();
        $scope = 'scope1 scope2';
        $idToken = 'id-token-here';
        $client = new Client();

        $person = new Person();
        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())
            ->method('find')->with($userId)
            ->willReturn($person);

        $subIdService = $this->getSubjectIdentifierService();
        $subIdService->expects($this->once())
            ->method('getPerson')->with($userId, $client)
            ->willReturn(null);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(\LoginCidadao\OAuthBundle\Entity\AccessToken::class));
        $em->expects($this->once())
            ->method('getRepository')->with('LoginCidadaoCoreBundle:Person')
            ->willReturn($personRepo);

        $clientManager = $this->getClientManager();
        $clientManager->expects($this->once())
            ->method('getClientById')->with($clientId)
            ->willReturn($client);

        $accessTokenStorage = new AccessToken($em);
        $accessTokenStorage->setSubjectIdentifierService($subIdService);
        $accessTokenStorage->setClientManager($clientManager);
        $accessTokenStorage->setAccessToken($token, $clientId, $userId, $expires, $scope, $idToken);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testSetAccessTokenClientNotFound()
    {
        $clientId = 'client_id';
        $userId = 'subId';
        $token = 'my.access.token';
        $expires = time();
        $scope = 'scope1 scope2';
        $idToken = 'id-token-here';

        $clientManager = $this->getClientManager();
        $clientManager->expects($this->once())
            ->method('getClientById')->with($clientId)
            ->willReturn(null);

        $accessTokenStorage = new AccessToken($this->getEntityManager());
        $accessTokenStorage->setClientManager($clientManager);
        $this->assertNull($accessTokenStorage->setAccessToken($token, $clientId, $userId, $expires, $scope, $idToken));
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testSetAccessTokenClientCredentials()
    {
        $clientId = 'client_id';
        $userId = null;
        $token = 'my.access.token';
        $expires = time();
        $scope = 'scope1 scope2';
        $idToken = 'id-token-here';
        $client = new Client();

        $clientManager = $this->getClientManager();
        $clientManager->expects($this->once())
            ->method('getClientById')->with($clientId)
            ->willReturn($client);

        $accessTokenStorage = new AccessToken($this->getEntityManager());
        $accessTokenStorage->setClientManager($clientManager);
        $this->assertNull($accessTokenStorage->setAccessToken($token, $clientId, $userId, $expires, $scope, $idToken));
    }

    /**
     * @return EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager()
    {
        return $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return PersonRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPersonRepository()
    {
        return $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return SubjectIdentifierService|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSubjectIdentifierService()
    {
        return $this->getMockBuilder(SubjectIdentifierService::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return ClientManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientManager()
    {
        /** @var ClientManager|\PHPUnit_Framework_MockObject_MockObject $clientManager */
        $clientManager = $this->getMockBuilder(ClientManager::class)
            ->disableOriginalConstructor()->getMock();

        return $clientManager;
    }
}
