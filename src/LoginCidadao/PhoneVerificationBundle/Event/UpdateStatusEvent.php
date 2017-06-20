<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class UpdateStatusEvent extends Event
{
    /** @var string */
    private $transactionId;

    /** @var \DateTime */
    private $sentAt;

    /** @var \DateTime */
    private $deliveredAt;

    /** @var int */
    private $deliveryStatus;

    /** @var boolean */
    private $updated = false;

    /**
     * UpdateStatusEvent constructor.
     * @param string $transactionId
     */
    public function __construct($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * @param \DateTime $sentAt
     * @return UpdateStatusEvent
     */
    public function setSentAt(\DateTime $sentAt = null)
    {
        $this->sentAt = $sentAt;
        $this->setUpdated();

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeliveredAt()
    {
        return $this->deliveredAt;
    }

    /**
     * @param \DateTime $deliveredAt
     * @return UpdateStatusEvent
     */
    public function setDeliveredAt(\DateTime $deliveredAt = null)
    {
        $this->deliveredAt = $deliveredAt;
        $this->setUpdated();

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryStatus()
    {
        return $this->deliveryStatus;
    }

    /**
     * @param int $deliveryStatus
     * @return UpdateStatusEvent
     */
    public function setDeliveryStatus($deliveryStatus)
    {
        $this->deliveryStatus = $deliveryStatus;
        $this->setUpdated();

        return $this;
    }

    /**
     * Checks if any data was fetched.
     *
     * @return bool
     */
    public function isUpdated()
    {
        return $this->updated;
    }

    /**
     * @param bool $updated
     * @return UpdateStatusEvent
     */
    private function setUpdated($updated = true)
    {
        $this->updated = $updated;

        return $this;
    }
}
