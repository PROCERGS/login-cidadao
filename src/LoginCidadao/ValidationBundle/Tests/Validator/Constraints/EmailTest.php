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

use LoginCidadao\ValidationBundle\Validator\Constraints\Email;
use LoginCidadao\ValidationBundle\Validator\Constraints\EmailValidator;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testValidatedBy()
    {
        $email = new Email();
        $this->assertSame(EmailValidator::class, $email->validatedBy());
    }
}
