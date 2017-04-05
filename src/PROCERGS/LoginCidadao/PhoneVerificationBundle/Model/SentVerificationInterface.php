<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Model;

use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;

interface SentVerificationInterface
{
    /**
     * @return PhoneVerificationInterface
     */
    public function getPhoneVerification();

    /**
     * @param PhoneVerificationInterface $phoneVerification
     * @return SentVerificationInterface
     */
    public function setPhoneVerification(PhoneVerificationInterface $phoneVerification);

    /**
     * @return \DateTime
     */
    public function getSentAt();

    /**
     * @param \DateTime $sentAt
     * @return SentVerificationInterface
     */
    public function setSentAt(\DateTime $sentAt);

    /**
     * @return string
     */
    public function getMessageSent();

    /**
     * @param string $message
     * @return SentVerificationInterface
     */
    public function setMessageSent($message);

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * @param string $transactionId
     * @return SentVerificationInterface
     */
    public function setTransactionId($transactionId);
}
