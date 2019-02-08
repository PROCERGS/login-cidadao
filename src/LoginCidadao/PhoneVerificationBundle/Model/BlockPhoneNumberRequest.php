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
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints as LCAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

class BlockPhoneNumberRequest
{
    /**
     * @var PhoneNumber
     * @Assert\NotBlank()
     * @LCAssert\E164PhoneNumber(maxMessage="person.validation.mobile.length.max")
     * @AssertPhoneNumber()
     */
    public $phoneNumber;

    /**
     * @var PersonInterface
     * @Assert\NotBlank()
     */
    private $blockedBy;

    /**
     * BlockPhoneNumberRequest constructor.
     * @param PersonInterface $blockedBy
     */
    public function __construct(PersonInterface $blockedBy)
    {
        $this->blockedBy = $blockedBy;
    }

    /**
     * @return PersonInterface
     */
    public function getBlockedBy(): PersonInterface
    {
        return $this->blockedBy;
    }
}
