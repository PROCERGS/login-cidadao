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
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Uri extends Constraint
{
    const INVALID_URL_ERROR = '57c2f299-1154-4870-89bb-ef3b1f5ad229';

    protected static $errorNames = array(
        self::INVALID_URL_ERROR => 'INVALID_URL_ERROR',
    );

    public $message = 'This value is not a valid URI.';
    public $dnsMessage = 'The host could not be resolved.';
    public $checkDNS = false;
}
