<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\Model;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorization;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorizationRepository;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManager;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use PHPUnit\Framework\TestCase;

class RemoteClaimManagerTest extends TestCase
{
    public function testEnforceNewAuthorization()
    {
        $authorization = $this->getRemoteClaimAuthorization();

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist')->with($authorization);

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findAuthorization')->willReturn(null);

        $manager = new RemoteClaimManager($em, $repo, $this->getRemoteClaimRepo());
        $this->assertSame($authorization, $manager->enforceAuthorization($authorization));
    }

    public function testEnforceExistingAuthorization()
    {
        $authorization = $this->getRemoteClaimAuthorization();
        $existingAuthorization = $this->getRemoteClaimAuthorization();

        $em = $this->getEntityManager();
        $em->expects($this->never())->method('persist');

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findAuthorization')->willReturn($existingAuthorization);

        $manager = new RemoteClaimManager($em, $repo, $this->getRemoteClaimRepo());
        $this->assertSame($existingAuthorization, $manager->enforceAuthorization($authorization));
    }

    public function testIsAuthorizedClaimNameString()
    {
        $claimName = 'tag:example.com,2017:my_claim';
        $person = $this->getPerson();
        $client = $this->getClient();
        $authorization = $this->getRemoteClaimAuthorization();

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findAuthorization')->willReturn($authorization);

        $manager = new RemoteClaimManager($this->getEntityManager(), $repo, $this->getRemoteClaimRepo());

        $this->assertTrue($manager->isAuthorized($claimName, $person, $client));
    }

    public function testIsAuthorizedTagUri()
    {
        $claimName = new TagUri();
        $person = $this->getPerson();
        $client = $this->getClient();
        $authorization = $this->getRemoteClaimAuthorization();

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findAuthorization')->willReturn($authorization);

        $manager = new RemoteClaimManager($this->getEntityManager(), $repo, $this->getRemoteClaimRepo());

        $this->assertTrue($manager->isAuthorized($claimName, $person, $client));
    }

    public function testIsNotAuthorizedTagUri()
    {
        $claimName = new TagUri();
        $person = $this->getPerson();
        $client = $this->getClient();

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findAuthorization')->willReturn(null);

        $manager = new RemoteClaimManager($this->getEntityManager(), $repo, $this->getRemoteClaimRepo());

        $this->assertFalse($manager->isAuthorized($claimName, $person, $client));
    }

    public function testRevokeAllAuthorizations()
    {
        $remoteClaimAuthorizations = [
            $this->getRemoteClaimAuthorization(),
            $this->getRemoteClaimAuthorization(),
        ];

        $authorization = $this->getAuthorization();
        $authorization->expects($this->once())->method('getPerson')->willReturn($this->getPerson());
        $authorization->expects($this->once())->method('getClient')->willReturn($this->getClient());

        $em = $this->getEntityManager();
        $em->expects($this->exactly(count($remoteClaimAuthorizations)))->method('remove')
            ->with($this->isInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface'));

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findAllByClientAndPerson')->willReturn($remoteClaimAuthorizations);

        $manager = new RemoteClaimManager($em, $repo, $this->getRemoteClaimRepo());
        $manager->revokeAllAuthorizations($authorization);
    }

    public function testFilterRemoteClaimsString()
    {
        $scopes = 'scope1 scope2 tag:example.com,2017:my_claim scope3';
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo(), $this->getRemoteClaimRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals('scope1 scope2 scope3', $result);
    }

    public function testFilterRemoteClaimsArray()
    {
        $scopes = ['scope1', 'scope2', 'tag:example.com,2017:my_claim', 'scope3'];
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo(), $this->getRemoteClaimRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals(['scope1', 'scope2', 'scope3'], $result);
    }

    public function testFilterRemoteClaimsNoAction()
    {
        $scopes = 'scope1 scope2 scope3';
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo(), $this->getRemoteClaimRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals('scope1 scope2 scope3', $result);
    }

    public function testFilterRemoteClaimsEmptyString()
    {
        $scopes = '';
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo(), $this->getRemoteClaimRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals('', $result);
    }

    public function testFilterRemoteClaimsEmptyArray()
    {
        $scopes = [];
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo(), $this->getRemoteClaimRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals([], $result);
    }

    public function testGetRemoteClaimsFromAuthorization()
    {
        $client = $this->getClient();
        $person = $this->getPerson();

        /** @var \PHPUnit_Framework_MockObject_MockObject|Authorization $authorization */
        $authorization = $this->createMock('LoginCidadao\CoreBundle\Entity\Authorization');
        $authorization->expects($this->once())->method('getClient')->willReturn($client);
        $authorization->expects($this->once())->method('getPerson')->willReturn($person);

        $repo = $this->getRemoteClaimRepo();
        $repo->expects($this->once())->method('findByClientAndPerson')
            ->with($client, $person);

        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo(), $repo);
        $manager->getRemoteClaimsFromAuthorization($authorization);
    }

