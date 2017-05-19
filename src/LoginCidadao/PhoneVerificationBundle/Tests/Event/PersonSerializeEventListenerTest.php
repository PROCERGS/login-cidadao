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
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerification;
use LoginCidadao\PhoneVerificationBundle\Event\PersonSerializeEventListener;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;

class PersonSerializeEventListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnPreSerialize()
    {
        $phoneVerificationService = $this->getPhoneVerificationService();

        $eventListener = new PersonSerializeEventListener($phoneVerificationService);

        $person = new Person();
        $person->setMobile(PhoneNumberUtil::getInstance()->parse('+5551999999999', 'BR'));

        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\PreSerializeEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getObject')->willReturn($person);
        $eventListener->onPreSerialize($event);

        $this->assertTrue($person->getPhoneNumberVerified());
    }

    public function testNoPhone()
    {
        $phoneVerificationService = $this->getPhoneVerificationService();

        $eventListener = new PersonSerializeEventListener($phoneVerificationService);

        $person = new Person();

        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\PreSerializeEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getObject')->willReturn($person);
        $eventListener->onPreSerialize($event);

        $this->assertFalse($person->getPhoneNumberVerified());
    }

    public function testNotPerson()
    {
        $phoneVerificationService = $this->getPhoneVerificationService();

        $eventListener = new PersonSerializeEventListener($phoneVerificationService);

        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\PreSerializeEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getObject')->willReturn(new \DateTime());
        $response = $eventListener->onPreSerialize($event);

        $this->assertNull($response);
    }

    /**
     * @return PhoneVerificationService
     */
    private function getPhoneVerificationService()
    {
        $phoneVerificationServiceClass = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface';
        $phoneVerificationService = $this->getMock($phoneVerificationServiceClass);

        $phoneVerificationService->expects($this->any())->method('getPhoneVerification')
            ->willReturnCallback(
                function ($person, $phone) {
                    $phoneVerification = new PhoneVerification();
                    $phoneVerification->setPerson($person)
                        ->setPhone($phone)
                        ->setVerificationCode('123456')
                        ->setVerifiedAt(new \DateTime());

                    return $phoneVerification;
                }
            );

        return $phoneVerificationService;
    }
}
