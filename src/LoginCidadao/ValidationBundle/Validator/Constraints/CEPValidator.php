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
    
    public static function justNum($cep)
    {
        return preg_replace('/[^0-9]/', '', $cep);
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
    
    public static function validateMask($cep, $postal)
    {
        $a = strlen($postal);
        $exp = '/^';
        for ($i = 0; $i < $a; $i++) {
            if ($postal[$i] != '"') {
                if (is_numeric($postal[$i])) {
                    $exp .= '[0-'.$postal[$i].']{1}';
                } else if ($postal[$i] == ' ') {
                    $exp .= "\\".$postal[$i];
                } else {
                    $exp .= $postal[$i];
                }
            }
        }
        $exp .= '$/';
        return preg_match($exp, $cep);
    }

}
