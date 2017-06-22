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
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Event\UpdateStatusEvent;
use LoginCidadao\PhoneVerificationBundle\Model\DeliveryStatus;
use LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SmsStatusService
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var SentVerificationRepository */
    private $sentVerificationRepo;

    /** @var SymfonyStyle */
    private $io;

    /**
     * SmsStatusUpdater constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param SentVerificationRepository $sentVerificationRepo
     * @param SymfonyStyle|null $io
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        SentVerificationRepository $sentVerificationRepo,
        SymfonyStyle $io = null
    ) {
        $this->io = $io;
        $this->dispatcher = $dispatcher;
        $this->sentVerificationRepo = $sentVerificationRepo;
    }

    public function updateSentVerificationStatus(EntityManagerInterface $em)
    {
        $count = $this->sentVerificationRepo->countPendingUpdateSentVerification();

        if ($count === 0) {
            $this->comment('No messages pending update.');

            return [];
        }

        $query = $this->sentVerificationRepo->getPendingUpdateSentVerificationQuery();
        $sentVerifications = $query->iterate();

        $this->progressStart($count);
        $transactionsUpdated = [];
        foreach ($sentVerifications as $row) {
            /** @var SentVerificationInterface $sentVerification */
            $sentVerification = $row[0];
            $status = $this->getStatus($sentVerification->getTransactionId());

            if (false === $status->isUpdated()) {
                $this->progressAdvance(1);
                continue;
            }

            $deliveredAt = $status->getDeliveredAt();
            $sentVerification->setActuallySentAt($status->getSentAt())
                ->setDeliveredAt($deliveredAt)
                ->setFinished(
                    $deliveredAt instanceof \DateTime || DeliveryStatus::isFinal($status->getDeliveryStatus())
                );
            $em->flush();
            $em->clear();
            $transactionsUpdated[] = $sentVerification->getTransactionId();
            $this->progressAdvance(1);
        }
        $this->progressFinish();

        $countUpdated = count($transactionsUpdated);
        $this->comment("Updated {$countUpdated} transactions.");

        if ($countUpdated === 0) {
            $this->comment("It's possible the SMS-sending service you are using doesn't implement status updates.");
        }

        return $transactionsUpdated;
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
}
