<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerification;
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Event\UpdateStatusEvent;
use LoginCidadao\PhoneVerificationBundle\Service\SmsStatusService;
use LoginCidadao\PhoneVerificationBundle\Model\SmsStatusInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SmsStatusServiceTest extends TestCase
{
    public function testNoPendingUpdate()
    {
        $repo = $this->getRepository([]);
        $updater = $this->getSmsStatusService($this->getEntityManager(), $this->getDispatcher(), $repo, $this->getIo());
        $updater->updateSentVerificationStatus();
    }

    public function testOnePendingUpdate()
    {
        $date = new \DateTime();

        /** @var SmsStatusInterface|MockObject $status */
        $status = $this->createMock('LoginCidadao\PhoneVerificationBundle\Model\SmsStatusInterface');
        $status->expects($this->once())->method('getDateSent')->willReturn($date);
        $status->expects($this->once())->method('getDateDelivered')->willReturn($date);

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->atMost(1))
            ->method('dispatch')
            ->willReturnCallback(
                function ($eventName, UpdateStatusEvent $event) use ($status) {
                    $event->setDeliveryStatus($status);
                }
            );

        $sentVerification = new SentVerification();
        $sentVerification->setTransactionId('0123456');

        $items = [
            [
                $sentVerification,
            ],
        ];

        $repo = $this->getRepository($items);
        $updater = $this->getSmsStatusService($this->getEntityManager(), $dispatcher, $repo, $this->getIo());
        $updater->updateSentVerificationStatus();

        $this->assertEquals($date, $sentVerification->getActuallySentAt());
        $this->assertEquals($date, $sentVerification->getDeliveredAt());
        $this->assertTrue($sentVerification->isFinished());
    }

    public function testOnePendingUpdateNoListener()
    {
        $dispatcher = $this->getDispatcher();

        $sentVerification = new SentVerification();
        $sentVerification->setTransactionId('0123456');

        $items = [
            [
                $sentVerification,
            ],
        ];

        $repo = $this->getRepository($items);
        $updater = $this->getSmsStatusService($this->getEntityManager(), $dispatcher, $repo, $this->getIo());
        $updater->updateSentVerificationStatus();

        $this->assertFalse($sentVerification->isFinished());
    }

    public function testOnePendingUpdateNoListenerNoIo()
    {
        $dispatcher = $this->getDispatcher();

        $sentVerification = new SentVerification();
        $sentVerification->setTransactionId('0123456');

        $items = [
            [
                $sentVerification,
            ],
        ];

        $repo = $this->getRepository($items);
        $updater = $this->getSmsStatusService($this->getEntityManager(), $dispatcher, $repo);
        $updater->updateSentVerificationStatus();

        $this->assertFalse($sentVerification->isFinished());
    }

    public function testAverageTime()
    {
        $sentVerification1 = new SentVerification();
        $sentVerification1->setSentAt(new \DateTime())
            ->setDeliveredAt(new \DateTime('+30 seconds'))
            ->setActuallySentAt(new \DateTime('+5 seconds'));

        $sentVerification2 = new SentVerification();
        $sentVerification2->setSentAt(new \DateTime())
            ->setDeliveredAt(new \DateTime('+30 seconds'))
            ->setActuallySentAt(new \DateTime('+5 seconds'));

        $verifications = [
            $sentVerification1,
            $sentVerification2,
        ];

        $repo = $this->getRepository();
        $repo->expects($this->once())->method('getLastDeliveredVerifications')
            ->willReturn($verifications);

        $updater = $this->getSmsStatusService($this->getEntityManager(), $this->getDispatcher(), $repo, $this->getIo());
        $avg = $updater->getAverageDeliveryTime(2);

        $this->assertEquals(30, $avg);
    }

    public function testNoAverageTime()
    {
        $repo = $this->getRepository();
        $repo->expects($this->once())->method('getLastDeliveredVerifications')
            ->willReturn([]);

        $updater = $this->getSmsStatusService($this->getEntityManager(), $this->getDispatcher(), $repo, $this->getIo());
        $avg = $updater->getAverageDeliveryTime(2);

        $this->assertEquals(0, $avg);
    }

    public function testGetDelayedDeliveryTransactions()
    {
        $sentVerification = new SentVerification();
        $sentVerification->setSentAt(new \DateTime())
            ->setTransactionId('0123456');

        $repo = $this->getRepository();
        $repo->expects($this->once())->method('getNotDeliveredSince')->willReturn([$sentVerification]);

        $updater = $this->getSmsStatusService($this->getEntityManager(), $this->getDispatcher(), $repo, $this->getIo());
        $notDelivered = $updater->getDelayedDeliveryTransactions(0);

        $transaction = reset($notDelivered);

        $this->assertEquals($sentVerification->getTransactionId(), $transaction['transaction_id']);
        $this->assertEquals($sentVerification->getSentAt()->format('c'), $transaction['sent_at']);
    }

    public function testGetSmsStatus()
    {
        $id = '1234';
        $date = new \DateTime();

        /** @var SmsStatusInterface|MockObject $status */
        $status = $this->createMock('LoginCidadao\PhoneVerificationBundle\Model\SmsStatusInterface');
        $status->expects($this->once())->method('getDateSent')->willReturn($date);
        $status->expects($this->once())->method('getDateDelivered')->willReturn($date);

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->atMost(1))
            ->method('dispatch')
            ->willReturnCallback(
                function ($eventName, UpdateStatusEvent $event) use ($status) {
                    $event->setDeliveryStatus($status);
                }
            );

        $updater = $this->getSmsStatusService($this->getEntityManager(), $dispatcher, $this->getRepository(),
            $this->getIo());
        $response = $updater->getSmsStatus($id);

        $this->assertSame($status, $response);
        $this->assertSame($date, $response->getDateSent());
        $this->assertSame($date, $response->getDateDelivered());
    }

    private function getSmsStatusService(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        SentVerificationRepository $repo,
        SymfonyStyle $io = null
    ) {
        $updater = new SmsStatusService($em, $dispatcher, $repo);
        if ($io !== null) {
            $updater->setSymfonyStyle($io);
        }

        return $updater;
    }

    /**
     * @return MockObject|EventDispatcherInterface
     */
    private function getDispatcher()
    {
        $dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        return $dispatcher;
    }

    private function getQuery($items = [])
    {
        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->atMost(1))->method('iterate')
            ->willReturn(new \ArrayIterator($items));

        return $query;
    }

    /**
     * @param null|array $items
     * @return MockObject|SentVerificationRepository
     */
    private function getRepository($items = null)
    {
        $repo = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository')
            ->disableOriginalConstructor()
            ->getMock();

        if ($items !== null) {
            $repo->expects($this->atMost(1))
                ->method('getPendingUpdateSentVerificationQuery')
                ->willReturn($this->getQuery($items));

            $repo->expects($this->atMost(1))
                ->method('countPendingUpdateSentVerification')
                ->willReturn(count($items));
        }

        return $repo;
    }

    /**
     * @return MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');

        return $em;
    }

    /**
     * @return MockObject|SymfonyStyle
     */
    private function getIo()
    {
        $io = $this->getMockBuilder('Symfony\Component\Console\Style\SymfonyStyle')
            ->disableOriginalConstructor()
            ->getMock();

        return $io;
    }
}
