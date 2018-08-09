<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Tests\Event;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use libphonenumber\PhoneNumber;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use LoginCidadao\AccountRecoveryBundle\Event\AccountRecoveryDataEditEvent;
use LoginCidadao\AccountRecoveryBundle\Event\AccountRecoveryDataEventSubscriber;
use LoginCidadao\AccountRecoveryBundle\Event\AccountRecoveryEvents;
use LoginCidadao\AccountRecoveryBundle\Mailer\AccountRecoveryMailer;
use LoginCidadao\AccountRecoveryBundle\Service\AccountRecoveryService;
use LoginCidadao\CoreBundle\Entity\Person;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AccountRecoveryDataEventSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            AccountRecoveryEvents::ACCOUNT_RECOVERY_DATA_EDIT_INITIALIZE => 'onEditInitialize',
            AccountRecoveryEvents::ACCOUNT_RECOVERY_DATA_EDIT_COMPLETED => 'onEditCompleted',
            FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED => 'onPasswordResetRequested',
        ], AccountRecoveryDataEventSubscriber::getSubscribedEvents());
    }

    public function testOnEditInitialize()
    {
        /** @var AccountRecoveryData|MockObject $data */
        $data = $this->createMock(AccountRecoveryData::class);
        $data->expects($this->once())->method('getEmail');
        $data->expects($this->once())->method('getMobile');

        $event = new AccountRecoveryDataEditEvent($data);

        $subscriber = new AccountRecoveryDataEventSubscriber($this->getAccountRecoveryService());
        $subscriber->onEditInitialize($event);
    }

    public function testOnEditCompleted()
    {
        $initialEmail = 'email@example.com';
        $initialPhone = new PhoneNumber();

        $finalEmail = 'another.email@example.com';
        $finalPhone = new PhoneNumber();

        $finalData = (new AccountRecoveryData())
            ->setPerson((new Person())
                ->setEmail($mainEmail = 'main@example.com'))
            ->setEmail($finalEmail)
            ->setMobile($finalPhone);

        /** @var AccountRecoveryData|MockObject $data */
        $data = $this->createMock(AccountRecoveryData::class);
        $data->expects($this->once())->method('getEmail')->willReturn($initialEmail);
        $data->expects($this->once())->method('getMobile')->willReturn($initialPhone);

        $recoveryService = $this->getAccountRecoveryService();
        $recoveryService->expects($this->once())->method('notifyIfEmailChanged')->with($finalData, $initialEmail);
        $recoveryService->expects($this->once())->method('notifyIfPhoneChanged')->with($finalData, $initialPhone);

        $event = new AccountRecoveryDataEditEvent($data);
        $finalEvent = new AccountRecoveryDataEditEvent($finalData);

        $subscriber = new AccountRecoveryDataEventSubscriber($recoveryService);
        $subscriber->onEditInitialize($event);
        $subscriber->onEditCompleted($finalEvent);
    }

    public function testOnPasswordResetRequested()
    {
        $user = new Person();
        $event = new GetResponseUserEvent($user);

        $recoveryService = $this->getAccountRecoveryService();
        $recoveryService->expects($this->once())->method('sendPasswordResetEmail')->with($user);
        $recoveryService->expects($this->once())->method('sendPasswordResetSms')->with($user);

        $subscriber = new AccountRecoveryDataEventSubscriber($recoveryService);
        $subscriber->onPasswordResetRequested($event);
    }

    public function testOnEditCompletedRemovedPhoneWithoutEmail()
    {
        $this->markTestSkipped();
        $initialEmail = null;
        $initialPhone = new PhoneNumber();

        $finalEmail = $initialEmail;
        $finalPhone = null;

        /** @var AccountRecoveryData|MockObject $data */
        $data = $this->createMock(AccountRecoveryData::class);
        $data->expects($this->once())->method('getEmail')->willReturn($initialEmail);
        $data->expects($this->once())->method('getMobile')->willReturn($initialPhone);

        $finalData = (new AccountRecoveryData())
            ->setPerson((new Person())
                ->setEmail($mainEmail = 'main@example.com'))
            ->setEmail($finalEmail)
            ->setMobile($finalPhone);

        $initialEvent = new AccountRecoveryDataEditEvent($data);
        $finalEvent = new AccountRecoveryDataEditEvent($finalData);

        $subscriber = new AccountRecoveryDataEventSubscriber($this->getAccountRecoveryService());
        $subscriber->onEditInitialize($initialEvent);
        $subscriber->onEditCompleted($finalEvent);
    }

    public function testOnEditCompletedRemovedPhoneWithEmail()
    {
        $this->markTestSkipped();
        $initialEmail = 'email@example.com';
        $initialPhone = new PhoneNumber();

        $finalEmail = $initialEmail;
        $finalPhone = null;

        /** @var AccountRecoveryData|MockObject $data */
        $data = $this->createMock(AccountRecoveryData::class);
        $data->expects($this->once())->method('getEmail')->willReturn($initialEmail);
        $data->expects($this->once())->method('getMobile')->willReturn($initialPhone);

        $finalData = (new AccountRecoveryData())
            ->setPerson((new Person())
                ->setEmail($mainEmail = 'main@example.com'))
            ->setEmail($finalEmail)
            ->setMobile($finalPhone);

        $initialEvent = new AccountRecoveryDataEditEvent($data);
        $finalEvent = new AccountRecoveryDataEditEvent($finalData);

        $subscriber = new AccountRecoveryDataEventSubscriber($this->getAccountRecoveryService());
        $subscriber->onEditInitialize($initialEvent);
        $subscriber->onEditCompleted($finalEvent);
    }

    public function testOnEditCompletedWithoutChanges()
    {
        $this->markTestSkipped();
        $initialEmail = 'email@example.com';
        $initialPhone = new PhoneNumber();

        $finalEmail = $initialEmail;
        $finalPhone = $initialPhone;

        /** @var AccountRecoveryData|MockObject $data */
        $data = $this->createMock(AccountRecoveryData::class);
        $data->expects($this->once())->method('getEmail')->willReturn($initialEmail);
        $data->expects($this->once())->method('getMobile')->willReturn($initialPhone);

        $finalData = (new AccountRecoveryData())
            ->setPerson((new Person())
                ->setEmail($mainEmail = 'main@example.com'))
            ->setEmail($finalEmail)
            ->setMobile($finalPhone);

        $initialEvent = new AccountRecoveryDataEditEvent($data);
        $finalEvent = new AccountRecoveryDataEditEvent($finalData);

        $subscriber = new AccountRecoveryDataEventSubscriber($this->getAccountRecoveryService());
        $subscriber->onEditInitialize($initialEvent);
        $subscriber->onEditCompleted($finalEvent);
    }

    /**
     * @return AccountRecoveryService|MockObject
     */
    private function getAccountRecoveryService()
    {
        /** @var AccountRecoveryService|MockObject $accountRecoveryService */
        $accountRecoveryService = $this->createMock(AccountRecoveryService::class);

        return $accountRecoveryService;
    }
}
