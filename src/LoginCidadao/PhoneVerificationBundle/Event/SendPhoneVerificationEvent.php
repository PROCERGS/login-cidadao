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

use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;
use Symfony\Component\EventDispatcher\Event;

class SendPhoneVerificationEvent extends Event
{
    /** @var PhoneVerificationInterface */
    private $phoneVerification;

    /** @var SentVerificationInterface */
    private $sentVerification;

    /**
     * SendPhoneVerificationEvent constructor.
     * @param PhoneVerificationInterface $phoneVerification
     */
    public function __construct(PhoneVerificationInterface $phoneVerification)
    {
        $this->phoneVerification = $phoneVerification;
    }

    /**
     * @return PhoneVerificationInterface
     */
    public function getPhoneVerification()
    {
        return $this->phoneVerification;
    }

    /**
     * @return SentVerificationInterface
     */
    public function getSentVerification()
    {
        return $this->sentVerification;
    }

    /**
     * @param SentVerificationInterface $sentVerification
     */
    public function setSentVerification($sentVerification)
    {
        $this->sentVerification = $sentVerification;
    }
}
