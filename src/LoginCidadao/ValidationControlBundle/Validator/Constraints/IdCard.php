<?php

namespace LoginCidadao\ValidationControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IdCard extends Constraint
{

    public function validatedBy()
    {
        return "validation.idcard.validator";
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}
