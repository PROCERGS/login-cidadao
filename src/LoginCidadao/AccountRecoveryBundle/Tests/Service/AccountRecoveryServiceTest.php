<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryDataRepository;
use LoginCidadao\AccountRecoveryBundle\Event\AccountRecoveryEvents;
use LoginCidadao\AccountRecoveryBundle\Event\SendResetPasswordSmsEvent;
use LoginCidadao\AccountRecoveryBundle\Mailer\AccountRecoveryMailer;
use LoginCidadao\AccountRecoveryBundle\Service\AccountRecoveryService;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AccountRecoveryServiceTest extends TestCase
{
    public function testGetExistingAccountRecoveryData()
    {
        $person = new Person();
        $data = (new AccountRecoveryData())->setPerson($person);

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository($person, $data),
            $this->getMailer(),
            $this->getDispatcher()
        );
        $this->assertSame($data, $service->getAccountRecoveryData($person));
    }

    public function testGetNonExistingAccountRecoveryDataAndDontCreate()
    {
        $person = new Person();

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository($person, null),
            $this->getMailer(),
            $this->getDispatcher()
        );
        $this->assertNull($service->getAccountRecoveryData($person, false));
    }

    public function testGetNonExistingAccountRecoveryDataAndCreate()
    {
        $person = new Person();

        $service = new AccountRecoveryService(
            $this->getEntityManager(true),
            $this->getRepository($person, null),
            $this->getMailer(),
            $this->getDispatcher()
        );
        $this->assertInstanceOf(AccountRecoveryData::class, $service->getAccountRecoveryData($person));
    }

    public function testSetEmail()
    {
        $person = new Person();
        $service = new AccountRecoveryService(
            $this->getEntityManager(true),
            $this->getRepository($person, null),
            $this->getMailer(),
            $this->getDispatcher()
        );
        $data = $service->setRecoveryEmail($person, $email = 'email@example.com');
        $this->assertSame($email, $data->getEmail());
    }

    public function testSetPhone()
    {
        $person = new Person();
        $service = new AccountRecoveryService(
            $this->getEntityManager(true),
            $this->getRepository($person, null),
            $this->getMailer(),
            $this->getDispatcher()
        );
        $data = $service->setRecoveryPhone($person, $phone = new PhoneNumber());
        $this->assertSame($phone, $data->getMobile());
    }

    public function testSendPasswordResetEmail()
    {
        $person = new Person();
        $data = $this->createMock(AccountRecoveryData::class);
        $data->expects($this->once())->method('getEmail')->willReturn('email@example.com');

        $mailer = $this->getMailer();
        $mailer->expects($this->once())->method('sendResettingEmailMessageToRecoveryEmail')->with($data);

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository($person, $data),
            $mailer,
            $this->getDispatcher()
        );
        $service->sendPasswordResetEmail($person);
    }

    public function testSendPasswordResetSms()
    {
        $person = new Person();
        $data = $this->createMock(AccountRecoveryData::class);
        $data->expects($this->once())->method('getMobile')->willReturn(new PhoneNumber());

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch')->with(
            AccountRecoveryEvents::ACCOUNT_RECOVERY_RESET_PASSWORD_SEND_SMS,
            $this->isInstanceOf(SendResetPasswordSmsEvent::class)
        );

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository($person, $data),
            $this->getMailer(),
            $dispatcher
        );
        $service->sendPasswordResetSms($person);
    }

    public function testNotifyEmailChangedFromNull()
    {
        $data = (new AccountRecoveryData())->setEmail('email@example.com');

        $mailer = $this->getMailer();
        $mailer->expects($this->never())->method('sendRecoveryEmailChangedMessage');
        $mailer->expects($this->never())->method('sendRecoveryEmailRemovedMessage');

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository(),
            $mailer,
            $this->getDispatcher()
        );
        $service->notifyIfEmailChanged($data, null);
    }

    public function testNotifyEmailChanged()
    {
        $person = (new Person())->setEmail('some@example.com');
        $data = (new AccountRecoveryData())
            ->setPerson($person)
            ->setEmail('email@example.com');

        $mailer = $this->getMailer();
        $mailer->expects($this->exactly(3))->method('sendRecoveryEmailChangedMessage');
        $mailer->expects($this->never())->method('sendRecoveryEmailRemovedMessage');

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository(),
            $mailer,
            $this->getDispatcher()
        );
        $service->notifyIfEmailChanged($data, 'old@example.com');
    }

    public function testNotifyEmailRemoved()
    {
        $person = (new Person())->setEmail('some@example.com');
        $data = (new AccountRecoveryData())
            ->setPerson($person)
            ->setEmail(null);

        $mailer = $this->getMailer();
        $mailer->expects($this->never())->method('sendRecoveryEmailChangedMessage');
        $mailer->expects($this->exactly(2))->method('sendRecoveryEmailRemovedMessage');

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository(),
            $mailer,
            $this->getDispatcher()
        );
        $service->notifyIfEmailChanged($data, 'old@example.com');
    }

    public function testNotifyPhoneChangedWithEmail()
    {
        $person = (new Person())->setEmail('some@example.com');
        $data = (new AccountRecoveryData())
            ->setPerson($person)
            ->setEmail('email@example.com')
            ->setMobile((new PhoneNumber())->setNationalNumber('1234'));

        $mailer = $this->getMailer();
        $mailer->expects($this->exactly(2))->method('sendRecoveryPhoneChangedMessage');
        $mailer->expects($this->never())->method('sendRecoveryPhoneRemovedMessage');

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository(),
            $mailer,
            $this->getDispatcher()
        );
        $service->notifyIfPhoneChanged($data, (new PhoneNumber())->setNationalNumber('4321'));
    }

    public function testNotifyPhoneChangedWithoutEmail()
    {
        $person = (new Person())->setEmail('some@example.com');
        $data = (new AccountRecoveryData())
            ->setPerson($person)
            ->setEmail(null)
            ->setMobile((new PhoneNumber())->setNationalNumber('1234'));

        $mailer = $this->getMailer();
        $mailer->expects($this->once())->method('sendRecoveryPhoneChangedMessage');
        $mailer->expects($this->never())->method('sendRecoveryPhoneRemovedMessage');

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository(),
            $mailer,
            $this->getDispatcher()
        );
        $service->notifyIfPhoneChanged($data, (new PhoneNumber())->setNationalNumber('4321'));
    }

    public function testNotifyPhoneRemovedWithEmail()
    {
        $person = (new Person())->setEmail('some@example.com');
        $data = (new AccountRecoveryData())
            ->setPerson($person)
            ->setEmail('email@example.com')
            ->setMobile(null);

        $mailer = $this->getMailer();
        $mailer->expects($this->never())->method('sendRecoveryPhoneChangedMessage');
        $mailer->expects($this->exactly(2))->method('sendRecoveryPhoneRemovedMessage');

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository(),
            $mailer,
            $this->getDispatcher()
        );
        $service->notifyIfPhoneChanged($data, (new PhoneNumber())->setNationalNumber('4321'));
    }

    public function testNotifyPhoneRemovedWithoutEmail()
    {
        $person = (new Person())->setEmail('some@example.com');
        $data = (new AccountRecoveryData())
            ->setPerson($person)
            ->setEmail(null)
            ->setMobile(null);

        $mailer = $this->getMailer();
        $mailer->expects($this->never())->method('sendRecoveryPhoneChangedMessage');
        $mailer->expects($this->once())->method('sendRecoveryPhoneRemovedMessage');

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository(),
            $mailer,
            $this->getDispatcher()
        );
        $service->notifyIfPhoneChanged($data, (new PhoneNumber())->setNationalNumber('4321'));
    }

    private function getRepository(PersonInterface $person = null, AccountRecoveryData $data = null)
    {
        /** @var AccountRecoveryDataRepository|MockObject $repo */
        $repo = $this->createMock(AccountRecoveryDataRepository::class);
        if (null !== $person) {
            $repo->expects($this->once())->method('findByPerson')
                ->with($person)
                ->willReturn($data);
        }

        return $repo;
    }

    private function getEntityManager(bool $persist)
    {
        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        if ($persist) {
            $em->expects($this->once())->method('persist')->with($this->isInstanceOf(AccountRecoveryData::class));
        } else {
            $em->expects($this->never())->method('persist');
        }

        return $em;
    }

    private function getMailer()
    {
        /** @var AccountRecoveryMailer|MockObject $mailer */
        $mailer = $this->createMock(AccountRecoveryMailer::class);

        return $mailer;
    }

    private function getDispatcher()
    {
        /** @var EventDispatcherInterface|MockObject $dispatcher */
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        return $dispatcher;
    }
}
