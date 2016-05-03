<?php

namespace LoginCidadao\APIBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RequestTimeoutException extends HttpException
{

    public function __construct($message = null, \Exception $previous = null,
                                $statusCode = 408, array $headers = array(),
                                $code = 0)
    {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

}
