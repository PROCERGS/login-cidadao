<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Entity;

use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;

class SubjectIdentifierTest extends \PHPUnit_Framework_TestCase
{
    public function testGettersSetters()
    {
        $subId = 'some_subject_identifier';
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $client = $this->getMock('LoginCidadao\OAuthBundle\Model\ClientInterface');
        $createdAt = new \DateTime();
        $updatedAt = new \DateTime();

        $sub = new SubjectIdentifier();
        $sub
            ->setSubjectIdentifier($subId)
            ->setPerson($person)
            ->setClient($client)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt);

        $this->assertEquals($subId, $sub->getSubjectIdentifier());
        $this->assertEquals($person, $sub->getPerson());
        $this->assertEquals($client, $sub->getClient());
        $this->assertEquals($createdAt, $sub->getCreatedAt());
        $this->assertEquals($updatedAt, $sub->getUpdatedAt());
    }

    /**
     * @group time-sensitive
     */
    public function testLifecycleCallbacks()
    {
        $sub = new SubjectIdentifier();
        $sub->setUpdatedAt()
            ->setCreatedAt();

        $this->assertInstanceOf('\DateTime', $sub->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $sub->getUpdatedAt());

        $previousDate = $sub->getUpdatedAt();
        sleep(1);
        $sub->setUpdatedAt();

        $this->assertNotEquals($previousDate, $sub->getUpdatedAt());
    }
}
