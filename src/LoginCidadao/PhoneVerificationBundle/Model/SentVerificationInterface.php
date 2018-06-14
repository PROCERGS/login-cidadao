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

interface SentVerificationInterface
{

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return PhoneNumber
     */
    public function getPhone();

    /**
     * @param PhoneNumber $phone
     * @return SentVerificationInterface
     */
    public function setPhone(PhoneNumber $phone);

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

    /**
     * Get the date the message was actually sent to the user's phone
     *
     * @return \DateTime
     */
    public function getActuallySentAt();

    /**
     * Set the date the message was actually sent to the user's phone
     *
     * @param \DateTime $sentAt
     * @return SentVerificationInterface
     */
    public function setActuallySentAt(\DateTime $sentAt = null);

    /**
     * @return \DateTime
     */
    public function getDeliveredAt();

    /**
     * @param \DateTime $deliveredAt
     * @return SentVerificationInterface
     */
    public function setDeliveredAt(\DateTime $deliveredAt = null);

    /**
     * Determines whether or not a SentVerification status reached it's final state.
     * @return bool
     */
    public function isFinished();

    /**
     * @param bool $finished
     * @return SentVerificationInterface
     */
    public function setFinished($finished = true);
}
