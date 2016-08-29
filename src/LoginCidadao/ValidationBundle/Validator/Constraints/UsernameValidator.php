<?php
namespace LoginCidadao\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class UsernameValidator extends ConstraintValidator
{

    /**
     * @param $var
     * @return bool
     */
    public static function isUsernameValid($var)
    {
        return preg_match('/^[A-Za-z0-9_.-]+$/i', $var) && ($a = strlen($var)) && $a >= 1 && $a <= 40;
    }

    public static function getValidUsername()
    {
        return uniqid(mt_rand(), true);
    }

    public function validate($value, Constraint $constraint)
    {
        if (!isset($value) || $value === null || !strlen(trim($value))) {
            return;
        }
        if (!self::isUsernameValid($value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
