<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifierRepository;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use PHPUnit\Framework\TestCase;

class SubjectIdentifierServiceTest extends TestCase
{
    /**
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager()
    {
        return $this->createMock('Doctrine\ORM\EntityManagerInterface');
    }

    /**
     * @return ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClient()
    {
        /** @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->createMock('LoginCidadao\OAuthBundle\Model\ClientInterface');

        return $client;
    }

    /**
     * @return SubjectIdentifierRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRepo()
    {
        $repo = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifierRepository')
            ->disableOriginalConstructor()
            ->getMock();

        return $repo;
    }

    /**
     * @param mixed|null $id
     * @return PersonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPerson($id = null)
    {
        $person = $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        if ($id) {
            $person->expects($this->once())->method('getId')->willReturn($id);
        }

        return $person;
    }

    /**
     * @param null|string $type
     * @param ClientInterface|null $client
     * @return ClientMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientMetadata($type = null, ClientInterface $client = null)
    {
        $metadata = $this->createMock('LoginCidadao\OpenIDBundle\Entity\ClientMetadata');

        if ($client) {
            $metadata->expects($this->any())->method('getClient')->willReturn($client);
        }

        if (!$type) {
            return $metadata;
        }

        $metadata->expects($this->once())->method('getSubjectType')->willReturn($type);
        if ($type === 'pairwise') {
            $metadata->expects($this->once())->method('getSectorIdentifier')
                ->willReturn('https://example.com/sector.identifier');
        }

        return $metadata;
    }

    public function testPairwiseSubjectIdentifier()
    {
        $id = 123456;
        $secret = 'my.secret';

        $repo = $this->getRepo();
        $person = $this->getPerson($id);
        $metadata = $this->getClientMetadata('pairwise');

        $service = new SubjectIdentifierService($this->getEntityManager(), $repo, $secret);
        $sub = $service->getSubjectIdentifier($person, $metadata);

        $this->assertNotNull($sub);
        $this->assertNotEquals($id, $sub);
    }

    public function testPublicSubjectIdentifier()
    {
        $id = 654321;
        $secret = 'my.secret';

        $repo = $this->getRepo();
        $person = $this->getPerson($id);
        $metadata = $this->getClientMetadata('public');

        $service = new SubjectIdentifierService($this->getEntityManager(), $repo, $secret);
        $sub = $service->getSubjectIdentifier($person, $metadata);

        $this->assertNotNull($sub);
        $this->assertEquals($id, $sub);
    }

    public function testPublicSubjectIdentifierNullMetadata()
    {
        $id = 654321;
        $secret = 'my.secret';

        $repo = $this->getRepo();
        $person = $this->getPerson($id);

        $service = new SubjectIdentifierService($this->getEntityManager(), $repo, $secret);
        $sub = $service->getSubjectIdentifier($person, null);

        $this->assertNotNull($sub);
        $this->assertEquals($id, $sub);
    }

    public function testFetchSubjectIdentifier()
    {
        $id = 123456;
        $secret = 'my.secret';
        $expectedSub = 'my_sub_id';

        $subId = $this->createMock('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier');
        $subId->expects($this->once())->method('getSubjectIdentifier')->willReturn($expectedSub);

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findOneBy')->willReturn($subId);

        $person = $this->getPerson();
        $metadata = $this->getClientMetadata();

        $service = new SubjectIdentifierService($this->getEntityManager(), $repo, $secret);
        $sub = $service->getSubjectIdentifier($person, $metadata);

        $this->assertNotNull($sub);
        $this->assertNotEquals($id, $sub);
    }

    public function testIsSubjectIdentifierPersisted()
    {
        $secret = 'my.secret';

        $subId = $this->createMock('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier');
        /** @var ClientInterface $client */
        $client = $this->createMock('LoginCidadao\OAuthBundle\Model\ClientInterface');

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findOneBy')->willReturn($subId);

        $service = new SubjectIdentifierService($this->getEntityManager(), $repo, $secret);

        $this->assertTrue($service->isSubjectIdentifierPersisted($this->getPerson(), $client));
    }

    public function testCreateOnEnforceSubjectIdentifier()
    {
        $id = 123456;
        $secret = 'my.secret';

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier'));

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findOneBy')->willReturn(null);

        $person = $this->getPerson($id);
        $client = $this->getClient();
        $metadata = $this->getClientMetadata(null, $client);

        $service = new SubjectIdentifierService($em, $repo, $secret);
        $sub = $service->enforceSubjectIdentifier($person, $metadata);

        $this->assertInstanceOf('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier', $sub);
    }

    public function testFetchOnEnforceSubjectIdentifier()
    {
        $secret = 'my.secret';

        $subId = $this->createMock('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier');

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findOneBy')->willReturn($subId);

        $person = $this->getPerson();
        $client = $this->getClient();
        $metadata = $this->getClientMetadata(null, $client);

        $service = new SubjectIdentifierService($this->getEntityManager(), $repo, $secret);
        $sub = $service->enforceSubjectIdentifier($person, $metadata);

        $this->assertInstanceOf('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier', $sub);
        $this->assertSame($subId, $sub);
    }

    public function testGetPerson()
    {
        $person = new Person();
        $subId = 'my_sub_id';
        $client = new Client();

        $repo = $this->getRepo();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['subjectIdentifier' => $subId, 'client' => $client])
            ->willReturn($person);

        $service = new SubjectIdentifierService($this->getEntityManager(), $repo, 'my.secret');
        $this->assertSame($person, $service->getPerson($subId, $client));
    }

    public function testConvertSubjectIdentifier()
    {
        $oldSub = '123';

        /** @var PersonInterface|\PHPUnit_Framework_MockObject_MockObject $person */
        $person = $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->once())->method('getId')->willReturn($oldSub);

        $client = new Client();
        $metadata = new ClientMetadata();
        $metadata->setClient($client);

        $subjectIdentifier = (new SubjectIdentifier())
            ->setClient($client)
            ->setPerson($person)
            ->setSubjectIdentifier($oldSub);

        $repo = $this->getRepo();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['person' => $person, 'client' => $client])
            ->willReturn($subjectIdentifier);

        $service = new SubjectIdentifierService($this->getEntityManager(), $repo, 'my.secret');
        $newSub = $service->convertSubjectIdentifier($person, $metadata);

        $this->assertSame($person, $newSub->getPerson());
        $this->assertSame($client, $newSub->getClient());
        $this->assertNotEquals($oldSub, $newSub->getSubjectIdentifier());
    }
}
