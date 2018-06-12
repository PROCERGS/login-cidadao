<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Manager;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ClientManagerTest extends TestCase
{
    public function testGetClientById()
    {
        $id = 123;

        $repo = $this->getClientRepository();
        $repo->expects($this->once())->method('find')->with($id)->willReturn(new Client());

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->with('LoginCidadaoOAuthBundle:Client')
            ->willReturn($repo);

        $manager = new ClientManager($em, $this->getDispatcher(), $this->getPersonRepository(), '');
        $this->assertInstanceOf('LoginCidadao\OAuthBundle\Model\ClientInterface', $manager->getClientById($id));
    }

    public function testGetClientByNull()
    {
        $em = $this->getEntityManager();
        $em->expects($this->never())->method('getRepository');

        $manager = new ClientManager($em, $this->getDispatcher(), $this->getPersonRepository(), '');
        $this->assertNull($manager->getClientById(null));
    }

    public function testGetClientByPublicId()
    {
        $id = '123_randomIdHere';

        $repo = $this->getClientRepository();
        $repo->expects($this->once())->method('findOneBy')
            ->with(['id' => 123, 'randomId' => 'randomIdHere'])
            ->willReturn(new Client());

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->with('LoginCidadaoOAuthBundle:Client')
            ->willReturn($repo);

        $manager = new ClientManager($em, $this->getDispatcher(), $this->getPersonRepository(), '');
        $manager->getClientById($id);
    }

    /**
     * @throws \Doctrine\DBAL\Exception\UniqueConstraintViolationException
     */
    public function testRegisterEmpty()
    {
        $em = $this->emExpectRegistration($this->getEntityManager());

        $manager = new ClientManager($em, $this->getDispatcher(), $this->getPersonRepository(), 'scope1 scope2');
        $metadata = $manager->populateNewMetadata(new ClientMetadata());

        $registeredClient = $manager->register($metadata);

        $this->assertInstanceOf('LoginCidadao\OAuthBundle\Model\ClientInterface', $registeredClient);
        $this->assertSame('clientID', $registeredClient->getId());
    }

    /**
     * @throws \Doctrine\DBAL\Exception\UniqueConstraintViolationException
     */
    public function testRegisterWithClient()
    {
        $client = new Client();
        $metadata = (new ClientMetadata())
            ->setClient($client);

        $em = $this->emExpectRegistration($this->getEntityManager());

        $manager = new ClientManager($em, $this->getDispatcher(), $this->getPersonRepository(), 'scope1 scope2');

        $registeredClient = $manager->register($metadata);

        $this->assertInstanceOf('LoginCidadao\OAuthBundle\Model\ClientInterface', $registeredClient);
        $this->assertSame('clientID', $registeredClient->getId());
        $this->assertSame($client, $registeredClient);
    }

    /**
     * @throws \Doctrine\DBAL\Exception\UniqueConstraintViolationException
     */
    public function testRegisterWithContacts()
    {
        $emails = ['email@example.com', 'not-verified@example.com', 'non-existent@example.com'];
        $metadata = (new ClientMetadata())
            ->setContacts($emails);
        $users = [
            (new Person())
                ->setEmail('email@example.com')
                ->setEmailConfirmedAt(new \DateTime()),
            (new Person())
                ->setEmail('not-verified@example.com'),
        ];

        $em = $this->emExpectRegistration($this->getEntityManager());

        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())
            ->method('findBy')->with(['email' => $emails])
            ->willReturn($users);

        $manager = new ClientManager($em, $this->getDispatcher(), $personRepo, 'scope1 scope2');
        $metadata = $manager->populateNewMetadata($metadata);

        $registeredClient = $manager->register($metadata);

        $this->assertInstanceOf('LoginCidadao\OAuthBundle\Model\ClientInterface', $registeredClient);
        $this->assertSame('clientID', $registeredClient->getId());
        $this->assertCount(1, $registeredClient->getOwners());
    }

    public function testPopulateNewMetadata()
    {
        $client = (new Client())
            ->setRedirectUris(['https://example.com']);
        $metadata = (new ClientMetadata())
            ->setClient($client);

        $em = $this->getEntityManager();

        $manager = new ClientManager($em, $this->getDispatcher(), $this->getPersonRepository(),
            'scope1 scope2');
        $metadata = $manager->populateNewMetadata($metadata);
        $this->assertSame('example.com', $metadata->getClient()->getName());
    }

    /**
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager()
    {
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');

        return $em;
    }

    /**
     * @param EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function emExpectRegistration($em)
    {
        $allowedParams = $this->logicalOr(
            $this->isInstanceOf('LoginCidadao\OAuthBundle\Model\ClientInterface'),
            $this->isInstanceOf('LoginCidadao\OpenIDBundle\Entity\ClientMetadata')
        );
        $em->expects($this->exactly(2))
            ->method('persist')->with($allowedParams)
            ->willReturnCallback(function ($entity) {
                if ($entity instanceof ClientInterface) {
                    $entity->setId('clientID');
                }
                if ($entity instanceof ClientMetadata) {
                    $entity->setId('metadataID');
                }

                return $entity;
            });
        $em->expects($this->once())->method('flush');

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
     * @return EventDispatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDispatcher()
    {
        $dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        return $dispatcher;
    }

    /**
     * @return PersonRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPersonRepository()
    {
        return $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\PersonRepository')
            ->disableOriginalConstructor()->getMock();
    }
}
