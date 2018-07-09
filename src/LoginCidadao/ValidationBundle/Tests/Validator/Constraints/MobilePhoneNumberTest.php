<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\ValidationBundle\Tests\Validator\Constraints;

use LoginCidadao\ValidationBundle\Validator\Constraints\MobilePhoneNumber;
use LoginCidadao\ValidationBundle\Validator\Constraints\MobilePhoneNumberValidator;
use PHPUnit\Framework\TestCase;

class MobilePhoneNumberTest extends TestCase
{
    public function testValidatedBy()
    {
        $this->assertSame(MobilePhoneNumberValidator::class, (new MobilePhoneNumber())->validatedBy());
    }
}
