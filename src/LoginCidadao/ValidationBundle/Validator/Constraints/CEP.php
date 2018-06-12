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
class CEP extends Constraint
{

    public $message = 'Invalid CEP.';
    public $lengthMessage = 'CEP must be 8 characters long.';

    public function validatedBy()
    {
        return CEPValidator::class;
    }

}
