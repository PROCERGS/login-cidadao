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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class E164PhoneNumberValidatorTest extends TestCase
{
    public function testValidationSuccess()
    {
        $validator = new E164PhoneNumberValidator();
        $validator->initialize($this->getContext($this->never()));
        $validator->validate('+1-541-754-3010', new E164PhoneNumber());
        $validator->validate('+55 51 3333-3333', new E164PhoneNumber());
    }

    public function testValidationError()
    {
        $validator = new E164PhoneNumberValidator();
        $validator->initialize($this->getContext($this->once()));
        $validator->validate('0123456789012345', new E164PhoneNumber());
    }

    /**
     * @return ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContext($violations)
    {
        $context = $this->createMock('Symfony\Component\Validator\Context\ExecutionContextInterface');
        $context->expects($violations)->method('addViolation');

        return $context;
    }
}
