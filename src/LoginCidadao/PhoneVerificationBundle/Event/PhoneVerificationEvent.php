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
use Symfony\Component\EventDispatcher\Event;

class PhoneVerificationEvent extends Event
{
    /** @var PhoneVerificationInterface */
    private $phoneVerification;

    /** @var string|int */
    private $providedCode;

    /**
     * PhoneVerificationEvent constructor.
     * @param PhoneVerificationInterface $phoneVerification
     * @param int|string $providedCode
     */
    public function __construct(PhoneVerificationInterface $phoneVerification, $providedCode)
    {
        $this->phoneVerification = $phoneVerification;
        $this->providedCode = $providedCode;
    }

    /**
     * @return PhoneVerificationInterface
     */
    public function getPhoneVerification()
    {
        return $this->phoneVerification;
    }

    /**
     * @return int|string
     */
    public function getProvidedCode()
    {
        return $this->providedCode;
    }
}
