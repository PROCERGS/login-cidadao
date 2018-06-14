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

use LoginCidadao\ValidationBundle\Validator\Constraints\CPF;
use LoginCidadao\ValidationBundle\Validator\Constraints\CPFValidator;
use PHPUnit\Framework\TestCase;

class CPFTest extends TestCase
{
    public function testValidatedBy()
    {
        $this->assertSame(CPFValidator::class, (new CPF())->validatedBy());
    }
}
