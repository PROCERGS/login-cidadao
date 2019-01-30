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

class BlocklistOptions
{
    /** @var int */
    private $autoBlockPhoneLimit;

    /**
     * BlocklistOptions constructor.
     * @param int $autoBlockPhoneLimit
     */
    public function __construct(int $autoBlockPhoneLimit)
    {
        $this->autoBlockPhoneLimit = $autoBlockPhoneLimit;
    }

    public function isAutoBlockEnabled(): bool
    {
        $limit = $this->getAutoBlockPhoneLimit();

        return null !== $limit && $limit > 0;
    }

    /**
     * @return int
     */
    public function getAutoBlockPhoneLimit(): int
    {
        return $this->autoBlockPhoneLimit;
    }
}
