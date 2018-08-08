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
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use LoginCidadao\AccountRecoveryBundle\Mailer\AccountRecoveryMailer;
use LoginCidadao\AccountRecoveryBundle\Service\AccountRecoveryService;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountRecoveryDataEventSubscriber implements EventSubscriberInterface
{
    /** @var string|null */
    private $originalRecoveryEmail;

    /** @var PhoneNumber|null */
    private $originalRecoveryPhone;

    /** @var AccountRecoveryMailer */
    private $mailer;

    /** @var AccountRecoveryService */
    private $accountRecoveryService;

    /**
     * AccountRecoveryDataEventSubscriber constructor.
     * @param AccountRecoveryMailer $mailer
     * @param AccountRecoveryService $accountRecoveryService
     */
    public function __construct(AccountRecoveryMailer $mailer, AccountRecoveryService $accountRecoveryService)
    {
        $this->mailer = $mailer;
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
        $currentEmail = $event->getAccountRecoveryData()->getEmail();
        $currentPhone = $event->getAccountRecoveryData()->getMobile();

        if (null !== $this->originalRecoveryEmail && $this->originalRecoveryEmail !== $currentEmail) {
            if (null !== $currentEmail) {
                $this->notifyEmailChanged($event->getAccountRecoveryData(), $this->originalRecoveryEmail);
            } else {
                $this->notifyEmailRemoved($event->getAccountRecoveryData(), $this->originalRecoveryEmail);
            }
        }
        if (null !== $this->originalRecoveryPhone && $this->originalRecoveryPhone->__toString() !== $currentPhone->__toString()) {
            if (null !== $currentPhone) {
                $this->notifyPhoneChanged($event->getAccountRecoveryData(), $this->originalRecoveryPhone);
            } else {
                $this->notifyPhoneRemoved($event->getAccountRecoveryData(), $this->originalRecoveryPhone);
            }
        }
    }

    public function onPasswordResetRequested(GetResponseUserEvent $event)
    {
        $user = $event->getUser();
        if ($user instanceof PersonInterface) {
            $this->accountRecoveryService->sendPasswordResetEmail($user);
        }
        // TODO: send SMS
    }

    private function notifyEmailChanged(AccountRecoveryData $accountRecoveryData, string $oldEmail)
    {
        $person = $accountRecoveryData->getPerson();
        $this->mailer->sendRecoveryEmailChangedMessage($accountRecoveryData, $oldEmail);
        $this->mailer->sendRecoveryEmailChangedMessage($accountRecoveryData, $person->getEmail());
        $this->mailer->sendRecoveryEmailChangedMessage($accountRecoveryData, $accountRecoveryData->getEmail());
    }

    private function notifyPhoneChanged(AccountRecoveryData $accountRecoveryData, PhoneNumber $oldPhone)
    {
        $person = $accountRecoveryData->getPerson();
        $this->mailer->sendRecoveryPhoneChangedMessage($accountRecoveryData, $person->getEmail());
        if (null !== $accountRecoveryData->getEmail()) {
            $this->mailer->sendRecoveryPhoneChangedMessage($accountRecoveryData, $accountRecoveryData->getEmail());
        }
    }

    private function notifyEmailRemoved(AccountRecoveryData $accountRecoveryData, string $oldEmail)
    {
        $person = $accountRecoveryData->getPerson();
        $this->mailer->sendRecoveryEmailRemovedMessage($accountRecoveryData, $oldEmail);
        $this->mailer->sendRecoveryEmailRemovedMessage($accountRecoveryData, $person->getEmail());
    }

    private function notifyPhoneRemoved(AccountRecoveryData $accountRecoveryData, PhoneNumber $oldPhone)
    {
        $person = $accountRecoveryData->getPerson();
        $this->mailer->sendRecoveryPhoneRemovedMessage($accountRecoveryData, $person->getEmail());
        if (null !== $accountRecoveryData->getEmail()) {
            $this->mailer->sendRecoveryPhoneRemovedMessage($accountRecoveryData, $accountRecoveryData->getEmail());
        }
    }
}
