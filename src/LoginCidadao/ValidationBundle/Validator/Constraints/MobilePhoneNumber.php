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
class MobilePhoneNumber extends Constraint
{
    public $missing9thDigit = 'mobile.missing_9th_digit';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
