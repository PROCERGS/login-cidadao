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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class E164PhoneNumberValidator extends ConstraintValidator
{
    const MAX_E164_LENGTH = 15;

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        // Remove formatting
        $number = preg_replace('/[^0-9]/', '', $value);

        // Check length
        if (strlen($number) > self::MAX_E164_LENGTH
            && $constraint instanceof E164PhoneNumber
        ) {
            $this->context->addViolation($constraint->maxMessage);
        }
    }
}