    public function testGetRemoteClaimsAuthorizationsFromAuthorization()
    {
        $client = $this->getClient();
        $person = $this->getPerson();

        /** @var \PHPUnit_Framework_MockObject_MockObject|Authorization $authorization */
        $authorization = $this->createMock('LoginCidadao\CoreBundle\Entity\Authorization');
        $authorization->expects($this->once())->method('getClient')->willReturn($client);
        $authorization->expects($this->once())->method('getPerson')->willReturn($person);

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findAllByClientAndPerson')
            ->with($client, $person);

        $manager = new RemoteClaimManager($this->getEntityManager(), $repo, $this->getRemoteClaimRepo());
        $manager->getRemoteClaimsAuthorizationsFromAuthorization($authorization);
    }

    public function testGetExistingRemoteClaim()
    {
        $claimName = new TagUri();

        $expected = new RemoteClaim();
        $repo = $this->getRemoteClaimRepo();
        $repo->expects($this->once())->method('findOneBy')->with(['name' => $claimName])
            ->willReturn($expected);

        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo(), $repo);

        $this->assertSame($expected, $manager->getExistingRemoteClaim($claimName));
    }

    public function testGetRemoteClaimsWithTokens()
    {
        $client = $this->getClient();
        $person = $this->getPerson();
        $claimName = (new TagUri())
            ->setAuthorityName('example.com')
            ->setDate('2018-01')
            ->setSpecific('example');

        $claimAuth = (new RemoteClaimAuthorization())
            ->setPerson($person)
            ->setClient($client)
            ->setClaimName($claimName);

        $authRepo = $this->getRepo();
        $authRepo->expects($this->once())->method('findAllByClientAndPerson')
            ->with($client, $person)->willReturn([$claimAuth]);

        $remoteClaim = (new RemoteClaim())->setName($claimName);
        $claimsRepo = $this->getRemoteClaimRepo();
        $claimsRepo->expects($this->once())->method('findByClientAndPerson')
            ->with($client, $person)->willReturn([$remoteClaim]);

        $manager = new RemoteClaimManager($this->getEntityManager(), $authRepo, $claimsRepo);
        $result = $manager->getRemoteClaimsWithTokens($client, $person);

        $this->assertEquals([
            'tag:example.com,2018-01:example' => [
                'authorization' => $claimAuth,
                'remoteClaim' => $remoteClaim,
            ],
        ], $result);
    }

    public function testGetRemoteClaimAuthorizationByAccessToken()
    {
        $claimName = TagUri::createFromString('tag:example.com,2017:my_claim');
        $client = $this->getClient();
        $person = $this->getPerson();
        $provider = $this->getClaimProvider();
        $token = 'my_access_token';
        $claimAuth = (new RemoteClaimAuthorization())
            ->setPerson($person)
            ->setClient($client)
            ->setClaimName($claimName)
            ->setAccessToken($token)
            ->setClaimProvider($provider);

        $authRepo = $this->getRepo();
        $authRepo->expects($this->once())->method('findOneBy')
            ->with([
                'claimProvider' => $provider,
                'accessToken' => $token,
            ])
            ->willReturn($claimAuth);

        $manager = new RemoteClaimManager($this->getEntityManager(), $authRepo, $this->getRemoteClaimRepo());

        $this->assertSame($claimAuth, $manager->getRemoteClaimAuthorizationByAccessToken($provider, $token));
    }

    public function testUpdateRemoteClaimUri()
    {
        $claimName = TagUri::createFromString('tag:example.com,2018:my_claim2');
        $uri = 'https://new.uri';

        $remoteClaim = (new RemoteClaim())
            ->setUri('https://old.uri/');

        $claimRepo = $this->getRemoteClaimRepo();
        $claimRepo->expects($this->once())->method('findOneBy')
            ->willReturn($remoteClaim);
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo(), $claimRepo);
        $manager->updateRemoteClaimUri($claimName, $uri);

        $this->assertEquals($uri, $remoteClaim->getUri());
    }

    public function testUpdateRemoteClaimUriNotFoundClaim()
    {
        $claimName = TagUri::createFromString('tag:example.com,2018:my_claim2');
        $uri = 'https://new.uri';

        $remoteClaim = null;

        $claimRepo = $this->getRemoteClaimRepo();
        $claimRepo->expects($this->once())->method('findOneBy')
            ->willReturn($remoteClaim);
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo(), $claimRepo);

        $this->assertNull($manager->updateRemoteClaimUri($claimName, $uri));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->createMock('Doctrine\ORM\EntityManagerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteClaimAuthorizationRepository
     */
    private function getRepo()
    {
        return $this->getMockBuilder('LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorizationRepository')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteClaimRepository
     */
    private function getRemoteClaimRepo()
    {
        return $this->getMockBuilder('LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteClaimAuthorizationInterface
     */
    private function getRemoteClaimAuthorization()
    {
        return $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PersonInterface
     */
    private function getPerson()
    {
        return $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    private function getClient()
    {
        return $this->createMock('LoginCidadao\OAuthBundle\Model\ClientInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ClaimProviderInterface
     */
    private function getClaimProvider()
    {
        return $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Authorization
     */
    private function getAuthorization()
    {
        return $this->createMock('LoginCidadao\CoreBundle\Entity\Authorization');
    }
}
