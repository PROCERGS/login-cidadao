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

class PhoneVerificationOptions
{
    /** @var bool */
    private $caseSensitive;

    /** @var bool */
    private $useLowerCase;

    /** @var bool */
    private $useUpperCase;

    /** @var bool */
    private $useNumbers;

    /** @var int */
    private $length;

    /** @var string */
    private $smsResendTimeout;

    /** @var string */
    private $verificationTokenLength;

    /**
     * PhoneVerificationOptions constructor.
     * @param int $length
     * @param bool $useNumbers
     * @param bool $caseSensitive
     * @param bool $useLowerCase
     * @param bool $useUpperCase
     * @param $smsResendTimeout
     * @param $verificationTokenLength
     */
    public function __construct(
        $length,
        $useNumbers,
        $caseSensitive,
        $useLowerCase,
        $useUpperCase,
        $smsResendTimeout,
        $verificationTokenLength
    ) {
        $this->length = $length;
        $this->useNumbers = $useNumbers;
        $this->caseSensitive = $caseSensitive;
        $this->useLowerCase = $useLowerCase;
        $this->useUpperCase = $useUpperCase;
        $this->smsResendTimeout = $smsResendTimeout;
        $this->verificationTokenLength = $verificationTokenLength;
    }

    /**
     * @return bool
     */
    public function isCaseSensitive()
    {
        return $this->caseSensitive;
    }

    /**
     * @return bool
     */
    public function isUseLowerCase()
    {
        return $this->useLowerCase;
    }

    /**
     * @return bool
     */
    public function isUseUpperCase()
    {
        return $this->useUpperCase;
    }

    /**
     * @return bool
     */
    public function isUseNumbers()
    {
        return $this->useNumbers;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    public function getSmsResendTimeout()
    {
        return $this->smsResendTimeout;
    }

    /**
     * @return mixed
     */
    public function getVerificationTokenLength()
    {
        return $this->verificationTokenLength;
    }
}
