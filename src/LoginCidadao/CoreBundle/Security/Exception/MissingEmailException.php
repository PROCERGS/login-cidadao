<?php

namespace LoginCidadao\CoreBundle\Security\Exception;

use Exception;

class MissingEmailException extends \Exception
{
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }
}
