<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Service;

use Doctrine\ORM\EntityManager;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Entity\SentVerification;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;

class VerificationSentService
{
    /** @var EntityManager */
    private $em;

    /** @var SentVerificationRepository */
    private $sentVerificationRepository;

    /**
     * VerificationSentService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->sentVerificationRepository = $this->em
            ->getRepository('PROCERGSLoginCidadaoPhoneVerificationBundle:SentVerification');
    }

    /**
     * @param PhoneVerificationInterface $phoneVerification
     * @return null|SentVerificationInterface
     */
    public function getLastVerificationSent(PhoneVerificationInterface $phoneVerification)
    {
        return $this->sentVerificationRepository->getLastVerificationSent($phoneVerification);
    }

    public function registerVerificationSent(
        PhoneVerificationInterface $phoneVerification,
        $transactionId,
        $message = null
    ) {
        $sentVerification = new SentVerification();
        $sentVerification
            ->setPhoneVerification($phoneVerification)
            ->setTransactionId($transactionId)
            ->setMessageSent($message)
            ->setSentAt(new \DateTime());

        $this->em->persist($sentVerification);
        $this->em->flush($sentVerification);
    }
}
