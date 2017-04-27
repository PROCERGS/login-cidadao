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

interface PhoneVerificationInterface
{
    /**
     * Get the verification's id
     * @return mixed
     */
    public function getId();

    /**
     * Get the phone's owner
     * @return PersonInterface
     */
    public function getPerson();

    /**
     * @param PersonInterface $person
     * @return PhoneVerificationInterface
     */
    public function setPerson(PersonInterface $person);

    /**
     * Get the phone being verified
     * @return PhoneNumber
     */
    public function getPhone();

    /**
     * @param PhoneNumber $phone
     * @return PhoneVerificationInterface
     */
    public function setPhone(PhoneNumber $phone);

    /**
     * Get the date when the phone was validated
     * @return \DateTime
     */
    public function getVerifiedAt();

    /**
     * @param \DateTime $verifiedAt
     * @return PhoneVerificationInterface
     */
    public function setVerifiedAt($verifiedAt);

    /**
     * @return bool
     */
    public function isVerified();

    /**
     * Set verificationCode
     *
     * @param string $verificationCode
     * @return PhoneVerificationInterface
     */
    public function setVerificationCode($verificationCode);

    /**
     * Get verificationCode
     * @return string
     */
    public function getVerificationCode();

    /**
     * Set the Verification Token used to perform "one-click verification".
     *
     * @param string $verificationToken
     * @return PhoneVerificationInterface
     */
    public function setVerificationToken($verificationToken);

    /**
     * Get the Verification Token used to perform "one-click verification".
     *
     * @return string
     */
    public function getVerificationToken();

    /**
     * @return \DateTime
     */
    public function getCreatedAt();
}
