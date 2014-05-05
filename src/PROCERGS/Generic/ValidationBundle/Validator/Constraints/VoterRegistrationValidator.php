<?php
namespace PROCERGS\Generic\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class VoterRegistrationValidator extends ConstraintValidator
{

    /**
     * Currently only checks if the value is numeric.
     *
     * @param string $cep            
     * @return boolean
     */
    public static function isValid($var)
    {
        $paddedInsc = str_pad($var, 12, "0", STR_PAD_LEFT);
        
        $dig1 = 0;
        $dig2 = 0;
        $tam = strlen($paddedInsc);
        $digitos = substr($paddedInsc, $tam - 2, 2);
        $estado = substr($paddedInsc, $tam - 4, 2);
        $titulo = substr($paddedInsc, 0, $tam - 2);
        $exce = ($estado == '01') || ($estado == '02');
        $dig1 = (ord($titulo[0]) - 48) * 9 + (ord($titulo[1]) - 48) * 8 + (ord($titulo[2]) - 48) * 7 + (ord($titulo[3]) - 48) * 6 + (ord($titulo[4]) - 48) * 5 + (ord($titulo[5]) - 48) * 4 + (ord($titulo[6]) - 48) * 3 + (ord($titulo[7]) - 48) * 2;
        $resto = ($dig1 % 11);
        if ($resto == 0) {
            if ($exce) {
                $dig1 = 1;
            } else {
                $dig1 = 0;
            }
        } else {
            if ($resto == 1) {
                $dig1 = 0;
            } else {
                $dig1 = 11 - $resto;
            }
        }
        $dig2 = (ord($titulo[8]) - 48) * 4 + (ord($titulo[9]) - 48) * 3 + $dig1 * 2;
        $resto = ($dig2 % 11);
        if ($resto == 0) {
            if ($exce) {
                $dig2 = 1;
            } else {
                $dig2 = 0;
            }
        } else {
            if ($resto == 1) {
                $dig2 = 0;
            } else {
                $dig2 = 11 - $resto;
            }
        }
        if ((ord($digitos[0]) - 48 == $dig1) && (ord($digitos[1]) - 48 == $dig2)) {
            return true;
        } else {
            return false;
        }
    }

    public function validate($value, Constraint $constraint)
    {
        if (! isset($value) || $value === null || ! strlen(trim($value))) {
            return;
        }
        if (! self::isValid($value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
