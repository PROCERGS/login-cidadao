<?php

namespace LoginCidadao\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CPF extends Constraint
{

    public $message = '%string% is not a CPF.';
    public $lengthMessage = 'A CPF must contain 11 numbers.';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

}
