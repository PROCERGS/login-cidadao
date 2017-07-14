<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;

/**
 * Class SentVerification
 *
 * @ORM\Table(
 *     name="sent_verification",
 *     indexes={@ORM\Index(name="idx_phone", columns={"phone"})}
 * )
 * @ORM\Entity(repositoryClass="LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository")
 */
class SentVerification implements SentVerificationInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=false)
     */
    private $phone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable=false)
     */
    private $sentAt;

    /**
     * @var string
     *
     * @ORM\Column(name="message_sent", type="string", length=255, nullable=true)
     */
    private $messageSent;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", length=255, nullable=false)
     */
    private $transactionId;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return PhoneNumber
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param PhoneNumber $phone
     * @return SentVerificationInterface
     */
    public function setPhone(PhoneNumber $phone)
    {
        $this->phone = $phone;

        return $this;
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
     * @return SentVerificationInterface
     */
    public function setSentAt(\DateTime $sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageSent()
    {
        return $this->messageSent;
    }

    /**
     * @param string $message
     * @return SentVerificationInterface
     */
    public function setMessageSent($message)
    {
        $this->messageSent = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     * @return SentVerification
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }
}
