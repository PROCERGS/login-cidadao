<?php

namespace LoginCidadao\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CEP extends Constraint
{

    public $message = 'Invalid CEP.';
    public $lengthMessage = 'CEP must be 8 characters long.';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

}
