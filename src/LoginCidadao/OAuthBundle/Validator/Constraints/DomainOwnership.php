<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target("CLASS")
 */
class DomainOwnership extends Constraint
{
    public $unknown         = "Domain validation failed.";
    public $domainMismatch  = "Your domain doesn't match the domain used on the Validation URL";
    public $invalidResponse = "Your validation code wasn't found.";
    public $invalidUrl      = "Invalid Validation URL. Check that it's a valid URL and that it doesn't have a query string.";

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
