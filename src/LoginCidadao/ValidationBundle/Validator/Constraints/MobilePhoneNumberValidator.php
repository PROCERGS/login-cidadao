<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\ValidationBundle\Validator\Constraints;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class MobilePhoneNumberValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($constraint instanceof MobilePhoneNumber)) {
            return;
        }

        $phoneNumber = false;
        if ($value instanceof PhoneNumber) {
            $number = $this->phoneNumberToString($value);
            $phoneNumber = $value;
        } else {
            // Remove formatting
            $number = preg_replace('/[^0-9+]/', '', $value);
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        if (!$phoneNumber) {
            try {
                $phoneNumber = $phoneUtil->parse($number, null);
            } catch (\Exception $e) {
                // This failure will be detected by another Validator
                return;
            }
        }

        // Check length
        if (false === self::isMobile($phoneNumber)) {
            $this->context->addViolation($constraint->missing9thDigit);
        }
    }

    /**
     * This checks if the given PhoneNumber instance is a Mobile phone.
     * Additionally, it checks for the 9th digit in case the number is from Brazil (+55)
     *
     * @param $phone
     * @return bool
     */
    public static function isMobile($phone)
    {
        if (!($phone instanceof PhoneNumber)) {
            return false;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        $allowedTypes = [PhoneNumberType::MOBILE, PhoneNumberType::FIXED_LINE_OR_MOBILE];
        if (false === array_search($phoneUtil->getNumberType($phone), $allowedTypes)) {
            return false;
        }

        // Brazilian mobile phone without 9th digit
        if ($phone->getCountryCode() == '55' && strlen($phone->getNationalNumber()) !== 11) {
            return false;
        }

        return true;
    }

    private function phoneNumberToString($phone)
    {
        if ($phone instanceof PhoneNumber) {
            $phoneUtil = PhoneNumberUtil::getInstance();

            return $phoneUtil->format($phone, PhoneNumberFormat::E164);
        } else {
            return $phone;
        }
    }
}
