<?php

namespace LoginCidadao\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class CEPValidator extends ConstraintValidator
{
    /**
     * Currently only checks if the value is numeric.
     * @param string $cep
     * @return boolean
     */
    public static function isCEPValid($cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);

        return is_numeric($cep);
    }

    public static function checkLength($cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);

        return strlen($cep) == 8;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!isset($value) || $value === null || !strlen(trim($value))) {
            return;
        }
        if (!self::checkLength($value)) {
            $this->context->addViolation($constraint->lengthMessage);
        }
        if (!self::isCEPValid($value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
