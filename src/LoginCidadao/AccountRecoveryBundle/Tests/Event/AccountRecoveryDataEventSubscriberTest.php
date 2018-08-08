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

use libphonenumber\PhoneNumber;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use LoginCidadao\AccountRecoveryBundle\Event\AccountRecoveryDataEditEvent;
use LoginCidadao\AccountRecoveryBundle\Event\AccountRecoveryDataEventSubscriber;
use LoginCidadao\AccountRecoveryBundle\Event\AccountRecoveryEvents;
use LoginCidadao\AccountRecoveryBundle\Mailer\AccountRecoveryMailer;
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
        ], AccountRecoveryDataEventSubscriber::getSubscribedEvents());
    }

    public function testOnEditInitialize()
    {
        /** @var AccountRecoveryMailer|MockObject $mailer */
        $mailer = $this->createMock(AccountRecoveryMailer::class);

        /** @var AccountRecoveryData|MockObject $data */
        $data = $this->createMock(AccountRecoveryData::class);
        $data->expects($this->once())->method('getEmail');
        $data->expects($this->once())->method('getMobile');

        $event = new AccountRecoveryDataEditEvent($data);

        $subscriber = new AccountRecoveryDataEventSubscriber($mailer);
        $subscriber->onEditInitialize($event);
    }

    public function testOnEditCompletedWithChanges()
    {
        $initialEmail = 'email@example.com';
        $initialPhone = new PhoneNumber();

        $finalEmail = 'another.email@example.com';
        $finalPhone = new PhoneNumber();

        /** @var AccountRecoveryMailer|MockObject $mailer */
        $mailer = $this->createMock(AccountRecoveryMailer::class);
        $mailer->expects($this->exactly(3))->method('sendRecoveryEmailChangedMessage');
        $mailer->expects($this->exactly(2))->method('sendRecoveryPhoneChangedMessage');

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

        $subscriber = new AccountRecoveryDataEventSubscriber($mailer);
        $subscriber->onEditInitialize($initialEvent);
        $subscriber->onEditCompleted($finalEvent);
    }

    public function testOnEditCompletedRemovedRecoveryData()
    {
        $initialEmail = 'email@example.com';
        $initialPhone = new PhoneNumber();

        $finalEmail = null;
        $finalPhone = null;

        /** @var AccountRecoveryMailer|MockObject $mailer */
        $mailer = $this->createMock(AccountRecoveryMailer::class);
        $mailer->expects($this->exactly(2))->method('sendRecoveryEmailRemovedMessage');
        $mailer->expects($this->exactly(1))->method('sendRecoveryPhoneRemovedMessage');

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

        $subscriber = new AccountRecoveryDataEventSubscriber($mailer);
        $subscriber->onEditInitialize($initialEvent);
        $subscriber->onEditCompleted($finalEvent);
    }

    public function testOnEditCompletedRemovedPhoneWithoutEmail()
    {
        $initialEmail = null;
        $initialPhone = new PhoneNumber();

        $finalEmail = $initialEmail;
        $finalPhone = null;

        /** @var AccountRecoveryMailer|MockObject $mailer */
        $mailer = $this->createMock(AccountRecoveryMailer::class);
        $mailer->expects($this->never())->method('sendRecoveryEmailRemovedMessage');
        $mailer->expects($this->once())->method('sendRecoveryPhoneRemovedMessage');

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

        $subscriber = new AccountRecoveryDataEventSubscriber($mailer);
        $subscriber->onEditInitialize($initialEvent);
        $subscriber->onEditCompleted($finalEvent);
    }

    public function testOnEditCompletedRemovedPhoneWithEmail()
    {
        $initialEmail = 'email@example.com';
        $initialPhone = new PhoneNumber();

        $finalEmail = $initialEmail;
        $finalPhone = null;

        /** @var AccountRecoveryMailer|MockObject $mailer */
        $mailer = $this->createMock(AccountRecoveryMailer::class);
        $mailer->expects($this->never())->method('sendRecoveryEmailRemovedMessage');
        $mailer->expects($this->exactly(2))->method('sendRecoveryPhoneRemovedMessage');

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

        $subscriber = new AccountRecoveryDataEventSubscriber($mailer);
        $subscriber->onEditInitialize($initialEvent);
        $subscriber->onEditCompleted($finalEvent);
    }

    public function testOnEditCompletedWithoutChanges()
    {
        $initialEmail = 'email@example.com';
        $initialPhone = new PhoneNumber();

        $finalEmail = $initialEmail;
        $finalPhone = $initialPhone;

        /** @var AccountRecoveryMailer|MockObject $mailer */
        $mailer = $this->createMock(AccountRecoveryMailer::class);
        $mailer->expects($this->never())->method('sendRecoveryEmailChangedMessage');
        $mailer->expects($this->never())->method('sendRecoveryPhoneChangedMessage');

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

        $subscriber = new AccountRecoveryDataEventSubscriber($mailer);
        $subscriber->onEditInitialize($initialEvent);
        $subscriber->onEditCompleted($finalEvent);
    }

    public function testOnAddRecoveryData()
    {
        $initialEmail = null;
        $initialPhone = null;

        $finalEmail = $initialEmail;
        $finalPhone = $initialPhone;

        /** @var AccountRecoveryMailer|MockObject $mailer */
        $mailer = $this->createMock(AccountRecoveryMailer::class);
        $mailer->expects($this->never())->method('sendRecoveryEmailChangedMessage');
        $mailer->expects($this->never())->method('sendRecoveryPhoneChangedMessage');

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

        $subscriber = new AccountRecoveryDataEventSubscriber($mailer);
        $subscriber->onEditInitialize($initialEvent);
        $subscriber->onEditCompleted($finalEvent);
    }
}
