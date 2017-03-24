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

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;

abstract class AbstractPhoneVerificationService
{
    /**
     * Gets phone verification record (PhoneVerificationInterface) for the given phone number.
     *
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public abstract function getPhoneVerification(PersonInterface $person, $phone);

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public abstract function createPhoneVerification(PersonInterface $person, $phone);

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface|null
     */
    public abstract function getPendingPhoneVerification(PersonInterface $person, $phone);

    /**
     * @param PhoneVerificationInterface $phoneVerification
     * @return bool
     */
    public abstract function removePhoneVerification(PhoneVerificationInterface $phoneVerification);

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function enforcePhoneVerification(PersonInterface $person, $phone)
    {
        $phoneVerification = $this->getPhoneVerification($person, $phone);

        return $phoneVerification ?: $this->createPhoneVerification($person, $phone);
    }
}
