<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SectorIdentifier extends Constraint
{
    public $message = 'Invalid or missing sector_identifier_uri';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
