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

use libphonenumber\PhoneNumber;
use LoginCidadao\ValidationBundle\Validator\Constraints\MobilePhoneNumber;
use LoginCidadao\ValidationBundle\Validator\Constraints\MobilePhoneNumberValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MobilePhoneNumberValidatorTest extends TestCase
{
    public function testNotPhoneNumber()
    {
        $this->assertFalse(MobilePhoneNumberValidator::isMobile(new \DateTime()));
    }

    public function testNotMobile()
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode(55);
        $phone->setNationalNumber('5133333333');

        $this->assertFalse(MobilePhoneNumberValidator::isMobile($phone));
    }

    public function testMissing9thDigit()
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode(55);
        $phone->setNationalNumber('5199999999');

        $this->assertFalse(MobilePhoneNumberValidator::isMobile($phone));
    }

    public function testValidateSuccessWithString()
    {
        $validator = new MobilePhoneNumberValidator();
        $validator->initialize($this->getContext($this->never()));
        $validator->validate('+5551999999999', new MobilePhoneNumber());
    }

    public function testSkipWithInvalidString()
    {
        $validator = new MobilePhoneNumberValidator();
        $validator->initialize($this->getContext($this->never()));
        $validator->validate('+5', new MobilePhoneNumber());
    }

    public function testValidationFailure()
    {
        $phone = (new PhoneNumber())
            ->setCountryCode(55)
            ->setNationalNumber('5199999999');

        $validator = new MobilePhoneNumberValidator();
        $validator->initialize($this->getContext($this->once()));
        $validator->validate($phone, new MobilePhoneNumber());
    }

    public function testValidMobileNumber()
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode(55);
        $phone->setNationalNumber('51999999999');

        $this->assertTrue(MobilePhoneNumberValidator::isMobile($phone));
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
