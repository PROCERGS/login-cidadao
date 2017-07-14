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
use LoginCidadao\PhoneVerificationBundle\Exception\VerificationNotSentException;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

interface PhoneVerificationServiceInterface
{
    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function createPhoneVerification(PersonInterface $person, PhoneNumber $phone);

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function enforcePhoneVerification(PersonInterface $person, PhoneNumber $phone);

    /**
     * Gets phone verification record (PhoneVerificationInterface) for the given phone number.
     *
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function getPhoneVerification(PersonInterface $person, PhoneNumber $phone);

    /**
     * @param mixed $id
     * @param PersonInterface $person
     * @return PhoneVerificationInterface
     */
    public function getPhoneVerificationById($id, PersonInterface $person = null);

    /**
     * @param PersonInterface $person
     * @param mixed $id
     * @return PhoneVerificationInterface
     */
    public function getPendingPhoneVerificationById($id, PersonInterface $person = null);

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
     * Sends the verification code
     *
     * @param PhoneVerificationInterface $phoneVerification
     * @return SentVerificationInterface
     * @throws VerificationNotSentException
     */
    public function sendVerificationCode(PhoneVerificationInterface $phoneVerification);

    /**
     * @param SentVerificationInterface $sentVerification
     * @return SentVerification
     */
    public function registerVerificationSent(SentVerificationInterface $sentVerification);

    /**
     * @param PhoneVerificationInterface $phoneVerification
     * @return SentVerificationInterface
     */
    public function getLastSentVerification(PhoneVerificationInterface $phoneVerification);

    /**
     * Returns the date when a new verification code request will be possible.
     *
     * This is essentially the last message sent plus the resend timeout.
     *
     * @param PhoneVerificationInterface $phoneVerification
     * @return \DateTime
     */
    public function getNextResendDate(PhoneVerificationInterface $phoneVerification);

    /**
     * Verifies the phone number using the id and the verification token.
     *
     * This will call <code>verify()</code>
     *
     * @param PhoneVerificationInterface $phoneVerification
     * @param string $providedToken
     * @return bool
     */
    public function verifyToken(PhoneVerificationInterface $phoneVerification, $providedToken);
}
