<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;
use Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType;

/**
 * @codeCoverageIgnore
 */
class SentVerificationRepository extends EntityRepository
{
    public function getLastVerificationSent(PhoneVerificationInterface $phoneVerification)
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.phone = :phone')
            ->orderBy('s.sentAt', 'DESC')
            ->setParameter('phone', $phoneVerification->getPhone(), PhoneNumberType::NAME);

        // Filter only SentVerification that belong to this PhoneVerification
        if ($phoneVerification->isVerified()) {
            $query
                ->andWhere('s.sentAt BETWEEN :created AND :verified')
                ->setParameter('created', $phoneVerification->getCreatedAt())
                ->setParameter('verified', $phoneVerification->getVerifiedAt());
        } else {
            $query
                ->andWhere('s.sentAt >= :created')
                ->setParameter('created', $phoneVerification->getCreatedAt());
        }

        return $query->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getPendingUpdateSentVerificationQuery()
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.finished IS NULL OR s.finished != :finished')
            ->setParameter('finished', true, \PDO::PARAM_BOOL)
            ->orderBy('s.sentAt', 'DESC')
            ->getQuery();

        return $query;
    }

    public function countPendingUpdateSentVerification()
    {
        $count = $this->createQueryBuilder('s')
            ->select('COUNT(s)')
            ->where('s.finished IS NULL OR s.finished != :finished')
            ->setParameter('finished', true, \PDO::PARAM_BOOL)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * @param int $limit
     * @return array|SentVerificationInterface[]
     */
    public function getLastDeliveredVerifications($limit = 10)
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.deliveredAt IS NOT NULL')
            ->orderBy('s.deliveredAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param \DateTime $date
     * @return SentVerification[]
     */
    public function getNotDeliveredSince(\DateTime $date)
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.deliveredAt IS NULL')
            ->andWhere('s.sentAt <= :date')
            ->andWhere('s.finished IS NULL OR s.finished != :finished')
            ->orderBy('s.sentAt', 'ASC')
            ->setParameter('date', $date)
            ->setParameter('finished', true, \PDO::PARAM_BOOL)
            ->getQuery();

        return $query->getResult();
    }
}
