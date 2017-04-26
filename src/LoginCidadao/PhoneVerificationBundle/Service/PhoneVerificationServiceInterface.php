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
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerification;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;

interface PhoneVerificationServiceInterface
{
    /**
     * Gets phone verification record (PhoneVerificationInterface) for the given phone number.
     *
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function getPhoneVerification(PersonInterface $person, PhoneNumber $phone);

    /**
     * @param PersonInterface $person
     * @param mixed $id
     * @return PhoneVerificationInterface
     */
    public function getPhoneVerificationById(PersonInterface $person, $id);

    /**
     * @param PersonInterface $person
     * @param mixed $id
     * @return PhoneVerificationInterface
     */
    public function getPendingPhoneVerificationById(PersonInterface $person, $id);

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function createPhoneVerification(PersonInterface $person, PhoneNumber $phone);

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface|null
     */
    public function getPendingPhoneVerification(PersonInterface $person, PhoneNumber $phone);

    /**
     * @param PersonInterface $person
     * @return PhoneVerificationInterface[]
     */
    public function getAllPendingPhoneVerification(PersonInterface $person);

    /**
     * @param PhoneVerificationInterface $phoneVerification
     * @return bool
     */
    public function removePhoneVerification(PhoneVerificationInterface $phoneVerification);

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function enforcePhoneVerification(PersonInterface $person, PhoneNumber $phone);

    /**
     * Verifies code without dispatching any event or making any changes.
     *
     * @param $provided
     * @param $expected
     * @return bool
     */
    public function checkVerificationCode($provided, $expected);

    /**
     * Verifies a phone and dispatches event.
     *
     * @param PhoneVerificationInterface $phoneVerification
     * @param $providedCode
     * @return bool
     */
    public function verify(PhoneVerificationInterface $phoneVerification, $providedCode);

    /**
     * @param PhoneVerificationInterface $phoneVerification
     */
    public function resendVerificationCode(PhoneVerificationInterface $phoneVerification);

    /**
     * @param SentVerificationInterface $sentVerification
     * @return SentVerification
     */
    public function registerVerificationSent(SentVerificationInterface $sentVerification);
}
