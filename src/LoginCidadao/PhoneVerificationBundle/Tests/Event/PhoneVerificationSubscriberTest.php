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
use Symfony\Component\Security\Http\SecurityEvents;

class PhoneVerificationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    private function getPhoneVerification()
    {
        $phoneVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');

        return $phoneVerification;
    }

    private function getPhoneVerificationService()
    {
        $phoneVerificationServiceClass = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface';
        $phoneVerificationService = $this->getMock($phoneVerificationServiceClass);

        return $phoneVerificationService;
    }

    private function getDispatcher()
    {
        return $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

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

        $phoneVerification = $this->getPhoneVerification();

        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->once())->method('getPhoneVerification')
            ->willReturn($phoneVerification);
        $phoneVerificationService->expects($this->once())->method('createPhoneVerification')
            ->willReturn($phoneVerification);

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch');

        $listener = new PhoneVerificationSubscriber($phoneVerificationService);
        $listener->onPhoneChange($event, PhoneVerificationEvents::PHONE_CHANGED, $dispatcher);
    }

    public function testOnPhoneSet()
    {
        $person = new Person();
        $person->setMobile(PhoneNumberUtil::getInstance()->parse('+5551999999999', 'BR'));

        $event = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Event\PhoneChangedEvent')
            ->setConstructorArgs([$person, null])
            ->getMock();

        $event->expects($this->any())->method('getPerson')->willReturn($person);
        $event->expects($this->any())->method('getOldPhone')->willReturn(null);

        $phoneVerification = $this->getPhoneVerification();

        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->once())->method('createPhoneVerification')
            ->willReturn($phoneVerification);

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch');

        $listener = new PhoneVerificationSubscriber($phoneVerificationService);
        $listener->onPhoneChange($event, PhoneVerificationEvents::PHONE_CHANGED, $dispatcher);
    }

    public function testOnPhoneUnset()
    {
        $oldPhone = PhoneNumberUtil::getInstance()->parse('+5551999999999', 'BR');
        $person = new Person();
        $person->setMobile(null);

        $event = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Event\PhoneChangedEvent')
            ->setConstructorArgs([$person, null])
            ->getMock();

        $event->expects($this->any())->method('getPerson')->willReturn($person);
        $event->expects($this->any())->method('getOldPhone')->willReturn($oldPhone);

        $phoneVerificationService = $this->getPhoneVerificationService();

        $dispatcher = $this->getDispatcher();

        $listener = new PhoneVerificationSubscriber($phoneVerificationService);
        $listener->onPhoneChange($event, PhoneVerificationEvents::PHONE_CHANGED, $dispatcher);
    }

    public function testOnVerificationRequest()
    {
        $phoneVerificationServiceClass = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface';
        $phoneVerificationService = $this->getMock($phoneVerificationServiceClass);

        $person = new Person();
        $person->setMobile(PhoneNumberUtil::getInstance()->parse('+5551999999999', 'BR'));

        $phoneVerification = $this->getPhoneVerification();
        $phoneVerification->expects($this->any())->method('getPerson')->willReturn($person);

        $event = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent')
            ->setConstructorArgs([$phoneVerification])
            ->getMock();
        $event->expects($this->once())->method('getPhoneVerification')->willReturn($phoneVerification);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->atLeastOnce())->method('log');

        $listener = new PhoneVerificationSubscriber($phoneVerificationService);
        $listener->setLogger($logger);
        $listener->onVerificationRequest($event);
    }

    public function testOnCodeSent()
    {
        $sentVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface');

        $phoneVerificationServiceClass = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface';
        $phoneVerificationService = $this->getMock($phoneVerificationServiceClass);
        $phoneVerificationService->expects($this->once())->method('registerVerificationSent')->with($sentVerification);

        $event = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getSentVerification')->willReturn($sentVerification);

        $listener = new PhoneVerificationSubscriber($phoneVerificationService);
        $listener->onCodeSent($event);
    }

    public function testOnLoginNoPhone()
    {
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->once())->method('getMobile')->willReturn(null);

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($person);

        $phoneVerificationService = $this->getPhoneVerificationService();
        $dispatcher = $this->getDispatcher();

        $event = $this->getMockBuilder('Symfony\Component\Security\Http\Event\InteractiveLoginEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getAuthenticationToken')->willReturn($token);

        $listener = new PhoneVerificationSubscriber($phoneVerificationService);
        $listener->onLogin($event, SecurityEvents::INTERACTIVE_LOGIN, $dispatcher);
    }

    public function testOnLoginWithPhone()
    {
        $phone = $this->getMockBuilder('libphonenumber\PhoneNumber')
            ->disableOriginalConstructor()
            ->getMock();

        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->once())->method('getMobile')->willReturn($phone);

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($person);

        $verification = $this->getPhoneVerification();
        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->once())->method('enforcePhoneVerification')
            ->willReturn($verification);

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch')
            ->with(
                PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED,
                $this->isInstanceOf('LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent')
            );

        $event = $this->getMockBuilder('Symfony\Component\Security\Http\Event\InteractiveLoginEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getAuthenticationToken')->willReturn($token);

        $listener = new PhoneVerificationSubscriber($phoneVerificationService);
        $listener->onLogin($event, SecurityEvents::INTERACTIVE_LOGIN, $dispatcher);
    }
}
