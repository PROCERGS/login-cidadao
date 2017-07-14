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

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use LoginCidadao\PhoneVerificationBundle\Model\ConfirmPhoneTask;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class UserRegistrationSubscriber implements EventSubscriberInterface
{
    /** @var PhoneVerificationServiceInterface */
    private $phoneVerificationService;

    /** @var TaskStackManagerInterface */
    private $stackManager;

    public function __construct(
        PhoneVerificationServiceInterface $phoneVerificationService,
        TaskStackManagerInterface $stackManager
    ) {
        $this->phoneVerificationService = $phoneVerificationService;
        $this->stackManager = $stackManager;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
        );
    }

    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        if (!$user instanceof PersonInterface || !$user->getMobile()) {
            return;
        }

        $phoneVerification = $this->phoneVerificationService->enforcePhoneVerification($user, $user->getMobile());
        $task = new ConfirmPhoneTask($phoneVerification->getId());
        $this->stackManager->setTaskSkipped($task);
    }
}
