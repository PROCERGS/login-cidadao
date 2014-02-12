<?php

namespace PROCERGS\Generic\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CPF extends Constraint
{

    public $message = '%string% is not a CPF.';

    public function validatedBy()
    {
        return get_class($this) . 'Validator';
    }

}
