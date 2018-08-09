<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Event;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use libphonenumber\PhoneNumber;
use LoginCidadao\AccountRecoveryBundle\Service\AccountRecoveryService;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountRecoveryDataEventSubscriber implements EventSubscriberInterface
{
    /** @var string|null */
    private $originalRecoveryEmail;

    /** @var PhoneNumber|null */
    private $originalRecoveryPhone;

    /** @var AccountRecoveryService */
    private $accountRecoveryService;

    /**
     * AccountRecoveryDataEventSubscriber constructor.
     * @param AccountRecoveryService $accountRecoveryService
     */
    public function __construct(AccountRecoveryService $accountRecoveryService)
    {
        $this->accountRecoveryService = $accountRecoveryService;
    }

    public static function getSubscribedEvents()
    {
        return [
            AccountRecoveryEvents::ACCOUNT_RECOVERY_DATA_EDIT_INITIALIZE => 'onEditInitialize',
            AccountRecoveryEvents::ACCOUNT_RECOVERY_DATA_EDIT_COMPLETED => 'onEditCompleted',
            FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED => 'onPasswordResetRequested',
        ];
    }

    public function onEditInitialize(AccountRecoveryDataEditEvent $event)
    {
        $this->originalRecoveryEmail = $event->getAccountRecoveryData()->getEmail();
        $this->originalRecoveryPhone = $event->getAccountRecoveryData()->getMobile();
    }

    public function onEditCompleted(AccountRecoveryDataEditEvent $event)
    {
        $this->accountRecoveryService->notifyIfEmailChanged(
            $event->getAccountRecoveryData(),
            $this->originalRecoveryEmail
        );

        $this->accountRecoveryService->notifyIfPhoneChanged(
            $event->getAccountRecoveryData(),
            $this->originalRecoveryPhone
        );
    }

    public function onPasswordResetRequested(GetResponseUserEvent $event)
    {
        $user = $event->getUser();
        if ($user instanceof PersonInterface) {
            $this->accountRecoveryService->sendPasswordResetEmail($user);
            $this->accountRecoveryService->sendPasswordResetSms($user);
        }
    }
}
