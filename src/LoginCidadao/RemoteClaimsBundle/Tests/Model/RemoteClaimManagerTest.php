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
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorizationRepository;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManager;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;

class RemoteClaimManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testEnforceNewAuthorization()
    {
        $authorization = $this->getRemoteClaimAuthorization();

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist')->with($authorization);

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findAuthorization')->willReturn(null);

        $manager = new RemoteClaimManager($em, $repo);
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

        $manager = new RemoteClaimManager($em, $repo);
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

        $manager = new RemoteClaimManager($this->getEntityManager(), $repo);

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

        $manager = new RemoteClaimManager($this->getEntityManager(), $repo);

        $this->assertTrue($manager->isAuthorized($claimName, $person, $client));
    }

    public function testIsNotAuthorizedTagUri()
    {
        $claimName = new TagUri();
        $person = $this->getPerson();
        $client = $this->getClient();

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findAuthorization')->willReturn(null);

        $manager = new RemoteClaimManager($this->getEntityManager(), $repo);

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

        $manager = new RemoteClaimManager($em, $repo);
        $manager->revokeAllAuthorizations($authorization);
    }

    public function testFilterRemoteClaimsString()
    {
        $scopes = 'scope1 scope2 tag:example.com,2017:my_claim scope3';
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals('scope1 scope2 scope3', $result);
    }

    public function testFilterRemoteClaimsArray()
    {
        $scopes = ['scope1', 'scope2', 'tag:example.com,2017:my_claim', 'scope3'];
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals(['scope1', 'scope2', 'scope3'], $result);
    }

    public function testFilterRemoteClaimsNoAction()
    {
        $scopes = 'scope1 scope2 scope3';
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals('scope1 scope2 scope3', $result);
    }

    public function testFilterRemoteClaimsEmptyString()
    {
        $scopes = '';
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals('', $result);
    }

    public function testFilterRemoteClaimsEmptyArray()
    {
        $scopes = [];
        $manager = new RemoteClaimManager($this->getEntityManager(), $this->getRepo());
        $result = $manager->filterRemoteClaims($scopes);

        $this->assertEquals([], $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->getMock('Doctrine\ORM\EntityManagerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteClaimAuthorizationRepository
     */
    public function getRepo()
    {
        return $this->getMockBuilder('LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorizationRepository')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteClaimAuthorizationInterface
     */
    public function getRemoteClaimAuthorization()
    {
        return $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PersonInterface
     */
    public function getPerson()
    {
        return $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    public function getClient()
    {
        return $this->getMock('LoginCidadao\OAuthBundle\Model\ClientInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Authorization
     */
    public function getAuthorization()
    {
        return $this->getMock('LoginCidadao\CoreBundle\Entity\Authorization');
    }
}
