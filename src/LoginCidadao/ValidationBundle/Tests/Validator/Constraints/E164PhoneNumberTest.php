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

use LoginCidadao\ValidationBundle\Validator\Constraints\E164PhoneNumber;
use LoginCidadao\ValidationBundle\Validator\Constraints\E164PhoneNumberValidator;
use PHPUnit\Framework\TestCase;

class E164PhoneNumberTest extends TestCase
{
    public function testValidatedBy()
    {
        $this->assertSame(E164PhoneNumberValidator::class, (new E164PhoneNumber())->validatedBy());
    }
}
