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

use LoginCidadao\PhoneVerificationBundle\Entity\SentVerification;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use Symfony\Component\EventDispatcher\Event;

class SendPhoneVerificationEvent extends Event
{
    /** @var PhoneVerificationInterface */
    private $phoneVerification;

    /** @var SentVerification */
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
     * @return SentVerification
     */
    public function getSentVerification()
    {
        return $this->sentVerification;
    }

    /**
     * @param SentVerification $sentVerification
     */
    public function setSentVerification($sentVerification)
    {
        $this->sentVerification = $sentVerification;
    }
}
