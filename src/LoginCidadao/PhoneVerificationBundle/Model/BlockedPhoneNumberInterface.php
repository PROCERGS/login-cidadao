<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Model;

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;

interface BlockedPhoneNumberInterface
{
    /**
     * BlockedPhoneNumberInterface constructor.
     * @param PhoneNumber $phoneNumber
     * @param PersonInterface $blockedBy
     * @param \DateTime $createdAt
     */
    public function __construct(PhoneNumber $phoneNumber, PersonInterface $blockedBy, \DateTime $createdAt);

    public function getPhoneNumber(): PhoneNumber;

    /**
     * @return PersonInterface
     */
    public function getBlockedBy(): PersonInterface;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;
}
