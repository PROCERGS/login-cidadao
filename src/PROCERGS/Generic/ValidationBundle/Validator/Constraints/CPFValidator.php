<?php

namespace PROCERGS\Generic\ValidationBundle\Validator\Constraints;

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
        $cpf = preg_replace('[^0-9]', '', $cpf);
        if (!is_numeric($cpf)) {
            return false;
        }

        $digitoUm = 0;
        $digitoDois = 0;

        if (strlen($cpf) != 11 || preg_match('/([0-9])\\1{10}/', $cpf)) {
            return false;
        }

        for ($i = 0, $x = 10; $i <= 8; $i++, $x--) {
            $digitoUm += $cpf[$i] * $x;
        }
        for ($i = 0, $x = 11; $i <= 9; $i++, $x--) {
            $iStr = "$i";
            if (str_repeat($iStr, 11) == $cpf) {
                return false;
            }
            $digitoDois += $cpf[$i] * $x;
        }

        $calculoUm = (($digitoUm % 11) < 2) ? 0 : 11 - ($digitoUm % 11);
        $calculoDois = (($digitoDois % 11) < 2) ? 0 : 11 - ($digitoDois % 11);
        if ($calculoUm != $cpf[9] || $calculoDois != $cpf[10]) {
            return false;
        }
        return true;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!self::isCPFValid($value)) {
            $this->context->addViolation(
                    $constraint->message, array('%string%' => $value)
            );
        }
    }

}
