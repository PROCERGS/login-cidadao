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
 * Class Age
 * @package LoginCidadao\ValidationBundle\Validator\Constraints
 * @Annotation
 */
class Age extends Constraint
{
    public $min = 0;
    public $max = 150;

    public $futureError = 'person.validation.age.future';
    public $minError = 'person.validation.age.min';
    public $maxError = 'person.validation.age.max';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
