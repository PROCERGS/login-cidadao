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

abstract class AbstractPhoneVerification implements PhoneVerificationInterface
{
    /**
     * @return bool
     */
    public function isVerified()
    {
        return $this->getVerifiedAt() instanceof \DateTime && $this->getVerifiedAt() <= new \DateTime();
    }
}
