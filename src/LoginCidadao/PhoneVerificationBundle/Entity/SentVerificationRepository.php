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
}
