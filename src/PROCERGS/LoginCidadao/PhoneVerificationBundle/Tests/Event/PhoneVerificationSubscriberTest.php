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

use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Event\PhoneVerificationSubscriber;

class PhoneVerificationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertArrayHasKey(
            PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED,
            PhoneVerificationSubscriber::getSubscribedEvents()
        );
    }

    public function testOnVerificationRequest()
    {
        $phoneNumber = $this->getMock('libphonenumber\PhoneNumber');

        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->atLeastOnce())->method('getMobile')->willReturn($phoneNumber);

        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);
        $phoneVerification->expects($this->any())->method('getPerson')->willReturn($person);
        $phoneVerification->expects($this->any())->method('getVerificationCode')->willReturn(123456);

        $event = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getPhoneVerification')->willReturn($phoneVerification);
        $event->expects($this->once())->method('setSentVerification')->with(
            $this->isInstanceOf('LoginCidadao\PhoneVerificationBundle\Entity\SentVerification')
        );

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher->expects($this->once())->method('dispatch')->with(
            PhoneVerificationEvents::PHONE_VERIFICATION_CODE_SENT,
            $event
        );

        $subscriber = $this->getPhoneVerificationSubscriber();
        $subscriber->onVerificationRequest($event, PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $dispatcher);
    }

    /**
     * @return PhoneVerificationSubscriber
     */
    private function getPhoneVerificationSubscriber()
    {
        $smsService = $this->getMockBuilder('PROCERGS\Sms\SmsService')
            ->disableOriginalConstructor()
            ->getMock();
        $smsService->expects($this->once())->method('easySend')->willReturn('012345');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->once())->method('trans')->willReturn('message');

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->willReturnCallback(
            function ($routeName) {
                return $routeName;
            }
        );

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('log');

        $subscriber = new PhoneVerificationSubscriber($smsService, $translator, $router);
        $subscriber->setLogger($logger);

        return $subscriber;
    }
}