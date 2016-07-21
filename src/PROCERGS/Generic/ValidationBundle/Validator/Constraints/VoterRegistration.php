<?php
namespace PROCERGS\Generic\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class VoterRegistration extends Constraint
{

    public $message = 'voter_registration.invalid';
}
