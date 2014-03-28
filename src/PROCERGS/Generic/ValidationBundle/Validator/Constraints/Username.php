<?php
namespace PROCERGS\Generic\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Username extends Constraint
{

    public $message = 'change_username.invalid.username';
}
