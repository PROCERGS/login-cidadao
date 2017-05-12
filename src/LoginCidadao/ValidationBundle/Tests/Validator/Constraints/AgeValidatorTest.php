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

use LoginCidadao\ValidationBundle\Validator\Constraints\Age;
use LoginCidadao\ValidationBundle\Validator\Constraints\AgeValidator;

class AgeValidatorTest extends \PHPUnit_Framework_TestCase
{
    const MIN_AGE = 18;
    const MAX_AGE = 150;

    public function testFutureDate()
    {
        $context = $this->getContext($this->getConstraint()->futureError);

        $this->validate($context, new \DateTime('+1 day'));
    }

    public function testMinAge()
    {
        $context = $this->getContext($this->getConstraint()->minError);

        $age = (self::MIN_AGE * 12) - 1;
        $this->validate($context, new \DateTime("-{$age} months"));
    }

    public function testMaxAge()
    {
        $context = $this->getContext($this->getConstraint()->maxError);

        $age = (self::MAX_AGE * 12) + 1;
        $this->validate($context, new \DateTime("-{$age} months"));
    }

    private function getContext($expectedMessage)
    {
        $builder = $this->getMockBuilder('Symfony\Component\Validator\Violation\ConstraintViolationBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['addViolation'])
            ->getMock();

        $context = $this->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->setMethods(['buildViolation'])
            ->getMock();
        $context->expects($this->once())
            ->method('buildViolation')
            ->with($this->equalTo($expectedMessage))
            ->willReturn($builder);

        return $context;
    }

    private function getConstraint()
    {
        $constraint = new Age();
        $constraint->min = self::MIN_AGE;
        $constraint->max = self::MAX_AGE;

        return $constraint;
    }

    private function validate($context, $date)
    {
        $validator = new AgeValidator();
        $validator->initialize($context);
        $validator->validate($date, $this->getConstraint());
    }
}
