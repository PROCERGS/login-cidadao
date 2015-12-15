<?php

namespace LoginCidadao\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class CPFValidator extends ConstraintValidator
{

    /**
     * @param string $cpf
     * @return boolean
     */
    public static function isCPFValid($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (!is_numeric($cpf)) {
            return false;
        }

        $check1 = 0;
        $check2 = 0;

        if (strlen($cpf) != 11 || preg_match('/([0-9])\\1{10}/', $cpf)) {
            return false;
        }

        for ($i = 0, $x = 10; $i <= 8; $i++, $x--) {
            $check1 += $cpf[$i] * $x;
        }
        for ($i = 0, $x = 11; $i <= 9; $i++, $x--) {
            $iStr = "$i";
            if (str_repeat($iStr, 11) == $cpf) {
                return false;
            }
            $check2 += $cpf[$i] * $x;
        }

        $calc1 = (($check1 % 11) < 2) ? 0 : 11 - ($check1 % 11);
        $calc2 = (($check2 % 11) < 2) ? 0 : 11 - ($check2 % 11);
        if ($calc1 != $cpf[9] || $calc2 != $cpf[10]) {
            return false;
        }
        return true;
    }

    public static function checkLength($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return strlen($cpf) == 11;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!isset($value) || null === $value || '' === $value) {
            return;
        }
        if (!self::checkLength($value)) {
            $this->context->addViolation($constraint->lengthMessage);
        }
        if (!self::isCPFValid($value)) {
            $this->context->addViolation(
                    $constraint->message, array('%string%' => $value)
            );
        }
    }

}
