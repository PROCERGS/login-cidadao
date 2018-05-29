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

use LoginCidadao\PhoneVerificationBundle\Model\SmsStatusInterface;
use Symfony\Component\EventDispatcher\Event;

class UpdateStatusEvent extends Event
{
    /** @var string */
    private $transactionId;

    /** @var SmsStatusInterface */
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
        if (null !== $this->getDeliveryStatus()) {
            return $this->getDeliveryStatus()->getDateSent();
        } else {
            return null;
        }
    }

    /**
     * @return \DateTime
     */
    public function getDeliveredAt()
    {
        if (null !== $this->getDeliveryStatus()) {
            return $this->getDeliveryStatus()->getDateDelivered();
        } else {
            return null;
        }
    }

    /**
     * @return SmsStatusInterface
     */
    public function getDeliveryStatus()
    {
        return $this->deliveryStatus;
    }

    /**
     * @param SmsStatusInterface $deliveryStatus
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
