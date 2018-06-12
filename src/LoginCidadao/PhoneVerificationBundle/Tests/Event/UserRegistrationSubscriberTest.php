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

use FOS\UserBundle\FOSUserEvents;
use LoginCidadao\PhoneVerificationBundle\Event\UserRegistrationSubscriber;
use PHPUnit\Framework\TestCase;

class UserRegistrationSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertArrayHasKey(
            FOSUserEvents::REGISTRATION_COMPLETED,
            UserRegistrationSubscriber::getSubscribedEvents()
        );
    }

    public function testOnRegistrationCompletedWithPhone()
    {
        $phone = $this->createMock('libphonenumber\PhoneNumber');
        $user = $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $user->expects($this->exactly(2))->method('getMobile')->willReturn($phone);

        $verificationService = $this->createMock(
            'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface'
        );
        $verificationService->expects($this->once())
            ->method('enforcePhoneVerification')->with($user, $phone)
            ->willReturn($this->getPhoneVerification());

        $event = $this->getMockBuilder('FOS\UserBundle\Event\FilterUserResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getUser')->willReturn($user);

        $subscriber = new UserRegistrationSubscriber($verificationService, $this->getStackManager(true));
        $subscriber->onRegistrationCompleted($event);
    }

    public function testOnRegistrationCompletedWithoutPhone()
    {
        $user = $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $user->expects($this->once())->method('getMobile')->willReturn(null);

        $verificationService = $this->createMock(
            'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface'
        );

        $event = $this->getMockBuilder('FOS\UserBundle\Event\FilterUserResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getUser')->willReturn($user);

        $subscriber = new UserRegistrationSubscriber($verificationService, $this->getStackManager(false));
        $subscriber->onRegistrationCompleted($event);
    }

    private function getStackManager($setTaskSkipped = false)
    {
        $taskStackManager = $this->createMock('LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface');

        if ($setTaskSkipped) {
            $taskStackManager->expects($this->once())->method('setTaskSkipped')
                ->with($this->isInstanceOf('LoginCidadao\TaskStackBundle\Model\TaskInterface'));
        }

        return $taskStackManager;
    }

    private function getPhoneVerification()
    {
        $phoneVerification = $this->createMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');
        $phoneVerification->expects($this->once())->method('getId')->willReturn('some_task');

        return $phoneVerification;
    }
}
