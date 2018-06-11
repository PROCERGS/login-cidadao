<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        return CPFValidator::class;
    }
}
