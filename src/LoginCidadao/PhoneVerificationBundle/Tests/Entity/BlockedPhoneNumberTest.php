<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Entity;

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\PhoneVerificationBundle\Entity\BlockedPhoneNumber;
use PHPUnit\Framework\TestCase;

class BlockedPhoneNumberTest extends TestCase
{
    public function testEntity()
    {
        $phone = new PhoneNumber();
        $blockedBy = new Person();
        $date = new \DateTime();

        $blocked = new BlockedPhoneNumber($phone, $blockedBy, $date);

        $this->assertNull($blocked->getId());
        $this->assertSame($phone, $blocked->getPhoneNumber());
        $this->assertSame($blockedBy, $blocked->getBlockedBy());
        $this->assertSame($date, $blocked->getCreatedAt());
    }
}
