<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\AuthorizationRepository;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Service\AuthorizationManager;
use LoginCidadao\OAuthBundle\Entity\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationManagerTest extends TestCase
{
    public function testGetAuthorization()
    {
        $person = new Person();
        $client = new Client();
        $authorization = (new Authorization())
            ->setPerson($person)
            ->setClient($client);

        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);

        /** @var AuthorizationRepository|MockObject $repo */
        $repo = $this->createMock(AuthorizationRepository::class);
        $repo->expects($this->once())->method('findOneBy')
            ->with(['person' => $person, 'client' => $client])
            ->willReturn($authorization);

        $manager = new AuthorizationManager($em, $repo);
        $this->assertSame($authorization, $manager->getAuthorization($person, $client));
    }

    public function testEnforceNewAuthorization()
    {
        $person = new Person();
        $client = new Client();
        $scope = ['new'];
        $strategy = AuthorizationManager::SCOPE_REPLACE;

        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(Authorization::class));

        /** @var AuthorizationRepository|MockObject $repo */
        $repo = $this->createMock(AuthorizationRepository::class);

        $manager = new AuthorizationManager($em, $repo);
        $authorization = $manager->enforceAuthorization($person, $client, $scope, $strategy);

        $this->assertInstanceOf(Authorization::class, $authorization);
        $this->assertContains('new', $authorization->getScope());
    }

    public function testEnforceExistingAuthorization()
    {
        $person = new Person();
        $client = new Client();
        $scope = ['new'];
        $strategy = AuthorizationManager::SCOPE_MERGE;

        $existingAuthorization = (new Authorization())
            ->setPerson($person)
            ->setClient($client)
            ->setScope(['old']);

        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');
        $em->expects($this->never())->method('persist')->with($this->isInstanceOf(Authorization::class));

        /** @var AuthorizationRepository|MockObject $repo */
        $repo = $this->createMock(AuthorizationRepository::class);
        $repo->expects($this->once())->method('findOneBy')
            ->with(['person' => $person, 'client' => $client])
            ->willReturn($existingAuthorization);

        $manager = new AuthorizationManager($em, $repo);
        $authorization = $manager->enforceAuthorization($person, $client, $scope, $strategy);

        $this->assertInstanceOf(Authorization::class, $authorization);
        $this->assertContains('new', $authorization->getScope());
        $this->assertContains('old', $authorization->getScope());
    }

    public function testMergeFailsWithInvalidStrategy()
    {
        $this->expectException(\InvalidArgumentException::class);

        $person = new Person();
        $client = new Client();
        $scope = ['new'];
        $strategy = 'invalid';

        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        /** @var AuthorizationRepository|MockObject $repo */
        $repo = $this->createMock(AuthorizationRepository::class);

        $manager = new AuthorizationManager($em, $repo);
        $authorization = $manager->enforceAuthorization($person, $client, $scope, $strategy);

        $this->assertInstanceOf(Authorization::class, $authorization);
        $this->assertContains('new', $authorization->getScope());
    }
}
