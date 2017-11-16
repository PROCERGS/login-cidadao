<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Exception;

class DynamicRegistrationException extends \Exception
{
    const ERROR_INVALID_REDIRECT_URI    = 'invalid_redirect_uri';
    const ERROR_INVALID_CLIENT_METADATA = 'invalid_client_metadata';

    protected $message;
    protected $code;

    public function __construct($message, $code, $previous = null)
    {
        parent::__construct($message, null, $previous);
        $this->code    = $code;
        $this->message = $message;
    }

    public function getData()
    {
        return array(
            'error' => $this->code,
            'error_description' => $this->message
        );
    }
}
