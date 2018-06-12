<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;
use PROCERGS\LoginCidadao\NfgBundle\Event\GetConnectCallbackResponseEvent;
use PROCERGS\LoginCidadao\NfgBundle\EventListener\NfgSubscriber;
use PROCERGS\LoginCidadao\NfgBundle\NfgEvents;
use Psr\Log\LoggerInterface;

class NfgSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $events = NfgSubscriber::getSubscribedEvents();

        $this->assertCount(1, $events);
        $this->assertEquals([
            NfgEvents::CONNECT_CALLBACK_RESPONSE => 'onConnectCallbackResponse',
        ], $events);
    }

    public function testOnConnectCallbackResponseNoPersonOrNfgProfile()
    {
        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->never())->method('notice');

        $personMeuRS = new PersonMeuRS();

        /** @var GetConnectCallbackResponseEvent|MockObject $event */
        $event = $this->getMockBuilder('PROCERGS\LoginCidadao\NfgBundle\Event\GetConnectCallbackResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->exactly(2))->method('getPersonMeuRS')->willReturn($personMeuRS);

        $subscriber = new NfgSubscriber($em);
        $subscriber->setLogger($logger);

        $subscriber->onConnectCallbackResponse($event);
    }

    public function testOnConnectCallbackResponseDoNothing()
    {
        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->never())->method('notice');

        $person = new Person();
        $person->setBirthdate(new \DateTime());
        $person->setMobile(new PhoneNumber());

        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setPerson($person);

        /** @var GetConnectCallbackResponseEvent|MockObject $event */
        $event = $this->getMockBuilder('PROCERGS\LoginCidadao\NfgBundle\Event\GetConnectCallbackResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->exactly(2))->method('getPersonMeuRS')->willReturn($personMeuRS);

        $subscriber = new NfgSubscriber($em);
        $subscriber->setLogger($logger);

        $subscriber->onConnectCallbackResponse($event);
    }

    public function testOnConnectCallbackResponseUpdate()
    {
        $personInterface = 'LoginCidadao\CoreBundle\Model\PersonInterface';
        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf($personInterface));
        $em->expects($this->once())->method('flush');

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->exactly(2))->method('notice');

        $phoneNumber = new PhoneNumber();

        /** @var PersonInterface|MockObject $person */
        $person = $this->createMock($personInterface);
        $person->expects($this->once())->method('setBirthdate');
        $person->expects($this->once())->method('setMobile')->with($phoneNumber);

        $nfgProfile = new NfgProfile();
        $nfgProfile->setMobile($phoneNumber);
        $nfgProfile->setBirthdate(new \DateTime());

        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setPerson($person);
        $personMeuRS->setNfgProfile($nfgProfile);

        $event = $this->getMockBuilder('PROCERGS\LoginCidadao\NfgBundle\Event\GetConnectCallbackResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->exactly(2))->method('getPersonMeuRS')->willReturn($personMeuRS);

        $subscriber = new NfgSubscriber($em);
        $subscriber->setLogger($logger);

        $subscriber->onConnectCallbackResponse($event);
    }
}
