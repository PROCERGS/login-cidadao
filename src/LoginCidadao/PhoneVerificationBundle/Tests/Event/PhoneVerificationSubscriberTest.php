<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Event;

use libphonenumber\PhoneNumberUtil;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\PhoneVerificationBundle\Event\PhoneVerificationSubscriber;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;

class PhoneVerificationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertArrayHasKey(
            PhoneVerificationEvents::PHONE_CHANGED,
            PhoneVerificationSubscriber::getSubscribedEvents()
        );
        $this->assertArrayHasKey(
            PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED,
            PhoneVerificationSubscriber::getSubscribedEvents()
        );
    }

    public function testOnPhoneChange()
    {
        $person = new Person();
        $person->setMobile(PhoneNumberUtil::getInstance()->parse('+5551999999999', 'BR'));

        $oldPhone = PhoneNumberUtil::getInstance()->parse('+5551999998888', 'BR');
        $event = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Event\PhoneChangedEvent')
            ->setConstructorArgs([$person, $oldPhone])
            ->getMock();

        $event->expects($this->any())->method('getPerson')->willReturn($person);
        $event->expects($this->any())->method('getOldPhone')->willReturn($oldPhone);

        $phoneVerificationServiceClass = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService';
        $phoneVerificationService = $this->getMockBuilder($phoneVerificationServiceClass)
            ->disableOriginalConstructor()
            ->getMock();
        $phoneVerificationService->expects($this->once())->method('getPhoneVerification')
            ->willReturn($this->getMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface'));

        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dispatcher->expects($this->once())->method('dispatch');

        $listener = new PhoneVerificationSubscriber($phoneVerificationService);
        $listener->onPhoneChange($event, PhoneVerificationEvents::PHONE_CHANGED, $dispatcher);
    }

    public function testOnVerificationRequest()
    {
        $phoneVerificationServiceClass = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService';
        $phoneVerificationService = $this->getMockBuilder($phoneVerificationServiceClass)
            ->disableOriginalConstructor()
            ->getMock();

        $person = new Person();
        $person->setMobile(PhoneNumberUtil::getInstance()->parse('+5551999999999', 'BR'));

        $oldPhone = PhoneNumberUtil::getInstance()->parse('+5551999998888', 'BR');
        $event = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Event\PhoneChangedEvent')
            ->setConstructorArgs([$person, $oldPhone])
            ->getMock();

        $event->expects($this->any())->method('getPerson')->willReturn($person);
        $event->expects($this->any())->method('getOldPhone')->willReturn($oldPhone);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->atLeastOnce())->method('log');

        $listener = new PhoneVerificationSubscriber($phoneVerificationService);
        $listener->setLogger($logger);
        $listener->onVerificationRequest($event);
    }
}
