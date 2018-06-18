<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Event\UpdateStatusEvent;
use LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Model\SmsStatusInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SmsStatusService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var SentVerificationRepository */
    private $sentVerificationRepo;

    /** @var SymfonyStyle */
    private $io;

    /**
     * SmsStatusUpdater constructor.
     * @param EntityManagerInterface $em
     * @param EventDispatcherInterface $dispatcher
     * @param SentVerificationRepository $sentVerificationRepo
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        SentVerificationRepository $sentVerificationRepo
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->sentVerificationRepo = $sentVerificationRepo;
    }

    /**
     * @param SymfonyStyle $io
     * @return SmsStatusService
     */
    public function setSymfonyStyle(SymfonyStyle $io)
    {
        $this->io = $io;

        return $this;
    }

    /**
     * @param $transactionId
     * @return SmsStatusInterface
     */
    public function getSmsStatus($transactionId)
    {
        $event = $this->getStatus($transactionId);

        return $event->getDeliveryStatus();
    }

    /**
     * @param int $batchSize
     * @return array
     */
    public function updateSentVerificationStatus($batchSize = 1)
    {
        $count = $this->sentVerificationRepo->countPendingUpdateSentVerification();

        if ($count === 0) {
            $this->comment('No messages pending update.');

            return [];
        }

        $this->em->getConnection() ? $this->em->getConnection()->getConfiguration()->setSQLLogger(null) : null;
        gc_enable();
        $this->progressStart($count);
        $transactionsUpdated = [];
        foreach ($this->iterateSentVerifications() as $row) {
            $transactionsUpdated[] = $this->handleSentVerificationStatusUpdate($row[0]);
            array_filter($transactionsUpdated);

            if ((count($transactionsUpdated) % $batchSize) === 0) {
                $this->flushAndClear();
                gc_collect_cycles();
            }
            $this->progressAdvance(1);
        }

        $this->flushAndClear();
        $this->progressFinish();

        $countUpdated = count($transactionsUpdated);
        $this->comment("Updated {$countUpdated} transactions.");

        if ($countUpdated === 0) {
            $this->comment("It's possible the SMS-sending service you are using doesn't implement status updates.");
        }

        return $transactionsUpdated;
    }

    /**
     * @param SentVerificationInterface $sentVerification
     * @return SentVerificationInterface|null
     */
    private function handleSentVerificationStatusUpdate(SentVerificationInterface $sentVerification)
    {
        try {
            $event = $this->getStatus($sentVerification->getTransactionId());
            if (false === $event->isUpdated()) {
                $this->progressAdvance(1);
                unset($event, $sentVerification);
                gc_collect_cycles();

                return null;
            }

            return $this->updateSentVerification($sentVerification, $event);
        } finally {
            unset($event, $sentVerification);
        }
    }

    /**
     * @param $amount
     * @return float average delivery time in seconds (abs value)
     */
    public function getAverageDeliveryTime($amount)
    {
        /** @var SentVerificationInterface[] $sentVerifications */
        $sentVerifications = $this->sentVerificationRepo->getLastDeliveredVerifications($amount);

        if (count($sentVerifications) === 0) {
            return 0;
        }

        $times = [];
        foreach ($sentVerifications as $sentVerification) {
            $times[] = abs(
                $sentVerification->getDeliveredAt()->format('U') - $sentVerification->getSentAt()->format('U')
            );
        }
        $sum = array_sum($times);

        $avg = $sum / count($times);

        return $avg;
    }

    /**
     * @param int $maxDeliverySeconds
     * @return array
     */
    public function getDelayedDeliveryTransactions($maxDeliverySeconds = 0)
    {
        $date = new \DateTime("-{$maxDeliverySeconds} seconds");
        $notDelivered = $this->sentVerificationRepo->getNotDeliveredSince($date);

        $transactions = [];
        foreach ($notDelivered as $sentVerification) {
            $transactions[] = [
                'transaction_id' => $sentVerification->getTransactionId(),
                'sent_at' => $sentVerification->getSentAt()->format('c'),
            ];
        }

        return $transactions;
    }

    /**
     * @param $transactionId
     * @return UpdateStatusEvent
     */
    private function getStatus($transactionId)
    {
        $event = new UpdateStatusEvent($transactionId);
        $this->dispatcher->dispatch(PhoneVerificationEvents::PHONE_VERIFICATION_GET_SENT_VERIFICATION_STATUS, $event);

        return $event;
    }

    private function comment($message)
    {
        if (!$this->io) {
            return;
        }
        $this->io->comment($message);
    }

    private function progressStart($max = 0)
    {
        if (!$this->io) {
            return;
        }
        $this->io->progressStart($max);
    }

    private function progressAdvance($step = 1)
    {
        if (!$this->io) {
            return;
        }
        $this->io->progressAdvance($step);
    }

    private function progressFinish()
    {
        if (!$this->io) {
            return;
        }
        $this->io->progressFinish();
    }

    /**
     * @return IterableResult
     */
    private function iterateSentVerifications()
    {
        return $this->sentVerificationRepo->getPendingUpdateSentVerificationQuery()->iterate();
    }

    private function flushAndClear()
    {
        $this->em->flush();
        $this->em->clear();
    }

    private function updateSentVerification(SentVerificationInterface $sentVerification, UpdateStatusEvent $event)
    {
        $deliveredAt = $event->getDeliveredAt();
        $sentVerification->setActuallySentAt($event->getSentAt())
            ->setDeliveredAt($deliveredAt)
            ->setFinished($deliveredAt instanceof \DateTime || $event->getDeliveryStatus()->isFinal());

        return $sentVerification;
    }
}
