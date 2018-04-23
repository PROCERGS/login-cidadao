<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ClaimUriUnavailableException extends \RuntimeException
{
    /**
     * ClaimUriUnavailableException constructor.
     * @param string $message
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", \Throwable $previous = null)
    {
        parent::__construct($message, Response::HTTP_NOT_FOUND, $previous);
    }
}
