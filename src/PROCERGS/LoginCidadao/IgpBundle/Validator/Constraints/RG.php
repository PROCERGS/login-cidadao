<?php

namespace PROCERGS\LoginCidadao\IgpBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RG extends Constraint
{

    public $message = 'Invalid RG.';
    public $lengthMessage = 'RG must be 10 characters long.';

    public function validatedBy()
    {
        return 'igp_rg_validator';
    }
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}
