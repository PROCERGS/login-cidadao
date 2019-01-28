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
use libphonenumber\PhoneNumber;
use LoginCidadao\PhoneVerificationBundle\Model\BlockedPhoneNumberInterface;

/**
 * Class BlockedPhoneNumberRepository
 * @package LoginCidadao\PhoneVerificationBundle\Entity
 *
 * @codeCoverageIgnore
 */
class BlockedPhoneNumberRepository extends EntityRepository
{
    /**
     * @param PhoneNumber $phoneNumber
     * @return BlockedPhoneNumberInterface
     */
    public function findByPhone(PhoneNumber $phoneNumber): ?BlockedPhoneNumberInterface
    {
        /** @var BlockedPhoneNumberInterface $blockedPhone */
        $blockedPhone = $this->findOneBy(['phoneNumber' => $phoneNumber]);

        return $blockedPhone;
    }

    /**
     * @param string $search
     * @return BlockedPhoneNumberInterface[]
     */
    public function searchBlocksByPartialPhone(string $search): array
    {

        return $this->getSearchByPartialPhoneQuery($search)
            ->getQuery()
            ->getResult();
    }

    public function getSearchByPartialPhoneQuery(string $search)
    {
        if ($search[0] === '+') {
            $search = "{$search}%";
        } else {
            $search = "%{$search}%";
        }

        return $this->createQueryBuilder('b')
            ->where('b.phoneNumber LIKE :search')
            ->setParameter('search', $search);
    }
}
