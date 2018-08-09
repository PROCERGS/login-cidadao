<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryDataRepository;
use LoginCidadao\AccountRecoveryBundle\Event\AccountRecoveryEvents;
use LoginCidadao\AccountRecoveryBundle\Event\SendResetPasswordSmsEvent;
use LoginCidadao\AccountRecoveryBundle\Mailer\AccountRecoveryMailer;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AccountRecoveryService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var AccountRecoveryDataRepository */
    private $repository;

    /** @var AccountRecoveryMailer */
    private $mailer;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * AccountRecoveryService constructor.
     * @param EntityManagerInterface $em
     * @param AccountRecoveryDataRepository $repository
     * @param AccountRecoveryMailer $mailer
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManagerInterface $em,
        AccountRecoveryDataRepository $repository,
        AccountRecoveryMailer $mailer,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->repository = $repository;
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
    }

    public function getAccountRecoveryData(PersonInterface $person, bool $createIfNotFound = true): ?AccountRecoveryData
    {
        /** @var AccountRecoveryData $data */
        $data = $this->repository->findByPerson($person);
        if (null === $data && $createIfNotFound) {
            $data = (new AccountRecoveryData())
                ->setPerson($person);
            $this->em->persist($data);
        }

        return $data;
    }

    public function setRecoveryEmail(PersonInterface $person, string $email = null): AccountRecoveryData
    {
        return $this->getAccountRecoveryData($person)
            ->setEmail($email);
    }

    public function setRecoveryPhone(PersonInterface $person, PhoneNumber $phoneNumber = null): AccountRecoveryData
    {
        return $this->getAccountRecoveryData($person)
            ->setMobile($phoneNumber);
    }

    public function sendPasswordResetEmail(PersonInterface $person)
    {
        $data = $this->getAccountRecoveryData($person);
        if (null !== $data->getEmail()) {
            $this->mailer->sendResettingEmailMessageToRecoveryEmail($data);
        }
    }

    public function sendPasswordResetSms(PersonInterface $person)
    {
        $data = $this->getAccountRecoveryData($person);
        if (null !== $data->getMobile()) {
            $event = new SendResetPasswordSmsEvent($data);
            $this->dispatcher->dispatch(AccountRecoveryEvents::ACCOUNT_RECOVERY_RESET_PASSWORD_SEND_SMS, $event);
        }
    }

    public function notifyIfEmailChanged(AccountRecoveryData $accountRecoveryData, string $oldEmail = null)
    {
        $currentEmail = $accountRecoveryData->getEmail();
        if (null !== $oldEmail && $oldEmail !== $currentEmail) {
            if (null !== $currentEmail) {
                $this->notifyEmailChanged($accountRecoveryData, $oldEmail);
            } else {
                $this->notifyEmailRemoved($accountRecoveryData, $oldEmail);
            }
        }
    }

    private function notifyEmailChanged(AccountRecoveryData $accountRecoveryData, string $oldEmail)
    {
        $person = $accountRecoveryData->getPerson();
        $this->mailer->sendRecoveryEmailChangedMessage($accountRecoveryData, $oldEmail);
        $this->mailer->sendRecoveryEmailChangedMessage($accountRecoveryData, $person->getEmail());
        $this->mailer->sendRecoveryEmailChangedMessage($accountRecoveryData, $accountRecoveryData->getEmail());
    }

    private function notifyEmailRemoved(AccountRecoveryData $accountRecoveryData, string $oldEmail)
    {
        $person = $accountRecoveryData->getPerson();
        $this->mailer->sendRecoveryEmailRemovedMessage($accountRecoveryData, $oldEmail);
        $this->mailer->sendRecoveryEmailRemovedMessage($accountRecoveryData, $person->getEmail());
    }

    public function notifyIfPhoneChanged(AccountRecoveryData $accountRecoveryData, PhoneNumber $oldPhone = null)
    {
        $currentPhone = $accountRecoveryData->getMobile();
        if (null !== $oldPhone && (string)$oldPhone !== (string)$currentPhone) {
            if (null !== $currentPhone) {
                $this->notifyPhoneChanged($accountRecoveryData, $oldPhone);
            } else {
                $this->notifyPhoneRemoved($accountRecoveryData, $oldPhone);
            }
        }
    }

    private function notifyPhoneChanged(AccountRecoveryData $accountRecoveryData, PhoneNumber $oldPhone)
    {
        $person = $accountRecoveryData->getPerson();
        $this->mailer->sendRecoveryPhoneChangedMessage($accountRecoveryData, $person->getEmail());
        if (null !== $accountRecoveryData->getEmail()) {
            $this->mailer->sendRecoveryPhoneChangedMessage($accountRecoveryData, $accountRecoveryData->getEmail());
        }
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
