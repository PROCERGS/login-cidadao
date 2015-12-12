<?php

namespace LoginCidadao\CoreBundle\Security\Exception;

class AlreadyLinkedAccount extends \Exception
{

    public function __construct($message = null)
    {
        if (is_null($message)) {
            $message = 'exception.alreadyLinkedAccount';
        }
        parent::__construct($message);
    }

}
