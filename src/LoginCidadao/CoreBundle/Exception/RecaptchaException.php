<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Exception;


use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class RecaptchaException extends BadCredentialsException
{

    /**
     * RecaptchaException constructor.
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getClass()
    {
        return get_class($this);
    }
}
