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

use LoginCidadao\PhoneVerificationBundle\Event\TaskSubscriber;
use LoginCidadao\TaskStackBundle\TaskStackEvents;

class TaskSubscriberTest extends \PHPUnit_Framework_TestCase
{
    private function getTokenStorage($shouldBeUsed = true, $userClass = 'LoginCidadao\CoreBundle\Model\PersonInterface')
    {
        $tokenStorage = $this->getMock(
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface'
        );
        if ($shouldBeUsed) {
            $user = $this->getMock($userClass);

            $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
            $token->expects($this->atLeastOnce())->method('getUser')->willReturn($user);

            $tokenStorage->expects($this->atLeastOnce())->method('getToken')->willReturn($token);
        }

        return $tokenStorage;
    }

    private function getPhoneVerificationService()
    {
        $class = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService';
        $phoneVerificationService = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();

        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, false);
        $subscriber->onGetTasks($event);
    }

    public function testOnGetTasks()
    {
        $phoneVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');
        $tokenStorage = $this->getTokenStorage();

        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->once())->method('getAllPendingPhoneVerification')
            ->willReturn([$phoneVerification]);

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('addTaskIfStackEmpty');

        $subscriber = new TaskSubscriber($tokenStorage, $phoneVerificationService, true);
        $subscriber->onGetTasks($event);
    }

    public function testUnsupportedUser()
    {
        $tokenStorage = $this->getTokenStorage(true, 'Symfony\Component\Security\Core\User\UserInterface');
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
}
