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

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Model\BlockedPhoneNumberInterface;

interface BlocklistInterface
{
    public function isPhoneBlocked(PhoneNumber $phoneNumber): bool;

    /**
     * @param PhoneNumber $phoneNumber
     * @return PersonInterface[]
     */
    public function blockByPhone(PhoneNumber $phoneNumber): array;

    /**
     * Checks if the phone is blocked. If it is, all relevant accounts will be blocked.
     *
     * @param PhoneNumber $phoneNumber
     * @return PersonInterface[]
     */
    public function checkPhoneNumber(PhoneNumber $phoneNumber): array;

    /**
     * @param PhoneNumber $phoneNumber
     * @param PersonInterface $blockedBy
     * @return BlockedPhoneNumberInterface
     */
    public function addBlockedPhoneNumber(
        PhoneNumber $phoneNumber,
        PersonInterface $blockedBy
    ): BlockedPhoneNumberInterface;

    /**
     * @param PhoneNumber $phoneNumber
     * @return BlockedPhoneNumberInterface|null
     */
    public function getBlockedPhoneNumberByPhone(PhoneNumber $phoneNumber): ?BlockedPhoneNumberInterface;
}
