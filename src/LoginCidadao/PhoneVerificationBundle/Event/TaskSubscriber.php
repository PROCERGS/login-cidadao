<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Event;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Model\ConfirmPhoneTask;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;
use LoginCidadao\TaskStackBundle\Event\GetTasksEvent;
use LoginCidadao\TaskStackBundle\TaskStackEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class TaskSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorage */
    private $tokenStorage;

    /** @var PhoneVerificationService */
    private $phoneVerificationService;

    /** @var bool */
    private $verificationEnabled;

    /**
     * TaskSubscriber constructor.
     * @param TokenStorage $tokenStorage
     * @param PhoneVerificationService $phoneVerificationService
     * @param bool $verificationEnabled
     */
    public function __construct(
        TokenStorage $tokenStorage,
        PhoneVerificationService $phoneVerificationService,
        $verificationEnabled = false
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->phoneVerificationService = $phoneVerificationService;
        $this->verificationEnabled = $verificationEnabled;
    }

    public static function getSubscribedEvents()
    {
        return [
            TaskStackEvents::GET_TASKS => ['onGetTasks', 100],
        ];
    }

    public function onGetTasks(GetTasksEvent $event)
    {
        if (!$this->verificationEnabled) {
            return;
        }

        /** @var PersonInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof PersonInterface) {
            return;
        }

        $pending = $this->phoneVerificationService->getAllPendingPhoneVerification($user);
        if (count($pending) <= 0) {
            return;
        }

        $task = new ConfirmPhoneTask();
        // TODO: only add if Stack is empty
        $event->addTask($task);
    }
}
