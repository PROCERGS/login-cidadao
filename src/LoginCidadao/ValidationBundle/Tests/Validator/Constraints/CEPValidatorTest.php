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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CEPValidatorTest extends TestCase
{
    public function testValid()
    {
        $validValues = [
            '00000-000',
            '00000000',
        ];

        foreach ($validValues as $value) {
            $this->assertTrue(CEPValidator::isCEPValid($value), "Invalid value: {$value}");
            $this->assertTrue(CEPValidator::checkLength($value), "Invalid value: {$value}");
        }

        $context = $this->getContext($this->never());
        $constraint = new CEP();

        $validator = new CEPValidator();
        $validator->initialize($context);
        $validator->validate(null, $constraint);
        $validator->validate('00000000', $constraint);
    }

    public function testInvalid()
    {
        $this->assertFalse(CEPValidator::isCEPValid('not_postal_code'));
        $this->assertFalse(CEPValidator::checkLength('123456789'));

        $context = $this->getContext($this->once());
        $constraint = new CEP();

        $validator = new CEPValidator();
        $validator->initialize($context);
        $validator->validate('000000000', $constraint);
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
