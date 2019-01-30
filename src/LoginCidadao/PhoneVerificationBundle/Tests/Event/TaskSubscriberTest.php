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

use libphonenumber\PhoneNumberType;
use LoginCidadao\PhoneVerificationBundle\Event\TaskSubscriber;
use LoginCidadao\PhoneVerificationBundle\Exception\VerificationNotSentException;
use LoginCidadao\PhoneVerificationBundle\Model\ConfirmPhoneTask;
use LoginCidadao\TaskStackBundle\TaskStackEvents;
use PHPUnit\Framework\TestCase;

class TaskSubscriberTest extends TestCase
{
    private function getUser($userClass = 'LoginCidadao\CoreBundle\Model\PersonInterface')
    {
        return $this->createMock($userClass);
    }

    private function getTokenStorage($shouldBeUsed = true, $user = null)
    {
        $tokenStorage = $this->createMock(
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface'
        );
        if ($shouldBeUsed) {
            $user = $user ?: $this->getUser();

            $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
            $token->expects($this->atLeastOnce())->method('getUser')->willReturn($user);

            $tokenStorage->expects($this->atLeastOnce())->method('getToken')->willReturn($token);
        }

        return $tokenStorage;
    }

    private function getPhoneVerificationService()
    {
        $class = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface';
        $phoneVerificationService = $this->createMock($class);

        return $phoneVerificationService;
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals([TaskStackEvents::GET_TASKS => ['onGetTasks', 100]], TaskSubscriber::getSubscribedEvents());
    }

    public function testVerificationDisabled()
    {
        $tokenStorage = $this->getTokenStorage(false);
        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->never())->method('getAllPendingPhoneVerification');

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();

        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, false);
        $subscriber->onGetTasks($event);
    }

    public function testOnGetTasks()
    {
        $phoneVerification = $this->createMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');
        $tokenStorage = $this->getTokenStorage();

        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->once())
            ->method('getAllPendingPhoneVerification')
            ->willReturn([$phoneVerification]);
        $phoneVerificationService->expects($this->once())
            ->method('isVerificationMandatory')
            ->willReturn(true);

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('addTaskIfStackEmpty')
            ->willReturnCallback(function (ConfirmPhoneTask $task) {
                $this->assertTrue($task->isMandatory());
            });

        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, true);
        $subscriber->onGetTasks($event);
    }

    public function testOnGetTasksOAuthToken()
    {
        $token = $this->createMock('FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken');
        $tokenStorage = $this->getTokenStorage(false);
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $phoneVerificationService = $this->getPhoneVerificationService();
        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();

        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, true);
        $subscriber->onGetTasks($event);
    }

    public function testOnGetTasksOrphanVerification()
    {
        $user = $this->getUser();
        $user->expects($this->once())->method('getMobile')->willReturn(null);

        $phoneVerification = $this->createMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');
        $phoneVerification->expects($this->once())->method('getPhone')->willReturn(new PhoneNumberType());
        $tokenStorage = $this->getTokenStorage(true, $user);

        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->once())->method('getAllPendingPhoneVerification')
            ->willReturn([$phoneVerification]);

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();

        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, true);
        $subscriber->onGetTasks($event);
    }

    public function testOnGetTasksSendFailed()
    {
        $phoneVerification = $this->createMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');
        $tokenStorage = $this->getTokenStorage();

        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->once())->method('getAllPendingPhoneVerification')
            ->willReturn([$phoneVerification]);
        $phoneVerificationService->expects($this->once())->method('sendVerificationCode')->willThrowException(
            new VerificationNotSentException()
        );

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();

        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, true);
        $subscriber->onGetTasks($event);
    }

    public function testUnsupportedUser()
    {
        $tokenStorage = $this->getTokenStorage(
            true,
            $this->getUser('Symfony\Component\Security\Core\User\UserInterface')
        );
        $phoneVerificationService = $this->getPhoneVerificationService();

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();

        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, true);
        $subscriber->onGetTasks($event);
    }

    public function testNoPendingVerification()
    {
        $tokenStorage = $this->getTokenStorage();
        $phoneVerificationService = $this->getPhoneVerificationService();

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();

        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, true);
        $subscriber->onGetTasks($event);
    }

    public function testOAuthRequest()
    {
        $tokenStorage = $this->getTokenStorage(false);
        $token = $this->createMock('FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken');
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $phoneVerificationService = $this->getPhoneVerificationService();

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();
        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, true);
        $subscriber->onGetTasks($event);
    }
}
