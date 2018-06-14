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

use LoginCidadao\ValidationBundle\Validator\Constraints\CEP;
use LoginCidadao\ValidationBundle\Validator\Constraints\CEPValidator;
use PHPUnit\Framework\TestCase;

class CEPTest extends TestCase
{
    public function testValidatedBy()
    {
        $this->assertSame(CEPValidator::class, (new CEP())->validatedBy());
    }
}
