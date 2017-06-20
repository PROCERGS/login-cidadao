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

class SmsStatusUpdater
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var SentVerificationRepository */
    private $sentVerificationRepo;

    /**
     * SmsStatusUpdater constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param SentVerificationRepository $sentVerificationRepo
     */
    public function __construct(EventDispatcherInterface $dispatcher, SentVerificationRepository $sentVerificationRepo)
    {
        $this->dispatcher = $dispatcher;
        $this->sentVerificationRepo = $sentVerificationRepo;
    }

    public function updateSentVerificationStatus(SymfonyStyle $io, EntityManagerInterface $em = null)
    {
        $count = $this->sentVerificationRepo->countPendingUpdateSentVerification();

        if ($count === 0) {
            $io->comment('No messages pending update.');

            return [];
        }

        $query = $this->sentVerificationRepo->getPendingUpdateSentVerificationQuery();
        $sentVerifications = $query->iterate();

        $io->progressStart($count);
        $transactionsUpdated = [];
        foreach ($sentVerifications as $row) {
            /** @var SentVerificationInterface $sentVerification */
            $sentVerification = $row[0];
            $status = $this->getStatus($sentVerification->getTransactionId());

            if (false === $status->isUpdated()) {
                $io->progressAdvance(1);
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
            $io->progressAdvance(1);
        }
        $io->progressFinish();

        $countUpdated = count($transactionsUpdated);
        $io->comment("Updated {$countUpdated} transactions.");

        if ($countUpdated === 0) {
            $io->comment("It's possible the SMS-sending service you are using doesn't implement status updates.");
        }

        return $transactionsUpdated;
    }

    /**
     * @param $amount
     * @return float average delivery time in minutes (abs value)
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
            $times[] = $sentVerification->getDeliveredAt()->format('U') - $sentVerification->getSentAt()->format('U');
        }
        $sum = array_sum($times);

        $avg = $sum / count($times);

        return abs($avg / 60);
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
}
