<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Tests\Event;

use Eljam\CircuitBreaker\Breaker;
use Eljam\CircuitBreaker\Exception\CircuitOpenException;
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Event\PhoneVerificationSubscriber;
use PROCERGS\Sms\Exception\InvalidCountryException;
use PROCERGS\Sms\SmsService;
use Psr\Log\LogLevel;

class PhoneVerificationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertArrayHasKey(
            PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED,
            PhoneVerificationSubscriber::getSubscribedEvents()
        );
    }

    public function testOnVerificationRequestClosedCircuitBreaker()
    {
        $phoneNumber = $this->getMock('libphonenumber\PhoneNumber');
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $phoneVerification = $this->getPhoneVerification($person, $phoneNumber);
        $event = $this->getEvent($phoneVerification, true);
        $dispatcher = $this->getDispatcher($event);

        $subscriber = $this->getPhoneVerificationSubscriber();
        $subscriber->onVerificationRequest($event, PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $dispatcher);
    }

    public function testOnVerificationRequestOpenCircuitBreaker()
    {
        $phoneNumber = $this->getMock('libphonenumber\PhoneNumber');
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $phoneVerification = $this->getPhoneVerification($person, $phoneNumber);
        $event = $this->getEvent($phoneVerification, false);
        $dispatcher = $this->getDispatcher();
        $breaker = $this->getBreaker(new CircuitOpenException());

        $subscriber = $this->getPhoneVerificationSubscriber($breaker, false);
        $subscriber->onVerificationRequest($event, PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $dispatcher);
    }

    public function testOnVerificationRequestCloseCircuitBreaker()
    {
        $phoneNumber = $this->getMock('libphonenumber\PhoneNumber');
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $phoneVerification = $this->getPhoneVerification($person, $phoneNumber);
        $event = $this->getEvent($phoneVerification, false);
        $dispatcher = $this->getDispatcher();
        $breaker = $this->getBreaker(new \Exception("Generic error"));

        $subscriber = $this->getPhoneVerificationSubscriber($breaker, false);
        $subscriber->onVerificationRequest($event, PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $dispatcher);
    }

    public function testUnsupportedCountry()
    {
        $phoneNumber = new PhoneNumber();
        $phoneNumber->setCountryCode(1)
            ->setNationalNumber('123456');
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $phoneVerification = $this->getPhoneVerification($person, $phoneNumber);
        $event = $this->getEvent($phoneVerification, false);
        $dispatcher = $this->getDispatcher();
        $breakerConfig = [
            'allowed_exceptions' => ['PROCERGS\Sms\Exception\InvalidCountryException'],
        ];
        $breaker = new Breaker('my_breaker', $breakerConfig);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('log')->with(LogLevel::ERROR);

        $smsService = $this->getSmsService();
        $smsService->expects($this->once())->method('easySend')
            ->willThrowException(new InvalidCountryException('Unsupported Country'));

        $subscriber = $this->getPhoneVerificationSubscriber($breaker, false, $smsService, $logger);
        $subscriber->setLogger($logger);

        $subscriber->onVerificationRequest($event, PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $dispatcher);
    }

    /**
     * @param Breaker|null $breaker
     * @param bool $expectEasySend
     * @return PhoneVerificationSubscriber
     */
    private function getPhoneVerificationSubscriber(
        Breaker $breaker = null,
        $expectEasySend = true,
        $smsService = null,
        $logger = null
    ) {
        $smsService = $smsService ?: $this->getSmsService();
        if ($expectEasySend) {
            $smsService->expects($this->once())->method('easySend')->willReturn('012345');
        }

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->once())->method('trans')->willReturn('message');

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->willReturnCallback(
            function ($routeName) {
                return $routeName;
            }
        );

        $breaker = $breaker ?: new Breaker('breaker');

        if ($logger === null) {
            $logger = $this->getMock('Psr\Log\LoggerInterface');
            $logger->expects($this->once())->method('log');
        }

        $subscriber = new PhoneVerificationSubscriber($smsService, $translator, $router, $breaker);
        $subscriber->setLogger($logger);

        return $subscriber;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SmsService
     */
    private function getSmsService()
    {
        $smsService = $this->getMockBuilder('PROCERGS\Sms\SmsService')
            ->disableOriginalConstructor()
            ->getMock();

        return $smsService;
    }

    private function getPhoneVerification(PersonInterface $person, PhoneNumber $phoneNumber)
    {
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);
        $phoneVerification->expects($this->any())->method('getPerson')->willReturn($person);
        $phoneVerification->expects($this->any())->method('getVerificationCode')->willReturn(123456);
        $phoneVerification->expects($this->any())->method('getPhone')->willReturn($phoneNumber);

        return $phoneVerification;
    }

    private function getEvent(PhoneVerificationInterface $phoneVerification = null, $sent = true)
    {
        $event = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->atLeastOnce())->method('getPhoneVerification')->willReturn($phoneVerification);

        if ($sent) {
            $event->expects($this->once())->method('setSentVerification')->with(
                $this->isInstanceOf('LoginCidadao\PhoneVerificationBundle\Entity\SentVerification')
            );
        }

        return $event;
    }

    private function getDispatcher(SendPhoneVerificationEvent $event = null)
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        if ($event) {
            $dispatcher->expects($this->once())->method('dispatch')->with(
                PhoneVerificationEvents::PHONE_VERIFICATION_CODE_SENT,
                $event
            );
        }

        return $dispatcher;
    }

    private function getBreaker($exception = null)
    {
        $breaker = null;
        if ($exception instanceof \Exception) {
            $breaker = $this->getMockBuilder('Eljam\CircuitBreaker\Breaker')
                ->disableOriginalConstructor()
                ->getMock();
            $breaker->expects($this->once())->method('protect')
                ->willThrowException($exception);
        }

        return $breaker ?: new Breaker('name');
    }
}
