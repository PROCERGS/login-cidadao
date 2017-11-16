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

use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;

class SubjectIdentifierServiceTest extends \PHPUnit_Framework_TestCase
{
    private function getRepo()
    {
        $repo = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifierRepository')
            ->disableOriginalConstructor()
            ->getMock();

        return $repo;
    }

    private function getPerson($id = null)
    {
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        if ($id) {
            $person->expects($this->once())->method('getId')->willReturn($id);
        }

        return $person;
    }

    private function getClientMetadata($type = null)
    {
        $metadata = $this->getMock('LoginCidadao\OpenIDBundle\Entity\ClientMetadata');
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

        $service = new SubjectIdentifierService($repo, $secret);
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

        $service = new SubjectIdentifierService($repo, $secret);
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

        $service = new SubjectIdentifierService($repo, $secret);
        $sub = $service->getSubjectIdentifier($person, null);

        $this->assertNotNull($sub);
        $this->assertEquals($id, $sub);
    }

    public function testFetchSubjectIdentifier()
    {
        $id = 123456;
        $secret = 'my.secret';
        $expectedSub = 'my_sub_id';

        $subId = $this->getMock('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier');
        $subId->expects($this->once())->method('getSubjectIdentifier')->willReturn($expectedSub);

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findOneBy')->willReturn($subId);

        $person = $this->getPerson();
        $metadata = $this->getClientMetadata();

        $service = new SubjectIdentifierService($repo, $secret);
        $sub = $service->getSubjectIdentifier($person, $metadata);

        $this->assertNotNull($sub);
        $this->assertNotEquals($id, $sub);
    }

    public function testIsSubjectIdentifierPersisted()
    {
        $secret = 'my.secret';

        $subId = $this->getMock('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier');
        $client = $this->getMock('LoginCidadao\OAuthBundle\Model\ClientInterface');

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('findOneBy')->willReturn($subId);

        $service = new SubjectIdentifierService($repo, $secret);

        $this->assertTrue($service->isSubjectIdentifierPersisted($this->getPerson(), $client));
    }
}
