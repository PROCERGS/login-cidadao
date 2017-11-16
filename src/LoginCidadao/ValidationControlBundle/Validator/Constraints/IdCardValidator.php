<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\ValidationControlBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use LoginCidadao\ValidationControlBundle\Handler\ValidationHandler;

/**
 * @Annotation
 */
class IdCardValidator extends ConstraintValidator
{
    /** @var ValidationHandler */
    private $handler;

    public function __construct(ValidationHandler $handler)
    {
        $this->handler = $handler;
    }

    public function validate($value, Constraint $constraint)
    {
        $this->handler->idCardValidate($this->context, $constraint, $value);
    }
}
