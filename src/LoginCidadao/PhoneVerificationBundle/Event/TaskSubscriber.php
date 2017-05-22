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

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerification;
use LoginCidadao\PhoneVerificationBundle\Exception\VerificationNotSentException;
use LoginCidadao\PhoneVerificationBundle\Model\ConfirmPhoneTask;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
use LoginCidadao\TaskStackBundle\Event\GetTasksEvent;
use LoginCidadao\TaskStackBundle\TaskStackEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TaskSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var PhoneVerificationServiceInterface */
    private $phoneVerificationService;

    /** @var bool */
    private $verificationEnabled;

    /**
     * TaskSubscriber constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param PhoneVerificationServiceInterface $phoneVerificationService
     * @param bool $verificationEnabled
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PhoneVerificationServiceInterface $phoneVerificationService,
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

        $token = $this->tokenStorage->getToken();
        if ($token instanceof OAuthToken) {
            return;
        }

        /** @var PersonInterface $user */
        $user = $token->getUser();
        if (!$user instanceof PersonInterface) {
            return;
        }

        $pending = $this->phoneVerificationService->getAllPendingPhoneVerification($user);
        if (!is_array($pending) || count($pending) === 0) {
            return;
        }

        /** @var PhoneVerification $pendingVerification */
        $pendingVerification = reset($pending);
        $lastSentVerification = $this->phoneVerificationService->getLastSentVerification($pendingVerification);

        if ($pendingVerification && !$lastSentVerification) {
            try {
                if ($user->getMobile() != $pendingVerification->getPhone()) {
                    // Orphan verification. Do not add a task
                    return;
                }
                $this->phoneVerificationService->sendVerificationCode($pendingVerification);
            } catch (VerificationNotSentException $e) {
                // could not send the verification code, do not add the task
                return;
            }
        }

        $event->addTaskIfStackEmpty(new ConfirmPhoneTask($pendingVerification->getId()));
    }
}
